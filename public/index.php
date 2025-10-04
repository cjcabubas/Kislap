<?php
require_once __DIR__ . '/../config/dbconfig.php';

try {
    echo "Database connection successful!<br><br>";

    // Fetch records from application table
    $stmt = $pdo->query("SELECT * FROM application");
    $applications = $stmt->fetchAll();

    if (!$applications) {
        echo "No records found.";
    } else {
        echo "<b>Applications:</b><br>";
        foreach ($applications as $app) {
            echo "ID: " . $app['application_id'] . " | ";
            echo $app['firstName'] . " " . $app['lastName'] . " | ";
            echo "Phone: " . $app['phoneNumber'] . " | ";
            echo "Address: " . $app['address'] . " | ";
            echo "Status: " . $app['status'] . "<br>";
        }
    }

} catch (Exception $e) {
    echo "Connection or query failed: " . $e->getMessage();
}