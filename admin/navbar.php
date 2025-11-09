<?php
// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}
?>
<header class="admin-header">
    <div class="nav-container">
        <div class="brand">
            <a href="dashboard.php">
                <i class="fas fa-chart-line"></i>
                AdminPanel
            </a>
        </div>
        <nav class="admin-nav">
            <a href="dashboard.php" <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : '' ?>>
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            <a href="products.php" <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'class="active"' : '' ?>>
                <i class="fas fa-box"></i>
                Products
            </a>
            <a href="orders.php" <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'class="active"' : '' ?>>
                <i class="fas fa-shopping-cart"></i>
                Orders
            </a>
            <a href="reports.php" <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'class="active"' : '' ?>>
                <i class="fas fa-chart-bar"></i>
                Reports
            </a>
            <a href="admin_contact.php" <?= basename($_SERVER['PHP_SELF']) == 'admin_contact.php' ? 'class="active"' : '' ?>>
                <i class="fas fa-comment"></i>
                Message
            </a>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </nav>
    </div>
</header>

<style>
:root {
    --primary: #2563eb;
    --primary-dark: #1d4ed8;
    --secondary: #64748b;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #06b6d4;
    --light: #f8fafc;
    --dark: #1e293b;
    --border: #e2e8f0;
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Premium Navigation */
.admin-header {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    padding: 1rem 2rem;
    box-shadow: var(--shadow);
    position: sticky;
    top: 0;
    z-index: 100;
    border-bottom: 1px solid var(--border);
}

.nav-container {
    max-width: 1400px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.brand a {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.admin-nav {
    display: flex;
    gap: 2rem;
    align-items: center;
}

.admin-nav a {
    color: var(--secondary);
    text-decoration: none;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.admin-nav a:hover, .admin-nav a.active {
    color: var(--primary);
    background: var(--light);
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .admin-header {
        padding: 1rem;
    }

    .nav-container {
        flex-direction: column;
        gap: 1rem;
    }

    .admin-nav {
        gap: 1rem;
    }
}
</style>