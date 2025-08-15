<?php
require_once '../init.php';
require_once '../controllers/AuthController.php';

$auth = new AuthController($pdo);
$auth->logout();
