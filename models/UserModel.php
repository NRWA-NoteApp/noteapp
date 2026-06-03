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
        $statement = $this->connection->prepare("
            SELECT *
            FROM korisnici
            WHERE email = :email
            LIMIT 1
        ");
        $statement->execute([
            'email' => $email,
        ]);

        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection->prepare("
            SELECT id, ime, email, uloga, datum_registracije
            FROM korisnici
            WHERE id = :id
            LIMIT 1
        ");
        $statement->execute([
            'id' => $id,
        ]);

        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findAllWithStats(): array
    {
        $statement = $this->connection->prepare("
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
        ");
        $statement->execute();

        return $statement->fetchAll();
    }

    public function create(array $data): int
    {
        $lozinkaHash = (string) $data['lozinka_hash'];
        $passwordInfo = password_get_info($lozinkaHash);

        if (($passwordInfo['algoName'] ?? 'unknown') === 'unknown') {
            throw new InvalidArgumentException('Lozinka mora biti pohranjena kao hash.');
        }

        $statement = $this->connection->prepare("
            INSERT INTO korisnici (ime, email, lozinka_hash, uloga)
            VALUES (:ime, :email, :lozinka_hash, :uloga)
        ");
        $statement->execute([
            'ime' => $data['ime'],
            'email' => $data['email'],
            'lozinka_hash' => $lozinkaHash,
            'uloga' => $data['uloga'] ?? 'user',
        ]);

        return (int) $this->connection->lastInsertId();
    }
}
