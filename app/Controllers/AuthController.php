<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Service\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

class AuthController extends BaseController
{
    public function __construct(
        Twig $view,
        private AuthService $authService,
        private LoggerInterface $logger,
    ) {
        parent::__construct($view);
    }

    public function showRegister(Request $request, Response $response): Response
    {
        // TODO: you also have a logger service that you can inject and use anywhere; file is var/app.log
        $this->logger->info('Register page requested');

        return $this->render($response, 'auth/register.twig');
    }

    public function register(Request $request, Response $response): Response
    {
        // TODO: call corresponding service to perform user registration
        $data = $request->getParsedBody();
        $username = $data['username'];
        $password = $data['password'];

        try {
            $this->authService->register($username,$password);
            return $response->withHeader('Location', '/login')->withStatus(302);
        } catch(\RuntimeException $e) {
            $message = $e->getMessage();
            if(str_contains($message, 'Username')){
                $errors['username'] = $message;
            }
            if(str_contains($message, 'Password')){
                $errors['password'] = $message;
            }
            return $this->view->render($response, '/auth/register.twig', [
            'errors' => $errors
        ]);
        } 

    }

    public function showLogin(Request $request, Response $response): Response
    {
        return $this->render($response, 'auth/login.twig');
    }

    public function login(Request $request, Response $response): Response
    {
        // TODO: call corresponding service to perform user login, handle login failures
        $data = $request->getParsedBody();
        $username = $data['username'];
        $password = $data['password'];

        $errors=[];
        try {
            $this->authService->attempt($username,$password);
            return $response->withHeader('Location', '/')->withStatus(302);
        } catch(\RuntimeException $e) {
            $message = $e->getMessage();
            if(str_contains($message, 'account')){
                $errors['username'] = $message;
            }
            if(str_contains($message, 'password')){
                $errors['password'] = $message;
            }
            
            return $this->view->render($response, '/auth/login.twig', [
            'errors' => $errors
        ]);
        } 
    }

    public function logout(Request $request, Response $response): Response
    {
        // TODO: handle logout by clearing session data and destroying session
        // var_dump($_SESSION);
        $_SESSION = [];
        session_destroy();
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}
