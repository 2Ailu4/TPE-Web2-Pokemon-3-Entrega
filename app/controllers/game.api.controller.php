<?php
require_once './app/models/pokemon.model.php';
require_once './app/models/trainer.model.php';
require_once './app/views/json.view.php';

class GameApiController {
    private $model;
    private $view;

    public function __construct() {
        $this->model = new PokemonModel();
        $this->model = new TrainerModel();
        $this->view = new JSONView();
    }


}