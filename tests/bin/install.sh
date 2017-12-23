#!/usr/bin/env bash

#if [ $# -lt 3 ]; then
	#echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version]"
	#exit 1
#fi

DB_NAME=${1-aptest}
DB_USER=${2-aptest}
DB_PASS=${3-aptest}
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}

WWW=/var/www
HOST=aptest

cd "$TRAVIS_BUILD_DIR"
WP_TESTS_DIR="$TRAVIS_BUILD_DIR/wordpress-tests-lib"
WP_CORE_DIR="$TRAVIS_BUILD_DIR/www"

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
BIN_DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

install_composer(){
	command -v composer >/dev/null 2>&1 || {
		stty -echo
		sudo apt-get install curl php5-cli git -y
		curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
	}

	#echo 'export PATH="$PATH:/home/travis/.composer/vendor/bin"' >> ~/.bashrc
	# curl -i http://localhost:4444/wd/hub/status
}

install_wpcli(){
	command -v wp >/dev/null 2>&1 || {
		cd /tmp
		curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
		chmod +x wp-cli.phar
		sudo mv wp-cli.phar /usr/local/bin/wp
		wp --info
	}
}

vhost(){
	# creates virtual hosts.
	# Create the file with VirtualHost configuration in /etc/apache2/site-available/
	echo "<VirtualHost *:80>
        ServerName aptest.local
        ServerAdmin webmaster@localhost

        DocumentRoot PATH

          <Directory PATH>
            Options FollowSymLinks MultiViews ExecCGI
            AllowOverride All
            Order deny,allow
            Allow from all
          </Directory>

          # Wire up Apache to use Travis CI's php-fpm.
          <IfModule mod_fastcgi.c>
            AddHandler php5-fcgi .php
            Action php5-fcgi /php5-fcgi
            Alias /php5-fcgi /usr/lib/cgi-bin/php5-fcgi
            FastCgiExternalServer /usr/lib/cgi-bin/php5-fcgi -host 127.0.0.1:9000 -pass-header Authorization
          </IfModule>

    </VirtualHost>" | sed -e "s,PATH,`pwd`/www,g" | sudo tee /etc/apache2/sites-available/default > /dev/null

	# Add the host to the hosts file
	sudo echo 127.0.0.1 $HOST.local >> /etc/hosts

	# Enable the site
	sudo a2ensite $HOST
	sudo service apache2 restart
}


download() {
    if [ `which curl` ]; then
        curl -s "$1" > "$2";
    elif [ `which wget` ]; then
        wget -nv -O "$2" "$1"
    fi
}

if [[ $WP_VERSION =~ [0-9]+\.[0-9]+(\.[0-9]+)? ]]; then
	WP_TESTS_TAG="tags/$WP_VERSION"
elif [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
	WP_TESTS_TAG="trunk"
else
	# http serves a single offer, whereas https serves multiple. we only want one
	download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
	grep '[0-9]+\.[0-9]+(\.[0-9]+)?' /tmp/wp-latest.json
	LATEST_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//')
	if [[ -z "$LATEST_VERSION" ]]; then
		echo "Latest WordPress version could not be found"
		exit 1
	fi
	WP_TESTS_TAG="tags/$LATEST_VERSION"
fi

set -ex

download_wp() {

	#if [ -d $WP_CORE_DIR ]; then
		#return;
	#fi

	mkdir -p $WP_CORE_DIR

	if [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
		mkdir -p /tmp/wordpress-nightly
		download https://wordpress.org/nightly-builds/wordpress-latest.zip  /tmp/wordpress-nightly/wordpress-nightly.zip
		unzip -q /tmp/wordpress-nightly/wordpress-nightly.zip -d /tmp/wordpress-nightly/
		mv /tmp/wordpress-nightly/wordpress/* $WP_CORE_DIR
	else
		if [ $WP_VERSION == 'latest' ]; then
			local ARCHIVE_NAME='latest'
		else
			local ARCHIVE_NAME="wordpress-$WP_VERSION"
		fi
		download https://wordpress.org/${ARCHIVE_NAME}.tar.gz  ${TRAVIS_BUILD_DIR}/wordpress.tar.gz
		tar --strip-components=1 -zxmf ${TRAVIS_BUILD_DIR}/wordpress.tar.gz -C $WP_CORE_DIR
	fi

	download https://raw.github.com/markoheijnen/wp-mysqli/master/db.php $WP_CORE_DIR/wp-content/db.php
}

install_test_suite() {
	# portable in-place argument for both GNU sed and Mac OSX sed
	if [[ $(uname -s) == 'Darwin' ]]; then
		local ioption='-i .bak'
	else
		local ioption='-i'
	fi

	# set up testing suite if it doesn't yet exist
	if [ ! -d $WP_TESTS_DIR ]; then
		# set up testing suite
		mkdir -p $WP_TESTS_DIR
		svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/ $WP_TESTS_DIR/includes
	fi

	cd $WP_TESTS_DIR

	if [ ! -f wp-tests-config.php ]; then
		download https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR':" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s|localhost|${DB_HOST}|" "$WP_TESTS_DIR"/wp-tests-config.php
	fi

}

install_db() {
	# parse DB_HOST for port or socket references
	local PARTS=(${DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]};
	local EXTRA=""

	if ! [ -z $DB_HOSTNAME ] ; then
		if [ $(echo $DB_SOCK_OR_PORT | grep -e '^[0-9]\{1,\}$') ]; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	# create database
	# DBS=`mysql -u$DB_USER -p$DB_PASS -Bse 'show databases'| egrep -v 'information_schema|mysql'`
	# for db in $DBS; do
	# if [ "$db" = "$DB_NAME" ]
	# then
	# mysqladmin DROP $DB_NAME -f --user="$DB_USER" --password="$DB_PASS"$EXTRA
	#   fi
	# done

	mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA
}

copy_anspress(){
	whoami
	NEW_ANSPRESS_DIR="$WP_CORE_DIR/wp-content/plugins/anspress-question-answer"

	rm -rf "$NEW_ANSPRESS_DIR"
	rm -rf "$WP_CORE_DIR/wp-config.php"
	sudo mkdir -p "$NEW_ANSPRESS_DIR"
	sudo chown -R travis:travis $NEW_ANSPRESS_DIR
	cd $NEW_ANSPRESS_DIR
	git init
	git remote add origin "https://github.com/anspress/anspress.git"
	git fetch origin
	sudo git reset --hard origin/master
}

core_install(){
	cd $WP_CORE_DIR
	sudo echo "apache_modules:
  - mod_rewrite" > wp-cli.yml
  	sudo chown -R travis:travis $WP_CORE_DIR

	wp core config --dbname=$DB_NAME --dbuser=$DB_USER --dbpass="$DB_PASS" --allow-root
	wp core install --url='http://aptest.local/' --title='AnsPress_test' --admin_user='admin' --admin_password='admin' --admin_email=support@wptest.localhost --allow-root
	cat "$WP_CORE_DIR/wp-config.php"
	# wp rewrite structure '/%postname%/' --hard --allow-root
	# wp plugin activate anspress-question-answer --allow-root

	# if [ $EXTENSIONS == 'true' ]; then
	# 	wp plugin install categories-for-anspress --activate --allow-root
	# 	wp plugin install tags-for-anspress --activate --allow-root
	# 	wp plugin install anspress-email --activate --allow-root
	# 	wp theme install twentytwelve --activate --allow-root
	# fi
}

install_composer
install_wpcli
#vhost
download_wp
install_test_suite
install_db
copy_anspress
core_install