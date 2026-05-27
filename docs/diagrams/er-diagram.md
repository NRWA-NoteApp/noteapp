# ER dijagram - NoteApp

```mermaid
erDiagram
    KORISNICI {
        BIGINT id PK
        VARCHAR ime
        VARCHAR email UK
        VARCHAR lozinka_hash
        ENUM uloga
        TIMESTAMP datum_registracije
    }

    KATEGORIJE {
        BIGINT id PK
        VARCHAR naziv
        CHAR boja
        BIGINT korisnik_id FK
    }

    BILJESKE {
        BIGINT id PK
        VARCHAR naslov
        TEXT sadrzaj
        TIMESTAMP datum_izrade
        TIMESTAMP datum_izmjene
        BIGINT korisnik_id FK
        BIGINT kategorija_id FK "nullable"
    }

    KORISNICI ||--o{ KATEGORIJE : "ima"
    KORISNICI ||--o{ BILJESKE : "ima"
    KATEGORIJE ||--o{ BILJESKE : "organizira"
```

## Napomene

- Jedan korisnik može imati više bilješki.
- Jedan korisnik može imati više kategorija.
- Jedna kategorija pripada točno jednom korisniku.
- Bilješka pripada točno jednom korisniku, a kategorija može biti `NULL` ako se kategorija obriše.
