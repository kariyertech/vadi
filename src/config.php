<?php
require_once 'dbconfig.php';

try {
    $db = new PDO(
        "pgsql:host=$db_host;port=$db_port;dbname=$db_name",
        $db_user,
        $db_password
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if the projects table exists, if not create it
    $tableExists = $db->query("SELECT to_regclass('public.projects') IS NOT NULL as exists")->fetch(PDO::FETCH_ASSOC);
    
    if (!$tableExists['exists']) {
        $db->exec("
            CREATE TABLE projects (
                id SERIAL PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                project_name VARCHAR(255) NOT NULL,
                project_type VARCHAR(50) NOT NULL,
                project_goal TEXT NOT NULL,
                project_description TEXT,
                project_requirements TEXT,
                keyword_tags TEXT[],
                file_path VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Create admin_users table if it doesn't exist
        $db->exec("
            CREATE TABLE IF NOT EXISTS admin_users (
                id SERIAL PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Insert a default admin user
        $defaultAdminEmail = 'admin@vadi.com';
        $defaultAdminPassword = generate_password_hash('Vadi@Admin2025');
        
        $stmt = $db->prepare("INSERT INTO admin_users (email, password) VALUES (?, ?)");
        $stmt->execute([$defaultAdminEmail, $defaultAdminPassword]);
        
        // Insert an example user
        $exampleUserEmail = 'user@example.com';
        $exampleUserPassword = generate_password_hash('User@2025');
        
        $stmt = $db->prepare("INSERT INTO admin_users (email, password) VALUES (?, ?)");
        $stmt->execute([$exampleUserEmail, $exampleUserPassword]);
    }
    
} catch (PDOException $e) {
    // Log the error
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Display a user-friendly message
    die("Veritabanı bağlantısı kurulamadı. Lütfen daha sonra tekrar deneyiniz.");
}

// Function to sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to handle file upload
function handleFileUpload($file) {
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
        // Ensure proper permissions for the uploads directory
        chmod($targetDir, 0777);
    }
    
    $fileName = basename($file["name"]);
    $targetPath = $targetDir . time() . '_' . $fileName;
    
    $allowedTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'mp4' => 'video/mp4',
        'wav' => 'audio/wav',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xls' => 'application/vnd.ms-excel'
    ];
    
    $fileType = mime_content_type($file["tmp_name"]);
    if (!in_array($fileType, $allowedTypes)) {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $targetPath)) {
        return $targetPath;
    }
    return false;
} 