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
            WHERE korisnik_id = {$userId}
            ORDER BY naziv ASC
        ";

        return $this->connection->query($sql)->fetchAll();
    }

    public function create(array $data): int
    {
        $naziv = $this->connection->quote($data['naziv']);
        $boja = $this->connection->quote($data['boja']);
        $korisnikId = (int) $data['korisnik_id'];

        $sql = "
            INSERT INTO kategorije (naziv, boja, korisnik_id)
            VALUES ({$naziv}, {$boja}, {$korisnikId})
        ";

        $this->connection->exec($sql);

        return (int) $this->connection->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM kategorije WHERE id = {$id}";

        return $this->connection->exec($sql) !== false;
    }
}
