<?php

$router->get('/', 'HomeController@index', ['AuthOnly']);

$router->get('/login', 'AuthController@index', ['GuestOnly']);
$router->post('/login', 'AuthController@login', ['GuestOnly', 'ValidateCsrf']);
$router->get('/logout', 'AuthController@logout', ['AuthOnly']);

$router->get('/customers', 'CustomerController@index', ['AuthOnly']);
$router->get('/customers/add', 'CustomerController@add', ['AuthOnly']);
$router->post('/customers/add', 'CustomerController@insert', ['AuthOnly', 'ValidateCsrf']);
$router->get('/customers/edit/{id}', 'CustomerController@edit', ['AuthOnly']);
$router->post('/customers/edit/{id}', 'CustomerController@update', ['AuthOnly', 'ValidateCsrf']);
$router->get('/customers/delete/{id}', 'CustomerController@delete', ['AuthOnly']);

$router->get('/groups', 'GroupController@index', ['AuthOnly']);
$router->get('/groups/add', 'GroupController@add', ['AuthOnly']);
$router->post('/groups/add', 'GroupController@insert', ['AuthOnly', 'ValidateCsrf']);
$router->get('/groups/edit/{id}', 'GroupController@edit', ['AuthOnly']);
$router->post('/groups/edit/{id}', 'GroupController@update', ['AuthOnly', 'ValidateCsrf']);
$router->get('/groups/delete/{id}', 'GroupController@delete', ['AuthOnly']);

$router->get('/awards', 'AwardController@index', ['AuthOnly']);
$router->get('/awards/add', 'AwardController@add', ['AuthOnly']);
$router->post('/awards/add', 'AwardController@insert', ['AuthOnly', 'ValidateCsrf']);
$router->get('/awards/edit/{id}', 'AwardController@edit', ['AuthOnly']);
$router->post('/awards/edit/{id}', 'AwardController@update', ['AuthOnly', 'ValidateCsrf']);
$router->get('/awards/delete/{id}', 'AwardController@delete', ['AuthOnly']);

$router->get('/products', 'ProductController@index', ['AuthOnly']);
$router->get('/products/add', 'ProductController@add', ['AuthOnly']);
$router->post('/products/add', 'ProductController@insert', ['AuthOnly', 'ValidateCsrf']);
$router->get('/products/edit/{id}', 'ProductController@edit', ['AuthOnly']);
$router->post('/products/edit/{id}', 'ProductController@update', ['AuthOnly', 'ValidateCsrf']);
$router->get('/products/delete/{id}', 'ProductController@delete', ['AuthOnly']);

$router->get('/users', 'UserController@index', ['AuthOnly']);
$router->get('/users/add', 'UserController@add', ['AuthOnly']);
$router->post('/users/add', 'UserController@insert', ['AuthOnly', 'ValidateCsrf']);
$router->get('/users/edit/{id}', 'UserController@edit', ['AuthOnly']);
$router->post('/users/edit/{id}', 'UserController@update', ['AuthOnly', 'ValidateCsrf']);
$router->get('/users/delete/{id}', 'UserController@delete', ['AuthOnly']);

$router->get('/levels', 'LevelController@index', ['AuthOnly']);
$router->get('/levels/add', 'LevelController@add', ['AuthOnly']);
$router->post('/levels/add', 'LevelController@insert', ['AuthOnly', 'ValidateCsrf']);
$router->get('/levels/edit/{id}', 'LevelController@edit', ['AuthOnly']);
$router->post('/levels/edit/{id}', 'LevelController@update', ['AuthOnly', 'ValidateCsrf']);
$router->get('/levels/delete/{id}', 'LevelController@delete', ['AuthOnly']);

$router->get('/companies', 'CompanyController@index', ['AuthOnly']);
$router->get('/companies/add', 'CompanyController@add', ['AuthOnly']);
$router->post('/companies/add', 'CompanyController@insert', ['AuthOnly', 'ValidateCsrf']);
$router->get('/companies/edit/{id}', 'CompanyController@edit', ['AuthOnly']);
$router->post('/companies/edit/{id}', 'CompanyController@update', ['AuthOnly', 'ValidateCsrf']);
$router->get('/companies/delete/{id}', 'CompanyController@delete', ['AuthOnly']);
