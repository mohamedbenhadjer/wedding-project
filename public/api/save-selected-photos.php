<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('POST only', 405);
verifyCsrf();
$couple = requireCouple();
$weddingId = $couple['wedding_id'];

$ids = json_decode($_POST['photo_ids'] ?? '[]', true);
if (!is_array($ids)) jsonError('Invalid payload');

$pdo = db();
$pdo->beginTransaction();
try {
  $pdo->prepare('DELETE FROM memory_selections WHERE wedding_id = ?')
      ->execute([$weddingId]);

  if ($ids) {
    $check = $pdo->prepare('SELECT 1 FROM photos WHERE id = ? AND wedding_id = ?');
    $ins   = $pdo->prepare('INSERT INTO memory_selections (wedding_id,photo_id,position) VALUES (?,?,?)');
    $pos = 0;
    foreach ($ids as $pid) {
      $check->execute([$pid, $weddingId]);
      if (!$check->fetchColumn()) continue;
      $ins->execute([$weddingId, $pid, $pos++]);
    }
  }
  $pdo->commit();
} catch (Throwable $e) {
  $pdo->rollBack();
  jsonError('Save failed', 500);
}

jsonResponse(['ok' => true, 'count' => count($ids)]);
