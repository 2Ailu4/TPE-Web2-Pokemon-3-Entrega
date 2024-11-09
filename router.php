<?php
require_once 'libs/router.php';
require_once 'app/controllers/game.api.controller.php';

$router = new Router();

#                  endpoint                        verbo           controller                   metodo
$router->addRoute('aprendizaje'                  ,'GET'       ,  'AprendizajeApiController',   'getAll');  
$router->addRoute('aprendizaje/:id_pok/:id_mov'  ,'GET'       ,  'AprendizajeApiController',   'get'   );
$router->addRoute('aprendizaje/:id_pok/:id_mov'  ,'PUT'       ,  'AprendizajeApiController',   'update'); 
$router->addRoute('aprendizaje'                  ,'POST'      ,  'AprendizajeApiController',   'insert'); 


$router->route($_GET['resource'], $_SERVER['REQUEST_METHOD']);  

