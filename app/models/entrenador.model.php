<?php

class EntrenadorModel{
    private $db;

    public function __construct(){
        $this->db = new PDO("mysql:host=".MYSQL_HOST .
                                ";dbname=".MYSQL_DB.";charset=utf8", 
                                MYSQL_USER, MYSQL_PASS);
    }

    public function getTrainer($id_trainer){
        $query = $this->db->prepare('SELECT * FROM entrenadorpokemon WHERE id_entrenador = :id_entrenador');
        $query->execute([":id_entrenador"=>$id_trainer]);

        $trainer = $query->fetch(PDO::FETCH_ASSOC);

        return $trainer;
    }


}
