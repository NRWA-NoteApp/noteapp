<?php

require_once __DIR__ . '/../Router.php';
require_once __DIR__ . '/../services/Security.php';
require_once __DIR__ . '/../services/JwtService.php';
require_once __DIR__ . '/../models/NoteModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/NoteController.php';
require_once __DIR__ . '/../controllers/ApiNoteController.php';
require_once __DIR__ . '/../controllers/ApiCategoryController.php';
require_once __DIR__ . '/../controllers/ApiAuthController.php';
require_once __DIR__ . '/../controllers/AdminController.php';

header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self'");
header('X-Content-Type-Options: nosniff');

try {
    $connection = require_once __DIR__ . '/../config/database.php';
    $jwtConfig = require_once __DIR__ . '/../config/jwt.php';
    $authMiddleware = new AuthMiddleware($connection, $jwtConfig);

    $noteController = new NoteController($connection, $authMiddleware);
    $apiNoteController = new ApiNoteController($connection, $authMiddleware);
    $apiCategoryController = new ApiCategoryController($connection, $authMiddleware);
    $apiAuthController = new ApiAuthController($connection, $jwtConfig);
    $adminController = new AdminController($connection, $authMiddleware);
    $router = new Router();
    $apiNoteRoute = '/api/notes/{id}';

    $router->post('/api/auth/register', [$apiAuthController, 'register']);
    $router->post('/api/auth/login', [$apiAuthController, 'login']);
    $router->get('/api/notes', [$apiNoteController, 'index']);
    $router->get($apiNoteRoute, [$apiNoteController, 'show']);
    $router->post('/api/notes', [$apiNoteController, 'store']);
    $router->put($apiNoteRoute, [$apiNoteController, 'update']);
    $router->delete($apiNoteRoute, [$apiNoteController, 'destroy']);
    $router->get('/api/categories', [$apiCategoryController, 'index']);
    $router->get('/api/admin/users', [$adminController, 'users']);
    $router->get('/api/admin/notes', [$adminController, 'notes']);

    $router->get('/', [$noteController, 'index']);
    $router->get('/notes', [$noteController, 'index']);
    $router->get('/notes/create', [$noteController, 'create']);
    $router->post('/notes', [$noteController, 'create']);
    $router->get('/notes/{id}', [$noteController, 'show']);
    $router->get('/notes/{id}/edit', [$noteController, 'edit']);
    $router->post('/notes/{id}', [$noteController, 'edit']);

    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (Throwable $exception) {
    http_response_code(500);

    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

    if (str_starts_with($path, '/api/')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'Aplikacija trenutno ne može obraditi API zahtjev.',
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    $message = 'Provjerite je li MySQL pokrenut i jesu li postavke baze u skladu s lokalnim okruženjem.';
    require_once __DIR__ . '/../views/error.php';
}
