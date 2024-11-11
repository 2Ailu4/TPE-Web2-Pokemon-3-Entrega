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
    
    private function get_WHERE_params($filters,$FROM_TABLE){
        $validFilters=[];
        if($FROM_TABLE === 'aprendizaje'){$validFilters=['id_pokemon_aprendizaje' => 'aprendizaje.FK_id_pokemon', 'id_movimiento_aprendizaje' => 'aprendizaje.FK_id_movimiento','nivel_aprendizaje' => 'aprendizaje.nivel_aprendizaje'];}     
        if($FROM_TABLE === 'movimiento'){$validFilters = ['id_movimiento' => 'movimiento.id_movimiento', 'nombre_movimiento' => 'movimiento.nombre_movimiento', 'tipo_movimiento' => 'movimiento.tipo_movimiento'];}
        if($FROM_TABLE === 'pokemon'){$validFilters = ['filter_name' =>'pokemon.nombre','filter_type' =>'pokemon.tipo','filter_nro_pokedex'=>'pokemon.nro_pokedex','filter_trainer'=>'$pokemon.FK_id_entrenador'];}
        $where=[]; $params=[]; 
        
        foreach($validFilters as $filter_column => $real_column){
            if(isset($filters[$filter_column])) {
                $key = ":$filter_column";
                $where["$filter_column"] = "$real_column = $key";   
                $params[$key] = $filters[$filter_column];
            }
        }
        return ['where'=>$where,'params'=>$params];
    }

    // NO SE PERMITE ORDENAR POR MAS DE UN CAMPO 
    public function getAll( $filters, $SORT_BY = false, $ORDER = null, $LIMIT = false, $page = null) {
        $TABLES = " aprendizaje";
        $SELECT_attributes = "aprendizaje.*";

        // si filters no esta vacio, obtiene los arreglos
        $WHERE_params= []; // $where (arreglo de filtros para consulta sql) y $params (parametros $key=>value "sanitizados")
                           
        $where = []; // arreglo de condiciones ['filter_name' => "pokemon.nombre = :filter_name", 'filter_nro_pokedex' => "nro_pokedex = :filter_nro_pokedex",..]
        $params = [];// arreglo de parametros  [':filter_name' => "Bulbasaur", ':filter_nro_pokedex' => 1,..]

        $SORT = " ORDER BY aprendizaje.FK_id_pokemon";    //orden por defecto
        $pokemonFields = ['nro_pokedex', 'nombre', 'tipo', 'fecha_captura', 'peso', 'id_entrenador'];
         
        if ($SORT_BY) {
            if (in_array($SORT_BY, $pokemonFields)) { 
                $TABLES .= ' JOIN pokemon ON aprendizaje.FK_id_pokemon = pokemon.id';
                if($SORT_BY === "id_entrenador"){
                    $SORT = ' ORDER BY pokemon.FK_id_entrenador';
                }else $SORT = ' ORDER BY pokemon.'.$SORT_BY; 
                $WHERE_params=$this->get_WHERE_params($filters,'pokemon');
            }
            if (in_array($SORT_BY, ['nombre_movimiento', 'tipo_movimiento', 'poder_movimiento', 'precision_movimiento', 'descripcion_movimiento'])) {
                $TABLES .= ' JOIN movimiento ON aprendizaje.FK_id_movimiento = movimiento.id_movimiento';
                $SORT = ' ORDER BY aprendizaje.FK_id_pokemon ASC, movimiento.'.$SORT_BY;
                $WHERE_params=$this->get_WHERE_params($filters, 'movimiento');
            }
            if (in_array($SORT_BY, ['id_pokemon', 'id_movimiento', 'nivel_aprendizaje'])) {
                if($SORT_BY === "id_pokemon"){
                    $SORT = ' ORDER BY aprendizaje.FK_id_pokemon';
                }else{
                    if($SORT_BY === "id_movimiento"){
                        $SORT = ' ORDER BY aprendizaje.FK_id_movimiento';
                    }else{
                        $SORT = ' ORDER BY aprendizaje.FK_id_pokemon ASC, aprendizaje.nivel_aprendizaje';
                    }
                }
                $WHERE_params=$this->get_WHERE_params($filters,'aprendizaje');
            }
        }
        $sql = "SELECT $SELECT_attributes FROM $TABLES "; 
        var_dump($WHERE_params) ;
        $where = isset($WHERE_params['where']) ? $WHERE_params['where'] : []; 
        if(!empty($where)){
            $params = $WHERE_params['params'];
            $sql .= " WHERE ( (" . implode(') AND ( ', $where).") ) ";
        }
        if($SORT){$sql .=  $SORT;}
        if($LIMIT){ $sql .= "LIMIT ".$LIMIT;}
        if($ORDER === "DESC"){ $sql .= ' DESC';}
                         else{$sql .= ' ASC';}    //por defecto es ASC

        var_dump("SQLLLL------->", $sql);

        $query = $this->db->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_OBJ);
    } 

    public function get($id_pokemon , $id_movimiento){
        $query = $this->db->prepare('SELECT * FROM aprendizaje WHERE FK_id_pokemon = ? ,FK_id_movimiento=?');
        $query->execute([$id_pokemon,$id_movimiento]);

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
        var_dump("WHEREEEE", $whereParams);
        
        $updateParams = $fields['SET_params'];
        var_dump("UPDATEEEEE", $updateParams);

        $query = $this->db->prepare("UPDATE aprendizaje SET $updateParams WHERE $whereParams");
        $query->execute($ASSOC_Array);
        if ($query->rowCount() > 0){
            return $fields['ASSOC_ARRAY'];
        }
        else
            return false;
    }

    private function _getQueryFields_WITH_TableColumns(){
        // Mapeo de campos válidos y sus respectivas columnas en las tablas     
        return $fieldsMap = [
            //pokemon
            'pokemon'=>[
                'id' => 'pokemon.id',                                       // no lo tiene ORDER
                'nro_pokedex' => 'pokemon.nro_pokedex',
                'nombre' => 'pokemon.nombre',
                'tipo' => 'pokemon.tipo',
                'fecha_captura' => 'pokemon.fecha_captura',
                'peso' => 'pokemon.peso',
                'id_entrenador' => 'pokemon.FK_id_entrenador' 
            ],
            //movimiento
            'movimiento'=>[
            'id_movimiento' => 'movimiento.id_movimiento',              // no lo tiene ORDER
            'nombre_movimiento' => 'movimiento.nombre_movimiento',
            'tipo_movimiento' => 'movimiento.tipo_movimiento',
            'poder_movimiento' => 'movimiento.poder_movimiento',
            'precision_movimiento' => 'movimiento.precision_movimiento',
            'descripcion_movimiento' => 'movimiento.descripcion_movimiento'
            ],
            //aprendizaje
            'aprendizaje'=>[
                'id_pokemon_aprendizaje' => 'aprendizaje.FK_id_pokemon',       // no lo tiene ORDER
                'id_movimiento_aprendizaje' => 'aprendizaje.FK_id_movimiento', // no lo tiene ORDER
                'nivel_aprendizaje' => 'aprendizaje.nivel_aprendizaje'
            ]
        ];
    } 

    public function getQueryFields(){
         
        function getParamType($tableColumn){
            // Subcadenas para filtrar
            $numericSubStrings = ["id", "nro", "nivel", "precision", "peso","poder"]; 
            $dateSubStrings = ["fecha", "date"];
            
            //expresiones regulares (`i` al final para que sea case-insensitive)
            $numericPattern = "/(" . implode("|", $numericSubStrings) . ")/i";  
            $datePattern = "/(" . implode("|", $dateSubStrings) . ")/i";  

            if (preg_match($numericPattern, $tableColumn)) {return "NUM";} 
            if (preg_match($datePattern, $tableColumn)) {return "DATE";}
            return "STR";
            
        } 
        $fields= $this->_getQueryFields_WITH_TableColumns();
        $result=[];
        foreach($fields['pokemon'] as $query_param => $param_type){
            $result[$query_param] = getParamType($query_param);
        }
        foreach($fields['movimiento'] as $query_param => $param_type){
            $result[$query_param] = getParamType($query_param);
        }
        foreach($fields['aprendizaje'] as $query_param => $param_type){
            $result[$query_param] = getParamType($query_param);
        }
        return $result;
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
}



