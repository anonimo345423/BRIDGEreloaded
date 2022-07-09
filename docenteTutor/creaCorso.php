<?php
$h3 = "Crea corso";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || $permesso != 3) {
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
      //GESTIONE POST
      if (isset($_POST["crea"])) {
        $idTipoCorso = $_POST["idTipoCorso"];
        if (isset($_POST["anno"])) $anno = $_POST["anno"];
        else $anno = "";
        if (isset($_POST["istruzioni"])) $istruzioni = $_POST["istruzioni"];
        else $istruzioni = null;
        $query = "INSERT into corsi(idTipoCorso,anno,istruzioni) VALUES ($idTipoCorso,'$anno','$istruzioni')";
        $result = $link->query($query);
      }
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">
          <?php if (isset($_POST["crea"]) && $result) echo "<div class='alert alert-success' role='alert'>
            Corso creato!
            </div>";
          else if (isset($_POST["crea"])) echo "<div class='alert alert-danger' role='alert'>
            Corso non creato, probabilmente esiste già un corso per quell'anno!
            </div>";
          ?>
          <h2 class='text-center'>Docente</h2>
          <h3 class="mb-5"><?php echo $h3 ?></h3>
          <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <label for="nomeAccount">Seleziona tipo di corso</label>
            <select class="form-select mb-4" id="idTipoCorso" name="idTipoCorso" required>
              <option selected disabled style="display:none" value=""></option>
              <?php
              //qui ci sono i tipoCorsi che ha il docente
              $query = "SELECT idTipoCorso, nomeCorso
              FROM tipicorsi
              WHERE docente='$username'
              ";
              $result = $link->query($query);
              while ($row = $result->fetch_assoc()) {
                extract($row, EXTR_OVERWRITE);
                $nomeCorso = str_replace("_"," ",$nomeCorso);
                echo ("<option value='$idTipoCorso'>$nomeCorso</option>");
              }
              ?>
            </select>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="anno" name="anno" placeholder="Anno (es. 2021-2022)" />
              <label for="anno">Anno (es. 2021-2022)</label>
            </div>

            <div class='mb-3'>
              <label for='istruzioni' class='form-label'>Istruzioni per questo corso</label>
              <textarea class='form-control' id='istruzioni' rows='10' name='istruzioni'></textarea>
            </div>

            <div>
              <input class="btn btn-primary" type="submit" name="crea" value="Crea" />
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</body>

</html>