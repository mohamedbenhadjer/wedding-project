<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('POST only', 405);
verifyCsrf();
$couple = requireCouple();

$name1 = trim($_POST['name1'] ?? '');
$name2 = trim($_POST['name2'] ?? '');
$date  = trim($_POST['date']  ?? '');
$venue = trim($_POST['venue'] ?? '');
$card  = trim($_POST['card_style'] ?? '');

if ($name1 === '' || $name2 === '' || $date === '') jsonError('Missing required fields');
if (!in_array($card, ['dark','cream','rose','sage',''], true)) $card = '';

$pdo = db();
if ($card !== '') {
  $pdo->prepare('UPDATE weddings SET name1=?, name2=?, wedding_date=?, venue=?, card_style=? WHERE id=?')
      ->execute([$name1, $name2, $date, $venue ?: null, $card, $couple['wedding_id']]);
} else {
  $pdo->prepare('UPDATE weddings SET name1=?, name2=?, wedding_date=?, venue=? WHERE id=?')
      ->execute([$name1, $name2, $date, $venue ?: null, $couple['wedding_id']]);
}

// Update session display name
$_SESSION['couple']['name1'] = $name1;
$_SESSION['couple']['name2'] = $name2;

jsonResponse(['ok' => true]);
