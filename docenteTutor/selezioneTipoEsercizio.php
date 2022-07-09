<?php
$h3 = "Gestisci tipi esercizio (Selezione tipo esercizio)";
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
  <script src="../js/jquery.min.js"></script>
	<script src="../js/showHideTesto.js"></script>
  <div class="container-fluid">
    <div class="row">
      <?php
      require_once("../sidebar.php");
      if (!isset($_GET["idTipoCorso"])) { //se non ho idCorso, scelgo il corso in selezioneCorso e arrivo qua
        header("Location: gestisciTipiEsercizi.php");
        exit();
      } else $idTipoCorso = $_GET["idTipoCorso"];

      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">

          <?php
          //query
          $username = $_SESSION['username'];
          $query = "SELECT obbligatorio,categoria,testo,idTipoEsercizio
        FROM tipiesercizi t
        WHERE idTipoCorso=? AND disabilitato=0
        ORDER BY categoria,obbligatorio desc";
          $stmt = $link->prepare($query);
          $stmt->bind_param("i", $idTipoCorso);
          $stmt->execute();
          $result = $stmt->get_result();

          if (isset($_GET["delete"])) echo '<div class="alert alert-success" role="alert">
        Tipo di esercizio eliminato con successo
       </div><br>'; //potrei avere delete in get se arrivo da gestioneTipiEsercizi ed ho cancellato un esercizio.
          ?>
        <?php if (isset($_GET["flag"])&&$_GET["flag"] == 1) echo '<div class="alert alert-success" role="alert">
          Tipo di esercizio modificato con successo
         </div><br>';
          if (isset($_GET["flag"])&&$_GET["flag"] == 2) echo '<div class="alert alert-danger" role="alert">
         Errore nella modifica dell\'esercizio
        </div><br>';
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
                  <th scope="col">Categoria</th>
                  <th scope="col">Facoltativo</th>
                  <th scope="col">Testo</th>
                  <th scope="col"></th>
                </tr>
              </thead>
              <tbody class="bg-lighter">
                <?php
                while ($row = $result->fetch_assoc()) {
                  if (!empty($row)) extract($row, EXTR_OVERWRITE); //tira fuori da array a variabili col loro nome
                  $categoriaSenzaNumero=substr($categoria, 1);
                  $rowCounter++;
                  if ($obbligatorio == 0) $facoltativo = "<i class='bi bi-check display-6' style='color:black'></i>";
                  else $facoltativo = "";
                  echo "<tr>
            <td>$categoriaSenzaNumero</td>
            <td>$facoltativo</td>
            <td>
            <button class='btn btn-secondary control' id='$idTipoEsercizio' type='button' >Testo</button>
            </td>
            <td><a class='btn btn-primary' href='gestisciTipiEsercizi.php?idTipoEsercizio=$idTipoEsercizio&idTipoCorso=$idTipoCorso'>Modifica</a></td>
            </tr>
            
            <tr class='show_hide' id='testo$idTipoEsercizio'>
            <td colspan=4 class='bg-white' style= 'font-family: courier new; text-align:left !important;'>$testo</td>";
            echo "</tr>"; 
                }
                ?>
              </tbody>
            </table>
            <?php
            if ($rowCounter == 0) echo "<p class='text-center'>Nessun Tipo di esercizio presente</p>";
            ?>
          </div>
        </div>
      </div>
    </div>
</body>

</html>