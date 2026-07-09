<?php

session_start();

$loginError =
    $_GET['error'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

<title>
    Home Grocery System
</title>

<link rel="stylesheet" href="styles.css">

</head>

<body class="auth-body">

<div class="login-container">

    <h2>
        Home Grocery System
    </h2>

    <p>
        Select your system role and login.
    </p>

    <?php if (!empty($loginError)): ?>

    <div class="alert-error-message">

        <?php
        echo htmlspecialchars($loginError);
        ?>

    </div>

    <?php endif; ?>

    <div class="role-selector-container">

        <div
            id="togglePill"
            class="toggle-bg-pill member-selected">

        </div>

        <button
            type="button"
            id="memberBtn"
            class="role-toggle-btn active"
            onclick="selectRole('Member')">

            Member

        </button>

        <button
            type="button"
            id="adminBtn"
            class="role-toggle-btn"
            onclick="selectRole('Admin')">

            Admin

        </button>

    </div>

    <hr>

    login_process.php

        <input
            type="hidden"
            name="userRole"
            id="selectedUserRole"
            value="Member">

        <div class="form-group">

            <label
                id="userLabel">

                Member ID

            </label>

            <input
                type="text"
                name="memberId"
                id="memberId"
                required>

        </div>

        <div class="form-group">

            <label>
                Password
            </label>

            <input
                type="password"
                name="password"
                required>

        </div>

        <button
            type="submit"
            class="btn-primary">

            Login

        </button>

    </form>

</div>

<script>

function selectRole(role)
{
    document
        .getElementById(
            'selectedUserRole'
        )
        .value =
        role;

    const memberBtn =
        document.getElementById(
            'memberBtn'
        );

    const adminBtn =
        document.getElementById(
            'adminBtn'
        );

    const pill =
        document.getElementById(
            'togglePill'
        );

    const label =
        document.getElementById(
            'userLabel'
        );

    if(role === 'Admin')
    {
        pill.className =
            'toggle-bg-pill admin-selected';

        memberBtn.classList.remove(
            'active'
        );

        adminBtn.classList.add(
            'active'
        );

        label.innerText =
            'Administrator Username';
    }
    else
    {
        pill.className =
            'toggle-bg-pill member-selected';

        adminBtn.classList.remove(
            'active'
        );

        memberBtn.classList.add(
            'active'
        );

        label.innerText =
            'Member ID';
    }
}

</script>

</body>

</html>
