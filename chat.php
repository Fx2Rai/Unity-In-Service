<?php
session_start();
include 'db_connect.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$query = $conn->prepare("SELECT name, district FROM volunteers WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

// Insert message into database
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO volunteer_chat (sender_id, district, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $user['district'], $message);
        $stmt->execute();
    }
    exit();
}

// Fetch messages as JSON
if (isset($_GET['fetch'])) {
    $query = "SELECT v.name, vc.sender_id, vc.district, vc.message, vc.sent_at 
              FROM volunteer_chat vc
              JOIN volunteers v ON vc.sender_id = v.id
              ORDER BY vc.sent_at ASC";
    $result = $conn->query($query);
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode(["messages" => $messages]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Chat - Unity In Service</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #121212;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .chat-container {
            width: 90%;
            max-width: 500px;
            background: #1e1e1e;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
        }
        .chat-header {
            background: #ff5722;
            padding: 15px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
        }
        .chat-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 400px;
        }
        .message {
            padding: 10px;
            border-radius: 8px;
            max-width: 75%;
            word-wrap: break-word;
            font-size: 14px;
        }
        .sent {
            align-self: flex-end;
            background: #ff5722;
            color: white;
            border-top-right-radius: 0;
        }
        .received {
            align-self: flex-start;
            background: #333;
            color: white;
            border-top-left-radius: 0;
        }
        .chat-input {
            display: flex;
            padding: 10px;
            background: #222;
            border-top: 1px solid #333;
        }
        .chat-input input {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #333;
            color: white;
            outline: none;
        }
        .chat-input button {
            background: #ff5722;
            border: none;
            padding: 10px 15px;
            margin-left: 10px;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            transition: 0.3s ease;
        }
        .chat-input button:hover {
            background: #e64a19;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">Volunteer Chat</div>

        <div class="chat-messages" id="chat-messages"></div>

        <form id="chat-form" class="chat-input">
            <input type="text" name="message" id="message" placeholder="Type a message..." autocomplete="off">
            <button type="submit">Send</button>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const chatMessages = document.getElementById("chat-messages");
            const chatForm = document.getElementById("chat-form");
            const messageInput = document.getElementById("message");

            function fetchMessages() {
                fetch("?fetch=1")
                    .then(response => response.json())
                    .then(data => {
                        chatMessages.innerHTML = "";
                        data.messages.forEach(msg => {
                            const messageElement = document.createElement("div");
                            messageElement.classList.add("message", msg.sender_id == <?= $user_id ?> ? "sent" : "received");
                            messageElement.innerHTML = `<strong>${msg.name} (${msg.district}):</strong> ${msg.message}`;
                            chatMessages.appendChild(messageElement);
                        });
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    })
                    .catch(error => console.error("Error fetching messages:", error));
            }

            chatForm.addEventListener("submit", function (e) {
                e.preventDefault();
                const messageText = messageInput.value.trim();
                if (!messageText) return;

                const formData = new FormData();
                formData.append("message", messageText);

                fetch(window.location.href, {
                    method: "POST",
                    body: formData
                }).then(() => {
                    messageInput.value = "";
                    fetchMessages();
                }).catch(error => console.error("Error sending message:", error));
            });

            fetchMessages();
            setInterval(fetchMessages, 3000);
        });
    </script>
</body>
</html>
<?php

require 'db_connect.php'; // Include database connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details from the database
$sql = "SELECT name, profile_image FROM volunteers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

$user_name = $user_data['name'] ?? 'Unknown User';
$profile_image = !empty($user_data['profile_image']) ? $user_data['profile_image'] : 'uploads/default_avatar.jpg';

// Store name in session for future use
$_SESSION['user_name'] = $user_name;
$_SESSION['profile_image'] = $profile_image;

// Get user role
$user_role = $_SESSION['user_role'] ?? 'Unknown Role';

// Define the sidebar menu based on user role
$menu_items = ($user_role === 'admin') ? "
    <li><a href='admin_dashboard.php' class='menu-item' data-page='dashboard'><i class='fas fa-tachometer-alt'></i> Dashboard</a></li>
    <li><a href='volunteer_management.php' class='menu-item' data-page='volunteers'><i class='fas fa-users'></i> Volunteers</a></li>
    <li><a href='image_analyzer.php' class='menu-item' data-page='analyzer'><i class='fas fa-camera'></i> Image Analyzer</a></li>
" : "
    <li><a href='volunteer_dashboard.php' class='menu-item' data-page='requirements'><i class='fas fa-tasks'></i> Manage Requirements</a></li>
    <li><a href='volunteer_image_upload.php' class='menu-item' data-page='upload'><i class='fas fa-upload'></i> Upload Image</a></li>
    <li><a href='chat.php' class='menu-item' data-page='messages'><i class='fas fa-comments'></i> Messages</a></li>
    <li><a href='volunteer_profile.php' class='menu-item' data-page='profile'><i class='fas fa-user'></i> My Profile</a></li>
";
?>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>Disaster Management</h2>
    </div>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Image Upload</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <div class="user-profile">
        <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="User" class="avatar">
        <div class="user-info">
            <h3><?php echo htmlspecialchars($user_name); ?></h3>
            <p><?php echo htmlspecialchars($user_role); ?></p>
        </div>
    </div>
    <div class="sidebar-menu">
        <ul>
            <?php echo $menu_items; ?>
            <li class="divider"></li>
            <li><a href="login.php" class="menu-item"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    <div class="sidebar-footer">
        <p>&copy; 2025 Disaster Management</p>
    </div>
</nav>

<!-- Sidebar Styles -->
<style>
:root {
    --sidebar-width: 280px;
    --sidebar-bg: #1e2a31;
    --sidebar-hover: #2c3e50;
    --sidebar-active: #3498db;
    --sidebar-text: #ecf0f1;
    --sidebar-muted: #bdc3c7;
    --dark-bg: #121212;
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
    display: flex;
    flex-direction: column;
    color: var(--sidebar-text);
}

.sidebar.open {
    left: 0;
}

.sidebar-header {
    padding: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.user-profile {
    display: flex;
    align-items: center;
    padding: 20px;
}

.user-profile .avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--sidebar-active);
    margin-right: 15px;
}

.sidebar-menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
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

.sidebar-menu ul li a:hover {
    background: var(--sidebar-hover);
    border-left: 4px solid var(--sidebar-active);
}

.sidebar-footer {
    padding: 15px 20px;
    text-align: center;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}
</style>

<!-- Sidebar JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    const menuItems = document.querySelectorAll('.menu-item');

    // Toggle sidebar on menu button click
    const menuBtn = document.getElementById('menuBtn');
    if (menuBtn) {
        menuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            mainContent.classList.toggle('shift');
        });
    }

    // Set active menu item based on current page
    const currentPage = window.location.pathname.split('/').pop();
    menuItems.forEach(item => {
        if (currentPage.includes(item.getAttribute('data-page'))) {
            item.classList.add('active');
        }
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (!sidebar.contains(event.target) && !menuBtn.contains(event.target) && window.innerWidth < 992) {
            sidebar.classList.remove('open');
            mainContent.classList.remove('shift');
        }
    });

    // Auto open sidebar on larger screens
    if (window.innerWidth >= 992) {
        sidebar.classList.add('open');
        mainContent.classList.add('shift');
    }
});
</script>
