<?php
$h3 = "Esercizi consegnati";
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

          <?php //i prossimi pezzi sono come consegnaEsercizi.php
          //se non ho l'idCorso, lo ottengo da selezioneCorso
          $self = basename(__FILE__, '.php'); //nome del file php
          if (!isset($_GET["idCorso"])) {
            header("Location: selezioneCorso.php?to=$self");
            exit();
          } else $idCorso = $_GET["idCorso"];
          $username = $_SESSION['username'];


          //QUERY nome corso
          $query = "SELECT nomeCorso,anno,estensione
          FROM corsi c
          INNER JOIN tipicorsi tc on tc.idTipoCorso=c.idTipoCorso
          WHERE idCorso=?";
          $stmt = $link->prepare($query);
          $stmt->bind_param("i", $idCorso);
          $stmt->execute();
          $result = $stmt->get_result();
          $row = $result->fetch_assoc();
          $nomeCorso = $row["nomeCorso"];
          $anno = $row["anno"];
          $estensioneQuery = $row["estensione"];
          $estensioneQueryArr=explode("|",$estensioneQuery);
          foreach ($estensioneQueryArr as &$value) {
            $value = '.' . $value;
          }
          $estensioneVirgola=implode(",",$estensioneQueryArr);


          //QUERY controllo che utente sia iscritto al corso
          $query = "SELECT *
          FROM iscritto i
          INNER JOIN login l on l.username=i.username
          INNER JOIN corsi c on i.idCorso=c.idCorso
          WHERE c.idCorso=? and i.username=? AND archiviato=0";
          $stmt = $link->prepare($query);
          $stmt->bind_param("is", $idCorso, $username);
          $stmt->execute();
          $result2 = $stmt->get_result();
          if ($result2->num_rows == 0) header("Location: ../login.php");
          else {
            $row = $result2->fetch_assoc();
            $nome = $row["nome"];
            $cognome = $row["cognome"];
          }
          //QUERY maxVoto
          $query = "SELECT tipoVoto
          FROM tipicorsi t
          INNER JOIN corsi c on t.idTipoCorso=c.idTipoCorso
          WHERE idCorso=?";
          $stmt = $link->prepare($query);
          $stmt->bind_param("i", $idCorso);
          $stmt->execute();
          $resultMaxVoto = $stmt->get_result();
          $row = $resultMaxVoto->fetch_assoc();
          $tipoVoto = $row["tipoVoto"];
          if ($tipoVoto == "decimale") $maxVoto = "10";
          else if ($tipoVoto == "trentesimi") $maxVoto = "30";
          //QUERY per esercizi consegnati, inoltre prende i commenti e li concatena dal group by per creare un unico campo diviso da |
          $query = "SELECT GROUP_CONCAT(commento ORDER BY idCommentoEsercizio SEPARATOR '|') commenti,
          GROUP_CONCAT(dataOraCommento ORDER BY idCommentoEsercizio SEPARATOR '|') dateOre,
          GROUP_CONCAT(riservato ORDER BY idCommentoEsercizio SEPARATOR '|') riservati,
          GROUP_CONCAT(numeroConsegna ORDER BY idCommentoEsercizio SEPARATOR '|') numeroConsegna,
          e.idEsercizio, testo, categoria, obbligatorio, t.idTipoEsercizio, stato, riservato, voto
          FROM esercizi e
          INNER JOIN tipiesercizi t ON t.idTipoEsercizio=e.idTipoEsercizio
          LEFT JOIN commentiesercizi c on e.idEsercizio=c.idEsercizio
          WHERE idCorso=? and username=?
          GROUP BY e.idEsercizio
          ORDER BY stato,categoria,obbligatorio desc
