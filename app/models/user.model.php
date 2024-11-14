<?php
require_once './config/config.php';

class UserModel {
    private $db;

    public function __construct() {
        $this->db = new PDO("mysql:host=".MYSQL_HOST .
                            ";dbname=".MYSQL_DB.";charset=utf8", 
                            MYSQL_USER, MYSQL_PASS);
    }
 
    public function getUser($user) {  
        $query = $this->db->prepare('SELECT * FROM admin_user WHERE (email= :user OR nombre_usuario = :user)');
        $query->execute([':user'=>$user]);
    
        $user = $query->fetch(PDO::FETCH_OBJ);
    
        return $user;
    }
}