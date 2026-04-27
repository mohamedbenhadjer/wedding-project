<?php
require_once __DIR__ . '/db.php';

function genId(int $len = 8): string {
  $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
  $out = '';
  for ($i = 0; $i < $len; $i++) $out .= $chars[random_int(0, strlen($chars) - 1)];
  return $out;
}

function genUsername(string $name): string {
  $base = strtolower(preg_replace('/[^a-z]/i', '', $name));
  $base = substr($base, 0, 8);
  if ($base === '') $base = 'guest';
  return $base . random_int(100, 999);
}

function uniqueUsername(string $name): string {
  $pdo = db();
  $stmt = $pdo->prepare('SELECT 1 FROM guests WHERE username = ?');
  for ($i = 0; $i < 30; $i++) {
    $candidate = genUsername($name);
    $stmt->execute([$candidate]);
    if (!$stmt->fetchColumn()) return $candidate;
  }
  return $name . '_' . bin2hex(random_bytes(3));
}

function uniqueWeddingId(): string {
  $pdo = db();
  $stmt = $pdo->prepare('SELECT 1 FROM weddings WHERE id = ?');
  for ($i = 0; $i < 30; $i++) {
    $id = genId(8);
    $stmt->execute([$id]);
    if (!$stmt->fetchColumn()) return $id;
  }
  throw new RuntimeException('Could not allocate wedding id');
}

function genPassword(): string {
  $chars = 'abcdefhjkmnpqrstuvwxyz23456789';
  $out = '';
  for ($i = 0; $i < 8; $i++) $out .= $chars[random_int(0, strlen($chars) - 1)];
  return $out;
}

function h(?string $s): string {
  return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
  header('Location: ' . $url);
  exit;
}

function jsonResponse($data, int $code = 200): void {
  http_response_code($code);
  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}

function jsonError(string $msg, int $code = 400): void {
  jsonResponse(['ok' => false, 'error' => $msg], $code);
}

function daysUntil(string $date): int {
  $d1 = new DateTime('today');
  $d2 = new DateTime($date);
  return (int) $d1->diff($d2)->format('%r%a');
}
