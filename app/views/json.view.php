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
    // ERRORES DE PARAMETROS VALIDOS
        public function invalid_params_response($check_parameters){
            $this->response("[Invalid_params_Error]: revisa los parametros de $check_parameters",400);
        }
        public function invalid_parms_type_response($correct_type){
            $this->response("[Invalid_params_type_Error]: el parametro debe ser de tipo $correct_type",400);
        }
    // ERRORES DE REQERIMIENTOS
        public function requirementError_response($nameOFempty_field){
            $this->response("[Requirement_Error]: completar campo $nameOFempty_field",400);
        }
    // ERRORES DE TIPO
        public function typeError_response($field_name, $correct_type){
            $this->response("[Type_Error]: el campo '$field_name' debe ser de tipo '$correct_type'", 400);
        }
    // ERRORES DE INEXISTENCIA EN DB
        public function existence_Error_response($entity, $id){
            $this->response("[Existence_Error]: la instancia de '$entity' con id = '$id' no existe, '$entity' invalido/a ",404); 
        }
        public function existence_Error_response_Aprendizaje($id_pokemon, $id_movimiento){
            $this->response("[Existence_Error]: no existe el 'Pokemon' con id:$id_pokemon ni el 'Movimiento' con id:$id_movimiento", 404);
        }
    // ADVERTENCIA DE INEXISTENCIA EN DB
        public function unlinked_Warning_response($id_pokemon, $id_movimiento){   
            $this->response("[Existence_Error]: no existe relacion entre [id_pokemon,id_movimiento]=[$id_pokemon,$id_movimiento]", 404);
        }
    // ERRORES DE EXISTENCIA EN DB
        public function aprendizaje_alreadyExists_Error_response($pokemon, $movimiento){
            $this->response("[AlreadyExists_Error]: el pokemon $pokemon->nombre con id =  $pokemon->id_pokemon, ya cuenta con el movimiento $movimiento->nombre_movimiento con id = $movimiento->id_movimiento.",404);// NOT FOUND
        }  
    // ERRORES DE DB
        public function server_Error_response(){
            $this->response("[Server_Error]: fallo la coneccion con el servidor. Vuelve a intentarlo mas tarde",500);
        }
        public function aprendizaje_insert_server_Error_response($pokemon, $movimiento){
            $this->response("[Server_Error]: no se pudo vincular el Pokemon: $pokemon->nombre,con el Movimiento: $movimiento->nombre_movimiento. Vuelve a intentarlo mas tarde",500);
        }
    }