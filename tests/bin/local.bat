cd /d "D:\xampp\htdocs\aptest"
wp db reset --yes|more
call wp core install --url=aptest.local --title=AnsPress --admin_user=admin --admin_password=admin --admin_email=test@admin.com
call wp rewrite structure '/%postname%/' --hard --allow-root
start selenium ^&^& exit
PATH %PATH%;D:\xampp\htdocs\anspress\wp-content\plugins\anspress-question-answer\vendor\bin
cd /d "D:\xampp\htdocs\anspress\wp-content\plugins\anspress-question-answer"
git checkout-index -a -f --prefix=D:/xampp/htdocs/aptest/wp-content/plugins/anspress-question-answer/
cd /d "D:\xampp\htdocs\aptest"
call wp plugin activate anspress-question-answer/anspress-question-answer.php
call wp theme activate twentytwelve
cd /d "D:\xampp\htdocs\anspress\wp-content\plugins\anspress-question-answer"
call codecept run ui --steps --debug
@ECHO OFF
FOR /F "tokens=5 delims= " %%P IN ('netstat -ano ^| findstr 4444') DO (
  if /i not "%%P" == "0" (
    taskkill /PID %%P 2>NUL
  )
)
cmd /k