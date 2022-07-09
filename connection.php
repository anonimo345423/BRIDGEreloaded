<?php

//DATI DA MODIFICARE
$host="localhost";
$user="root";
$pswDB="";
$db="bridge";
//DATI DA MODIFICARE

$link = mysqli_connect($host, $user, $pswDB, $db);
if (mysqli_connect_errno()) {
    echo "<p style=\"color:white;\">Errore di connessione al database.\n".mysqli_connect_error()."</p>";
    exit;
}
?>
