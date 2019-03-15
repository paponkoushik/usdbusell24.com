<?php

use Monolog\Logger;
use Slim\Container;

$container = $app->getContainer();

/**
 * @param Container $container
 * @return \Slim\Views\Twig
 */
$container['view'] = function (\Slim\Container $container) {
    $view = new \Slim\Views\Twig(ROOT_DIR . DIRECTORY_SEPARATOR . "templates", [
        'debug' => true,
        'auto_reload' => true,
    ]);

    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));

    $twigEnvironment = $view->getEnvironment();
    $twigEnvironment->addGlobal("app_env", [
        'APP_MODE' => getenv("APP_MODE"),
        'SHORT_URL_DOMAIN' => getenv("SHORT_URL_DOMAIN"),
        'SHORT_URL_SSL' => getenv("SHORT_URL_SSL")
    ]);

    $twigEnvironment->addGlobal("session", $_SESSION);
    $twigEnvironment->addGlobal('message', $container->get('flash')->getMessages());

    $view->addExtension(new Twig_Extension_Debug());

    return $view;

};

/**
 * @param \Psr\Container\ContainerInterface $container
 * @return Logger
 * @throws Exception
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 */
$container['logger'] = function (\Psr\Container\ContainerInterface $container) {
    $logger = new Monolog\Logger(getenv("LOGGER_NAME"));

    if (getenv("LOGGER_HANDLER") === "file") {
        $fileHandler = new Monolog\Handler\StreamHandler(getenv("LOG_FILE"));
        $logger->pushHandler($fileHandler);
    } elseif (getenv("LOGGER_HANDLER") === "syslog") {
        $syslogHandler = new \Monolog\Handler\SyslogHandler(getenv("LOGGER_NAME"));
        $logger->pushHandler($syslogHandler);
    }

    return $logger;
};

/**
 * @param \Psr\Container\ContainerInterface $container
 * @return \Slim\Flash\Messages
 */
$container['flash'] = function (\Psr\Container\ContainerInterface $container) {
    return new Slim\Flash\Messages();
};




