<?php
require 'includes/db.php';
// About Us Page for E-commerce Website
$pageTitle = "About Us";
$companyName = "Sahat E-Commerce";
$currentYear = date("Y");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . " | " . $companyName; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary: #1a365d;
            --secondary: #2d74da;
            --accent: #e53e3e;
            --light: #f7fafc;
            --dark: #2d3748;
            --text: #2d3748;
            --text-light: #718096;
            --gradient: linear-gradient(135deg, #1a365d 0%, #2d74da 100%);
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        body {
            background-color: var(--light);
            color: var(--text);
            line-height: 1.7;
            overflow-x: hidden;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .top-bar {
            background: var(--gradient);
            color: white;
            padding: 10px 0;
            font-size: 0.85rem;
        }

        .top-bar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: translateY(-2px);
        }

        .logo i {
            color: var(--secondary);
            margin-right: 12px;
            font-size: 2rem;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 30px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            padding: 8px 0;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--secondary);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--secondary);
        }

        .nav-icons {
            display: flex;
            gap: 20px;
        }

        .icon {
            position: relative;
            cursor: pointer;
            color: var(--dark);
            font-size: 1.3rem;
            transition: all 0.3s ease;
            padding: 8px;
            border-radius: 50%;
        }

        .icon:hover {
            background: rgba(45, 116, 218, 0.1);
            color: var(--secondary);
            transform: translateY(-2px);
        }

        .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--accent);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Hero Section */
        .about-hero {
            background: linear-gradient(135deg, rgba(13, 40, 78, 0.9) 0%, rgba(131, 183, 255, 0.9) 100%), 
                        url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 140px 0 100px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .about-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="%23ffffff" opacity="0.1"><polygon points="1000,100 1000,0 0,100"></polygon></svg>');
            background-size: cover;
        }

        .about-hero h1 {
            font-size: 3.5rem;
            margin-bottom: 25px;
            font-weight: 700;
            position: relative;
            animation: fadeInUp 1s ease;
        }

        .about-hero p {
            font-size: 1.3rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
            font-weight: 300;
            animation: fadeInUp 1s ease 0.2s both;
        }

        /* Content Sections */
        .section {
            padding: 100px 0;
            position: relative;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
            font-weight: 700;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--secondary);
            border-radius: 2px;
        }

        .section-title p {
            color: var(--text-light);
            max-width: 700px;
            margin: 0 auto;
            font-size: 1.1rem;
            line-height: 1.8;
        }

        /* Our Story Section */
        .our-story {
            background-color: white;
        }

        .story-content {
            display: flex;
            align-items: center;
            gap: 60px;
        }

        .story-text {
            flex: 1;
        }

        .story-text h3 {
            font-size: 2rem;
            margin-bottom: 25px;
            color: var(--primary);
            font-weight: 600;
        }

        .story-text p {
            margin-bottom: 25px;
            color: var(--text-light);
            font-size: 1.05rem;
        }

        .story-image {
            flex: 1;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
        }

        .story-image:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-5px);
        }

        .story-image img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.5s ease;
        }

        .story-image:hover img {
            transform: scale(1.05);
        }

        /* Values Section */
        .values {
            background: linear-gradient(135deg, var(--light) 0%, #edf2f7 100%);
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }

        .value-card {
            background-color: white;
            padding: 40px 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .value-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient);
        }

        .value-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .value-icon {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            color: white;
            font-size: 2.2rem;
            transition: all 0.3s ease;
        }

        .value-card:hover .value-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .value-card h3 {
            font-size: 1.6rem;
            margin-bottom: 20px;
            color: var(--primary);
            font-weight: 600;
        }

        .value-card p {
            color: var(--text-light);
            line-height: 1.8;
        }

        /* Team Section */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 40px;
        }

        .team-member {
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
        }

        .team-member:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .member-image {
            height: 300px;
            overflow: hidden;
            position: relative;
        }

        .member-image::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(transparent, rgba(26, 54, 93, 0.7));
        }

        .member-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .team-member:hover .member-image img {
            transform: scale(1.1);
        }

        .member-info {
            padding: 25px;
            text-align: center;
            position: relative;
        }

        .member-info h3 {
            font-size: 1.4rem;
            margin-bottom: 8px;
            color: var(--primary);
            font-weight: 600;
        }

        .member-info p {
            color: var(--secondary);
            margin-bottom: 20px;
            font-weight: 500;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 12px;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .social-links a:hover {
            background: var(--gradient);
            color: white;
            transform: translateY(-3px);
        }

        /* Stats Section */
        .stats {
            background: var(--gradient);
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .stats::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="%23ffffff" opacity="0.05"><polygon points="0,0 1000,100 1000,0"></polygon></svg>');
            background-size: cover;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            position: relative;
        }

        .stat-item {
            padding: 30px 20px;
        }

        .stat-item h3 {
            font-size: 3.5rem;
            margin-bottom: 15px;
            color: white;
            font-weight: 700;
        }

        .stat-item p {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 300;
        }

        /* CTA Section */
        .cta {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            text-align: center;
            padding: 100px 0;
            position: relative;
        }

        .cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="%231a365d" opacity="0.02"><circle cx="200" cy="50" r="30"/><circle cx="600" cy="30" r="20"/><circle cx="800" cy="70" r="25"/></svg>');
            background-size: cover;
        }

        .cta h2 {
            font-size: 2.8rem;
            margin-bottom: 25px;
            color: var(--primary);
            font-weight: 700;
            position: relative;
        }

        .cta p {
            max-width: 700px;
            margin: 0 auto 40px;
            color: var(--text-light);
            font-size: 1.2rem;
            line-height: 1.8;
            position: relative;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 40px;
            background: var(--gradient);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
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
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(45, 116, 218, 0.3);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeInUp 0.8s ease both;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .story-content {
                flex-direction: column;
                text-align: center;
            }
            
            .story-image {
                order: -1;
                max-width: 500px;
                margin: 0 auto;
            }

            .about-hero h1 {
                font-size: 2.8rem;
            }

            .section-title h2 {
                font-size: 2.2rem;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .about-hero {
                padding: 120px 0 80px;
            }

            .about-hero h1 {
                font-size: 2.2rem;
            }

            .about-hero p {
                font-size: 1.1rem;
            }

            .section {
                padding: 80px 0;
            }

            .cta h2 {
                font-size: 2.2rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 30px;
            }

            .values-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .stat-item h3 {
                font-size: 2.8rem;
            }

            .team-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header with Navbar -->
    <?php require 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <h1>Our Story</h1>
            <p><?php echo $companyName; ?> was founded with a simple mission: to make online shopping effortless, enjoyable, and accessible to everyone.</p>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="section our-story">
        <div class="container">
            <div class="story-content">
                <div class="story-text fade-in">
                    <h3>From Humble Beginnings to E-commerce Excellence</h3>
                    <p>Founded in 2025, <?php echo $companyName; ?> started as a small family business with a passion for connecting customers with quality products. What began as a modest online store has grown into a trusted e-commerce platform serving millions of customers worldwide.</p>
                    <p>Our journey has been guided by a commitment to innovation, customer satisfaction, and ethical business practices. We believe that shopping online should be simple, secure, and enjoyable.</p>
                    <p>Today, we partner with over 500 brands and deliver to more than 30 countries, but our core values remain the same: quality, integrity, and exceptional service.</p>
                </div>
                <div class="story-image fade-in">
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80" alt="Our team working together">
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="section values">
        <div class="container">
            <div class="section-title">
                <h2>Our Values</h2>
                <p>These principles guide everything we do at <?php echo $companyName; ?></p>
            </div>
            <div class="values-grid">
                <div class="value-card fade-in">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Customer First</h3>
                    <p>Our customers are at the heart of every decision we make. We listen, adapt, and strive to exceed expectations with every interaction.</p>
                </div>
                <div class="value-card fade-in">
                    <div class="value-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3>Quality Assurance</h3>
                    <p>We carefully curate our product selection and maintain rigorous quality standards to ensure you receive only the best.</p>
                </div>
                <div class="value-card fade-in">
                    <div class="value-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Innovation</h3>
                    <p>We continuously evolve our platform and services to provide a cutting-edge shopping experience that's intuitive and efficient.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="section">
        <div class="container">
            <div class="section-title">
                <h2>Meet Our Team</h2>
                <p>The passionate people behind <?php echo $companyName; ?>'s success</p>
            </div>
            <div class="team-grid">
                <?php
                $teamMembers = [
                    [
                        'name' => 'Sahat Ahmed',
                        'position' => 'CEO & Founder',
                        'image' => 'assets/images/sahat.jpg',
                        'social' => ['linkedin', 'twitter', 'instagram']
                    ],
                ];

                foreach ($teamMembers as $index => $member) {
                    echo '<div class="team-member fade-in" style="animation-delay: ' . ($index * 0.1) . 's">
                        <div class="member-image">
                            <img src="' . $member['image'] . '" alt="' . $member['name'] . '">
                        </div>
                        <div class="member-info">
                            <h3>' . $member['name'] . '</h3>
                            <p>' . $member['position'] . '</p>
                            <div class="social-links">';
                    
                    foreach ($member['social'] as $platform) {
                        $iconClass = 'fab fa-' . $platform . ($platform === 'linkedin' ? '-in' : '');
                        echo '<a href="#"><i class="' . $iconClass . '"></i></a>';
                    }
                    
                    echo '</div>
                        </div>
                    </div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="section stats">
        <div class="container">
            <div class="section-title">
                <h2>By The Numbers</h2>
                <p>Our impact and growth over the years</p>
            </div>
            <div class="stats-grid">
                <?php
                $stats = [
                    ['value' => '1M+', 'label' => 'Happy Customers'],
                    ['value' => '500+', 'label' => 'Brand Partners'],
                    ['value' => '30+', 'label' => 'Countries Served'],
                    ['value' => '99%', 'label' => 'Customer Satisfaction']
                ];

                foreach ($stats as $index => $stat) {
                    echo '<div class="stat-item fade-in" style="animation-delay: ' . ($index * 0.1) . 's">
                        <h3>' . $stat['value'] . '</h3>
                        <p>' . $stat['label'] . '</p>
                    </div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2 class="fade-in">Ready to Experience <?php echo $companyName; ?>?</h2>
            <p class="fade-in">Join millions of satisfied customers who trust us for their shopping needs.</p>
            <a href="#" class="btn fade-in">
                <i class="fas fa-shopping-bag"></i>
                Start Shopping Now
            </a>
        </div>
    </section>

    <!-- Footer -->
    <?php require 'includes/footer.php'; ?>

    <script>
        // Simple animation for stats counter
        document.addEventListener('DOMContentLoaded', function() {
            const statItems = document.querySelectorAll('.stat-item h3');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const stat = entry.target;
                        const target = parseInt(stat.textContent);
                        let current = 0;
                        const increment = target / 50;
                        
                        const timer = setInterval(() => {
                            current += increment;
                            if (current >= target) {
                                stat.textContent = target + (stat.textContent.includes('+') ? '+' : (stat.textContent.includes('%') ? '%' : ''));
                                clearInterval(timer);
                            } else {
                                stat.textContent = Math.floor(current) + (stat.textContent.includes('+') ? '+' : (stat.textContent.includes('%') ? '%' : ''));
                            }
                        }, 30);
                        
                        observer.unobserve(stat);
                    }
                });
            }, { threshold: 0.5 });
            
            statItems.forEach(stat => {
                observer.observe(stat);
            });

            // Add scroll animation
            const fadeElements = document.querySelectorAll('.fade-in');
            const fadeObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                        fadeObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            fadeElements.forEach(el => {
                el.style.animationPlayState = 'paused';
                fadeObserver.observe(el);
            });
        });
    </script>
</body>
</html>