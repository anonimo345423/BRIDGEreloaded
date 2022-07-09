<?php
require_once("connection.php");
ob_start();
session_start();
echo('<!DOCTYPE html>
<html>   
<head>
    <meta id="testViewport" name="viewport" content="width=device-width, initial-scale=1.0">');
    ?>
    	<script> if (screen.width < 768) { 	
var mvp = document.getElementById('testViewport');
mvp.setAttribute('content','width=768'); 
} </script>
<?php
echo('
    <meta charset="UTF-8">
    <link rel="icon" href="../favicon.ico">
    <link rel="stylesheet" href="../css/stileBootstrap.css">
    <link rel="stylesheet" href="../css/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">');
if(!empty($h3)) echo("<title>Bridge: $h3</title>");
else echo ("<title>Bridge</title>");
echo('<script src="../js/bootstrap.min.js"></script>');
echo('</head>');
?>