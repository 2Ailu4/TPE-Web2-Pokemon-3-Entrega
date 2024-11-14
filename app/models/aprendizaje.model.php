<?php
require_once './config/config.php';

class AprendizajeModel {
    private $db;

    public function __construct() {
        $this->db = new PDO("mysql:host=".MYSQL_HOST .
                            ";dbname=".MYSQL_DB.";charset=utf8", 
                            MYSQL_USER, MYSQL_PASS);
    }

    
    public function exists($id_pokemon , $id_movimiento){
        $query = $this->db->prepare('SELECT 1 FROM aprendizaje WHERE FK_id_pokemon = ? AND FK_id_movimiento=?');
        $query->execute([$id_pokemon,$id_movimiento]);

        return $query->fetch(PDO::FETCH_OBJ);
    }

    public function getAll( $filters=[], $sorts =[],$page = null, $LIMIT = null ) {
        $TABLES = " aprendizaje";
        $SELECT_attributes = "aprendizaje.*";

        $JOINs  = [];    
        $where  = [];   // arreglo de condiciones ['filter_name' => "pokemon.nombre = :filter_name", 'filter_nro_pokedex' => "nro_pokedex = :filter_nro_pokedex",..]
        $params = [];   // arreglo de parametros  [':filter_name' => "Bulbasaur", ':filter_nro_pokedex' => 1,..]
         
        $valid_query_params = $this->_getQueryFields_WITH_TableColumns();
        foreach($valid_query_params as $table_name => $validQuerys){            // $validQuerys: campos de cada tabla(table_name).Ej pokemon: [id,nro_pok,..]
            $WHERE_params=$this->get_WHERE_params($filters,$validQuerys);
            if(!empty($WHERE_params['where'])){
                $JOINs[$table_name] = true;
                $where = array_merge($where,$WHERE_params['where']);
                $params = array_merge($params,$WHERE_params['params']);
            }    
            //REFACTOR LOGICA AILEN/// 
        }     
        //DEFAULT SORT::           
        $SORT = " ORDER BY aprendizaje.FK_id_pokemon ASC, aprendizaje.FK_id_movimiento ASC, aprendizaje.nivel_aprendizaje ASC";    
        
        //REFACTOR LOGICA AILEN/// 
        if (!empty($sorts) || !empty($filters)) {
            
            if(isset($JOINs['pokemon'])) {$TABLES .= ' JOIN pokemon ON aprendizaje.FK_id_pokemon = pokemon.id';}
            if(isset($JOINs['movimiento'])) {$TABLES .= ' JOIN movimiento ON aprendizaje.FK_id_movimiento = movimiento.id_movimiento';}  
            //REFACTORLOGICA AILEN///                
        }
        /// A REFACTORIZAR:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
        //$pokemonFields = ['nro_pokedex', 'nombre', 'tipo', 'fecha_captura', 'peso', 'id_entrenador'];
        // if ($SORT_BY) {
            // if (in_array($SORT_BY, $pokemonFields) || in_array('pokemon',$JOINs)) { 
            //     $TABLES .= ' JOIN pokemon ON aprendizaje.FK_id_pokemon = pokemon.id';
            //     if($SORT_BY === "id_entrenador"){
            //         $SORT = ' ORDER BY pokemon.FK_id_entrenador';
            //     }else $SORT = ' ORDER BY pokemon.'.$SORT_BY; 
            //     $WHERE_params=$this->get_WHERE_params($filters,$valid_query_params['pokemon']);
            // }
            // if (in_array($SORT_BY, ['nombre_movimiento', 'tipo_movimiento', 'poder_movimiento', 'precision_movimiento', 'descripcion_movimiento'])) {
            //     $TABLES .= ' JOIN movimiento ON aprendizaje.FK_id_movimiento = movimiento.id_movimiento';
            //     $SORT = ' ORDER BY aprendizaje.FK_id_pokemon ASC, movimiento.'.$SORT_BY;
            //     $WHERE_params=$this->get_WHERE_params($filters, ['movimiento']);
            // }
            // if (in_array($SORT_BY, ['id_pokemon', 'id_movimiento', 'nivel_aprendizaje'])) {
            //     if($SORT_BY === "id_pokemon"){
            //         $SORT = ' ORDER BY aprendizaje.FK_id_pokemon';
            //     }else{
            //         if($SORT_BY === "id_movimiento"){
            //             $SORT = ' ORDER BY aprendizaje.FK_id_movimiento';
            //         }else{
            //             $SORT = ' ORDER BY aprendizaje.FK_id_pokemon ASC, aprendizaje.nivel_aprendizaje';
            //         }
            //     }
            //     $WHERE_params=$this->get_WHERE_params($filters,['aprendizaje']);
            // }
        //}
      
        $sql = "SELECT $SELECT_attributes FROM $TABLES "; 
        if(!empty($where)){$sql .= " WHERE ( (" . implode(') AND ( ', $where).") ) ";}
        if($SORT){$sql .=  $SORT;}
        if($LIMIT){ $sql .= "LIMIT ".$LIMIT;}

        var_dump("SQLLLL------->", $sql);

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
   
    public function update($id_pokemon, $id_movimiento, $ASSOC_UPD_params){
        $whereParams="FK_id_pokemon = :id_pok and FK_id_movimiento = :id_mov";
        $fields = $this->generate_update_params($ASSOC_UPD_params); 

        $ASSOC_Array = $fields['ASSOC_ARRAY'];
        $ASSOC_Array[':id_pok'] = intval($id_pokemon);
        $ASSOC_Array[':id_mov'] = intval($id_movimiento);
        
        $updateParams = $fields['SET_params'];

        $query = $this->db->prepare("UPDATE aprendizaje SET $updateParams WHERE $whereParams");
        $query->execute($ASSOC_Array);
        if ($query->rowCount() > 0){
            return $fields['ASSOC_ARRAY'];
        }
        else
            return false;
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
                    $fieldsMap[$table][$column]['query_column'] = 'FK_'.$table . '.' . $column;
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
                if($values['type']==="date") {$sql_OP = ' = ';} // ver la fecha como se consulta
                $where["$filter_column"] = $values['query_column'] . $sql_OP . $key;   
                $params[$key] = $filters[$filter_column];
            }
        }
        return ['where'=>$where,'params'=>$params];
    }
}



