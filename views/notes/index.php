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

        <section class="auth-grid" aria-label="Autentikacija">
            <form class="auth-panel" data-login-form>
                <h2>Prijava</h2>
                <label for="login-email">Email</label>
                <input id="login-email" name="email" type="email" value="ana.horvat@example.com" required>

                <label for="login-password">Lozinka</label>
                <input id="login-password" name="lozinka" type="password" value="password" required>

                <button class="button" type="submit">Prijavi se</button>
            </form>

            <form class="auth-panel" data-register-form>
                <h2>Registracija</h2>
                <label for="register-name">Ime</label>
                <input id="register-name" name="ime" type="text" required>

                <label for="register-email">Email</label>
                <input id="register-email" name="email" type="email" required>

                <label for="register-password">Lozinka</label>
                <input id="register-password" name="lozinka" type="password" required>

                <button class="button" type="submit">Registriraj se</button>
            </form>
        </section>

        <section class="auth-status" data-auth-status hidden>
            <p data-auth-message></p>
            <button class="button secondary" type="button" data-logout>Odjava</button>
        </section>

        <p class="api-status" data-notes-status>Učitavanje bilješki preko API-ja...</p>

        <section class="empty-state" data-notes-empty <?= empty($notes) ? '' : 'hidden' ?>>
            <h2>Nema bilješki za prikaz.</h2>
            <p data-empty-message>Prijavite se kako biste učitali svoje bilješke.</p>
        </section>

        <section class="note-grid" data-notes-list aria-label="Popis bilješki" <?= empty($notes) ? 'hidden' : '' ?>>
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

        <section class="admin-panel" data-admin-panel hidden>
            <div class="page-header">
                <div>
                    <p class="eyebrow">Admin</p>
                    <h2>Pregled sustava</h2>
                </div>
            </div>
            <div class="admin-grid">
                <article>
                    <h3>Korisnici</h3>
                    <div data-admin-users></div>
                </article>
                <article>
                    <h3>Bilješke</h3>
                    <div data-admin-notes></div>
                </article>
            </div>
        </section>
    </main>

    <script src="/notes.js" defer></script>
</body>
</html>
