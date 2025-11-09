<?php
session_start();

$err = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    
    // Hardcoded credentials for testing
    if($user === 'sahat' && $pass === '123456789'){
        $_SESSION['admin_id'] = 1; // Set a dummy admin ID
        header('Location: dashboard.php'); 
        exit;
    }
    
    // Original database check (commented out for now)
    /*
    $stmt = $mysqli->prepare("SELECT admin_id, password_hash FROM admins WHERE username = ?");
    $stmt->bind_param('s', $user); $stmt->execute(); $res = $stmt->get_result();
    if($res->num_rows){
        $a = $res->fetch_assoc();
        if(password_verify($pass, $a['password_hash'])){
            $_SESSION['admin_id'] = $a['admin_id'];
            header('Location: dashboard.php'); exit;
        }
    }
    */
    
    $err = "Invalid admin credentials";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Control Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --success: #10b981;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
            --border: #e2e8f0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 1.5rem;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 2.5rem 2rem;
            text-align: center;
            color: white;
        }

        .login-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .login-brand i {
            font-size: 2rem;
        }

        .login-brand h1 {
            font-size: 1.75rem;
            font-weight: 700;
        }

        .login-subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
            font-weight: 500;
        }

        .login-body {
            padding: 2.5rem 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group i {
            position: absolute;
            left: 1rem;
            color: var(--secondary);
            font-size: 1.125rem;
            z-index: 2;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 2px solid var(--border);
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            font-weight: 500;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            transform: translateY(-1px);
        }

        .btn {
            width: 100%;
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.5);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Error Message */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            border-left: 4px solid;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-left-color: var(--danger);
        }

        .alert-danger i {
            color: var(--danger);
        }

        /* Demo Credentials */
        .demo-credentials {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 0.75rem;
            padding: 1.25rem;
            margin-top: 2rem;
        }

        .demo-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: #0369a1;
        }

        .demo-header i {
            font-size: 1.125rem;
        }

        .credential-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e0f2fe;
        }

        .credential-item:last-child {
            border-bottom: none;
        }

        .credential-label {
            font-weight: 500;
            color: #475569;
        }

        .credential-value {
            font-weight: 600;
            color: var(--primary);
            font-family: 'Monaco', 'Consolas', monospace;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
            color: var(--secondary);
            font-size: 0.875rem;
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            animation: fadeIn 0.6s ease-out;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-header {
                padding: 2rem 1.5rem;
            }

            .login-body {
                padding: 2rem 1.5rem;
            }

            .login-brand h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-brand">
                    <i class="fas fa-shield-alt"></i>
                    <h1>AdminPanel</h1>
                </div>
                <p class="login-subtitle">Secure Admin Access</p>
            </div>

            <div class="login-body">
                <?php if(isset($err) && $err): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($err) ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" class="form-control" 
                                   placeholder="Enter your username" value="sahat" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" class="form-control" 
                                   placeholder="Enter your password" value="123456789" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In to Dashboard
                    </button>
                </form>

                <div class="demo-credentials">
                    <div class="demo-header">
                        <i class="fas fa-info-circle"></i>
                        <span>Test Credentials</span>
                    </div>
                    <div class="credential-item">
                        <span class="credential-label">Username:</span>
                        <span class="credential-value">sahat</span>
                    </div>
                    <div class="credential-item">
                        <span class="credential-label">Password:</span>
                        <span class="credential-value">123456789</span>
                    </div>
                </div>

                <div class="login-footer">
                    <p>&copy; 2024 AdminPanel. Secure access required.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add focus effects
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.querySelector('i').style.color = 'var(--primary)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.querySelector('i').style.color = 'var(--secondary)';
                });
            });

            // Add loading state to button
            const form = document.querySelector('form');
            form.addEventListener('submit', function() {
                const button = this.querySelector('button[type="submit"]');
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Authenticating...';
                button.disabled = true;
            });
        });
    </script>
</body>
</html>