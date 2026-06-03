# NoteApp

Web aplikacija za stvaranje, uređivanje i organiziranje osobnih bilješki po kategorijama, izrađena u sklopu kolegija Napredni Razvoj Web Aplikacija.

## Opis projekta

Svaki registrirani korisnik ima privatni prostor s bilješkama vidljivim samo njemu. Administrator ima pregled nad svim korisnicima i sadržajem sustava.

## Tehnologije

- **Backend:** PHP bez okvira, uz PDO MySQL ekstenziju
- **Frontend:** HTML, CSS i Fetch API
- **Baza podataka:** MySQL
- **Autentikacija:** JWT s bcrypt hashiranjem lozinki
- **Sigurnost:** PDO prepared statements, CSRF tokeni, output escaping i Content-Security-Policy
- **Razvojno okruženje:** Laragon (Apache + MySQL)

## Struktura baze podataka

| Entitet      | Atributi                                                                     |
| ------------ | ---------------------------------------------------------------------------- |
| `korisnici`  | id, ime, email, lozinka_hash, uloga (user/admin), datum_registracije         |
| `biljeske`   | id, naslov, sadrzaj, datum_izrade, datum_izmjene, korisnik_id, kategorija_id |
| `kategorije` | id, naziv, boja, korisnik_id                                                 |

**Relacije:** jedan korisnik -> više bilješki; jedna kategorija -> više bilješki; kategorija pripada jednom korisniku.

SQL skripta za kreiranje tablica nalazi se u [`database/schema.sql`](database/schema.sql), opcionalni početni podaci za lokalnu provjeru u [`database/seed.sql`](database/seed.sql), ER dijagram u [`docs/diagrams/er-diagram.md`](docs/diagrams/er-diagram.md), a MVC dijagram arhitekture u [`docs/diagrams/architecture.md`](docs/diagrams/architecture.md).

## Instalacija i pokretanje

```bash
# 1. Klonirati repozitorij
git clone https://github.com/mapokapo/nrwa-noteapp.git
cd nrwa-noteapp

# 2. Kreirati bazu podataka (u MySQL klijentu ili phpMyAdmin)
CREATE DATABASE noteapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 3. Pokrenuti SQL skriptu
mysql -u root -p noteapp < database/schema.sql

# 4. Opcionalno uvesti početne podatke za lokalnu provjeru
mysql -u root -p noteapp < database/seed.sql

# 5. Pokrenuti aplikaciju ugrađenim PHP poslužiteljem
php -S 127.0.0.1:8000 -t public public/index.php
```

Nakon pokretanja aplikacija je dostupna na `http://127.0.0.1:8000`. U Laragon okruženju treba pokrenuti Apache i MySQL, kreirati bazu `noteapp`, uvesti `database/schema.sql` i usmjeriti web poslužitelj na mapu `public`.

Zadane postavke baze su `127.0.0.1`, baza `noteapp`, korisnik `root` i prazna lozinka. Mogu se promijeniti varijablama okruženja `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` i `DB_PASSWORD`.

## Pokretanje kroz Docker

Ako ne koristite Laragon, aplikaciju možete pokrenuti kroz Docker Compose:

```bash
docker compose up
```

Docker Compose pokreće PHP/Apache aplikaciju na `http://127.0.0.1:8000` i MySQL bazu na lokalnom portu `3307`. Pri prvom pokretanju MySQL kontejner automatski učitava `database/schema.sql` i `database/seed.sql`.

Za ponovno stvaranje baze od početka pokrenite:

```bash
docker compose down -v
docker compose up
```

JWT tajni ključ može se promijeniti varijablom okruženja `JWT_SECRET`. Token traje 24 sata i u payloadu sadrži `user_id` i `uloga`.

HTML obrasci koriste CSRF token iz sesije. Korisnički podaci koji se prikazuju u HTML-u escapaju se pomoću `htmlspecialchars`, a SQL upiti izvršavaju se kroz PDO prepared statements.

## Web rute

| Ruta               | Opis                                      |
| ------------------ | ----------------------------------------- |
| `/`                | Početna stranica s prijavom i bilješkama  |
| `/notes`           | Početna stranica s prijavom i bilješkama  |
| `/notes/{id}`      | Detalji vlastite bilješke                 |
| `/notes/create`    | Obrazac za novu bilješku prijavljenog korisnika |
| `/notes/{id}/edit` | Obrazac za uređivanje vlastite bilješke   |

Lista bilješki se nakon prijave dinamično učitava preko Fetch API poziva na `/api/notes`, bez ponovnog učitavanja cijele stranice. JWT token se sprema u `localStorage`, šalje kroz `Authorization: Bearer <token>` zaglavlje i sinkronizira u cookie kako bi server mogao zaštititi privatne web rute.

## API rute

API vraća JSON odgovore i koristi HTTP statusne kodove `200`, `201`, `400`, `401`, `403`, `404` i `500`.

