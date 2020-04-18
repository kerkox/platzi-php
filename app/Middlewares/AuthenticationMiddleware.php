<?php

namespace App\Middlewares;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if($request->getUri()->getPath() === '/admin'){
            $sessionUserId = $_SESSION['userId'] ?? null;
            if(!$sessionUserId) {
                return new RedirectResponse('/login');
//                return new EmptyResponse(401);
            }
        }

        return $handler->handle($request);

    }
}