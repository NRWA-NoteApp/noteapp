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
        $statement = $this->connection->prepare("
            SELECT *
            FROM kategorije
            WHERE korisnik_id = :user_id
            ORDER BY naziv ASC
        ");
        $statement->execute([
            'user_id' => $userId,
        ]);

        return $statement->fetchAll();
    }

    public function existsForUser(int $id, int $userId): bool
    {
        $statement = $this->connection->prepare("
            SELECT id
            FROM kategorije
            WHERE id = :id
                AND korisnik_id = :user_id
            LIMIT 1
        ");
        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        return (bool) $statement->fetch();
    }

    public function create(array $data): int
    {
        $statement = $this->connection->prepare("
            INSERT INTO kategorije (naziv, boja, korisnik_id)
            VALUES (:naziv, :boja, :korisnik_id)
        ");
        $statement->execute([
            'naziv' => $data['naziv'],
            'boja' => $data['boja'],
            'korisnik_id' => (int) $data['korisnik_id'],
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare("DELETE FROM kategorije WHERE id = :id");

        return $statement->execute([
            'id' => $id,
        ]);
    }
}
