<?php
$h3 = "Iscrizione corsi";
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

          <?php
          //POST: gestione form
          if (isset($_POST["iscriviti"])) {
            $idCorso = $_POST["iscriviti"];
            $username = $_SESSION["username"];
            $query = "INSERT INTO iscritto (idCorso, username) VALUES (?, ?)";
            $stmt = $link->prepare($query);
            $stmt->bind_param("ss", $idCorso, $username);
            $result = $stmt->execute();
          }
          //CONFERMA messaggio se c'è stato post prima
          if (isset($result) && $result == true) echo ("<div class='alert alert-success' role='alert'>
        Sei stato iscritto al corso! Ora puoi <a href='consegnaEsercizi.php?idCorso=$idCorso'>consegnare gli esercizi</a>
      </div>");
          else if (isset($result) && $result == false) echo ('<div class="alert alert-danger" role="alert">
      Errore nell\'iscrizione al corso
    </div>');
          //QUERY: seleziona i corsi e i relativi dati da tipicorsi tali che l'utente non sia già iscritto a quel corso, inoltre fa inner join su login per dati docente
          $username = $_SESSION['username'];
          $query = "SELECT *
        FROM corsi c
        INNER JOIN tipicorsi t ON t.idTipoCorso=c.idTipoCorso
        INNER JOIN login l on l.username=t.docente
        WHERE archiviato=0 AND c.idCorso NOT IN (
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
          <p>Qui puoi iscriverti ad un corso, è necessario iscriversi per poter consegnare esercizi ed eventuali tesine per quel corso!
            <br>Per ulteriori dettagli su un corso cliccare sul suo nome
            <br>Puoi inoltre filtrare per nome del corso nel campo di ricerca
          </p>

          <div class="container-fluid bg-white py-3 mt-2 mb-5 border table-responsive rounded-3">
            <?php
            //TABELLA
            require_once("../filterTable.html");
            $self = $_SERVER['PHP_SELF'];
            ?>
            <form action="<?php echo "$self"; ?>" method="post">
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
                    if(empty($anno)) $anno="-";
                    $nomeCorso = $row["nomeCorso"];
                    $nomeCorso = str_replace("_"," ",$nomeCorso);
                    $idCorso = $row["idCorso"];
                    echo "<tr>
            <td><a class='p-3' style='text-decoration: none; '  href='../condivise/dettagliCorso.php?idCorso=$idCorso'>$nomeCorso</a></td>
            <td>$anno</td>
            <td>$docente</td>
            <td><button class='btn btn-primary' type='submit' name='iscriviti' value='$idCorso'>Iscriviti</button></td>
            </tr>";
                  }
                  ?>
                </tbody>
              </table>
            </form>
            <?php
            if ($rowCounter == 0) echo ("<p class='text-center mt-5'>Non ci sono altri corsi a cui puoi iscriverti</p>");
            ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</body>

</html>