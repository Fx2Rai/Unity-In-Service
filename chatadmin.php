<?php
session_start();
include 'db_connect.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'volunteer'; // Check if user is admin

// Fetch user details
$query = $conn->prepare("SELECT name, district, phone FROM volunteers WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

// Fetch messages
$messages = $conn->query("SELECT v.name, v.phone, vc.id, vc.sender_id, vc.district, vc.message, vc.sent_at 
                          FROM volunteer_chat vc 
                          JOIN volunteers v ON vc.sender_id = v.id 
                          ORDER BY vc.sent_at ASC")->fetch_all(MYSQLI_ASSOC);

// Insert message if volunteer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message']) && $user_role != 'admin') {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO volunteer_chat (sender_id, district, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $user['district'], $message);
        $stmt->execute();
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Chat</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Dark Theme */
        body {
            background-color: #181818;
            color: #f1f1f1;
            font-family: Arial, sans-serif;
        }

        .chat-container {
            max-width: 800px;
            margin: 20px auto;
            background: #2c3e50;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .chat-header {
            padding: 1.5rem;
            background: #34495e;
            color: #ffffff;
            font-size: 1.4rem;
            font-weight: 600;
            text-align: center;
        }

        .chat-messages {
            height: 60vh;
            padding: 1.5rem;
            overflow-y: auto;
            background: #1c2833;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .message {
            max-width: 75%;
            padding: 1rem 1.5rem;
            border-radius: 1.5rem;
            line-height: 1.4;
            word-break: break-word;
            animation: fadeIn 0.3s ease-in;
        }

        .message.sent {
            background: #3498db;
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 0.5rem;
        }

        .message.received {
            background: #4f5b62;
            color: #e1e1e1;
            margin-right: auto;
            border-bottom-left-radius: 0.5rem;
        }

        .message strong {
            display: block;
            font-size: 0.9em;
            margin-bottom: 0.5rem;
            opacity: 0.8;
        }

        .chat-input {
            display: flex;
            padding: 1.5rem;
            background: #2c3e50;
            border-top: 1px solid #4f5b62;
            gap: 1rem;
        }

        .chat-input input {
            flex: 1;
            padding: 0.8rem 1.2rem;
            border: 1px solid #4f5b62;
            border-radius: 2rem;
            font-size: 1rem;
            background: #1c2833;
            color: #e1e1e1;
            transition: all 0.3s ease;
        }

        .chat-input input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .chat-input button {
            padding: 0.8rem 1.5rem;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .chat-input button:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Scrollbar styling */
        .chat-messages::-webkit-scrollbar {
            width: 8px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: #2c3e50;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: #555;
            border-radius: 4px;
        }

        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: #444;
        }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">Volunteer Chat</div>
    <div class="chat-messages" id="chat-messages">
        <?php foreach ($messages as $msg): ?>
            <div class="message <?= $msg['sender_id'] == $user_id ? 'sent' : 'received' ?>" id="message-<?= $msg['id'] ?>">
                <strong><?= htmlspecialchars($msg['name']) ?> (<?= htmlspecialchars($msg['district']) ?>) Phone: <?= htmlspecialchars($msg['phone']) ?></strong>
                <p><?= htmlspecialchars($msg['message']) ?></p>
                <?php if ($msg['sender_id'] == $user_id || $user_role == 'admin'): ?>
                    <button class="delete-message" 
        data-message-id="<?= $msg['sender_id'] ?>" 
        data-chat-id="<?= $msg['id'] ?>"
        style="
            padding: 0.5rem 1rem; 
            background: #e74c3c; 
            color: white; 
            border: none; 
            border-radius: 1rem; 
            cursor: pointer; 
            font-size: 0.9rem; 
            transition: background 0.3s ease, transform 0.3s ease;
        "
        onmouseover="this.style.background='#c0392b'; this.style.transform='translateY(-2px)';" 
        onmouseout="this.style.background='#e74c3c'; this.style.transform='translateY(0)';">
    Delete
</button>
                    
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if ($user_role != 'admin'): ?>
        <form id="chat-form" class="chat-input" method="POST">
            <input type="text" name="message" id="message" placeholder="Type a message..." autocomplete="off">
            <button type="submit">Send</button>
        </form>
    <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const chatMessages = document.getElementById('chat-messages');
    
    chatMessages.addEventListener('click', function(event) {
        if (event.target && event.target.classList.contains('delete-message')) {
            const messageId = event.target.getAttribute('data-message-id');
            const chatId = event.target.getAttribute('data-chat-id');
            
            if (confirm("Are you sure you want to delete this message?")) {
                fetch('delete_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        'message_id': messageId,
                        'chat_id': chatId
                    })
                })
                .then(response => response.text())
                .then(data => {
                    if (data === "Message deleted successfully.") {
                        const messageDiv = document.getElementById('message-' + chatId);
                        messageDiv.remove();
                    }
                    alert(data); // Display response message
                })
                .catch(error => {
                    alert("Error deleting message.");
                });
            }
        }
    });
});
</script>

</body>
</html>


<?php


// Ensure the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php"); // Redirect to login if the user is not logged in as admin
    exit;
}

// Fetch session variables
$user_email = $_SESSION['user_email'] ?? 'Unknown User';
$user_role = $_SESSION['user_role'] ?? 'Unknown Role';
?>

