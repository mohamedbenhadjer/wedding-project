<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('POST only', 405);
verifyCsrf();
$couple = requireCouple();
$weddingId = $couple['wedding_id'];

$rows = [];

// Single manual-add path
if (!empty($_POST['manual'])) {
  $name  = trim($_POST['name']  ?? '');
  $email = trim($_POST['email'] ?? '');
  if ($name === '') jsonError('Name is required');
  $rows[] = [$name, $email];
} elseif (!empty($_FILES['csv']['tmp_name'])) {
  $fh = fopen($_FILES['csv']['tmp_name'], 'r');
  if (!$fh) jsonError('Could not read CSV');
  $first = true;
  while (($line = fgetcsv($fh)) !== false) {
    if (!$line || count(array_filter($line, 'strlen')) === 0) continue;
    $a = trim($line[0] ?? '');
    $b = trim($line[1] ?? '');
    if ($first) {
      $first = false;
      if (strcasecmp($a, 'name') === 0 && strcasecmp($b, 'email') === 0) continue;
    }
    if ($a === '') continue;
    $rows[] = [$a, $b];
  }
  fclose($fh);
} else {
  jsonError('No CSV or manual row provided');
}

if (!$rows) jsonError('CSV was empty');

$pdo = db();
$ins = $pdo->prepare(
  'INSERT INTO guests (username,password_hash,password_plain,name,email,wedding_id) VALUES (?,?,?,?,?,?)'
);

$created = [];
$pdo->beginTransaction();
try {
  foreach ($rows as [$name, $email]) {
    $username = uniqueUsername($name);
    $plain    = genPassword();
    $hash     = password_hash($plain, PASSWORD_DEFAULT);
    $ins->execute([$username, $hash, $plain, $name, $email ?: null, $weddingId]);
    $created[] = [
      'username' => $username,
      'password' => $plain,
      'name'     => $name,
      'email'    => $email,
    ];
  }
  $pdo->commit();
} catch (Throwable $e) {
  $pdo->rollBack();
  jsonError('Import failed: ' . $e->getMessage(), 500);
}

jsonResponse(['ok' => true, 'created' => $created]);
