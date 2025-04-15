<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    // Modified query to check only email first
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: index2.html');
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Email not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JainZ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #FF4D8D;
            --secondary: #FF85A9;
            --accent: #FFB6C1;
            --bg-light: #FFF5F7;
            --text-dark: #2C1810;
            --text-light: #FFF;
            --gradient-main: linear-gradient(45deg, #FF4D8D, #FF85A9, #FFB6C1);
            --gradient-hover: linear-gradient(45deg, #FFB6C1, #FF85A9, #FF4D8D);
            --shadow-soft: 0 10px 30px rgba(255, 77, 141, 0.15);
            --shadow-strong: 0 15px 40px rgba(255, 77, 141, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--bg-light);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: var(--text-dark);
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            max-width: 500px;
            width: 100%;
            position: relative;
            overflow: hidden;
            border: 2px solid rgba(255, 77, 141, 0.1);
            animation: slideIn 0.6s ease-out;
            margin: 0 auto;
        }

        .form-title {
            color: var(--primary);
            font-size: 2.2rem;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 2px solid rgba(255, 77, 141, 0.2);
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.9);
            color: var(--text-dark);
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(255, 77, 141, 0.1);
            outline: none;
        }

        .input-icon {
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--gradient-main);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            background: var(--gradient-hover);
            transform: translateY(-3px);
            box-shadow: var(--shadow-strong);
        }

        .footer {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-dark);
        }

        .footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .footer a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        .error-message {
            background: rgba(255, 101, 132, 0.1);
            color: #FF6584;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            text-align: center;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        @media (max-width: 768px) {
            .form-container { padding: 1.5rem; }
            .form-title { font-size: 1.8rem; }
            .btn-primary { padding: 0.8rem 1.5rem; font-size: 1rem; }
        }

        @media (max-width: 480px) {
            .form-container { padding: 1rem; }
            .form-title { font-size: 1.5rem; }
            .btn-primary { padding: 0.6rem 1.2rem; font-size: 0.9rem; }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2 class="form-title">Welcome Back</h2>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email Address" required>
                <i class="input-icon fas fa-envelope"></i>
            </div>
            
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
                <i class="input-icon fas fa-lock"></i>
            </div>
            
            <button type="submit" class="btn-primary">
                Login <i class="fas fa-sign-in-alt"></i>
            </button>
        </form>
        
        <div class="footer">
            <p>Don't have an account? <a href="register.php">Register <i class="fas fa-arrow-right"></i></a></p>
            <p><a href="forgot-password.php">Forgot Password? <i class="fas fa-key"></i></a></p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = this.querySelector('input[name="email"]').value;
            const password = this.querySelector('input[name="password"]').value;

            if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                e.preventDefault();
                alert('Please enter a valid email address');
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
            }
        });
    </script>
</body>
</html>