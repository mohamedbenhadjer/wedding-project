<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('POST only', 405);
verifyCsrf();
$guest = requireGuest();

$stmt = db()->prepare('SELECT wedding_date FROM weddings WHERE id = ?');
$stmt->execute([$guest['wedding_id']]);
$w = $stmt->fetch();
if (!$w) jsonError('Wedding not found', 404);
if (strtotime($w['wedding_date']) > strtotime('today')) {
  jsonError('Uploads open after the wedding date', 403);
}

if (empty($_FILES['photos']) || !is_array($_FILES['photos']['name'])) {
  jsonError('No photos uploaded');
}

$message = trim($_POST['message'] ?? '');
if (mb_strlen($message) > 300) $message = mb_substr($message, 0, 300);

$targetDir = UPLOADS_DIR . '/' . $guest['wedding_id'] . '/' . $guest['username'];
if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
  jsonError('Could not create upload folder', 500);
}

$saved = [];
$files = $_FILES['photos'];
$count = count($files['name']);
if ($count > 10) jsonError('Maximum 10 photos per upload');

$finfo = new finfo(FILEINFO_MIME_TYPE);
$pdo   = db();
$ins   = $pdo->prepare('INSERT INTO photos (id,wedding_id,guest_username,file_path,message) VALUES (?,?,?,?,?)');

for ($i = 0; $i < $count; $i++) {
  if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
  if ($files['size'][$i] > MAX_UPLOAD_BYTES) continue;

  $mime = $finfo->file($files['tmp_name'][$i]);
  if (!isset(ALLOWED_MIME[$mime])) continue;
  $ext = ALLOWED_MIME[$mime];

  $id  = bin2hex(random_bytes(6));
  $rel = $guest['wedding_id'] . '/' . $guest['username'] . '/' . $id . '.' . $ext;
  $dst = UPLOADS_DIR . '/' . $rel;
  if (!move_uploaded_file($files['tmp_name'][$i], $dst)) continue;

  $ins->execute([$id, $guest['wedding_id'], $guest['username'], $rel, $message ?: null]);
  $saved[] = ['id' => $id, 'url' => UPLOADS_URL . '/' . $rel];
}

if (!$saved) jsonError('No valid images could be saved');
jsonResponse(['ok' => true, 'saved' => $saved]);
