<?php

define('ABSPATH', dirname(__DIR__));

require(ABSPATH . '/bootstrap/config.php');
require(ABSPATH . '/bootstrap/secure.php');
require(ABSPATH . '/bootstrap/autoload.php');

use Services\Container;
use Services\Router;

$container = new Container;

Container::setContainer($container);

require(ABSPATH . '/app/support/services.php');

$router = new Router($container);

require(ABSPATH . '/routes/web.php');

$router->dispatch();
