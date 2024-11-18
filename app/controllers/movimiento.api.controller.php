<?php

require_once './app/models/movimiento.model.php';
require_once './app/views/json.view.php';

class MovimientoApiController{
    private $model;
    private $view;

    public function __construct(){
        $this->model = new MovimientoModel();
        $this->view = new JSONView();
    }

    public function getAll($req, $res){
        $movimientos = $this->model->getAll();
        if(!$movimientos){
            return $this->view->no_Coincidences();
        }
        return $this->view->response($movimientos);
    }


    public function get($req, $res){
        $id_mov = $req->params->id_mov;
        $movimiento = $this->model->get($id_mov);
        if(!$movimiento){
            return $this->view->existence_Error_response("Movimiento", $id_mov);
        }
        return $this->view->response($movimiento);
    }


    public function update($req, $res){
        if(!$res->user) {
            return $this->view->unauthorized();
        }

        $id_mov = $req->params->id_mov;
        $movimiento = $this->model->get($id_mov);
        if(!$movimiento){
            return $this->view->existence_Error_response("Movimiento", $id_mov);
        }
        
        $attributesToUpdate = [];

        $updateFields = $req->body;
        $fields_to_verify = $this->model->getValid_TableFields();

        foreach ($updateFields as $field => $updateField){
            if (!isset($fields_to_verify[$field])){
                return $this->view->invalid_Field($field);
            }
            if (strtoupper($field)==="ID_MOVIMIENTO"){
                return $this->view->restictedAccess_Field($field,400);
            }
            if($fields_to_verify[$field] !== gettype($updateField)){
                return $this->view->typeError_response($field,$fields_to_verify[$field]);
            }
            
            // Guardo en $attributesToUpdate solo los campos que se modificaron
            if(!empty($req->body->$field)){ 
                if($updateField !== $movimiento->$field){   // verifica si ingreso el mismo valor que el actual al campo a modificar
                    $attributesToUpdate[$field] = $updateField;
                } 
            }
        }
        $update = null;
        if(!empty($attributesToUpdate)){
            $update = $this->model->update($id_mov, $attributesToUpdate);
        }

        if(!empty($update)){
            $movimientoActualizado = $this->model->get($id_mov);
            return $this->view->response($movimientoActualizado);
        }else{
            if(count($attributesToUpdate)===0) 
                return $this->view->allreadyUpdate("Movimiento", $id_mov);
            return $this->view->updateError("Movimiento", $id_mov);
        }
    }


    public function insert($req, $res){
        if(!$res->user) {
            return $this->view->unauthorized();
        }

        $insertFields = $req->body;
        $valid_fields=$this->model->getValid_TableFields();

        unset($valid_fields['id_movimiento']);
        foreach($valid_fields as $field_name => $field_type){
            if(!isset($insertFields->$field_name) || empty($insertFields->$field_name)){
                return $this->view->emptyField($field_name);
            }
            if($field_type !== gettype($insertFields->$field_name)){
                return $this->view->typeError_response($field_name, $field_type);
            }
        }

        $nombre = $insertFields->nombre_movimiento;
        $tipo = $insertFields->tipo_movimiento;
        $poder = $insertFields->poder_movimiento;
        $precision = $insertFields->precision_movimiento;
        $descripcion = $insertFields->descripcion_movimiento;

        $id_new_movimiento = $this->model->insert($nombre, $tipo, $poder, $precision, $descripcion);

        if(!$id_new_movimiento){
            return $this->view->insertError("Movimiento", $id_new_movimiento);
        }
        
        $new_movimiento = $this->model->get($id_new_movimiento);
        if(!$new_movimiento){
            return $this->view->row_Not_Found("Movimiento");
        }

        return $this->view->response($new_movimiento);
    }

    public function delete($req, $res){// Los pokemons no se eliminan, si no que se liberan  
        if(!$res->user) {
            return $this->view->response("No autorizado", 401);
        }   
        $id_movimiento = is_numeric($req->params->id_mov) ? intval($req->params->id_mov) : null;
        
        if(!($id_movimiento > 0))
            return $this->view->typeError_response("id_movimiento", "[Naturales >0]");
         
        
        if(!$this->model->exists($id_movimiento))
            return $this->view->existence_Error_response("[Movimiento]", $id_movimiento);

        $this->model->delete($id_movimiento);
        if($this->model->exists($id_movimiento)){
            $this->view->response("El movimiento con $id_movimiento se elimino exitosamente."); 
        }else
            $this->view->server_Error_response(); 
    }


}