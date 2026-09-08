<?php
session_start();
// If there is an error code passed back from login_process.php, catch it here
$loginError = isset($_GET['error']) ? $_GET['error'] : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Gateway - Authentication</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Baseline Reset Rule Controllers */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { width: 100%; height: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow: hidden; }

        /* 1. AUTO-SIZING FULL VIEWPORT SPLASH BANNER LAYER */
        .welcome-splash-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;   /* Forces structural width to map exactly to browser window edge */
            height: 100vh;  /* Forces structural height to map exactly to browser window edge */
            
            /* FIXED PATH: We look inside the local images directory using lowercase extension */
            background: url('images/welcomeImg.png') no-repeat center center;
            background-size: cover; /* Auto-sizes cleanly across all screen monitors without distortion */
            z-index: 100;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            padding-bottom: 6rem;
            cursor: pointer;
            transition: transform 0.6s cubic-bezier(0.77, 0, 0.175, 1);
        }

        /* Dark overlay filter panel layer to keep action button contrasting cleanly */
        .welcome-splash-canvas::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.15);
            z-index: 1;
        }

        .cta-proceed-button {
            position: relative;
            z-index: 2;
            background: #007bc4;
            color: #ffffff;
            font-size: 1.2rem;
            font-weight: bold;
            padding: 1rem 2.5rem;
            border: none;
            border-radius: 50px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.4);
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            animation: pulseGlow 2s infinite;
        }

        @keyframes pulseGlow {
            0% { transform: scale(1); box-shadow: 0 10px 25px rgba(0,123,196,0.4); }
            50% { transform: scale(1.04); box-shadow: 0 10px 35px rgba(0,123,196,0.7); }
            100% { transform: scale(1); box-shadow: 0 10px 25px rgba(0,123,196,0.4); }
        }

        /* Splash dismissal transition framework utility */
        .canvas-dismissed { transform: translateY(-100%); }

        /* 2. BACKGROUND CORE LOGIN TERMINAL WRAPPER */
        .login-core-wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f3f4f6;
        }

        /* Structural utility fallbacks for inline pill alignment configuration */
        .role-selector-container {
            position: relative;
            display: flex;
            width: 100%;
            background: #e5e7eb;
            border-radius: 30px;
            margin: 1.5rem 0;
            padding: 4px;
        }
        .role-toggle-btn {
            position: relative;
            z-index: 2;
            flex: 1;
            background: transparent;
            border: none;
            padding: 0.75rem;
            font-weight: bold;
            color: #4b5563;
            cursor: pointer;
            transition: color 0.3s;
        }
        .role-toggle-btn.active { color: #ffffff; }
        .toggle-bg-pill {
            position: absolute;
            top: 4px; bottom: 4px;
            width: calc(50% - 4px);
            background: #007bc4;
            border-radius: 26px;
            transition: transform 0.3s cubic-bezier(0.25, 1, 0.5, 1);
            z-index: 1;
        }
        .member-selected { transform: translateX(0); }
        .admin-selected { transform: translateX(100%); }

        .alert-error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.75rem;
            border-radius: 6px;
            font-size: 0.85rem;
            margin-bottom: 1rem;
            text-align: left;
            border-left: 4px solid #dc2626;
        }
        hr { border: 0; height: 1px; background: #e5e7eb; margin: 1rem 0; }
    </style>
</head>
<body class="auth-body">

    <div class="welcome-splash-canvas" id="welcomeSplash" onclick="dismissWelcomeSplash()">
        <button type="button" class="cta-proceed-button">Access Login Portal</button>
    </div>

    <div class="login-core-wrapper">
        <div class="login-container">
            <h2>Home Grocery System</h2>
            <p>Select your system role profile to access account operations.</p>
            
            <div class="role-selector-container">
                <div id="activeToggleSelectorBg" class="toggle-bg-pill member-selected"></div>
                <button type="button" class="role-toggle-btn active" id="toggleMemberBtn" onclick="selectLoginRole('Member')">Resident Member</button>
                <button type="button" class="role-toggle-btn" id="toggleAdminBtn" onclick="selectLoginRole('Admin')">Matron / Admin</button>
            </div>

            <hr>

            <?php if (!empty($loginError)): ?>
                <div class="alert-error-message">
                    <strong>Authentication Failed:</strong> <?php echo htmlspecialchars($loginError); ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" action="login_process.php" method="POST">
                <input type="hidden" id="selectedUserRole" name="userRole" value="Member">

                <div class="form-group">
                    <label for="memberId" id="usernameFieldLabel">Resident Member ID</label>
                    <input type="text" id="memberId" name="memberId" placeholder="e.g., 01-01" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="password">Password Secure Key</label>
                    <input type="password" id="password" name="password" placeholder="Enter security password" required>
                </div>
                <button type="submit" class="btn-primary">Authenticate Account</button>
            </form>
        </div>
    </div>

    <script>
        // Smoothly slide banner away out of structural frame context limits
        function dismissWelcomeSplash() {
            const splashLayer = document.getElementById('welcomeSplash');
            splashLayer.classList.add('canvas-dismissed');
            
            setTimeout(() => {
                splashLayer.style.display = 'none';
            }, 600);
        }

        // Drop splash presentation layer instantly if authentication fails back with server errors
        <?php if (!empty($loginError)): ?>
            document.getElementById('welcomeSplash').style.display = 'none';
        <?php endif; ?>

        // Handle structural label configuration modifications contextually based on active toggle states
        function selectLoginRole(role) {
            const hiddenRoleField = document.getElementById('selectedUserRole');
            const toggleBg = document.getElementById('activeToggleSelectorBg');
            const memberBtn = document.getElementById('toggleMemberBtn');
            const adminBtn = document.getElementById('toggleAdminBtn');
            const fieldLabel = document.getElementById('usernameFieldLabel');
            const inputField = document.getElementById('memberId');

            hiddenRoleField.value = role;

            if (role === 'Admin') {
                toggleBg.className = "toggle-bg-pill admin-selected";
                memberBtn.classList.remove('active');
                adminBtn.classList.add('active');
                
                fieldLabel.innerText = "Administrator Username";
                inputField.placeholder = "e.g., matron_admin";
            } else {
                toggleBg.className = "toggle-bg-pill member-selected";
                adminBtn.classList.remove('active');
                memberBtn.classList.add('active');
                
                fieldLabel.innerText = "Resident Member ID";
                inputField.placeholder = "e.g., 01-01";
            }
        }
    </script>
</body>
</html>