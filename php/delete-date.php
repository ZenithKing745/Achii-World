<?php
/**
 * Delete a date record
 * Called via GET parameter id
 */

require_once 'db.php';

// Get and validate ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ../set-date.php');
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM dates WHERE id = :id');
    $stmt->execute([':id' => $id]);
} catch (PDOException $e) {
    error_log('Delete date error: ' . $e->getMessage());
}

header('Location: ../set-date.php');
exit;
?>
