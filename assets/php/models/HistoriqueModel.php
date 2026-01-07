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
    
    // Récupérer tout avec pagination
    public function getAll($limit = 50, $offset = 0) {
        $sql = "SELECT * FROM $this->table ORDER BY date_action DESC LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $limit, $offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // Filtrer par type avec pagination
    public function getByEntityType($entity_type, $limit = 50, $offset = 0) {
        $sql = "SELECT * FROM $this->table WHERE entity_type = ? ORDER BY date_action DESC LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sii', $entity_type, $limit, $offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // Recherche globale
    public function search($term) {
        $termLike = "%" . $term . "%";
        $sql = "SELECT * FROM $this->table 
                WHERE details LIKE ? 
                OR entity_type LIKE ? 
                OR action LIKE ?
                ORDER BY date_action DESC LIMIT 100"; // Limit search results too for safety
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sss', $termLike, $termLike, $termLike);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // Advanced Filtering
    public function getFilteredLogs($filters = [], $limit = 50, $offset = 0) {
        $sql = "SELECT * FROM $this->table WHERE 1=1";
        $types = "";
        $params = [];

        // Entity Type
        if (!empty($filters['entity_type'])) {
            $sql .= " AND entity_type = ?";
            $types .= "s";
            $params[] = $filters['entity_type'];
        }

        // Search Term (Action, Details)
        if (!empty($filters['search'])) {
            $sql .= " AND (details LIKE ? OR action LIKE ? OR entity_type LIKE ?)";
            $termLike = "%" . $filters['search'] . "%";
            $types .= "sss";
            $params[] = $termLike;
            $params[] = $termLike;
            $params[] = $termLike;
        }

        // Date Range
        if (!empty($filters['start_date'])) {
            $sql .= " AND date_action >= ?";
            $types .= "s";
            $params[] = $filters['start_date'] . " 00:00:00";
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND date_action <= ?";
            $types .= "s";
            $params[] = $filters['end_date'] . " 23:59:59";
        }

        $sql .= " ORDER BY date_action DESC LIMIT ? OFFSET ?";
        $types .= "ii";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = mysqli_prepare($this->conn, $sql);
        if ($types && $params) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}
