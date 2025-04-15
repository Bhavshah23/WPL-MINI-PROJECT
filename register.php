<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $birth_date = sanitizeInput($_POST['birth_date']);
    $gender = sanitizeInput($_POST['gender']);
    
    // Validate age (must be 18+)
    $age = date_diff(date_create($birth_date), date_create('today'))->y;
    if ($age < 18) {
        $error = "You must be at least 18 years old to register";
    } else {
        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));
        
        $sql = "INSERT INTO users (name, email, password, birth_date, gender, verification_token) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $name, $email, $password, $birth_date, $gender, $verification_token);
        
        if ($stmt->execute()) {
            // Send verification email
            $verify_link = "http://" . $_SERVER['HTTP_HOST'] . "/verify.php?token=" . $verification_token;
            $message = "Welcome to JainZ! Please verify your email by clicking this link: " . $verify_link;
            
            if (sendEmail($email, "Verify Your Email", $message)) {
                $success = "Registration successful! Please check your email to verify your account.";
            }
        } else {
            $error = "Registration failed. Email might already be registered.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - JainZ</title>
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

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 2px solid rgba(255, 77, 141, 0.2);
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.9);
            color: var(--text-dark);
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-group input:focus,
        .form-group select:focus {
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

        .success-message {
            background: rgba(46, 213, 115, 0.1);
            color: #2ed573;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            text-align: center;
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
        <h2 class="form-title">Join JainZ Community</h2>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" id="registerForm">
            <div class="form-group">
                <input type="text" name="name" placeholder="Full Name" required>
                <i class="input-icon fas fa-user"></i>
            </div>
            
            <div class="form-group">
                <input type="email" name="email" placeholder="Email Address" required>
                <i class="input-icon fas fa-envelope"></i>
            </div>
            
            <div class="form-group">
                <input type="password" name="password" placeholder="Create Password" required minlength="8">
                <i class="input-icon fas fa-lock"></i>
            </div>
            
            <div class="form-group">
                <input type="date" name="birth_date" required max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>">
                <i class="input-icon fas fa-calendar"></i>
            </div>
            
            <div class="form-group">
                <select name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
                <i class="input-icon fas fa-venus-mars"></i>
            </div>
            
            <button type="submit" class="btn-primary">
                Create Account <i class="fas fa-user-plus"></i>
            </button>
        </form>
        
        <div class="footer">
            <p>Already have an account? <a href="login.php">Login <i class="fas fa-arrow-right"></i></a></p>
        </div>
    </div>
    
    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = this.querySelector('input[name="password"]').value;
            const name = this.querySelector('input[name="name"]').value;
            const email = this.querySelector('input[name="email"]').value;

            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
            }
            
            if (name.length < 2) {
                e.preventDefault();
                alert('Please enter a valid name');
            }

            if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                e.preventDefault();
                alert('Please enter a valid email address');
            }
        });
    </script>
</body>
</html>