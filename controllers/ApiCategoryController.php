<?php

class ApiCategoryController
{
    private CategoryModel $categories;

    public function __construct(PDO $connection)
    {
        $this->categories = new CategoryModel($connection);
    }

    public function index(): void
    {
        $this->json([
            'data' => $this->categories->findByUser(1),
        ]);
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
