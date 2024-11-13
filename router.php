<?php
require_once 'libs/router.php';
require_once 'app/controllers/aprendizaje.api.controller.php';

$router = new Router();

//Aprendizaje
#                  endpoint                        verbo           controller                   metodo
$router->addRoute('aprendizaje'                  ,'GET'       ,  'AprendizajeApiController',   'getAll');  
$router->addRoute('aprendizaje/:id_pok/:id_mov'  ,'GET'       ,  'AprendizajeApiController',   'get'   );
//$router->addRoute('aprendizaje/:id_pok'          ,'GET'       ,  'AprendizajeApiController',   'get'   );
//$router->addRoute('aprendizaje/:id_mov'          ,'GET'       ,  'AprendizajeApiController',   'get'   );
$router->addRoute('aprendizaje/:id_pok/:id_mov'  ,'PUT'       ,  'AprendizajeApiController',   'update'); 
$router->addRoute('aprendizaje'                  ,'POST'      ,  'AprendizajeApiController',   'insert'); 

//Movimiento
#                  endpoint                        verbo           controller            metodo
$router->addRoute('movimiento'                    ,'GET'       ,  'MovimientoApiController',   'getAll'); 
$router->addRoute('movimiento/:id_mov'            ,'GET'       ,  'MovimientoApiController',   'get'   );  
$router->addRoute('movimiento/:id_mov'            ,'PUT'       ,  'MovimientoApiController',   'update');  
$router->addRoute('movimiento'                    ,'POST'      ,  'MovimientoApiController',   'insert'); 
 
$router->route($_GET['resource'], $_SERVER['REQUEST_METHOD']);  