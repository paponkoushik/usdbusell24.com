<?php

namespace USDBuySell\App\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;


class AuthMiddleware
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * AuthMiddleware constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $this->container->get('logger');
        $this->flash = $this->container->get('flash');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $sessionAuth = $_SESSION;

        if(!empty($sessionAuth['auth'])){
            $request = $request->withAttribute("authUser", $_SESSION['auth']);
            return $next($request, $response);
        }

        $this->flash->addMessage("error", "You need to login first");
        return $response->withRedirect("/login");
    }
}