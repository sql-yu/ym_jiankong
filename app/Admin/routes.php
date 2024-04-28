<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('package', 'YmPackageController');
    $router->resource('developers', 'YmDeveloperController');
    $router->resource('account', 'YmAccountController');
    $router->resource('receive_account', 'YmReceiveAccountController');
});
