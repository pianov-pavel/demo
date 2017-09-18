Развертывание приложения:
Подразумевается, что развертывание происходит в окружении Unix:<br>
-- установлены php 7.0<br>
-- PostgreSQL<br>
-- Apache 2 - настройки дефолтные<br>
-- проект разворачивать в директории web-сервера var/www/html


1) git clone https://github.com/pianov-pavel/demo.git
2) composer init
3) php init
4) Создать БД и прописать в конфиге фреймворка настройки подключения к базе
5) php yii migrate
6) ./yii test/users base_user_name base_domain password - команда для генерации пользователей
где, base_user_name - основа для username, base_domain - почтовый домен, password - пароль

7) http://localhost/demo/backend/web/index.php - админка
8) http://localhost/demo/frontend/web/index.php - интерфейс юзера