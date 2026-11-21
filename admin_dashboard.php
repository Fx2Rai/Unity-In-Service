<?php
include 'db_connect.php';
include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Unity In Service</title>
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
        .map-search-container {
        position: absolute;
        top: 20px;
        left: 20px;
        z-index: 1000;
        width: 300px;
    }
    
    .map-search-box {
        display: flex;
        gap: 5px;
    }
    
    #search-input {
        flex: 1;
        padding: 8px;
        border-radius: 4px;
        border: none;
        background: rgba(255, 255, 255, 0.9);
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
        
        .map-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 10000;
        }
        
        #map-container {
            width: 80%;
            height: 70vh;
            margin: 5% auto;
            border-radius: 8px;
            position: relative;
        }
        
        .map-instructions {
            color: white;
            text-align: center;
            padding: 10px;
        }
        
        .close-map {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 15px;
            background: var(--danger-color);
            border: none;
            color: white;
            border-radius: var(--border-radius);
            cursor: pointer;
            z-index: 10001;
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
            <h1>Admin Dashboard</h1>
        </div>
        
        <div class="card">
            <h2>Add New Requirement</h2>
                <form id="requirements-form" method="POST" action="add_requirements.php">
                    <input type="text" name="requirement" placeholder="Requirement" required>
                    <input type="text" name="disaster_area" placeholder="Disaster Area" required>
                    <input type="number" name="needed" placeholder="Quantity Needed" required>
                    <input type="text" id="latitude" name="latitude" placeholder="Latitude" readonly required>
                    <input type="text" id="longitude" name="longitude" placeholder="Longitude" readonly required>
                    <button type="button" onclick="openMapPicker()">Select Location</button>
                    <button type="submit">Add Requirement</button>
                </form>
            
                <div class="map-modal" id="map-modal">
                    <button class="close-map" onclick="closeMapPicker()">×</button>
                    <div class="map-search-container">
                        <div class="map-search-box">
                            <input type="text" id="search-input" placeholder="Search location...">
                            <button onclick="searchLocation()" style="padding: 8px 12px;">Search</button>
                        </div>
                    </div>
                    <div class="map-instructions">Click on the map to select location or search above</div>
                    <div id="map-container"></div>
                </div>
            
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
                <th>Location</th>
                <th>Needed</th>
                <th>Progress</th>
                <th>Status</th>
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
                $status = 'Pending';
                $status_color = '#e74c3c'; // Red
                
                if ($progress >= 100) {
                    $status = 'Completed';
                    $status_color = '#2ecc71'; // Green
                } elseif ($progress >= 50) {
                    $status = 'In Progress';
                    $status_color = '#f39c12'; // Orange
                }
            ?>
            <tr>
                <td><?= htmlspecialchars($row['requirement']) ?></td>
                <td><?= htmlspecialchars($row['disaster_area']) ?></td>
                <td><?= round($row['latitude'], 4) ?>, <?= round($row['longitude'], 4) ?></td>
                <td><?= $row['needed'] ?></td>
                <td>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: <?= $progress ?>%; background: <?= $status_color ?>"></div>
                        <span><?= round($progress) ?>%</span>
                    </div>
                </td>
                <td>
                    <?= $status ?> (<?= round($progress) ?>%)
                </td>
                <td>
                    <form method="POST" action="update_requirements.php" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <input type="number" name="collected" value="<?= $row['collected'] ?>" min="0" max="<?= $row['needed'] ?>">
                        <button type="submit">Update</button>
                    </form>
                    <form method="POST" action="delete_requirement.php" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" style="background: var(--danger-color);">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
    </div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        let map;
        let marker;

        async function searchLocation() {
        const query = document.getElementById('search-input').value;
        if (!query) return;

        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.length > 0) {
                const firstResult = data[0];
                const lat = parseFloat(firstResult.lat);
                const lon = parseFloat(firstResult.lon);
                
                map.setView([lat, lon], 12);
                
                if (marker) map.removeLayer(marker);
                marker = L.marker([lat, lon]).addTo(map);
                
                document.getElementById('latitude').value = lat.toFixed(6);
                document.getElementById('longitude').value = lon.toFixed(6);
            } else {
                alert('Location not found');
            }
        } catch (error) {
            console.error('Search error:', error);
            alert('Error searching location');
        }
    }

    // Update the existing map click handler to clear search input
    map.on('click', function(e) {
        document.getElementById('search-input').value = '';
        if (marker) map.removeLayer(marker);
        marker = L.marker(e.latlng).addTo(map);
        document.getElementById('latitude').value = e.latlng.lat.toFixed(6);
        document.getElementById('longitude').value = e.latlng.lng.toFixed(6);
    });

        function openMapPicker() {
            document.getElementById('map-modal').style.display = 'block';
            if (!map) {
                map = L.map('map-container').setView([20, 0], 2);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                
                map.on('click', function(e) {
                    if (marker) map.removeLayer(marker);
                    marker = L.marker(e.latlng).addTo(map);
                    document.getElementById('latitude').value = e.latlng.lat.toFixed(6);
                    document.getElementById('longitude').value = e.latlng.lng.toFixed(6);
                });
            }
        }

        function closeMapPicker() {
            document.getElementById('map-modal').style.display = 'none';
        }

        document.getElementById('requirements-form').addEventListener('submit', function(e) {
            if (!document.getElementById('latitude').value || !document.getElementById('longitude').value) {
                e.preventDefault();
                alert('Please select a location on the map');
            }
        });

        // Show notifications
        const notifications = document.querySelectorAll('.notification');
        notifications.forEach(notification => {
            if (notification.textContent.trim() !== '') {
                notification.style.display = 'block';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 3000);
            }
        });
    </script>
</body>
</html>