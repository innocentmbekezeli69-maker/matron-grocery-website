<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['memberId']);
    $password = trim($_POST['password']);
    $selectedRole = trim($_POST['userRole']); // Capture choice from toggle flag

    // Fetch account details safely using a prepared statement
    $stmt = $conn->prepare("SELECT Username, Password, Role FROM tblUsers WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if ($password === $user['Password']) {
            // Strict role verification gate mapping logic block
            if ($user['Role'] !== $selectedRole) {
                echo "<script>alert('Role Misalignment: Selected gateway profile does not match your system database parameters.'); window.location.href='index.php';</script>";
                exit();
            }

            // Establish active global secure runtime parameters context
            $_SESSION['username'] = $user['Username'];
            $_SESSION['role'] = $user['Role'];

            if ($user['Role'] === 'Admin') {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                // Secondary check: verify if the resident account is flagged active
                $memberStmt = $conn->prepare("SELECT Active FROM tblMembers WHERE MemberID = ?");
                $memberStmt->bind_param("s", $user['Username']);
                $memberStmt->execute();
                $memberResult = $memberStmt->get_result();
                $member = $memberResult->fetch_assoc();

                if ($member && $member['Active'] === 'Y') {
                    header("Location: portal.php");
                    exit();
                } else {
                    echo "<script>alert('Access Denied: Your member profile is currently set to Inactive.'); window.location.href='index.php';</script>";
                }
                $memberStmt->close();
            }
        } else {
            echo "<script>alert('Invalid Password entered.'); window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('No matching account profile found.'); window.location.href='index.php';</script>";
    }
    $stmt->close();
}
$conn->close();
?>