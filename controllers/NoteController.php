<?php

class NoteController
{
    private const FORM_TEMPLATE = 'notes/form';

    private NoteModel $notes;
    private CategoryModel $categories;

    public function __construct(PDO $connection)
    {
        $this->notes = new NoteModel($connection);
        $this->categories = new CategoryModel($connection);
    }

    public function index(): void
    {
        $this->render('notes/index', [
            'csrfToken' => Security::csrfToken(),
            'notes' => [],
        ]);
    }

    public function show(array $params): void
    {
        $note = $this->notes->findById((int) $params['id']);

        if ($note === null) {
            http_response_code(404);
            require_once __DIR__ . '/../views/404.php';
            return;
        }

        $this->render('notes/show', [
            'note' => $note,
        ]);
    }

    public function create(): void
    {
        $defaultUserId = 1;
        $note = [
            'naslov' => '',
            'sadrzaj' => '',
            'korisnik_id' => $defaultUserId,
            'kategorija_id' => '',
        ];
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $note = $this->noteFromPost($defaultUserId);

            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Sigurnosni token obrasca nije valjan. Pokušajte ponovno.';
            }

            $validation = $this->validateNoteInput($note, $defaultUserId);
            $errors = array_merge($errors, $validation['errors']);

            if ($errors === []) {
                $noteId = $this->notes->create($validation['data']);

                header("Location: /notes/{$noteId}");
                return;
            }
        }

        $this->render(self::FORM_TEMPLATE, [
            'action' => '/notes',
            'buttonText' => 'Spremi bilješku',
            'categories' => $this->categories->findByUser($defaultUserId),
            'csrfToken' => Security::csrfToken(),
            'errors' => $errors,
            'note' => $note,
        ]);
    }

    public function edit(array $params): void
    {
        $note = $this->notes->findById((int) $params['id']);

        if ($note === null) {
            http_response_code(404);
            require_once __DIR__ . '/../views/404.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $submittedNote = $this->noteFromPost((int) $note['korisnik_id']);
            $errors = [];

            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? null)) {
                $errors[] = 'Sigurnosni token obrasca nije valjan. Pokušajte ponovno.';
            }

            $validation = $this->validateNoteInput($submittedNote, (int) $note['korisnik_id']);
            $errors = array_merge($errors, $validation['errors']);

            if ($errors !== []) {
                $this->render(self::FORM_TEMPLATE, [
                    'action' => "/notes/{$params['id']}",
                    'buttonText' => 'Ažuriraj bilješku',
                    'categories' => $this->categories->findByUser((int) $note['korisnik_id']),
                    'csrfToken' => Security::csrfToken(),
                    'errors' => $errors,
                    'note' => $submittedNote,
                ]);
                return;
            }

            $this->notes->update((int) $params['id'], $validation['data']);

            header("Location: /notes/{$params['id']}");
            return;
        }

        $this->render(self::FORM_TEMPLATE, [
            'action' => "/notes/{$params['id']}",
            'buttonText' => 'Ažuriraj bilješku',
            'categories' => $this->categories->findByUser((int) $note['korisnik_id']),
            'csrfToken' => Security::csrfToken(),
            'errors' => [],
            'note' => $note,
        ]);
    }

    private function noteFromPost(int $userId): array
    {
        return [
            'naslov' => trim((string) ($_POST['naslov'] ?? '')),
            'sadrzaj' => trim((string) ($_POST['sadrzaj'] ?? '')),
            'korisnik_id' => $userId,
            'kategorija_id' => $_POST['kategorija_id'] ?? '',
        ];
    }

    private function validateNoteInput(array $note, int $userId): array
    {
        $errors = [];
        $naslov = trim((string) $note['naslov']);
        $sadrzaj = trim((string) $note['sadrzaj']);
        $kategorijaId = $this->normalizeCategoryId($note['kategorija_id']);

        if ($naslov === '') {
            $errors[] = 'Naslov je obavezan.';
        } elseif ($this->textLength($naslov) > 255) {
            $errors[] = 'Naslov smije imati najviše 255 znakova.';
        }

        if ($sadrzaj === '') {
            $errors[] = 'Sadržaj ne smije biti prazan.';
        }

        if ($kategorijaId === false) {
            $errors[] = 'Odaberite valjanu kategoriju.';
        } elseif ($kategorijaId !== null && !$this->categories->existsForUser($kategorijaId, $userId)) {
            $errors[] = 'Odabrana kategorija ne pripada korisniku bilješke.';
        }

        return [
            'errors' => $errors,
            'data' => [
                'naslov' => $naslov,
                'sadrzaj' => $sadrzaj,
                'korisnik_id' => $userId,
                'kategorija_id' => $kategorijaId,
            ],
        ];
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

    private function textLength(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }

    private function render(string $template, array $data = []): void
    {
        extract($data);

        require_once __DIR__ . "/../views/{$template}.php";
    }
}
