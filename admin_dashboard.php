<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}
require_once 'db_config.php';

$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : date('Y-m-d');

// Item Injection Event Controller
if (isset($_POST['add_item'])) {
    $itemId = trim($_POST['item_id']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $avail = $_POST['available'];

    // FIX 1: Auto-generate the correct lowercase portable image filename string matching VB.NET logic
    $portableImageFileName = strtolower($itemId) . ".jpg";

    // FIX 2: Included 'Image' inside the SQL Insert parameters
    $insertStmt = $conn->prepare("INSERT INTO tblGroceryItems (ItemID, Description, Price, Image, Available) VALUES (?, ?, ?, ?, ?)");
    if ($insertStmt) {
        $insertStmt->bind_param("ssdss", $itemId, $desc, $price, $portableImageFileName, $avail);
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
        <div class="nav-brand">System Administration Hub</div>
        <div class="nav-user">Matron Mode: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> | <a href="logout.php" class="logout-link">Sign Out</a></div>
    </header>

    <div class="admin-container">
        <section class="admin-section">
            <h3>Inventory Management & Item Catalog</h3>
            <form method="POST" class="inline-form">
                <input type="text" name="item_id" placeholder="Item Code (e.g. BR0003)" required>
                <input type="text" name="description" placeholder="Product Description" required>
                <input type="number" step="0.01" name="price" placeholder="Price (R)" required>
                <select name="available">
                    <option value="Y">Available (Y)</option>
                    <option value="N">Unavailable (N)</option>
                </select>
                <button type="submit" name="add_item" class="btn-primary" style="width:auto; padding:0.75rem 1.5rem;">Save Product</button>
            </form>

            <table style="margin-top: 2rem;">
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
                    // Executing catalog query with error handling setup
                    $catResult = $conn->query("SELECT * FROM tblGroceryItems");
                    if ($catResult && $catResult->num_rows > 0) {
                        while($item = $catResult->fetch_assoc()) {
                            $badge = $item['Available'] === 'Y' ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Disabled</span>';
                            
                            // FIX 4: Pull image string name or provide a default fallback image layout 
                            $dbImgFile = !empty($item['Image']) ? $item['Image'] : 'default.jpg';
                            $imagePath = "images/" . htmlspecialchars($dbImgFile);

                            // Cleaned up outputs using htmlspecialchars to prevent XSS breakout issues
                            echo "<tr>";
                            // FIX 5: Rendered the actual HTML image thumbnail tag 
                            echo "<td><img src='{$imagePath}' alt='Product' style='width:50px; height:50px; object-fit:contain; display:block; margin:auto;'></td>";
                            echo "<td>" . htmlspecialchars($item['ItemID']) . "</td>";
                            echo "<td>" . htmlspecialchars($item['Description']) . "</td>";
                            echo "<td>R " . number_format($item['Price'], 2) . "</td>";
                            echo "<td>{$badge}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='empty-msg'>No inventory records found. Make sure the 'tblGroceryItems' table exists and has been populated.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>

        <section class="admin-section">
            <h3>Management Information System (MIS) Reports</h3>
            <form method="POST">
                <div class="date-filter-form">
                    <label>Timeline Window Selection:</label>
                    <div class="date-inputs">
                        <input type="date" name="startDate" value="<?php echo htmlspecialchars($startDate); ?>">
                        <input type="date" name="endDate" value="<?php echo htmlspecialchars($endDate); ?>">
                    </div>
                </div>
                <div class="report-buttons">
                    <button type="submit" name="report_type" value="wholesale" class="btn-report">1. Consolidated Wholesale List</button>
                    <button type="submit" name="report_type" value="popularity" class="btn-report">2. Item Popularity Matrix</button>
                    <button type="submit" name="report_type" value="participation" class="btn-report">3. Resident Engagement Rates</button>
                </div>
            </form>

            <div class="report-output-window">
                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['report_type'])) {
                    $type = $_POST['report_type'];
                    
                    if ($type === 'wholesale') {
                        echo "<h4>Consolidated Wholesale Bulk Ordering List</h4>";
                        $stmt = $conn->prepare("SELECT gi.ItemID, gi.Description, SUM(mo.Quantity) As TotalVolume, SUM(mo.TotalPrice) As CumulativeCost FROM tblMemberOrders mo JOIN tblGroceryItems gi ON mo.ItemID = gi.ItemID WHERE mo.OrderDate BETWEEN ? AND ? GROUP BY gi.ItemID, gi.Description ORDER BY TotalVolume DESC");
                        if($stmt) {
                            $stmt->bind_param("ss", $startDate, $endDate);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            echo "<table><thead><tr><th>Item ID</th><th>Description</th><th>Total Units Demanded</th><th>Combined Cost</th></tr></thead><tbody>";
                            while($r = $res->fetch_assoc()) {
                                echo "<tr><td>" . htmlspecialchars($r['ItemID']) . "</td><td>" . htmlspecialchars($r['Description']) . "</td><td>" . htmlspecialchars($r['TotalVolume']) . "</td><td>R " . number_format($r['CumulativeCost'], 2) . "</td></tr>";
                            }
                            echo "</tbody></table>";
                            $stmt->close();
                        }
                    } 
                    elseif ($type === 'popularity') {
                        echo "<h4>Product Popularity Frequency Analysis</h4>";
                        $stmt = $conn->prepare("SELECT gi.ItemID, gi.Description, COUNT(mo.OrderID) As Frequency, IFNULL(SUM(mo.Quantity),0) As TotalUnits FROM tblGroceryItems gi LEFT JOIN tblMemberOrders mo ON gi.ItemID = mo.ItemID AND mo.OrderDate BETWEEN ? AND ? GROUP BY gi.ItemID, gi.Description ORDER BY Frequency DESC");
                        if($stmt) {
                            $stmt->bind_param("ss", $startDate, $endDate);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            echo "<table><thead><tr><th>Item ID</th><th>Description</th><th>Order Occurrences</th><th>Gross Units Distributed</th></tr></thead><tbody>";
                            while($r = $res->fetch_assoc()) {
                                echo "<tr><td>" . htmlspecialchars($r['ItemID']) . "</td><td>" . htmlspecialchars($r['Description']) . "</td><td>" . htmlspecialchars($r['Frequency']) . "</td><td>" . htmlspecialchars($r['TotalUnits']) . "</td></tr>";
                            }
                            echo "</tbody></table>";
                            $stmt->close();
                        }
                    } 
                    elseif ($type === 'participation') {
                        echo "<h4>Resident System Participation Analysis</h4>";
                        $stmt = $conn->prepare("SELECT m.MemberID, m.Name, m.Surname, COUNT(mo.OrderID) As OrdersLogged FROM tblMembers m LEFT JOIN tblMemberOrders mo ON m.MemberID = mo.MemberID AND mo.OrderDate BETWEEN ? AND ? GROUP BY m.MemberID, m.Name, m.Surname ORDER BY OrdersLogged DESC");
                        if($stmt) {
                            $stmt->bind_param("ss", $startDate, $endDate);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            echo "<table><thead><tr><th>Member ID</th><th>Full Name</th><th>Total Submissions Logged</th></tr></thead><tbody>";
                            while($r = $res->fetch_assoc()) {
                                echo "<tr><td>" . htmlspecialchars($r['MemberID']) . "</td><td>" . htmlspecialchars($r['Name']) . " " . htmlspecialchars($r['Surname']) . "</td><td>" . htmlspecialchars($r['OrdersLogged']) . "</td></tr>";
                            }
                            echo "</tbody></table>";
                            $stmt->close();
                        }
                    }
                }
                ?>
            </div>
        </section>
    </div>
</body>
</html>
<?php $conn->close(); ?>
