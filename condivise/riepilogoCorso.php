<?php
$h3 = "Riepilogo corso";
$rowCounter = 0;
$msgFlag = 0;
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || $permesso < 2 || $permesso == 4) {
  header("Location: ../login.php");
  exit();
}
$username = $_SESSION['username'];
if (!isset($_GET["idCorso"])) {
  header("Location: ../docenteTutor/selezioneCorso.php?to=../condivise/riepilogoCorso.php&text=Riepilogo Corso");
  exit();
}
$idCorso = $_GET["idCorso"];
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
          $username = $_SESSION['username'];
          $query="SELECT nomeCorso,anno FROM corsi c INNER JOIN tipicorsi t on t.idTipoCorso=c.idTipoCorso WHERE idCorso=$idCorso";
          $result = $link->query($query);
          $row = $result->fetch_assoc();
          extract($row, EXTR_OVERWRITE);
          $nomeCorso=str_replace("_"," ",$nomeCorso);
          ?>

          <?php if ($_SESSION["permesso"] == 3) echo "<h2 class='text-center'>Docente</h2>"; ?>
          <h3 class='mb-4 mt-5'><?php echo "$h3 ($nomeCorso $anno)"; ?></h3>

          <div class="container-fluid bg-white py-3 mt-2 mb-5 border table-responsive rounded-3">
            <?php
            //TABELLA corsi da archiviare
            require_once("../filterTable.html"); ?>
            <?php
            $query = "SELECT nome,cognome,l.username,mail,count(l.username) totali, accettati
            FROM esercizi e
            INNER JOIN login l on e.username=l.username
            LEFT JOIN
            (SELECT count(l.username) accettati, l.username user
            FROM esercizi e
            INNER JOIN login l on e.username=l.username
            WHERE idCorso=$idCorso and stato=3
            group by l.username) e2 on l.username=user
            WHERE idCorso=$idCorso
            group by l.username
            UNION ALL
            SELECT nome,cognome,l.username,mail,0,0
            FROM login l
            INNER JOIN iscritto i on l.username=i.username
            WHERE permesso=1 and idCorso=$idCorso and l.username NOT IN (
              SELECT l.username
              FROM esercizi e
              INNER JOIN login l on e.username=l.username
              LEFT JOIN
              (SELECT count(l.username) accettati, l.username user
              FROM esercizi e
              INNER JOIN login l on e.username=l.username
              WHERE idCorso=$idCorso and stato=3
              group by l.username) e2 on l.username=user
              WHERE idCorso=$idCorso
              group by l.username
                )
            order by totali desc,accettati desc
            ";
            $result = $link->query($query);
            ?>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
              <table id="myTable" class="table table-lg table-hover text-center align-middle border border-dark">
                <thead>
                  <tr>
                    <th scope="col">Nome</th>
                    <th scope="col">Cognome</th>
                    <th scope="col">Matricola</th>
                    <th scope="col">Mail</th>
                    <th scope="col">Esercizi svolti</th>
                    <th scope="col">Esercizi accettati</th>
                  </tr>
                </thead>
                <tbody class="bg-lighter">
                  <?php
                  while ($row = $result->fetch_assoc()) {
                    $rowCounter++;
                    extract($row, EXTR_OVERWRITE);
                    if ($accettati == null) $accettati = 0;
                    echo "<tr>
            <td>$nome</td>
            <td>$cognome</td>
            <td>$username</td>
            <td><a href='mailto:$mail'>$mail</a></td>
            <td>$totali</td>
            <td>$accettati</td>
            </tr>";
                  }
                  ?>
                </tbody>
              </table>


            </form>
            <?php if ($rowCounter == 0) echo ("<p class='text-center mt-5'>Nessun esercizio consegnato</p>"); ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</body>

</html>