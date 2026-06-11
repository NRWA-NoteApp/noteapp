<?php

class NoteModel
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    private function baseSelect(): string
    {
        return "
            SELECT
                n.*,
                c.naziv AS kategorija_naziv,
                c.boja AS kategorija_boja,
                u.ime AS korisnik_ime
            FROM biljeske n
            LEFT JOIN kategorije c
                ON n.kategorija_id = c.id
            INNER JOIN korisnici u
                ON n.korisnik_id = u.id
        ";
    }

    public function findAll(): array
    {
        $query = $this->baseSelect() . "
            ORDER BY n.datum_izmjene DESC
        ";

        $stmt = $this->connection->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $query = $this->baseSelect() . "
            WHERE n.id = :id
            LIMIT 1
        ";

        $stmt = $this->connection->prepare($query);
        $stmt->execute([
            'id' => $id
        ]);

        $result = $stmt->fetch();

        return $result !== false ? $result : null;
    }

    public function findByUser(int $userId): array
    {
        $query = $this->baseSelect() . "
            WHERE n.korisnik_id = :userId
            ORDER BY n.datum_izmjene DESC
        ";

        $stmt = $this->connection->prepare($query);
        $stmt->execute([
            'userId' => $userId
        ]);

        return $stmt->fetchAll();
    }

    public function findByIdForUser(int $id, int $userId): ?array
    {
        $query = $this->baseSelect() . "
            WHERE n.id = :id
                AND n.korisnik_id = :userId
            LIMIT 1
        ";

        $stmt = $this->connection->prepare($query);
        $stmt->execute([
            'id' => $id,
            'userId' => $userId
        ]);

        $record = $stmt->fetch();

        return $record !== false ? $record : null;
    }

    public function create(array $data): int
    {
        $sql = "
            INSERT INTO biljeske (
                naslov,
                sadrzaj,
                korisnik_id,
                kategorija_id
            )
            VALUES (
                :naslov,
                :sadrzaj,
                :korisnik_id,
                :kategorija_id
            )
        ";

        $stmt = $this->connection->prepare($sql);

        $stmt->execute([
            'naslov' => $data['naslov'],
            'sadrzaj' => $data['sadrzaj'],
            'korisnik_id' => (int) $data['korisnik_id'],
            'kategorija_id' => !empty($data['kategorija_id'])
                ? (int) $data['kategorija_id']
                : null
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE biljeske
            SET
                naslov = :naslov,
                sadrzaj = :sadrzaj,
                kategorija_id = :kategorija_id
            WHERE id = :id
        ";

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            'naslov' => $data['naslov'],
            'sadrzaj' => $data['sadrzaj'],
            'kategorija_id' => !empty($data['kategorija_id'])
                ? (int) $data['kategorija_id']
                : null,
            'id' => $id
        ]);
    }

    public function updateForUser(int $id, int $userId, array $data): bool
    {
        $sql = "
            UPDATE biljeske
            SET
                naslov = :naslov,
                sadrzaj = :sadrzaj,
                kategorija_id = :kategorija_id
            WHERE id = :id
                AND korisnik_id = :userId
        ";

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            'naslov' => $data['naslov'],
            'sadrzaj' => $data['sadrzaj'],
            'kategorija_id' => !empty($data['kategorija_id'])
                ? (int) $data['kategorija_id']
                : null,
            'id' => $id,
            'userId' => $userId
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->connection->prepare(
            "DELETE FROM biljeske WHERE id = :id"
        );

        return $stmt->execute([
            'id' => $id
        ]);
    }

    public function deleteForUser(int $id, int $userId): bool
    {
        $sql = "
            DELETE FROM biljeske
            WHERE id = :id
                AND korisnik_id = :userId
        ";

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'userId' => $userId
        ]);
    }
}