<?php

    class JSONView {
        public function response($data, $status = 200) { // renderiza nuestro objeto/recurso como un json
            header("Content-Type: application/json");       //va a generar la respuesta html como un json
            $statusText = $this->_requestStatus($status);
            header("HTTP/1.1 $status $statusText");
            echo json_encode($data);    //nos va a generar la respuesta http para nuestra API
        }

        private function _requestStatus($code) {
            $status = array(
                200 => "OK",
                201 => "Created",
                204 => "No Content",
                400 => "Bad Request",
                404 => "Not Found",
                500 => "Internal Server Error"
            );
            if(!isset($status[$code])) {
                $code = 500;
            }
            return $status[$code];
        }
        
        public function requirementError_response($nameOFempty_field){
            $this->response("[Requirement_Error]: completar campo $nameOFempty_field",400);
        }

        public function typeError_response($field_name, $correct_type){
            $this->response("[Type_Error]: el campo '$field_name' debe ser de tipo '$correct_type'", 400);
        }
        
        public function existence_Error_response($entity, $id){
            $this->response("[Existence_Error]: la instancia de $entity con id = $id no existe, $entity invalido/a ",404); 
        }

        public function aprendizaje_alreadyExists_Error_response($pokemon, $movimiento){
            $this->response("[AlreadyExists_Error]: el pokemon $pokemon->nombre con id =  $pokemon->id_pokemon, ya cuenta con el movimiento $movimiento->nombre_movimiento con id = $movimiento->id_movimiento.",404);// NOT FOUND
        }  

        public function aprendizaje_insert_server_Error_response($pokemon, $movimiento){
            $this->response("[Server_Error]: no se pudo vincular el Pokemon: $pokemon->nombre,con el Movimiento: $movimiento->nombre_movimiento. Vuelve a intentarlo mas tarde",500);
        }
    }