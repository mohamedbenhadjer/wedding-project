<?php
header('Content-Type: application/json');
session_set_cookie_params(['lifetime' => 0]);
session_start();
echo "ok";
