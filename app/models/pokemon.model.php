<?php
require_once './config/config.php';

class PokemonModel {
    private $db;

    public function __construct() {
        $this->db = new PDO("mysql:host=".MYSQL_HOST .
                            ";dbname=".MYSQL_DB.";charset=utf8", 
                            MYSQL_USER, MYSQL_PASS);
    }
    
    public function exists($id){
        $query = $this->db->prepare('SELECT 1 FROM pokemon WHERE id=?');
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_OBJ);
    }

    public function getAll(){
        $query = $this->db->prepare('SELECT * FROM pokemon');
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
    
    public function get($id){
        $query = $this->db->prepare('SELECT nro_pokedex, nombre, tipo, fecha_captura, peso, FK_id_entrenador, imagen_pokemon 
                                     FROM pokemon WHERE id=?');
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_OBJ);
    }


    public function getValid_TableFields(){
        $fieldsMap = [ 'id' => null, 'nro_pokedex' => null, 'nombre' => null,'tipo' => null,'peso' => null,'FK_id_entrenador' => null];
        foreach ($fieldsMap as $table_column => $value) {
                $fieldsMap[$table_column] = $this->getParamType($table_column);  
            }
        return $fieldsMap;
    }

/// mover a :  class Controlador::  --------------------------------------------------------------------  
    private function getParamType($tableColumn){
        // Subcadenas para filtrar
        $numericSubStrings = ["id", "nro", "nivel", "precision", "peso","poder"]; 
        $dateSubStrings = ["fecha"];
        
        //expresiones regulares (`i` al final para que sea case-insensitive)
        $numericPattern = "/(" . implode("|", $numericSubStrings) . ")/i";  
        $datePattern = "/(" . implode("|", $dateSubStrings) . ")/i";      

        if (preg_match($numericPattern, $tableColumn)) {return "integer";}    
        if (preg_match($datePattern, $tableColumn)) {return "date";}
        return "string"; 
    }
// ---------------------------------------------------------------------------------------------------------

    public function countNroPokedex($nro_Pokedex){
        $query = $this->db->prepare('SELECT COUNT(*) FROM pokemon WHERE nro_pokedex= ?');
        $query->execute([$nro_Pokedex]);
        return $query->fetchColumn();
    }

    public function getPokemonByNroPokedex($nroPokedex){
        $query = $this->db->prepare('SELECT nombre, tipo, imagen_pokemon FROM pokemon WHERE nro_pokedex=?');
        $query->execute([$nroPokedex]);

        $pokemon = $query->fetch(PDO::FETCH_OBJ);
        return $pokemon;
    }


    public function insertPokemon($nro_pokedex, $nombre, $tipo, $peso, $entrenador=null){
        $query = $this->db->prepare('INSERT INTO pokemon(nro_pokedex, nombre, tipo, peso, FK_id_entrenador) 
                                            VALUES (?, ?, ?, ?, ?)');
        $query->execute([$nro_pokedex, $nombre, $tipo, $peso, $entrenador]);

        return $this->db->lastInsertId();
    }


    public function getPokemonByName($name){
        $query = $this->db->prepare('SELECT nro_pokedex, tipo FROM pokemon WHERE nombre LIKE ?');
        $query->execute([$name]);

        $pokemon = $query->fetch(PDO::FETCH_OBJ);
        return $pokemon;
    }

 
    public function updatePokemon($id_pokemon, $ASSOC_UPD_params){
        $whereParams="id = :id ";
        $fields = $this->generate_update_params($ASSOC_UPD_params); 

        $ASSOC_Array = $fields['ASSOC_ARRAY'];
        $ASSOC_Array[':id'] = intval($id_pokemon);
        $updateParams = $fields['SET_params'];

        $this->update($updateParams, $whereParams, $ASSOC_Array);
    } 

    
    public function updateName_BY_nro_Pokedex($name, $nro_Pokedex){
        $updateParams ="nombre = :new_name";
        $whereParams = "nro_pokedex = :current_nro_pokedex";

        $ASSOC_Array=[':current_nro_pokedex'=>$nro_Pokedex,':new_name'=>$name];

        $this->update($updateParams, $whereParams, $ASSOC_Array);
    }

    
    public function update_Nro_Pokedex($new_nro_Pokedex, $old_nro_Pokedex){
        $updateParams ="nro_pokedex = :new_nro_pokedex";
        $whereParams = "nro_pokedex = :old_nro_pokedex";

        $ASSOC_Array=[':old_nro_pokedex'=>$old_nro_Pokedex,':new_nro_pokedex'=>$new_nro_Pokedex];

        $this->update($updateParams, $whereParams, $ASSOC_Array);
    }

    public function updateNroNameType_By_ID($id_Pokemon, $nro_Pokedex, $name, $type){
        $updateParams ="nro_pokedex = :new_nro_pokedex,    
                        nombre = :new_nombre,              
                        tipo = :new_tipo ";             
        $whereParams = "id = :id";

        $ASSOC_Array=[':id'=>intval($id_Pokemon), ':new_nro_pokedex'=>$nro_Pokedex,':new_nombre'=>$name,':new_tipo'=>$type];

        $this->update($updateParams, $whereParams, $ASSOC_Array);
       
    }

    public function updateType_BY_nro_Pokedex($nro_Pokedex, $type){ 
        $updateParams ="tipo = :new_tipo";
        $whereParams = "nro_pokedex = :old_nro_pokedex";

        $ASSOC_Array=[':old_nro_pokedex'=>$nro_Pokedex,':new_tipo'=>$type];

        $this->update($updateParams, $whereParams, $ASSOC_Array);
    }

    private function update($updateParams, $whereParams, $ASSOC_Array){
        $query = $this->db->prepare("UPDATE pokemon
                                    SET  $updateParams 
                                    WHERE $whereParams");                       
        $query->execute($ASSOC_Array); 

    }



    private function generate_update_params(array $fields){
        $SET_params='';
        $ASSOC_Params_array=[];
        $num_of_fields = count($fields);
        $i=0;
        foreach ($fields as $key => $value) {
                $associate=':'.$key;
                $SET_params .= $key . ' = '.$associate; //id = :id

                $ASSOC_Params_array[$associate] = $value;   //[':id'=>value, ...]
                $i++;
                if($i< $num_of_fields)
                    $SET_params.=', ';
        }
        return ['ASSOC_ARRAY'=>$ASSOC_Params_array,'SET_params'=>$SET_params];
    }

}