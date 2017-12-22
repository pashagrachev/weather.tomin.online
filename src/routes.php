<?php

use Slim\Http\Request;
use Slim\Http\Response;
use App\Controllers;

// Routes
$app->any('/', function(Request $request, Response $response){
    $this->logger->info("Slim-Skeleton '/' route");

    $controller = new Controllers\HomeController;
    return $controller($request, $response);
});


$app->any('/deployment', function(Request $request, Response $response){
    $this->logger->info("Slim-Skeleton '/deployment' route");

    $controller = new Controllers\DeploymentController;
    return $controller->deployment($request, $response);
});