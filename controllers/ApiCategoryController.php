<?php

class ApiCategoryController
{
    private CategoryModel $categories;
    private AuthMiddleware $auth;

    public function __construct(PDO $connection, AuthMiddleware $auth)
    {
        $this->categories = new CategoryModel($connection);
        $this->auth = $auth;
    }

    public function index(): void
    {
        $user = $this->auth->requireUser();

        if ($user === null) {
            return;
        }

        $this->json([
            'data' => $this->categories->findByUser((int) $user['id']),
        ]);
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
