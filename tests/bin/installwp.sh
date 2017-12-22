#!/usr/bin/env bash

core_install(){
	cd $WP_CORE_DIR
	sudo echo "apache_modules:
  - mod_rewrite" > wp-cli.yml
  	sudo chown -R travis:travis $WP_CORE_DIR

	wp core config --dbname=$DB_NAME --dbuser=$DB_USER --dbpass="$DB_PASS" --allow-root
	wp core install --url='http://aptest.localhost/' --title='AnsPress_test' --admin_user='admin' --admin_password='admin' --admin_email=support@wptest.localhost --allow-root
	# wp rewrite structure '/%postname%/' --hard --allow-root
	# wp plugin activate anspress-question-answer --allow-root

	# if [ $EXTENSIONS == 'true' ]; then
	# 	wp plugin install categories-for-anspress --activate --allow-root
	# 	wp plugin install tags-for-anspress --activate --allow-root
	# 	wp plugin install anspress-email --activate --allow-root
	# 	wp theme install twentytwelve --activate --allow-root
	# fi
}