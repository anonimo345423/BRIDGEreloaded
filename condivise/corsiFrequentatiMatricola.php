<?php
$h3 = "Corsi frequentati da studenti e disiscrizione";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];

if (!isset($_SESSION['valid']) || $permesso < 3) {
  header("Location: ../login.php");
  exit();
}
$username = $_SESSION['username'];

$rowCounter = 0;
$msgFlag = 0;
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
          $arr = explode("|", $name); //divido il name del post che contiene le 2 info username|idCorso
          $usernameStudente = $arr[0];
          $idCorso = $arr[1];
          $query = "DELETE from iscritto WHERE idCorso=$idCorso AND username='$usernameStudente'";
          $result = $link->query($query);
          if ($result) $msgFlag = 1;
          else $msgFlag = 2;
          //si potrebbero eliminare i file che hanno nel nome cognome e nome dello studente (nella cartella dell'idCorso corrispondente) ma alla fine a livello di funzionalità non serve
          //perchè tanto nel database viene eliminato tutto e quindi quei file restano solo lì morti
        }
      }
      //FINE POST
      //CHECK: creo un array degli idCorso su cui il docente sono io e poi lo uso dopo con la funzione in_array(), lo faccio solo se sono un docente, ossia permesso=3
      $idCorsi = [];
      if ($permesso == 3) {
        $query = "SELECT idCorso
        FROM corsi c
        INNER JOIN tipicorsi t ON t.idTipoCorso=c.idTipoCorso
        WHERE docente='$username'";
        $result = $link->query($query);
        while ($row = $result->fetch_assoc()) {
          array_push($idCorsi, $row["idCorso"]);
        }
      }
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">

          <?php
          $query = "SELECT nome,cognome,l.username username,i.idCorso idCorso,nomeCorso,anno
        FROM login l
        INNER JOIN iscritto i on i.username=l.username
        INNER JOIN corsi c on i.idCorso=c.idcorso
        INNER JOIN tipicorsi t on c.idTipoCorso=t.idTipoCorso
        WHERE archiviato=0
        ORDER BY cognome, l.username";
          $result = $link->query($query);
          if ($msgFlag == 1) echo '<div class="alert alert-success" role="alert">
          Disiscritto con successo
         </div><br>';
          if ($msgFlag == 2) echo '<div class="alert alert-danger" role="alert">
         Errore nella disiscrizione
        </div><br>';
          ?>

                    <?php if($_SESSION["permesso"]==3) echo "<h2 class='text-center'>Docente</h2>"; ?>
          <h3 class='mb-4 mt-5'><?php echo $h3 ?></h3>
          <p><strong>ATTENZIONE:</strong> procedere con cautela nella disiscrizione in quanto permanente
            <br>
          </p>

          <div class="container-fluid bg-white py-3 mt-2 mb-5 border table-responsive rounded-3">
            <?php
            //TABELLA
            require_once("../filterTable.html"); ?>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
              <table id="myTable" class="table table-lg table-hover text-center align-middle border border-dark">
                <thead>
                  <tr>
                    <th scope="col">Matricola</th>
                    <th scope="col">Nome Cognome</th>
                    <th scope="col">Corso</th>
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody class="bg-lighter">
                  <?php
                  while ($row = $result->fetch_assoc()) {
                    $rowCounter++;
                    $nomeCognome = $row["nome"] . " " . $row["cognome"];
                    $username = $row["username"];
                    $idCorso = $row["idCorso"];
                    $nomeCorso = str_replace("_"," ",$row["nomeCorso"]);
                    $nomeCorso = $nomeCorso . " " . $row["anno"];
                    echo "<tr>
            <td>$username</td>
            <td>$nomeCognome</td>
            <td>$nomeCorso</td>";
                    if (in_array($idCorso, $idCorsi) || $permesso == 4) echo "<td><input class='btn btn-danger' type = 'submit' name = '$username|$idCorso' value='Disiscrivi'/></td>"; //dà pulsante disiscrizione solo se sei admin o docente con quel corso
                    else echo "<td></td>";
                    echo "</tr>"; //qui sopra ho detto che se il permesso è 4 (admin) oppure è un docente ed il corso è suo, allora dai delete button
                  }
                  ?>
                </tbody>
              </table>
            </form>
            <?php
            if ($rowCounter == 0) echo ("<p class='text-center mt-5'>Non ci sono utenti iscritti a nessun corso</p>");
            ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</body>

</html>