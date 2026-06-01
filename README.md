# NoteApp

Web aplikacija za stvaranje, uređivanje i organiziranje osobnih bilješki po kategorijama, izrađena u sklopu kolegija Napredni Razvoj Web Aplikacija.

## Opis projekta

Svaki registrirani korisnik ima privatni prostor s bilješkama vidljivim samo njemu. Administrator ima pregled nad svim korisnicima i sadržajem sustava.

## Tehnologije

- **Backend:** PHP bez okvira
- **Baza podataka:** MySQL
- **Razvojno okruženje:** Laragon (Apache + MySQL)

## Struktura baze podataka

| Entitet      | Atributi                                                                     |
| ------------ | ---------------------------------------------------------------------------- |
| `korisnici`  | id, ime, email, lozinka_hash, uloga (user/admin), datum_registracije         |
| `biljeske`   | id, naslov, sadrzaj, datum_izrade, datum_izmjene, korisnik_id, kategorija_id |
| `kategorije` | id, naziv, boja, korisnik_id                                                 |

**Relacije:** jedan korisnik -> više bilješki; jedna kategorija -> više bilješki; kategorija pripada jednom korisniku.

SQL skripta za kreiranje tablica nalazi se u [`database/schema.sql`](database/schema.sql), testni podaci u [`database/seed.sql`](database/seed.sql), a ER dijagram u [`docs/diagrams/er-diagram.md`](docs/diagrams/er-diagram.md).

## Instalacija i pokretanje

```bash
# 1. Klonirati repozitorij
git clone https://github.com/mapokapo/nrwa-noteapp.git
cd nrwa-noteapp

# 2. Kreirati bazu podataka (u MySQL klijentu ili phpMyAdmin)
CREATE DATABASE noteapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 3. Pokrenuti SQL skriptu
mysql -u root -p noteapp < database/schema.sql

# 4. Uvesti testne podatke
mysql -u root -p noteapp < database/seed.sql

# 5. Pokrenuti aplikaciju
php -S 127.0.0.1:8000 -t public public/index.php
```

Nakon pokretanja aplikacija je dostupna na `http://127.0.0.1:8000`. U Laragonu je potrebno usmjeriti web poslužitelj na mapu `public`.

Zadane postavke baze su `127.0.0.1`, baza `noteapp`, korisnik `root` i prazna lozinka. Mogu se promijeniti varijablama okruženja `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` i `DB_PASSWORD`.

## Web rute

| Ruta               | Opis                           |
| ------------------ | ------------------------------ |
| `/`                | Lista svih bilješki            |
| `/notes`           | Lista svih bilješki            |
| `/notes/{id}`      | Detalji jedne bilješke         |
| `/notes/create`    | Obrazac za novu bilješku       |
| `/notes/{id}/edit` | Obrazac za uređivanje bilješke |

## Arhitekturalne odluke

Dokumentirane u mapi `docs/adr/`:

- [`ADR-001`](docs/adr/ADR-001.md) - Odabir MySQL baze podataka

## Struktura projekta

```
nrwa-noteapp/
├── config/
│   └── database.php
├── controllers/
│   └── NoteController.php
├── database/
│   ├── schema.sql
│   └── seed.sql
├── docs/
│   ├── adr/
│   │   └── ADR-001.md
│   └── diagrams/
│       └── er-diagram.md
├── models/
│   ├── CategoryModel.php
│   └── NoteModel.php
├── public/
│   ├── .htaccess
│   ├── index.php
│   └── style.css
├── views/
│   ├── notes/
│   │   ├── form.php
│   │   ├── index.php
│   │   └── show.php
│   ├── 404.php
│   └── error.php
├── CHANGELOG.md
├── README.md
├── Router.php
└── .gitignore
```
