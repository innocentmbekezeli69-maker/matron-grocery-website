<?php

session_start();

if (
    !isset($_SESSION['username']) ||
    $_SESSION['role'] !== 'Admin'
) {
    header("Location: index.php");
    exit();
}

require_once 'db_config.php';

$startDate =
    $_POST['startDate']
    ?? date('Y-m-d', strtotime('-30 days'));

$endDate =
    $_POST['endDate']
    ?? date('Y-m-d');

if (isset($_POST['add_item'])) {

    $itemId =
        trim($_POST['item_id']);

    $description =
        trim($_POST['description']);

    $price =
        floatval($_POST['price']);

    $available =
        $_POST['available'];

    $image =
        strtolower($itemId) . ".jpg";

    $stmt = $conn->prepare(
        "INSERT INTO tblgroceryitems
        (
            ItemID,
            Description,
            Price,
            Image,
            Available
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

    if ($stmt) {

        $stmt->bind_param(
            "ssdss",
            $itemId,
            $description,
            $price,
            $image,
            $available
        );

        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin_dashboard.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>
    Administrative Dashboard
</title>

<link rel="stylesheet" href="styles.css">

</head>

<body>

<header class="navbar">

    <div class="nav-brand">
        Administrative Dashboard
    </div>

    <div class="nav-user">

        Logged In:

        <strong>
            <?php echo htmlspecialchars($_SESSION['username']); ?>
        </strong>

        |

        <a href="logout.php" class="logout-link">
            Sign Out
        </a>

    </div>

</header>

<div class="admin-container">

<section class="admin-section">

    <h3>
        Inventory Management
    </h3>

    <form
        method="POST"
        class="inline-form">

        <input
            type="text"
            name="item_id"
            placeholder="Item Code"
            required>

        <input
            type="text"
            name="description"
            placeholder="Description"
            required>

        <input
            type="number"
            step="0.01"
            name="price"
            placeholder="Price"
            required>

        <select name="available">

            <option value="Y">
                Available
            </option>

            <option value="N">
                Disabled
            </option>

        </select>

        <button
            type="submit"
            name="add_item"
            class="btn-primary">

            Save Product

        </button>

    </form>

    <table>

        <thead>

            <tr>

                <th>Image</th>
                <th>Item ID</th>
                <th>Description</th>
                <th>Price</th>
                <th>Status</th>

            </tr>

        </thead>

        <tbody>

        <?php

        $products =
            $conn->query(
                "
