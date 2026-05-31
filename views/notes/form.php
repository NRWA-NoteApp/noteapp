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

            <form method="post" action="<?= $action ?>" class="note-form">
                <input type="hidden" name="korisnik_id" value="<?= $note['korisnik_id'] ?>">

                <label for="naslov">Naslov</label>
                <input
                    id="naslov"
                    name="naslov"
                    type="text"
                    value="<?= $note['naslov'] ?>"
                    required
                >

                <label for="kategorija_id">Kategorija</label>
                <select id="kategorija_id" name="kategorija_id">
                    <option value="">Bez kategorije</option>
                    <?php foreach ($categories as $category): ?>
                        <option
                            value="<?= $category['id'] ?>"
                            <?= (string) $category['id'] === (string) $note['kategorija_id'] ? 'selected' : '' ?>
                        >
                            <?= $category['naziv'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="sadrzaj">Sadržaj</label>
                <textarea id="sadrzaj" name="sadrzaj" rows="12" required><?= $note['sadrzaj'] ?></textarea>

                <button class="button" type="submit"><?= $buttonText ?></button>
            </form>
        </section>
    </main>
</body>
</html>
