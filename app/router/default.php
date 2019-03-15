<?php

use USDBuySell\App\Controller\AuthController;
use USDBuySell\App\Controller\DefaultController;
use USDBuySell\App\Middleware\AuthMiddleware;


//Default Route
$app->get('/', DefaultController::class . ':home');
$app->get('/faq', DefaultController::class . ':faqView');
$app->get('/about', DefaultController::class . ':aboutView');
$app->get('/track', DefaultController::class . ':paymentProofView');
$app->get('/contact', DefaultController::class . ':contactView');
$app->get('/terms-and-conditions', DefaultController::class . ':termAndConditionView');
$app->get('/testimonials', DefaultController::class . ':testimonialsView');
$app->get('/api/public-providers', DefaultController::class . ':providersView');
$app->get('/api/public-providers/{providerName}/sell-rate', DefaultController::class . ':providersSellRate');

//Dashboard
$app->get('/dashboard', DefaultController::class . ':dashboardView')
    ->add(new AuthMiddleware($app->getContainer()));
$app->post('/dashboard/update/{userUUID}', DefaultController::class . ':userProfileUpdate')
    ->add(new AuthMiddleware($app->getContainer()));
$app->post('/dashboard/upload-picture/{userUUID}', DefaultController::class . ':uploadPicture')
    ->add(new AuthMiddleware($app->getContainer()));
//Login
$app->get('/login', AuthController::class . ':loginView');
$app->post('/login', AuthController::class . ':loginProcess');

//Signup
$app->get('/signup', AuthController::class . ':signUpView');
$app->post('/signup', AuthController::class . ':signUpProcess');

// Forgot Password Routes
$app->group('/forgot-password', function () use ($app) {
    $app->get('', AuthController::class . ':forgotPasswordView');
});

//Logout
$app->get('/logout', AuthController::class . ':logoutProcess');

