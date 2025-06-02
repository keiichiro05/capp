<?php
session_start();

// Header keamanan tambahan
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Log in</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="U-PSN Login Portal">
    
    <!-- Favicon -->
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/login.css" rel="stylesheet">
</head>
<body>
    <div class="split-screen">
        <!-- Left Section with Background Image -->
        <div class="left-section">
            <div class="text-white position-relative text-center p-4">
                <h2 class="display-5 fw-bold mb-4">Welcome to U-PSN</h2>
                <p class="lead">Your trusted pharmaceutical network</p>
            </div>
        </div>
        
        <!-- Right Section with Login Form -->
        <div class="right-section">
            <div class="login-container">
                <img src="img/logo.png" alt="E-pharm Logo" class="login-logo">
                <h1 class="login-header">Sign In</h1>
                
                <form id="loginForm" action="proses_login.php" method="post" novalidate>
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" id="userid" name="userid" class="form-control" 
                                   placeholder="UserName" required aria-label="UserName">
                        </div>
                    </div>
                    
                    <div class="form-group password-toggle">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" id="password" name="password" class="form-control" 
                                   placeholder="Password" required aria-label="Password">
                            <i class="password-toggle-icon fas fa-eye" id="togglePassword"></i>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-login">
                            <span class="button-text">Sign In</span>
                        </button>
                    </div>
                    
                    <div class="login-footer text-center">
                        <a href="#" class="d-block mb-2">Forgot your password?</a>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="#" aria-label="Terms of Service">Terms</a>
                            <span class="text-muted">•</span>
                            <a href="#" aria-label="Privacy Policy">Privacy</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/login.js"></script>
</body>
</html>