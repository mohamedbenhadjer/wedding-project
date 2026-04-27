<?php
require_once __DIR__ . '/auth.php';

function csrfToken(): string {
  startSession();
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}

function csrfField(): string {
  return '<input type="hidden" name="_csrf" value="' . h(csrfToken()) . '">';
}

function verifyCsrf(): void {
  startSession();
  $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF'] ?? '';
  if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
    http_response_code(403);
    if (($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json'
        || strpos($_SERVER['CONTENT_TYPE'] ?? '', 'json') !== false) {
      jsonError('Invalid CSRF token', 403);
    }
    exit('Invalid CSRF token');
  }
}
