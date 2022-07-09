<?php
$h3 = "Dettagli corso";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid'])) {
  header("Location: ../login.php");
  exit();
}
$rowCounter = 0;
if (isset($_GET["idCorso"])) $idCorso = $_GET["idCorso"];
?>

<body>
  <div class="container-fluid">
    <div class="row">
      <?php
      require_once("../sidebar.php");
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">

          <?php
          //query nel caso in cui non ho parametro get
          if (!isset($idCorso)) {
            $query = "SELECT nomeCorso, anno, l1.nome nomeDocente, l1.cognome cognomeDocente, GROUP_CONCAT(l2.nome ORDER BY l2.username SEPARATOR '|') nomeTutor, GROUP_CONCAT(l2.cognome ORDER BY l2.username SEPARATOR '|') cognomeTutor, maxStudentiTesine, sitoCorso
            FROM corsi c
            INNER JOIN tipicorsi tc ON tc.idTipoCorso=c.idTipoCorso
            LEFT JOIN tutoraggio t on tc.idTipoCorso=t.idTipoCorso
            INNER JOIN login l1 on l1.username=tc.docente
            LEFT JOIN login l2 on l2.username=t.tutor
            WHERE archiviato=0
            GROUP BY idCorso
            ORDER BY anno,nomeCorso
            ";
            $result = $link->query($query);
          }
          //altrimenti
          else {
            $query = "SELECT nomeCorso, anno, l1.nome nomeDocente, l1.cognome cognomeDocente, GROUP_CONCAT(l2.nome ORDER BY l2.username SEPARATOR '|') nomeTutor, GROUP_CONCAT(l2.cognome ORDER BY l2.username SEPARATOR '|') cognomeTutor, maxStudentiTesine, sitoCorso
            FROM corsi c
            INNER JOIN tipicorsi tc ON tc.idTipoCorso=c.idTipoCorso
            LEFT JOIN tutoraggio t on tc.idTipoCorso=t.idTipoCorso
            INNER JOIN login l1 on l1.username=tc.docente
            LEFT JOIN login l2 on l2.username=t.tutor
            WHERE archiviato=0 and idCorso=?
            GROUP BY idCorso
            ORDER BY anno,nomeCorso
            ";

            $stmt = $link->prepare($query);
            $stmt->bind_param("i", $idCorso);
            $stmt->execute();
            $result = $stmt->get_result();
          }
          ?>

                    <?php if($_SESSION["permesso"]==3) echo "<h2 class='text-center'>Docente</h2>"; ?>
          <h3 class='mb-4 mt-5'><?php echo $h3 ?></h3>
          <p>Qui trovi tutti i dati relativi ad ogni corso
            <br> Puoi trovare maggiori informazione consultando il sito web fornito dal professore del corso
          </p>
          <div class="container-fluid bg-white mt-2 mb-5 border table-responsive py-5 rounded-3">
            <?php
            //TABELLA
            if (!isset($idCorso)) require_once("../filterTable.html"); ?>
            <table id="myTable" class="table table-xl table-hover text-center py-5 align-middle border border-dark">
              <thead>
                <tr>
                  <th scope="col">Nome corso</th>
                  <th scope="col">Anno</th>
                  <th scope="col">Docente</th>
                  <th scope="col">tutor</th>
                  <th scope="col">Tesine</th>
                  <th scope="col">Sito del corso</th>
                </tr>
              </thead>
              <tbody class="bg-lighter">
                <?php
                while ($row = $result->fetch_assoc()) {
                  $rowCounter++;
                  $docente = $row["nomeDocente"] . " " . $row["cognomeDocente"];
                  $nomiTutor=explode("|", $row["nomeTutor"]);
                  $cognomiTutor=explode("|", $row["cognomeTutor"]);
                  $tutor="";
                  $i=0;
                  foreach($nomiTutor as $nomeTutor){
                    $cognomeTutor=$cognomiTutor[$i];
                    $tutor=$tutor."$nomeTutor $cognomeTutor<br>";
                    $i++;
                  }
                  
                  $anno = $row["anno"];
                  $nomeCorso = $row["nomeCorso"];
                  $nomeCorso = str_replace("_"," ",$nomeCorso);
                  $maxStudentiTesine = $row["maxStudentiTesine"];
                  if ($maxStudentiTesine > 0) $tesine = "<i class='bi bi-check display-6' style='color:black'></i>";
                  else $tesine = "<i class='bi bi-x display-6' style='color:black'></i>";
                  $sito = $row["sitoCorso"];

                  echo "<tr>
            <td>$nomeCorso</td>
            <td>$anno</td>
            <td>$docente</td>
            <td>$tutor</td>
            <td>$tesine</td>
            <td><a href='$sito' target='_blank'>$sito</a></td>
            </tr>";
                }
                ?>
              </tbody>
            </table>
            <?php
            if ($rowCounter == 0) echo ("<p class='text-center mt-5'>Non c'è nessun corso disponibile</p>");
            ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</body>

</html>