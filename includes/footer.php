<!-- footer.php -->
<footer class="main-footer">
    <div class="footer-container">

        <div class="footer-section">
            <h3>Sahat E-Commerce</h3>
            <p>Your trusted shopping destination.</p>
            <a href="<?= BASE_URL ?>"><img src="<?= BASE_URL ?>assets/images/logo.png" alt="Logo" class="nav-footer-logo"></a>
        </div>

        <div class="footer-section">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>index.php">Home</a></li>
                <li><a href="<?= BASE_URL ?>about.php">About Us</a></li>
                <li><a href="<?= BASE_URL ?>contact.php">Contact Us</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h4>Navigation</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>orders.php">My Order</a></li>
                <li><a href="<?= BASE_URL ?>cart.php">Cart</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h4>Follow Us</h4>
            <div class="social">
    <a href="https://www.youtube.com/@SAHATTECHSTUDIO" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
    <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
    <a href="https://rb.gy/bug5ol" aria-label="LinkedIn"><i class="fa-brands fa-linkedin"></i></a>
    <a href="https://github.com/SahatAhmed" aria-label="GitHub"><i class="fa-brands fa-github"></i></a>
</div>

        </div>

    </div>

    <div class="footer-bottom">
        © <?php echo date("Y"); ?> Sahat E-Commerce — All Rights Reserved
    </div>
</footer>

<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .main-footer {
        width: 100%;
        background: #111;
        color: #eee;
        padding: 40px 0px 0 0px;
        margin-top: 40px;
        margin-left: 0px;
    }

    .nav-footer-logo { height: 60px; width: auto; margin-left: 20px; margin-top: 10px; transition: transform 0.3s ease; }
    .nav-footer-logo:hover { transform: scale(1.05); }

    /* ✅ EXACT SAME WIDTH AS HEADER (max-width:1200px) */
    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;

        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 40px;
    }

    .footer-section h3,
    .footer-section h4 {
        margin-bottom: 12px;
        color: #fff;
    }

    .footer-section p { color: #bbb; }

    .footer-section ul {
        list-style: none;
        padding: 0;
    }

    .footer-section ul li {
        margin: 6px 0;
    }

    .footer-section ul li a {
        text-decoration: none;
        color: #bbb;
        transition: .3s;
    }

    .footer-section ul li a:hover {
        color: #ff6b35;
    }

    .social a {
        font-size: 20px;
        margin-right: 12px;
        color: #ccc;
        transition: .3s;
    }

    .social a:hover {
        color: #ff6b35;
    }

    .footer-bottom {
        margin-top: 30px;
        text-align: center;
        border-top: 1px solid #333;
        padding: 15px 0;
        font-size: 14px;
        color: #aaa;
    }
</style>
