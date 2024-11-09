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
    // public function getAll($filter_pokemon_name=null , $filter_type=null, $ORDER_BY = false, $LIMIT_params = false) {
        $TABLES = " aprende";
        $SELECT_attributes = " aprende.*";
        $WHERE_params= false; // se arma a partir de los filtros ej filter_pokemon_name : 
                                //WHERE pokemon.nombre like $filter_pokemon_name
        $SORT = " ORDER BY aprende.FK_id_pokemon";    //orden por defecto
        
        $joinPokemon = false;
        $joinMovimiento = false;
        
        if ($SORT_BY) {
            if (in_array($SORT_BY, ['nro_pokedex', 'nombre', 'tipo', 'fecha_captura', 'peso', 'id_entrenador'])) {
                $joinPokemon = true;
            }
            if (in_array($SORT_BY, ['nombre_movimiento', 'tipo_movimiento', 'poder_movimiento', 'precision_movimiento', 'descripcion_movimiento'])) {
                $joinMovimiento = true;
            }
        }
        
        if ($joinPokemon) {
            $TABLES .= ' JOIN pokemon ON aprende.FK_id_pokemon = pokemon.id';
        }
        if ($joinMovimiento) {
            $TABLES .= ' JOIN movimiento as m ON a.FK_id_movimiento = m.id_movimiento';
        }
        
        if ($SORT_BY) {
            switch ($SORT_BY) {
                // ordenar por campo de aprende
                case 'FK_id_pokemon':
                    $SORT_BY = ' ORDER BY a.FK_id_pokemon';
                    break;
                case 'FK_id_movimiento':
                    $SORT_BY = ' ORDER BY a.FK_id_movimiento';
                    break;
                case 'nivel_aprendizaje':
                    $SORT_BY = ' ORDER BY a.nivel_aprendizaje';
                    break;
                // ordenar por campo de pokemon
                case 'nro_pokedex':
                    $SORT_BY = ' ORDER BY p.nro_pokedex';
                    break;
                case 'nombre':
                    $SORT_BY = ' ORDER BY p.nombre';
                    break;
                case 'tipo':
                    $SORT_BY = ' ORDER BY p.tipo';
                    break;
                case 'fecha_captura':
                    $SORT_BY = ' ORDER BY p.fecha_captura';
                    break;
                case 'peso':
                    $SORT_BY = ' ORDER BY p.peso';
                    break;
                case 'id_entrenador':
                    $SORT_BY = ' ORDER BY p.FK_id_entrenador';
                    break;
                // ordenar por campo de movimiento
                case 'nombre_movimiento':
                    $SORT_BY = ' ORDER BY m.nombre_movimiento';
                    break;
                case 'tipo_movimiento':
                    $SORT_BY = ' ORDER BY m.tipo_movimiento';
                    break;
                case 'poder_movimiento':
                    $SORT_BY = ' ORDER BY m.poder_movimiento';
                    break;
                case 'precision_movimiento':
                    $SORT_BY = ' ORDER BY m.precision_movimiento';
                    break;
                case 'descripcion_movimiento':
                    $SORT_BY = ' ORDER BY m.descripcion_movimiento';
                    break;

                default:
                    break;
            }
        }

        $sql = "SELECT $SELECT_attributes FROM $TABLES ";  // agregar que liste la info del campo por el que se ordena 
        
        if($WHERE_params) $sql .=  "WHERE ".$WHERE_params;
        if($SORT){$sql .=  $SORT;}
        if($SORT_BY) $sql .=  $SORT_BY;
        if($LIMIT_params) $sql .= "LIMIT ".$LIMIT_params;

        $query = $this->db->prepare($sql);
        $query->execute();
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
}
