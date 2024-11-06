<?php
require_once './app/models/pokemon.model.php';
require_once './app/models/movimiento.model.php';
require_once './app/models/aprende.model.php';
require_once './app/views/json.view.php';

class GameApiController {
    private $pokemon_model;
    private $aprende_model;
    private $movimiento_model;
    private $view;

    public function __construct() {
        $this->pokemon_model = new PokemonModel();
        $this->aprende_model = new AprendeModel();
        $this->movimiento_model = new MovimientoModel();
        $this->view = new JSONView();
    }

    public function getAll($req, $res){
        // if(!$res->user) {
        //     return $this->view->response("No autorizado", 401);
        // }

        $pokemonMovements = $this->aprende_model->getAll();
        if(!$pokemonMovements){
            $this->view->response("La tabla aprende no cuenta con filas",404);
            return;
        }

        $result = [];
        foreach($pokemonMovements as $movement_learned){
            $movement = $this->movimiento_model->get($movement_learned->FK_id_movimiento);
            if(!$movement){
                $this->view->response("Inconsistencias en DB entre tablas 'aprende' & 'movimiento' (No existe el movimiento con id=$movement_learned->FK_id_movimiento) ",500);
                return;
            }

            if(!isset($result[$movement_learned->FK_id_pokemon])){     // si aun no cargue el pokemon 
                $pokemon = $this->pokemon_model->get($movement_learned->FK_id_pokemon); //obtengo el POKEMON  de la DB
                if(!$pokemon){
                    $this->view->response("Inconsistencias en DB entre tablas 'aprende' & 'pokemon' (No existe el pokemon con id=$movement_learned->FK_id_pokemon)",500);
                    return;
                }
                
                $pokemon->movimientos = [];
                array_push($pokemon->movimientos,$movement); // a pokemon le creo un arreglo de movimientos e inserto el actual
                $result[$movement_learned->FK_id_pokemon] =$pokemon; // incerto el pokemon en el resultado 
            }else{// otro movimiento para un pokemon ya agregado a result
                array_push($result[$movement_learned->FK_id_pokemon]->movimientos,$movement); // incerto al arreglo movimientos el movimiento actual
            }
            
        }
        $this->view->response($result);
    }
}

