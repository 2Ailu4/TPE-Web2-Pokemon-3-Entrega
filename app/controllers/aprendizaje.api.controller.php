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

        $filters = []; $sorts = [];
        if($req->query !== null){
            $sortsANDfilters = $this->getValid_Sorts_And_Filters($req->query,$this->aprendizaje_model->getQueryFields()); 
            $sorts = $sortsANDfilters['sorts'];
            $filters = $sortsANDfilters['filters'];
            if($sortsANDfilters['invalid_filters'] > 0 && $sortsANDfilters['invalid_sorts'] > 0){
                $this->view->invalid_params_response('"filtros y ordenamientos"');
            }
            if($sortsANDfilters['invalid_sorts'] > 0)   {$this->view->invalid_params_response("'ordenamientos'");}
            if($sortsANDfilters['invalid_filters'] > 0) {$this->view->invalid_params_response("'filtros'");}
        } 
        $page = isset($req->query->page) ? $req->query->page : null;
        $limit  = isset($req->query->limit) ? $req->query->limit: null;
        $offset  = isset($req->query->offset) ? $req->query->offset: null;
         
        $relaciones = $this->aprendizaje_model->getAll($filters, $sorts, $limit,$page,$offset);

        if(!$relaciones){return $this->view->response("No se encontraron coincidencias para la busqueda", 404);}

        $result = [];
        foreach($relaciones as $movement_learned){
            $movement = $this->movimiento_model->get($movement_learned->FK_id_movimiento);
            $movement->nivel_aprendizaje = $movement_learned->nivel_aprendizaje;
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



    public function get($req, $res){
        // id:,id_mov me da un (id_p,id_mov,niv_pok) ==> pokemon . movimiento (info del movimiento) . niv_aprendizaje
        
        $id_pokemon = is_numeric($req->params->id_pok)    ? intval($req->params->id_pok) : null;
        $id_movimiento = is_numeric($req->params->id_mov) ? intval($req->params->id_mov) : null;
        
        $exists_empty_params = $this->exists_empty_params([$id_pokemon, $id_movimiento]);
        if($exists_empty_params){ 
            return $this->view->invalid_parms_type_response("entero");
            die();
        }
        $this->check_rows_existence_on_tables($id_pokemon, $id_movimiento);
        
        $aprendizaje = $this->aprendizaje_model->get($id_pokemon,$id_movimiento);
        
        if($aprendizaje) $this->view->server_Error_response();

        $movement = $this->movimiento_model->get($aprendizaje->FK_id_movimiento);
        $movement->nivel_aprendizaje = $aprendizaje->nivel_aprendizaje;

        $pokemon = $this->pokemon_model->get($aprendizaje->FK_id_pokemon);
        $pokemon->movimiento = $movement;
         
        $this->view->response($pokemon);
        
    }

    public function insert($req, $res){
        var_dump($req->body);
        $id_pokemon = isset($req->body->id_pokemon) ? $req->body->id_pokemon : null;
        $id_movimiento = isset($req->body->id_movimiento) ? $req->body->id_movimiento : null;
        $nivel_aprendizaje = isset($req->body->nivel_aprendizaje) ? $req->body->nivel_aprendizaje : null;
         
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
        if(!intval($req->params->id_pok) || !intval($req->params->id_mov)){
            return $this->view->response("El id no puede ser un string", 400);
        }

        $id_pokemon = $req->params->id_pok;
        $id_movimiento = $req->params->id_mov;

        $existe = $this->aprendizaje_model->exists($id_pokemon, $id_movimiento);
        if(!$existe){
            $pokemon = $this->pokemon_model->get($id_pokemon);
            $movimiento = $this->movimiento_model->get($id_movimiento);
            if(!$pokemon && !$movimiento){
                return $this->view->response("No existe el pokemon con id = $id_pokemon ni el movimiento con id = $id_movimiento", 404);
            }
            if(!$pokemon){
                return $this->view->response("No fue posible encontrar el pokemon con id=$id_pokemon", 404);
            }
            if(!$movimiento){
                return $this->view->response("No fue posible encontrar el movimiento con id=$id_movimiento", 404);
            }
            return $this->view->response("No existe la relacion con el pokemon=$id_pokemon y el movimiento=$id_movimiento", 404);
        }

        $attributesToUpdate = [];
        if(!empty($req->body->FK_id_pokemon)){
            $pokemoToUpdate = $req->body->FK_id_pokemon;
            if($this->pokemon_model->get($pokemoToUpdate)){
            // if($this->pokemon_model->exists($pokemoToUpdate)){
                $attributesToUpdate['FK_id_pokemon'] = intval($pokemoToUpdate);
            }else{
                return $this->view->response("No existe el pokemon con id= $pokemoToUpdate el cual reemplace al actual", 404);
            }
        }
        if(!empty($req->body->FK_id_movimiento)){
            $movimientoToUpdate = $req->body->FK_id_movimiento;
            if($this->movimiento_model->get($movimientoToUpdate)){
            // if($this->pokemon_model->exists($movimientoToUpdate)){
                $attributesToUpdate['FK_id_movimiento'] = intval($movimientoToUpdate);
            }else{
                return $this->view->response("No existe el movimiento con id= $movimientoToUpdate el cual reemplace al actual", 404);
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

        $update = null;
        if(!empty($attributesToUpdate)){
            $update= $this->aprendizaje_model->update($id_pokemon, $id_movimiento, $attributesToUpdate);
        }

        if(!empty($update)){
            $aprendizajeActualizado = $this->aprendizaje_model->get($id_pokemon, $id_movimiento);
            $this->view->response($aprendizajeActualizado);
        }else{
            $this->view->response("No fue posible actualizar la relacion aprendizaje");
        }
    }

    private function exists_empty_params($params){
        foreach($params as $param)
            if(empty($param)) 
                return true;
        return false;
    }
    private function check_rows_existence_on_tables($id_pokemon,$id_movimiento){
        //chequea si el pokemon, movimiento y la relacion

        $exists = $this->aprendizaje_model->exists($id_pokemon, $id_movimiento);

        if(!$exists){
            $pokemon = $this->pokemon_model->get($id_pokemon);
            $movimiento = $this->movimiento_model->get($id_movimiento);
            if(!$pokemon && !$movimiento){
                return $this->view->existence_Error_response_Aprendizaje($id_pokemon, $id_movimiento);  
            }
            if(!$pokemon){
                return $this->view->existence_Error_response('Pokemon', $id_pokemon);                  
            }
            if(!$movimiento){
                return $this->view->existence_Error_response('Movimiento', $id_movimiento);             
            }
            return $this->view->unlinked_Warning_response($id_pokemon, $id_movimiento);                
            die();
        }  
         
    }

    private function getValid_Sorts_And_Filters($query_params, $resource_query_fields){//, $resource_sort_fields){ // ?nombre=sasa&peso=dasdas&fecha_captura='121212'&sort_nombre_movimiento=ASC
        $params = clone $query_params;
         
                
        unset($params->resource);
         
        $filters = [];  
        $sorts = [];  
        $invalid_filters = 0;
        $invalid_sorts = 0;
         
        foreach($params as $param_name => $value){          // separa query-params de ordenamiento y de filtro
            var_dump("param name   ",$param_name);
            if(stripos($param_name,"sort_") === 0) {                // [case-insensitive]: sort_nombre_movimiento  coincide 's' de "sort_" en posicion 0 de param_name https://www.php.net/manual/en/function.stripos.php
                
                if (str_contains(strtoupper($param_name), 'ID')) {
                    continue;  // pasa al siguiente elemento del for
                }
                //$param_name = sort_nombre_movimiento=desc  ==> nombre_movimiento=desc
                $field = substr($param_name,strlen("sort_"));           // toma el sub-string a partir del string que se quiere remover a partir de la posicion 0
                      
                $curr_table='';
                foreach($resource_query_fields as $table_name =>$values){
                    var_dump($table_name);
                    if(empty($curr_table) && isset($resource_query_fields[$table_name][$field]))
                        $curr_table = $table_name;
                }
                if(empty($curr_table)) {  
                    $invalid_sorts++; 
                }else {
                    $orderBy = $curr_table . '.' . "$field";
                    if(strtoupper($value) === 'DESC'){
                        $sorts[$field] = $orderBy." DESC" ;
                    }else $sorts[$field] = $orderBy." ASC";   
                }
            }else{
                if(!isset($resource_query_fields['pokemon'][$param_name]) && !isset($resource_query_fields['movimiento'][$param_name]) && !isset($resource_query_fields['aprendizaje'][$param_name])) {
                  $invalid_filters++; var_dump($param_name);
                }else 
                    $filters[$param_name] = $value;
            }
        } // filters = [nombre=>Bulbasaur , tipo=>fuego,fecha_captura=>2024]
          // sorts = [nombre=ASC,tipo=>DESC]
          var_dump($sorts);
        return ['filters' => $filters, 'sorts'=> $sorts, 'invalid_filters' => $invalid_filters, 'invalid_sorts' => $invalid_sorts];

    }
}

