<?php
$h3 = "Tesina assegnata";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || $permesso != 1) {
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

          $query = "SELECT GROUP_CONCAT(commento ORDER BY c.idCommentoTesina SEPARATOR '|') commenti,
        GROUP_CONCAT(statoCommento ORDER BY c.idCommentoTesina SEPARATOR '|') statiCommenti,
        GROUP_CONCAT(riservato ORDER BY c.idCommentoTesina SEPARATOR '|') riservati, GROUP_CONCAT(dataOraCommento ORDER BY c.idCommentoTesina SEPARATOR '|') dateOreCommenti,
         testo, scadenza, stato, account, titolo, statiTesine statiTotali
        FROM tesine t
        LEFT JOIN commentitesine c on t.idTesina=c.idTesina
        INNER JOIN tipitesine tt on t.idTipoTesina=tt.idTipoTesina
        INNER JOIN tipicorsi tc ON tt.idTipoCorso=tc.idTipoCorso
        INNER JOIN studentitesina s on t.idTesina=s.idTesina
        WHERE idCorso=? AND username=?";
          $stmt = $link->prepare($query);
          $stmt->bind_param("is", $idCorso, $username);
          $stmt->execute();
          $result = $stmt->get_result();

          $row = $result->fetch_assoc();


          $testo = $row["testo"];
          if ($row["scadenza"] == null) $scadenza = "Nessuna";
          else {
            $scadenza = $row["scadenza"];
            $oggi = date('Y-m-d');
            $date1 = new DateTime($scadenza);
            $date2 = new DateTime($oggi);
            $interval = $date1->diff($date2);
            if (!$interval->invert) $scadenza = "<strong style='color:red'>Scaduta</strong>";
            else if ($interval->days == 1) $scadenza = "Domani";
            else if ($interval->days == 0) $scadenza = "Oggi";
            else {
              $giorni = $interval->format('%d');
              $mesi = $interval->format('%m');
              if ($mesi == 1) $vocaleMesi = "e";
              else $vocaleMesi = "i";
              if ($giorni == 1) $vocaleGiorni = "o";
              else $vocaleGiorni = "i";
              if ($mesi == 0) $scadenza = "Mancano ancora $giorni giorn$vocaleGiorni";
              else $scadenza = "Mancano ancora $mesi mes$vocaleMesi e $giorni giorn$vocaleGiorni";
            }
          }
          if ($row["account"] == null) $account = "-";
          else $account = $row["account"];

          $commenti = $row["commenti"];
          $commentiArr = explode("|", $commenti); //siccome i commenti nel db si trovano tutti in un campo, li divido col divisore |
          //print_r($commentiArr);
          $statiCommenti = $row["statiCommenti"];
          $statiCommentiArr = explode("|", $statiCommenti);
          $riservati = $row["riservati"];
          $riservatiArr = explode("|", $riservati);
          $dateOreCommenti = $row["dateOreCommenti"];
          $dateOreCommentiArr = explode("|", $dateOreCommenti);
          $size = count($commentiArr);

          $titolo = $row["titolo"];
          if (empty($titolo)) $titolo = "Tesina Assegnata";
          $stato = $row["stato"];
          $statiTotali = $row["statiTotali"];

          if (isset($_GET["success"])) echo "<br><div class='alert alert-success' role='alert'>
          Ecco la tua tesina!
         </div>"
          ?>
          <h3 class='mb-5 mt-4'><?php echo $titolo ?></h3>
          <?php
          if ($row["testo"] == NULL) { //cioè se esce che non ci sono entry in studentitesina con l'username dello studente:
            echo '<div class="container-fluid bg-white mt-2 mb-5 border table-responsive py-5 rounded-3 text-center">';
            echo ("<p>Non hai ancora una tesina assegnata<br>Svolgi gli esercizi e poi fattene assegnare una!</p>");
            echo "<a href='selezioneCorso.php?to=tesinaAssegnata' class='btn btn-primary'>Torna indietro</a>";
            exit();
          }
          ?>
          <div class="container-fluid bg-white mt-2 mb-5 py-5 border table-responsive rounded-3 ">
            <p class="mb-5">Qui trovi tutti i dati relativi alla tesina che ti &egrave; stata assegnata, se vuoi avanzare al prossimo stato parla col professore!</p>
            <table id="" class="table table-xl table-hover text-center align-middle border border-dark">
              <thead>
                <tr>
                  <th scope="col">Scadenza</th>
                  <th scope="col">Account assegnato</th>
                </tr>
              </thead>
              <tbody class="bg-lighter">

                <?php
                //FORM e tabella

                echo "
              <tr>
              <td>$scadenza</td>";
                echo "
              <td>$account</td>";
                echo "</tr>";
                ?>

              </tbody>
            </table>
            <br>
            <?php
            echo ("<div class='border border-secondary px-3 py-3 my-5'><strong>Testo:<br><br></strong><p style= 'font-family: courier new'>$testo</p></div><br><br>");
            //progress bar
            $stato--; //perchè se sto allo stato 1 vuol dire che non lo ho ancora completato, è come se fossi allo stato 0
            $completata = "";
            $percent = 1 / $statiTotali * 100;
            $percentStringa = "$percent%";
            $i = 1;
            if ($stato == $statiTotali) {
              //$percent="100%"
              $completata = "bg-success";
            } //ossia se ho finito, cambio il colore della progress bar
            echo "<p class='text-center'>Questa barra indica lo stato di avanzamento della tesina:</p>";
            echo "<div class='progress mb-5' style='height:3.5vh; min-height: 30px;'>";
            while ($i <= $statiTotali) {
              if ($i <= $stato) $attiva = "progress-bar";
              else $attiva = "";
              echo "<div class='$attiva $completata text-center' role='progressbar' style='width: $percentStringa;  position: relative; left: ; font-size:large;height:3.5vh; min-height: 30px'><strong>$i" . "° Stato</strong></div>";
              $i++;
            }
            echo "</div><br><br>";
            $j = 0;
            while ($j < $size) {
              if (empty($commentiArr)) break;
              if (!empty($commentiArr[$j]) && $riservatiArr[$j] == 0) {
                $date = strtotime($dateOreCommentiArr[$j]);
                $dataOraModificata = date("d/m/Y H:i", $date);
                if($statiCommentiArr[$j]<=$statiTotali) echo "<strong>Commento $statiCommentiArr[$j]° stato</strong> ($dataOraModificata)<br> <textarea disabled class='form-control'>$commentiArr[$j]</textarea><br>"; //stampo solo se il commento non è riservato (=0)
                else echo "<strong>Commento fine tesina</strong> ($dataOraModificata)<br> <textarea disabled class='form-control'>$commentiArr[$j]</textarea><br>"; 
              }
              $j++;
            }
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>