<?php
$h3 = "Selezione corso";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || $permesso != 1) header("Location: ../login.php");
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
          $nomeTo = ucfirst(strtolower($nomeTo)); //tolgo .php
          $username = $_SESSION['username'];
          if(isset($_GET["text"])) $nomeTo=$_GET["text"];
          $h3 = "$nomeTo ($h3)";

          //query opposta ad iscrizioneCorso.php
          $username = $_SESSION['username'];
          $query = "SELECT *
        FROM corsi c
        INNER JOIN tipicorsi t ON t.idTipoCorso=c.idTipoCorso
        INNER JOIN login l on l.username=t.docente
        WHERE archiviato=0 AND c.idCorso IN (
            SELECT idCorso
            FROM iscritto
            WHERE username=?
        )
        ORDER BY anno desc,nomeCorso";
          $stmt = $link->prepare($query);
          $stmt->bind_param("s", $username);
          $stmt->execute();
          $result = $stmt->get_result();

          ?>

                    
          <h3 class='mb-4 mt-5'><?php echo $h3 ?></h3>
          <?php
          if ($to=="consegnaEsercizi") echo '<p>Per quale corso vuoi consegnare gli esercizi?</p>';
          else if ($to=="gestioneEsercizi") echo'<p>Per quale corso vuoi gestire i tuoi esercizi?</p>';
          else if ($to=="listaTesine") echo'<p>Per quale corso vuoi vedere le tesine disponibili?</p>';
          else if ($to=="tesinaAssegnata") echo'<p>Per quale corso vuoi vedere la tua tesina?</p>';
          else echo '<p>Per quale corso vuoi visualizzare gli esercizi/tesine?</p>';
          ?>
          <div class="container-fluid bg-white py-3 mt-2 mb-5 border table-responsive rounded-3">
            <?php
            //TABELLA
            require_once("../filterTable.html"); ?>
            <table id="myTable" class="table table-lg table-hover text-center align-middle border border-dark">
              <thead>
                <tr>
                  <th scope="col">Nome corso</th>
                  <th scope="col">Anno</th>
                  <th scope="col">Docente</th>
                  <th scope="col"></th>
                </tr>
              </thead>
              <tbody class="bg-lighter">
                <?php
                while ($row = $result->fetch_assoc()) {
                  $rowCounter++;
                  $docente = $row["nome"] . " " . $row["cognome"];
                  $anno = $row["anno"];
                  $nomeCorso = $row["nomeCorso"];
                  $nomeCorso = str_replace("_"," ",$nomeCorso);
                  $idCorso = $row["idCorso"];
                  echo "<tr>
            <td><a class='p-3' style='text-decoration: none; '  href='../condivise/dettagliCorso.php?idCorso=$idCorso'>$nomeCorso</a></td>
            <td>$anno</td>
            <td>$docente</td>
            <td><a class='btn btn-primary' href='$to.php?idCorso=$idCorso'>Seleziona</a></td>
            </tr>"; 
                }
                ?>
              </tbody>
            </table>
            <?php
            if ($rowCounter == 0) echo ("<p class='text-center mt-5'>Non sei <a href='iscrizioneCorso.php'>iscritto</a> a nessun corso</p>");
            ?>
          </div>



        </div>
      </div>
    </div>
  </div>
</body>

</html>