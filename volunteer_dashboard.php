<?php
include 'db_connect.php';
include 'sidebar1.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Dashboard - Unity In Service</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --dark-bg: #121212;
            --card-bg: #1e1e1e;
            --light-text: #ffffff;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--light-text);
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            margin-left: 25%;
            padding: 20px;
            transition: var(--transition);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--box-shadow);
        }
        
        form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        input, select {
            padding: 10px 15px;
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: var(--light-text);
            flex: 1;
            min-width: 150px;
        }
        
        button {
            background: var(--primary-color);
            color: var(--light-text);
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }
        
        button:hover {
            opacity: 0.9;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .progress-container {
            width: 100%;
            height: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            transition: width 0.5s ease;
            background: var(--secondary-color);
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: var(--secondary-color);
            color: white;
            border-radius: var(--border-radius);
            display: none;
            z-index: 1000;
        }
        
        .notification.error {
            background: var(--danger-color);
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="header">
            <h1>Volunteer Dashboard</h1>
        </div>
        
        <div class="card">
            <h2>Update Contributions</h2>
           
            <?php if (isset($_GET['success'])): ?>
                <div class="notification"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="notification error"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>Current Requirements</h2>
            <table>
                <thead>
                    <tr>
                        <th>Requirement</th>
                        <th>Disaster Area</th>
                        <th>Needed</th>
                        <th>Collected</th>
                        <th>Progress</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM disaster_requirements");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    while ($row = $result->fetch_assoc()):
                        $progress = ($row['collected'] / $row['needed']) * 100;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['requirement']) ?></td>
                        <td><?= htmlspecialchars($row['disaster_area']) ?></td>
                        <td><?= $row['needed'] ?></td>
                        <td><?= $row['collected'] ?></td>
                        <td>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: <?= $progress ?>%"></div>
                            </div>
                            <small><?= round($progress) ?>%</small>
                        </td>
                        <td>
                            <form method="POST" action="update_requirements.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <input type="number" name="collected" value="<?= $row['collected'] ?>" 
                                       min="0" max="<?= $row['needed'] ?>" style="width: 80px;">
                                <button type="submit">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Notification handling
        const notifications = document.querySelectorAll('.notification');
        notifications.forEach(notification => {
            if (notification.textContent.trim() !== '') {
                notification.style.display = 'block';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 3000);
            }
        });

        // Form submission handling
        document.getElementById('volunteer-requirements-form').addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to update this contribution?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>