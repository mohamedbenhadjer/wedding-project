<?php
require_once __DIR__ . '/helpers.php';

function startSession(): void {
  if (session_status() === PHP_SESSION_ACTIVE) return;
  session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']),
  ]);
  session_start();
}

function setCoupleSession(array $data): void {
  startSession();
  $_SESSION['couple'] = $data;
  unset($_SESSION['guest']);
}

function setGuestSession(array $data): void {
  startSession();
  $_SESSION['guest'] = $data;
  unset($_SESSION['couple']);
}

function currentCouple(): ?array {
  startSession();
  return $_SESSION['couple'] ?? null;
}

function currentGuest(): ?array {
  startSession();
  return $_SESSION['guest'] ?? null;
}

function requireCouple(): array {
  $c = currentCouple();
  if (!$c) redirect('couple-login.php');
  return $c;
}

function requireGuest(): array {
  $g = currentGuest();
  if (!$g) redirect('guest-login.php');
  return $g;
}

function logoutAll(): void {
  startSession();
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $p['path'], $p['domain'], $p['secure'], $p['httponly']);
  }
  session_destroy();
}

function currentWedding(): ?array {
  $c = currentCouple();
  if (!$c) return null;
  $stmt = db()->prepare('SELECT * FROM weddings WHERE id = ?');
  $stmt->execute([$c['wedding_id']]);
  $w = $stmt->fetch();
  return $w ?: null;
}
