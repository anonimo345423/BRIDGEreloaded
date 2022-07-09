<?php
$h3 = "Gestisci tipologia di corso (selezione corso)";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || $permesso != 4) {
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
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">

          <?php
          $query = "SELECT *
        FROM tipicorsi t
        LEFT JOIN login l on l.username=t.docente
        ORDER BY nomeCorso";
          $result = $link->query($query);
          if (isset($_GET["msgShow"]) && $_GET["msgShow"] == 1) echo '<br><div class="alert alert-success" role="alert">
        Tipologia di corso eliminata corretamente!
        </div>'; //messaggio conferma eliminazione
          if (isset($_GET["msgShow"]) && $_GET["msgShow"] == 2) echo '<br><div class="alert alert-danger" role="alert">
        Errore nell\'eliminazione della tipologia di corso
        </div>';
          ?>

                    
          <h3 class='mb-4 mt-5'><?php echo $h3 ?></h3>
          <p>Seleziona il corso da gestire</p>

          <div class="container-fluid bg-white py-3 mt-2 mb-5 border table-responsive rounded-3">
            <?php
            //TABELLA
            require_once("../filterTable.html"); ?>
            <table id="myTable" class="table table-lg table-hover text-center align-middle border border-dark">
              <thead>
                <tr>
                  <th scope="col">Nome corso</th>
                  <th scope="col">Docente</th>
                  <th scope="col"></th>
                </tr>
              </thead>
              <tbody class="bg-lighter">
                <?php
                while ($row = $result->fetch_assoc()) {
                  $rowCounter++;
                  $docente = $row["nome"] . " " . $row["cognome"];
                  $nomeCorso = $row["nomeCorso"];
                  $nomeCorso = str_replace("_"," ",$nomeCorso);
                  $idTipoCorso = $row["idTipoCorso"];
                  echo "<tr>
            <td>$nomeCorso</td>
            <td>$docente</td>
            <td><a class='btn btn-primary' href='gestisciTipoCorso.php?idTipoCorso=$idTipoCorso'>Seleziona</a></td>
            </tr>"; 
                }
                ?>
              </tbody>
            </table>
            <?php
            if ($rowCounter == 0) echo ("<p class='text-center mt-5'>Non ci sono tipi di corso</p>");
            ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</body>

</html>