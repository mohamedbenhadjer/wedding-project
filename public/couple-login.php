<?php
// Alias — reuse the signup page in login mode.
$_GET['mode'] = 'login';
require __DIR__ . '/couple-signup.php';
