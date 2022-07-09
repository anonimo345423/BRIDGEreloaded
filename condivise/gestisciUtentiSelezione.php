<?php
$h3 = "Gestisci utenti (selezione utente)";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || $permesso < 3) {
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
        FROM login
        ORDER BY permesso desc, cognome, username";
          $result = $link->query($query);
          if (isset($_GET["msgShow"]) && $_GET["msgShow"] = 1) echo '<br><div class="alert alert-success" role="alert">
        Utente eliminato corretamente!
        </div>';
          ?>

                    <?php if($_SESSION["permesso"]==3) echo "<h2 class='text-center'>Docente</h2>"; ?>
          <h3 class='mb-4 mt-5'><?php echo $h3 ?></h3>
          <p>Seleziona l'utente del quale vuoi modificare i dati, usa la <strong>Matricola</strong> (o l'username) nella campo di ricerca</p>

          <div class="container-fluid bg-white py-3 mt-2 mb-5 border table-responsive rounded-3">
            <?php
            //TABELLA
            require_once("../filterTable.html"); ?>
            <table id="myTable" class="table table-lg table-hover text-center align-middle border border-dark">
              <thead>
                <tr>
                  <th scope="col">Matricola</th>
                  <th scope="col">Nome Cognome</th>
                  <th scope="col">Permesso</th>
                  <th scope="col"></th>
                </tr>
              </thead>
              <tbody class="bg-lighter">
                <?php
                while ($row = $result->fetch_assoc()) {
                  $rowCounter++;
                  $nomeCognome = $row["nome"] . " " . $row["cognome"];
                  $username = $row["username"];
                  $permesso = $row["permesso"];

                  if ($_SESSION["permesso"] == 3 && $permesso > 2) continue; //per evitare che un docente possa modificare admin o altri docenti

                  if ($permesso == 1) $permesso = "Utente";
                  if ($permesso == 2) $permesso = "Tutor";
                  if ($permesso == 3) $permesso = "Docente";
                  if ($permesso == 4) $permesso = "Admin";
                  echo "<tr>
            <td>$username</td>
            <td>$nomeCognome</td>
            <td>$permesso</td>
            <td><a class='btn btn-primary' href='gestisciUtenti.php?username=$username'>Seleziona</a></td>
            </tr>"; 
                }
                ?>
              </tbody>
            </table>
            <?php
            if ($rowCounter == 0) echo ("<p class='text-center mt-5'>Non ci sono utenti</p>");
            ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</body>

</html>