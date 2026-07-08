<?php
session_start();
header('Content-Type: application/json');
require_once 'db_config.php';

// Security check: Verify the user has an active, authenticated member session
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Member') {
    echo json_encode(["success" => false, "message" => "Unauthorized User Session Context."]);
    exit();
}

// Receive the raw JSON payload containing the shopping cart array from the browser frontend
$inputData = json_decode(file_get_contents('php://input'), true);

if (!empty($inputData['cart'])) {
    $memberId = $_SESSION['username'];
    $orderDate = date('Y-m-d'); // Captures the exact system timestamp of submission
    $successCount = 0;

    // Use a safe MySQL prepared statement to prevent injection vulnerabilities
    $stmt = $conn->prepare("INSERT INTO tblMemberOrders (MemberID, OrderDate, ItemID, Quantity, TotalPrice) VALUES (?, ?, ?, ?, ?)");

    if ($stmt) {
        foreach ($inputData['cart'] as $item) {
            $itemId = $item['id'];
            $qty = intval($item['qty']);
            $totalPrice = floatval($item['price']) * $qty;

            // Bind the structural row types matching your table mapping script parameters
            $stmt->bind_param("sssid", $memberId, $orderDate, $itemId, $qty, $totalPrice);
            
            if ($stmt->execute()) {
                $successCount++;
            }
        }
        $stmt->close();
    }

    // Return a JSON operational status packet back to the browser interface
    if ($successCount > 0) {
        echo json_encode(["success" => true, "message" => "All item segments stored cleanly in the database system."]);
    } else {
        echo json_encode(["success" => false, "message" => "Database rejected transaction execution inputs."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Empty basket arrays compiled."]);
}

$conn->close();
?>