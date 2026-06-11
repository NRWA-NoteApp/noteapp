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
        $sql = "
            SELECT *
            FROM korisnici
            WHERE email = :email
            LIMIT 1
        ";

        $stmt = $this->connection->prepare($sql);

        $stmt->execute([
            'email' => $email
        ]);

        $result = $stmt->fetch();

        return $result !== false ? $result : null;
    }

    public function findById(int $id): ?array
    {
        $sql = "
            SELECT
                id,
                ime,
                email,
                uloga,
                datum_registracije
            FROM korisnici
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->connection->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        $result = $stmt->fetch();

        return $result !== false ? $result : null;
    }

    public function findAllWithStats(): array
    {
        $query = "
            SELECT
                k.id,
                k.ime,
                k.email,
                k.uloga,
                k.datum_registracije,
                COUNT(DISTINCT b.id) AS broj_biljeski,
                COUNT(DISTINCT c.id) AS broj_kategorija
            FROM korisnici k
            LEFT JOIN biljeske b
                ON b.korisnik_id = k.id
            LEFT JOIN kategorije c
                ON c.korisnik_id = k.id
            GROUP BY
                k.id,
                k.ime,
                k.email,
                k.uloga,
                k.datum_registracije
            ORDER BY k.datum_registracije DESC
        ";

        $stmt = $this->connection->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $hash = (string) $data['lozinka_hash'];

        $hashInfo = password_get_info($hash);

        if (($hashInfo['algoName'] ?? 'unknown') === 'unknown') {
            throw new InvalidArgumentException(
                'Lozinka mora biti pohranjena kao hash.'
            );
        }

        $sql = "
            INSERT INTO korisnici (
                ime,
                email,
                lozinka_hash,
                uloga
            )
            VALUES (
                :ime,
                :email,
                :lozinka_hash,
                :uloga
            )
        ";

        $stmt = $this->connection->prepare($sql);

        $stmt->execute([
            'ime' => $data['ime'],
            'email' => $data['email'],
            'lozinka_hash' => $hash,
            'uloga' => $data['uloga'] ?? 'user'
        ]);

        return (int) $this->connection->lastInsertId();
    }
}