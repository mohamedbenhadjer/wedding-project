<?php
require_once __DIR__ . '/../includes/auth.php';
logoutAll();
redirect('guest-login.php');
