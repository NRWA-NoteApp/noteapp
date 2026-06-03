<?php

class ApiNoteController
{
    private const NOTE_NOT_FOUND_MESSAGE = 'Bilješka nije pronađena.';

    private NoteModel $notes;
    private CategoryModel $categories;
    private AuthMiddleware $auth;

    public function __construct(PDO $connection, AuthMiddleware $auth)
    {
        $this->notes = new NoteModel($connection);
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
            'data' => $this->notes->findByUser((int) $user['id']),
        ]);
    }

    public function show(array $params): void
    {
        $user = $this->auth->requireUser();

        if ($user === null) {
            return;
        }

        $note = $this->notes->findByIdForUser((int) $params['id'], (int) $user['id']);

        if ($note === null) {
            $this->json([
                'error' => self::NOTE_NOT_FOUND_MESSAGE,
            ], 404);
            return;
        }

        $this->json([
            'data' => $note,
        ]);
    }

    public function store(): void
    {
        $user = $this->auth->requireUser();

        if ($user === null) {
            return;
        }

        $data = $this->buildNoteData($this->readJsonBody());

        if ($data === null) {
            $this->json([
                'error' => 'Pošaljite ispravan JSON s poljima naslov, sadrzaj i kategorija_id.',
            ], 400);
            return;
        }

        if (!$this->categoryBelongsToUser($data['kategorija_id'], (int) $user['id'])) {
            $this->json([
                'error' => 'Odabrana kategorija ne pripada prijavljenom korisniku.',
            ], 400);
            return;
        }

        $noteId = $this->notes->create([
            'naslov' => $data['naslov'],
            'sadrzaj' => $data['sadrzaj'],
            'korisnik_id' => (int) $user['id'],
            'kategorija_id' => $data['kategorija_id'] ?? null,
        ]);

        $this->json([
            'data' => $this->notes->findByIdForUser($noteId, (int) $user['id']),
        ], 201);
    }

    public function update(array $params): void
    {
        $user = $this->auth->requireUser();

        if ($user === null) {
            return;
        }

        $noteId = (int) $params['id'];

        if ($this->notes->findByIdForUser($noteId, (int) $user['id']) === null) {
            $this->json([
                'error' => self::NOTE_NOT_FOUND_MESSAGE,
            ], 404);
            return;
        }

        $data = $this->buildNoteData($this->readJsonBody());

        if ($data === null) {
            $this->json([
                'error' => 'Pošaljite ispravan JSON s poljima naslov, sadrzaj i kategorija_id.',
            ], 400);
            return;
        }

        if (!$this->categoryBelongsToUser($data['kategorija_id'], (int) $user['id'])) {
            $this->json([
                'error' => 'Odabrana kategorija ne pripada prijavljenom korisniku.',
            ], 400);
            return;
        }

        $this->notes->updateForUser($noteId, (int) $user['id'], [
            'naslov' => $data['naslov'],
            'sadrzaj' => $data['sadrzaj'],
            'kategorija_id' => $data['kategorija_id'] ?? null,
        ]);

        $this->json([
            'data' => $this->notes->findByIdForUser($noteId, (int) $user['id']),
        ]);
    }

    public function destroy(array $params): void
    {
        $user = $this->auth->requireUser();

        if ($user === null) {
            return;
        }

        $noteId = (int) $params['id'];

        if ($this->notes->findByIdForUser($noteId, (int) $user['id']) === null) {
            $this->json([
                'error' => self::NOTE_NOT_FOUND_MESSAGE,
            ], 404);
            return;
        }

        $this->notes->deleteForUser($noteId, (int) $user['id']);

        $this->json([
            'message' => 'Bilješka je obrisana.',
        ]);
    }

    private function readJsonBody(): ?array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);

        return is_array($data) ? $data : null;
    }

    private function buildNoteData(?array $data): ?array
    {
        if (!$this->hasValidBody($data)) {
            return null;
        }

        $kategorijaId = $this->normalizeCategoryId($data['kategorija_id']);

        if ($kategorijaId === false) {
            return null;
        }

        return [
            'naslov' => (string) $data['naslov'],
            'sadrzaj' => (string) $data['sadrzaj'],
            'kategorija_id' => $kategorijaId,
        ];
    }

    private function hasValidBody(?array $data): bool
    {
        return $data !== null
            && isset($data['naslov'], $data['sadrzaj'])
            && array_key_exists('kategorija_id', $data)
            && is_scalar($data['naslov'])
            && is_scalar($data['sadrzaj'])
            && trim((string) $data['naslov']) !== ''
            && trim((string) $data['sadrzaj']) !== '';
    }

    private function normalizeCategoryId(mixed $kategorijaId): int|false|null
    {
        if ($kategorijaId === null || $kategorijaId === '') {
            return null;
        }

        if (!ctype_digit((string) $kategorijaId) || (int) $kategorijaId < 1) {
            return false;
        }

        return (int) $kategorijaId;
    }

    private function categoryBelongsToUser(?int $categoryId, int $userId): bool
    {
        return $categoryId === null || $this->categories->existsForUser($categoryId, $userId);
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
