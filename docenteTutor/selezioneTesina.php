<?php
$h3 = "Stati tesine (selezione tesine)";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || ($permesso != 2 && $permesso != 3)) {
  header("Location: ../login.php");
  exit();
}
$rowCounter = 0;
$username = $_SESSION['username'];
?>
<html>

<body>
  <script src="../js/jquery.min.js"></script>
  <script src="../js/showHideTesto.js"></script>
  <div class="container-fluid">
    <div class="row">
      <?php
      require_once("../sidebar.php");
      if (!isset($_GET["idCorso"])) {
        header("Location: selezioneCorso.php?to=selezioneTesina.php");
        exit();
      } else $idCorso = $_GET["idCorso"];
      //trovo numero statiTesine
      $query = "SELECT statiTesine
      FROM tipicorsi t
      INNER JOIN corsi c ON c.idTipoCorso=t.idTipoCorso
      WHERE idCorso=$idCorso";
      $result = $link->query($query);
      $row = $result->fetch_assoc();
      $statiMax = $row["statiTesine"];
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">
          <?php
          if (isset($_GET["msgShow"]) && $_GET["msgShow"] == 1) echo '<br><div class="alert alert-success" role="alert">
        Tesina corretta con successo!
        </div>';
          else if (isset($_GET["msgShow"]) && $_GET["msgShow"] == 2) echo '<br><div class="alert alert-danger" role="alert">
        Correzione della tesina fallita!
        </div>';
          ?>
          <h2 class='text-center'>Docente</h2>
          <h3 class='mb-4 mt-5'><?php echo $h3 ?></h3>          <p>Le tesine con titolo in rosso sono quelle che sono scadute</p>
          <div class="container-fluid bg-white mt-2 mb-5 border table-responsive py-5 pb-3 rounded-3 ">
          <?php
            //TABELLA
            require_once("../filterTable.html"); ?>
            <table id="myTable" class="table table-xl table-hover text-center align-middle border border-dark">
              <thead>
                <tr>
                  <th scope="col">Titolo</th>
                  <th scope="col">Proposta da studente?</th>
                  <th scope="col">Assegnata a</th>
                  <th scope="col">Stato</th>
                  <th scope="col">Testo</th>
                  <th scope="col">Seleziona</th>
                </tr>
              </thead>
              <tbody class="bg-lighter">
                <?php
                $query = "SELECT *
        FROM tipitesine tt
        INNER JOIN tesine t on t.idTipoTesina=tt.idTipoTesina
        WHERE idCorso=?
    ORDER BY stato, titolo";
                $stmt = $link->prepare($query);
                $stmt->bind_param("i", $idCorso);
                $stmt->execute();
                $result = $stmt->get_result();



                while ($row = $result->fetch_assoc()) {
                  $rowCounter++;
                  $stato = $row["stato"];
                  if ($stato-1 == $statiMax) $stato = "<strong class='text-success'>Terminata</strong>"; //cioè gli do un colore verde per capire che è finita quella tesina

                  $idTipoTesina = $row["idTipoTesina"];
                  $testo = $row["testo"];
                  $titolo = $row["titolo"];
                  $extra = $row["extra"];
                  $idTesina = $row["idTesina"];
                  
                  $scadenza = $row["scadenza"];
                  if (!empty($scadenza)){
                    $oggi=date('Y-m-d');
                    $date1 = new DateTime($scadenza);
                    $date2 = new DateTime($oggi);
                    $interval = $date1->diff($date2);
                    if (!$interval->invert) $titolo="<strong style='color:red'>$titolo</strong>";
                  }

                  if (!empty($extra) && $extra == $idCorso) $extra = "Si";
                  else if (!empty($extra) && $extra != $idCorso) {
                    $rowCounter--; //perchè non vale come row questa
                    continue;
                  }
                  //spiego la riga sopra: extra contiene l'idCorso da cui la tesina è stata creata,
                  //se non è il nostro caso skippiamo il resto del ciclo ed andiamo alla prossima row
                  else $extra = "No";
                  //trovo gli studenti assegnati a quell'idTesina
                  $studenti = "";
                  $query = "SELECT nome,cognome,l.username,idTesina
      FROM studentitesina s
      INNER JOIN login l ON l.username=s.username
      WHERE idTesina=$idTesina";
                  $resultNomi = $link->query($query);
                  while ($rowNomi = $resultNomi->fetch_assoc()) {
                    $studenti = $studenti . $rowNomi["nome"] . " " . $rowNomi["cognome"] . " " . $rowNomi["username"] . "<br>";
                  }


                  echo "
      <tr>
      <td>$titolo</td>
      <td>$extra</td>
      <td>$studenti</td>
      <td>$stato</td>
      <td>
      <button class='btn btn-secondary control' id='$idTipoTesina' type='button' >Testo</button>
      </td>
      <td>
      <a class='btn btn-primary' href='statiTesine.php?idTesina=$idTesina&idCorso=$idCorso'>Seleziona</a>
      </td>
      </tr>
      
      <tr class='show_hide' id='testo$idTipoTesina'>
      <td colspan=6 class='bg-white' style= 'font-family: courier new; text-align:left !important;'>$testo</td>
      </tr>
      "; //la tr di id testo$idTipoTesina viene mostrata solo se il relativo button $idTipoTesina viene premuto (vedi script js inclusi a inizio pagina per la meccanica precisa)
                }


                ?>
              </tbody>
            </table>
            <?php
            if ($rowCounter == 0) echo ("<p class='text-center mt-5'>Non ci sono tesine assegnate</p>");
            ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</body>

</html>