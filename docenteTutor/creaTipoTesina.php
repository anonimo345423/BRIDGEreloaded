<?php
$h3 = "Crea tipo tesina";
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
        $titolo = htmlspecialchars($_POST["titolo"], ENT_QUOTES);
        if (!empty($_POST["mesiScadenza"])) $mesiScadenza = $_POST["mesiScadenza"];
        else $mesiScadenza = 0;
        if (!empty($_POST["corso"])) $extra = $_POST["corso"];
        else $extra = "null";
        $query = "INSERT INTO tipitesine(titolo,idTipoCorso,mesiScadenza,extra,testo) VALUES('$titolo',$idTipoCorso,$mesiScadenza,$extra,'$testo')";

        try {
          $result=$link->query($query);
          if($result)$msgFlag = 1;
          else $msgFlag=2;
        }
        //se fallisce inserimento, primary key violata:
        catch (exception $e) {
          $msgFlag = 2;
        }
      }
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">
          <?php
          //CHECK che tesine siano attive nel corso tramite maxStudentiTesine>0
          $query = "SELECT maxStudentiTesine
      FROM tipicorsi
      where idTipoCorso=$idTipoCorso";
          $result = $link->query($query);
          $row = $result->fetch_assoc();
          if ($row["maxStudentiTesine"] == 0) {
            echo ("<p>Tesine non presenti in questo corso</p>");
            exit();
          }
          if ($msgFlag == 1) echo '<div class="alert alert-success" role="alert">
          Tipo di tesina inserita con successo
         </div><br>';
          if ($msgFlag == 2) echo '<div class="alert alert-danger" role="alert">
         Errore nell\'inserimento della tesina, occhio che non esista gi&agrave; una tesina con titolo uguale
        </div><br>';
          ?>
          <h2 class='text-center'>Docente</h2>
          <h3 class='mb-5 mt-3'><?php echo $h3 ?></h3>          <form action="<?php echo "$self?idTipoCorso=$idTipoCorso" ?>" method="post">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="titolo" name="titolo" placeholder="Titolo" required />
              <label for="titolo">Titolo</label>
            </div><br>
            <p>Se vuoi che il tipo di tesina sia solo per uno specifico corso invece che tutti i corsi di quel tipo (per esempio solo per l'anno 2021-2022 di un corso)
              selezionalo qui sotto, altrimenti lascia vuoto
            </p>
            <div class="mb-3">
              <label for="extra">Seleziona corso specifico (facoltativo, leggi sopra)</label>
              <select class="form-select py-2" id="corso" name="corso">
                <option selected></option>
                <?php
                //corso specifico
                $query = "SELECT anno, nomeCorso, idCorso
                FROM corsi c
                INNER JOIN tipicorsi t on t.idTipoCorso=c.idTipoCorso
                WHERE t.idTipoCorso=$idTipoCorso";
                $result = $link->query($query);
                while ($row = $result->fetch_assoc()) {
                  extract($row, EXTR_OVERWRITE);
                  $nomeCorso = str_replace("_"," ",$nomeCorso);
                  $corso = $nomeCorso . " " . $anno;
                  echo ("<option value='$idCorso'>$corso</option>");
                }

                ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="testo" class="form-label">Testo</label>
              <textarea class="form-control" id="testo" name="testo" rows="10" required autofocus></textarea>
            </div>

            <div class="form-floating mb-3">
              <input type="number" class="form-control" id="mesiScadenza" name="mesiScadenza" placeholder="Scade dopo quanti mesi? (facoltativo)" />
              <label for="mesiScadenza">Scade dopo quanti mesi? (facoltativo)</label>
            </div><br>

            <div>
              <input class="btn btn-primary" type="submit" name="crea" value="Crea" />
              <br><br>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</body>

</html>