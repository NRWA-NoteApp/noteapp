<?php

class NoteModel
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function findAll(): array
    {
        $sql = "
            SELECT
                b.*,
                k.naziv AS kategorija_naziv,
                k.boja AS kategorija_boja,
                u.ime AS korisnik_ime
            FROM biljeske b
            LEFT JOIN kategorije k ON b.kategorija_id = k.id
            INNER JOIN korisnici u ON b.korisnik_id = u.id
            ORDER BY b.datum_izmjene DESC
        ";

        return $this->connection->query($sql)->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $sql = "
            SELECT
                b.*,
                k.naziv AS kategorija_naziv,
                k.boja AS kategorija_boja,
                u.ime AS korisnik_ime
            FROM biljeske b
            LEFT JOIN kategorije k ON b.kategorija_id = k.id
            INNER JOIN korisnici u ON b.korisnik_id = u.id
            WHERE b.id = {$id}
            LIMIT 1
        ";

        $note = $this->connection->query($sql)->fetch();

        return $note ?: null;
    }

    public function findByUser(int $userId): array
    {
        $sql = "
            SELECT
                b.*,
                k.naziv AS kategorija_naziv,
                k.boja AS kategorija_boja,
                u.ime AS korisnik_ime
            FROM biljeske b
            LEFT JOIN kategorije k ON b.kategorija_id = k.id
            INNER JOIN korisnici u ON b.korisnik_id = u.id
            WHERE b.korisnik_id = {$userId}
            ORDER BY b.datum_izmjene DESC
        ";

        return $this->connection->query($sql)->fetchAll();
    }

    public function findByIdForUser(int $id, int $userId): ?array
    {
        $sql = "
            SELECT
                b.*,
                k.naziv AS kategorija_naziv,
                k.boja AS kategorija_boja,
                u.ime AS korisnik_ime
            FROM biljeske b
            LEFT JOIN kategorije k ON b.kategorija_id = k.id
            INNER JOIN korisnici u ON b.korisnik_id = u.id
            WHERE b.id = {$id}
                AND b.korisnik_id = {$userId}
            LIMIT 1
        ";

        $note = $this->connection->query($sql)->fetch();

        return $note ?: null;
    }

    public function create(array $data): int
    {
        $naslov = $this->connection->quote($data['naslov']);
        $sadrzaj = $this->connection->quote($data['sadrzaj']);
        $korisnikId = (int) $data['korisnik_id'];
        $kategorijaId = empty($data['kategorija_id']) ? 'NULL' : (int) $data['kategorija_id'];

        $sql = "
            INSERT INTO biljeske (naslov, sadrzaj, korisnik_id, kategorija_id)
            VALUES ({$naslov}, {$sadrzaj}, {$korisnikId}, {$kategorijaId})
        ";

        $this->connection->exec($sql);

        return (int) $this->connection->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $naslov = $this->connection->quote($data['naslov']);
        $sadrzaj = $this->connection->quote($data['sadrzaj']);
        $kategorijaId = empty($data['kategorija_id']) ? 'NULL' : (int) $data['kategorija_id'];

        $sql = "
            UPDATE biljeske
            SET naslov = {$naslov},
                sadrzaj = {$sadrzaj},
                kategorija_id = {$kategorijaId}
            WHERE id = {$id}
        ";

        return $this->connection->exec($sql) !== false;
    }

    public function updateForUser(int $id, int $userId, array $data): bool
    {
        $naslov = $this->connection->quote($data['naslov']);
        $sadrzaj = $this->connection->quote($data['sadrzaj']);
        $kategorijaId = empty($data['kategorija_id']) ? 'NULL' : (int) $data['kategorija_id'];

        $sql = "
            UPDATE biljeske
            SET naslov = {$naslov},
                sadrzaj = {$sadrzaj},
                kategorija_id = {$kategorijaId}
            WHERE id = {$id}
                AND korisnik_id = {$userId}
        ";

        return $this->connection->exec($sql) !== false;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM biljeske WHERE id = {$id}";

        return $this->connection->exec($sql) !== false;
    }

    public function deleteForUser(int $id, int $userId): bool
    {
        $sql = "DELETE FROM biljeske WHERE id = {$id} AND korisnik_id = {$userId}";

        return $this->connection->exec($sql) !== false;
    }
}
