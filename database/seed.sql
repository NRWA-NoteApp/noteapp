SET NAMES utf8mb4;

INSERT INTO korisnici (id, ime, email, lozinka_hash, uloga) VALUES
    (1, 'Ana Horvat', 'ana.horvat@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'user'),
    (2, 'Marko Novak', 'marko.novak@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'admin');

INSERT INTO kategorije (id, naziv, boja, korisnik_id) VALUES
    (1, 'Fakultet', '#5d6b4d', 1),
    (2, 'Osobno', '#9a6b4f', 1),
    (3, 'Administracija', '#4f6b9a', 2);

INSERT INTO biljeske (id, naslov, sadrzaj, korisnik_id, kategorija_id) VALUES
    (
        1,
        'Plan učenja',
        'Ponoviti MVC arhitekturu, proći primjere ruta i pripremiti pitanja za vježbe.',
        1,
        1
    ),
    (
        2,
        'Ideje za projekt',
        'Dodati jasne kategorije, jednostavan prikaz bilješki i uredan obrazac za unos novog sadržaja.',
        1,
        2
    ),
    (
        3,
        'Administratorski pregled',
        'Zabilježiti koje korisnike i sadržaj sustav prikazuje administratoru u preglednom popisu.',
        2,
        3
    );
