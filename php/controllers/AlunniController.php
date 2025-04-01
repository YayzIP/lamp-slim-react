<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AlunniController
{
  public function index(Request $request, Response $response, $args){
    $db = Db::getInstance();
    $results = $db->select("alunni");
    $response->getBody()->write(json_encode($results));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }

  public function show(Request $request, Response $response, $args){
    $db = Db::getInstance();
    $results = $db->select("alunni", "ID = " . $args["id"]);
    $response->getBody()->write(json_encode($results));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }


  public function destroy(Request $request, Response $response, $args){
    $response->getBody()->write("ciao");
    return $response;
  }

  public function create(Request $request, Response $response, $args){
    
    $body = json_decode($request->getBody()->getContents(), true);    
    $nome = $body["nome"];
    $cognome = $body["cognome"];

    // Inserisco nel db

    $response->getBody()->write(json_encode(array("message"=> "Success")));
    return $response->withHeader("Content-type", "application/json")->withStatus(201);
  }
}
