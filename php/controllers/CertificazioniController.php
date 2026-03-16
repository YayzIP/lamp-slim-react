<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once __DIR__ . '/../db.php';

class CertificazioniController
{
    private $mysqli_connection;

    public function __construct()
    {
        $this->mysqli_connection = Database::getConnection();
    }

    public function index(Request $request, Response $response, $args)
    {
        $result = $this->mysqli_connection->query("SELECT * FROM certificazioni");
        $results = $result->fetch_all(MYSQLI_ASSOC);
        $response->getBody()->write(json_encode($results));
        return $response->withHeader("Content-type", "application/json")->withStatus(200);
    }

    public function show(Request $request, Response $response, $args)
    {
        $id = $args['id'] ?? null;

        if ($id) {
            $stmt = $this->mysqli_connection->prepare("SELECT * FROM certificazioni WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                $response->getBody()->write("404");
                return $response->withHeader("Content-type", "application/json")->withStatus(404);
            }

            $results = $result->fetch_all(MYSQLI_ASSOC);
            $response->getBody()->write(json_encode($results));
            return $response->withHeader("Content-type", "application/json")->withStatus(200);
        }

        return $response->withStatus(400);
    }
}
