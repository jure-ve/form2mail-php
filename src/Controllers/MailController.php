<?php
namespace Form2Mail\Controllers;

use Form2Mail\Services\MailService;
use Form2Mail\Services\ValidationService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as SlimResponse;

class MailController
{
    private $mailService;
    private $validationService;

    public function __construct(MailService $mailService, ValidationService $validationService)
    {
        $this->mailService = $mailService;
        $this->validationService = $validationService;
    }

    public function sendEmail(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        // Valida los datos
        $validationResult = $this->validationService->validate($data);
        if (!$validationResult['isValid']) {
            $response = new SlimResponse();
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => implode(', ', $validationResult['errors'])
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $success = $this->mailService->sendEmail($data);
            if ($success) {
                $response->getBody()->write(json_encode([
                    'status' => 'success',
                    'message' => 'Correo enviado correctamente'
                ]));
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            }

            throw new \Exception('No se pudo enviar el correo');
        } catch (\Exception $e) {
            $response = new SlimResponse();
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Error al enviar el correo: ' . $e->getMessage()
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}