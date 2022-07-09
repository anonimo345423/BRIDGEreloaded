<?php
$h3 = "Assegna tesine";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || ($permesso != 2 && $permesso != 3)) {
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
      if (!isset($_GET["idCorso"]) || !isset($_GET["idTipoTesina"]) || !isset($_GET["idTipoCorso"])) { //va in selezioneCorso a prendere idCorso, poi in selezioneTipiTesineDisponibili a prendere idTipoTesina fra quelle non prese in quel corso
        header("Location: selezioneCorso.php?to=selezioneTipiTesineDisponibili.php&text=$h3");
        exit();
      }
      $idTipoCorso = $_GET["idTipoCorso"];
      $idTipoTesina = $_GET["idTipoTesina"];
      $idCorso = $_GET["idCorso"];
      //maxStudentiTesine, nomeAccount, numeroAccount
      $query = "SELECT maxStudentiTesine, nomeAccount, numeroAccount
      FROM tipicorsi
      WHERE idTipoCorso=$idTipoCorso";
      $result = $link->query($query);
      $row = $result->fetch_assoc();
      extract($row, EXTR_OVERWRITE);
      //POST
      if (isset($_POST["assegna"])) {
        //trovo scadenza come data odierna+tot mesi, dove tot mesi sono quelli in mesiScadenza della tabella tipitesine
        $query = "SELECT mesiScadenza
        FROM tipitesine
        WHERE idTipoTesina=?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("i", $idTipoTesina);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $mesiScadenza = $row["mesiScadenza"];

        if ($mesiScadenza != 0) { //creo una scadenza solo se mesiScadenza!=0, ossia se c'è una scadenza impostata ad tipitesine
          $date = new DateTime('now');
          $date->modify("+$mesiScadenza month");
          $scadenza = $date->format('Y-m-d');
        } else $scadenza = null;
        //inserisco prima in tesine
        $query = "INSERT INTO tesine(idTipoTesina,idCorso,scadenza)
        VALUES(?,?,?)";
        $stmt = $link->prepare($query);
        $stmt->bind_param("iis", $idTipoTesina, $idCorso, $scadenza);
        $stmt->execute();
        $result = $stmt->get_result();
        //trovo idTesina per query successiva
        $query = "SELECT idTesina
          FROM tesine
          WHERE idTipoTesina=? AND idCorso=?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("ii", $idTipoTesina, $idCorso);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $idTesina = $row["idTesina"];
        //poi inserisco gli studenti
        $i = 1;
        while ($i <= $maxStudentiTesine) {
          $studente = $_POST["studenti$i"];
          if (!empty($_POST["account$i"])) $account = $_POST["account$i"];
          else $account = null;
          if (!empty($studente)) {
            $query = "INSERT INTO studentitesina(idTesina,username,account)
            VALUES (?,?,?)";
            $stmt = $link->prepare($query);
            $stmt->bind_param("iss", $idTesina, $studente, $account);
            $resultCheck = $stmt->execute();
          }
          $i++;
        }
        //arrivato a questo punto, se resultCheck è false vuol dire che non sono stati inseriti studenti per qualche errore, elimino allora la tesina inserita.
        if (!isset($resultCheck) || !$resultCheck) {
          $query = "DELETE FROM tesine WHERE idTesina=$idTesina";
          $result = $link->query($query);
        }

        if ($result == TRUE) header("Location: selezioneTipiTesineDisponibili.php?idCorso=$idCorso&msg=1"); //success
        else header("Location: selezioneTipiTesineDisponibili..php?idCorso=$idCorso&msg=2"); //fail
        exit();
      }
      //FINE POST

      //trovo il numero di categorie del corso
      $query = "SELECT categorie
      FROM tipicorsi t
      INNER JOIN corsi c on c.idTipoCorso=t.idTipoCorso
      WHERE idCorso=?";
      $stmt = $link->prepare($query);
      $stmt->bind_param("i", $idCorso);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();
      $categorie = $row["categorie"];
      $arrCategorie = explode("|", $categorie);
      $numCategorie = count($arrCategorie); //numero di categorie, e quindi di esercizi da avere accettati per accedere alle tesine

      //Trovo la lista di studenti con *numCategorie* esercizi fatti
      $listaStudenti = "<option selected></option>"; //la lista sarà fatta da tutte le <option> per la select, inizio con la option vuota
      $query = "SELECT COUNT(idEsercizio) num, nome, cognome, e.username
        FROM esercizi e
        INNER JOIN tipiesercizi t on e.idTipoEsercizio=t.idTipoEsercizio
        INNER JOIN login l on l.username=e.username
        WHERE stato=3 AND obbligatorio=1 AND idCorso=? AND l.username NOT IN(
          SELECT username
          FROM studentitesina s
          INNER JOIN tesine t on t.idTesina=s.idTesina
          WHERE idCorso=?
          )
        group by l.username"; //prendo il numero di esercizi obbligatori e accettati fatti dagli studenti e i relativi username
      //e che non hanno già una tesina (not in)
      $stmt = $link->prepare($query);
      $stmt->bind_param("ii", $idCorso, $idCorso);
      $stmt->execute();
      $result = $stmt->get_result();
      while ($row = $result->fetch_assoc()) {
        extract($row, EXTR_OVERWRITE);
        if ($num >= $numCategorie) $listaStudenti = $listaStudenti . "<option value='$username'>$nome $cognome $username</option>";
      }
      //CREO la lista degli account disponibili, partendo dai parametri presi prima nomeAccount e numeroAccount e togliendoci gli account già assegnati in studentitesina
      //lista totale
      $AccountTotali = [];
      $i = 0;
      while ($i < $numeroAccount) {
        $num = $i + 1;
        $AccountTotali[$i] = "$nomeAccount$num"; //quindi avremo un array tipo lweb1,lweb2 ecc...
        $i++;
      }
      //faccio array con quelli già assegnati
      $AccountPresi = [];
      $i = 0;
      $query = "SELECT account
        FROM studentitesina s";
      $result = $link->query($query);
      while ($row = $result->fetch_assoc()) {
        extract($row, EXTR_OVERWRITE);
        $AccountPresi[$i] = "$account";
        $i++;
      }
      //faccio array_diff ossia differenza fra array
      $accountDisponibili = array_diff($AccountTotali, $AccountPresi);
      $listaAccount = "<option></option>";
      foreach ($accountDisponibili as $accountDisponibile) {
        $listaAccount = $listaAccount . "<option value='$accountDisponibile'>$accountDisponibile</option>";
      }
      $query="SELECT nomeCorso,anno FROM corsi c INNER JOIN tipicorsi t on t.idTipoCorso=c.idTipoCorso WHERE idCorso=$idCorso";
      $result = $link->query($query);
      $row = $result->fetch_assoc();
      extract($row, EXTR_OVERWRITE);
      $nomeCorso=str_replace("_"," ",$nomeCorso);
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">
          <h2 class='text-center'>Docente</h2>
          <h3 class='mb-5 mt-3'><?php echo "$h3 ($nomeCorso $anno)" ?></h3>           <p class='mb-5'>Seleziona gli studenti a cui vuoi assegnare la tesina (minimo 1, massimo <?php echo $maxStudentiTesine ?>)</p>
          <form action="<?php $self = $_SERVER['PHP_SELF'];
                        echo "$self?idTipoTesina=$idTipoTesina&idCorso=$idCorso&idTipoCorso=$idTipoCorso";
                        ?>" method="post">

            <?php
            $i = 1;
            while ($i <= $maxStudentiTesine) {
              echo ("<div class='row'>");
              echo ("<div class='col-9'>
              <select class='form-select py-2 my-3' name = 'studenti$i'>
              ");
              echo ($listaStudenti);
              echo ("</select>
                </div>");
              //sopra selezione studenti, sotto selezione account
              echo ("<div class='col-3'>
          <select class='form-select py-2 my-3' name = 'account$i'>");
              echo ($listaAccount);
              echo ("</select>
          </div>
          </div>");
              $i++;
            }
            ?>
            <input class="btn btn-primary" type="submit" name="assegna" value="Assegna" />
          </form>
        </div>
      </div>
    </div>
  </div>
</body>

</html>