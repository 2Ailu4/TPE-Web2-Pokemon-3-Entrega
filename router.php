<?php
require_once 'libs/router.php';
require_once 'app/controllers/game.api.controller.php';

$router = new Router();

#                  endpoint                   verbo           controller             metodo
$router->addRoute('aprende'                  ,'GET'       ,  'GameApiController',   'getAll');  
// $router->addRoute('aprende'                  ,'PUT'       ,  'GameApiController',   'update'); 

$router->route($_GET['resource'], $_SERVER['REQUEST_METHOD']);  

