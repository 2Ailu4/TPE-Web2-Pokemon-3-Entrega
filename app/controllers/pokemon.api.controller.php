<?php
require_once './app/models/pokemon.model.php';
require_once './app/models/entrenador.model.php';
require_once './app/views/json.view.php';

class PokemonApiController{
    private $model;
    private $entrenador_model;
    private $view;

    public function __construct(){
        $this->model = new PokemonModel();
        $this->entrenador_model = new EntrenadorModel();
        $this->view = new JSONView();
    }

    public function getAll($req, $res){
        $pokemons = $this->model->getAll();
        if(!$pokemons){
            return $this->view->response("No se encontraron filas en la tabla Pokemon", 404);
        }
        return $this->view->response($pokemons);
    }


    public function get($req, $res){
        $id_pok = $req->params->id_pok;
        $pokemon = $this->model->get($id_pok);
        if(!$pokemon){
            return $this->view->response("No existe el pokmeon con id:$id_pok", 404);
        }
        return $this->view->response($pokemon);
    }


    public function update($req, $res){
        $id_Pokemon = $req->params->id_pok;
        $pokemon = $this->model->get($id_Pokemon);
        if(!$pokemon){
            return $this->view->response("No existe el pokemon con id:$id_Pokemon", 404);
        }
        
        $attributesToUpdate = [];

        $updateFields = $req->body;
        $fields_to_verify = $this->model->getValid_TableFields();

        foreach ($updateFields as $field => $value){   
            if($field !== "FK_id_entrenador"){
                if (!isset($fields_to_verify[$field])){
                    return $this->view->response("'$field' es un campo invalido",400);
                }
                if($fields_to_verify[$field] !== gettype($value)){
                    return $this->view->response("'$field' debe ser de tipo $fields_to_verify[$field]", 400);
                }
            }
            
            // Guardo en $attributesToUpdate solo los campos que se modificaron
            if(!empty($req->body->$field) || $field === 'FK_id_entrenador'  ){ 
                if($value !== $pokemon->$field){   // verifica si ingreso el mismo valor que el actual al campo a modificar
                    $attributesToUpdate[$field] = $value;   
                } 
            }
        }
        $attributesToUpdate['id'] = $id_Pokemon; 
        $updated = $this->updatePokemonsFields($pokemon, $attributesToUpdate);
        
        $update_attributes=[];
        $update_attributes= $this->add_existent_keys(['peso','fecha_captura','FK_id_entrenador'],$attributesToUpdate);
        if (isset($update_attributes['FK_id_entrenador']) && $update_attributes['FK_id_entrenador'] === "NULL") { $update_attributes['FK_id_entrenador']= NULL;}
       
        foreach($update_attributes as $field => $value){
            $updated[$field] = $value;
        }
        if($updated){
            $this->view->response('Se modificaron los siguientes campos:');
            $this->view->response($updated);
        }
        if($update_attributes){
            $this->model->updatePokemon($id_Pokemon, $update_attributes);
        }
    }




