<?php
$h3 = "Stati Tesine";
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
  <script src="../js/jquery.min.js"></script>
  <script src="../js/textarea.js"></script>
  <div class="container-fluid">
    <div class="row">
      <?php
      require_once("../sidebar.php");
      if (!isset($_GET["idTesina"]) || !isset($_GET["idCorso"])) { //va in selezioneCorso a prendere idCorso, poi in selezioneTesina a prendere idTesina fra quelle prese in quel corso
        header("Location: selezioneCorso.php?to=selezioneTesina.php&text=$h3");
        exit();
      }
      $idTesina = $_GET["idTesina"];
      $idCorso = $_GET["idCorso"];

      //QUERY PER TABELLA, statiTotali serve per select in fondo
      $query = "SELECT titolo,stato,scadenza,statiTesine statiTotali,testo
    FROM tesine t
    INNER JOIN tipitesine tt ON t.idTipoTesina=tt.idTipoTesina
    INNER JOIN tipicorsi tc ON tt.idTipoCorso=tc.idTipoCorso
    WHERE idTesina=$idTesina";
      $result = $link->query($query);
      $row = $result->fetch_assoc();
      extract($row, EXTR_OVERWRITE);
      //GESTIONE POST
      if (isset($_POST["avanza"])||isset($_POST["rimani"])) {
        if(isset($_POST["avanza"])) $statoNuovo=$stato+1;
        else $statoNuovo=$stato;
        $riservato = 0; //riservato parte 0, se in post trovo spunta lo metto 1
        if (isset($_POST["riservato"])) $riservato = 1;
        if (!empty($_POST["new"])) { //aggiunta nuovo commento
          $commentoNuovo = htmlspecialchars($_POST["new"], ENT_QUOTES);
          $dataOra = date("Y-m-d H:i:s");
          $query = "INSERT INTO commentitesine(idTesina, dataOraCommento, statoCommento, riservato, commento)
                VALUES ($idTesina,'$dataOra',$stato, $riservato,'$commentoNuovo')";
          $result = $link->query($query);
          $stato = $statoNuovo; //perchè così posso usarlo per dopo
        }
        foreach ($_POST as $key => $value) { //query di update degli altri valori post
          if ($key != "new" && $key != "avanza" && $key != "rimani" && $key != "riservato" && $key != "stato") { //se è effettivamente un commento vecchio
            if (!empty($value)) { //e non è vuoto
              $value = htmlspecialchars($value, ENT_QUOTES);
              $query = "UPDATE commentitesine
                    SET commento='$value'
                    WHERE idCommentoTesina=$key";
              $result = $link->query($query);
            } else { //se invece è vuoto, elimina
              $query = "DELETE FROM commentitesine
                    WHERE idCommentoTesina=$key";
              $result = $link->query($query);
            }
          }
        }
        //a questo punto posso updatare esito esercizio con lo stato:
        $query = "UPDATE tesine
      SET stato=$statoNuovo
      WHERE idTesina=$idTesina";
        $result = $link->query($query);
        if ($result == true) header("Location: selezioneTesina.php?idCorso=$idCorso&msgShow=1");
        else header("Location: selezioneTesina.php?idCorso=$idCorso&msgShow=2");
        exit();
      }
      //FINE POST


      if (empty($scadenza)) $scadenza = "Nessuna";
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
      $query="SELECT nomeCorso,anno FROM corsi c INNER JOIN tipicorsi t on t.idTipoCorso=c.idTipoCorso WHERE idCorso=$idCorso";
      $result = $link->query($query);
      $row = $result->fetch_assoc();
      extract($row, EXTR_OVERWRITE);
      $nomeCorso=str_replace("_"," ",$nomeCorso);
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">

          <h2 class='text-center'>Docente</h2>
          <h3 class='mb-5 mt-3'><?php echo "$h3 ($nomeCorso $anno)" ?></h3>
          <p class='mb-5'>Se vuoi rimuovere un commento togli tutto il suo testo e sarà rimosso</p>
          <table id="" class="table table-lg table-hover text-center align-middle border border-dark">
            <thead>
              <tr>
                <th scope="col">Titolo</th>
                <th scope="col">Scadenza</th>
              </tr>
            </thead>
            <tbody class="bg-lighter">
              <?php

              //print
              echo "<tr>
        <td>$titolo</td>
        <td>$scadenza</td>";
              echo "</tr>";
              ?>
            </tbody>
          </table>
          <br>
          <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . "?idTesina=$idTesina&idCorso=$idCorso"; ?>" method="post">
            <?php
            echo ("<div class='border border-secondary px-3 py-3 mb-5'><strong>Testo:<br><br></strong><p style= 'font-family: courier new'>$testo</p></div><br><br>");

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

            $i = 1;
            //stampo commenti
            $query = "SELECT commento, dataOraCommento, statoCommento, riservato, idCommentoTesina
        FROM commentitesine c
        INNER JOIN tesine t ON t.idTesina=c.idTesina
        WHERE c.idTesina=$idTesina";
            $result = $link->query($query);
            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                extract($row, EXTR_OVERWRITE);
                if ($riservato == 1) $riservatoTesto = "(riservato)";
                else $riservatoTesto = "";
                $dataOraCommentoLeggibile = date("d/m/Y H:i", strtotime($dataOraCommento));
                if($statoCommento>$statiTotali) $statoCommento="Terminata";
                echo "
            <div class='mb-3'>
              <label for='$idCommentoTesina' class='form-label'>Commento $dataOraCommentoLeggibile stato <strong>$statoCommento</strong> $riservatoTesto</label>
              <textarea class='form-control' id='$idCommentoTesina' rows='3' name='$idCommentoTesina'>$commento</textarea>
            </div>
            ";
                $i++;
              }
            }
            //qui sotto commento nuovo
            ?>

            <div class='mb-3'>
              <label for='new' class='form-label'>Commento nuovo</label>
              <textarea class='form-control' id='new' rows='3' name='new'></textarea>
            </div>

            <div style='display:flex; align-items: center;'>
              <label for='riservato' class='form-label text-center  mx-2'>Riservato</label>
              <input type='checkbox' class='' id='riservato' name='riservato' />
            </div>
            <div class="row mt-5 text-center">
              <div class='col-2'>
                <?php
                if($stato+1<=$statiTotali) echo ("<input class='btn btn-success' type='submit' name='avanza' value='Avanza al prossimo stato' />");
                //c'è il +1 perchè precedentemente c'è stato un $stato--;
                ?>
              </div>
              <div class="col-8"></div>
              <div class="col-2">
                <input class='btn btn-warning' type='submit' name='rimani' value='Rimani allo stato attuale' />
              </div>
            </div>

          </form><br><br>


        </div>
      </div>
    </div>
  </div>
</body>

</html>