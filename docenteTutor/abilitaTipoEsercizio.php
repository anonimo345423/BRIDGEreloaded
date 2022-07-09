<?php
$h3 = "Abilita tipi esercizi";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
$flag=0;
if (!isset($_SESSION['valid']) || $permesso != 3) {
  header("Location: ../login.php");
  exit();
}
if (!isset($_GET["idTipoCorso"])) {
  header("Location: selezioneTipoCorso.php?to=abilitaTipoEsercizio.php");
  exit();
}
$username = $_SESSION['username'];
$idTipoCorso = $_GET["idTipoCorso"];
$rowCounter = 0;
//POST
if (isset($_POST["conferma"])){
  $query="UPDATE tipiesercizi SET disabilitato=2 WHERE idTipoCorso=$idTipoCorso AND disabilitato=0"; //disabilitato ad 1 significa che l'eserc è stato "eliminato" da gestioneTipiEsercizi.php, a 2 disabilitato
  $link->query($query);
  if(isset($_POST["checkbox"])){ //se è vuoto vuol dire che è tutto disabilitato
    $checkbox=$_POST["checkbox"]; //array con tipiesercizi scelti da tenere attivi
    foreach ($checkbox as $idTipoEserc){
      $query="UPDATE tipiesercizi SET disabilitato=0 WHERE idTipoEsercizio=$idTipoEserc"; //perchè quelli che sono nel checkbox li voglio attivi
      $link->query($query);
    }
    $flag=1;
  }
}
?>
<html>

<body>
  <script src="../js/jquery.min.js"></script>
	<script src="../js/showHideTesto.js"></script>
  <div class="container-fluid">
    <div class="row">
      <?php
    require_once("../sidebar.php");
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">
          <?php
          if($flag==1) echo ("<div class='alert alert-success' role='alert'>
          Esercizi abilitati con successo!
        </div>");
        echo "<h2 class='text-center'>Docente</h2>";
        $query="SELECT nomeCorso FROM tipicorsi WHERE idTipoCorso=$idTipoCorso";
          $result = $link->query($query);
          $row = $result->fetch_assoc();
          extract($row, EXTR_OVERWRITE);
          $nomeCorso=str_replace("_"," ",$nomeCorso);

          echo ("<h3 class='mb-5 mt-5'>$h3 ($nomeCorso)</h3>");
          $self = $_SERVER['PHP_SELF'] . "?idTipoCorso=$idTipoCorso";
          echo ("<form action='$self' method='post'>");
          ?>
          <table id="" class="table table-xl table-hover text-center align-middle border border-dark">
            <thead>
              <tr>
                <th scope="col">Seleziona</th>
                <th scope="col">Categoria</th>
                <th scope="col">Obbligatorio</th>
                <th scope="col">Testo</th>

              </tr>
            </thead>
            <tbody class="bg-lighter">

              <?php
              //FORM e tabella
              $query = "SELECT * FROM tipiesercizi WHERE idTipoCorso=$idTipoCorso";
              $result = $link->query($query);
              while ($row = $result->fetch_assoc()) {
                $rowCounter++;
                $testo = $row["testo"];
                $categoria = $row["categoria"];
                $obbligatorio = $row["obbligatorio"];
                $idTipoEsercizio = $row["idTipoEsercizio"];
                if ($obbligatorio > 0) $obbligatorio = "<i class='bi bi-check display-6' style='color:green'></i>";
                else $obbligatorio = "<i class='bi bi-x display-6' style='color:red'></i>";
                $disabilitato=$row["disabilitato"];
                if($disabilitato==0) $ifChecked="checked";
                else $ifChecked="";
                echo "
          <tr>
          <td>
          <div class='form-check'>
            <input class='form-check-input' type='checkbox' name='checkbox[]' value='$idTipoEsercizio' $ifChecked>
          </div>
          </td>
          <td>$categoria</td>
          <td>$obbligatorio</td>
          <td>
          <button class='btn btn-secondary control' id='$idTipoEsercizio' type='button' >Testo</button>
          </td>
          </tr>
          
          <tr class='show_hide' id='testo$idTipoEsercizio'>
          <td colspan=4 class='bg-white' style= 'font-family: courier new; text-align:left !important;'>$testo</td>
          </tr>";
              }
              ?>

            </tbody>
          </table>
          <div class='row pb-5 pt-2'>
            <div class='col-2'>
              <button class='btn btn-primary' type='submit' name='conferma'>Conferma</button>
            </div>
          </div>
          </form>
          <?php
          if ($rowCounter == 0) echo ("<p class='text-center mt-5'>Non c'è nessun esercizio disponibile</p>");
          ?>
        </div>
      </div>
    </div>
  </div>
</body>

</html>