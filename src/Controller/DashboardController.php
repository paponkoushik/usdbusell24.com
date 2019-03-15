<?php

namespace USDBuySell\App\Controller;


use Slim\Http\Request;
use Slim\Http\Response;

class DashboardController extends AppController
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function home(Request $request, Response $response, array $args = [])
    {
        if ($_SESSION['auth']->role != 'super-admin') {
            $this->getFlash()->addMessage('error', 'Sorry, Invalid Permission');
            return $response->withRedirect('/login');
        }

        return $this->getView()->render($response, 'admin/dashboard.twig', []);
    }
}