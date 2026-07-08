<?php

session_start();

header('Content-Type: application/json');

require_once 'db_config.php';


// ============================================================
// SESSION VALIDATION
// ============================================================
if (
    !isset($_SESSION['username']) ||
    $_SESSION['role'] !== 'Member'
) {

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized user session."
    ]);

    exit();

}


// ============================================================
// READ JSON INPUT
// ============================================================
$inputData = json_decode(
    file_get_contents('php://input'),
    true
);

if (
    !$inputData ||
    !isset($inputData['cart']) ||
    count($inputData['cart']) === 0
) {

    echo json_encode([
        "success" => false,
        "message" => "Your cart is empty."
    ]);

    exit();

}


$memberId =
    $_SESSION['username'];

$orderDate =
    date('Y-m-d');

$successCount = 0;


// ============================================================
// INSERT ORDERS
// ============================================================
$stmt = $conn->prepare(
    "INSERT INTO tblmemberorders
    (
        MemberID,
        OrderDate,
        ItemID,
        Quantity,
        TotalPrice
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?
    )"
);

if (!$stmt) {

    echo json_encode([
        "success" => false,
        "message" => $conn->error
    ]);

    exit();

}


// ============================================================
// PROCESS CART ITEMS
// ============================================================
foreach ($inputData['cart'] as $item) {

    $itemId =
        trim($item['id'] ?? '');

    $qty =
        intval($item['qty'] ?? 0);

    $price =
        floatval($item['price'] ?? 0);

    if (
        $itemId === '' ||
        $qty <= 0
    ) {

        continue;

    }

    $totalPrice =
        $price * $qty;

    $stmt->bind_param(
        "sssid",
        $memberId,
        $orderDate,
        $itemId,
        $qty,
        $totalPrice
    );

    if ($stmt->execute()) {

        $successCount++;

    }

}

$stmt->close();


// ============================================================
// RESPONSE
// ============================================================
if ($successCount > 0) {

    echo json_encode([
        "success" => true,
        "message" => "Order submitted successfully."
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "No order records were saved."
    ]);

}

$conn->close();

?>
``
