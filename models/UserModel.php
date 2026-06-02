<?php

class UserModel
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function findByEmail(string $email): ?array
    {
        $email = $this->connection->quote($email);
        $sql = "
            SELECT *
            FROM korisnici
            WHERE email = {$email}
            LIMIT 1
        ";

        $user = $this->connection->query($sql)->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $sql = "
            SELECT id, ime, email, uloga, datum_registracije
            FROM korisnici
            WHERE id = {$id}
            LIMIT 1
        ";

        $user = $this->connection->query($sql)->fetch();

        return $user ?: null;
    }

    public function findAllWithStats(): array
    {
        $sql = "
            SELECT
                u.id,
                u.ime,
                u.email,
                u.uloga,
                u.datum_registracije,
                COUNT(DISTINCT b.id) AS broj_biljeski,
                COUNT(DISTINCT k.id) AS broj_kategorija
            FROM korisnici u
            LEFT JOIN biljeske b ON b.korisnik_id = u.id
            LEFT JOIN kategorije k ON k.korisnik_id = u.id
            GROUP BY u.id, u.ime, u.email, u.uloga, u.datum_registracije
            ORDER BY u.datum_registracije DESC
        ";

        return $this->connection->query($sql)->fetchAll();
    }

    public function create(array $data): int
    {
        $ime = $this->connection->quote($data['ime']);
        $email = $this->connection->quote($data['email']);
        $lozinkaHash = $this->connection->quote($data['lozinka_hash']);
        $uloga = $this->connection->quote($data['uloga'] ?? 'user');

        $sql = "
            INSERT INTO korisnici (ime, email, lozinka_hash, uloga)
            VALUES ({$ime}, {$email}, {$lozinkaHash}, {$uloga})
        ";

        $this->connection->exec($sql);

        return (int) $this->connection->lastInsertId();
    }
}
