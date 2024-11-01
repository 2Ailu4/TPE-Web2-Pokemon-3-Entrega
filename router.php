<?php
require_once 'libs/router.php';
require_once 'app/controllers/game.api.controller.php';

$router = new Router();

#                  endpoint                     verbo           controller         metodo
$router->addRoute('x'                         ,''       ,  'GameApiController',   '');  


$router->route($_GET['resource'], $_SERVER['REQUEST_METHOD']);  


