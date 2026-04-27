<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/themes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('POST only', 405);
verifyCsrf();
$couple = requireCouple();

$theme = $_POST['theme'] ?? '';
if (!isset(THEMES[$theme])) jsonError('Unknown theme');

$stmt = db()->prepare('UPDATE weddings SET theme = ? WHERE id = ?');
$stmt->execute([$theme, $couple['wedding_id']]);

jsonResponse(['ok' => true, 'theme' => $theme]);
