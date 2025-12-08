<?php require_once '../init.php';
session_unset();
session_destroy();
header("Location: ../login.php");
exit();
