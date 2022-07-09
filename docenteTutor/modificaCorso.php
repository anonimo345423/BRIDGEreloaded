<?php
$h3 = "Modifica corso";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || $permesso != 3) {
  header("Location: ../login.php");
  exit();
}
$username = $_SESSION['username'];
if(!isset($_GET["idCorso"])){
  header("Location:selezioneCorso.php?to=modificaCorso.php");
  exit();
}
$idCorso=$_GET["idCorso"];
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
        if (isset($_POST["istruzioni"])) $istruzioni = nl2br($_POST["istruzioni"]);
        else $istruzioni = "";
        $query = "UPDATE corsi set istruzioni=? WHERE idCorso=$idCorso";
        $stmt = $link->prepare($query);
        $stmt->bind_param("s", $istruzioni);
        $stmt->execute();
      }
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">
          <h2 class='text-center'>Docente</h2>
          <h3 class='mb-5 mt-3'><?php echo $h3 ?></h3>
          <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'])."?idCorso=$idCorso" ?>" method="post">
            <div class='mb-3'>
              <label for='istruzioni' class='form-label'>istruzioni in HTML (tieni conto che andando a capo verrà scritto un &lt;br&gt;
                in html, però altri tag tipo strong li devi scrivere tu)</label>
              <textarea class='form-control' id='istruzioni' rows='10' name='istruzioni'><?php
              $query = "SELECT istruzioni FROM corsi WHERE idCorso=$idCorso";
              $result = $link->query($query);
              $row = $result->fetch_assoc();
              if (!empty($row)) extract($row, EXTR_OVERWRITE);
              if (!empty($istruzioni)) {
                $istruzioni = str_replace("<br>", "\n", $istruzioni);
                $istruzioni = str_replace("<br />", "", $istruzioni);
                echo $istruzioni;
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