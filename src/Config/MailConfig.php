<?php
namespace Form2Mail\Config;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailConfig {
    public static function getMailer(): PHPMailer {
        $mail = new PHPMailer(true);
        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USERNAME'];
            $mail->Password = $_ENV['SMTP_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int) ($_ENV['SMTP_PORT'] ?: 587);
            $mail->CharSet = 'UTF-8';
            $mail->SMTPDebug = ($_ENV['APP_ENV'] === 'dev') ? SMTP::DEBUG_SERVER : 0;
            
            // Opciones de seguridad adicionales
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => false,
                ],
            ];

            return $mail;
        } catch (Exception $e) {
            error_log("Error al configurar PHPMailer: " . $e->getMessage());
            throw $e;
        }
    }
}