# Changelog

Sve značajne promjene u ovom projektu dokumentirane su u ovoj datoteci.

Format temeljen na [Keep a Changelog](https://keepachangelog.com/hr/1.0.0/).

## [0.6.0] - 2026-05-30

### Dodano

- Finalna README dokumentacija s uputama za instalaciju, web rutama i svim API endpointima
- Primjeri JSON zahtjeva i odgovora za auth, bilješke, kategorije i admin API rute
- MVC dijagram arhitekture u `docs/diagrams/architecture.md`

### Promijenjeno

- `/api/categories` sada vraća kategorije prijavljenog korisnika kroz JWT provjeru
- Forma za prijavu više nema unaprijed popunjene testne podatke
- Početni podaci u dokumentaciji označeni su kao opcionalni za lokalnu provjeru

## [0.5.0] - 2026-05-30

### Dodano

- CSRF tokeni i serverska validacija tokena na HTML obrascima
- Content-Security-Policy zaglavlje za HTTP odgovore
- Serverska validacija naslova i sadržaja bilješki
- ADR-002: sigurnosne mjere web aplikacije

### Promijenjeno

- SQL upiti u modelima prebačeni su na PDO prepared statements
- Korisnički podaci u HTML predlošcima escapaju se prije ispisa
- Spremanje korisnika provjerava da je lozinka pohranjena kao hash

## [0.4.0] - 2026-05-30

### Dodano

- Registracija i prijava preko `/api/auth/register` i `/api/auth/login`
- JWT tokeni s trajanjem od 24 sata i payloadom za `user_id` i `uloga`
- `AuthMiddleware` za provjeru Bearer tokena u API zahtjevima
- Zaštita `/api/notes` ruta i filtriranje bilješki po prijavljenom korisniku
- Osnovne admin API rute za pregled korisnika i bilješki
- Login i register obrasci s pohranom tokena u `localStorage`

## [0.3.0] - 2026-05-30

### Dodano

- REST API rute za bilješke i kategorije s JSON odgovorima
- Kreiranje, dohvat, ažuriranje i brisanje bilješki preko `/api/notes`
- Fetch API učitavanje liste bilješki bez osvježavanja stranice
- API dokumentacija u `README.md`

## [0.2.0] - 2026-05-30

### Dodano

- MVC struktura s mapama `controllers`, `models`, `views`, `config` i `public`
- `Router` klasa za mapiranje web putanja na metode kontrolera
- `NoteModel` i `CategoryModel` za rad s bilješkama i kategorijama
- `NoteController` s prikazom liste, detalja, unosa i uređivanja bilješki
- HTML predlošci i osnovni stilovi za prikaz bilješki
- Testni podaci u `database/seed.sql`

## [0.1.0] - 2026-05-21

### Dodano

- MySQL shema (`database/schema.sql`) za tablice `korisnici`, `biljeske` i `kategorije`
- ER dijagram u `docs/diagrams/er-diagram.md`
- ADR-001: odabir MySQL baze podataka
- Početni `README.md`
