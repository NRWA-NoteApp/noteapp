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
        $statement = $this->connection->prepare("
            SELECT
                b.*,
                k.naziv AS kategorija_naziv,
                k.boja AS kategorija_boja,
                u.ime AS korisnik_ime
            FROM biljeske b
            LEFT JOIN kategorije k ON b.kategorija_id = k.id
            INNER JOIN korisnici u ON b.korisnik_id = u.id
            ORDER BY b.datum_izmjene DESC
        ");

        $statement->execute();

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection->prepare("
            SELECT
                b.*,
                k.naziv AS kategorija_naziv,
                k.boja AS kategorija_boja,
                u.ime AS korisnik_ime
            FROM biljeske b
            LEFT JOIN kategorije k ON b.kategorija_id = k.id
            INNER JOIN korisnici u ON b.korisnik_id = u.id
            WHERE b.id = :id
            LIMIT 1
        ");
        $statement->execute([
            'id' => $id,
        ]);

        $note = $statement->fetch();

        return $note ?: null;
    }

    public function findByUser(int $userId): array
    {
        $statement = $this->connection->prepare("
            SELECT
                b.*,
                k.naziv AS kategorija_naziv,
                k.boja AS kategorija_boja,
                u.ime AS korisnik_ime
            FROM biljeske b
            LEFT JOIN kategorije k ON b.kategorija_id = k.id
            INNER JOIN korisnici u ON b.korisnik_id = u.id
            WHERE b.korisnik_id = :user_id
            ORDER BY b.datum_izmjene DESC
        ");
        $statement->execute([
            'user_id' => $userId,
        ]);

        return $statement->fetchAll();
    }

    public function findByIdForUser(int $id, int $userId): ?array
    {
        $statement = $this->connection->prepare("
            SELECT
                b.*,
                k.naziv AS kategorija_naziv,
                k.boja AS kategorija_boja,
                u.ime AS korisnik_ime
            FROM biljeske b
            LEFT JOIN kategorije k ON b.kategorija_id = k.id
            INNER JOIN korisnici u ON b.korisnik_id = u.id
            WHERE b.id = :id
                AND b.korisnik_id = :user_id
            LIMIT 1
        ");
        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        $note = $statement->fetch();

        return $note ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->connection->prepare("
            INSERT INTO biljeske (naslov, sadrzaj, korisnik_id, kategorija_id)
            VALUES (:naslov, :sadrzaj, :korisnik_id, :kategorija_id)
        ");
        $statement->execute([
            'naslov' => $data['naslov'],
            'sadrzaj' => $data['sadrzaj'],
            'korisnik_id' => (int) $data['korisnik_id'],
            'kategorija_id' => empty($data['kategorija_id']) ? null : (int) $data['kategorija_id'],
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->connection->prepare("
            UPDATE biljeske
            SET naslov = :naslov,
                sadrzaj = :sadrzaj,
                kategorija_id = :kategorija_id
            WHERE id = :id
        ");

        return $statement->execute([
            'naslov' => $data['naslov'],
            'sadrzaj' => $data['sadrzaj'],
            'kategorija_id' => empty($data['kategorija_id']) ? null : (int) $data['kategorija_id'],
            'id' => $id,
        ]);
    }

    public function updateForUser(int $id, int $userId, array $data): bool
    {
        $statement = $this->connection->prepare("
            UPDATE biljeske
            SET naslov = :naslov,
                sadrzaj = :sadrzaj,
                kategorija_id = :kategorija_id
            WHERE id = :id
                AND korisnik_id = :user_id
        ");

        return $statement->execute([
            'naslov' => $data['naslov'],
            'sadrzaj' => $data['sadrzaj'],
            'kategorija_id' => empty($data['kategorija_id']) ? null : (int) $data['kategorija_id'],
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare("DELETE FROM biljeske WHERE id = :id");

        return $statement->execute([
            'id' => $id,
        ]);
    }

    public function deleteForUser(int $id, int $userId): bool
    {
        $statement = $this->connection->prepare("
            DELETE FROM biljeske
            WHERE id = :id
                AND korisnik_id = :user_id
        ");

        return $statement->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);
    }
}
