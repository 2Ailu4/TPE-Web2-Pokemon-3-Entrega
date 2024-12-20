<?php
require_once './config/config.php';

class AprendizajeModel {
    private $db;

    public function __construct() {
        $this->db = new PDO("mysql:host=".MYSQL_HOST .
                            ";dbname=".MYSQL_DB.";charset=utf8", 
                            MYSQL_USER, MYSQL_PASS);
        $this->_deploy();
    }
    private function _deploy() {
        $query = $this->db->query('SHOW TABLES');
        $tables = $query->fetchAll();
        if (count($tables) == 0) { 
            $sqlFile = './tpe-web2-hiese-peralta.sql';
            $sql = file_get_contents($sqlFile);
            
            // Arreglo para separar en consultas
            $queries = explode(';', $sql);
            foreach ($queries as $query) {
                $query = trim($query); // quitamos espacios en blanco al inicio y fin             
                if (!empty($query)) {
                    $this->db->query($query);
                }
            }
        }
    }
    public function exists($id_pokemon , $id_movimiento){
        $query = $this->db->prepare('SELECT 1 FROM aprendizaje WHERE FK_id_pokemon = :id_pok AND FK_id_movimiento= :id_mov');
        $query->execute([':id_pok' => $id_pokemon, ':id_mov' => $id_movimiento]);

        return $query->fetch(PDO::FETCH_OBJ);
    }

     
    public function getTempTable_PaginatedPokemons($JOIN_movimiento =false, $where, $sorts =[],$page = 1, $limit = null){
        $TABLES = 'aprendizaje JOIN pokemon ON (aprendizaje.FK_id_pokemon = pokemon.id)';
        if($JOIN_movimiento) {$TABLES .= ' 
                                           JOIN movimiento ON (movimiento.id_movimiento = aprendizaje.FK_id_movimiento)';}  
        
        //WITH se usa para crear un conjunto de resultados temporales que se pueden referenciar en otras instrucciones
        $tempPokemonsTable ="WITH pokemon_filtrados AS (    
                            SELECT DISTINCT id
                            FROM $TABLES";

        $SORT = " ORDER BY pokemon.id";
        
        if(!empty($where)){$tempPokemonsTable .= " WHERE ( (" . implode(') AND ( ', $where).") ) ";}
        
        if(!empty($sorts)){$SORT = ' ORDER BY '.implode(', ',$sorts);} 
        if($SORT){$tempPokemonsTable .=  $SORT;}

        if(isset($limit)){                                
            $offset = (int)(($page*$limit) - $limit);  
            $tempPokemonsTable .= " LIMIT " . (int)$limit 
                                    . " OFFSET ". (int)$offset . ")";    
        }
        return $tempPokemonsTable;   
    }


    public  function getAll( $filters=[], $sorts =[],$page = null, $limit = null, $paginate_by_pokemons = false) {
        $TABLES = " aprendizaje";
        $SELECT_attributes = "aprendizaje.*";

        $JOINs  = [];    
        $where  = [];   // arreglo de condiciones ['filter_name' => "pokemon.nombre = :filter_name", 'filter_nro_pokedex' => "nro_pokedex = :filter_nro_pokedex",..]
        $params = [];   // arreglo de parametros  [':filter_name' => "Bulbasaur", ':filter_nro_pokedex' => 1,..]
         
        $valid_query_params = $this->_getQueryFields_WITH_TableColumns();   //todos los campos de las tablas
        foreach($valid_query_params as $table_name => $validQuerys){            // $validQuerys: campos de cada tabla(table_name).Ej pokemon: [id,nro_pok,..]
            $WHERE_params=$this->get_WHERE_params($filters,$validQuerys);
            if(!empty($WHERE_params['where'])){
                $JOINs[$table_name] = true;
                $where = array_merge($where,$WHERE_params['where']);
                $params = array_merge($params,$WHERE_params['params']);
            }    
            foreach($sorts as $column => $sort){
                if(isset($valid_query_params[$table_name][$column])){$JOINs[$table_name] = true;}
            }
        }     
        //DEFAULT SORT::           
        $SORT = " ORDER BY aprendizaje.FK_id_pokemon ASC, aprendizaje.FK_id_movimiento ASC, aprendizaje.nivel_aprendizaje ASC";    
        
        if (!empty($sorts) || !empty($filters) || !empty($paginate_by_pokemons)) {
            
            if(isset($JOINs['pokemon'])) {$TABLES .= ' JOIN pokemon ON aprendizaje.FK_id_pokemon = pokemon.id';}
            if(isset($JOINs['movimiento'])) {$TABLES .= ' JOIN movimiento ON aprendizaje.FK_id_movimiento = movimiento.id_movimiento';}  
            if(isset($limit) && $paginate_by_pokemons) {$TABLES .= ' JOIN pokemon_filtrados ON aprendizaje.FK_id_pokemon = pokemon_filtrados.id';}
            if(!empty($sorts)){
                if( isset($sorts['id_entrenador'])){
                    $aux = explode(".",$sorts['id_entrenador']); //$sorts['id_entrenador'] = pokemon.id_entrenador ==> string[pokemon,id_entrenador]
                    $real_table_name=implode(".FK_",$aux);  //"pokemon" . ".FK_" . "id_entrenador"
                    $sorts['id_entrenador']=$real_table_name;
                }
                $SORT = ' ORDER BY '.implode(', ',$sorts);
            }            
        }
        
        
        $sql = "SELECT $SELECT_attributes FROM $TABLES "; 
        
        if(isset($limit) && $paginate_by_pokemons){ //si se quiere paginar
            if(isset($JOINs['movimiento'])){
                $temp_Table = $this->getTempTable_PaginatedPokemons(true, $where, $sorts, $page, $limit);
            }else{
                $temp_Table = $this->getTempTable_PaginatedPokemons(false, $where, $sorts, $page, $limit);
            }
            $sql = $temp_Table . "
                     " . $sql;
        }

        if(!empty($where)){$sql .= " WHERE ( (" . implode(') AND ( ', $where).") ) ";}
        if($SORT){$sql .=  $SORT;}

        var_dump($sql);

        $query = $this->db->prepare($sql);
        $query->execute($params);

        return $query->fetchAll(PDO::FETCH_OBJ);
        
    } 

    public function get($id_pokemon , $id_movimiento){
        $query = $this->db->prepare('SELECT * FROM aprendizaje WHERE FK_id_pokemon = :id_p AND FK_id_movimiento = :id_m');
        $query->execute([':id_p' => $id_pokemon,':id_m' => $id_movimiento]);

        return $query->fetch(PDO::FETCH_OBJ);
    }

    public function insert($id_pokemon, $id_movimiento, $nivel_aprendizaje){
        $query = $this->db->prepare('INSERT INTO aprendizaje (FK_id_pokemon,FK_id_movimiento,nivel_aprendizaje)
                                    VALUES(?,?,?)');
        $query->execute([$id_pokemon, $id_movimiento, $nivel_aprendizaje]);
        
        // [FK_id_pokemon,FK_id_movimiento]: por no ser claves autoincremetales no podemos hacer uso de $this->db->lastInsertId(); 
        // debemos resolverlo manualmente
        if(!$this->exists($id_pokemon,$id_movimiento)){ return false;}
        
        return ['id_pokemon' => intval($id_pokemon) , 'id_movimiento' => intval($id_movimiento)];
        
    }

    public function delete($id_pok,$id_mov){
        $query = $this->db->prepare('DELETE FROM aprendizaje WHERE (FK_id_pokemon = :id_pok AND FK_id_movimiento = :id_mov)');
        $query->execute([':id_pok' => $id_pok,':id_mov' => $id_mov]);
    }

    public function update($old_id_pokemon, $old_id_movimiento, $ASSOC_UPD_params){
        $whereParams="FK_id_pokemon = :OLD_id_pok and FK_id_movimiento = :OLD_id_mov";
        $fields = $this->generate_update_params($ASSOC_UPD_params); 

        $ASSOC_Array = $fields['ASSOC_ARRAY'];
        $ASSOC_Array[':OLD_id_pok'] = intval($old_id_pokemon);
        $ASSOC_Array[':OLD_id_mov'] = intval($old_id_movimiento);
        
        $updateParams = $fields['SET_params'];  // ej: 'nivel_apredizaje' = :nivel

        $query = $this->db->prepare("UPDATE aprendizaje SET $updateParams WHERE $whereParams");
        $query->execute($ASSOC_Array);
        // if ($query->rowCount() > 0){
        //     return $fields['ASSOC_ARRAY'];
        // }
        // else
        //     return false;
        return $query->rowCount() > 0;
    }

    private function _getQueryFields_WITH_TableColumns(){
         
        $fieldsMap = [
            //pokemon
            'pokemon'=>['nro_pokedex' => [],'nombre' => [],'tipo' => [], 'fecha_captura' => [], 'peso' => [],'id_entrenador' => []],
            //movimiento
            'movimiento'=>['nombre_movimiento' => [], 'tipo_movimiento' => [], 'poder_movimiento' => [], 'precision_movimiento' => [],'descripcion_movimiento' => []],
            //aprendizaje
            'aprendizaje'=>['id_pokemon' => [], 'id_movimiento' => [],'nivel_aprendizaje' => [] ] 
        ];
        foreach ($fieldsMap as $table => $columns) {
            foreach ($columns as $column => $value) {
                if(in_array($column,['id_entrenador','id_pokemon','id_movimiento']))
                    $fieldsMap[$table][$column]['query_column'] = $table. '.' .'FK_'. $column;
                else $fieldsMap[$table][$column]['query_column'] = $table . '.' . $column;  
                $fieldsMap[$table][$column]['type'] = $this->getParamType($column); 
            }
        }
        return $fieldsMap;
    }
    public function getQueryFields(){
        // obtiene campos de ordenamientos/filtros, que a su vez son los nombres de las columnas de la tabla
        $fieldsMap = [
            'pokemon'=>['nro_pokedex' => null,'nombre' => null,'tipo' => null, 'fecha_captura' => null, 'peso' => null,'id_entrenador' => null],
            'movimiento'=>['nombre_movimiento' => null, 'tipo_movimiento' => null, 'poder_movimiento' => null, 'precision_movimiento' => null,'descripcion_movimiento' => null],
            'aprendizaje'=>['id_pokemon' => null, 'id_movimiento' => null,'nivel_aprendizaje' => null ] 
        ];
        foreach ($fieldsMap as $table => $columns) {
            foreach ($columns as $column => $value) {
                $fieldsMap[$table][$column] = $this->getParamType($column);  
            }
        } 
        return $fieldsMap;
    }

    private function generate_update_params(array $field){
        $SET_params='';
        $ASSOC_Params_array=[];
        $num_of_fields = count($field);

        $i=0;
        foreach ($field as $key => $value) {
                $associate=':'.$key;
                $SET_params .= $key . ' = '.$associate; //id = :id

                $ASSOC_Params_array[$associate] = $value;   //[':id'=>value, ...]
                $i++;
                if($i< $num_of_fields)
                    $SET_params.=', ';
        }
        return ['ASSOC_ARRAY'=>$ASSOC_Params_array,'SET_params'=>$SET_params];
    }

 /// mover a :  class Controlador::  --------------------------------------------------------------------  
    private function getParamType($tableColumn){
        // Subcadenas para filtrar
        $numericSubStrings = ["id", "nro", "nivel", "precision", "peso","poder"]; 
        $dateSubStrings = ["fecha"];
        
        //expresiones regulares (`i` al final para que sea case-insensitive)
        $numericPattern = "/(" . implode("|", $numericSubStrings) . ")/i";  
        $datePattern = "/(" . implode("|", $dateSubStrings) . ")/i";  

        if (preg_match($numericPattern, $tableColumn)) {return "num";} 
        if (preg_match($datePattern, $tableColumn)) {return "date";}
        return "string";
        
    }

    private function get_WHERE_params($filters,$validFilters,$FROM_TABLE = null){
        $where=[]; $params=[]; 
        
        foreach($validFilters as $filter_column => $values){
            if(isset($filters[$filter_column])) {
                $sql_OP=' = ';
                $key = ":$filter_column";
                if($values['type']==="string") {$sql_OP = ' LIKE ';}
                if($values['type']==="date") {
                    $where["$filter_column"] = "DATE(".$values['query_column'].")" . $sql_OP . " STR_TO_DATE("."'".$filters[$filter_column]."'".", '%m/%d/%Y') "; 
                }
                else {
                    if(strtoupper($filter_column)==="DESCRIPCION_MOVIMIENTO"){
                        $where[$filter_column] = $values['query_column'] . $sql_OP."'"."%". $filters[$filter_column] ."%"."'";   
                    }else{
                      $where[$filter_column] = $values['query_column'] . $sql_OP . $key;   
                      $params[$key] = $filters[$filter_column];  
                    }
                    
                }
            }
        }
        return ['where'=>$where,'params'=>$params];
    }
}



