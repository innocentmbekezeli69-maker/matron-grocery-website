<?php

session_start();

require_once "db_config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

$username = trim($_POST['memberId'] ?? '');
$password = trim($_POST['password'] ?? '');
$selectedRole = trim($_POST['userRole'] ?? '');

if (
    empty($username) ||
    empty($password) ||
    empty($selectedRole)
) {
    header("Location: index.php?error=Missing login details");
    exit();
}

$stmt = $conn->prepare(
    "SELECT
        Username,
        Password,
        Role
     FROM tblusers
     WHERE Username = ?"
);

if (!$stmt) {

    header(
        "Location: index.php?error=Database error"
    );

    exit();
}

$stmt->bind_param(
    "s",
    $username
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    header(
        "Location: index.php?error=Account not found"
    );

    exit();
}

$user = $result->fetch_assoc();

$stmt->close();

/*
    Original system used plain text passwords.
    Compare directly to preserve compatibility.
*/

if ($password !== $user['Password']) {

    header(
        "Location: index.php?error=Invalid password"
    );

    exit();
}

if ($user['Role'] !== $selectedRole) {

    header(
        "Location: index.php?error=Role mismatch"
    );

    exit();
}

$_SESSION['username'] = $user['Username'];
$_SESSION['role'] = $user['Role'];

if ($user['Role'] === 'Admin') {

    header(
        "Location: admin_dashboard.php"
    );

    exit();
}

/*
    Member Validation
*/

$memberStmt = $conn->prepare(
    "SELECT
        Active
     FROM tblmembers
     WHERE MemberID = ?"
);

if (!$memberStmt) {

    header(
        "Location: index.php?error=Database error"
    );

    exit();
}

$memberStmt->bind_param(
    "s",
    $user['Username']
);

$memberStmt->execute();

$memberResult =
    $memberStmt->get_result();

$member =
    $memberResult->fetch_assoc();

$memberStmt->close();

if (
    !$member ||
    $member['Active'] !== 'Y'
) {

    session_destroy();

    header(
        "Location: index.php?error=Account inactive"
    );

    exit();
}

header(
    "Location: portal.php"
);

exit();

?>
