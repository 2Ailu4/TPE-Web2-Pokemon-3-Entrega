<?php
require_once './app/models/pokemon.model.php';
require_once './app/models/movimiento.model.php';
require_once './app/models/aprendizaje.model.php';
require_once './app/views/json.view.php';

class AprendizajeApiController {
    private $pokemon_model;
    private $aprendizaje_model;
    private $movimiento_model;
    private $view;

    public function __construct() {
        $this->pokemon_model = new PokemonModel();
        $this->aprendizaje_model = new AprendizajeModel();
        $this->movimiento_model = new MovimientoModel();
        $this->view = new JSONView();
    }

    public function getAll($req, $res){
        // if(!$res->user) {
        //     return $this->view->response("No autorizado", 401);
        // }
        $filter_type = null;
        if(!isset($req->query->$filter_type))  
            $filter_type=$req->query->filter_type;

        $filter_name = null;
        if(!isset($req->query->$filter_name))  
            $filter_name=$req->query->filter_name;

        $orderBy = null;
        if(isset($req->query->order)){
            $orderBy = $req->query->order;
        }   

        $pokemonMovements = $this->aprendizaje_model->getAll($filter_name,$filter_type,$orderBy);
        if(!$pokemonMovements){
            $this->view->response("La tabla aprende no cuenta con filas",404);
            return;
        }

        $result = [];
        foreach($pokemonMovements as $movement_learned){
            $movement = $this->movimiento_model->get($movement_learned->FK_id_movimiento);
            if(!$movement){
                $this->view->response("Inconsistencias en DB entre tablas 'aprende' & 'movimiento' (No existe el movimiento con id=$movement_learned->FK_id_movimiento) ",500);
                return;
            }

            if(!isset($result[$movement_learned->FK_id_pokemon])){     // si aun no cargue el pokemon 
                $pokemon = $this->pokemon_model->get($movement_learned->FK_id_pokemon); //obtengo el POKEMON  de la DB
                if(!$pokemon){
                    $this->view->response("Inconsistencias en DB entre tablas 'aprende' & 'pokemon' (No existe el pokemon con id=$movement_learned->FK_id_pokemon)",500);
                    return;
                }
                
                $pokemon->movimientos = [];
                array_push($pokemon->movimientos,$movement); // a pokemon le creo un arreglo de movimientos e inserto el actual
                $result[$movement_learned->FK_id_pokemon] = $pokemon; // incerto el pokemon en el resultado 
            }else{// otro movimiento para un pokemon ya agregado a result
                array_push($result[$movement_learned->FK_id_pokemon]->movimientos,$movement); // incerto al arreglo movimientos el movimiento actual
            }
            
        }
        $this->view->response($result);
    }

    // ('aprendizaje'                  ,'POST'      ,  'AprendizajeApiController',   'insert'); 

    public function insert($req, $res){
    
        $id_pokemon = isset($req->query->id_pokemon) ? $req->query->id_pokemon : null;
        $id_movimiento = isset($req->query->id_movimiento) ? $req->query->id_movimiento : null;
        $nivel_aprendizaje = isset($req->query->nivel_aprendizaje) ? $req->query->nivel_aprendizaje : null;
         
        if(empty($id_pokemon)){       return $this->view->requirementError_response('id_pokemon');}
        if(empty($id_movimiento)){    return $this->view->requirementError_response('id_movimiento'); }
        if(empty($nivel_aprendizaje)){return $this->view->requirementError_response('campo nivel_aprendizaje');}

        if(!is_numeric($id_pokemon)){       return $this->view->typeError_response('id_pokemon','numerico');}
        if(!is_numeric($id_movimiento)){    return $this->view->typeError_response('id_movimiento','numerico');}
        if(!is_numeric($nivel_aprendizaje)){return $this->view->typeError_response('nivel_aprendizaje','numerico');}
        

        if(!($this->pokemon_model->exists($id_movimiento))){return $this->view->existence_Error_response('Movimiento', $id_movimiento);}
        if(!($this->movimiento_model->exists($id_pokemon))){return $this->view->existence_Error_response('Pokemon', $id_pokemon);}
        
        $pokemon = $this->pokemon_model->get($id_pokemon);
        $movimiento = $this->movimiento_model->get($id_movimiento);
        $already_exists = $this->aprendizaje_model->exists($id_pokemon , $id_movimiento);

        if($already_exists){return $this->view->aprendizaje_alreadyExists_Error_response($pokemon, $movimiento);}
        
        $id_aprendizaje = $this->aprendizaje_model->insert($id_pokemon , $id_movimiento, $nivel_aprendizaje);

        if(!($id_aprendizaje)){return $this->view->aprendizaje_insert_server_Error_response($pokemon, $movimiento);}

        $aprendizaje = new stdClass;
        $aprendizaje->id_pokemon = $id_pokemon;
        $aprendizaje->id_movimiento = $id_movimiento;   
        $aprendizaje->nivel_aprendizaje = $nivel_aprendizaje;  

        $this->view->response($aprendizaje,201);
    }
}

