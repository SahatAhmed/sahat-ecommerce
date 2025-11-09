<?php
require_once __DIR__ . '/../includes/db.php';   // Load DB connection ($mysqli)

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                if (isset($_POST['id'])) {
                    $id = intval($_POST['id']);
                    $stmt = $mysqli->prepare("DELETE FROM contact_messages WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        $success = "Message deleted successfully!";
                    } else {
                        $error = "Error deleting message: " . $mysqli->error;
                    }
                    $stmt->close();
                }
                break;
            
            case 'mark_read':
                if (isset($_POST['id'])) {
                    $id = intval($_POST['id']);
                    $stmt = $mysqli->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $stmt->close();
                }
                break;
            
            case 'export_csv':
                exportToCSV($mysqli);
                exit;
        }
    }
}

// Export to CSV function
function exportToCSV($mysqli) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=contact_messages_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Message', 'Date']);
    
    $query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
    $result = $mysqli->query($query);
    
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['email'],
            $row['phone'],
            $row['message'],
            $row['created_at']
        ]);
    }
    
    fclose($output);
    exit;
}

// Check if table has is_read column, if not add it
$check_column = $mysqli->query("SHOW COLUMNS FROM contact_messages LIKE 'is_read'");
if ($check_column->num_rows == 0) {
    $mysqli->query("ALTER TABLE contact_messages ADD COLUMN is_read TINYINT(1) DEFAULT 0");
}

// Fetch all contact messages
$query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = $mysqli->query($query);

// Get statistics
$total_messages = $result->num_rows;
$today_messages = 0;
$unread_count = 0;

// Reset pointer to calculate stats
$result->data_seek(0);
$today = date('Y-m-d');
while ($row = $result->fetch_assoc()) {
    if (date('Y-m-d', strtotime($row['created_at'])) === $today) {
        $today_messages++;
    }
    if (isset($row['is_read']) && $row['is_read'] == 0) {
        $unread_count++;
    }
}

