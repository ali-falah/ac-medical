<?php

require_once 'php_action/db_connect.php';
unset($_SESSION['user_type']);
header('location:'.$url.'login.php');		
?>