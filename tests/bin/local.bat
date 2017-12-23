cd /d "D:\xampp2\htdocs\aptest"
wp db reset --yes|more
wp core install --url=aptest.local --title=AnsPress --admin_user=admin --admin_password=admin --admin_email=test@admin.com|more
start selenium ^&^& exit
PATH %PATH%;D:\xampp2\htdocs\anspress\wp-content\plugins\anspress-question-answer\vendor\bin
cd /d "D:\xampp2\htdocs\anspress\wp-content\plugins\anspress-question-answer"
git checkout-index -a -f --prefix=D:/xampp2/htdocs/aptest/wp-content/plugins/anspress-question-answer/
call codecept run ui --steps
@ECHO OFF
FOR /F "tokens=5 delims= " %%P IN ('netstat -ano ^| findstr 4444') DO (
  if /i not "%%P" == "0" (
    taskkill /PID %%P 2>NUL
  )
)
cmd /k