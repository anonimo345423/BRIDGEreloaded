<?php
$h3 = "Messaggio home studenti";
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
  <script src="../js/jquery.min.js"></script>
  <script src="../js/textarea.js"></script>
  <div class="container-fluid">
    <div class="row">
      <?php
      require_once("../sidebar.php");
      //POST
      if (isset($_POST["modifica"])) {
        if (isset($_POST["messaggio"])) $messaggio = nl2br($_POST["messaggio"]);
        else $messaggio = "";
        $query = "DELETE FROM homestudente";
        $link->query($query);
        $query = "INSERT INTO homestudente (messaggio) values (?)";
        $stmt = $link->prepare($query);
        $stmt->bind_param("s", $messaggio);
        $stmt->execute();
      }

      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">

        <h3 class='mb-5 mt-3'><?php echo $h3 ?></h3>
          <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
            <div class='mb-3'>
              <label for='messaggio' class='form-label'>Messaggio in HTML (tieni conto che andando a capo verrà scritto un &lt;br&gt;
                in html, però altri tag tipo strong li devi scrivere tu)</label>
              <textarea class='form-control' id='messaggio' rows='10' name='messaggio'><?php
              $query = "SELECT messaggio FROM homestudente";
              $result = $link->query($query);
              $row = $result->fetch_assoc();
              if (!empty($row)) extract($row, EXTR_OVERWRITE);
              if (!empty($messaggio)) {
                $messaggio = str_replace("<br>", "", $messaggio);
                $messaggio = str_replace("<br />", "", $messaggio);
                echo $messaggio;
              }
              ?></textarea>
            </div>
            <div class=''>
              <input class='btn btn-primary' type='submit' name='modifica' value='Modifica' />
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>

</html>