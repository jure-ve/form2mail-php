<?php
namespace Form2Mail\Services;

class ValidationService
{
    private $requiredFields = ['to', 'subject', 'body'];

    public function validate(array $data): array
    {
        $errors = [];

        // Verifica campos requeridos
        foreach ($this->requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $errors[] = "El campo '$field' es obligatorio";
            }
        }

        // Valida que 'to' sea un email válido
        if (isset($data['to']) && !empty(trim($data['to']))) {
            if (!filter_var($data['to'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "El campo 'to' debe ser una dirección de correo electrónico válida";
            }
        }

        if (!empty($errors)) {
            return ['isValid' => false, 'errors' => $errors];
        }

        return ['isValid' => true, 'errors' => []];
    }

    public function getRequiredFields(): array
    {
        return $this->requiredFields;
    }
}