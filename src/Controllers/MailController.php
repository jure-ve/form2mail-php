<?php
namespace Form2Mail\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Form2Mail\Config\MailConfig;
use PHPMailer\PHPMailer\Exception;

class MailController {
    public function sendEmail(Request $request, Response $response): Response {
        $data = $request->getParsedBody();

        // Validación de campos requeridos
        $errors = $this->validateInput($data);
        if (!empty($errors)) {
            $response->getBody()->write(json_encode(['errors' => $errors]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $mail = MailConfig::getMailer();
            $mail->setFrom('no-reply@tudominio.com', 'Form2Mail API');
            $mail->addAddress($this->sanitizeInput($data['to']));
            $mail->Subject = $this->sanitizeInput($data['subject'] ?? 'Notificación del sistema');
            $mail->isHTML(false); // O true si planeas permitir HTML
            $mail->Body = $this->sanitizeInput($data['body']);

            if (!$mail->send()) {
                throw new Exception('Error al enviar el correo: ' . $mail->ErrorInfo);
            }

            $response->getBody()->write(json_encode(['status' => 'success', 'message' => 'Correo enviado correctamente']));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            error_log("Error en MailController: " . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => 'Error interno al enviar el correo']));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    private function validateInput(array $data): array {
        $errors = [];
        if (empty($data['to']) || !filter_var($data['to'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El campo "to" es obligatorio y debe ser un email válido';
        }
        if (empty($data['body'])) {
            $errors[] = 'El campo "body" es obligatorio';
        }
        return $errors;
    }

    private function sanitizeInput(string $input): string {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}