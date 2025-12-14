
<?php 
require_once __DIR__ . '/../config/Database.php';

class DocumentModel {
    private $db;
    public $conn; // Public pour access transactionnel si besoin
    private $table = "DOCUMENT";

    public function __construct($db = null) {
        if ($db && $db instanceof Database) {
            $this->db = $db;
        } else {
            $this->db = new Database();
        }
        $this->conn = $this->db->conn;
    }

    public function getAll(){
        // On joint avec CLIENT pour avoir le nom
        $sql = "SELECT d.*, c.nom, c.prenom 
                FROM $this->table d
                JOIN CLIENT c ON d.id_client = c.id
                ORDER BY d.date_creation DESC";
        $result = mysqli_query($this->conn, $sql);
        if($result) {
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
        return [];
    }

    public function getById($id){
        $sql = "SELECT * FROM $this->table WHERE id_document=?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    }

    // Création d'un document (Retourne l'ID inséré ou false)
    public function create($numero_d, $montant_total, $status, $id_client){
        // date_creation est DEFAULT CURRENT_TIMESTAMP
        // reference_doc est optionnel
        $sql = "INSERT INTO $this->table (numero_d, montant_total, status, id_client) VALUES (?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sdsi', $numero_d, $montant_total, $status, $id_client);
            if(mysqli_stmt_execute($stmt)) {
                $id = mysqli_insert_id($this->conn);
                mysqli_stmt_close($stmt);
                return $id;
            }
            mysqli_stmt_close($stmt);
        }
        return false;
    }

    public function delete($id){
        // Suppression physique car DELETE CASCADE sur les détails
        $sql = "DELETE FROM $this->table WHERE id_document=?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        return mysqli_stmt_execute($stmt);
    }
    public function updateStatus($id, $status){
        $sql = "UPDATE $this->table SET status=? WHERE id_document=?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'si', $status, $id);
        return mysqli_stmt_execute($stmt);
    }
}

