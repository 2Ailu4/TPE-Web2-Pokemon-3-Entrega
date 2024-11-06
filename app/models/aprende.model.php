<?php
require_once './config/config.php';

class AprendeModel {
    private $db;

    public function __construct() {
        $this->db = new PDO("mysql:host=".MYSQL_HOST .
                            ";dbname=".MYSQL_DB.";charset=utf8", 
                            MYSQL_USER, MYSQL_PASS);
    }

    // public function getAll($orderBy = false){
    //     $sql = 'SELECT * FROM aprende';

    //     if($orderBy){
    //         switch($orderBy){
    //             case 'pokemon':
    //                 $sql .= ' ORDER BY FK_id_pokemon';
    //                 break;
    //             case 'movimiento':
    //                 $sql .= ' ORDER BY FK_id_movimiento';
    //                 break;
    //             case 'nivel':
    //                 $sql .= ' ORDER BY nivel_aprendizaje';
    //                 break;
    //         }
    //     }

    //     $query = $this->db->prepare($sql);
    //     $query->execute();
    //     return $query->fetchAll(PDO::FETCH_OBJ);
    // }

// ordena por nombre del pokemon(muestra solo la info de aprende)
// SELECT * FROM `aprende` as a ORDER BY (SELECT nombre FROM pokemon as p WHERE a.FK_id_pokemon=p.id)

// ordena por nombre del pokemon(muestra el nombre del pokemon)
// SELECT p.nombre as nombre_pokemon, a.* FROM aprende as a JOIN pokemon as p ON a.FK_id_pokemon = p.id ORDER BY p.nombre





// NO SE PERMITE ORDENAR POR MAS DE UN CAMPO 
    public function getAll($orderBy = false) {
        $sql = 'SELECT a.* FROM aprende as a';  // agregar que liste la info del campo por el que se ordena 
        
        $joinPokemon = false;
        $joinMovimiento = false;
        
        if ($orderBy) {
            if (in_array($orderBy, ['nro_pokedex', 'nombre', 'tipo', 'fecha_captura', 'peso', 'id_entrenador'])) {
                $joinPokemon = true;
            }
            if (in_array($orderBy, ['nombre_movimiento', 'tipo_movimiento', 'poder_movimiento', 'precision_movimiento', 'descripcion_movimiento'])) {
                $joinMovimiento = true;
            }
        }
        
        if ($joinPokemon) {
            $sql .= ' JOIN pokemon as p ON a.FK_id_pokemon = p.id';
        }
        if ($joinMovimiento) {
            $sql .= ' JOIN movimiento as m ON a.FK_id_movimiento = m.id_movimiento';
        }
        
        if ($orderBy) {
            switch ($orderBy) {
                // ordenar por campo de aprende
                case 'FK_id_pokemon':
                    $sql .= ' ORDER BY a.FK_id_pokemon';
                    break;
                case 'FK_id_movimiento':
                    $sql .= ' ORDER BY a.FK_id_movimiento';
                    break;
                case 'nivel_aprendizaje':
                    $sql .= ' ORDER BY a.nivel_aprendizaje';
                    break;
                // ordenar por campo de pokemon
                case 'nro_pokedex':
                    $sql .= ' ORDER BY p.nro_pokedex';
                    break;
                case 'nombre':
                    $sql .= ' ORDER BY p.nombre';
                    break;
                case 'tipo':
                    $sql .= ' ORDER BY p.tipo';
                    break;
                case 'fecha_captura':
                    $sql .= ' ORDER BY p.fecha_captura';
                    break;
                case 'peso':
                    $sql .= ' ORDER BY p.peso';
                    break;
                case 'id_entrenador':
                    $sql .= ' ORDER BY p.FK_id_entrenador';
                    break;
                // ordenar por campo de movimiento
                case 'nombre_movimiento':
                    $sql .= ' ORDER BY m.nombre_movimiento';
                    break;
                case 'tipo_movimiento':
                    $sql .= ' ORDER BY m.tipo_movimiento';
                    break;
                case 'poder_movimiento':
                    $sql .= ' ORDER BY m.poder_movimiento';
                    break;
                case 'precision_movimiento':
                    $sql .= ' ORDER BY m.precision_movimiento';
                    break;
                case 'descripcion_movimiento':
                    $sql .= ' ORDER BY m.descripcion_movimiento';
                    break;

                default:
                    break;
            }
        }
    
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
    

}
