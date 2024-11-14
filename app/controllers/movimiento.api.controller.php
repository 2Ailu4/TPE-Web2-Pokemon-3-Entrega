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
            return $this->view->response("No existen filas en la tabla Movimiento", 404);
        }
        return $this->view->response($movimientos);
    }


    public function get($req, $res){
        $id_mov = $req->params->id_mov;
        $movimiento = $this->model->get($id_mov);
        if(!$movimiento){
            return $this->view->response("No existe el movimiento con id:$id_mov", 404);
        }
        return $this->view->response($movimiento);
    }


    public function update($req, $res){
        $id_mov = $req->params->id_mov;
        $movimiento = $this->model->get($id_mov);
        if(!$movimiento){
            return $this->view->response("No existe el movimiento con id:$id_mov", 404);
        }
        
        $attributesToUpdate = [];

        $updateFields = $req->body;
        $fields_to_verify = $this->model->getValid_TableFields();

        foreach ($updateFields as $field => $updateField){
            if (!isset($fields_to_verify[$field])){
                return $this->view->response("'$field' es un campo invalido",400);
            }
            if($fields_to_verify[$field] !== gettype($updateField)){
                return $this->view->response("'$field' debe ser de tipo $fields_to_verify[$field]", 400);
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
                return $this->view->response("El movimiento con id:$id_mov ya se encuentra actualizado", 200);
            return $this->view->response("No fue posible actualizar el movimiento con id:$id_mov", 404);
        }

    }


    public function insert($req, $res){
    ///---------------------LLEVAR A CONTROLADRO GENERICO--------------------------------------------
        $updateFields = $req->body;
        $valid_fields=$this->model->getValid_TableFields();

        unset($valid_fields['id_movimiento']);
        foreach($valid_fields as $field_name => $field_type){
            if(!isset($updateFields->$field_name) || empty($updateFields->$field_name)){
                return $this->view->response("Falta completar el campo [$field_name] ", 400);
            }
            if($field_type !== gettype($updateFields->$field_name)){
                return $this->view->response("'$field_name' debe ser de tipo [$field_type]", 400);
            }
        }
    ///--------------------------------------------------------------------------------------------

        $nombre = $updateFields->nombre_movimiento;
        $tipo = $updateFields->tipo_movimiento;
        $poder = $updateFields->poder_movimiento;
        $precision = $updateFields->precision_movimiento;
        $descripcion = $updateFields->descripcion_movimiento;

        $id_new_movimiento = $this->model->insert($nombre, $tipo, $poder, $precision, $descripcion);

        if(!$id_new_movimiento){
            return $this->view->response("No fue posible insertar", 404);
        }
        
        $new_movimiento = $this->model->get($id_new_movimiento);
        if(!$new_movimiento){
            return $this->view->response("No fue posible encontrar el nuevo movimiento", 404);
        }

        return $this->view->response($new_movimiento);
    }


}