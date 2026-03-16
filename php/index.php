<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/controllers/AlunniController.php';
require __DIR__ . '/controllers/CertificazioniController.php';

$app = AppFactory::create();

$app->get('/alunni', "AlunniController:index");
$app->get('/alunni/{id:[0-9]+}', "AlunniController:show");
$app->addBodyParsingMiddleware();
$app->post('/alunni', "AlunniController:create");
$app->put('/alunni/{id:[0-9]+}', "AlunniController:update");
$app->delete('/alunni/{id:[0-9]+}', "AlunniController:index");
$app->get('/alunni/{id:[0-9]+}/certificazioni', "AlunniController:indexCertificazioni");

$app->get('/certificazioni', "CertificazioniController:index");
$app->get('/certificazioni/{id:[0-9]+}', "CertificazioniController:show");

/* $app->any('/{routes:.+}', function (Request $request, Response $response, $args) {
    $response->getBody()->write(json_encode([
        'error' => 'Route non trovata',
        'message' => 'La richiesta non corrisponde a nessuna delle possibili'
    ]));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(404);
});
 */

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$app->run();
