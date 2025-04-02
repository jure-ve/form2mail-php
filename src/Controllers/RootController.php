<?php
namespace Form2Mail\Controllers;

use Form2Mail\Services\MailService;
use Form2Mail\Services\ValidationService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as SlimResponse;

class RootController
{
    public function index(Request $request, Response $response): Response
    {
        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'Bienvenido a Form2Mail API. Usa POST /api/send para enviar correos.',
            'documentation' => 'Ver README.md para más detalles.'
        ]));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }
}