<?php

class HistoriqueModel
{
    private $conn;
    private $table = "HISTORIQUE";

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function create($entity_type, $entity_id, $action, $details = null, $user_id = null)
    {
        $sql = "INSERT INTO $this->table (entity_type, entity_id, action, details, user_id, date_action) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($this->conn, $sql);
        // entity_type(s), entity_id(i), action(s), details(s), user_id(i)
        mysqli_stmt_bind_param($stmt, 'sissi', $entity_type, $entity_id, $action, $details, $user_id);
        return mysqli_stmt_execute($stmt);
    }
    
    public function getAll() {
        // Create lookup array for easy entity name resolution if needed later
        // For now, raw data is fine
        $sql = "SELECT * FROM $this->table ORDER BY date_action DESC";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // Filter by entity type (e.g., 'CLIENT', 'DOCUMENT')
    public function getByEntityType($entity_type) {
        $sql = "SELECT * FROM $this->table WHERE entity_type = ? ORDER BY date_action DESC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $entity_type);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}
