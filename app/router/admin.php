<?php

use USDBuySell\App\Controller\AdminController;
use USDBuySell\App\Controller\DashboardController;
use USDBuySell\App\Controller\UsersController;
use USDBuySell\App\Middleware\AuthMiddleware;

$app->group('/admin', function () use ($app) {
    //Dashboard Route
    $app->get('', DashboardController::class . ':home');

    //Money Provider Routes
    $app->group('/money-provider', function () use ($app) {
        $app->get('', AdminController::class . ':getMoneyProvider');
        $app->post('/create', AdminController::class . ':moneyProviderCreateProcess');
        $app->get('/delete/{providerUUID}', AdminController::class . ':moneyProviderDelete');
        $app->post('/update', AdminController::class . ':moneyProviderUpdateProcess');
    });

    //Exchange Request Route
    $app->group('/exchange-request', function () use ($app) {
        $app->get('', AdminController::class . ':getExchangeRequest');
    });

    //User Routes
    $app->group('/users', function () use ($app) {
        $app->get('', UsersController::class . ':getUsers');
        $app->post('/create', UsersController::class . ':userCreateProcess');
        $app->post('/update', UsersController::class . ':userUpdate');
        $app->get('/profile/{userUUID}', UsersController::class . ':getProfile');
        $app->post('/profile', UsersController::class . ':ProfileUpdate');
        $app->get('/delete/{userUUID}', UsersController::class . ':userDelete');
    });

    //Admin Profile Routes
    $app->group('/profile', function () use ($app) {
        $app->get('', UsersController::class . ':getAdminProfile');
        $app->post('/update', UsersController::class . ':adminProfileUpdate');
        $app->post('/change-password/{adminUUID}', UsersController::class . ':changePassword');
        $app->post('/upload-picture/{adminUUID}', UsersController::class . ':uploadPicture');
    });
})->add(new AuthMiddleware($app->getContainer()));