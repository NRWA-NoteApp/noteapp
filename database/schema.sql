SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS biljeske;
DROP TABLE IF EXISTS kategorije;
DROP TABLE IF EXISTS korisnici;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE korisnici (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ime VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    lozinka_hash VARCHAR(255) NOT NULL,
    uloga ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    datum_registracije TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_korisnici_email UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kategorije (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    naziv VARCHAR(100) NOT NULL,
    boja CHAR(7) NOT NULL,
    korisnik_id BIGINT UNSIGNED NOT NULL,

    INDEX idx_kategorije_korisnik_id (korisnik_id),

    CONSTRAINT fk_kategorije_korisnik
        FOREIGN KEY (korisnik_id)
        REFERENCES korisnici (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE biljeske (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    naslov VARCHAR(255) NOT NULL,
    sadrzaj TEXT NOT NULL,
    datum_izrade TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    datum_izmjene TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    korisnik_id BIGINT UNSIGNED NOT NULL,
    kategorija_id BIGINT UNSIGNED NULL,

    INDEX idx_biljeske_korisnik_id (korisnik_id),
    INDEX idx_biljeske_kategorija_id (kategorija_id),

    CONSTRAINT fk_biljeske_korisnik
        FOREIGN KEY (korisnik_id)
        REFERENCES korisnici (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_biljeske_kategorija
        FOREIGN KEY (kategorija_id)
        REFERENCES kategorije (id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
