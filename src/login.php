<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Document Archive</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233b82f6'><path d='M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z' /></svg>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background elements */
        .background-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .floating-shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .floating-shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .floating-shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        .floating-shape:nth-child(4) {
            width: 100px;
            height: 100px;
            top: 10%;
            right: 30%;
            animation-delay: 1s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 1;
            animation: slideInUp 0.8s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .login-header p {
            color: #6b7280;
            font-size: 1.1rem;
            font-weight: 400;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-input {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            outline: none;
        }

        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            transform: translateY(-1px);
        }

        .form-input:hover {
            border-color: #d1d5db;
        }

        .password-input-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #3b82f6;
        }

        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #3b82f6;
        }

        .forgot-password {
            color: #3b82f6;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #1d4ed8;
        }

        .login-button {
            width: 100%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            padding: 1.25rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
            margin-bottom: 1.5rem;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.5);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .login-button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e5e7eb;
            z-index: 0;
        }

        .divider span {
            background: rgba(255, 255, 255, 0.95);
            padding: 0 1rem;
            position: relative;
            z-index: 1;
        }

        .signup-link {
            text-align: center;
            color: #6b7280;
            font-size: 0.95rem;
        }

        .signup-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .signup-link a:hover {
            color: #1d4ed8;
        }

        .message {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-align: center;
            animation: slideInDown 0.5s ease-out;
        }

        .message.success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #d1fae5;
        }

        .message.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Loading spinner */
        .spinner {
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive design */
        @media (max-width: 480px) {
            .login-container {
                margin: 1rem;
                padding: 2rem 1.5rem;
            }
            
            .login-header h1 {
                font-size: 2rem;
            }
            
            .logo-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }
        }

        /* Demo credentials helper */
        .demo-credentials {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            color: #64748b;
        }

        .demo-credentials strong {
            color: #334155;
        }
    </style>
</head>
<body>
    <div class="background-animation">
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
    </div>

    <div class="login-container">
        <div class="login-header">
            <div class="logo-icon">
                <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                </svg>
            </div>
            <h1>Welcome Back</h1>
            <p>Sign in to your document archive</p>
        </div>

        <!-- Demo credentials info -->
        <div class="demo-credentials">
            <strong>Demo Credentials:</strong><br>
            Username: <strong>admin</strong><br>
            Password: <strong>admin123</strong>
        </div>

        <!-- Message display area -->
        <div id="messageArea"></div>

        <form id="loginForm" action="auth.php" method="POST">
            <div class="form-group">
                <label for="username" class="form-label">Username or Email</label>
                <input type="text" id="username" name="username" class="form-input" required 
                       placeholder="Enter your username or email" autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="password-input-wrapper">
                    <input type="password" id="password" name="password" class="form-input" required 
                           placeholder="Enter your password" autocomplete="current-password">
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <svg id="eyeIcon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="login-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember_me" value="1">
                    Remember me
                </label>
                <a href="#" class="forgot-password">Forgot password?</a>
            </div>

            <button type="submit" class="login-button" id="loginBtn">
                Sign In
            </button>
        </form>

        <div class="divider">
            <span>or</span>
        </div>

        <div class="signup-link">
            Don't have an account? <a href="register.php">Create one</a>
        </div>
    </div>

    <script>
        // Password visibility toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                `;
            }
        }

        // Form submission handling
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const loginBtn = document.getElementById('loginBtn');
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            // Basic validation
            if (!username || !password) {
                showMessage('Please fill in all fields', 'error');
                return;
            }
            
            // Show loading state
            loginBtn.innerHTML = `
                <span class="spinner"></span>
                Signing in...
            `;
            loginBtn.disabled = true;
            
            // Simulate login process (replace with actual authentication)
            setTimeout(() => {
                // For demo purposes - in real app, this would be handled by the server
                if (username === 'admin' && password === 'admin123') {
                    showMessage('Login successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    showMessage('Invalid username or password', 'error');
                    resetLoginButton();
                }
            }, 1500);
        });
        
        function resetLoginButton() {
            const loginBtn = document.getElementById('loginBtn');
            loginBtn.innerHTML = 'Sign In';
            loginBtn.disabled = false;
        }
        
        function showMessage(text, type) {
            const messageArea = document.getElementById('messageArea');
            messageArea.innerHTML = `<div class="message ${type}">${text}</div>`;
            
            // Auto-hide message after 5 seconds
            setTimeout(() => {
                messageArea.innerHTML = '';
            }, 5000);
        }
        
        // Auto-focus username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
        
        // Handle enter key in form fields
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && (e.target.id === 'username' || e.target.id === 'password')) {
                document.getElementById('loginForm').dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>
</html>