<?php
// Mocking a web request internally:
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['manual'] = '1';
$_POST['name'] = 'Test Guest';
$_POST['email'] = 'test@example.com';
$_POST['_csrf'] = 'valid';

// Need to mock session
session_start();
$_SESSION['csrf'] = 'valid';
$_SESSION['couple'] = ['wedding_id' => 'W1234567']; // we created this in the previous test

ob_start();
try {
    require '/home/mohamed/wedding-project/public/api/import-csv.php';
} catch (Throwable $e) {
    echo "Exception: " . $e->getMessage();
}
$output = ob_get_clean();
echo "Output:\n" . $output;
