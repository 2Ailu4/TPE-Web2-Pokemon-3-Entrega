<?php
require_once './config/config.php';

class MovimientoModel {
    private $db;

    public function __construct() {
        $this->db = new PDO("mysql:host=".MYSQL_HOST .
                            ";dbname=".MYSQL_DB.";charset=utf8", 
                            MYSQL_USER, MYSQL_PASS);
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

    public function insert($nombre, $tipo, $poder, $precision, $descripcion){
        $query = $this->db->prepare('INSERT INTO movimiento ( nombre_movimiento, tipo_movimiento, poder_movimiento,
                                                              precision_movimiento, descripcion_movimiento)
                                                 VALUES (?,?,?,?,?)');
        $query->execute([$nombre, $tipo,$poder, $precision, $descripcion]);
        return $query->fetch(PDO::FETCH_OBJ);
    }

    public function update($id){
        $query = $this->db->prepare('');
        $query->execute();
    }
}