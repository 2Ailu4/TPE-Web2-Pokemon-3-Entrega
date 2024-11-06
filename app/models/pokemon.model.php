<?php
require_once './config/config.php';

class PokemonModel {
    private $db;

    public function __construct() {
        $this->db = new PDO("mysql:host=".MYSQL_HOST .
                            ";dbname=".MYSQL_DB.";charset=utf8", 
                            MYSQL_USER, MYSQL_PASS);
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
    

}