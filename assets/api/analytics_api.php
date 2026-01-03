<?php
// assets/api/analytics_api.php
header('Content-Type: application/json');
require_once '../php/config/Database.php';

session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$db = new Database();
$conn = $db->conn;

$response = [
    'status' => 'success',
    'monthly_revenue' => [],
    'top_products' => [],
    'top_clients' => [],
    'current_month_stats' => []
];

try {
    // 1. Monthly Revenue (Current Year)
    $year = date('Y');
    // Initialize all 12 months with 0
    for ($i = 1; $i <= 12; $i++) {
        $response['monthly_revenue'][$i] = 0;
    }

    $sqlRevenue = "SELECT MONTH(date_reglement) as mois, SUM(montant) as total 
                   FROM REGLEMENT 
                   WHERE YEAR(date_reglement) = ? 
                   GROUP BY MONTH(date_reglement)";
    
    $stmt = mysqli_prepare($conn, $sqlRevenue);
    mysqli_stmt_bind_param($stmt, 's', $year);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($res)) {
        $response['monthly_revenue'][intval($row['mois'])] = floatval($row['total']);
    }

    // 2. Top 5 Products (Best Sellers by Revenue)
    // We link DOCUMENT -> DETAIL_DOCUMENT -> SERVICE_PRODUIT
    $sqlProducts = "SELECT s.libelle, SUM(dd.montant) as revenue, SUM(dd.quantite) as qty
                    FROM DETAIL_DOCUMENT dd
                    JOIN DOCUMENT d ON dd.id_document = d.id_document
                    JOIN SERVICE_PRODUIT s ON dd.id_service_produit = s.id
                    WHERE d.status != 'IMPAYE' -- Only count paid or in-progress revenue
                    GROUP BY s.libelle
                    ORDER BY revenue DESC
                    LIMIT 5";
    
    $resProd = mysqli_query($conn, $sqlProducts);
    while ($row = mysqli_fetch_assoc($resProd)) {
        $response['top_products'][] = [
            'label' => $row['libelle'],
            'value' => floatval($row['revenue']),
            'qty' => intval($row['qty'])
        ];
    }

    // 3. Top 5 Clients
    $sqlClients = "SELECT c.nom, c.prenom, SUM(d.montant_total) as total_billed
                   FROM DOCUMENT d
                   JOIN CLIENT c ON d.id_client = c.id
                   WHERE d.status != 'IMPAYE'
                   GROUP BY d.id_client
                   ORDER BY total_billed DESC
                   LIMIT 5";

    $resCli = mysqli_query($conn, $sqlClients);
    while ($row = mysqli_fetch_assoc($resCli)) {
        $response['top_clients'][] = [
            'name' => $row['nom'] . ' ' . ($row['prenom'] ?? ''),
            'value' => floatval($row['total_billed'])
        ];
    }

    // 4. Current Month Specific Stats (for reset logic verification)
    $currentMonth = date('m');
    $sqlCurrentMonth = "SELECT SUM(montant) as total FROM REGLEMENT 
                        WHERE MONTH(date_reglement) = ? AND YEAR(date_reglement) = ?";
    $stmtMonth = mysqli_prepare($conn, $sqlCurrentMonth);
    mysqli_stmt_bind_param($stmtMonth, 'ss', $currentMonth, $year);
    mysqli_stmt_execute($stmtMonth);
    $resMonth = mysqli_stmt_get_result($stmtMonth);
    $monthTotal = mysqli_fetch_assoc($resMonth)['total'] ?? 0;
    
    $response['current_month_stats'] = [
        'month' => date('F'), // English month name, frontend can translate
        'revenue' => floatval($monthTotal)
    ];

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
