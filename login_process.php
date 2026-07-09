<?php

session_start();

require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username =
        trim($_POST['memberId']);

    $password =
        trim($_POST['password']);

    $selectedRole =
        trim($_POST['userRole']);

    $stmt = $conn->prepare(
        "SELECT
            Username,
            Password,
            Role
         FROM tblusers
         WHERE Username = ?"
    );

    $stmt->bind_param(
        "s",
        $username
    );

    $stmt->execute();

    $result =
        $stmt->get_result();

    if ($result->num_rows === 1) {

        $user =
            $result->fetch_assoc();

        if (
            $password ===
            $user['Password']
        ) {

            if (
                $user['Role'] !==
                $selectedRole
            ) {

                header(
                    "Location:index.php?error=Role mismatch"
                );

                exit();

            }

            $_SESSION['username'] =
                $user['Username'];

            $_SESSION['role'] =
                $user['Role'];

            if (
                $user['Role'] === 'Admin'
            ) {

                header(
                    "Location: admin_dashboard.php"
                );

                exit();

            }

            $memberStmt =
                $conn->prepare(
                    "SELECT Active
                     FROM tblmembers
                     WHERE MemberID = ?"
                );

            $memberStmt->bind_param(
                "s",
                $user['Username']
            );

            $memberStmt->execute();

            $member =
                $memberStmt
                ->get_result()
                ->fetch_assoc();

            if (
                $member &&
                $member['Active'] === 'Y'
            ) {

                header(
                    "Location: portal.php"
                );

                exit();

            }

            header(
                "Location:index.php?error=Account inactive"
            );

            exit();

        }

        header(
            "Location:index.php?error=Invalid password"
        );

        exit();

    }

    header(
        "Location:index.php?error=Account not found"
    );

    exit();

}

?>
