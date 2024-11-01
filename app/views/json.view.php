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
    }