<?php
use Dotenv\Dotenv;
use Form2Mail\Controllers\MailController;
use Form2Mail\Controllers\RootController;
use Form2Mail\Middleware\JsonValidationMiddleware;
use Form2Mail\Middleware\AuthMiddleware;
use Form2Mail\Services\MailService;
use Form2Mail\Services\ValidationService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno desde .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

try {
    $dotenv->required(['SMTP_HOST', 'SMTP_USERNAME', 'SMTP_PASSWORD', 'SMTP_PORT', 'API_KEY'])->notEmpty();
} catch (Exception $e) {
    die("Error: No se encontraron las variables de entorno requeridas. Verifica el archivo .env: " . $e->getMessage());
}

// Crear el contenedor manualmente
$container = new Container();

// Configurar las dependencias en el contenedor
$container->set('MailService', function () {
    return new MailService();
});
$container->set('ValidationService', function () {
    return new ValidationService();
});
$container->set(MailController::class, function ($container) {
    return new MailController(
        $container->get('MailService'),
        $container->get('ValidationService')
    );
});

// AppFactory
AppFactory::setContainer($container);
$app = AppFactory::create();

// Configura middlewares
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorMiddleware->setErrorHandler(
    HttpNotFoundException::class,
    function (Request $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails) use ($app) {
        $response = $app->getResponseFactory()->createResponse();
        $response->getBody()->write(json_encode(['error' => 'Ruta no encontrada']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }
);

$errorMiddleware->setErrorHandler(
    HttpMethodNotAllowedException::class,
    function (Request $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails) use ($app) {
        $response = $app->getResponseFactory()->createResponse();
        $response->getBody()->write(json_encode(['error' => 'Método no permitido']));
        return $response->withStatus(405)->withHeader('Content-Type', 'application/json');
    }
);

$app->add(JsonValidationMiddleware::class);
$app->add(AuthMiddleware::class);
$app->addBodyParsingMiddleware();

// Middleware para CORS
$app->add(function (Request $request, $handler): Response {
    $response = $handler->handle($request);
    $allowedOrigins = explode(',', $_ENV['CORS_ALLOWED_ORIGINS']);
    $origin = $request->getHeaderLine('Origin');

    if (in_array($origin, $allowedOrigins)) { 
        $response = $response->withHeader('Access-Control-Allow-Origin', $origin);
    } else {
        $response = $response->withHeader('Access-Control-Allow-Origin', '');
    }

    return $response
        ->withHeader('Access-Control-Allow-Headers', 'X-API-KEY, Content-Type')
        ->withHeader('Access-Control-Allow-Methods', 'POST');
});

// Rutas
$app->get('/', [RootController::class, 'index']);
$app->post('/api/send', [MailController::class, 'sendEmail']);

$app->run();