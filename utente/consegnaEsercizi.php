<?php //questa pagina sarà strutturata nel seguente modo, se non c'è in get l'idCorso header a pagina di selezioneCorso, la quale avrà una tabella coi corsi a cui si è iscritti e
//come parametro get la pagina a cui tornare dopo. selezioneCorso allora avrà un anchor per ogni corso che rimanda alla pagina suddetta, con parametro get=idCorso, quindi esempio:
//entro in gestioneEsercizio.php senza get, vengo reinderizzato in selezioneCorso?to=gestioneEsercizio, scelgo il corso e mi rimanda a $to.php?idCorso=$idCorso
$h3 = "Consegna esercizi";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || $permesso != 1) {
  header("Location: ../login.php");
  exit();
}
$rowCounter = 0;
?>

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
          //se non ho l'idCorso, lo ottengo da selezioneCorso
          $self = basename(__FILE__, '.php'); //nome del file php
          if (!isset($_GET["idCorso"])) {
            header("Location: selezioneCorso.php?to=$self");
            exit();
          } else $idCorso = $_GET["idCorso"];
          $username = $_SESSION['username'];


          //QUERY nome corso
          $query = "SELECT nomeCorso,anno,estensione,categorie cat
        FROM corsi c
        INNER JOIN tipicorsi tc on tc.idTipoCorso=c.idTipoCorso
        WHERE idCorso=?";
          $stmt = $link->prepare($query);
          $stmt->bind_param("i", $idCorso);
          $stmt->execute();
          $result2 = $stmt->get_result();
          $row = $result2->fetch_assoc();
          $nomeCorso = $row["nomeCorso"];
          $anno = $row["anno"];
          $estensioneQuery = $row["estensione"];
          $estensioneQueryArr=explode("|",$estensioneQuery);
          foreach ($estensioneQueryArr as &$value) {
            $value = '.' . $value;
          }
          $estensioneVirgola=implode(",",$estensioneQueryArr);

          $numCategorie= count(explode("|",$row["cat"]));

          //QUERY controllo che utente sia iscritto al corso e che il corso non sia archiviato
          $query = "SELECT *
        FROM iscritto i
        INNER JOIN login l on l.username=i.username
        INNER JOIN corsi c on i.idCorso=c.idCorso
        WHERE c.idCorso=? and i.username=? AND archiviato=0";
          $stmt = $link->prepare($query);
          $stmt->bind_param("is", $idCorso, $username);
          $stmt->execute();
          $result3 = $stmt->get_result();
          if ($result3->num_rows == 0) header("Location: ../login.php");
          else {
            $row = $result3->fetch_assoc();
            $nome = $row["nome"];
            $cognome = $row["cognome"];
          }


          //POST
          if (isset($_POST["consegna"])) {
            $idTipoEsercizio = $_POST['radio'];
            $date = date("Y-m-d H:i:s");
            $query = "INSERT INTO esercizi (idTipoEsercizio, username, idCorso, dataOra) VALUES (?, ?, ?, '$date')";
            $stmt = $link->prepare($query);
            $stmt->bind_param("isi", $idTipoEsercizio, $username, $idCorso); //l'execute lo faccia dopo
            //inserisco anche notifica per docente
            $query = "UPDATE corsi SET notifica=1 WHERE idCorso=?";
            $stmtNotifica = $link->prepare($query);
            $stmtNotifica->bind_param("i", $idCorso);
            $stmtNotifica->execute();

            //query per la categoria
            $query = "SELECT categoria
          FROM tipiesercizi
          WHERE idTipoEsercizio=?";
            $stmt2 = $link->prepare($query);
            $stmt2->bind_param("i", $idTipoEsercizio);
            $stmt2->execute();
            $result4 = $stmt2->get_result();
            $row = $result4->fetch_assoc();
            $categoria = $row["categoria"];

            //file
            if($anno!="") $nomeCorso="$nomeCorso.$anno";
            $dir = "../corsi/$nomeCorso/";
            $nameFile = $categoria . "." . $cognome . "." . $nome . "." . $username . "." . $idTipoEsercizio;
            if (!is_dir($dir)) mkdir($dir); //crea cartella del corso se non esiste
            $from = $_FILES["fileUpload"]["tmp_name"];
            $estensione = "." . pathinfo($_FILES["fileUpload"]["name"], PATHINFO_EXTENSION);
            $to = $dir . $nameFile . $estensione;

            if (array_key_exists('fileUpload', $_FILES) && in_array($estensione,$estensioneQueryArr)) {
              $stmt->execute();
              move_uploaded_file($from, $to);
              echo ("<div class='alert alert-success' role='alert'>
                    L'esercizio è stato consegnato!
                  </div>");
            } else echo ("<div class='alert alert-danger' role='alert'>
                      Qualcosa non va con il file, controlla l'estensione!
                    </div>");
          }




          //QUERY esericizi, prende tutti gli esercizi per quell'idCorso, tranne quelli obbligatori(=1) e di categoria tale che:
          //esistono altri esercizi della stessa categoria già consegnati (stato 1/3) per quell'idCorso
          //nel primo NOT IN tolgo gli esercizi obbligatori che hanno la stessa categoria di un esercizio già svolto
          //nel secondo tolgo quelli già svolti (quindi i facoltativi)
          $query = "SELECT idTipoEsercizio,testo,categoria,obbligatorio
        FROM corsi c
        INNER JOIN tipiesercizi te ON te.idTipoCorso=c.idTipoCorso
        WHERE idCorso=? AND disabilitato=0 AND (categoria,obbligatorio) NOT IN (
            SELECT categoria,obbligatorio
            FROM tipiesercizi t
            INNER JOIN esercizi e ON e.idTipoEsercizio=t.idTipoEsercizio
            WHERE t.obbligatorio=1 and username=? and e.idCorso=?
            )
            AND idTipoEsercizio NOT IN (
              SELECT idTipoEsercizio
              FROM esercizi
              WHERE username=? and idCorso=?
              )
        ORDER BY categoria,obbligatorio desc";
          $stmt = $link->prepare($query);
          $stmt->bind_param("isisi", $idCorso, $username, $idCorso, $username, $idCorso);
          $stmt->execute();
          $result = $stmt->get_result();


          $nomeCorsoLeggibile = str_replace("_"," ",$nomeCorso);
          echo ("<h3 class='mb-4 mt-5'>$h3 $nomeCorsoLeggibile $anno</h3>");
          $self = $_SERVER['PHP_SELF'] . "?idCorso=$idCorso"; //come action del form qui sotto rivado a me stesso, con tanto di parametro get idCorso altrimenti la pagina non si aprirebbe
          echo ("<form action='$self' method='post' enctype='multipart/form-data'>");
          ?>

          <p>Ci sono degli esercizi obbligatori ed altri facoltativi, il tuo compito &egrave; svolgere un esercizio obbligatorio per ognuna delle <strong><?php echo $numCategorie ?></strong> categorie.
            <br>Gli esercizi facoltativi sono invece a tua discrezione. Farli non e' pericoloso, potrebbe invece essere utile!<br>
            Ci possono essere piu' esercizi "obbligatori" per un medesimo argomento: in tal caso scegline uno<br><br>
            <?php
            $queryIstruz = "SELECT istruzioni FROM corsi WHERE idCorso=$idCorso";
            $resultIstruz = $link->query($queryIstruz);
            $rowIstruz = $resultIstruz->fetch_assoc();
            if (!empty($rowIstruz)) extract($row, EXTR_OVERWRITE);
            if (!empty($istruzioni)) {
              //$istruzioni = str_replace("<br>", "\n", $istruzioni);
              //$istruzioni = str_replace("<br />", "", $istruzioni);
              echo "<strong>Istruzioni del corso:<br></strong>$istruzioni";
            }
            ?>
          </p>
          <div class="container-fluid bg-white mt-2 mb-5 border table-responsive py-5 rounded-3 ">
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

                while ($row = $result->fetch_assoc()) {
                  $rowCounter++;
                  $testo = $row["testo"];
                  $categoria = $row["categoria"];
                  $categoriaSenzaNumero=substr($categoria, 1);
                  $obbligatorio = $row["obbligatorio"];
                  $idTipoEsercizio = $row["idTipoEsercizio"];
                  if ($obbligatorio > 0) $obbligatorio = "<i class='bi bi-check display-6' style='color:green'></i>";
                  else $obbligatorio = "<i class='bi bi-x display-6' style='color:red'></i>";

                  echo "
          <tr>
          <td>
          <div class='form-check'>
            <input class='form-check-input' type='radio' name='radio' value='$idTipoEsercizio' required>
          </div>
          </td>
          <td>$categoriaSenzaNumero</td>
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
            <?php
            if ($rowCounter == 0) echo ("<p class='text-center mt-5'>Non c'è nessun esercizio disponibile</p>");
            else {
              $estensioneSpazio=str_replace(","," </strong>/<strong> ",$estensioneVirgola);
              echo ("<p class='pt-5 pb-3'>
          Seleziona uno degli esercizi con il pallino alla sua sinistra, carica il file corrispondente e premi \"Consegna\"<br>
          Se devi consegnare una cartella, comprimila e poi fai l'upload<br>
          Se una volta inserito l'esercizio vorrai fare dei cambiamenti, ti basterà selezionare dal men&ugrave; <a href='gestioneEsercizi.php?idCorso=$idCorso'>esercizi consegnati</a> e fare la riconsegna o l'eliminazione</p>
          <div class='row py-2'>
            <div class='col'>
              <input class='form-control' type='file' name='fileUpload' accept='$estensioneVirgola' required>
            </div>
          </div>
            Per questo corso devi consegnare file che abbiano estensione <strong> $estensioneSpazio </strong>
            <div class='col-2 mt-4'>
              <button class='btn btn-primary' type='submit' name='consegna'>Consegna</button>
            </div>
          </div>
          </form>
          ");
            }
            ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</body>

</html>