    public function updatePokemonsFields($pokemon, $updateFields){
        
        $updated=[];
        $pokemonByName=NULL;
        $pokemonByNroPokedex=NULL;

        $nro_pokedex_for_update_Type = "";

        $nombre_modificado = false;
        $nro_Pokedex_modificado = false;

        if(array_key_exists('nombre',$updateFields)){ 
            $pokemonByName = $this->model->getPokemonByName($updateFields['nombre']);  //nro,tipo
            $nombre_modificado=true;
        }
        if(array_key_exists('nro_pokedex',$updateFields)){ 
            $pokemonByNroPokedex = $this->model->getPokemonByNroPokedex($updateFields['nro_pokedex']);
            $nro_Pokedex_modificado=true;
        }
        
        if($nro_Pokedex_modificado && $nombre_modificado){  //se modificaron los dos

            if ($this->exists($pokemonByName) && $this->exists($pokemonByNroPokedex)){  //existen ambos en la db
                if($updateFields['nombre'] === $pokemonByNroPokedex->nombre){  // verifico que coincidan el nombre y el nombre del nro de pokedex (se puede consultar x nro_pok)
                    // al pok con id le modifica el nro y el nombre, y le arrastra el tipo asociado
                    $this->model->updateNroNameType_By_ID($updateFields['id'], $updateFields['nro_pokedex'], $updateFields['nombre'], $pokemonByNroPokedex->tipo); //(WHERE id)
                    $nro_pokedex_for_update_Type = $updateFields['nro_pokedex'];
                }
                else{  //existen el nro y el nombre pero estos no coinciden (si falla actualiza solo los campos validos que modifico)
                    return $this->view->response("No es posible actualizar el nombre del pokemon ".$updateFields['nombre'].", ya que el nombre asociado a Nro_Pokedex: ".$updateFields['nro_pokedex']." cuenta con otro nombre", 500);
                }
            }else{  //el nuevo nombre y nro no existen en la db === no hay conflicto para actualizar
                if(!($this->exists($pokemonByName)) && !($this->exists($pokemonByNroPokedex))){  
                    // al pok con id le modifico ese nuevo nombre y nuevo nro
                    $this->model->updateNroNameType_By_ID($updateFields['id'], $updateFields['nro_pokedex'], $updateFields['nombre'], $pokemon->tipo);  //(WHERE id)
                }
                else{  //nro_pokedex existe en la db pero el nombre no
                    if(!$this->exists($pokemonByName) && $this->exists($pokemonByNroPokedex)){    
                        // 1° al pok con id le asigna el nuevo nro y por arrastre le cambia el nombre y tipo
                        $this->model->updateNroNameType_By_ID($updateFields['id'], $updateFields['nro_pokedex'], $pokemonByNroPokedex->nombre, $pokemonByNroPokedex->tipo); //(WHERE id)
                        // 2° a todos los pok con ese nuevo nro_pok existente en la db les cambio el nombre
                        $this->model->updateName_BY_nro_Pokedex($updateFields['nombre'], $updateFields['nro_pokedex']);
                    }
                    else{  //nombre existe en la db pero nro_pokedex no
                        // 1° al pok con id le asigna el nuevo nombre y por arrastre le cambia el nro y tipo
                        $this->model->updateNroNameType_By_ID($updateFields['id'], $pokemonByName->nro_pokedex, $updateFields['nombre'], $pokemonByName->tipo); //(WHERE id)
                        // 2° a todos los pok con ese nuevo nombre existente en la db les cambio el nro_pok
                        $this->model->update_Nro_Pokedex($updateFields['nro_pokedex'], $pokemonByName->nro_pokedex);
                    }
                }                
                $nro_pokedex_for_update_Type = $updateFields['nro_pokedex'];
            }
            $updated['nro_pokedex']=$nro_pokedex_for_update_Type;
            $updated['nombre']=$updateFields['nombre'];
        }else{
            if($nro_Pokedex_modificado){ //si se modifico el nro_pokedex
                if($this->exists($pokemonByNroPokedex)){   // y existe el nro en la db
                    // al pok con id le cambio el nro y por arrastre le cambio el nombre y tipo
                    $this->model->updateNroNameType_By_ID($updateFields['id'], $updateFields['nro_pokedex'], $pokemonByNroPokedex->nombre, $pokemonByNroPokedex->tipo);  //(WHERE id)
                }
                else{  // si no existe el nro en la db
                    // a todos los pokemons con ese nro se los reemplazo por el nuevo nro
                    $this->model->update_Nro_Pokedex($updateFields['nro_pokedex'], $pokemon->nro_pokedex);
                }
                $nro_pokedex_for_update_Type = $updateFields['nro_pokedex'];
                $updated['nro_pokedex']=$nro_pokedex_for_update_Type;
            }
            if($nombre_modificado){ // si se modifico el nombre
                if($this->exists($pokemonByName)){  //y existe el nombre en la db
                    // al pok con id le cambio el nombre y por arrastre le cambio el nro y tipo
                    $this->model->updateNroNameType_By_ID($updateFields['id'], $pokemonByName->nro_pokedex, $updateFields['nombre'], $pokemonByName->tipo); //(WHERE id)
                    $nro_pokedex_for_update_Type = $pokemonByName->nro_pokedex;
                }
                else{  //si no existe el nombre en la db
                    // a todos los pokemons con ese nombre se los reemplazo por el nuevo nombre
                    $this->model->updateName_BY_nro_Pokedex($updateFields['nombre'], $pokemon->nro_pokedex);
                    $nro_pokedex_for_update_Type = $pokemon->nro_pokedex;
                }
                $updated['nombre']=$updateFields['nombre'];
            }
        }

        if(array_key_exists('tipo',$updateFields)){ 
            $type_updated = $this->model->updateType_BY_nro_Pokedex($nro_pokedex_for_update_Type, $updateFields['tipo']);
            $updated['tipo']=$updateFields['tipo'];
        }

        return $updated;
    }

