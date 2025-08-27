<?php
// Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si l'utilisateur est déjà connecté, le rediriger vers le tableau de bord approprié
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if (isset($_SESSION['role'])) {
        // Déterminer le chemin de base
        $baseDir = dirname($_SERVER['SCRIPT_NAME']);
        if ($baseDir === '/') {
            $adminPath = '/admin-dashboard.php';
            $userPath = '/user-dashboard.php';
        } else {
            if (substr($baseDir, -1) !== '/') {
                $baseDir .= '/';
            }
            $adminPath = $baseDir . 'admin-dashboard.php';
            $userPath = $baseDir . 'user-dashboard.php';
        }
        
        if (in_array($_SESSION['role'], ['admin', 'manager'])) {
            header('Location: ' . $adminPath);
        } else if ($_SESSION['role'] === 'user') {
            header('Location: ' . $userPath);
        }
        exit;
    }
}

// Récupérer le message d'erreur éventuel
$loginMessage = '';
if (isset($_SESSION['login_message'])) {
    $loginMessage = $_SESSION['login_message'];
    unset($_SESSION['login_message']);  // Effacer le message après l'avoir récupéré
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Marsa Maroc</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background: url('assets/images/1_4.png') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            color: white;
        }
        
        .login-container {
            background-color: rgba(0, 32, 84, 0.9);
            width: 400px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .login-card-header {
            background-color: rgba(0, 38, 100, 0.95);
            padding: 30px 20px;
            text-align: center;
            border-bottom: 2px solid rgba(219, 43, 57, 0.7);
            position: relative;
        }
        
        .login-card-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 30%;
            width: 40%;
            height: 2px;
            background: #ffffff;
        }
        
        .user-icon {
            width: 80px;
            height: 80px;
            background: #db2b39;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }
        
        .user-icon i {
            font-size: 40px;
            color: white;
        }
        
        .login-title {
            color: white;
            font-size: 28px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .login-body {
            padding: 30px;
            background: rgba(128, 128, 128, 0.3); /* Gris très transparent */
        }
        
        .input-label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            margin-bottom: 8px;
            display: block;
            font-weight: 500;
        }
        
        .input-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .input-field {
            width: 100%;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            padding: 15px;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        .input-field:focus {
            border-color: #ce0e2d;
            box-shadow: 0 0 0 2px rgba(206, 14, 45, 0.2);
            outline: none;
            background: rgba(255, 255, 255, 0.15);
        }
        
        .input-field:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.35);
        }
        
        .input-field::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .remember-checkbox {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            accent-color: #ce0e2d;
            cursor: pointer;
        }
        
        .remember-text {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            cursor: pointer;
        }
        
        .login-button {
            width: 100%;
            background: rgba(206, 14, 45, 0.85);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 15px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(3px);
        }
        
        .login-button:hover {
            background: rgba(206, 14, 45, 0.95);
            box-shadow: 0 4px 15px rgba(206, 14, 45, 0.4);
            transform: translateY(-2px);
        }
        
        .login-button::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.5s ease-in-out;
        }
        
        .login-button:hover::after {
            transform: translateX(100%);
        }
        
        .login-footer {
            text-align: center;
            padding: 0 30px 30px;
            background: rgba(0, 32, 84, 0.7);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .account-text, .forgot-link, .back-link {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            text-decoration: none;
            margin: 5px 0;
            display: block;
        }
        
        .forgot-link {
            color: #db2b39;
            margin: 10px 0;
            font-weight: 500;
        }
        
        .attribution {
            position: absolute;
            bottom: 10px;
            color: rgba(255, 255, 255, 0.4);
            font-size: 12px;
            text-align: center;
        }
        
        .loading {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .error-message {
            background: rgba(255, 64, 129, 0.2);
            color: white;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 14px;
            text-align: center;
        }
            filter: blur(50px);
        }

        .shape2 {
            width: 600px;
            height: 600px;
            bottom: -200px;
            left: -100px;
            background: linear-gradient(135deg, #ff3333ff, #ff3939ff);
            opacity: 0.2;
            filter: blur(80px);
        }

        .shape3 {
            width: 300px;
            height: 300px;
            top: 30%;
            right: 10%;
            background: linear-gradient(135deg, #00ccff, #3366ff);
            opacity: 0.2;
            filter: blur(40px);
        }

        .dots {
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(circle, rgba(255, 255, 255, 0.15) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .geometric-shape {
            position: absolute;
            border: 1px solid rgba(255, 255, 255, 0.2);
            z-index: -1;
        }

        .geo-1 {
            width: 200px;
            height: 200px;
            border-radius: 24px;
            transform: rotate(45deg);
            top: 10%;
            left: 5%;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .geo-2 {
            width: 150px;
            height: 150px;
            bottom: 10%;
            right: 10%;
            transform: rotate(30deg);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Card de login */
        .login-container {
            background: rgba(128, 128, 128, 0.4); /* Gris transparent */
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1), inset 0 0 30px rgba(0, 0, 0, 0.05);
            width: 400px;
            overflow: hidden;
            position: relative;
            z-index: 10;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
        }

        .login-card-header {
            background: rgba(128, 128, 128, 0.35); /* Gris très transparent */
            text-align: center;
            padding: 30px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .user-icon {
            width: 80px;
            height: 80px;
            background: rgba(206, 14, 45, 0.85); /* Rouge Marsa Maroc semi-transparent */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .user-icon i {
            font-size: 42px;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .login-title {
            color: white;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .login-body {
            padding: 30px;
        }

        .input-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            margin-bottom: 6px;
            display: block;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-field {
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 8px;
            padding: 15px;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .input-field::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .input-field:focus {
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
            outline: none;
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .remember-checkbox {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            cursor: pointer;
        }

        .remember-text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
        }

        .login-button {
            width: 100%;
            background: #ff2c2cff;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .login-button:hover {
            background: #ff6699;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .login-footer {
            text-align: center;
            padding: 0 30px 30px;
        }

        .account-text {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 15px;
            font-size: 14px;
        }

        .forgot-link {
            color: #FF4081;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-block;
            margin-top: 5px;
        }

        .forgot-link:hover {
            color: #ff6699;
            text-decoration: underline;
        }

        .attribution {
            position: absolute;
            bottom: 10px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.4);
            text-align: center;
            width: 100%;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 20px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: white;
        }

        /* Loading Animation */
        .loading {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Animation d'apparition */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-container {
            animation: fadeIn 0.5s ease-out forwards;
        }

        /* Erreur messages */
        .error-message {
            background: rgba(206, 14, 45, 0.2);
            color: white;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 14px;
            text-align: center;
            border: 1px solid rgba(206, 14, 45, 0.3);
        }

        /* Responsive design */
        @media (max-width: 480px) {
            body {
                padding: var(--spacing-md);
            }
            
            .login-container {
                max-width: 100%;
            }
            
            .login-header {
                padding: var(--spacing-xl);
            }
            
            .login-form {
                padding: var(--spacing-xl);
            }

            .login-logo {
                width: 80px;
                height: 80px;
                font-size: 2.5rem;
            }

            .login-title {
                font-size: var(--font-size-3xl);
            }
        }
    </style>
</head>
<body>
    <!-- Card de login -->
    <div class="login-container">
        <div class="login-card-header">
            <div class="user-icon">
                <i class="fas fa-anchor"></i>
            </div>
            <h2 class="login-title">Marsa Maroc</h2>
            <p style="color: rgba(255, 255, 255, 0.8); font-size: 14px; margin-top: 5px;">Système de Gestion Portuaire</p>
        </div>

        <div class="login-body">
            <form id="loginForm" action="#" method="post">
                <div class="input-group">
                    <label for="username" class="input-label">Username *</label>
                    <input type="text" id="username" name="username" class="input-field" placeholder="Enter your Username" required>
                </div>
                
                <div class="input-group">
                    <label for="password" class="input-label">Password *</label>
                    <input type="password" id="password" name="password" class="input-field" placeholder="Enter your Password" required>
                </div>
                
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember" class="remember-checkbox">
                    <label for="remember" class="remember-text">Remember me</label>
                </div>
                
                <button type="submit" class="login-button" id="loginBtn">
                    <span class="btn-text">CONNEXION</span>
                    <div class="loading"></div>
                </button>
                
                <div id="errorMessage" class="error-message" style="display: <?php echo !empty($loginMessage) ? 'block' : 'none'; ?>;">
                    <?php if (!empty($loginMessage)): ?>
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($loginMessage); ?>
                    <?php endif; ?>
                </div>
            </form>
        </div>

                        <!-- Texte supprimé comme demandé -->
    </div>

    <div class="attribution">
        
    </div>

    <script>
        // Form Submission with Loading
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const loginBtn = document.getElementById('loginBtn');
            const btnText = loginBtn.querySelector('.btn-text');
            const loading = loginBtn.querySelector('.loading');
            const errorDiv = document.getElementById('errorMessage');
            
            // Clear previous errors
            errorDiv.style.display = 'none';
            
            // Get form data
            const formData = new FormData(this);
            const username = formData.get('username');
            const password = formData.get('password');
            
            // Validate form
            if (!username || !password) {
                showError('Veuillez remplir tous les champs');
                return;
            }
            
            // Show loading
            btnText.style.display = 'none';
            loading.style.display = 'block';
            loginBtn.disabled = true;
            
            try {
                // Call authentication API
                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ username, password })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Redirect based on user role from database
                    if (result.user.role === 'admin' || result.user.role === 'manager') {
                        window.location.href = 'admin-dashboard.php';
                    } else {
                        window.location.href = 'user-dashboard.php';
                    }
                } else {
                    showError(result.error || 'Erreur d\'authentification');
                }
            } catch (error) {
                console.error('Erreur de connexion:', error);
                showError('Erreur de connexion au serveur. Veuillez réessayer.');
            } finally {
                // Hide loading
                btnText.style.display = 'block';
                loading.style.display = 'none';
                loginBtn.disabled = false;
            }
        });
        
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    ${message}
                </div>
            `;
            errorDiv.style.display = 'block';
        }
        
        function showForgotPassword() {
            alert('Contactez l\'administrateur système pour réinitialiser votre mot de passe.\n\nEmail: admin@marsamaroc.ma\nTél: +212 5XX XX XX XX');
        }
        
        // Auto-focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
        
        // Clear error on input
        document.getElementById('username').addEventListener('input', function() {
            document.getElementById('errorMessage').style.display = 'none';
        });
        
        document.getElementById('password').addEventListener('input', function() {
            document.getElementById('errorMessage').style.display = 'none';
        });
    </script>
</body>
</html>
 