<?php
namespace Form2Mail\Services;

use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configureMailer();
    }

    private function configureMailer(): void
    {
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['SMTP_HOST'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['SMTP_USERNAME'];
        $this->mailer->Password = $_ENV['SMTP_PASSWORD'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $_ENV['SMTP_PORT'];
    }

    public function sendEmail(array $data): bool
    {
        // Configura el remitente y destinatario
        $this->mailer->setFrom($_ENV['SMTP_USERNAME']); // Usuario SMTP como remitente por defecto
        $this->mailer->addAddress($data['to']);
        $this->mailer->Subject = $data['subject'];

        // Construye el cuerpo como una lista de campo: valor
        $bodyLines = [];
        foreach ($data as $key => $value) {
            $bodyLines[] = "$key: $value";
        }
        $this->mailer->Body = implode("\n", $bodyLines);

        return $this->mailer->send();
    }
}