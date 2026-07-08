<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

require_once 'db_config.php';

$startDate = isset($_POST['startDate'])
    ? $_POST['startDate']
    : date('Y-m-d', strtotime('-30 days'));

$endDate = isset($_POST['endDate'])
    ? $_POST['endDate']
    : date('Y-m-d');

if (isset($_POST['add_item'])) {

    $itemId = trim($_POST['item_id']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $avail = $_POST['available'];

    $portableImageFileName =
        strtolower($itemId) . ".jpg";

    $insertStmt = $conn->prepare(
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

    if ($insertStmt) {

        $insertStmt->bind_param(
            "ssdss",
            $itemId,
            $desc,
            $price,
            $portableImageFileName,
            $avail
        );

        $insertStmt->execute();
        $insertStmt->close();

    }

    header("Location: admin_dashboard.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Executive Administrative Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

<header class="navbar">

    <div class="nav-brand">
        System Administration Hub
    </div>

    <div class="nav-user">

        Matron Mode:
        <strong>
            <?php echo htmlspecialchars($_SESSION['username']); ?>
        </strong>

        |

        .php" class="logout-link">
            Sign Out
        </a>

    </div>

</header>

<div class="admin-container">

    <section class="admin-section">

        <h3>
            Inventory Management & Item Catalog
        </h3>

        <form method="POST" class="inline-form">

            <input
                type="text"
                name="item_id"
                placeholder="Item Code (e.g. BR0003)"
                required>

            <input
                type="text"
                name="description"
                placeholder="Product Description"
                required>

            <input
                type="number"
                step="0.01"
                name="price"
                placeholder="Price (R)"
                required>

            <select name="available">

                <option value="Y">
                    Available (Y)
                </option>

                <option value="N">
                    Unavailable (N)
                </option>

            </select>

            <button
                type="submit"
                name="add_item"
                class="btn-primary"
                style="width:auto;padding:0.75rem 1.5rem;">

                Save Product

            </button>

        </form>

        <table style="margin-top:2rem;">

            <thead>

                <tr>

                    <th>Product View</th>
                    <th>Item ID</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Availability Status</th>

                </tr>

            </thead>

            <tbody>

            <?php

            $catResult =
                $conn->query(
                    "SELECT * FROM tblgroceryitems"
                );

            if (
                $catResult &&
                $catResult->num_rows > 0
            ) {

                while (
                    $item =
                    $catResult->fetch_assoc()
                ) {

                    $badge =
                        $item['Available'] === 'Y'
                        ? '<span class="badge badge-success">Active</span>'
                        : '<span class="badge badge-danger">Disabled</span>';

                    $dbImgFile =
                        !empty($item['Image'])
                        ? $item['Image']
                        : 'default.jpg';

                    $imagePath =
                        "https://matron-grocery-api.onrender.com/images/" .
                        rawurlencode($dbImgFile);

                    echo "<tr>";

                    echo "<td>
                            }'
                                alt='Product'
                                style='width:50px;height:50px;object-fit:contain;display:block;margin:auto;'>
                          </td>";

                    echo "<td>{$item['ItemID']}</td>";
                    echo "<td>{$item['Description']}</td>";

                    echo "<td>R " .
                            number_format(
                                $item['Price'],
                                2
                            ) .
                         "</td>";

                    echo "<td>{$badge}</td>";

                    echo "</tr>";

                }

            } else {

                echo "
                <tr>
                    <td colspan='5' class='empty-msg'>
                        No inventory records found.
                    </td>
                </tr>";

            }

            ?>

            </tbody>

        </table>

    </section>

    <section class="admin-section">

        <h3>
            Management Information System (MIS) Reports
        </h3>

        <form method="POST">

            <div class="date-filter-form">

                <label>
                    Timeline Window Selection:
                </label>

                <div class="date-inputs">

                    <input
                        type="date"
                        name="startDate"
                        value="<?php echo $startDate; ?>">

                    <input
                        type="date"
                        name="endDate"
                        value="<?php echo $endDate; ?>">

                </div>

            </div>

            <div class="report-buttons">

                <button
                    type="submit"
                    name="report_type"
                    value="wholesale"
                    class="btn-report">

                    1. Consolidated Wholesale List

                </button>

                <button
                    type="submit"
                    name="report_type"
                    value="popularity"
                    class="btn-report">

                    2. Item Popularity Matrix

                </button>

                <button
                    type="submit"
                    name="report_type"
                    value="participation"
                    class="btn-report">

                    3. Resident Engagement Rates

                </button>

            </div>

        </form>

        <div class="report-output-window">

        <?php

        if (
            $_SERVER["REQUEST_METHOD"] == "POST" &&
            isset($_POST['report_type'])
        ) {

            $type = $_POST['report_type'];

            if ($type === 'wholesale') {

                echo "<h4>Consolidated Wholesale Bulk Ordering List</h4>";

                $stmt = $conn->prepare(
                    "SELECT
                        gi.ItemID,
                        gi.Description,
                        SUM(mo.Quantity) AS TotalVolume,
                        SUM(mo.TotalPrice) AS CumulativeCost
                    FROM tblmemberorders mo
                    JOIN tblgroceryitems gi
                        ON mo.ItemID = gi.ItemID
                    WHERE mo.OrderDate BETWEEN ? AND ?
                    GROUP BY gi.ItemID, gi.Description
                    ORDER BY TotalVolume DESC"
                );

            } elseif ($type === 'popularity') {

                echo "<h4>Product Popularity Frequency Analysis</h4>";

                $stmt = $conn->prepare(
                    "SELECT
                        gi.ItemID,
                        gi.Description,
                        COUNT(mo.OrderID) AS Frequency,
                        IFNULL(SUM(mo.Quantity),0) AS TotalUnits
                    FROM tblgroceryitems gi
                    LEFT JOIN tblmemberorders mo
                        ON gi.ItemID = mo.ItemID
                        AND mo.OrderDate BETWEEN ? AND ?
                    GROUP BY gi.ItemID, gi.Description
                    ORDER BY Frequency DESC"
                );

            } else {

                echo "<h4>Resident System Participation Analysis</h4>";

                $stmt = $conn->prepare(
                    "SELECT
                        m.MemberID,
                        m.Name,
                        m.Surname,
                        COUNT(mo.OrderID) AS OrdersLogged
                    FROM tblmembers m
                    LEFT JOIN tblmemberorders mo
                        ON m.MemberID = mo.MemberID
                        AND mo.OrderDate BETWEEN ? AND ?
                    GROUP BY
                        m.MemberID,
                        m.Name,
                        m.Surname
                    ORDER BY OrdersLogged DESC"
                );

            }

            if ($stmt) {

                $stmt->bind_param(
                    "ss",
                    $startDate,
                    $endDate
                );

                $stmt->execute();

                $res =
                    $stmt->get_result();

                echo "<table border='1' cellpadding='5'>";

                while (
                    $row =
                    $res->fetch_assoc()
                ) {

                    echo "<tr>";

                    foreach ($row as $value) {

                        echo "<td>" .
                             htmlspecialchars($value) .
                             "</td>";

                    }

                    echo "</tr>";

                }

                echo "</table>";

                $stmt->close();

            }

        }

        ?>

        </div>

    </section>

</div>

</body>
</html>

<?php
$conn->close();
?>
