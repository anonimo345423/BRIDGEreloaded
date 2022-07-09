<?php
$h3 = "Archivia corso";
$rowCounter = 0;
$msgFlag = 0;
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || $permesso < 3) {
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
      //POST
      if (!empty($_POST)) {
        foreach ($_POST as $name => $val) {
          if ($val == "Archivia") {
            $idCorso = $name;
            $query = "UPDATE corsi SET archiviato=1 WHERE idCorso=$idCorso";
            $result = $link->query($query);
            if ($result) $msgFlag = 1;
            else $msgFlag = 2;
          } else if ($val == "Attiva") {
            $idCorso = $name;
            $query = "UPDATE corsi SET archiviato=0 WHERE idCorso=$idCorso";
            $result = $link->query($query);
            if ($result) $msgFlag = 1;
            else $msgFlag = 2;
          }
        }
      }
      //FINE POST
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">

          <?php
          $username = $_SESSION['username'];
          //query docente
          if ($permesso == 3) {
            $query = "SELECT nomeCorso, anno, idCorso
            FROM corsi c
            INNER JOIN tipicorsi t on t.idTipoCorso=c.idTipoCorso
            WHERE docente=? AND archiviato=0
            order by anno desc";
            $stmt = $link->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
          }
          //query admin
          else {
            $query = "SELECT nomeCorso, anno, idCorso
            FROM corsi c
            INNER JOIN tipicorsi t on t.idTipoCorso=c.idTipoCorso
            WHERE archiviato=0
            order by anno desc";
            $stmt = $link->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
          }
          if ($msgFlag == 1) echo '<div class="alert alert-success" role="alert">
              Operazione effettuata con successo
            </div><br>';
            if ($msgFlag == 2) echo '<div class="alert alert-danger" role="alert">
              Operazione fallita
            </div><br>';

          ?>

                    <?php if($_SESSION["permesso"]==3) echo "<h2 class='text-center'>Docente</h2>"; ?>
          <h3 class='mb-4 mt-5'><?php echo $h3 ?></h3>
          <p>Il corso una volta archiviato può essere anche riportato attivo dalla tabella sotto</p>

          <div class="container-fluid bg-white py-3 mt-2 mb-5 border table-responsive rounded-3">
            <?php
            //TABELLA corsi da archiviare
            require_once("../filterTable.html"); ?>
            <h5 class="mb-4 mt-3">Corsi archiviabili:</h5>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
              <table id="myTable" class="table table-lg table-hover text-center align-middle border border-dark">
                <thead>
                  <tr>
                    <th scope="col">Nome corso</th>
                    <th scope="col">Anno</th>
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody class="bg-lighter">
                  <?php
                  while ($row = $result->fetch_assoc()) {
                    $rowCounter++;
                    $anno = $row["anno"];
                    $nomeCorso = $row["nomeCorso"];
                    $nomeCorso = str_replace("_"," ",$nomeCorso);
                    $idCorso = $row["idCorso"];
                    echo "<tr>
            <td>$nomeCorso</td>
            <td>$anno</td>
            <td><input class='btn btn-danger' type = 'submit' name = '$idCorso' value='Archivia'/></td>
            </tr>"; 
                  }
                  ?>
                </tbody>
              </table>
              <?php
              if ($rowCounter == 0) echo ("<p class='text-center mt-5'>Nessun corso da archiviare</p>");
              $rowCounter = 0;


              //ZONA CORSI DA ATTIVARE

              //query docente
              if ($permesso == 3) {
                $query = "SELECT nomeCorso, anno, idCorso
          FROM corsi c
          INNER JOIN tipicorsi t on t.idTipoCorso=c.idTipoCorso
          WHERE docente=? AND archiviato=1
          order by anno desc";
                $stmt = $link->prepare($query);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
              }
              //query admin
              else {
                $query = "SELECT nomeCorso, anno, idCorso
          FROM corsi c
          INNER JOIN tipicorsi t on t.idTipoCorso=c.idTipoCorso
          WHERE archiviato=1
          order by anno desc";
                $stmt = $link->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
              }
              //TABELLA corsi da togliere da archivio
              require_once("../filterTable.html"); ?>
              <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <h5 class="mb-4 mt-3">Corsi riattivabili (precedentemente archiviati):</h5>
                <table id="myTable2" class="table table-lg table-hover text-center align-middle border border-dark">
                  <thead>
                    <tr>
                      <th scope="col">Nome corso</th>
                      <th scope="col">Anno</th>
                      <th scope="col"></th>
                    </tr>
                  </thead>
                  <tbody class="bg-lighter">
                    <?php
                    while ($row = $result->fetch_assoc()) {
                      $rowCounter++;
                      $anno = $row["anno"];
                      $nomeCorso = $row["nomeCorso"];
                      $nomeCorso = str_replace("_"," ",$nomeCorso);
                      $idCorso = $row["idCorso"];
                      echo "<tr>
            <td>$nomeCorso</td>
            <td>$anno</td>
            <td><input class='btn btn-primary' type = 'submit' name = '$idCorso' value='Attiva'/></td>
            </tr>"; 
                    }
                    ?>
                  </tbody>
                </table>


              </form>
              <?php if ($rowCounter == 0) echo ("<p class='text-center mt-5'>Nessun corso da attivare</p>"); ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</body>

</html>