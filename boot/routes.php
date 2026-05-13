<?php

use Core\Router;

Router::get('/', 'HomeController@index', ['AuthOnly']);

Router::get('/login', 'AuthController@index', ['GuestOnly']);
Router::post('/login', 'AuthController@login', ['GuestOnly', 'ValidateCsrf']);
Router::get('/logout', 'AuthController@logout', ['AuthOnly']);

Router::get('/customers', 'CustomerController@index', ['AuthOnly']);
Router::get('/customers/add', 'CustomerController@add', ['AuthOnly']);
Router::post('/customers/add', 'CustomerController@insert', ['AuthOnly', 'ValidateCsrf']);
Router::get('/customers/edit/{id}', 'CustomerController@edit', ['AuthOnly']);
Router::post('/customers/edit/{id}', 'CustomerController@update', ['AuthOnly', 'ValidateCsrf']);
Router::get('/customers/delete/{id}', 'CustomerController@delete', ['AuthOnly']);

Router::get('/groups', 'GroupController@index', ['AuthOnly']);
Router::get('/groups/add', 'GroupController@add', ['AuthOnly']);
Router::post('/groups/add', 'GroupController@insert', ['AuthOnly', 'ValidateCsrf']);
Router::get('/groups/edit/{id}', 'GroupController@edit', ['AuthOnly']);
Router::post('/groups/edit/{id}', 'GroupController@update', ['AuthOnly', 'ValidateCsrf']);
Router::get('/groups/delete/{id}', 'GroupController@delete', ['AuthOnly']);

Router::get('/awards', 'AwardController@index', ['AuthOnly']);
Router::get('/awards/add', 'AwardController@add', ['AuthOnly']);
Router::post('/awards/add', 'AwardController@insert', ['AuthOnly', 'ValidateCsrf']);
Router::get('/awards/edit/{id}', 'AwardController@edit', ['AuthOnly']);
Router::post('/awards/edit/{id}', 'AwardController@update', ['AuthOnly', 'ValidateCsrf']);
Router::get('/awards/delete/{id}', 'AwardController@delete', ['AuthOnly']);

Router::get('/products', 'ProductController@index', ['AuthOnly']);
Router::get('/products/add', 'ProductController@add', ['AuthOnly']);
Router::post('/products/add', 'ProductController@insert', ['AuthOnly', 'ValidateCsrf']);
Router::get('/products/edit/{id}', 'ProductController@edit', ['AuthOnly']);
Router::post('/products/edit/{id}', 'ProductController@update', ['AuthOnly', 'ValidateCsrf']);
Router::get('/products/delete/{id}', 'ProductController@delete', ['AuthOnly']);

Router::get('/users', 'UserController@index', ['AuthOnly']);
Router::get('/users/add', 'UserController@add', ['AuthOnly']);
Router::post('/users/add', 'UserController@insert', ['AuthOnly', 'ValidateCsrf']);
Router::get('/users/edit/{id}', 'UserController@edit', ['AuthOnly']);
Router::post('/users/edit/{id}', 'UserController@update', ['AuthOnly', 'ValidateCsrf']);
Router::get('/users/delete/{id}', 'UserController@delete', ['AuthOnly']);

Router::get('/levels', 'LevelController@index', ['AuthOnly']);
Router::get('/levels/add', 'LevelController@add', ['AuthOnly']);
Router::post('/levels/add', 'LevelController@insert', ['AuthOnly', 'ValidateCsrf']);
Router::get('/levels/edit/{id}', 'LevelController@edit', ['AuthOnly']);
Router::post('/levels/edit/{id}', 'LevelController@update', ['AuthOnly', 'ValidateCsrf']);
Router::get('/levels/delete/{id}', 'LevelController@delete', ['AuthOnly']);

Router::get('/companies', 'CompanyController@index', ['AuthOnly']);
Router::get('/companies/add', 'CompanyController@add', ['AuthOnly']);
Router::post('/companies/add', 'CompanyController@insert', ['AuthOnly', 'ValidateCsrf']);
Router::get('/companies/edit/{id}', 'CompanyController@edit', ['AuthOnly']);
Router::post('/companies/edit/{id}', 'CompanyController@update', ['AuthOnly', 'ValidateCsrf']);
Router::get('/companies/delete/{id}', 'CompanyController@delete', ['AuthOnly']);

Router::dispatch();
