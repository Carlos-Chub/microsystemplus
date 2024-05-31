<?php
include '../../includes/Config/database.php';
class Proteccion
{

    private $conn;

    public function __construct()
    {
        // Crea una nueva instancia de la clase Database para la conexión a la base de datos
        include '../../includes/Config/database.php';
        $this->conn = new Database($db_host, $db_name, $db_user, $db_password, $db_name_general);
    }

    public function getAll()
    {
        try {
            $this->conn->openConnection();
            $query = $this->conn->selectAll('ctb_fuente_fondos');

            $users = [];
            // while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            //     $users[] = $row;
            // }
            return $users;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        } finally {
            $this->conn->closeConnection();
        }
    }
}
