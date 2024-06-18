<?php
session_start();

$cartCount = isset($_SESSION['selectedExtras']) ? count($_SESSION['selectedExtras']) : 0;
echo $cartCount;
?>