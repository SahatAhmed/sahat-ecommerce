<?php
require_once __DIR__ . '/includes/db.php'; // this loads $mysqli

$success = $error = "";

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate a simple CAPTCHA code if not exists
if (empty($_SESSION['captcha_code'])) {
    $_SESSION['captcha_code'] = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $captcha = trim($_POST['captcha'] ?? '');

    // Validate CAPTCHA
    if (empty($captcha) || strtoupper($captcha) !== $_SESSION['captcha_code']) {
        $error = "Invalid CAPTCHA code. Please try again.";
        // Generate new CAPTCHA after failed attempt
        $_SESSION['captcha_code'] = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
    } elseif ($name == "" || $email == "" || $message == "") {
        $error = "Please fill all required fields!";
        // Generate new CAPTCHA
        $_SESSION['captcha_code'] = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
    } else {
        // Use mysqli prepared statement
        $stmt = $mysqli->prepare("
            INSERT INTO contact_messages (name, email, phone, message) 
            VALUES (?, ?, ?, ?)
        ");

        if ($stmt) {
            $stmt->bind_param("ssss", $name, $email, $phone, $message);

            if ($stmt->execute()) {
                $success = "Your message has been sent successfully!";
                // Clear form values on success
                $_POST = array();
                // Generate new CAPTCHA after successful submission
                $_SESSION['captcha_code'] = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
            } else {
                $error = "Something went wrong. Please try again.";
                // Generate new CAPTCHA
                $_SESSION['captcha_code'] = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
            }
            $stmt->close();
        } else {
            $error = "Database error. Please try again.";
            $_SESSION['captcha_code'] = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Elite Solutions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #dbeafe;
            --secondary-color: #7c3aed;
            --accent-color: #f59e0b;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --light-bg: #f8fafc;
            --white: #ffffff;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --error-color: #ef4444;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Hero Section */
        .contact-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: var(--white);
            padding: 120px 0 100px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .contact-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="%23ffffff" opacity="0.1"><polygon points="1000,100 1000,0 0,100"/></svg>');
            background-size: cover;
        }
        
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }
        
        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }
        
        .hero-content p {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 30px;
            line-height: 1.7;
        }
        
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 50px;
            margin-top: 50px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            text-align: center;
            position: relative;
            padding: 0 20px;
        }
        
        .stat-item:not(:last-child)::after {
            content: '';
            position: absolute;
            right: -25px;
            top: 50%;
            transform: translateY(-50%);
            width: 1px;
            height: 40px;
            background: rgba(255, 255, 255, 0.3);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
            background: linear-gradient(to right, #ffffff, #e0f2fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        /* Contact Section */
        .contact-section {
            padding: 100px 0;
            position: relative;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 80px;
        }
        
        .section-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
        }
        
        .section-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border-radius: 2px;
        }
        
        .section-header p {
            color: var(--text-light);
            max-width: 600px;
            margin: 30px auto 0;
            font-size: 1.125rem;
            line-height: 1.7;
        }
        
        .contact-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            margin-bottom: 80px;
        }
        
        /* Map Section */
        .map-section {
            margin-bottom: 80px;
        }
        
        .map-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .map-header h3 {
            font-size: 2rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 15px;
        }
        
        .map-header p {
            color: var(--text-light);
            font-size: 1.1rem;
        }
        
        .map-container {
            height: 450px;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            position: relative;
        }
        
        #contact-map {
            height: 100%;
            width: 100%;
        }
        
        .map-overlay {
            position: absolute;
            top: 20px;
            left: 20px;
            background: var(--white);
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            max-width: 300px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .map-overlay h4 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .map-overlay p {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 5px;
        }
        
        /* Contact Cards */
        .contact-info {
            background: var(--white);
            padding: 50px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            height: fit-content;
            position: relative;
            overflow: hidden;
        }
        
        .contact-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
        }
        
        .contact-info h3 {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .contact-info h3 i {
            color: var(--primary-color);
        }
        
        .contact-info > p {
            color: var(--text-light);
            margin-bottom: 30px;
            font-size: 1.05rem;
            line-height: 1.7;
        }
        
        .contact-details {
            margin-top: 40px;
        }
        
        .contact-detail {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
            padding: 25px;
            border-radius: var(--radius);
            transition: var(--transition);
            background: var(--light-bg);
            border-left: 4px solid var(--primary-color);
            position: relative;
            overflow: hidden;
        }
        
        .contact-detail::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: var(--transition);
        }
        
        .contact-detail:hover::before {
            left: 100%;
        }
        
        .contact-detail:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        
        .contact-detail i {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            flex-shrink: 0;
            font-size: 1.25rem;
            box-shadow: var(--shadow-sm);
        }
        
        .contact-detail-content h4 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-dark);
        }
        
        .contact-detail-content p {
            color: var(--text-light);
            margin: 0;
            line-height: 1.6;
        }
        
        /* Form Styles */
        .contact-form {
            background: var(--white);
            padding: 50px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            position: relative;
        }
        
        .contact-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: var(--text-dark);
            font-size: 0.95rem;
            transition: var(--transition);
        }
        
        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 1rem;
            transition: var(--transition);
            background: var(--light-bg);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
            background: var(--white);
            transform: translateY(-2px);
        }
        
        textarea.form-control {
            min-height: 160px;
            resize: vertical;
            line-height: 1.6;
        }
        
        /* CAPTCHA Styles */
        .captcha-container {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            background: var(--light-bg);
            padding: 15px;
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
        }
        
        .captcha-code {
            font-family: 'Courier New', monospace;
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 5px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px dashed var(--primary-color);
            flex: 1;
            text-align: center;
            user-select: none;
            background-color: var(--light-bg);
        }
        
        .captcha-refresh {
            background: var(--primary-color);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .captcha-refresh:hover {
            background: var(--primary-dark);
            transform: rotate(90deg);
        }
        
        .captcha-input {
            flex: 1;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 16px 32px;
            border: none;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition);
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }
        
        .btn.loading i {
            animation: spin 1s linear infinite;
        }
        
        .btn i {
            margin-right: 10px;
            transition: var(--transition);
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius);
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            animation: slideIn 0.5s ease-out;
            position: relative;
            overflow: hidden;
        }
        
        .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }
        
        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        
        .alert-success::before {
            background-color: var(--success-color);
        }
        
        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .alert-error::before {
            background-color: var(--error-color);
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .required::after {
            content: " *";
            color: var(--error-color);
        }
        
        .form-note {
            text-align: center;
            margin-top: 20px;
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
            color: var(--text-light);
            font-size: 0.85rem;
        }
        
        .security-badge i {
            color: var(--success-color);
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .contact-wrapper {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .hero-content h1 {
                font-size: 3rem;
            }
        }
        
        @media (max-width: 768px) {
            .contact-hero {
                padding: 80px 0 60px;
            }
            
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .hero-content p {
                font-size: 1.1rem;
            }
            
            .hero-stats {
                gap: 30px;
            }
            
            .stat-item:not(:last-child)::after {
                display: none;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .contact-info, .contact-form {
                padding: 30px;
            }
            
            .map-overlay {
                position: relative;
                top: 0;
                left: 0;
                max-width: none;
                margin-bottom: 20px;
            }
            
            .captcha-container {
                flex-direction: column;
                text-align: center;
            }
            
            .captcha-refresh {
                align-self: center;
            }
        }
        
        @media (max-width: 480px) {
            .contact-hero {
                padding: 60px 0 40px;
            }
            
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .hero-stats {
                flex-direction: column;
                gap: 20px;
            }
            
            .contact-detail {
                flex-direction: column;
                text-align: center;
            }
            
            .contact-detail i {
                margin-right: 0;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <?php require 'includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Let's Start a Conversation</h1>
                <p>We're here to help you achieve your goals. Whether you have questions about our services, need technical support, or want to explore partnership opportunities, our team is ready to assist you every step of the way.</p>
                
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Support Available</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24h</div>
                        <div class="stat-label">Avg Response Time</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Client Satisfaction</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="section-header">
                <h2>Get In Touch With Us</h2>
                <p>Choose your preferred method of communication. We're always happy to hear from you and help with any inquiries.</p>
            </div>
            
            <div class="contact-wrapper">
                <div class="contact-info">
                    <h3><i class="fas fa-comment-dots"></i> Contact Information</h3>
                    <p>Fill out the form or reach out to us through any of the channels below. Our dedicated team typically responds within 2 hours during business days.</p>
                    
                    <div class="contact-details">
                        <div class="contact-detail">
                            <i class="fas fa-map-marker-alt"></i>
                            <div class="contact-detail-content">
                                <h4>Our Office</h4>
                                <p>Bakrahat Road<br>ThakurPukur Bazar<br>Kolkata</p>
                            </div>
                        </div>
                        
                        <div class="contact-detail">
                            <i class="fas fa-phone-alt"></i>
                            <div class="contact-detail-content">
                                <h4>Phone </h4>
                                <p>+91 91233 00890<br>Mon-Fri 10am-6pm</p>
                            </div>
                        </div>
                        
                        <div class="contact-detail">
                            <i class="fas fa-envelope"></i>
                            <div class="contact-detail-content">
                                <h4>Email Addresses</h4>
                                <p>Cooming Soon</p>
                            </div>
                        </div>
                        
                        <div class="contact-detail">
                            <i class="fas fa-clock"></i>
                            <div class="contact-detail-content">
                                <h4>Business Hours</h4>
                                <p>Monday - Friday: 10:00 AM - 6:00 PM<br>Saturday: 11:00 AM - 1:00 PM<br>Sunday: Closed</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form">
                    <?php if($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <?php if($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="contactForm">
                        <div class="form-group">
                            <label for="name" class="required">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" required value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="required">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email address" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" id="phone" name="phone" class="form-control" placeholder="Enter your phone number (optional)" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="message" class="required">Your Message</label>
                            <textarea id="message" name="message" class="form-control" placeholder="Please describe your inquiry in detail. The more information you provide, the better we can assist you." required><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="captcha" class="required">Security Verification</label>
                            <div class="captcha-container">
                                <div class="captcha-code" id="captchaCode">
                                    <?= htmlspecialchars($_SESSION['captcha_code']) ?>
                                </div>
                                <button type="button" class="captcha-refresh" id="refreshCaptcha">
                                    <i class="fas fa-redo"></i>
                                </button>
                                <input type="text" id="captcha" name="captcha" class="form-control captcha-input" placeholder="Enter the code above" required maxlength="6" value="<?= isset($_POST['captcha']) ? htmlspecialchars($_POST['captcha']) : '' ?>">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn" id="submitBtn">
                            <i class="fas fa-paper-plane"></i>
                            Send Message
                        </button>
                        
                        <div class="security-badge">
                            <i class="fas fa-shield-alt"></i>
                            <span>Protected by CAPTCHA. Your privacy is important to us.</span>
                        </div>
                        
                        <p class="form-note">We respect your privacy and will never share your information with third parties. All data is encrypted and securely stored.</p>
                    </form>
                </div>
            </div>
            
            <!-- Map Section -->
            <div class="map-section">
                <div class="map-header">
                    <h3>Find Us Here</h3>
                    <p>Visit our office or explore our office</p>
                </div>
                
                <div class="map-container">
    <!-- ✅ Google Maps Embed Only -->
    <iframe 
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3672.8538733880794!2d88.3042026!3d22.4634332!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a027a8b4825309b%3A0x98ad7f881b38a68d!2s9%2C%20Thakurpukur%20-%20Bibirhat%20-%20Bakhrahat%20-%20Raipur%20Rd%2C%20Thakurpukur%20Bazar%2C%20Thakurpukur%2C%20Kolkata%2C%20West%20Bengal%20700063!5e0!3m2!1sen!2sin!4v1700000000000"
        width="100%"
        height="450"
        style="border:0;"
        allowfullscreen=""
        loading="lazy">
    </iframe>
</div>

            </div>
        </div>
    </section>
    
    <?php require 'includes/footer.php'; ?>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize the map
        function initMap() {
            // Coordinates for New York (example location)
            const nyCoordinates = [40.7128, -74.0060];
            
            // Create map
            const map = L.map('contact-map').setView(nyCoordinates, 15);
            
            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            // Add custom marker
            const customIcon = L.divIcon({
                html: '<div style="background: linear-gradient(135deg, #2563eb, #7c3aed); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"><i class="fas fa-building"></i></div>',
                iconSize: [40, 40],
                iconAnchor: [20, 40],
                className: 'custom-marker'
            });
            
            // Add marker to map
            L.marker(nyCoordinates, {icon: customIcon}).addTo(map)
                .bindPopup('<b>Elite Solutions Headquarters</b><br>123 Business District, Suite 450<br>New York, NY 10001')
                .openPopup();
            
            // Add circle around location
            L.circle(nyCoordinates, {
                color: '#2563eb',
                fillColor: '#3b82f6',
                fillOpacity: 0.1,
                radius: 200
            }).addTo(map);
        }
        
        // Refresh CAPTCHA via AJAX
        function refreshCaptcha() {
            const refreshBtn = document.getElementById('refreshCaptcha');
            const captchaCode = document.getElementById('captchaCode');
            const captchaInput = document.getElementById('captcha');
            
            // Add loading state to refresh button
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            refreshBtn.disabled = true;
            
            // Make AJAX request to refresh CAPTCHA
            fetch('refresh_captcha.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        captchaCode.textContent = data.captcha;
                        captchaInput.value = '';
                    }
                })
                .catch(error => {
                    console.error('Error refreshing CAPTCHA:', error);
                    // Fallback: generate client-side CAPTCHA
                    const chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                    let newCaptcha = "";
                    for (let i = 0; i < 6; i++) {
                        newCaptcha += chars.charAt(Math.floor(Math.random() * chars.length));
                    }
                    captchaCode.textContent = newCaptcha;
                    captchaInput.value = '';
                })
                .finally(() => {
                    // Reset refresh button
                    refreshBtn.innerHTML = '<i class="fas fa-redo"></i>';
                    refreshBtn.disabled = false;
                });
        }
        
        // Form submission handling
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            
            const contactForm = document.getElementById('contactForm');
            const submitBtn = document.getElementById('submitBtn');
            const refreshBtn = document.getElementById('refreshCaptcha');
            
            // Add CAPTCHA refresh functionality
            if (refreshBtn) {
                refreshBtn.addEventListener('click', refreshCaptcha);
            }
            
            if (contactForm) {
                contactForm.addEventListener('submit', function() {
                    // Add loading state to button
                    submitBtn.classList.add('loading');
                    submitBtn.innerHTML = '<i class="fas fa-spinner"></i> Sending...';
                });
            }
        });
    </script>
</body>
</html>