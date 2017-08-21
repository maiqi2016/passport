<?php
Yii::setAlias('@passport', dirname(__DIR__));

Yii::setAlias('@rsa', dirname(__DIR__) . '/mixed/rsa');
Yii::setAlias('@thrift', dirname(__DIR__) . '/mixed/thrift');

define('VERSION', '0.0.1');

define('TIME', $_SERVER['REQUEST_TIME']);
define('DS', DIRECTORY_SEPARATOR);
define('DOMAIN', 'kakehotels.com');
define('PROJECT', 'kake');

define('DB_KAKE', 'kake');
define('DB_SERVICE', 'service');

define('MINUTE', 60);
define('HOUR', MINUTE * 60);
define('DAY', HOUR * 24);
define('WEEK', DAY * 7);
define('MONTH', DAY * 30);
define('YEAR', MONTH * 12);