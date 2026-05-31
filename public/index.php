<?php

require_once __DIR__ . '/../Router.php';
require_once __DIR__ . '/../models/NoteModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../controllers/NoteController.php';

try {
    $connection = require_once __DIR__ . '/../config/database.php';

    $noteController = new NoteController($connection);
    $router = new Router();

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
    $message = 'Provjerite je li MySQL pokrenut i jesu li postavke baze u skladu s lokalnim okruženjem.';
    require_once __DIR__ . '/../views/error.php';
}
