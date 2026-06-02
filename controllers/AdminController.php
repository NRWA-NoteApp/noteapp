<?php

class AdminController
{
    private AuthMiddleware $auth;
    private UserModel $users;
    private NoteModel $notes;

    public function __construct(PDO $connection, AuthMiddleware $auth)
    {
        $this->auth = $auth;
        $this->users = new UserModel($connection);
        $this->notes = new NoteModel($connection);
    }

    public function users(): void
    {
        if ($this->auth->requireRole('admin') === null) {
            return;
        }

        $this->json([
            'data' => $this->users->findAllWithStats(),
        ]);
    }

    public function notes(): void
    {
        if ($this->auth->requireRole('admin') === null) {
            return;
        }

        $this->json([
            'data' => $this->notes->findAll(),
        ]);
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
