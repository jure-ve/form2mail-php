<?php
namespace Form2Mail\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

class JsonValidationMiddleware
{
    public function __invoke(Request $request, $handler): Response
    {
        if ($request->getMethod() === 'POST') {
            $contentType = $request->getHeaderLine('Content-Type');
            if (stripos($contentType, 'application/json') === false) {
                $response = new SlimResponse();
                $response->getBody()->write(json_encode(['error' => 'El encabezado Content-Type debe ser application/json']));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            $body = $request->getBody()->getContents();
            if (empty($body) || json_decode($body) === null && json_last_error() !== JSON_ERROR_NONE) {
                $response = new SlimResponse();
                $response->getBody()->write(json_encode(['error' => 'El cuerpo de la solicitud debe ser un JSON válido']));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
            $request->getBody()->rewind();
        }
        return $handler->handle($request);
    }
}