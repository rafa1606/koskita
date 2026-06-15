<?php
require_once '../config/db.php';
$_SESSION = [];
session_destroy();
redirect('auth/login.php');
