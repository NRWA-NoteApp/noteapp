<?php

class ApiNoteController
{
    private const NOTE_NOT_FOUND_MESSAGE = 'Bilješka nije pronađena.';

    private NoteModel $notes;

    public function __construct(PDO $connection)
    {
        $this->notes = new NoteModel($connection);
    }

    public function index(): void
    {
        $this->json([
            'data' => $this->notes->findAll(),
        ]);
    }

    public function show(array $params): void
    {
        $note = $this->notes->findById((int) $params['id']);

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
        $data = $this->buildNoteData($this->readJsonBody());

        if ($data === null) {
            $this->json([
                'error' => 'Pošaljite ispravan JSON s poljima naslov, sadrzaj i kategorija_id.',
            ], 400);
            return;
        }

        $noteId = $this->notes->create([
            'naslov' => $data['naslov'],
            'sadrzaj' => $data['sadrzaj'],
            'korisnik_id' => 1,
            'kategorija_id' => $data['kategorija_id'] ?? null,
        ]);

        $this->json([
            'data' => $this->notes->findById($noteId),
        ], 201);
    }

    public function update(array $params): void
    {
        $noteId = (int) $params['id'];

        if ($this->notes->findById($noteId) === null) {
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

        $this->notes->update($noteId, [
            'naslov' => $data['naslov'],
            'sadrzaj' => $data['sadrzaj'],
            'kategorija_id' => $data['kategorija_id'] ?? null,
        ]);

        $this->json([
            'data' => $this->notes->findById($noteId),
        ]);
    }

    public function destroy(array $params): void
    {
        $noteId = (int) $params['id'];

        if ($this->notes->findById($noteId) === null) {
            $this->json([
                'error' => self::NOTE_NOT_FOUND_MESSAGE,
            ], 404);
            return;
        }

        $this->notes->delete($noteId);

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

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
