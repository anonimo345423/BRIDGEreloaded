<?php
$h3 = "";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || $permesso != 4) {
  header("Location: ../login.php");
  exit();
}
$username = $_SESSION['username'];
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
          contenuto accanto sidebar
        </div>
      </div>
    </div>
  </div>
</body>

</html>