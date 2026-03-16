<?php

class Alunno
{
    public $id;
    public $nome;
    public $cognome;

    public function __construct($nome = null, $cognome = null, $id = null)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->cognome = $cognome;
    }

    public static function fromArray(array $data)
    {
        return new Alunno(
            $data['nome'] ?? null,
            $data['cognome'] ?? null,
            $data['id'] ?? null
        );
    }

    public function toArray()
    {
        return [
            "id" => $this->id,
            "nome" => $this->nome,
            "cognome" => $this->cognome
        ];
    }
}

class AlunniHandler
{
    private $conn;

    public function __construct($mysqli_connection)
    {
        $this->conn = $mysqli_connection;
    }

    public function getAll()
    {
        $result = $this->conn->query("SELECT * FROM alunni");
        $rows = $result->fetch_all(MYSQLI_ASSOC);

        $alunni = [];

        foreach ($rows as $row) {
            $alunni[] = Alunno::fromArray($row);
        }

        return $alunni;
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM alunni WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            return null;
        }

        return Alunno::fromArray($result->fetch_assoc());
    }

    public function create(Alunno $alunno)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO alunni (nome, cognome) VALUES (?, ?)"
        );

        $stmt->bind_param("ss", $alunno->nome, $alunno->cognome);
        $stmt->execute();

        $alunno->id = $this->conn->insert_id;

        return $alunno;
    }

    public function update(Alunno $alunno)
    {
        $stmt = $this->conn->prepare(
            "UPDATE alunni SET nome = ?, cognome = ? WHERE id = ?"
        );

        $stmt->bind_param(
            "ssi",
            $alunno->nome,
            $alunno->cognome,
            $alunno->id
        );

        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM alunni WHERE id = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }
}
