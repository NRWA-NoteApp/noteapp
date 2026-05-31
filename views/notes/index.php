<!doctype html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NoteApp - bilješke</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <main class="container">
        <header class="page-header">
            <div>
                <p class="eyebrow">NoteApp</p>
                <h1>Bilješke</h1>
            </div>
            <a class="button" href="/notes/create">Nova bilješka</a>
        </header>

        <?php if (empty($notes)): ?>
            <section class="empty-state">
                <h2>Nema bilješki za prikaz.</h2>
                <p>Uvezi testne podatke iz baze ili dodaj prvu bilješku kroz obrazac.</p>
            </section>
        <?php else: ?>
            <section class="note-grid" aria-label="Popis bilješki">
                <?php foreach ($notes as $note): ?>
                    <article class="note-card">
                        <div class="note-meta">
                            <?php if (!empty($note['kategorija_naziv'])): ?>
                                <span class="category" style="--category-color: <?= $note['kategorija_boja'] ?>;">
                                    <?= $note['kategorija_naziv'] ?>
                                </span>
                            <?php else: ?>
                                <span class="category muted">Bez kategorije</span>
                            <?php endif; ?>
                            <span><?= $note['korisnik_ime'] ?></span>
                        </div>
                        <h2><?= $note['naslov'] ?></h2>
                        <p><?= strlen($note['sadrzaj']) > 140 ? substr($note['sadrzaj'], 0, 140) . '...' : $note['sadrzaj'] ?></p>
                        <a href="/notes/<?= $note['id'] ?>">Otvori detalje</a>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
