<?php
require_once './config/config.php';

class AprendeModel {
    private $db;

    public function __construct() {
        $this->db = new PDO("mysql:host=".MYSQL_HOST .
                            ";dbname=".MYSQL_DB.";charset=utf8", 
                            MYSQL_USER, MYSQL_PASS);
    }

 


// NO SE PERMITE ORDENAR POR MAS DE UN CAMPO 
    public function getAll($filter_pokemon_name=null , $filter_type=null, $ORDER_BY = false, $LIMIT_params = false) {
        $TABLES = " aprende";
        $SELECT_attributes = " aprende.*";
        $WHERE_params= false; // se arma a partir de los filtros ej filter_pokemon_name : 
                                //WHERE pokemon.nombre like $filter_pokemon_name
        
        $joinPokemon = false;
        $joinMovimiento = false;
        
        if ($ORDER_BY) {
            if (in_array($ORDER_BY, ['nro_pokedex', 'nombre', 'tipo', 'fecha_captura', 'peso', 'id_entrenador'])) {
                $joinPokemon = true;
            }
            if (in_array($ORDER_BY, ['nombre_movimiento', 'tipo_movimiento', 'poder_movimiento', 'precision_movimiento', 'descripcion_movimiento'])) {
                $joinMovimiento = true;
            }
        }
        
        if ($joinPokemon) {
            $TABLES .= ' JOIN pokemon ON aprende.FK_id_pokemon = pokemon.id';
        }
        if ($joinMovimiento) {
            $TABLES .= ' JOIN movimiento as m ON a.FK_id_movimiento = m.id_movimiento';
        }
        
        if ($ORDER_BY) {
            switch ($ORDER_BY) {
                // ordenar por campo de aprende
                case 'FK_id_pokemon':
                    $ORDER_BY = ' ORDER BY a.FK_id_pokemon';
                    break;
                case 'FK_id_movimiento':
                    $ORDER_BY = ' ORDER BY a.FK_id_movimiento';
                    break;
                case 'nivel_aprendizaje':
                    $ORDER_BY = ' ORDER BY a.nivel_aprendizaje';
                    break;
                // ordenar por campo de pokemon
                case 'nro_pokedex':
                    $ORDER_BY = ' ORDER BY p.nro_pokedex';
                    break;
                case 'nombre':
                    $ORDER_BY = ' ORDER BY p.nombre';
                    break;
                case 'tipo':
                    $ORDER_BY = ' ORDER BY p.tipo';
                    break;
                case 'fecha_captura':
                    $ORDER_BY = ' ORDER BY p.fecha_captura';
                    break;
                case 'peso':
                    $ORDER_BY = ' ORDER BY p.peso';
                    break;
                case 'id_entrenador':
                    $ORDER_BY = ' ORDER BY p.FK_id_entrenador';
                    break;
                // ordenar por campo de movimiento
                case 'nombre_movimiento':
                    $ORDER_BY = ' ORDER BY m.nombre_movimiento';
                    break;
                case 'tipo_movimiento':
                    $ORDER_BY = ' ORDER BY m.tipo_movimiento';
                    break;
                case 'poder_movimiento':
                    $ORDER_BY = ' ORDER BY m.poder_movimiento';
                    break;
                case 'precision_movimiento':
                    $ORDER_BY = ' ORDER BY m.precision_movimiento';
                    break;
                case 'descripcion_movimiento':
                    $ORDER_BY = ' ORDER BY m.descripcion_movimiento';
                    break;

                default:
                    break;
            }
        }

        $sql = "SELECT $SELECT_attributes FROM $TABLES ";  // agregar que liste la info del campo por el que se ordena 
        
        if($WHERE_params) $sql .=  "WHERE ".$WHERE_params;
        if($ORDER_BY) $sql .=  $ORDER_BY;
        if($LIMIT_params) $sql .= "LIMIT ".$LIMIT_params;

        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }
    
}
