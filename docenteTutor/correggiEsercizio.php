<?php
$h3 = "Correggi esercizio";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || ($permesso != 2 && $permesso != 3)) {
  header("Location: ../login.php");
  exit();
}
?>
<html>

<body>
  <script src="../js/jquery.min.js"></script>
  <script src="../js/showHideTesto.js"></script>
  <script src="../js/textarea.js"></script>
  <div class="container-fluid">
    <div class="row">
      <?php
      require_once("../sidebar.php");
      if (!isset($_GET["idEsercizio"])) { //se non ho idEsercizio, scelgo il corso in selezioneCorso e arrivo qua
        header("Location: selezioneCorso.php?to=selezioneEsercizio.php&text=$h3");
        exit();
      } else $idEsercizio = $_GET["idEsercizio"];
      //QUERY
      $query = "SELECT obbligatorio, categoria, testo, l.username matricola, idCorso, nome, cognome, t.idTipoEsercizio, dataOra, numeroConsegne
    FROM esercizi e
    INNER JOIN tipiesercizi t on t.idTipoEsercizio=e.idTipoEsercizio
    INNER JOIN login l on l.username=e.username
    WHERE idEsercizio=$idEsercizio";
      $result = $link->query($query);
      $row = $result->fetch_assoc();
      extract($row, EXTR_OVERWRITE);


      //GESTIONE POST
      if (isset($_POST["accetta"]) || isset($_POST["rifiuta"])) {
        $riservato = 0; //riservato parte 0, se in post trovo spunta lo metto 1
        if (isset($_POST["accetta"])) $stato = 3; //setto accettazione o meno
        else $stato = 2;
        if (isset($_POST["riservato"])) $riservato = 1;
        if (!empty($_POST["new"])) { //aggiunta nuovo commento
          $commentoNuovo = htmlspecialchars($_POST["new"], ENT_QUOTES);
          $query = "INSERT INTO commentiesercizi(idEsercizio, dataOraCommento,riservato,commento,numeroConsegna)
                    VALUES ($idEsercizio,'$dataOra',$riservato,'$commentoNuovo', $numeroConsegne)";
          $result = $link->query($query); //ho aggiunto il commento con numeroConsegna=numeroConsegne attuali dell'esercizio
        }
        foreach ($_POST as $key => $value) { //query di update degli altri valori post
          if ($key != "new" && $key != "accetta" && $key != "rifiuta" && $key != "riservato" && $key != "voto") { //se è effettivamente un commento vecchio
            if (!empty($value)) { //e non è vuoto
              $value = htmlspecialchars($value, ENT_QUOTES);
              $query = "UPDATE commentiesercizi
                            SET commento='$value'
                            WHERE idCommentoEsercizio=$key";
              $result = $link->query($query);
            } else { //se invece è vuoto, elimina
              $query = "DELETE FROM commentiesercizi
                            WHERE idCommentoEsercizio=$key";
              $result = $link->query($query);
            }
          }
        }
        //a questo punto posso updatare esito esercizio con lo stato:
        if (!empty($_POST["voto"])) $voto = $_POST["voto"];
        else $voto = "null";
        $query = "UPDATE esercizi
        SET voto=$voto, stato=$stato
        WHERE idEsercizio=$idEsercizio";
        $result = $link->query($query);
        if ($result == true) header("Location: selezioneEsercizio.php?idCorso=$idCorso&msgShow=1");
        else header("Location: selezioneEsercizio.php?idCorso=$idCorso&msgShow=2");
        exit();
      }
      //FINE POST
      $query="SELECT nomeCorso,anno FROM corsi c INNER JOIN tipicorsi t on t.idTipoCorso=c.idTipoCorso WHERE idCorso=$idCorso";
          $result = $link->query($query);
          $row = $result->fetch_assoc();
          extract($row, EXTR_OVERWRITE);
          $nomeCorso=str_replace("_"," ",$nomeCorso);
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">

          <h2 class='text-center'>Docente</h2>
          <h3 class='mb-4 mt-5'><?php echo "$h3 ($nomeCorso $anno)"; ?></h3>
          <p class='mb-5'>Se vuoi rimuovere un commento togli tutto il suo testo e sarà rimosso</p>
          <?php
          if ($numeroConsegne == 1) echo ("Questo esercizio &egrave; stato consegnato 1 volta");
          else echo ("Questo esercizio &egrave; stato consegnato $numeroConsegne volte");
          echo ("<br><br>");
          ?>
          <table id="" class="table table-lg table-hover text-center align-middle border border-dark">
            <thead>
              <tr>
                <th scope="col">Matricola</th>
                <th scope="col">Nome</th>
                <th scope="col">Cognome</th>
                <th scope="col">File</th>
                <th scope="col">Testo</th>
              </tr>
            </thead>
            <tbody class="bg-lighter">
              <?php
              //trovo percorso file
              $query = "SELECT anno,nomeCorso,estensione, tipoVoto, correzione, voto
        FROM esercizi e
        INNER JOIN corsi c on e.idCorso=c.idCorso
        INNER JOIN tipicorsi t on c.idTipoCorso=t.idTipoCorso
        WHERE idEsercizio=$idEsercizio";
              $result = $link->query($query);
              $row = $result->fetch_assoc();
              extract($row, EXTR_OVERWRITE);
              if ($tipoVoto == "decimale") $tipoVoto = "10";
              else if ($tipoVoto == "trentesimi") $tipoVoto = "30";
              $estensioneArr=explode("|",$estensione);
              if($anno!="") $nomeCorso="$nomeCorso.$anno";
              foreach($estensioneArr as $estensione){
                $dir = "../corsi/$nomeCorso";
                $file = "$dir/$categoria.$cognome.$nome.$matricola.$idTipoEsercizio.$estensione";
                if (file_exists($file)){
                  $found=1;
                  break;
                }
              }
              //print
              echo "<tr>
        <td>$matricola</td>
        <td>$nome</td>
        <td>$cognome</td>";
        if(isset($found)) echo"<td><a href='$file' download>File</a></td>";
        else echo"<td>File non trovato</td>"; 
        echo"<td>
        <button class='btn btn-secondary control' id='$idTipoEsercizio' type='button' >Testo</button>
        </td>
        </tr>
        
        <tr class='show_hide' id='testo$idTipoEsercizio'>
        <td colspan=5 class='bg-white' style= 'font-family: courier new; text-align:left !important;'>$testo</td>
        </tr>";
              ?>
            </tbody>
          </table>
          <br>
          <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . "?idEsercizio=$idEsercizio"; ?>"
            method="post">
            <?php
            $i = 1;
            //stampo commenti
            $query = "SELECT commento, dataOraCommento, riservato, idCommentoEsercizio, numeroConsegna
        FROM commentiesercizi c
        INNER JOIN esercizi e ON c.idEsercizio=e.idEsercizio
        WHERE e.idEsercizio=$idEsercizio";
            $result = $link->query($query);
            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                extract($row, EXTR_OVERWRITE);
                if ($riservato == 1) $riservatoTesto = "(riservato)";
                else $riservatoTesto = "";
                $dataOraCommentoLeggibile = date("d/m/Y H:i", strtotime($dataOraCommento));
                echo "
                <div class='mb-3'>
                    <label for='$idCommentoEsercizio' class='form-label'>Commento versione $numeroConsegna, $dataOraCommentoLeggibile $riservatoTesto</label>
                    <textarea class='form-control' id='$idCommentoEsercizio' rows='3' name='$idCommentoEsercizio'>$commento</textarea>
                </div>
                ";
                $i++;
              }
            }
            //qui sotto commento nuovo
            echo "
        <div class='mb-3'>
            <label for='new' class='form-label'>Commento nuovo</label>
            <textarea class='form-control' id='new' rows='3' name='new'></textarea>
        </div>
        ";
            echo "<div style='display:flex; align-items: center;'>
            <label for='riservato' class='form-label text-center  mx-2'>Riservato</label>
            <input type = 'checkbox' class='' id='riservato' name = 'riservato'/>
            </div> ";
            echo "<br><br>
            <div class='row text-center'>";
            if ($tipoVoto != "binario")

              echo "
                <div class='form-floating col-3'>
                <input type = 'number' class='form-control' id='voto' name = 'voto' placeholder = 'Voto /$tipoVoto' value='$voto' max='$tipoVoto' min='0'/>
                <label for='voto' class='form-label text-center'>Voto /$tipoVoto</label> 
            </div>
            </div><br>";
            echo "<div class='row text-center'>
            <div class='col-3'></div>";
            echo "<div class='col-3'>
            <input class='btn btn-success' type = 'submit' name = 'accetta' value='Accetta'/>
            </div>
            <div class='col-3'>
            <input class='btn btn-warning text-white' type = 'submit' name = 'rifiuta' value='Da rivedere'/>
            </div>
            </div>
           ";


            echo "</form><br><br>";
            ?>

        </div>
      </div>
    </div>
  </div>
</body>

</html>