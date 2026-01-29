<?php

$router->get('/login', 'AuthController@index', ['onlyVisitor']);
$router->post('/login', 'AuthController@login', ['ValidateCsrf', 'onlyVisitor']);
$router->get('/logout', 'AuthController@logout', ['AuthenticationRequired']);

$router->get('/', 'MainController@index', ['AuthenticationRequired']);

$router->get('/users', 'UserController@index', ['AuthenticationRequired']);
$router->get('/users/add', 'UserController@create', ['AuthenticationRequired']);
$router->post('/users/add', 'UserController@store', ['ValidateCsrf', 'AuthenticationRequired']);
$router->get('/users/edit/{id}', 'UserController@edit', ['AuthenticationRequired']);
$router->post('/users/edit/{id}', 'UserController@update', ['ValidateCsrf', 'AuthenticationRequired']);

$router->get('/levels', 'LevelController@index', ['AuthenticationRequired']);
$router->get('/levels/add', 'LevelController@create', ['AuthenticationRequired']);
$router->post('/levels/add', 'LevelController@store', ['ValidateCsrf', 'AuthenticationRequired']);
$router->get('/levels/edit/{id}', 'LevelController@edit', ['AuthenticationRequired']);
$router->post('/levels/edit/{id}', 'LevelController@update', ['ValidateCsrf', 'AuthenticationRequired']);
