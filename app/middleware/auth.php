<?php
// Jangan panggil session_start(); sudah di-handle oleh config/init.php

if (!isset($_SESSION['user'])) {
    if (isset($BASE_URL)) {
        header('Location: ' . $BASE_URL . 'auth/login.php');
    } else {
        header('Location: auth/login.php');
    }
    exit;
}
