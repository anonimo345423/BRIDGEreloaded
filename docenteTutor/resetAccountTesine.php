<?php
$h3 = "Reset account tesine";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || $permesso != 3) {
  header("Location: ../login.php");
  exit();
}
$username = $_SESSION['username'];
$msgDisplay = 0;
?>
<html>

<body>
  <div class="container-fluid">
    <div class="row">
      <?php
      require_once("../sidebar.php");
      //POST
      if (isset($_POST["reset"])) { //tolgo tutti gli account in studentitesina che iniziano con $nomeAccount
        $nomeAccount = $_POST["nomeAccount"];
        if(!empty($nomeAccount)){
          $query = "UPDATE studentitesina
          SET account=null
          WHERE account LIKE '$nomeAccount%'";
          $result = $link->query($query);
          if ($result == true) $msgDisplay = 1;
          else $msgDisplay = 2;
        }
        else $msgDisplay=2;
      }
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">
          <?php if ($msgDisplay == 1) echo "<div class='alert alert-success' role='alert'>
        Reset completato!
       </div>";
          if ($msgDisplay == 2) echo '<div class="alert alert-danger" role="alert">
         Errore nel reset, &egrave; probabile che questo tipo di corso non abbia account assegnati
        </div><br>';
          ?>
          <h2 class='text-center'>Docente</h2>
          <h3><?php echo $h3 ?></h3>
          <p><strong>ATTENZIONE:</strong> premendo il pulsante "reset" verranno tolti tutti gli account assegnati fino ad ora agli studenti per le loro tesine (solo per il tipo di corso scelto) </p>

          <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <label for="nomeAccount">Seleziona tipo di corso</label>
            <select class="form-select my-5" id="nomeAccount" name="nomeAccount" required>
              <option selected disabled style="display:none" value=""></option>
              <?php
              //qui ci sono i tipoCorsi che ha il docente
              $query = "SELECT nomeAccount, nomeCorso, maxStudentiTesine
                FROM tipicorsi
                WHERE docente='$username'
                ";
              $result = $link->query($query);
              while ($row = $result->fetch_assoc()) {
                extract($row, EXTR_OVERWRITE);
                $nomeCorso = str_replace("_"," ",$nomeCorso);
                if ($maxStudentiTesine != 0) echo ("<option value='$nomeAccount'>$nomeCorso</option>");
              }
              ?>
            </select>
            <input class="btn btn-danger" type="submit" name="reset" value="Reset" />
          </form>
        </div>

      </div>
    </div>
  </div>
</body>

</html>