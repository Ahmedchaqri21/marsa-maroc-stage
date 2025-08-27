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
            background: #1a0033;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            overflow: hidden;
        }

        /* Éléments géométriques en arrière-plan */
        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
            overflow: hidden;
        }

        .bg-shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
        }

        .shape1 {
            width: 800px;
            height: 800px;
            top: -400px;
            right: -200px;
            background: linear-gradient(135deg, #4a0072, #6600cc);
            opacity: 0.4;
            filter: blur(50px);
        }

        .shape2 {
            width: 600px;
            height: 600px;
            bottom: -200px;
            left: -100px;
            background: linear-gradient(135deg, #ff3366, #ff6b9b);
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
            background-image: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .geometric-shape {
            position: absolute;
            border: 1px solid rgba(255, 255, 255, 0.1);
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
            background: #4A148C;
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            width: 400px;
            overflow: hidden;
            position: relative;
            z-index: 10;
        }

        .login-card-header {
            background: #5E35B1;
            text-align: center;
            padding: 30px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-icon {
            width: 80px;
            height: 80px;
            background: #FF4081;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .user-icon i {
            font-size: 40px;
            color: white;
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
            background: #FF4081;
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
            background: rgba(255, 64, 129, 0.2);
            color: white;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 14px;
            text-align: center;
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
    <!-- Éléments de fond géométriques -->
    <div class="bg-shapes">
        <div class="dots"></div>
        <div class="bg-shape shape1"></div>
        <div class="bg-shape shape2"></div>
        <div class="bg-shape shape3"></div>
        <div class="geometric-shape geo-1"></div>
        <div class="geometric-shape geo-2"></div>
    </div>

    <!-- Card de login -->
    <div class="login-container">
        <div class="login-card-header">
            <div class="user-icon">
                <i class="fas fa-user"></i>
            </div>
            <h2 class="login-title">Login Now</h2>
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
                    <span class="btn-text">LOGIN</span>
                    <div class="loading"></div>
                </button>
                
                <div id="errorMessage" class="error-message" style="display: none;"></div>
            </form>
        </div>

        <div class="login-footer">
            <p class="account-text">Don't have an account?</p>
            <a href="#" class="forgot-link" id="forgotPassword">Forgot password?</a>
            <div>
                <a href="index.php" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Retour à l'accueil
                </a>
            </div>
        </div>
    </div>

    <div class="attribution">
        Image from Freepik
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
