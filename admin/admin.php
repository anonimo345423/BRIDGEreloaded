<?php
$h3 = "Home admin";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || $permesso != 4) {
  header("Location: ../login.php");
  exit();
}
?>
<html>

<body>
  <div class="container-fluid">
    <div class="row">
      <?php
      require_once("../sidebar.php");
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">
          
          <h3 class='mb-5 mt-3'><?php echo $h3 ?></h3>          <p>Hai a disposizione tutte le tue funzioni nel men&ugrave; di sinistra</p>
        </div>
      </div>
    </div>
  </div>
</body>

</html>