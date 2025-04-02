<?php
namespace Form2Mail\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware
{
    public function __invoke(Request $request, $handler): Response
    {
        $apiKey = $request->getHeaderLine('X-API-KEY');
        $validKey = $_ENV['API_KEY'];
        if ($apiKey !== $validKey) {
            $response = new SlimResponse();
            $response->getBody()->write(json_encode(['error' => 'No autorizado']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        return $handler->handle($request);
    }
}