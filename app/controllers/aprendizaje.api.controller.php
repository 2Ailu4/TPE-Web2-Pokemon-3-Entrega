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
        $filter_type = null;
        $filter_name = null;
        $sortBy = null;
        $order = null;
        $limit = null;
        
        if(isset($req->query->filter_type)){ $filter_type = $req->query->filter_type;}
        if(isset($req->query->filter_name)){ $filter_name = $req->query->filter_name;}
        if(isset($req->query->sort)){ $sortBy = $req->query->sort;}   
        if(isset($req->query->order)){ $order = $req->query->order;}

        $relaciones = $this->aprendizaje_model->getAll($filter_name,$filter_type,$sortBy,$limit, $order);

        if(!$relaciones){
            $this->view->response("La tabla aprende no cuenta con filas", 404);
            return;
        }

        $result = [];
        foreach($relaciones as $movement_learned){
            $movement = $this->movimiento_model->get($movement_learned->FK_id_movimiento);
            $movement->nivel_aprendizaje = $movement_learned->nivel_aprendizaje;
            if(!$movement){
                $this->view->response("Inconsistencias en DB entre tablas 'aprende' & 'movimiento' (No existe el movimiento con id:$movement_learned->FK_id_movimiento) ",500);
                return;
            }

            if(!isset($result[$movement_learned->FK_id_pokemon])){     // si aun no cargue el pokemon 
                $pokemon = $this->pokemon_model->get($movement_learned->FK_id_pokemon); //obtengo el POKEMON  de la DB
                if(!$pokemon){
                    $this->view->response("Inconsistencias en DB entre tablas 'aprende' & 'pokemon' (No existe el pokemon con id:$movement_learned->FK_id_pokemon)",500);
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

    public function insert($req, $res){
    ///---------------------LLEVAR A CONTROLADRO GENERICO--------------------------------------------
        // $updateFields = $req->body;
        // $valid_fields=$this->model->getValid_TableFields();

        // unset($valid_fields['id']);
        // foreach($valid_fields as $field_name => $field_type){
        //     if(!isset($updateFields->$field_name) || empty($updateFields->$field_name)){
        //         return $this->view->response("Falta completar el campo [$field_name] ", 400);
        //     }
        //     if($field_type !== gettype($updateFields->$field_name)){
        //         return $this->view->response("'$field_name' debe ser de tipo [$field_type]", 400);
        //     }
        // }
    ///--------------------------------------------------------------------------------------------

        if(!$res->user) {
            return $this->view->response("No autorizado", 401);
        }

                //  ***************REEMPLAZAR POR LO DE ARRIBA*******************
        $id_pokemon = isset($req->query->id_pokemon) ? $req->query->id_pokemon : null;
        $id_movimiento = isset($req->query->id_movimiento) ? $req->query->id_movimiento : null;
        $nivel_aprendizaje = isset($req->query->nivel_aprendizaje) ? $req->query->nivel_aprendizaje : null;
        // var_dump("pokemon = $id_pokemon","movimiento = $id_movimiento");
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

    public function update($req, $res){
        if(!$res->user) {
            return $this->view->response("No autorizado", 401);
        }

        if(!is_numeric($req->params->id_pok) || !is_numeric($req->params->id_mov)){
            return $this->view->response("El id no puede ser un string", 400);
        }

        $id_pokemon = $req->params->id_pok;
        $id_movimiento = $req->params->id_mov;

        $existe = $this->aprendizaje_model->exists($id_pokemon, $id_movimiento);
        if(!$existe){
            $pokemon = $this->pokemon_model->get($id_pokemon);
            $movimiento = $this->movimiento_model->get($id_movimiento);
            if(!$pokemon && !$movimiento){
                return $this->view->response("No existe el pokemon con id:$id_pokemon ni el movimiento con id:$id_movimiento", 404);
            }
            if(!$pokemon){
                return $this->view->response("No fue posible encontrar el pokemon con id:$id_pokemon", 404);
            }
            if(!$movimiento){
                return $this->view->response("No fue posible encontrar el movimiento con id:$id_movimiento", 404);
            }
            return $this->view->response("No existe la relacion con el pokemon:$id_pokemon y el movimiento:$id_movimiento", 404);
        }

// **********************GENERAZLIZARRRRRRRRRRRRR**************************************************************
        $attributesToUpdate = [];
        if(!empty($req->body->FK_id_pokemon)){
            $pokemoToUpdate = $req->body->FK_id_pokemon;
            if($this->pokemon_model->get($pokemoToUpdate)){
            // if($this->pokemon_model->exists($pokemoToUpdate)){
                $attributesToUpdate['FK_id_pokemon'] = intval($pokemoToUpdate);
            }else{
                return $this->view->response("No existe el pokemon con id:$pokemoToUpdate. Por favor intentelo de nuevo!!", 404);
            }
        }
        if(!empty($req->body->FK_id_movimiento)){
            $movimientoToUpdate = $req->body->FK_id_movimiento;
            if($this->movimiento_model->get($movimientoToUpdate)){
            // if($this->pokemon_model->exists($movimientoToUpdate)){
                $attributesToUpdate['FK_id_movimiento'] = intval($movimientoToUpdate);
            }else{
                return $this->view->response("No existe el movimiento con id:$movimientoToUpdate. Por favor intentelo de nuevo!!", 404);
            }
        }
        if(!empty($req->body->nivel_aprendizaje)){
            $nivelToUpdate = $req->body->nivel_aprendizaje;
            if($nivelToUpdate <= 100){
                $attributesToUpdate['nivel_aprendizaje'] = intval($nivelToUpdate);
            }else{
                return $this->view->response("ERROR: un pokemon no puedo aprender movimientos por encima del nivel 100", 404);
            }
        }
// **********************************************************************************************************************

        $update = null;
        if(!empty($attributesToUpdate)){
            $update= $this->aprendizaje_model->update($id_pokemon, $id_movimiento, $attributesToUpdate);
        }

        if(!empty($update)){
            $aprendizajeActualizado = $this->aprendizaje_model->get($id_pokemon, $id_movimiento);
            $this->view->response($aprendizajeActualizado);
        }else{
            $this->view->response("No fue posible actualizar la relacion Aprendizaje para el pokemon:$id_pokemon y el movimiento:$id_movimiento",404);
        }
    }

}

