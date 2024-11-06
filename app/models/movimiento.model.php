<?php
require_once './config/config.php';

class MovimientoModel {
    private $db;

    public function __construct() {
        $this->db = new PDO("mysql:host=".MYSQL_HOST .
                            ";dbname=".MYSQL_DB.";charset=utf8", 
                            MYSQL_USER, MYSQL_PASS);
    }

    public function getAll(){
        $query = $this->db->prepare('SELECT * FROM movimiento');
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    

}