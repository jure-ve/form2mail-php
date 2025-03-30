<?php
use Slim\Factory\AppFactory;
use Form2Mail\Controllers\MailController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno desde .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

try {
    $dotenv->required(['SMTP_HOST', 'SMTP_USERNAME', 'SMTP_PASSWORD', 'SMTP_PORT', 'API_KEY'])->notEmpty();
} catch (Exception $e) {
    die("Error: No se encontraron las variables de entorno requeridas. Verifica el archivo .env: " . $e->getMessage());
}

$app = AppFactory::create();

// Middleware para validar Content-Type y formato JSON
$app->add(function (Request $request, $handler): Response {
    // Verificar si el método es POST (solo aplicamos esta validación a /api/send)
    if ($request->getMethod() === 'POST') {
        $contentType = $request->getHeaderLine('Content-Type');

        // Verificar que el Content-Type sea application/json
        if (stripos($contentType, 'application/json') === false) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'error' => 'El encabezado Content-Type debe ser application/json'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Obtener el cuerpo crudo de la solicitud
        $body = $request->getBody()->getContents();

        // Verificar si el cuerpo está vacío o no es un JSON válido
        if (empty($body) || json_decode($body) === null && json_last_error() !== JSON_ERROR_NONE) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'error' => 'El cuerpo de la solicitud debe ser un JSON válido'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Restaurar el cuerpo para que el siguiente middleware lo use
        $request->getBody()->rewind();
    }

    return $handler->handle($request);
});

$app->addBodyParsingMiddleware();

// Middleware de autenticación API
$app->add(function (Request $request, $handler): Response {
    $apiKey = $request->getHeaderLine('X-API-KEY');
    $validKey = $_ENV['API_KEY'];
    if ($apiKey !== $validKey) {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode(['error' => 'No autorizado']));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }
    return $handler->handle($request);
});

// Middleware para CORS
$app->add(function (Request $request, $handler): Response {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-API-KEY, Content-Type')
        ->withHeader('Access-Control-Allow-Methods', 'POST');
});

// Ruta principal
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode([
        'message' => 'Bienvenido a Form2Mail API. Usa POST /api/send para enviar correos.',
        'documentation' => 'Ver README.md para más detalles.'
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});
// Ruta envío de correo
$app->post('/api/send', [MailController::class, 'sendEmail']);

// Manejo de errores global
$app->addErrorMiddleware(true, true, true);

$app->run();