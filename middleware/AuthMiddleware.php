<?php

class AuthMiddleware
{
    private JwtService $jwt;
    private UserModel $users;

    public function __construct(PDO $connection, array $jwtConfig)
    {
        $this->jwt = new JwtService($jwtConfig);
        $this->users = new UserModel($connection);
    }

    public function requireUser(): ?array
    {
        $token = $this->getRequestToken();
        $user = $this->authenticateToken($token);

        if ($token === null) {
            $this->json([
                'error' => 'Nedostaje Authorization Bearer token.',
            ], 401);
            return null;
        }

        if ($user === null) {
            $this->json([
                'error' => 'JWT token nije valjan ili je istekao.',
            ], 401);
            return null;
        }

        return $user;
    }

    public function currentUser(): ?array
    {
        return $this->authenticateToken($this->getRequestToken());
    }

    private function authenticateToken(?string $token): ?array
    {
        $user = null;

        if ($token !== null) {
            $payload = $this->jwt->decode($token);

            if (
                $payload !== null
                && isset($payload['user_id'], $payload['uloga'])
                && ctype_digit((string) $payload['user_id'])
            ) {
                $foundUser = $this->users->findById((int) $payload['user_id']);

                if ($foundUser !== null && $foundUser['uloga'] === $payload['uloga']) {
                    $user = $foundUser;
                }
            }
        }

        return $user;
    }

    public function requireRole(string $role): ?array
    {
        $user = $this->requireUser();

        if ($user === null) {
            return null;
        }

        if ($user['uloga'] !== $role) {
            $this->json([
                'error' => 'Nemate dopuštenje za traženi resurs.',
            ], 403);
            return null;
        }

        return $user;
    }

    private function getRequestToken(): ?string
    {
        return $this->getBearerToken() ?? $this->getCookieToken();
    }

    private function getBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

        if ($header === '' && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }

        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    private function getCookieToken(): ?string
    {
        $token = $_COOKIE['noteapp_jwt_token'] ?? null;

        return is_string($token) && $token !== '' ? $token : null;
    }

    private function json(array $payload, int $status): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
