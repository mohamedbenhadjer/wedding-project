<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('POST only', 405);
verifyCsrf();
$couple = requireCouple();

$username = $_POST['username'] ?? '';
if ($username === '') jsonError('Missing username');

$pdo = db();
$stmt = $pdo->prepare('SELECT wedding_id FROM guests WHERE username = ?');
$stmt->execute([$username]);
$g = $stmt->fetch();
if (!$g || $g['wedding_id'] !== $couple['wedding_id']) jsonError('Not allowed', 403);

$pdo->prepare('DELETE FROM guests WHERE username = ?')->execute([$username]);

jsonResponse(['ok' => true]);
