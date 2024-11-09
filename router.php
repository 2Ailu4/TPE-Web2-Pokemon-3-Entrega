<?php
require_once 'libs/router.php';
require_once 'app/controllers/game.api.controller.php';

$router = new Router();

//aprendizaje
#                  endpoint                        verbo           controller                   metodo
$router->addRoute('aprendizaje'                  ,'GET'       ,  'AprendizajeApiController',   'getAll');  
$router->addRoute('aprendizaje/:id_pok/:id_mov'  ,'GET'       ,  'AprendizajeApiController',   'get'   ); // if(!intval($req->params->id))
$router->addRoute('aprendizaje/:id_pok/:id_mov'  ,'PUT'       ,  'AprendizajeApiController',   'update'); // if(!intval($req->params->id))
$router->addRoute('aprendizaje'                  ,'POST'      ,  'AprendizajeApiController',   'insert'); 

//movimiento
#                  endpoint                        verbo           controller            metodo
$router->addRoute('movimiento'                    ,'GET'       ,  'MovimientoApiController',   'getAll'); 
$router->addRoute('movimiento/:id_mov'            ,'GET'       ,  'MovimientoApiController',   'get'   ); // if(!intval($req->params->id))
$router->addRoute('movimiento/:id_mov'            ,'PUT'       ,  'MovimientoApiController',   'update'); // if(!intval($req->params->id))
$router->addRoute('movimiento'                    ,'POST'      ,  'MovimientoApiController',   'insert'); 
 
$router->route($_GET['resource'], $_SERVER['REQUEST_METHOD']);  

//      ENDPOINTS
//GET:  api/pokemon
//      api/pokemon?sort=""&order=""

//      api/pokemon/nivel_aprendizaje?value=""&limite=""
//      api/pokemon/nombre-pokemon?value=""&limite=""
//      api/pokemon?nombre-pokemon="pikachu"
//      api/pokemon?nombre_pokemon=pikachu&...
// /:id
// ?apodo 
// /fk_id_entrenador