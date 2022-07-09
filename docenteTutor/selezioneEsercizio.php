<?php
$h3 = "Correggi esercizio (Selezione esercizio)";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || ($permesso != 2 && $permesso != 3)) {
  header("Location: ../login.php");
  exit();
}
$rowCounter = 0;

?>
<html>

<body>
  
  <div class="container-fluid">
    <div class="row">
      <?php
      require_once("../sidebar.php");
      if (!isset($_GET["idCorso"])) { //se non ho idCorso, scelgo il corso in selezioneCorso e arrivo qua
        header("Location: selezioneCorso.php?to=selezioneEsercizio.php");
        exit();
      } else $idCorso = $_GET["idCorso"];

      //quando apro questa pagina devo rimuovere le notifiche:
      $query = "UPDATE corsi SET notifica=0 WHERE idCorso=$idCorso";
      $result = $link->query($query);
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">

          <?php
          if (isset($_GET["msgShow"]) && $_GET["msgShow"] == 1) echo '<br><div class="alert alert-success" role="alert">
      Esercizio corretto con successo!
      </div>';
          else if (isset($_GET["msgShow"]) && $_GET["msgShow"] == 2) echo '<br><div class="alert alert-danger" role="alert">
      Correzione dell\'esercizio fallita!
      </div>';
          //query per controllo plagiarismo o meno
          $query = "SELECT correzione
        FROM tipicorsi t
        INNER JOIN corsi c on c.idTipoCorso=t.idTipoCorso
        WHERE idCorso=?";
          $stmt = $link->prepare($query);
          $stmt->bind_param("i", $idCorso);
          $stmt->execute();
          $result = $stmt->get_result();
          $row = $result->fetch_assoc();
          $correzione = $row["correzione"];

          //query
          $username = $_SESSION['username'];
          $query = "SELECT dataOra, e.username matricola, nome, cognome, t.idTipoEsercizio, idEsercizio, stato, obbligatorio, categoria
        FROM esercizi e
        INNER JOIN login l ON e.username=l.username
        INNER JOIN tipiesercizi t on t.idTipoEsercizio=e.idTipoEsercizio
        WHERE idCorso=?
        ORDER BY stato,obbligatorio desc,dataOra,cognome";
          $stmt = $link->prepare($query);
          $stmt->bind_param("i", $idCorso);
          $stmt->execute();
          $result = $stmt->get_result();
          ?>

                    <h2 class='text-center'>Docente</h2>
          <h3 class='mb-4 mt-5'><?php echo $h3 ?></h3>
          <p>Filtra per matricola e scegli cosa fare tramite i pulsanti</p>

          <div class="container-fluid bg-white py-3 mt-2 mb-5 border table-responsive rounded-3">
            <?php
            //TABELLA
            require_once("../filterTable.html"); ?>
            <table id="myTable" class="table table-lg table-hover text-center align-middle border border-dark">
              <thead>
                <tr>
                  <th scope="col">Matricola</th>
                  <th scope="col">Nome</th>
                  <th scope="col">Cognome</th>
                  <th scope="col">Stato</th>
                  <th scope="col">Categoria</th>
                  <th scope="col">Facolativo</th>
                  <th scope="col">Data invio esercizio</th>
                  <th scope="col"></th>
                  <?php if ($correzione == "sim") echo "<th scope='col'></th>"; ?>

                </tr>
              </thead>
              <tbody class="bg-lighter">
                <?php
                while ($row = $result->fetch_assoc()) {
                  if (!empty($row)) extract($row, EXTR_OVERWRITE); //tira fuori da array a variabili col loro nome
                  $rowCounter++;
                  if ($stato == 1) $statoScritta = "<strong class='text-secondary'>Da correggere</strong>";
                  else if ($stato == 3) $statoScritta = "<strong class='text-success'>Accettato</strong>";
                  else if ($stato == 2) $statoScritta = "<strong class='text-warning'>Da rivedere</strong>";
                  if ($stato == 1) $testoCorrezione = "Correggi";
                  else $testoCorrezione = "Correggi ancora";
                  if ($obbligatorio == 0) $facoltativo = "<i class='bi bi-check display-6' style='color:black'></i>";
                  else $facoltativo = "";
                  $dataOra = strtotime($dataOra);
                  $dataOra = date("d/m/Y H:i", $dataOra);
                  $categoria = $row["categoria"];
                  $categoriaSenzaNumero=substr($categoria, 1);
                  echo "<tr>
            <td>$matricola</td>
            <td>$nome</td>
            <td>$cognome</td>
            <td>$statoScritta</td>
            <td>$categoriaSenzaNumero</td>
            <td>$facoltativo</td>
            <td>$dataOra</td>
            <td><a class='btn btn-primary' href='correggiEsercizio.php?idEsercizio=$idEsercizio'>$testoCorrezione</a></td>";
                  if ($correzione == "sim") echo "<td><a class='btn btn-info' href='plagiarismo.php?idEsercizio=$idEsercizio&idCorso=$idCorso'>Plagiarismo</a></td>";
                  echo "</tr>"; 
                }
                ?>
              </tbody>
            </table>
            <?php
            if ($rowCounter == 0) echo "<p class='text-center'>Nessun esercizio da correggere</p>";
            ?>
          </div>
        </div>
      </div>
    </div>
</body>

</html>