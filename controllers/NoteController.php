<?php

class NoteController
{
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $noteId = $this->notes->create([
                'naslov' => $_POST['naslov'] ?? '',
                'sadrzaj' => $_POST['sadrzaj'] ?? '',
                'korisnik_id' => $_POST['korisnik_id'] ?? $defaultUserId,
                'kategorija_id' => $_POST['kategorija_id'] ?? null,
            ]);

            header("Location: /notes/{$noteId}");
            return;
        }

        $this->render('notes/form', [
            'action' => '/notes',
            'buttonText' => 'Spremi bilješku',
            'categories' => $this->categories->findByUser($defaultUserId),
            'note' => [
                'naslov' => '',
                'sadrzaj' => '',
                'korisnik_id' => $defaultUserId,
                'kategorija_id' => '',
            ],
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
            $this->notes->update((int) $params['id'], [
                'naslov' => $_POST['naslov'] ?? '',
                'sadrzaj' => $_POST['sadrzaj'] ?? '',
                'kategorija_id' => $_POST['kategorija_id'] ?? null,
            ]);

            header("Location: /notes/{$params['id']}");
            return;
        }

        $this->render('notes/form', [
            'action' => "/notes/{$params['id']}",
            'buttonText' => 'Ažuriraj bilješku',
            'categories' => $this->categories->findByUser((int) $note['korisnik_id']),
            'note' => $note,
        ]);
    }

    private function render(string $template, array $data = []): void
    {
        extract($data);

        require_once __DIR__ . "/../views/{$template}.php";
    }
}
