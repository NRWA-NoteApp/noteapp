<?php

class CategoryModel
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function findByUser(int $userId): array
    {
        $sql = "
            SELECT *
            FROM kategorije
            WHERE korisnik_id = :userId
            ORDER BY naziv ASC
        ";

        $stmt = $this->connection->prepare($sql);

        $stmt->execute([
            'userId' => $userId
        ]);

        return $stmt->fetchAll();
    }

    public function existsForUser(int $id, int $userId): bool
    {
        $query = "
            SELECT id
            FROM kategorije
            WHERE id = :id
                AND korisnik_id = :userId
            LIMIT 1
        ";

        $stmt = $this->connection->prepare($query);

        $stmt->execute([
            'id' => $id,
            'userId' => $userId
        ]);

        return $stmt->fetch() !== false;
    }

    public function create(array $data): int
    {
        $sql = "
            INSERT INTO kategorije (
                naziv,
                boja,
                korisnik_id
            )
            VALUES (
                :naziv,
                :boja,
                :korisnik_id
            )
        ";

        $stmt = $this->connection->prepare($sql);

        $stmt->execute([
            'naziv' => $data['naziv'],
            'boja' => $data['boja'],
            'korisnik_id' => (int) $data['korisnik_id']
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $sql = "
            DELETE FROM kategorije
            WHERE id = :id
        ";

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            'id' => $id
        ]);
    }
}