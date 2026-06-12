<?php
/**
 * Database Connection Handler
 * Establishes a PDO connection to MySQL database
 */

$host = 'localhost';
$db_name = 'lovesite_db';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db_name};charset={$charset}";

$pdo_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $pdo_options);
    // Ensure some optional tables exist for admin pages (safe to run every request)
    $ddls = [
        "CREATE TABLE IF NOT EXISTS reasons (
            id INT AUTO_INCREMENT PRIMARY KEY,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS songs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            artist VARCHAR(255) NULL,
            personal_note TEXT NULL,
            display_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS site_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
    foreach ($ddls as $ddl) {
        $pdo->exec($ddl);
    }

    // Ensure default settings exist so admin-playlist.php can read/update them
    $ensureSettings = [
        ['playlist_subtitle', ''],
        ['songs_section_heading', 'Songs That Mean Something 🎶']
    ];
    $insertStmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) 
        SELECT ?, ? FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM site_settings WHERE setting_key = ?)");
    foreach ($ensureSettings as $s) {
        $insertStmt->execute([$s[0], $s[1], $s[0]]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    die('
        <html>
        <head>
            <title>Database Connection Error</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                    background: linear-gradient(135deg, #FFF0F5 0%, #FFD6E0 100%);
                }
                .error-container {
                    background: white;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 4px 20px rgba(232, 114, 138, 0.2);
                    max-width: 500px;
                    text-align: center;
                }
                h1 {
                    color: #C45C78;
                    margin-bottom: 16px;
                }
                p {
                    color: #3D2B35;
                    line-height: 1.6;
                }
                .error-code {
                    background: #FFF0F5;
                    padding: 12px;
                    border-radius: 6px;
                    margin-top: 16px;
                    font-family: monospace;
                    font-size: 12px;
                    color: #E8728A;
                    text-align: left;
                    max-height: 200px;
                    overflow-y: auto;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1>❤️ Oops!</h1>
                <p>We couldn\'t connect to the database. Please make sure MySQL is running and the database is set up correctly.</p>
                <div class="error-code">' . htmlspecialchars($e->getMessage()) . '</div>
            </div>
        </body>
        </html>
    ');
}
?>
