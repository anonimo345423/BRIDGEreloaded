<?php
$h3 = "Autoassegnazione tesina";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
$rowCounter = 0;
if (!isset($_SESSION['valid']) || $permesso != 1) {
  header("Location: ../login.php");
  exit();
}
$username = $_SESSION['username']
?>
<html>

<body>
  <script src="../js/jquery.min.js"></script>
  <script src="../js/showHideTesto.js"></script>
  <div class="container-fluid">
    <div class="row">
      <?php
      require_once("../sidebar.php");
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">
          <?php
          //se non ho l'idCorso, lo ottengo da selezioneCorso
          $self = basename(__FILE__, '.php'); //nome del file php
          if (!isset($_GET["idCorso"])) {
            header("Location: selezioneCorso.php?to=$self");
            exit();
          } else $idCorso = $_GET["idCorso"];

          //GESTIONE POST
          if (isset($_POST["seleziona"])) {
            $idTipoTesina = $_POST["idTipoTesina"];
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
            //poi inserisco lo studente (solo uno perchè per più di uno si fa dal docente)
            $query = "INSERT INTO studentitesina(idTesina,username)
          VALUES (?,?)";
            $stmt = $link->prepare($query);
            $stmt->bind_param("is", $idTesina, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            header("Location: tesinaAssegnata.php?idCorso=$idCorso&success=1"); //lo mando in tesinaAssegnata con idCorso già settato
            exit();
          }

          //FINE POST
          //trovo il numero di categorie del corso e maxstudentiTesine che se è 0 non ci sono tesine (se è 0 è arrivato qui in maniera disonesta), autoAssegnazione se è 0 esco
          $query = "SELECT categorie, maxStudentiTesine, autoAssegnazione
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
          $numCategorie = count($arrCategorie);
          $maxStudentiTesine = $row["maxStudentiTesine"];
          $autoAssegnazione=$row["autoAssegnazione"];
          if ($maxStudentiTesine == 0 || $autoAssegnazione==0) {
            header("Location: ../login.php");
            exit();
          }

          //conto gli esercizi con stato 3 (accettati) e che sono obbligatori, se sono =$numCategorie allora può accedere a tesina
          $query = "SELECT COUNT(idEsercizio) num
        FROM esercizi e
        INNER JOIN tipiesercizi t on e.idTipoEsercizio=t.idTipoEsercizio 
        WHERE username=? AND stato=3 AND obbligatorio=1 AND idCorso=?";
          $stmt = $link->prepare($query);
          $stmt->bind_param("si", $username, $idCorso);
          $stmt->execute();
          $result = $stmt->get_result();
          $row = $result->fetch_assoc();
          if ($row["num"] == $numCategorie) $accessoTesine = 1;
          else $accessoTesine = 0;
          //Controllo pure che non abbia già lui stesso una tesina assegnata, se ce l'ha accessoTesine=0
          $query = "SELECT username
        FROM studentitesina s
        INNER JOIN tesine t on t.idTesina=s.idTesina
        WHERE idCorso=?";
          $stmt = $link->prepare($query);
          $stmt->bind_param("i", $idCorso);
          $stmt->execute();
          $result = $stmt->get_result();
          while ($row = $result->fetch_assoc()) {
            $user = $row["username"];
            if ($user == $username) $accessoTesine = 0;
          }
          //QUERY controllo che utente sia iscritto al corso e che il corso non sia archiviato, sennò lo mando fuori
          $query = "SELECT *
        FROM iscritto i
        INNER JOIN corsi c on i.idCorso=c.idCorso
        WHERE c.idCorso=? and i.username=? AND archiviato=0";
          $stmt = $link->prepare($query);
          $stmt->bind_param("is", $idCorso, $username);
          $stmt->execute();
          $result3 = $stmt->get_result();
          if ($result3->num_rows == 0) {
            header("Location: ../login.php");
            exit();
          }
          //a questo punto so se può accedere o meno alle tesine, se non può lo mando a listaTesine dove può vederle senza interagirci
          if ($accessoTesine == 0) {
            header("Location: listaTesine.php?idCorso=$idCorso&warning=1");
            exit;
          }

          ?>

          
          <h3 class='mb-4 mt-5'><?php echo $h3 ?></h3>          <p class="pb-3"><strong>Attenzione:</strong> puoi autoassegnarti la tesina solo se vuoi lavorarci da solo, se vuoi svolgere la tesina con un
            altro studente o non sei sicuro di come funzioni una tesina esci da questa pagina e contatta il professore via mail<br>
            Per farti assegnare un server dovrai comunque parlare col professore</p>

          <div class="container-fluid bg-white mt-2 mb-5 border table-responsive py-5 pb-3 rounded-3 ">
            <table id="" class="table table-xl table-hover text-center align-middle border border-dark">
              <thead>
                <tr>
                  <th scope="col"></th>
                  <th scope="col">Titolo</th>
                  <th scope="col">Testo</th>
                </tr>
              </thead>
              <tbody class="bg-lighter">

                <?php //inizio form
                $self = $_SERVER['PHP_SELF'] . "?idCorso=$idCorso"; //come action del form qui sotto rivado a me stesso, con tanto di parametro get idCorso altrimenti la pagina non si aprirebbe
                echo ("<form action='$self' method='post' enctype='multipart/form-data'>");
                $idCorso = $_GET["idCorso"];
                //trovo idTipoCorso
                $query = "SELECT idTipoCorso
        FROM corsi
        WHERE idCorso=?";
                $stmt = $link->prepare($query);
                $stmt->bind_param("i", $idCorso);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $idTipoCorso = $row["idTipoCorso"];

                //la query prende le tesine da tipitesine con idTipoCorso giusto e che non sono ancora state prese da degli studenti (tabella tesine)
                $query = "SELECT *
        FROM tipitesine
        WHERE idTipoCorso=? AND disabilitato=0 AND idTipoTesina NOT IN(
            SELECT t.idTipoTesina
            FROM tipitesine tt
            INNER JOIN tesine t on t.idTipoTesina=tt.idTipoTesina
            WHERE idCorso=?
        )
        ORDER BY titolo";
                $stmt = $link->prepare($query);
                $stmt->bind_param("ii", $idTipoCorso, $idCorso);
                $stmt->execute();
                $result = $stmt->get_result();


                while ($row = $result->fetch_assoc()) {
                  $rowCounter++;
                  $idTipoTesina = $row["idTipoTesina"];
                  $testo = $row["testo"];
                  $titolo = $row["titolo"];
                  $extra = $row["extra"];
                  if (!empty($extra) && $extra == $idCorso) $extra = "Si";
                  else if (!empty($extra) && $extra != $idCorso) {
                    $rowCounter--; //perchè non vale come row questa
                    continue;
                  }
                  //spiego la riga sopra: extra contiene l'idCorso da cui la tesina è stata creata,
                  //se non è il nostro caso skippiamo il resto del ciclo ed andiamo alla prossima row
                  else $extra = "No";

                  echo "
          <tr>
          <td>
            <div class='form-check'>
              <input class='form-check-input' type='radio' name='idTipoTesina' value='$idTipoTesina' required>
            </div>
          </td>
          <td>$titolo</td>
          <td>
          <button class='btn btn-secondary control' id='$idTipoTesina' type='button' >Testo</button>
          </td>
          </tr>
          
          <tr class='show_hide' id='testo$idTipoTesina'>
          <td colspan=4 class='bg-white' style= 'font-family: courier new; text-align:left !important;'>$testo</td>
          </tr>
          "; //la tr di id testo$idTipoTesina viene mostrata solo se il relativo button $idTipoTesina viene premuto (vedi script js inclusi a inizio pagina per la meccanica precisa)
                }
                ?>

              </tbody>
            </table>
            <?php
            if ($rowCounter == 0) echo "<p class='text-center mt-5'>Non ci sono tesine da assegnare, chiedi al professore di aggiungerne altre</p>";
            else echo "
            <div class='row pb-5 pt-2'>
              <div class='col-2'>
                <button class='btn btn-primary' type='submit' name='seleziona'>Assegna</button>
              </div>
            </div>";
            ?>
            </form>
          </div>

        </div>
      </div>
    </div>
  </div>
</body>

</html>