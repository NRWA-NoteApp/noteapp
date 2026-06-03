<!doctype html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Security::escape($note['naslov'] ?? '') ?> - NoteApp</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <main class="container narrow">
        <nav class="breadcrumb">
            <a href="/notes">Sve bilješke</a>
        </nav>

        <article class="note-detail">
            <div class="note-meta">
                <?php if (!empty($note['kategorija_naziv'])): ?>
                    <span class="category" style="--category-color: <?= Security::escape(Security::cssColor($note['kategorija_boja'] ?? '')) ?>;">
                        <?= Security::escape($note['kategorija_naziv']) ?>
                    </span>
                <?php else: ?>
                    <span class="category muted">Bez kategorije</span>
                <?php endif; ?>
                <span>Autor: <?= Security::escape($note['korisnik_ime'] ?? '') ?></span>
            </div>

            <h1><?= Security::escape($note['naslov'] ?? '') ?></h1>
            <div class="content">
                <?= nl2br(Security::escape($note['sadrzaj'] ?? '')) ?>
            </div>

            <footer class="note-footer">
                <span>Izrađeno: <?= Security::escape($note['datum_izrade'] ?? '') ?></span>
                <span>Izmijenjeno: <?= Security::escape($note['datum_izmjene'] ?? '') ?></span>
            </footer>

            <a class="button secondary" href="/notes/<?= (int) $note['id'] ?>/edit">Uredi bilješku</a>
        </article>
    </main>
</body>
</html>
