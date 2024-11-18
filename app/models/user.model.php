<?php
require_once './config/config.php';

class UserModel {
    private $db;

    public function __construct() {
        $this->db = new PDO("mysql:host=".MYSQL_HOST .
                            ";dbname=".MYSQL_DB.";charset=utf8", 
                            MYSQL_USER, MYSQL_PASS);
        $this->_deploy();
    }
    private function _deploy() {
        $query = $this->db->query('SHOW TABLES');
        $tables = $query->fetchAll();
        if (count($tables) == 0) { 
            $sqlFile = './tpe-web2-hiese-peralta.sql';
            $sql = file_get_contents($sqlFile);
            
            // Arreglo para separar en consultas
            $queries = explode(';', $sql);
            foreach ($queries as $query) {
                $query = trim($query); // quitamos espacios en blanco al inicio y fin             
                if (!empty($query)) {
                    $this->db->query($query);
                }
            }
        }
    }
    public function getUser($user) {  
        $query = $this->db->prepare('SELECT * FROM admin_user WHERE (email= :user OR nombre_usuario = :user)');
        $query->execute([':user'=>$user]);
    
        $user = $query->fetch(PDO::FETCH_OBJ);
    
        return $user;
    }
}