<?php
$h3 = "Selezione corso";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || ($permesso != 2 && $permesso != 3)) {
  header("Location: ../login.php");
  exit();
}
$rowCounter = 0;
?>

<body>
  <div class="container-fluid">
    <div class="row">
      <?php
      require_once("../sidebar.php");
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">
          <?php //se non sono stato chiamato da nessun'altra pagina, torno al login (vedi gestioneEsercizio.php per esempio di chiamata)
          if (!isset($_GET["to"])) header("Location: ../login.php");
          else $to = $_GET["to"];
          $pezziTo = preg_split('/(?=[A-Z])/', $to);
          $nomeTo = implode(" ", $pezziTo);
          $nomeTo = ucfirst(strtolower(substr($nomeTo, 0, -4))); //tolgo .php
          $username = $_SESSION['username'];
          if(isset($_GET["text"])) $nomeTo=$_GET["text"];
          $h3 = "$nomeTo ($h3)";

          //query DOCENTE
          if ($permesso == 3) {
            $username = $_SESSION['username'];
            $query = "SELECT nomeCorso, anno, idCorso
          FROM corsi c
          INNER JOIN tipicorsi t on t.idTipoCorso=c.idTipoCorso
          WHERE docente=? AND archiviato=0
          order by anno desc";
            $stmt = $link->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
          } else {
            $username = $_SESSION['username'];
            $query = "SELECT nomeCorso, anno, idCorso
          FROM corsi c
          INNER JOIN tipicorsi tc on tc.idTipoCorso=c.idTipoCorso
          INNER JOIN tutoraggio t on t.idTipoCorso=tc.idTipoCorso
          WHERE t.tutor=? AND archiviato=0
          order by anno desc";
            $stmt = $link->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
          }
          ?>

                    <h2 class='text-center'>Docente</h2>
          <h3 class='mb-4 mt-5'><?php echo $h3 ?></h3>
          <p>Seleziona il corso per il quale vuoi visualizzare gli esercizi/tesine</p>

          <div class="container-fluid bg-white py-3 mt-2 mb-5 border table-responsive rounded-3">
            <?php
            //TABELLA
            require_once("../filterTable.html"); ?>
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
                  $nomeCorso = str_replace("_", " ", $nomeCorso);
                  $idCorso = $row["idCorso"];
                  echo "<tr>
            <td>$nomeCorso</td>
            <td>$anno</td>
            <td><a class='btn btn-primary' href='$to?idCorso=$idCorso'>Seleziona</a></td>
            </tr>";
                }
                ?>
              </tbody>
            </table>
            <?php
            if ($rowCounter == 0) echo ("<p class='text-center mt-5'>Non hai nessun corso attivo</p>");
            ?>
          </div>



        </div>
      </div>
    </div>
  </div>
</body>

</html>