";
          $stmt = $link->prepare($query);
          $stmt->bind_param("is", $idCorso, $username);
          $stmt->execute();
          $resultEsercizi = $stmt->get_result();



          //POST RICONSEGNA
          if (isset($_POST["riconsegna"])) {
            $idTipoEsercizio = $_POST['radio'];

            //cerco il numero di consegne attuali per quell'esercizio e l'aumento di 1:
            $query="SELECT numeroConsegne,stato statoAttuale
            FROM esercizi
            WHERE idTipoEsercizio=? AND username=? AND idCorso=?";
            $stmt = $link->prepare($query);
            $stmt->bind_param("isi", $idTipoEsercizio, $username, $idCorso);
            $stmt->execute();
            $resultCons= $stmt->get_result();
            $rowCons = $resultCons->fetch_assoc();
            extract($rowCons, EXTR_OVERWRITE);
            if($statoAttuale==2) $numeroConsegne++; //cioè aumento il numero di consegne solo se l'esercizio era stato rifiutato

            $date = date("Y-m-d H:i:s");
            $query = "UPDATE esercizi set dataOra=?, stato=1, numeroConsegne=? WHERE idTipoEsercizio=? AND username=? AND idCorso=?";
            $stmt = $link->prepare($query);
            $stmt->bind_param("siisi", $date, $numeroConsegne, $idTipoEsercizio, $username, $idCorso);

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


            if (array_key_exists('fileUpload', $_FILES) && in_array($estensione,$estensioneQueryArr)) { //se l'estensione corrisponde a quella del tipoEsercizio
              //prima cancello i possibili vecchi file con tutte le estensioni:
              foreach($estensioneQueryArr as $est){
                $unlink = $dir . $nameFile . $est;
                unlink($unlink);
              }
              move_uploaded_file($from, $to);
              $stmt->execute();
              header("Location: $self.php?idCorso=$idCorso&caricato=1"); //ricarico pagina perchè le query sopra risultato outdated dopo eliminazioni
              exit();
            } else echo ("<div class='alert alert-danger' role='alert'>
                    Qualcosa non va con il file, controlla l'estensione!
                    </div>");
          }

          //POST ELIMINA
          if (isset($_POST["elimina"])) {
            $idTipoEsercizio = $_POST['radio'];
            $query = "DELETE FROM esercizi WHERE idTipoEsercizio=? AND username=? AND idCorso=? ";
            $stmt = $link->prepare($query);
            $stmt->bind_param("isi", $idTipoEsercizio, $username, $idCorso);

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
            $dir = "../corsi/$nomeCorso.$anno/";
            $nameFile = $categoria . "." . $cognome . "." . $nome . "." . $username . "." . $idTipoEsercizio;
            $estensione = $estensioneQuery;
            $file = $dir . $nameFile . $estensione;


            unlink($file); //remove the file
            $stmt->execute();
            echo ("<div class='alert alert-success' role='alert'>
                    L'esercizio è stato eliminato!
                  </div>");
            header("Location: $self.php?idCorso=$idCorso"); //ricarico pagina perchè le query sopra risultato outdated dopo eliminazioni
            exit();
          }

          //inzio FORM
          if (isset($_GET["caricato"])) echo ("<div class='alert alert-success' role='alert'>
          L'esercizio è stato riconsegnato!
        </div>");
        $nomeCorsoLeggibile = str_replace("_"," ",$nomeCorso);
          echo ("<h3 class='mb-4 mt-5'>$h3 $nomeCorsoLeggibile $anno</h3>");
          $self = $_SERVER['PHP_SELF'] . "?idCorso=$idCorso"; //come action del form qui sotto rivado a me stesso, con tanto di parametro get idCorso altrimenti la pagina non si aprirebbe
          echo ("<form action='$self' method='post' enctype='multipart/form-data'>");
          ?>
          <p>Un esercizio &egrave; finito quando viene contrassegnato come "Accettato" in verde<br>
        Se l'esercizio risulta "Da rivedere" probabilmente ci sono dei commenti che spiegano come modificarlo (e poi riconsegnarlo)<br>
</p>
          <div class="container-fluid bg-white mt-2 mb-5 border table-responsive py-5 rounded-3 ">
            <table id="" class="table table-xl table-hover text-center align-middle border border-dark">
              <thead>
                <tr>
                  <th scope="col">Seleziona</th>
                  <th scope="col">Categoria</th>
                  <th scope="col">Obbligatorio</th>
                  <th scope="col">Stato</th>
                  <?php if ($tipoVoto != "Binario") echo "<th scope='col'>Voto</th>"; ?>
                  <th scope="col">Commenti (e numero consegna)</th>
                  <th scope="col">Testo</th>
                </tr>
              </thead>
              <tbody class="bg-lighter">

                <?php
                //FORM e tabella
                $dataOra = ""; //imposto una dataOra vuota per il checkSuccessivo delle dateOre dei commenti
                while ($row = $resultEsercizi->fetch_assoc()) {
                  $rowCounter++;
                  $testo = $row["testo"];
                  $categoria = $row["categoria"];
                  $categoriaSenzaNumero=substr($categoria, 1);
                  $obbligatorio = $row["obbligatorio"];
                  $idTipoEsercizio = $row["idTipoEsercizio"];
                  $stato = $row["stato"];
                  $voto = $row["voto"];

                  if ($stato == 1) $statoScritta = "<strong class='text-secondary'>Inviato</strong>";
                  else if ($stato == 3) $statoScritta = "<strong class='text-success'>Accettato</strong>";
                  else if ($stato == 2) $statoScritta = "<strong class='text-warning'>Da rivedere</strong>";


                  $commenti = $row["commenti"];
                  $commentiArr = explode("|", $commenti); //siccome i commenti nel db si trovano tutti in un campo, li divido col divisore |
                  $dateOre = $row["dateOre"];
                  $dateOreArr = explode("|", $dateOre);
                  $riservati = $row["riservati"];
                  $riservatiArr = explode("|", $riservati);
                  $numeroConsegna = $row["numeroConsegna"];
                  $numeroConsegnaArr = explode("|", $numeroConsegna);

                  $size = count($commentiArr);

                  if ($obbligatorio > 0) $obbligatorio = "<i class='bi bi-check display-6' style='color:black'></i>";
                  else $obbligatorio = "<i class='bi bi-x display-6' style='color:black'></i>";

                  echo "
              <tr>
              <td>";
                  if ($stato != 3) echo "
              <div class='form-check'>
                <input class='form-check-input' type='radio' name='radio' value='$idTipoEsercizio' required>
              </div>";
                  echo "</td>
              <td>$categoriaSenzaNumero</td>
              <td>$obbligatorio</td>
              <td>$statoScritta</td>";
                  if ($tipoVoto != "Binario" && !empty($voto)) echo "<td>$voto/$maxVoto</td>";
                  else echo "<td></td>"; //se non ho ancora voto, non echo niente
                  echo "<td style='text-align:left !important;'>";
                  $versione = 0;
                  $i = 0;
                  while ($i < $size) { //controllo se è cambiata la dataOra del commento, se si avanzo la versione che viene mostrata
                    if (empty($commentiArr)) break;
                    if (!empty($commentiArr[$i]) && $riservatiArr[$i] == 0) {
                      $numero=$numeroConsegnaArr[$i];
                      $commentoDaMostrare=nl2br($commentiArr[$i]);
                      echo "<strong>$numero" . "° consegna</strong>:<br> $commentoDaMostrare<br><br>"; //stampo solo se il commento non è riservato (=0)
                    }
                    $i++;
                  }
                  echo "</td>
                  <td>
                  <button class='btn btn-secondary control' id='$idTipoEsercizio' type='button' >Testo</button>
                  </td>
                  </tr>
                  
                  <tr class='show_hide' id='testo$idTipoEsercizio'>
                  <td colspan=7 class='bg-white' style= 'font-family: courier new; text-align:left !important;'>$testo</td>
                  </tr>";
                }
                ?>

              </tbody>
            </table>
            <?php
            if ($rowCounter == 0) echo ("<p class='text-center mt-5'>Non c'&egrave; nessun <a href='consegnaEsercizi.php?idCorso=$idCorso'>esercizio consegnato</a></p>");
            else {
              $estensioneSpazio=str_replace(","," </strong>/<strong> ",$estensioneVirgola);
              echo ("<p class='pt-5 pb-3'>
              1) Seleziona uno degli esercizi con il pallino alla sua sinistra<br>
              2) Se vuoi riconsegnare l'esercizio scegli il file e clicca \"Riconsegna\", se invece vuoi solo eliminarlo clicca solo \"Elimina\" 
              (un esercizio accettato non può essere riconsegnato o eliminato)<br>
              Se devi riconsegnare una cartella, inseriscila in un file .rar<br>
              Se elimini un esercizio perderai tutti i commenti associati, a meno che tu non voglia cambiare esercizio riconsegnalo invece di eliminarlo</p>
              <input class='form-control mb-2' type='file' name='fileUpload' accept='$estensioneVirgola' required>
              Per questo corso devi consegnare file che abbiano estensione <strong> $estensioneSpazio </strong>
              <div class='row pb-5 pt-2 mt-3'>
                <div class='col-6 text-start'>
                  <button class='btn btn-primary' type='submit' name='riconsegna'>Riconsegna</button>
                </div>
                <div class='col-6 text-end'>
                  <button class='btn btn-danger' type='submit' name='elimina' formnovalidate>Elimina</button>
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