# IOT 800 Conversational

Symfony application to manage the interface with Telegram and Alexa
The application uses a SQLite database.

## How to install
 
~~~
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
~~~
Call `/ping` from the backend to let the application register the backend's ip address. There is a required header to accept the call.

