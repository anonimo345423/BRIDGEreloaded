<?php
$h3 = "Elimina corso";
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
      function deleteDirectory($dir) {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
          if ($item == '.' || $item == '..') continue;
          if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
        }
        return rmdir($dir);
      }

      require_once("../sidebar.php");
      //POST
      if (!empty($_POST)) {
        foreach ($_POST as $name => $val) {
          $idCorso = $name;
          $query = "SELECT nomeCorso, anno
            FROM tipicorsi t
            INNER JOIN corsi c on t.idTipoCorso=c.idTipoCorso
            WHERE idCorso=$idCorso";
          $result = $link->query($query);

          $query = "DELETE FROM corsi WHERE idCorso=$idCorso";
          $link->query($query);
          if ($result) {
            $msgFlag = 1;
            //elimino anche la cartella del corso con gli esercizi
            $row = $result->fetch_assoc();
            extract($row, EXTR_OVERWRITE);
            $dir = "../corsi/$nomeCorso.$anno";
            deleteDirectory($dir);
          } else $msgFlag = 2;
        }
      }
      //FINE POST
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">

          <?php
          $username = $_SESSION['username'];
          if ($msgFlag == 1) echo '<div class="alert alert-success" role="alert">
              Corso eliminato
            </div><br>';
          if ($msgFlag == 2) echo '<div class="alert alert-danger" role="alert">
            Corso non eliminato
            </div><br>';
          ?>

                    <?php if($_SESSION["permesso"]==3) echo "<h2 class='text-center'>Docente</h2>"; ?>
          <h3 class='mb-4 mt-5'><?php echo $h3 ?></h3>
          <p>Puoi eliminare un corso solo se lo hai prima archiviato</p>
          <p><strong>Attenzione</strong>: verranno anche eliminati i file contenenti gli esercizi degli studenti. Questa operazione è irreversibile</p>

          <div class="container-fluid bg-white py-3 mt-2 mb-5 border table-responsive rounded-3">
            <?php
            //TABELLA corsi da archiviare
            require_once("../filterTable.html"); ?>
            <?php
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
            else{
              $query = "SELECT nomeCorso, anno, idCorso
          FROM corsi c
          INNER JOIN tipicorsi t on t.idTipoCorso=c.idTipoCorso
          WHERE archiviato=1
          order by anno desc";
               $result=$link->query($query);
            }
            require_once("../filterTable.html"); ?>
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
            <td><input class='btn btn-danger' type = 'submit' name = '$idCorso' value='Elimina'/></td>
            </tr>"; 
                  }
                  ?>
                </tbody>
              </table>


            </form>
            <?php if ($rowCounter == 0) echo ("<p class='text-center mt-5'>Nessun corso da eliminare</p>"); ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</body>

</html>