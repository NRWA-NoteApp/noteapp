<?php

class ApiAuthController
{
    private UserModel $users;
    private JwtService $jwt;

    public function __construct(PDO $connection, array $jwtConfig)
    {
        $this->users = new UserModel($connection);
        $this->jwt = new JwtService($jwtConfig);
    }

    public function register(): void
    {
        $data = $this->readJsonBody();

        if (!$this->hasRegistrationBody($data)) {
            $this->json([
                'error' => 'Pošaljite ime, email i lozinku.',
            ], 400);
            return;
        }

        $email = trim((string) $data['email']);

        if ($this->users->findByEmail($email) !== null) {
            $this->json([
                'error' => 'Korisnik s tom email adresom već postoji.',
            ], 400);
            return;
        }

        $userId = $this->users->create([
            'ime' => trim((string) $data['ime']),
            'email' => $email,
            'lozinka_hash' => password_hash((string) $data['lozinka'], PASSWORD_BCRYPT),
            'uloga' => 'user',
        ]);
        $user = $this->users->findById($userId);

        $this->json([
            'data' => [
                'user' => $user,
                'token' => $this->createToken($user),
                'expires_in' => $this->jwt->getExpiresIn(),
            ],
        ], 201);
    }

    public function login(): void
    {
        $data = $this->readJsonBody();

        if (!$this->hasLoginBody($data)) {
            $this->json([
                'error' => 'Pošaljite email i lozinku.',
            ], 400);
            return;
        }

        $userWithPassword = $this->users->findByEmail(trim((string) $data['email']));

        if (
            $userWithPassword === null
            || !password_verify((string) $data['lozinka'], $userWithPassword['lozinka_hash'])
        ) {
            $this->json([
                'error' => 'Email ili lozinka nisu ispravni.',
            ], 401);
            return;
        }

        $user = $this->users->findById((int) $userWithPassword['id']);

        $this->json([
            'data' => [
                'user' => $user,
                'token' => $this->createToken($user),
                'expires_in' => $this->jwt->getExpiresIn(),
            ],
        ]);
    }

    private function createToken(array $user): string
    {
        return $this->jwt->encode([
            'user_id' => (int) $user['id'],
            'uloga' => (string) $user['uloga'],
        ]);
    }

    private function readJsonBody(): ?array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);

        return is_array($data) ? $data : null;
    }

    private function hasRegistrationBody(?array $data): bool
    {
        return $data !== null
            && isset($data['ime'], $data['email'], $data['lozinka'])
            && is_scalar($data['ime'])
            && is_scalar($data['email'])
            && is_scalar($data['lozinka'])
            && trim((string) $data['ime']) !== ''
            && filter_var(trim((string) $data['email']), FILTER_VALIDATE_EMAIL) !== false
            && trim((string) $data['lozinka']) !== '';
    }

    private function hasLoginBody(?array $data): bool
    {
        return $data !== null
            && isset($data['email'], $data['lozinka'])
            && is_scalar($data['email'])
            && is_scalar($data['lozinka'])
            && filter_var(trim((string) $data['email']), FILTER_VALIDATE_EMAIL) !== false
            && trim((string) $data['lozinka']) !== '';
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
