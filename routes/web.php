<?php

$router->get('/', 'MainController@index', 'OnlyAuthenticated');
