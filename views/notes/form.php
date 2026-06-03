<!doctype html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Obrazac bilješke - NoteApp</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <main class="container narrow">
        <nav class="breadcrumb">
            <a href="/notes">Sve bilješke</a>
        </nav>

        <section class="form-panel">
            <h1>Bilješka</h1>

            <?php if (!empty($errors)): ?>
                <div class="form-errors" role="alert">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= Security::escape($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= Security::escape($action) ?>" class="note-form">
                <input type="hidden" name="csrf_token" value="<?= Security::escape($csrfToken) ?>">
                <input type="hidden" name="korisnik_id" value="<?= (int) $note['korisnik_id'] ?>">

                <label for="naslov">Naslov</label>
                <input
                    id="naslov"
                    name="naslov"
                    type="text"
                    value="<?= Security::escape($note['naslov'] ?? '') ?>"
                    maxlength="255"
                    required
                >

                <label for="kategorija_id">Kategorija</label>
                <select id="kategorija_id" name="kategorija_id">
                    <option value="">Bez kategorije</option>
                    <?php foreach ($categories as $category): ?>
                        <option
                            value="<?= (int) $category['id'] ?>"
                            <?= (string) $category['id'] === (string) $note['kategorija_id'] ? 'selected' : '' ?>
                        >
                            <?= Security::escape($category['naziv']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="sadrzaj">Sadržaj</label>
                <textarea id="sadrzaj" name="sadrzaj" rows="12" required><?= Security::escape($note['sadrzaj'] ?? '') ?></textarea>

                <button class="button" type="submit"><?= Security::escape($buttonText) ?></button>
            </form>
        </section>
    </main>
</body>
</html>
