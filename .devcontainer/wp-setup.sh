#!  /bin/bash

#Site configuration options
SITE_TITLE="AnsPress Dev Site"
ADMIN_USER=admin
ADMIN_PASS=password
ADMIN_EMAIL="admin@localhost.com"
#Space-separated list of plugin ID's to install and activate
PLUGINS="query-monitor rewrite-rules-inspector fakerpress wp-mail-logging"

#Set to true to wipe out and reset your wordpress install (on next container rebuild)
WP_RESET=true

echo "Setting up WordPress"
DEVDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd /var/www/html;
if $WP_RESET ; then
    echo "Resetting WP"
    wp plugin delete $PLUGINS
    wp db reset --yes
    rm wp-config.php;
fi

if [ ! -f wp-config.php ]; then
    echo "Configuring";
	cp /var/www/html/wp-content/plugins/anspress-question-answer/.devcontainer/wp-config.php /var/www/html/wp-config.php;

    #Data import
    # cd $DEVDIR/data/
    # for f in *.sql; do
    #     wp db import $f --path=/var/www/html
    # done

    cd /var/www/html;

    # wp config create --dbhost="db" --dbname="wordpress" --dbuser="wp_user" --dbpass="wp_pass" --skip-check;
    wp core install --url="http://localhost:8080" --title="$SITE_TITLE" --admin_user="$ADMIN_USER" --admin_email="$ADMIN_EMAIL" --admin_password="$ADMIN_PASS" --skip-email;

    wp rewrite structure '/%postname%/';
    wp rewrite flush --hard;
    wp plugin install $PLUGINS --activate;
    wp plugin activate anspress-question-answer;
    wp theme install twentytwelve --activate;
    curl -N http://loripsum.net/api/5 | wp post generate --count=100 --post_type=question;
    wp user generate --count=100;

    # cp -r plugins/* /var/www/html/wp-content/plugins
    # for p in plugins/*; do
    #     wp plugin activate $(basename $p)
    # done
else
    echo "Already configured"
fi
