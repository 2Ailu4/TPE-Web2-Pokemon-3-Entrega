<?php
require_once 'libs/router.php';
require_once 'app/controllers/aprendizaje.api.controller.php';
require_once 'app/controllers/movimiento.api.controller.php';
require_once 'app/controllers/pokemon.api.controller.php';
require_once 'app/controllers/user.api.controller.php';
require_once 'app/middlewares/jwt.auth.middleware.php';

$router = new Router();

$router->addMiddleware(new JWTAuthMiddleware());

//Aprendizaje
#                  endpoint                        verbo           controller                   metodo
$router->addRoute('aprendizaje'                  ,'GET'       ,  'AprendizajeApiController',   'getAll');  
$router->addRoute('aprendizaje/:id_pok/:id_mov'  ,'GET'       ,  'AprendizajeApiController',   'get'   );
$router->addRoute('aprendizaje/:id_pok/:id_mov'  ,'PATCH'     ,  'AprendizajeApiController',   'update'); 
$router->addRoute('aprendizaje'                  ,'POST'      ,  'AprendizajeApiController',   'insert'); 
$router->addRoute('aprendizaje/:id_pok/:id_mov'  ,'DELETE'    ,  'AprendizajeApiController',   'delete');

//Movimiento
#                  endpoint                        verbo           controller            metodo
$router->addRoute('movimiento'                    ,'GET'       ,  'MovimientoApiController',   'getAll'); 
$router->addRoute('movimiento/:id_mov'            ,'GET'       ,  'MovimientoApiController',   'get'   );  
$router->addRoute('movimiento/:id_mov'            ,'PATCH'     ,  'MovimientoApiController',   'update');  
$router->addRoute('movimiento'                    ,'POST'      ,  'MovimientoApiController',   'insert'); 
$router->addRoute('movimiento/:id_mov'            ,'DELETE'    ,  'MovimientoApiController',   'delete');

//Pokemon
#                  endpoint                        verbo           controller            metodo
$router->addRoute('pokemon'                       ,'GET'       ,  'PokemonApiController',      'getAll'); 
$router->addRoute('pokemon/:id_pok'               ,'GET'       ,  'PokemonApiController',      'get'   );  
$router->addRoute('pokemon/:id_pok'               ,'PATCH'     ,  'PokemonApiController',      'update');  
$router->addRoute('pokemon'                       ,'POST'      ,  'PokemonApiController',      'insert'); 
$router->addRoute('pokemon/:id_pok'               ,'DELETE'    ,  'PokemonApiController','releasePokemon'); 


$router->addRoute('usuario/token'                 ,'GET'       ,  'UserApiController'   ,      'getToken');

$router->setDefaultRoute('AprendizajeApiController', 'NotFound');
 
$router->route($_GET['resource'], $_SERVER['REQUEST_METHOD']);  