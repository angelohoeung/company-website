<?php
// Parses the .env file
function loadEnv($path)
{
    if (!file_exists($path)) {
        throw new Exception("Environment file not found at: $path");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
            continue; // Skip comments or malformed lines
        }

        // Split into name and value
        list($name, $value) = array_map('trim', explode('=', $line, 2));
        putenv("$name=$value");
        $_ENV[$name] = $value;
    }
}

function getDatabaseConnection()
{
    // Validate environment variables
    $requiredEnvVars = ['DB_SERVER', 'DB_USERNAME', 'DB_PASSWORD', 'DB_NAME'];
    foreach ($requiredEnvVars as $var) {
        if (empty($_ENV[$var])) {
            throw new Exception("Missing required environment variable: $var");
        }
    }

    $servername = $_ENV['DB_SERVER'];
    $username = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'];
    $dbname = $_ENV['DB_NAME'];

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    return $conn;
}

try {
    loadEnv(__DIR__ . '/.env');
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die($e->getMessage());
}
