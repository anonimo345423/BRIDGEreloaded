<?php
$h3 = "Gestisci tipi tesina";
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
      if (!isset($_GET["idTipoTesina"])) {
        $self = basename(__FILE__, '.php');
        header("Location: selezioneTipoCorso.php?to=selezioneTipoTesine.php&text=&text=$h3");
        exit();
      } else {
        $idTipoTesina = $_GET["idTipoTesina"];
      }
      $self = htmlspecialchars($_SERVER['PHP_SELF']);


      //DATI DA idTipoTesina
      $query = "SELECT * FROM tipitesine WHERE idTipoTesina=$idTipoTesina";
      $result = $link->query($query);
      $row = $result->fetch_assoc();
      if (!empty($row)) extract($row, EXTR_OVERWRITE);
      $testo = str_replace("<br />", "", $testo); //tolgo i br dal testo perchè dovrà essere visualizzato nella textarea
      //DA EXTRA OTTENGO la variabile corso e idCorsoAttuale, che saranno usate nell'opzione di default della select per il corso specifico
      if (!empty($extra)) {
        $query = "SELECT anno, nomeCorso, idCorso idCorsoAttuale
        FROM corsi c
        INNER JOIN tipicorsi t on t.idTipoCorso=c.idTipoCorso
        WHERE c.idCorso=$extra";
        $result = $link->query($query);
        $row = $result->fetch_assoc();
        extract($row, EXTR_OVERWRITE);
        $nomeCorso = str_replace("_"," ",$nomeCorso);
        $corso = $nomeCorso . " " . $anno;
      } else {
        $idCorsoAttuale = "";
        $corso = "";
        //ossia se non è settato un "extra" lascio tutto vuoto
      }

      //POST
      if (isset($_POST["modifica"])) {
        $titolo = htmlspecialchars($_POST["titolo"], ENT_QUOTES);
        $testo = nl2br(htmlspecialchars($_POST["testo"], ENT_QUOTES));
        echo ($testo);
        if (!empty($_POST["corso"])) $extra = $_POST["corso"];
        else $extra = "null";
        if (!empty($_POST["mesiScadenza"])) $mesiScadenza = $_POST["mesiScadenza"];
        else $mesiScadenza = 0;

        $query = "UPDATE tipitesine
        SET titolo='$titolo', testo='$testo', extra=$extra, mesiScadenza=$mesiScadenza
        WHERE idTipoTesina=$idTipoTesina";
        $result = $link->query($query);
        if ($result == true) $msgFlag = 1;
        else $msgFlag = 2;
        $testo = str_replace("<br />", "", $testo); //tolgo di nuovo i br dal testo perchè dovrà essere visualizzato nella textarea
        header("Location: $self?idTipoTesina=$idTipoTesina&idTipoCorso=$idTipoCorso&msgFlag=$msgFlag");
        exit();
      }
      //POST 2
      if (isset($_POST['elimina']) && isset($_POST['checkElimina'])) {
        //ci sono 2 casi, se nessun utente ha svolto tesine di questo tipo, lo elimino,
        //nell'altro caso metto disabilitato=1 così nessuno potrà più sceglierla ma resta comunque presente

        //prima applico caso 2 a prescindere:
        $query="UPDATE tipitesine SET disabilitato=1 WHERE idTipoTesina=?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("i", $idTipoTesina);
        $stmt->execute();
        //e poi caso 1: controllo che non ci siano tipitesine che non hanno più tesine di nessuno studente:
        $query="DELETE FROM tipitesine WHERE disabilitato=1 AND idTipoTesina NOT IN (SELECT idTipoTesina FROM tesine)";
        $link->query($query);
        header("Location: selezioneTipoTesine.php?idTipoCorso=$idTipoCorso&delete=1"); //lo porto alla pagina di selezione con messaggio conferma
        exit();
      }


      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">

          <?php if (isset($_GET["msgFlag"]) && $_GET["msgFlag"] == 1) echo '<div class="alert alert-success" role="alert">
          Tipo di tesina modificata con successo
         </div><br>';
          if (isset($_GET["msgFlag"]) && $_GET["msgFlag"] == 2) echo '<div class="alert alert-danger" role="alert">
         Errore nella modifica della tesina
        </div><br>';
        $query="SELECT nomeCorso FROM tipicorsi WHERE idTipoCorso=$idTipoCorso";
          $result = $link->query($query);
          $row = $result->fetch_assoc();
          extract($row, EXTR_OVERWRITE);
          $nomeCorso=str_replace("_"," ",$nomeCorso);
          ?>
          <h2 class='text-center'>Docente</h2>
          <h3 class='mb-5 mt-3'><?php echo "$h3 ($nomeCorso)"; ?></h3>          <form action="<?php echo "$self?idTipoTesina=$idTipoTesina&idTipoCorso=$idTipoCorso" ?>" method="post">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="titolo" name="titolo" placeholder="Titolo" value="<?php echo $titolo; ?>" required />
              <label for="titolo">Titolo</label>
            </div><br>
            <p>Se vuoi che il tipo di tesina sia solo per uno specifico corso invece che tutti i corsi di quel tipo (per esempio solo per l'anno 2021-2022 di un corso)
              selezionalo qui sotto, altrimenti lascia vuoto
            </p>
            <div class="mb-3">
              <label for="extra">Seleziona corso specifico (facoltativo, leggi sopra)</label>
              <select class="form-select py-2" id="corso" name="corso">
                <option selected style="display:none" value="<?php echo $idCorsoAttuale ?>"><?php echo $corso; ?></option>

                <?php
                if ($idCorsoAttuale != "") echo ('<option value=""></option>'); //cioè se attualmente il corso specifico dalla tesina non è vuoto, dai l'opzione di rimuoverlo
                $query = "SELECT anno, nomeCorso, idCorso
                FROM corsi c
                INNER JOIN tipicorsi t on t.idTipoCorso=c.idTipoCorso
                WHERE t.idTipoCorso=$idTipoCorso";
                $result = $link->query($query);
                while ($row = $result->fetch_assoc()) {
                  extract($row, EXTR_OVERWRITE);
                  $corso = $nomeCorso . " " . $anno;
                  echo ("<option value='$idCorso'>$corso</option>");
                }

                ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="testo" class="form-label">Testo</label>
              <textarea class="form-control" id="testo" name="testo" rows="10" required><?php echo $testo; ?></textarea>
            </div>

            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="mesiScadenza" name="mesiScadenza" placeholder="Scade dopo quanti mesi? (facoltativo)" value="<?php if ($mesiScadenza > 0) echo $mesiScadenza; ?>" />
              <label for="anno">Scade dopo quanti mesi? (facoltativo, leggi sopra)</label>
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