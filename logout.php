<?php
session_start();
unset($_SESSION);
session_destroy();

header("Location: login.php"); // Dopo il logout si ritorna al login
?>