    private function exists($var){
        $setted = false;
        if(isset($var) && !empty($var)) {return $setted =true;}
        return $setted;
    }

    
    private function add_existent_keys($keys_arr, $arr){
        $arr_to_insert = [];
        foreach($keys_arr as $key_arr){
            if(array_key_exists($key_arr, $arr)){
                $arr_to_insert[$key_arr] = $arr[$key_arr];
            }
        }
        return $arr_to_insert;
    }



    public function insert($req, $res){
    ///---------------------LLEVAR A CONTROLADRO GENERICO--------------------------------------------
        $updateFields = $req->body;
        $valid_fields=$this->model->getValid_TableFields();

        unset($valid_fields['id']);
        foreach($valid_fields as $field_name => $field_type){
            if($updateFields->$field_name !== "FK_id_entrenador"){
                if(!isset($updateFields->$field_name) || empty($updateFields->$field_name)){
                    return $this->view->response("Falta completar el campo [$field_name] ", 400);
                }
                if($field_type !== gettype($updateFields->$field_name)){
                    return $this->view->response("'$field_name' debe ser de tipo [$field_type]", 400);
                }
            }
        }
    ///--------------------------------------------------------------------------------------------

        $id_entrenador = null; 
        $id_entr = $updateFields->FK_id_entrenador;
        if(!empty($id_entr) && $this->entrenador_model->getTrainer($id_entr)){
                $id_entrenador = $id_entr;
        }

        $nro_pokedex = $updateFields->nro_pokedex;
        $nombre_pokemon = $updateFields->nombre;
        $tipo_pokemon = $updateFields->tipo;
        $peso = $updateFields->peso;
         
        $nroPokedexExists = $this->model->countNroPokedex($nro_pokedex);

        if($nroPokedexExists > 0){  //si ya existe el nro_pokedex
            $pokemonInDB = $this->model->getPokemonByNroPokedex($nro_pokedex);
     
            if(($pokemonInDB->nombre === $nombre_pokemon) && ($pokemonInDB->tipo === $tipo_pokemon)){ 
                if($id_entrenador === "NULL"){
                    $id_New_Pokemon = $this->model->insertPokemon($nro_pokedex, $nombre_pokemon, $tipo_pokemon, $peso,null);
                    $this->showNewPokemon($id_New_Pokemon);
                }else{
                    $id_New_Pokemon = $this->model->insertPokemon($nro_pokedex, $nombre_pokemon, $tipo_pokemon, $peso, $id_entrenador);
                    $this->showNewPokemon($id_New_Pokemon);
                }
            }else{  //si el nombre o el tipo NO coinciden
                $this->view->response("Lo sentimos el pokemon con Numero de Pokedex: ".$nro_pokedex." ya existe, pero el nombre y el tipo ingresados no coinciden con el cargado en la base de datos", 500);
            }
        }else{  //si el nro_pokedex no existe en la DB
            if($id_entrenador === "NULL"){
                $id_New_Pokemon = $this->model->insertPokemon($nro_pokedex, $nombre_pokemon, $tipo_pokemon, $peso, null);
                $this->showNewPokemon($id_New_Pokemon);
            }else{
                $id_New_Pokemon = $this->model->insertPokemon($nro_pokedex, $nombre_pokemon, $tipo_pokemon, $peso, $id_entrenador);
                $this->showNewPokemon($id_New_Pokemon);
            }
        }
    }

    
    public function showNewPokemon($id_New_Pokemon){
        $new_pokemon = $this->model->get($id_New_Pokemon);
        $this->view->response("Se agrego correctamente el nuevo pokemon: ");
        $this->view->response($new_pokemon);
    }
}