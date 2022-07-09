<?php
$h3 = "Crea tipo esercizio";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || $permesso != 3) {
  header("Location: ../login.php");
  exit();
}
$username = $_SESSION['username'];
$msgFlag = 0;
?>
<html>

<body>
  <script src="../js/jquery.min.js"></script>
  <script src="../js/textarea.js"></script>
  <div class="container-fluid">
    <div class="row">
      <?php
      require_once("../sidebar.php");
      if (!isset($_GET["idTipoCorso"])) {
        $self = basename(__FILE__, '.php');
        header("Location: selezioneTipoCorso.php?to=$self.php");
        exit();
      } else $idTipoCorso = $_GET["idTipoCorso"];
      $self = htmlspecialchars($_SERVER['PHP_SELF']);
      //POST
      if (isset($_POST["crea"])) {
        $testo = nl2br(htmlspecialchars($_POST["testo"], ENT_QUOTES));
        if (isset($_POST["obbligatorio"])) $obbligatorio = 1;
        else $obbligatorio = 0;
        $categoria = $_POST["categoria"];
        $query = "INSERT INTO tipiesercizi(idTipoCorso,obbligatorio,categoria,testo) VALUES($idTipoCorso,$obbligatorio,'$categoria','$testo')";
        $result = $link->query($query);
        if ($result == true) $msgFlag = 1;
        else $msgFlag = 2;
      }
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">
          <?php if ($msgFlag == 1) echo '<div class="alert alert-success" role="alert">
          Tipo di esercizio inserito con successo
         </div><br>';
          if ($msgFlag == 2) echo '<div class="alert alert-danger" role="alert">
         Errore nell\'inserimento dell\'esercizio
        </div><br>';
          ?>
          <h2 class='text-center'>Docente</h2>
          <h3 class='mb-5 mt-3'><?php echo $h3 ?></h3>          <form action="<?php echo "$self?idTipoCorso=$idTipoCorso" ?>" method="post">
            <div class="mb-3">
              <label for="categoria">Seleziona categoria</label>
              <select class="form-select py-2" id="categoria" name="categoria" required>
                <option disabled selected style="display:none"></option>
                <?php

                $query = "SELECT categorie FROM tipicorsi WHERE idTipoCorso=$idTipoCorso";
                $result = $link->query($query);
                $row = $result->fetch_assoc();
                $categorie = $row["categorie"];
                $categorie = explode("|", $categorie); //array da delimiter |
                foreach ($categorie as $categoria) {
                  $categoriaSenzaNumero=substr($categoria, 1);
                  echo ("<option value='$categoria'>$categoriaSenzaNumero</option>");
                }
                ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="testo" class="form-label">Testo</label>
              <textarea class="form-control" id="testo" name="testo" rows="10" required autofocus></textarea>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="obbligatorio" id="obbligatorio">
              <label class="form-check-label" for="obbligatorio">
                Obbligatorio
              </label>
            </div><br>

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