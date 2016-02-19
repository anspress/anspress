#!/usr/bin/env bash

#if [ $# -lt 3 ]; then
	#echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version]"
	#exit 1
#fi

DB_NAME=${1-wptest}
DB_USER=${2-wptest}
DB_PASS=${3-wptest}
WP_VERSION=${4-latest}
DB_HOST=${5-localhost}

WWW=/var/www
HOST=wptest

WP_TESTS_DIR=${WP_TESTS_DIR-/var/www/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-/var/www/wptest/}

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
		
		composer global require codeception/codeception 

		export PATH="$PATH:$HOME/.composer/vendor/bin"		
	}

	if [ ! -f /root/selenium/selenium-server-standalone-2.42.2.jar ]; then
		cd /root
		sudo mkdir -p selenium
		cd selenium
		wget https://selenium-release.storage.googleapis.com/2.42/selenium-server-standalone-2.42.2.jar
	fi

	screen -S test -d -m java -jar /root/selenium/selenium-server-standalone-2.42.2.jar -Djava.security.egd=file:///dev/urandom switch
}

vhost(){
	# creates virtual hosts.
	# Create the file with VirtualHost configuration in /etc/apache2/site-available/
	echo "<VirtualHost *:80>
	        DocumentRoot /var/www/$HOST/
	        ServerName $HOST.localhost
	        ServerAlias wptest.anspress.io
	        <Directory /var/www/$HOST/>
                Options +Indexes +FollowSymLinks +MultiViews +Includes
                AllowOverride All
                Order allow,deny
                allow from all
	        </Directory>
	</VirtualHost>" > /etc/apache2/sites-available/$HOST.conf
	 
	# Add the host to the hosts file
	echo 127.0.0.1 $HOST.localhost >> /etc/hosts

	# Enable the site
	a2ensite $HOST
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

install_wp() {

	if [ -d $WP_CORE_DIR ]; then
		return;
	fi

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
		download https://wordpress.org/${ARCHIVE_NAME}.tar.gz  /tmp/wordpress.tar.gz
		tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C $WP_CORE_DIR
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
	DBS=`mysql -u$DB_USER -p$DB_PASS -Bse 'show databases'| egrep -v 'information_schema|mysql'`
	for db in $DBS; do
	if [ "$db" = "$DB_NAME" ]
	then
	mysqladmin DROP $DB_NAME -f --user="$DB_USER" --password="$DB_PASS"$EXTRA
	  fi
	done

	mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA
}

copy_anspress(){
	NEW_ANSPRESS_DIR="$WP_CORE_DIR/wp-content/plugins/anspress-question-answer"
	
	rm -rf "$NEW_ANSPRESS_DIR"
	rm -rf "$WP_CORE_DIR/wp-config.php"
	mkdir -p "$NEW_ANSPRESS_DIR"

	cd $BIN_DIR
	cp -r "../../"* "$NEW_ANSPRESS_DIR/"
}

core_install(){
	chown -R www-data:www-data $WP_CORE_DIR
	cd $WP_CORE_DIR
	echo "apache_modules:
  - mod_rewrite" > wp-cli.yml
	wp core config --dbname=wptest --dbuser=wptest --dbpass=wptest --allow-root
	wp core install --url='http://wptest.localhost/' --title='AnsPress_test' --admin_user='admin' --admin_password='admin' --admin_email=support@wptest.localhost --allow-root
	wp rewrite structure '/%postname%/' --hard --allow-root
	wp plugin activate anspress-question-answer --allow-root
	wp theme install twentytwelve --activate --allow-root
	wp user create user1 user1@localhost.com --user_pass='user1' --allow-root
	wp user create user2 user2@localhost.com --user_pass='user2' --allow-root
	wp user create user3 user3@localhost.com --user_pass='user3' --allow-root
	wp user create user4 user4@localhost.com --user_pass='user4' --allow-root
}

install_composer
vhost
install_wp
install_test_suite
install_db
copy_anspress
core_install
