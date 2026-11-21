<?php

include 'sidebar1.php';

// If the user is not logged in, redirect them to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Retrieve the volunteer's name using session data
$volunteerName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ''; // Assuming the user name is stored in session
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Image Upload</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #FF6B35;
            --secondary: #004E89;
            --dark: #1A1A1D;
            --gray: #4E4E50;
            --light: #F8F9FA;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: var(--dark);
            color: var(--light);
            display: flex;
            min-height: 100vh;
        }


        .main-content {
            margin-left: 250px;
            padding: 2rem;
            flex: 1;
        }

        .upload-container {
            max-width: 800px;
            margin: 0 auto;
        }

        h1 {
            color: var(--primary);
            margin-bottom: 2rem;
            font-size: 2rem;
            border-bottom: 2px solid var(--gray);
            padding-bottom: 1rem;
        }

        .upload-card {
            background: #1E1E1E;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            background: #2D2D2D;
            border: 1px solid var(--gray);
            border-radius: 6px;
            color: var(--light);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus, textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255,107,53,0.2);
        }

        button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        button:hover {
            background: #E05A2B;
            transform: translateY(-2px);
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .image-card {
            background: #1E1E1E;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .image-card:hover {
            transform: translateY(-5px);
        }

        .image-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 2px solid var(--primary);
        }

        .image-info {
            padding: 1rem;
        }

        .caption {
            color: var(--light);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .volunteer-name {
            color: var(--primary);
            font-size: 0.85rem;
            font-weight: 600;
        }

        .message {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 6px;
            display: none;
        }

        .success {
            background: #4CAF50;
            border: 1px solid #45a049;
        }

        .error {
            background: #f44336;
            border: 1px solid #d32f2f;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-menu">
            <?php include 'sidebar1.php'; ?>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="upload-container">
            <h1><i class="fas fa-camera-retro"></i> Disaster Area Image Upload</h1>
            
            <div class="upload-card">
                <div id="message" class="message"></div>
                
                <form id="image-upload-form" enctype="multipart/form-data" method="POST">
                    <div class="form-group">
                        <!-- Set the volunteer's name from the session -->
                        <input type="text" name="volunteer_name" placeholder="Your Full Name" value="<?php echo htmlspecialchars($volunteerName); ?>" readonly required>
                    </div>
                    
                    <div class="form-group">
                        <input type="file" name="image" accept="image/*" required>
                    </div>
                    
                    <div class="form-group">
                        <textarea name="caption" rows="3" placeholder="Image caption..."></textarea>
                    </div>
                    
                    <button type="submit">
                        <i class="fas fa-cloud-upload-alt"></i> Upload Image
                    </button>
                </form>
            </div>

        </div>
    </main>

    <script>
        document.getElementById('image-upload-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const messageDiv = document.getElementById('message');
            
            try {
                const response = await fetch('upload_image.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.message) {
                    messageDiv.className = 'message success';
                    messageDiv.textContent = result.message;
                    messageDiv.style.display = 'block';
                    
                    // Refresh gallery
                    const galleryResponse = await fetch('fetch_images.php');
                    const galleryHTML = await galleryResponse.text();
                    document.getElementById('image-gallery').innerHTML = galleryHTML;
                    
                    form.reset();
                } else if (result.error) {
                    messageDiv.className = 'message error';
                    messageDiv.textContent = result.error;
                    messageDiv.style.display = 'block';
                }
                
                // Hide message after 3 seconds
                setTimeout(() => {
                    messageDiv.style.display = 'none';
                }, 3000);
                
            } catch (error) {
                messageDiv.className = 'message error';
                messageDiv.className = 'message success';
                messageDiv.style.display = 'block';
                setTimeout(() => {
                    messageDiv.style.display = 'none';
                }, 3000);
            }
        });
    </script>

</body>
</html>