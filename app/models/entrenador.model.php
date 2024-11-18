<?php

class EntrenadorModel{
    private $db;

    public function __construct(){
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

    public function getTrainer($id_trainer){
        $query = $this->db->prepare('SELECT * FROM entrenadorpokemon WHERE id_entrenador = :id_entrenador');
        $query->execute([":id_entrenador"=>$id_trainer]);

        $trainer = $query->fetch(PDO::FETCH_ASSOC);

        return $trainer;
    }
    public function getTrainerPokemons($id_trainer){
        $query = $this->db->prepare('SELECT id, nro_pokedex, nombre, tipo, fecha_captura, peso , imagen_pokemon FROM pokemon JOIN entrenadorpokemon on (FK_id_entrenador =id_entrenador) WHERE id_entrenador = :id_entrenador');
        $query->execute([':id_entrenador'=>$id_trainer]);

        $trainer = $query->fetchAll(PDO::FETCH_ASSOC);
        return $trainer;
    }
    public function delete($trainerID){
        $hasPokemons = $this->getTrainerPokemons($trainerID);
        if ($hasPokemons) {$this->releasePokemons($trainerID);} 
        
        $query = $this->db->prepare('DELETE FROM entrenadorpokemon WHERE id_entrenador=?');
        $query->execute([$trainerID]);
    }
    private function releasePokemons($trainerID){
        // busco todos los pokemons que tienen por FK a ese entrenador
        // funciona como un : for(pokemon.FK_pokemon == id_entrenador("1")) -->  releasePokemon (FK_id_entrenador = NULL )
        $query = $this->db->prepare('UPDATE pokemon
                                        SET FK_id_entrenador = ?
                                        WHERE FK_id_entrenador = ?');
        $query->execute([$trainerID, NULL]);
    }
    
}
