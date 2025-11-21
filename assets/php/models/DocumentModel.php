


<?php 

class DocumentModel {
    private $conn;
    private $table = "DOCUMENT";

    public function __construct($conn){ $this->conn = $conn; }

    public function getAll(){
        $sql = "SELECT * FROM $this->table WHERE is_deleted=0";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getById($id){
        $stmt = mysqli_prepare($this->conn, "SELECT * FROM $this->table WHERE id_document=? AND is_deleted=0");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    }

    public function create($numero_d, $date_creation, $montant_total, $status, $reference_doc, $id_client){
        $stmt = mysqli_prepare($this->conn, "INSERT INTO $this->table (numero_d, date_creation, montant_total, status, reference_doc, ID, is_deleted) VALUES (?, ?, ?, ?, ?, ?, 0)");
        mysqli_stmt_bind_param($stmt, 'ssdssi', $numero_d, $date_creation, $montant_total, $status, $reference_doc, $id_client);
        return mysqli_stmt_execute($stmt);
    }

    public function update($id, $numero_d, $date_creation, $montant_total, $status, $reference_doc, $id_client){
        $stmt = mysqli_prepare($this->conn, "UPDATE $this->table SET numero_d=?, date_creation=?, montant_total=?, status=?, reference_doc=?, ID=? WHERE id_document=?");
        mysqli_stmt_bind_param($stmt, 'ssdssii', $numero_d, $date_creation, $montant_total, $status, $reference_doc, $id_client, $id);
        return mysqli_stmt_execute($stmt);
    }

    public function delete($id){
        $stmt = mysqli_prepare($this->conn, "UPDATE $this->table SET is_deleted=1 WHERE id_document=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        return mysqli_stmt_execute($stmt);
    }
}

