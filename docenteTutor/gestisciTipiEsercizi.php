<?php
$h3 = "Gestisci tipi esercizio";
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
      if (!isset($_GET["idTipoEsercizio"])) {
        $self = basename(__FILE__, '.php');
        header("Location: selezioneTipoCorso.php?to=selezioneTipoEsercizio.php&text=$h3");
        exit();
      } else {
        $idTipoEsercizio = $_GET["idTipoEsercizio"];
        $idTipoCorso = $_GET["idTipoCorso"];
      }
      $self = htmlspecialchars($_SERVER['PHP_SELF']);
      //DATI DA idTipoEsercizio
      $query = "SELECT * FROM tipiesercizi WHERE idTipoEsercizio=$idTipoEsercizio";
      $result = $link->query($query);
      $row = $result->fetch_assoc();
      if (!empty($row)) extract($row, EXTR_OVERWRITE);
      $categoriaSenzaNumero=substr($categoria, 1);
      $testo = str_replace("<br />", "", $testo); //tolgo i br dal testo perchè dovrà essere visualizzato nella textarea

      //POST
      if (isset($_POST["modifica"])) {
        $testo = nl2br(htmlspecialchars($_POST["testo"], ENT_QUOTES));
        if (isset($_POST["obbligatorio"])) $obbligatorio = 1;
        else $obbligatorio = 0;
        $categoria = $_POST["categoria"];

        $query = "UPDATE tipiesercizi
        SET testo='$testo', categoria='$categoria', obbligatorio=$obbligatorio
        WHERE idTipoEsercizio=$idTipoEsercizio";
        $result = $link->query($query);
        if ($result == true) $msgFlag = 1;
        else $msgFlag = 2;
        header("Location: selezioneTipoEsercizio.php?idTipoCorso=$idTipoCorso&flag=$msgFlag");
        exit();
      }
      //POST 2
      if (isset($_POST['elimina']) && isset($_POST['checkElimina'])) {
        //ci sono 2 casi, se nessun utente ha svolto esercizi di questo tipo, lo elimino,
        //nell'altro caso metto disabilitato=1 così nessuno potrà più sceglierlo ma resta comunque presente

        //prima applico caso 2 a prescindere:
        $query="UPDATE tipiesercizi SET disabilitato=1 WHERE idTipoEsercizio=?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("i", $idTipoEsercizio);
        $stmt->execute();
        //e poi caso 1: controllo che non ci siano tipiesercizi che non hanno più esercizi di nessuno studente:
        $query="DELETE FROM tipiesercizi WHERE disabilitato=1 AND idTipoEsercizio NOT IN (SELECT idTipoEsercizio FROM esercizi)";
        $link->query($query);
        header("Location: selezioneTipoEsercizio.php?idTipoCorso=$idTipoCorso&delete=1"); //lo porto alla pagina di selezione con messaggio conferma
      }
      $query="SELECT nomeCorso FROM tipicorsi WHERE idTipoCorso=$idTipoCorso";
          $result = $link->query($query);
          $row = $result->fetch_assoc();
          extract($row, EXTR_OVERWRITE);
          $nomeCorso=str_replace("_"," ",$nomeCorso);
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">

          
          <h2 class='text-center'>Docente</h2>
          <h3 class='mb-5 mt-3'><?php echo "$h3 ($nomeCorso)";?></h3>          <form action="<?php echo "$self?idTipoEsercizio=$idTipoEsercizio&idTipoCorso=$idTipoCorso" ?>" method="post">
            <div class="mb-3">
              <label for="categoria">Seleziona categoria</label>
              <select class="form-select py-2" id="categoria" name="categoria" required>
                <option selected style="display:none" value="<?php echo $categoria ?>"><?php echo $categoriaSenzaNumero; ?></option>
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
              <textarea class="form-control" id="testo" name="testo" rows="10" required autofocus><?php echo $testo; ?></textarea>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="obbligatorio" id="obbligatorio" <?php if ($obbligatorio == 1) echo "checked" ?>>
              <label class="form-check-label" for="obbligatorio">
                Obbligatorio
              </label>
            </div><br>

            <div class="row">
              <div class="col-2">
                <input class="btn btn-primary" type="submit" name="modifica" value="Modifica" />
              </div>
              <div class="col-9 text-end">
                <input class="form-check-input" type="checkbox" name="checkElimina" id="checkElimina">
                <label for="checkElimina">Conferma Eliminazione</label><br>
              </div>
              <div class="col-1">
                <input class="btn btn-danger" type="submit" name="elimina" value="Elimina" />
              </div>
            </div>
          </form> <br><br>

        </div>
      </div>
    </div>
  </div>
</body>

</html>