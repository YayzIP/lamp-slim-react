<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AlunniController
{
  private $mysqli_connection;
  
  /* Stabilisce la connessione nel costruttore */
  public function __construct() {
    $this->mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
    
    if ($this->mysqli_connection->connect_error) {
      die("Connessione fallita: " . $this->mysqli_connection->connect_error);
    }
  }

  public function index(Request $request, Response $response, $args){
    $result = $this->mysqli_connection->query("SELECT * FROM alunni");
    $results = $result->fetch_all(MYSQLI_ASSOC);
    $response->getBody()->write(json_encode($results));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }

  public function show(Request $request, Response $response, $args){
    $id = $args['id'] ?? null;
    
    if ($id) {
      $stmt = $this->mysqli_connection->prepare("SELECT * FROM alunni WHERE id = ?");
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
  
  
  public function create(Request $request, Response $response, $args){
    // Ottiene i dati dal body della richiesta
    $data = $request->getParsedBody();
    
    /* Per richieste JSON, getParsedBody() funziona automaticamente
    se l'header Content-Type: application/json è presente */
    
    $nome = $data['nome'] ?? null;
    $cognome = $data['cognome'] ?? null;
    $classe = $data['classe'] ?? null;
    
    // Validazione base
    if (!$nome || !$cognome) {
      $response->getBody()->write(json_encode([
        'error' => 'Nome e cognome sono obbligatori'
      ]));
      return $response->withHeader("Content-type", "application/json")->withStatus(400);
    }
    
    // Inserimento nel database
    $stmt = $this->mysqli_connection->prepare(
      "INSERT INTO alunni (nome, cognome, classe) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("sss", $nome, $cognome, $classe);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
      $responseData = [
        'id' => $this->mysqli_connection->insert_id,
        'nome' => $nome,
        'cognome' => $cognome,
        'classe' => $classe
      ];
      
      $response->getBody()->write(json_encode($responseData));
      return $response->withHeader("Content-type", "application/json")->withStatus(201);
    }
    
    return $response->withStatus(500);
  }

  public function update(Request $request, Response $response, $args){
    $id = $args['id'];
    
    // Ottiene i dati dal body
    $data = $request->getParsedBody();
    
    $nome = $data['nome'] ?? null;
    $cognome = $data['cognome'] ?? null;
    $classe = $data['classe'] ?? null;
    
    // Costruisci la query dinamicamente in base ai campi presenti
    $fields = [];
    $params = [];
    $types = "";
    
    if ($nome) {
      $fields[] = "nome = ?";
      $params[] = $nome;
      $types .= "s";
    }
    
    if ($cognome) {
      $fields[] = "cognome = ?";
      $params[] = $cognome;
      $types .= "s";
    }
    
    if ($classe) {
      $fields[] = "classe = ?";
      $params[] = $classe;
      $types .= "s";
    }
    
    if (empty($fields)) {
      $response->getBody()->write(json_encode([
        'error' => 'Nessun campo da aggiornare'
      ]));
      return $response->withHeader("Content-type", "application/json")->withStatus(400);
    }
    
    // Aggiungi l'id alla fine dei parametri
    $params[] = $id;
    $types .= "i";
    
    $sql = "UPDATE alunni SET " . implode(", ", $fields) . " WHERE id = ?";
    
    $stmt = $this->mysqli_connection->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
      return $response->withStatus(200);
    }
    
    $response->getBody()->write(json_encode([
      'error' => 'Alunno non trovato o nessuna modifica effettuata'
    ]));
    return $response->withHeader("Content-type", "application/json")->withStatus(404);
  }

  public function delete(Request $request, Response $response, $args){
    $id = $args['id'] ?? null;
    
    // Validazione ID
    if (!$id || !is_numeric($id)) {
        $response->getBody()->write(json_encode([
            'error' => 'ID non valido o mancante'
        ]));
        return $response->withHeader("Content-type", "application/json")->withStatus(400);
    }
    
    // Prima verifica se l'alunno esiste (opzionale ma consigliato)
    $checkStmt = $this->mysqli_connection->prepare("SELECT id FROM alunni WHERE id = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows == 0) {
        $response->getBody()->write(json_encode([
            'error' => 'Alunno non trovato'
        ]));
        return $response->withHeader("Content-type", "application/json")->withStatus(404);
    }
    $checkStmt->close();
    
    // Prepara ed esegue la DELETE
    $stmt = $this->mysqli_connection->prepare("DELETE FROM alunni WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        // Eliminazione avvenuta con successo
        $response->getBody()->write(json_encode([
            'message' => 'Alunno eliminato con successo',
            'id' => $id
        ]));
        return $response->withHeader("Content-type", "application/json")->withStatus(200);
    } else {
        // Se affected_rows è 0, qualcosa è andato storto
        $response->getBody()->write(json_encode([
            'error' => 'Errore durante l\'eliminazione'
        ]));
        return $response->withHeader("Content-type", "application/json")->withStatus(500);
    }
    
    $stmt->close();
}
  
  // Opzionale: chiudere la connessione quando l'oggetto viene distrutto
  public function __destruct() {
    if ($this->mysqli_connection) {
      $this->mysqli_connection->close();
    }
  }
}
