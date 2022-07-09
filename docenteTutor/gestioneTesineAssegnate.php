<?php
$h3 = "Modifica assegnazione tesina";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || ($permesso != 2 && $permesso != 3)) {
  header("Location: ../login.php");
  exit();
}
$usernameDocente = $_SESSION['username'];
$msg = 0;
?>
<html>

<body>
  <div class="container-fluid">
    <div class="row">
      <?php
      require_once("../sidebar.php");
      if (!isset($_GET["idCorso"]) || !isset($_GET["idTipoTesina"]) || !isset($_GET["idTipoCorso"])) { //va in selezioneCorso a prendere idCorso, poi in selezioneTipiTesineDisponibili a prendere idTipoTesina fra quelle non prese in quel corso
        header("Location: selezioneCorso.php?to=selezioneTipiTesineDisponibili.php");
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

      //idTesina
      $query = "SELECT idTesina
      FROM tesine
      WHERE idTipoTesina=$idTipoTesina AND idCorso=$idCorso";
      $result = $link->query($query);
      $row = $result->fetch_assoc();
      $idTesina = $row["idTesina"];
      //POST
      if (isset($_POST["checkElimina"]) && isset($_POST["elimina"])) {
        $query = "DELETE FROM tesine WHERE idTesina=$idTesina";
        $result = $link->query($query);
        if ($result) header("Location: selezioneTipiTesineDisponibili.php?idCorso=$idCorso&msg=3");
        else header("Location: selezioneTipiTesineDisponibili.php?idCorso=$idCorso&msg=4");
      }
      if (isset($_POST["modifica"])) {  //la gestisco cosi: se arrivo qui faccio update con idTesina che trovo da idTipoTesine in tesine,
        //poi faccio update della table tesine per scadenza, poi delete di tutto ciò che c'è in studentitesina con idTesina=all'attuale
        //e reinserisco gli studenti che ho ricevuto in post in studentiTestina
        if (!empty($_POST["scadenza"])) $scadenza = $_POST["scadenza"];
        else $scadenza = null;
        //update table tesine
        $query = "UPDATE tesine SET scadenza=? WHERE idTesina=$idTesina";
        $stmt = $link->prepare($query);
        $stmt->bind_param("s", $scadenza);
        $stmt->execute();
        //delete in studentitesina con idTesina corrispondente, li reinserisco tutti dopo
        $query = "DELETE FROM studentitesina WHERE idTesina=$idTesina";
        $result = $link->query($query);
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
            $result = $stmt->execute();
          }
          $i++;
        }
        if ($result) $msg = 1; //success
        else $msg = 2;
        header("Location: gestioneTesineAssegnate.php?idTipoTesina=$idTipoTesina&idCorso=$idCorso&idTipoCorso=$idTipoCorso&msg=$msg");
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
      $listaStudenti = ""; //la lista sarà fatta da tutte le <option> per la select, inizio con la option vuota
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
      $stmt = $link->prepare($query);
      $stmt->bind_param("ii", $idCorso, $idCorso);
      $stmt->execute();
      $result = $stmt->get_result();
      while ($row = $result->fetch_assoc()) {
        extract($row, EXTR_OVERWRITE);
        if ($num == $numCategorie) $listaStudenti = $listaStudenti . "<option value='$username'>$nome $cognome $username</option>";
      } //listaStudenti contiene gli studenti selezionabili
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
      $listaAccount = "";
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
          <h3 class='mb-5 mt-3'><?php echo "$h3 ($nomeCorso $anno)" ?></h3>          <?php
          if (isset($_GET["msg"])) $msg = $_GET["msg"];
          if ($msg == 1) echo '<div class="alert alert-success" role="alert">
      Modifica effettuata
     </div><br>';
          else if ($msg == 2) echo '<div class="alert alert-danger" role="alert">
     Modifica non effettuata
    </div><br>';
          //trovo stato e statiMax
          $query = "SELECT stato, statiTesine statiMax
    FROM tesine t
    INNER JOIN tipitesine tt on t.idTipoTesina=tt.idTipoTesina
    INNER JOIN tipicorsi tc on tt.idTipoCorso=tc.idTipoCorso
    WHERE idTesina=$idTesina";
          $result = $link->query($query);
          $row = $result->fetch_assoc();
          extract($row, EXTR_OVERWRITE);
          $class = ""; //aggiungo colore verde se stato=statoMax
          if (intval($stato)-1 == intval($statiMax)) echo ("<p class='my-4'><strong class='text-success'>Stato</strong>: Terminata</p>");
          else echo ("<p class='my-4'><strong>Stato</strong>: $stato</p>");
          ?>
          <p class='mb-5'>Seleziona gli studenti a cui vuoi assegnare la tesina (minimo 1, massimo <?php echo $maxStudentiTesine ?>)</p>
          <form action="<?php $self = $_SERVER['PHP_SELF'];
                        echo "$self?idTipoTesina=$idTipoTesina&idCorso=$idCorso&idTipoCorso=$idTipoCorso";
                        ?>" method="post">
            <p>
              <?php
              $i = 1;
              //query per studenti assegnati a questo idTesina
              $query = "SELECT l.username,nome,cognome
        FROM studentitesina s
        INNER JOIN login l on s.username=l.username
        WHERE idTesina=$idTesina";
              $result = $link->query($query);
              while ($i <= $maxStudentiTesine) {
                //avanzo nella row degli studenti assegnati a quella tesina
                if ($row = $result->fetch_assoc()) {
                  $matricola = $row["username"];
                  $nome = $row["nome"];
                  $cognome = $row["cognome"];
                  $primaOption = "<option value='$matricola' selected>$nome $cognome $matricola</option><option></option>"; //metto anche l'option per toglierlo prima
                } else {
                  $primaOption = "<option selected></option>";
                  $matricola = ""; //azzero per prossima query
                }

                //A QUESTO PUNTO cerco l'account connesso a questa matricola e all'idTesina della pagina:
                $queryAccount = "SELECT account
          FROM studentitesina
          WHERE username='$matricola' AND idTesina=$idTesina";
                $resultAccount = $link->query($queryAccount);
                $rowAccount = $resultAccount->fetch_assoc();
                if (!empty($rowAccount["account"])) {
                  $account = $rowAccount["account"];
                  $primaOptionAccount = "<option value='$account' selected>$account</option><option></option>";
                } else $primaOptionAccount = "<option></option>";

                //qui come prima opzione ($primaOption) metto uno studente attualmente assegnato
                echo ("<div class='row'>");
                echo ("<div class='col-9'>");
                echo ("<select class='form-select py-2 my-3' name = 'studenti$i'>");
                echo ($primaOption);
                echo ($listaStudenti);
                echo ("</select>
          </div>");

                echo ("<div class='col-3'>
          <select class='form-select py-2 my-3' name = 'account$i'>");
                echo ($primaOptionAccount);
                echo ($listaAccount);
                echo ("</select>
          </div>
          </div>");

                $i++;
              }
              //mi trovo scadenza attuale per dopo
              $query = "SELECT scadenza
          FROM tesine
          WHERE idTesina=$idTesina";
              $result = $link->query($query);
              $row = $result->fetch_assoc();
              $scadenza = $row["scadenza"];
              ?>
            <div class="form-floating mb-3">
              <input type="date" class="form-control" id="scadenza" name="scadenza" placeholder="Cambia scadenza (facoltativo)" value="<?php echo $scadenza; ?>" />
              <label for="scadenza">Cambia scadenza (facoltativo)</label>
            </div>
            (Per rimuovere la scadenza basta cancellare la data)

            <br><br><br><br>
            <div class="row mt-5">
              <div class="col-1">
                <input class="btn btn-primary" type="submit" name="modifica" value="Modifica" />
              </div>
              <div class="col-8"></div>
              <div class="col-2">
                <input class="form-check-input" type="checkbox" name="checkElimina" id="checkElimina">
                <label for="checkElimina">Conferma Eliminazione</label><br>
              </div>
              <div class="col-1">
                <input class="btn btn-danger" type="submit" name="elimina" value="elimina" />
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>

</html>