| Metoda   | Ruta                 | Opis                                      |
| -------- | -------------------- | ----------------------------------------- |
| `POST`   | `/api/auth/register` | Registracija korisnika i izdavanje tokena |
| `POST`   | `/api/auth/login`    | Prijava korisnika i izdavanje tokena      |
| `GET`    | `/api/notes`         | Dohvat bilješki prijavljenog korisnika    |
| `GET`    | `/api/notes/{id}`    | Dohvat jedne vlastite bilješke            |
| `POST`   | `/api/notes`         | Kreiranje bilješke prijavljenog korisnika |
| `PUT`    | `/api/notes/{id}`    | Ažuriranje vlastite bilješke              |
| `DELETE` | `/api/notes/{id}`    | Brisanje vlastite bilješke                |
| `GET`    | `/api/categories`    | Dohvat kategorija prijavljenog korisnika  |
| `GET`    | `/api/admin/users`   | Admin pregled korisnika                   |
| `GET`    | `/api/admin/notes`   | Admin pregled svih bilješki               |

Zaštićene rute `/api/notes`, `/api/categories` i `/api/admin/*` zahtijevaju `Authorization: Bearer <token>`. Obični korisnik vidi samo vlastite bilješke i kategorije, dok su admin rute dostupne samo korisnicima s ulogom `admin`.

Za ručno testiranje auth endpointa izvan web sučelja prvo treba otvoriti web stranicu, preuzeti vrijednost skrivenog polja `csrf_token` i poslati isti session cookie uz JSON zahtjev. Web sučelje to radi automatski kroz forme za prijavu i registraciju.

### `POST /api/auth/register`

Zahtjev:

```json
{
  "ime": "Novi Korisnik",
  "email": "novi.korisnik@example.com",
  "lozinka": "password",
  "csrf_token": "vrijednost_iz_html_obrasca"
}
```

Uspješan odgovor `201`:

```json
{
  "data": {
    "user": {
      "id": 3,
      "ime": "Novi Korisnik",
      "email": "novi.korisnik@example.com",
      "uloga": "user",
      "datum_registracije": "2026-05-30 17:45:00"
    },
    "token": "<jwt-token>",
    "expires_in": 86400
  }
}
```

### `POST /api/auth/login`

Zahtjev:

```json
{
  "email": "ana.horvat@example.com",
  "lozinka": "password",
  "csrf_token": "vrijednost_iz_html_obrasca"
}
```

Uspješan odgovor `200`:

```json
{
  "data": {
    "user": {
      "id": 1,
      "ime": "Ana Horvat",
      "email": "ana.horvat@example.com",
      "uloga": "user",
      "datum_registracije": "2026-05-30 17:45:00"
    },
    "token": "<jwt-token>",
    "expires_in": 86400
  }
}
```

Početni korisnici iz `database/seed.sql`, ako ih uvezete lokalno, koriste lozinku `password`.

### `GET /api/notes`

Zahtjev:

```http
GET /api/notes
Authorization: Bearer <jwt-token>
Accept: application/json
```

Uspješan odgovor `200`:

```json
{
  "data": [
    {
      "id": 1,
      "naslov": "Plan učenja",
      "sadrzaj": "Ponoviti MVC arhitekturu.",
      "datum_izrade": "2026-05-30 17:45:00",
      "datum_izmjene": "2026-05-30 17:45:00",
      "korisnik_id": 1,
      "kategorija_id": 1,
      "kategorija_naziv": "Fakultet",
      "kategorija_boja": "#5d6b4d",
      "korisnik_ime": "Ana Horvat"
    }
  ]
}
```

### `GET /api/notes/{id}`

Zahtjev:

```http
GET /api/notes/1
Authorization: Bearer <jwt-token>
Accept: application/json
```

Uspješan odgovor `200`:

```json
{
  "data": {
    "id": 1,
    "naslov": "Plan učenja",
    "sadrzaj": "Ponoviti MVC arhitekturu.",
    "datum_izrade": "2026-05-30 17:45:00",
    "datum_izmjene": "2026-05-30 17:45:00",
    "korisnik_id": 1,
    "kategorija_id": 1,
    "kategorija_naziv": "Fakultet",
    "kategorija_boja": "#5d6b4d",
    "korisnik_ime": "Ana Horvat"
  }
}
```

Ako bilješka ne postoji ili ne pripada prijavljenom korisniku, odgovor je `404`:

```json
{
  "error": "Bilješka nije pronađena."
}
```

### `POST /api/notes`

Zahtjev:

```http
POST /api/notes
Authorization: Bearer <jwt-token>
Content-Type: application/json
Accept: application/json
```

```json
{
  "naslov": "Nova bilješka",
  "sadrzaj": "Sadržaj bilješke",
  "kategorija_id": 1
}
```

Uspješan odgovor `201`:

```json
{
  "data": {
    "id": 4,
    "naslov": "Nova bilješka",
    "sadrzaj": "Sadržaj bilješke",
    "datum_izrade": "2026-05-30 17:45:00",
    "datum_izmjene": "2026-05-30 17:45:00",
    "korisnik_id": 1,
    "kategorija_id": 1,
    "kategorija_naziv": "Fakultet",
    "kategorija_boja": "#5d6b4d",
    "korisnik_ime": "Ana Horvat"
  }
}
```

