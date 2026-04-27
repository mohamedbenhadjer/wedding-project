<?php
require 'includes/db.php';
require 'includes/helpers.php';
$pdo = db();
$pdo->exec("INSERT IGNORE INTO couples (email, password, name1, name2, wedding_id) VALUES ('test@example.com', 'hash', 'A', 'B', 'W1234567')");
$pdo->exec("INSERT IGNORE INTO weddings (id, couple_email, name1, name2, wedding_date) VALUES ('W1234567', 'test@example.com', 'A', 'B', '2026-05-01')");

$name = 'John Doe';
$email = 'john@example.com';
$weddingId = 'W1234567';

$username = uniqueUsername($name);
$plain = genPassword();
$hash = password_hash($plain, PASSWORD_DEFAULT);

$ins = $pdo->prepare('INSERT INTO guests (username,password_hash,password_plain,name,email,wedding_id) VALUES (?,?,?,?,?,?)');
try {
    $ins->execute([$username, $hash, $plain, $name, $email, $weddingId]);
    echo "Success!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
