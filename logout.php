<?php
// 1. Start the session to gain access to the current session variables
session_start();

// 2. Unset all of the session variables
$_SESSION = array();

// 3. If it's desired to kill the session entirely, delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finally, destroy the session on the server
session_destroy();

// 5. Redirect back to the login gateway
header("Location: index.php");
exit();
?>