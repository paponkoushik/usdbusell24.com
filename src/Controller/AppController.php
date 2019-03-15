<?php

namespace USDBuySell\App\Controller;

use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use USDBuySell\App\Model\ModelLoader;

/**
 * Class AppController
 * @package USDBuySell\App\Controller
 */
class AppController
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var $view
     */
    protected $view;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var $homeUrl
     */
    public $homeUrl;

    /**
     * @var $config
     */
    public $config;

    /**
     * AppController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return LoggerInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getLogger()
    {
        return $this->container->get('logger');
    }

    /**
     * @return \Slim\Views\Twig
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getView()
    {
        return $this->container->get('view');
    }

    /**
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getSettings()
    {
        return $this->container->get('settings');
    }

    /**
     * @return \Slim\Flash\Messages
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getFlash()
    {
        return $this->container->get('flash');
    }

}