// Reset pointer again for main display
$result->data_seek(0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages Admin | Elite Solutions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            --warning-color: #f59e0b;
            --error-color: #ef4444;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 8px;
            --transition: all 0.3s ease;
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
            padding: 20px;
        }

        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Notifications */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: var(--radius);
            color: white;
            font-weight: 500;
            z-index: 1000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            box-shadow: var(--shadow-lg);
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            background: var(--success-color);
        }

        .notification.error {
            background: var(--error-color);
        }

        /* Header */
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .admin-header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-header h1 i {
            color: var(--primary-color);
        }

        .admin-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            color: var(--text-dark);
        }

        .btn:hover {
            background: var(--light-bg);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.total {
            background: var(--primary-light);
            color: var(--primary-color);
        }

        .stat-icon.today {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }

        .stat-icon.unread {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
        }

        .stat-info h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* Table */
        .table-container {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .table-header {
            padding: 20px;
            background: var(--light-bg);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h2 {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .search-box {
            display: flex;
            align-items: center;
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 8px 15px;
            width: 300px;
        }

        .search-box input {
            border: none;
            outline: none;
            width: 100%;
            padding: 5px;
            font-size: 0.95rem;
        }

        .search-box i {
            color: var(--text-light);
            margin-right: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--light-bg);
        }

        th {
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            color: var(--text-dark);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            background: var(--light-bg);
        }

        td {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: top;
        }

        tbody tr {
            transition: var(--transition);
        }

        tbody tr.unread {
            background: rgba(37, 99, 235, 0.05);
            border-left: 3px solid var(--primary-color);
        }

        tbody tr:hover {
            background: rgba(37, 99, 235, 0.03);
        }

        .message-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: var(--text-light);
        }

        .message-full {
            display: none;
            padding: 10px;
            background: var(--light-bg);
            border-radius: var(--radius);
            margin-top: 10px;
            line-height: 1.5;
        }

        .expand-btn {
            background: none;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
            font-size: 0.9rem;
            margin-top: 5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-new {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .badge-today {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }

        .badge-unread {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-bg);
            border: none;
            cursor: pointer;
            transition: var(--transition);
            color: var(--text-light);
        }

        .action-btn:hover {
            background: var(--primary-color);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--border-color);
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-xl);
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-header h3 {
            margin: 0;
            color: var(--text-dark);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
        }

        .message-detail {
            line-height: 1.6;
        }

        .message-detail p {
            margin-bottom: 15px;
        }

        .detail-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-right: 10px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .table-container {
                overflow-x: auto;
            }
            
            table {
                min-width: 900px;
            }
        }

        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .admin-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .search-box {
                width: 100%;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .admin-header h1 {
                font-size: 1.8rem;
            }
            
            .btn {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

    <!-- Notifications -->
    <?php if (isset($success)): ?>
    <div class="notification success" id="notification">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
    <div class="notification error" id="notification">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="admin-container">
        <!-- Header -->
        <div class="admin-header">
            <h1><i class="fas fa-envelope-open-text"></i> Contact Messages</h1>
            <div class="admin-actions">
                <a href="dashboard.php" class="btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="export_csv">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                </form>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-inbox"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $total_messages ?></h3>
                    <p>Total Messages</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon today">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $today_messages ?></h3>
                    <p>Today's Messages</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon unread">
                    <i class="fas fa-eye-slash"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $unread_count ?></h3>
                    <p>Unread Messages</p>
                </div>
            </div>
        </div>

        <!-- Messages Table -->
        <div class="table-container">
            <div class="table-header">
                <h2>All Messages</h2>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search messages...">
                </div>
            </div>

            <?php if ($total_messages > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): 
                        $isToday = date('Y-m-d', strtotime($row['created_at'])) === $today;
                        $isUnread = isset($row['is_read']) && $row['is_read'] == 0;
                    ?>
                    <tr class="<?= $isUnread ? 'unread' : '' ?>" id="message-<?= $row['id'] ?>">
                        <td><?= $row['id'] ?></td>
                        <td>
                            <div><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php if ($isToday): ?>
                                <span class="badge badge-today">Today</span>
                            <?php endif; ?>
                            <?php if ($isUnread): ?>
                                <span class="badge badge-unread">Unread</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="mailto:<?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?>" style="color: var(--primary-color); text-decoration: none;">
                                <?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </td>
                        <td><?= $row['phone'] ? htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8') : '<span style="color: var(--text-light);">N/A</span>' ?></td>
                        <td>
                            <div class="message-preview"><?= htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8') ?></div>
                            <button class="expand-btn" onclick="toggleMessage(this)">
                                <i class="fas fa-chevron-down"></i> Show More
                            </button>
                            <div class="message-full"><?= nl2br(htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8')) ?></div>
                        </td>
                        <td>
                            <div><?= date('M j, Y', strtotime($row['created_at'])) ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-light);">
                                <?= date('g:i A', strtotime($row['created_at'])) ?>
                            </div>
                        </td>
                        <td>
                            <div class="action-btns">
                                <button class="action-btn" title="Reply" onclick="replyToMessage('<?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>')">
                                    <i class="fas fa-reply"></i>
                                </button>
                                <button class="action-btn" title="View Details" onclick="viewMessage(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($row['phone'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>', `<?= addslashes(htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8')) ?>`, '<?= $row['created_at'] ?>')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this message?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="action-btn" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Messages Yet</h3>
                <p>All contact form submissions will appear here.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Message Detail Modal -->
    <div class="modal" id="messageModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Message Details</h3>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="message-detail" id="messageDetail">
                <!-- Message details will be inserted here -->
            </div>
        </div>
    </div>

    <script>
        // Show notifications
        document.addEventListener('DOMContentLoaded', function() {
            const notification = document.getElementById('notification');
            if (notification) {
                setTimeout(() => {
                    notification.classList.add('show');
                }, 100);
                
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 5000);
            }
        });

        // Toggle message expansion
        function toggleMessage(button) {
            const messageFull = button.nextElementSibling;
            const icon = button.querySelector('i');
            
            if (messageFull.style.display === 'block') {
                messageFull.style.display = 'none';
                icon.className = 'fas fa-chevron-down';
                button.innerHTML = '<i class="fas fa-chevron-down"></i> Show More';
            } else {
                messageFull.style.display = 'block';
                icon.className = 'fas fa-chevron-up';
                button.innerHTML = '<i class="fas fa-chevron-up"></i> Show Less';
            }
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Action functions
        function replyToMessage(email, name) {
            window.location.href = `mailto:${email}?subject=Re: Your inquiry to Elite Solutions&body=Dear ${name},%0D%0A%0D%0AThank you for contacting us. We have received your message and will get back to you shortly.%0D%0A%0D%0ABest regards,%0D%0AElite Solutions Team`;
        }

        function viewMessage(id, name, email, phone, message, date) {
            const modal = document.getElementById('messageModal');
            const detailDiv = document.getElementById('messageDetail');
            
            const formattedDate = new Date(date).toLocaleString();
            
            detailDiv.innerHTML = `
                <p><span class="detail-label">From:</span> ${name}</p>
                <p><span class="detail-label">Email:</span> <a href="mailto:${email}">${email}</a></p>
                <p><span class="detail-label">Phone:</span> ${phone}</p>
                <p><span class="detail-label">Date:</span> ${formattedDate}</p>
                <p><span class="detail-label">Message:</span></p>
                <div style="background: var(--light-bg); padding: 15px; border-radius: var(--radius); margin-top: 10px;">
                    ${message.replace(/\n/g, '<br>')}
                </div>
                <div style="margin-top: 20px; display: flex; gap: 10px;">
                    <button class="btn btn-primary" onclick="replyToMessage('${email}', '${name}')">
                        <i class="fas fa-reply"></i> Reply
                    </button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="mark_read">
                        <input type="hidden" name="id" value="${id}">
                        <button type="submit" class="btn">
                            <i class="fas fa-check"></i> Mark as Read
                        </button>
                    </form>
                </div>
            `;
            
            modal.style.display = 'flex';
            
            // Mark as read when viewing
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=mark_read&id=${id}`
            }).then(() => {
                const row = document.getElementById(`message-${id}`);
                if (row) {
                    row.classList.remove('unread');
                    // Update unread count
                    const unreadBadge = row.querySelector('.badge-unread');
                    if (unreadBadge) {
                        unreadBadge.remove();
                    }
                }
            });
        }

        function closeModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        // Close modal when clicking outside
        document.getElementById('messageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>