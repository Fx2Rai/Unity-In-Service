    <?php
    $servername = "localhost"; // Change if necessary
    $username = "root"; // Your MySQL username
    $password = ""; // Your MySQL password
    $dbname = "disaster_volunteering"; // Ensure this matches your database name

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    ?>
