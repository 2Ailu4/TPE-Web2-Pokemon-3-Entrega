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

    public function NotFound($req, $res){
        $this->view->response("Pagina no encontrada", 404);
    }

    public function getAll($req, $res){

        $filters = []; $sorts = []; $limit=null; $page=1;
        if($req->query !== null){
            $validQueries = $this->getValid_Sorts_And_Filters($req->query,$this->aprendizaje_model->getQueryFields()); 
            $sorts = $validQueries['sorts'];
            $filters = $validQueries['filters'];
            $page = $validQueries['page'];
            $limit = $validQueries['limit'];
            var_dump("VALIDDDD", $validQueries);

            if($validQueries['invalid_filters'] > 0 && $validQueries['invalid_sorts'] > 0){
                return $this->view->invalid_params_response('"filtros y ordenamientos"');
            }else{
                if($validQueries['invalid_sorts'] > 0){
                    return $this->view->invalid_params_response("'ordenamientos'");
                }else{
                    if($validQueries['invalid_filters'] > 0) { 
                        return $this->view->invalid_params_response("'filtros'");
                    }
                }
            } 
        }
       
        if(($page > 1) && !isset($limit)){
            $limit = 10;    // valor por defecto
        }

        if(!is_numeric($page) || !($page > 0)){
            if(!is_numeric($page)) 
                return $this->view->response("Page debe ser de tipo numerico",400);
            return $this->view->response("Solo es posible paginar por numeros mayores a 0",400);
        }else{ $page = intval($page);}

        if(isset($limit) ){
            if(!is_numeric($limit)){
                return $this->view->response("Limit debe ser de tipo numerico",400);
            }else
                $limit = empty($limit) ? 10: intval($limit);
               
        }
          
        $relaciones  = $this->aprendizaje_model->getAll($filters, $sorts,$page, $limit, true);
        if(!$relaciones){
            return $this->view->response("No se encontraron coincidencias para la busqueda", 404);
        }
        if(!isset($filters['id_pokemon']) && isset($filters['id_movimiento']) ){
            $result = $this->generate_Learning_List_By_Movement($relaciones); 
        }else
            $result = $this->generate_Learning_List($relaciones); 
         
        $this->view->response($result);
    }


    public function generate_Learning_List($relaciones){
        $result = [];
        foreach($relaciones as $movement_learned){
            $movement = $this->movimiento_model->get($movement_learned->FK_id_movimiento);
            $movement->nivel_aprendizaje = $movement_learned->nivel_aprendizaje;
        
            if(!$movement){
                $this->view->response("Inconsistencias en DB entre tablas 'aprende' & 'movimiento' (No existe el movimiento con id:$movement_learned->FK_id_movimiento) ", 500);
                die();
            }
        
            $pokemon_index = array_search($movement_learned->FK_id_pokemon, array_column($result, 'id'));
        
            if ($pokemon_index !== false) {
                array_push($result[$pokemon_index]->movimientos, $movement);
            } else {
                $pokemon = $this->pokemon_model->get($movement_learned->FK_id_pokemon); // Obtengo el POKEMON de la DB
                if (!$pokemon) {
                    $this->view->response("Inconsistencias en DB entre tablas 'aprende' & 'pokemon' (No existe el pokemon con id:$movement_learned->FK_id_pokemon)", 500);
                    die();
                }
        
                $pokemon->movimientos = [$movement]; // Inicializo el arreglo de movimientos con el primer movimiento
                $result[] = $pokemon; // Agrego el Pokémon al arreglo sin clave
            }
        }
        return $result;
    }

    public function generate_Learning_List_By_Movement($relaciones){
        $result = [];

        $movement = $this->movimiento_model->get($relaciones[0]->FK_id_movimiento);
        $movement->pokemons = []; 
        if(!$movement){
            $this->view->response("Inconsistencias en DB entre tablas 'aprende' & 'movimiento' (No existe el movimiento con id:$movement->id_movimiento) ", 500);
            die();
        }

        foreach($relaciones as $movement_learned){

            $pokemon = $this->pokemon_model->get($movement_learned->FK_id_pokemon); 
            if (!$pokemon) {
                $this->view->response("Inconsistencias en DB entre tablas 'aprende' & 'pokemon' (No existe el pokemon con id:$movement_learned->FK_id_pokemon)", 500);
                die();
            }
            $pokemon->nivel_aprendizaje = $movement_learned->nivel_aprendizaje;
            array_push($movement->pokemons, $pokemon);
        }
        $result[] = $movement; 
       
        return $result;
    }


    public function insert($req, $res){
        if(!$res->user) {
            return $this->view->response("No autorizado", 401);
        }

        $id_pokemon = isset($req->body->id_pokemon) ? $req->body->id_pokemon : null;
        $id_movimiento = isset($req->body->id_movimiento) ? $req->body->id_movimiento : null;
        $nivel_aprendizaje = isset($req->body->nivel_aprendizaje) ? $req->body->nivel_aprendizaje : null;
        
        if(empty($id_pokemon)){       return $this->view->requirementError_response('id_pokemon');}
        if(empty($id_movimiento)){    return $this->view->requirementError_response('id_movimiento'); }
        if(empty($nivel_aprendizaje)){return $this->view->requirementError_response('campo nivel_aprendizaje');}
        
        if(!is_numeric($id_pokemon)){       return $this->view->typeError_response('id_pokemon','numerico');}
        if(!is_numeric($id_movimiento)){    return $this->view->typeError_response('id_movimiento','numerico');}
        if(!is_numeric($nivel_aprendizaje)){return $this->view->typeError_response('nivel_aprendizaje','numerico');}

        if($nivel_aprendizaje <1 || $nivel_aprendizaje>100){return $this->view->response("ERROR: rango de aprednizaje debe encontrarse entre:[1-100]", 404);}

        if(!($this->pokemon_model->exists($id_movimiento))){return $this->view->existence_Error_response('Movimiento', $id_movimiento);}
        if(!($this->movimiento_model->exists($id_pokemon))){return $this->view->existence_Error_response('Pokemon', $id_pokemon);}

        $pokemon = $this->pokemon_model->get($id_pokemon);
        $movimiento = $this->movimiento_model->get($id_movimiento);

        if(!$pokemon){ return $this->view->existence_Error_response("Pokemon", $id_pokemon);}
        if(!$movimiento){return $this->view->existence_Error_response("Movimiento", $id_movimiento);}

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


    public function get($req, $res){ 
        $id_pokemon = is_numeric($req->params->id_pok)    ? intval($req->params->id_pok) : null;
        $id_movimiento = is_numeric($req->params->id_mov) ? intval($req->params->id_mov) : null;
       
        if(!($id_pokemon > 0))
            return $this->view->typeError_response("id_movimiento", "[Naturales >0]");
        if(!($id_movimiento > 0))
            return $this->view->typeError_response("id_pokemon", "[Naturales >0]");
   
        $exists_empty_params = $this->exists_empty_params([$id_pokemon, $id_movimiento]);
        if($exists_empty_params){ 
             $this->view->invalid_parms_type_response("entero");
            die();
        }
        $this->check_rows_existence_on_tables($id_pokemon, $id_movimiento);
        
        $aprendizaje = $this->aprendizaje_model->get($id_pokemon,$id_movimiento);
        
        if(!$aprendizaje) return $this->view->server_Error_response();

        $movement = $this->movimiento_model->get($aprendizaje->FK_id_movimiento);
        $movement->nivel_aprendizaje = $aprendizaje->nivel_aprendizaje;

        $pokemon = $this->pokemon_model->get($aprendizaje->FK_id_pokemon);
        $pokemon->movimiento = $movement;
         
        $this->view->response($pokemon);
        
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

        $attributesToUpdate = [];
        if(!empty($req->body->id_pokemon)){
            $pokemoToUpdate = $req->body->id_pokemon;
            if($this->pokemon_model->exists($pokemoToUpdate)){
                $attributesToUpdate['FK_id_pokemon'] = intval($pokemoToUpdate);
            }else{
                return $this->view->response("No existe el pokemon con id:$pokemoToUpdate. Por favor intentelo de nuevo!!", 404);
            }
        }
        if(!empty($req->body->id_movimiento)){
            $movimientoToUpdate = $req->body->id_movimiento;
            if($this->pokemon_model->exists($movimientoToUpdate)){
                $attributesToUpdate['FK_id_movimiento'] = intval($movimientoToUpdate);
            }else{
                return $this->view->response("No existe el movimiento con id:$movimientoToUpdate. Por favor intentelo de nuevo!!", 404);
            }
        }
        if(!empty($req->body->nivel_aprendizaje)){
            $nivelToUpdate = $req->body->nivel_aprendizaje;
            if($nivelToUpdate <= 100 && $nivelToUpdate > 0){
                $attributesToUpdate['nivel_aprendizaje'] = intval($nivelToUpdate);
            }else{
                return $this->view->response("ERROR: rango de aprednizaje debe encontrarse entre:[1-100]", 404);
            }
        }

        $new_id_pokemon =isset($attributesToUpdate ['FK_id_pokemon']) ? $attributesToUpdate['FK_id_pokemon'] : $id_pokemon;
        $new_id_movimiento =isset($attributesToUpdate ['FK_id_movimiento']) ? $attributesToUpdate['FK_id_movimiento'] : $id_movimiento;
        $allreadyExists = $this->aprendizaje_model->exists($new_id_pokemon, $new_id_movimiento);
        if($allreadyExists){ return $this->view->response(" Entrada duplicada :la relacion entre pokemon:$new_id_pokemon y el movimiento:$new_id_movimiento ya existe",404);}

        $update = null;
        if(!empty($attributesToUpdate)){
            $update= $this->aprendizaje_model->update($id_pokemon, $id_movimiento, $attributesToUpdate);
        }

        if(!empty($update)){
            $aprendizajeActualizado = $this->aprendizaje_model->get($new_id_pokemon, $new_id_movimiento); 
            $aprendizaje = new stdClass;
            $aprendizaje->id_pokemon = $aprendizajeActualizado->FK_id_pokemon;
            $aprendizaje->id_movimiento = $aprendizajeActualizado->FK_id_movimiento;   
            $aprendizaje->nivel_aprendizaje = $aprendizajeActualizado->nivel_aprendizaje;
            return $this->view->response($aprendizaje);
        }else{
            return  $this->view->response("No fue posible actualizar la relacion Aprendizaje para el pokemon:$id_pokemon y el movimiento:$id_movimiento",404);
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
                  $this->view->existence_Error_response_Aprendizaje($id_pokemon, $id_movimiento); 
                  die(); 
            }
            if(!$pokemon){
                  $this->view->existence_Error_response('Pokemon', $id_pokemon);  
                  die();                
            }
            if(!$movimiento){
                  $this->view->existence_Error_response('Movimiento', $id_movimiento);
                  die();             
            }
            $this->view->unlinked_Warning_response($id_pokemon, $id_movimiento);                
            die();
        }  
         
    }

    private function getValid_Sorts_And_Filters($query_params, $resource_query_fields){//, $resource_sort_fields){ // ?nombre=sasa&peso=dasdas&fecha_captura='121212'&sort_nombre_movimiento=ASC
        $params = clone $query_params;
         
        unset($params->resource);
         
        $filters = [];  
        $sorts = [];  
        $limit = null;
        $page = 1;
        $invalid_filters = 0;
        $invalid_sorts = 0;
         
        foreach($params as $param_name => $value){          // separa query-params de ordenamiento y de filtro
            if(stripos($param_name,"sort_") === 0) {                // [case-insensitive]: sort_nombre_movimiento  coincide 's' de "sort_" en posicion 0 de param_name https://www.php.net/manual/en/function.stripos.php
                if (strtoupper($param_name) !=='ID_ENTRENADOR'){ echo"fallo id";}
                if (!str_contains(strtoupper($param_name),"ID_ENTRENADOR") && str_contains(strtoupper($param_name), 'ID')) {
                    continue;  // pasa al siguiente elemento del for
                }
                //$param_name = sort_nombre_movimiento=desc  ==> nombre_movimiento=desc
                $field = substr($param_name,strlen("sort_"));           // toma el sub-string a partir del string que se quiere remover a partir de la posicion 0
                      
                $curr_table='';
                foreach($resource_query_fields as $table_name =>$values){//recorre los campos de las tres tablas
                    if(empty($curr_table) && isset($resource_query_fields[$table_name][$field]))//si el campo ingresado coincide con alguno de los campos de las tablas termina
                        $curr_table = $table_name;
                }
                if(empty($curr_table)) {  //no encontro coincidencia, el sort ingresado no es parte de los campos de las tablas 
                    $invalid_sorts++; 
                }else {
                    $orderBy = $curr_table . '.' . strtolower("$field");
                    if(strtoupper($value) === 'DESC'){
                        $sorts[$field] = $orderBy." DESC" ;
                    }else $sorts[$field] = $orderBy." ASC";   
                }
            }else{
                var_dump("111111",$param_name ,$value);
                if(str_contains(strtoupper($param_name) , 'LIMIT') || (strtoupper($param_name) === 'L')){
                    $limit = $value;
                }else{
                    if(str_contains(strtoupper($param_name) , 'PAGE') || (strtoupper($param_name) === 'P')){
                        $page = $value;
                    }else{
                        if(!isset($resource_query_fields['pokemon'][$param_name]) && !isset($resource_query_fields['movimiento'][$param_name]) && !isset($resource_query_fields['aprendizaje'][$param_name])) {
                            $invalid_filters++;   //el filtro ingresado no coincide con ningun campo de las tablas 
                        }else{ 
                            var_dump("22222", $param_name,$value);
                            if(strtolower($param_name)==="fecha_captura"){
                                $fecha = DateTime::createFromFormat('d/m/Y', $value);

                                // Convertir la fecha al formato yyyy-mm-dd
                                //$fecha_mysql = $fecha->format('m-d-Y');
                                 
                                // var_dump($fecha->format('m/d/Y'));
                                $filters[$param_name] = $fecha->format('m/d/Y');

                            }else {
                                $filters[$param_name] = $value;
                                var_dump($filters);
                            }
                        }
                    } 
                }
            }
        }
        // var_dump( ['filters' => $filters, 'sorts'=> $sorts, 'limit' => $limit, 'page' => $page, 'invalid_filters' => $invalid_filters, 'invalid_sorts' => $invalid_sorts]);

        return ['filters' => $filters, 'sorts'=> $sorts, 'limit' => $limit, 'page' => $page, 'invalid_filters' => $invalid_filters, 'invalid_sorts' => $invalid_sorts];
    }

}