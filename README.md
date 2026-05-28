# NoteApp

Web aplikacija za stvaranje, uređivanje i organiziranje osobnih bilješki po kategorijama, izrađena u sklopu kolegija Napredni Razvoj Web Aplikacija.

## Opis projekta

Svaki registrirani korisnik ima privatni prostor s bilješkama vidljivim samo njemu. Administrator ima pregled nad svim korisnicima i sadržajem sustava.

## Tehnologije

- **Baza podataka:** MySQL
- **Razvojno okruženje:** Laragon (Apache + MySQL)

## Struktura baze podataka

| Entitet      | Atributi                                                                     |
| ------------ | ---------------------------------------------------------------------------- |
| `korisnici`  | id, ime, email, lozinka_hash, uloga (user/admin), datum_registracije         |
| `biljeske`   | id, naslov, sadrzaj, datum_izrade, datum_izmjene, korisnik_id, kategorija_id |
| `kategorije` | id, naziv, boja, korisnik_id                                                 |

**Relacije:** jedan korisnik -> više bilješki; jedna kategorija -> više bilješki; kategorija pripada jednom korisniku.

SQL skripta za kreiranje tablica nalazi se u [`database/schema.sql`](database/schema.sql), a ER dijagram u [`docs/diagrams/er-diagram.md`](docs/diagrams/er-diagram.md).

## Instalacija baze podataka

```bash
# 1. Klonirati repozitorij
git clone https://github.com/mapokapo/nrwa-noteapp.git
cd nrwa-noteapp

# 2. Kreirati bazu podataka (u MySQL klijentu ili phpMyAdmin)
CREATE DATABASE noteapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 3. Pokrenuti SQL skriptu
mysql -u root -p noteapp < database/schema.sql
```

> Projekt se razvija u **Laragon** okruženju (Apache + MySQL).

## Arhitekturalne odluke

Dokumentirane u mapi `docs/adr/`:

- [`ADR-001`](docs/adr/ADR-001.md) - Odabir MySQL baze podataka

## Struktura projekta

```
nrwa-noteapp/
├── database/
│   └── schema.sql
├── docs/
│   ├── adr/
│   │   └── ADR-001.md
│   └── diagrams/
│       └── er-diagram.md
├── CHANGELOG.md
├── README.md
└── .gitignore
```