### `PUT /api/notes/{id}`

Zahtjev:

```http
PUT /api/notes/4
Authorization: Bearer <jwt-token>
Content-Type: application/json
Accept: application/json
```

```json
{
  "naslov": "Ažurirana bilješka",
  "sadrzaj": "Novi sadržaj bilješke",
  "kategorija_id": null
}
```

Uspješan odgovor `200`:

```json
{
  "data": {
    "id": 4,
    "naslov": "Ažurirana bilješka",
    "sadrzaj": "Novi sadržaj bilješke",
    "datum_izrade": "2026-05-30 17:45:00",
    "datum_izmjene": "2026-05-30 17:50:00",
    "korisnik_id": 1,
    "kategorija_id": null,
    "kategorija_naziv": null,
    "kategorija_boja": null,
    "korisnik_ime": "Ana Horvat"
  }
}
```

### `DELETE /api/notes/{id}`

Zahtjev:

```http
DELETE /api/notes/4
Authorization: Bearer <jwt-token>
Accept: application/json
```

Uspješan odgovor `200`:

```json
{
  "message": "Bilješka je obrisana."
}
```

### `GET /api/categories`

Zahtjev:

```http
GET /api/categories
Authorization: Bearer <jwt-token>
Accept: application/json
```

Uspješan odgovor `200`:

```json
{
  "data": [
    {
      "id": 1,
      "naziv": "Fakultet",
      "boja": "#5d6b4d",
      "korisnik_id": 1
    }
  ]
}
```

### `GET /api/admin/users`

Zahtjev:

```http
GET /api/admin/users
Authorization: Bearer <admin-jwt-token>
Accept: application/json
```

Uspješan odgovor `200`:

```json
{
  "data": [
    {
      "id": 2,
      "ime": "Marko Novak",
      "email": "marko.novak@example.com",
      "uloga": "admin",
      "datum_registracije": "2026-05-30 17:45:00",
      "broj_biljeski": 1,
      "broj_kategorija": 1
    }
  ]
}
```

### `GET /api/admin/notes`

Zahtjev:

```http
GET /api/admin/notes
Authorization: Bearer <admin-jwt-token>
Accept: application/json
```

Uspješan odgovor `200`:

```json
{
  "data": [
    {
      "id": 3,
      "naslov": "Administratorski pregled",
      "sadrzaj": "Zabilježiti što sustav prikazuje administratoru.",
      "datum_izrade": "2026-05-30 17:45:00",
      "datum_izmjene": "2026-05-30 17:45:00",
      "korisnik_id": 2,
      "kategorija_id": 3,
      "kategorija_naziv": "Administracija",
      "kategorija_boja": "#4f6b9a",
      "korisnik_ime": "Marko Novak"
    }
  ]
}
```

Za bilješke se na serveru provjerava da naslov nije prazan i da ima najviše 255 znakova, a sadržaj ne smije biti prazan.

Primjeri grešaka:

```json
{
  "error": "Nedostaje Authorization Bearer token."
}
```

```json
{
  "error": "Pošaljite naslov do 255 znakova, neprazan sadržaj i valjan kategorija_id."
}
```

## Arhitektura

NoteApp koristi jednostavnu MVC strukturu. Ulazna točka je `public/index.php`, `Router` prosljeđuje zahtjeve kontrolerima, kontroleri koriste modele za rad s MySQL bazom, a odgovori se vraćaju kao HTML predlošci ili JSON. Dijagram je dostupan u [`docs/diagrams/architecture.md`](docs/diagrams/architecture.md).

## Arhitekturalne odluke

Dokumentirane u mapi `docs/adr/`:

- [`ADR-001`](docs/adr/ADR-001.md) - Odabir MySQL baze podataka
- [`ADR-002`](docs/adr/ADR-002.md) - Sigurnosne mjere web aplikacije

## Struktura projekta

```
nrwa-noteapp/
├── config/
│   ├── database.php
│   └── jwt.php
├── controllers/
│   ├── AdminController.php
│   ├── ApiAuthController.php
│   ├── ApiCategoryController.php
│   ├── ApiNoteController.php
│   └── NoteController.php
├── database/
│   ├── schema.sql
│   └── seed.sql
├── docker-compose.yml
├── docs/
│   ├── adr/
│   │   ├── ADR-001.md
│   │   └── ADR-002.md
│   └── diagrams/
│       ├── architecture.md
│       └── er-diagram.md
├── models/
│   ├── CategoryModel.php
│   ├── NoteModel.php
│   └── UserModel.php
├── middleware/
│   └── AuthMiddleware.php
├── public/
│   ├── .htaccess
│   ├── index.php
│   ├── notes.js
│   └── style.css
├── services/
│   ├── JwtService.php
│   └── Security.php
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
