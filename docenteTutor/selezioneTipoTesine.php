<?php
$h3 = "Gestisci tipi tesina (Selezione tipo tesina)";
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
        header("Location: gestisciTipiTesine.php");
        exit();
      } else $idTipoCorso = $_GET["idTipoCorso"];

      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">

          <?php
          //query
          $username = $_SESSION['username'];
          $query = "SELECT *
        FROM tipitesine t
        LEFT JOIN corsi c on t.extra=c.idCorso
        WHERE t.idTipoCorso=? AND disabilitato=0
        ORDER BY extra,titolo";
          $stmt = $link->prepare($query);
          $stmt->bind_param("i", $idTipoCorso);
          $stmt->execute();
          $result = $stmt->get_result();

          if (isset($_GET["delete"])) echo '<div class="alert alert-success" role="alert">
        Tipo di tesina eliminata con successo
       </div><br>'; //potrei avere delete in get se arrivo da gestioneTipiTesine ed ho cancellato una tesina.
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
                  <th scope="col">Titolo</th>
                  <th scope="col">Specifica di un corso?</th>
                  <th scope="col">Visualizza testo</th>
                  <th scope="col"></th>
                </tr>
              </thead>
              <tbody class="bg-lighter">
                <?php
                while ($row = $result->fetch_assoc()) {
                  if (!empty($row)) extract($row, EXTR_OVERWRITE); //tira fuori da array a variabili col loro nome
                  $rowCounter++;
                  if(empty($anno)) $anno="-";
                  echo "<tr>
            <td>$titolo</td>
            <td>$anno</td>
            <td>
            <button class='btn btn-secondary control' id='$idTipoTesina' type='button' >Testo</button>
            </td>
            <td><a class='btn btn-primary' href='gestisciTipiTesine.php?idTipoTesina=$idTipoTesina'>Modifica</a></td>
            </tr>
            
            <tr class='show_hide' id='testo$idTipoTesina'>
            <td colspan=4 class='bg-white' style= 'font-family: courier new; text-align:left !important;'>$testo</td>
            </tr>
            ";
                }
                ?>
              </tbody>
            </table>
            <?php
            if ($rowCounter == 0) echo "<p class='text-center'>Nessun Tipo di tesina presente</p>";
            ?>
          </div>
        </div>
      </div>
    </div>
</body>

</html>