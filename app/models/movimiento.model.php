<?php
require_once './config/config.php';

class MovimientoModel {
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
    public function exists($id){
        $query = $this->db->prepare('SELECT 1 FROM movimiento WHERE id_movimiento=?');
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_OBJ);
    }

    public function getAll(){
        $query = $this->db->prepare('SELECT * FROM movimiento');
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
    
    public function get($id){
        $query = $this->db->prepare('SELECT * FROM movimiento WHERE id_movimiento=?');
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_OBJ);
    }
 

    public function getValid_TableFields(){
        // obtiene campos de ordenamientos/filtros, que a su vez son los nombres de las columnas de la tabla
        $fieldsMap = [ 'id_movimiento' => null, 'nombre_movimiento' => null, 'tipo_movimiento' => null,'poder_movimiento' => null, 'precision_movimiento' => null,'descripcion_movimiento' => null];
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


    public function getBy($field,$search){
        $fields = $this->getValid_TableFields();
        if(!isset($fields[$field])){ return null;} // sanitiza
       
        $query = $this->db->prepare("SELECT * FROM movimiento WHERE $field = :find");
        $query->execute([':find'=>$search]);

        return $query->fetch(PDO::FETCH_OBJ);
    }


    public function insert($nombre, $tipo, $poder, $precision, $descripcion){
        $query = $this->db->prepare('INSERT INTO movimiento ( nombre_movimiento, tipo_movimiento, poder_movimiento,
                                                              precision_movimiento, descripcion_movimiento)
                                                 VALUES (?,?,?,?,?)');
        $query->execute([$nombre, $tipo,$poder, $precision, $descripcion]);
        return $this->db->lastInsertId();
    }

    public function delete($id){
        $query = $this->db->prepare('DELETE FROM movimiento WHERE id_movimiento = :id');
        $query->execute([':id'=>$id]);
    }


    public function update($id, $ASSOC_UPD_params){
        $whereParams = "id_movimiento = :id";
        $fields = $this->generate_update_params($ASSOC_UPD_params); 
        
        $ASSOC_Array = $fields['ASSOC_ARRAY'];
        $ASSOC_Array[':id'] = intval($id);
        $updateParams = $fields['SET_params'];
        
        $query = $this->db->prepare("UPDATE movimiento SET $updateParams WHERE $whereParams");
        $query->execute($ASSOC_Array);

        return $query->rowCount();
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