<!-- Common Sidebar HTML -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>Disaster Management</h2>
    </div>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Image Upload</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <div class="user-profile">
        <!-- You can dynamically change the image path if needed -->
        <img src="uploads/WhatsApp Image 2025-04-01 at 13.44.42_e43f64f9.jpg" alt="Admin" class="avatar">
        <div class="user-info">
            <h3><?php echo htmlspecialchars($user_email); ?></h3> <!-- Display user email -->
            <p><?php echo htmlspecialchars($user_role); ?></p> <!-- Display user role (Admin) -->
        </div>
    </div>

    <div class="sidebar-menu">
        <ul>
            <li><a href="admin_dashboard.php" class="menu-item" data-page="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="volunteer_management.php" class="menu-item" data-page="volunteers"><i class="fas fa-users"></i> Volunteers</a></li>
            <li><a href="image_analyzer.php" class="menu-item" data-page="analyzer"><i class="fas fa-camera"></i> Image Analyzer</a></li>
            <li><a href="chatadmin.php" class="menu-item" data-page="analyzer"><i class="fas fa-camera"></i> Messages</a></li>

            <li class="divider"></li>
            
            <li><a href="login.php" class="menu-item"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    <div class="sidebar-footer">
        <p>© 2025 Disaster Management</p>
    </div>
</nav>

<!-- CSS for Sidebar -->
<style>
:root {
    --sidebar-width: 280px;
    --sidebar-bg: #1e2a31;
    --sidebar-hover: #2c3e50;
    --sidebar-active: #3498db;
    --sidebar-text: #ecf0f1;
    --sidebar-muted: #bdc3c7;
    --dark-bg: #121212;
    --card-bg: #1e1e1e;
    --transition-speed: 0.3s;
}

.sidebar {
    width: var(--sidebar-width);
    height: 100vh;
    background: var(--sidebar-bg);
    position: fixed;
    left: -var(--sidebar-width);
    top: 0;
    transition: left var(--transition-speed) ease-in-out;
    z-index: 1000;
    box-shadow: 3px 0 15px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    color: var(--sidebar-text);
}

.sidebar.open {
    left: 0;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-header h2 {
    margin: 0;
    font-size: 22px;
    font-weight: 600;
    color: var(--sidebar-text);
}

.user-profile {
    display: flex;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.user-profile .avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin-right: 15px;
    object-fit: cover;
    border: 2px solid var(--sidebar-active);
}

.user-profile .user-info h3 {
    margin: 0 0 5px 0;
    font-size: 16px;
    font-weight: 600;
}

.user-profile .user-info p {
    margin: 0;
    font-size: 14px;
    color: var(--sidebar-muted);
}

.sidebar-menu {
    flex: 1;
    overflow-y: auto;
    padding: 15px 0;
}

.sidebar-menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu ul li {
    margin-bottom: 5px;
}

.sidebar-menu ul li.divider {
    height: 1px;
    background: rgba(255, 255, 255, 0.1);
    margin: 15px 20px;
}

.sidebar-menu ul li a {
    color: var(--sidebar-text);
    text-decoration: none;
    font-size: 16px;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    transition: all var(--transition-speed);
    border-left: 4px solid transparent;
}

.sidebar-menu ul li a i {
    margin-right: 15px;
    width: 20px;
    text-align: center;
}

.sidebar-menu ul li a:hover {
    background: var(--sidebar-hover);
    border-left: 4px solid var(--sidebar-active);
}

.sidebar-menu ul li a.active {
    background: rgba(52, 152, 219, 0.2);
    border-left: 4px solid var(--sidebar-active);
    font-weight: 600;
}

.sidebar-footer {
    padding: 15px 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
}

.sidebar-footer p {
    margin: 0;
    font-size: 14px;
    color: var(--sidebar-muted);
}

/* Menu Button */
.menu-btn {
    position: fixed;
    left: 20px;
    top: 20px;
    background: var(--sidebar-active);
    color: white;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    border-radius: 5px;
    transition: background var(--transition-speed);
    z-index: 1100;
    display: flex;
    align-items: center;
    font-weight: 500;
}

.menu-btn i {
    margin-right: 5px;
}

.menu-btn:hover {
    background: #2980b9;
}

/* Main Content Adjustment */
.main-content {
    margin-left: 0;
    padding: 70px 20px 20px 20px;
    transition: margin-left var(--transition-speed) ease-in-out;
    min-height: 100vh;
    background: var(--dark-bg);
}

.main-content.shift {
    margin-left: var(--sidebar-width);
}

</style>

<!-- JavaScript for Sidebar Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const menuBtn = document.getElementById('menuBtn');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    const menuItems = document.querySelectorAll('.menu-item');
    
    // Toggle sidebar on menu button click
    if (menuBtn) {
        menuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            mainContent.classList.toggle('shift');
        });
    }
    
    // Set active menu item based on current page
    const currentPage = window.location.pathname.split('/').pop();
    menuItems.forEach(item => {
        const itemPage = item.getAttribute('data-page');
        if (currentPage.includes(itemPage)) {
            item.classList.add('active');
        }
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const isClickInsideSidebar = sidebar.contains(event.target);
        const isClickMenuBtn = menuBtn.contains(event.target);
        
        if (!isClickInsideSidebar && !isClickMenuBtn && window.innerWidth < 992 && sidebar.classList.contains('open')) {
            sidebar.classList.remove('open');
            mainContent.classList.remove('shift');
        }
    });
    
    // Toggle sidebar on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            sidebar.classList.add('open');
            mainContent.classList.add('shift');
        } else {
            sidebar.classList.remove('open');
            mainContent.classList.remove('shift');
        }
    });
    
    // Initialize sidebar state based on screen size
    if (window.innerWidth >= 992) {
        sidebar.classList.add('open');
        mainContent.classList.add('shift');
    }
});
</script>
