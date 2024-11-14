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

    // NO SE PERMITE ORDENAR POR MAS DE UN CAMPO 
    public function getAll($filter_pokemon_name=null , $filter_type=null, $SORT_BY = false, $LIMIT_params = false, $ORDER = null) {
        $TABLES = " aprendizaje";
        $SELECT_attributes = "aprendizaje.*";
        $WHERE_params= false; // se arma a partir de los filtros ej filter_pokemon_name : 
                                //WHERE pokemon.nombre like $filter_pokemon_name
        $SORT = " ORDER BY aprendizaje.FK_id_pokemon";    //orden por defecto
        
        if ($SORT_BY) { 
            if (in_array($SORT_BY, ['nro_pokedex', 'nombre', 'tipo', 'fecha_captura', 'peso', 'id_entrenador'])) {
                $TABLES .= ' JOIN pokemon ON aprendizaje.FK_id_pokemon = pokemon.id';
                if($SORT_BY === "id_entrenador"){
                    $SORT = ' ORDER BY pokemon.FK_id_entrenador';
                }
                $SORT = ' ORDER BY pokemon.'.$SORT_BY;
            }
            if (in_array($SORT_BY, ['nombre_movimiento', 'tipo_movimiento', 'poder_movimiento', 'precision_movimiento', 'descripcion_movimiento'])) {
                $TABLES .= ' JOIN movimiento ON aprendizaje.FK_id_movimiento = movimiento.id_movimiento';
                $SORT = ' ORDER BY aprendizaje.FK_id_pokemon ASC, movimiento.'.$SORT_BY;
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
            }
        }

        $sql = "SELECT $SELECT_attributes FROM $TABLES ";  

        if($WHERE_params){ $sql .=  "WHERE ".$WHERE_params;}
        if($SORT){$sql .=  $SORT;}
        if($LIMIT_params){ $sql .= "LIMIT ".$LIMIT_params;}
        if($ORDER === "DESC"){ $sql .= ' DESC';}
                         else{$sql .= ' ASC';}    //por defecto es ASC

        var_dump("SQLLLL------->", $sql);

        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    public function get($id_pokemon , $id_movimiento){
        $query = $this->db->prepare('SELECT * FROM aprendizaje WHERE FK_id_pokemon = :id_p AND FK_id_movimiento = :id_m');
        $query->execute([':id_p'=> $id_pokemon,':id_m'=>$id_movimiento]);

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



