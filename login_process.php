<?php

session_start();

require_once 'db_config.php';

// ============================================================
// PROCESS LOGIN REQUEST
// ============================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username =
        trim($_POST['memberId'] ?? '');

    $password =
        trim($_POST['password'] ?? '');

    $selectedRole =
        trim($_POST['userRole'] ?? '');


    // ========================================================
    // VALIDATIONS
    // ========================================================
    if (
        $username == '' ||
        $password == '' ||
        $selectedRole == ''
    ) {

        header(
            "Location: index.php?error=" .
            urlencode("All login fields are required.")
        );

        exit();

    }


    // ========================================================
    // FETCH USER ACCOUNT
    // ========================================================
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
            "Location: index.php?error=" .
            urlencode("Database error.")
        );

        exit();

    }

    $stmt->bind_param(
        "s",
        $username
    );

    $stmt->execute();

    $result =
        $stmt->get_result();


    // ========================================================
    // USER EXISTS?
    // ========================================================
    if ($result->num_rows !== 1) {

        $stmt->close();

        header(
            "Location: index.php?error=" .
            urlencode("No matching account profile found.")
        );

        exit();

    }

    $user =
        $result->fetch_assoc();


    // ========================================================
    // PASSWORD CHECK
    // ========================================================
    if ($password !== $user['Password']) {

        $stmt->close();

        header(
            "Location: index.php?error=" .
            urlencode("Invalid password entered.")
        );

        exit();

    }


    // ========================================================
    // ROLE CHECK
    // ========================================================
    if (
        strtoupper($user['Role']) <>
        strtoupper($selectedRole)
    ) {

        $stmt->close();

        header(
            "Location: index.php?error=" .
            urlencode(
                "Selected role does not match your account."
            )
        );

        exit();

    }


    // ========================================================
    // CREATE SESSION
    // ========================================================
    $_SESSION['username'] =
        $user['Username'];

    $_SESSION['role'] =
        $user['Role'];


    // ========================================================
    // ADMIN LOGIN
    // ========================================================
    if (
        strtoupper($user['Role']) == 'ADMIN'
    ) {

        $stmt->close();
        $conn->close();

        header(
            "Location: admin_dashboard.php"
        );

        exit();

    }


    // ========================================================
    // MEMBER ACTIVE CHECK
    // ========================================================
    $memberStmt = $conn->prepare(
        "SELECT
            Active
        FROM tblmembers
        WHERE MemberID = ?"
    );

    if (!$memberStmt) {

        $stmt->close();

        header(
            "Location: index.php?error=" .
            urlencode("Database error.")
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
    $stmt->close();
    $conn->close();


    // ========================================================
    // ACTIVE MEMBER?
    // ========================================================
    if (
        $member &&
        strtoupper($member['Active']) == 'Y'
    ) {

        header(
            "Location: portal.php"
        );

        exit();

    }

    header(
        "Location: index.php?error=" .
        urlencode(
            "Access Denied: Your member profile is inactive."
        )
    );

    exit();

}

// ============================================================
// INVALID ACCESS
// ============================================================
header("Location: index.php");
exit();

?>
