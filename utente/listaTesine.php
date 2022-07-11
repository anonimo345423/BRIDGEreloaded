<?php //si arriva in questa pagina da richiediTesina.php se non si hanno ancora fatto gli esercizi obbligatori
$h3 = "Lista tesine";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
$username = $_SESSION['username'];

$rowCounter = 0;
if (!isset($_SESSION['valid']) || $permesso != 1) {
  header("Location: ../login.php");
  exit();
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

      //se non ho l'idCorso, lo ottengo da selezioneCorso
      $self = basename(__FILE__, '.php'); //nome del file php
      if (!isset($_GET["idCorso"])) {
        header("Location: selezioneCorso.php?to=$self");
        exit();
      } else $idCorso = $_GET["idCorso"];
      //QUERY controllo che utente sia iscritto al corso e che il corso non sia archiviato, sennò lo mando fuori
      $query = "SELECT *
        FROM iscritto i
        INNER JOIN corsi c on i.idCorso=c.idCorso
        WHERE c.idCorso=? and i.username=? AND archiviato=0";
      $stmt = $link->prepare($query);
      $stmt->bind_param("is", $idCorso, $username);
      $stmt->execute();
      $result3 = $stmt->get_result();
      if ($result3->num_rows == 0) {
        header("Location: ../login.php");
        exit();
      }
      //trovo idTipoCorso
      $query = "SELECT idTipoCorso
        FROM corsi
        WHERE idCorso=?";
      $stmt = $link->prepare($query);
      $stmt->bind_param("i", $idCorso);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();
      $idTipoCorso = $row["idTipoCorso"];
      //trovo maxStudentiTesine
      $query = "SELECT maxStudentiTesine, autoAssegnazione
        FROM tipicorsi
        WHERE idTipoCorso=?";
      $stmt = $link->prepare($query);
      $stmt->bind_param("i", $idTipoCorso);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();
      $maxStudentiTesine = $row["maxStudentiTesine"];
      $autoAssegnazione = $row["autoAssegnazione"];
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">
          <?php
          if (isset($_GET["warning"])) echo ("<div class='alert alert-warning' role='alert'>
      Mi dispiace, non puoi richiedere una tesina per uno dei seguenti motivi:<br>
      1) Hai gi&agrave; una <a href='tesinaAssegnata.php?idCorso=$idCorso'>tesina assegnata</a><br>
      2) Non hai <a href='consegnaEsercizi.php?idCorso=$idCorso'>consegnato</a> un esercizio obbligatorio per ogni categoria<br>
      3) Non ti è stato ancora <a href='gestioneEsercizi.php?idCorso=$idCorso'>accettato</a> uno degli esercizi obbligatori che hai consegnato
    </div>");

          
          ?>
          
          <h3 class='mb-4 mt-5'><?php echo $h3 ?></h3>          <?php
            //se maxStudentiTesine=0 faccio exit dicendo che non ci sono tesine per questo corso
            if ($maxStudentiTesine == 0) {
              echo '<div class="container-fluid bg-white mt-2 mb-5 border table-responsive py-5 rounded-3 text-center">';
              echo "Questo corso non prevede tesine<br><br>";
              echo "<a href='selezioneCorso.php?to=listaTesine' class='btn btn-primary'>Torna indietro</a>";
              exit();
            }
            ?>
          <p class="mb-5">Qui trovi la lista delle tesine, puoi richiedere una tesina al professore. Ogni tesina può essere svolta da 1 fino a <strong><?php echo $maxStudentiTesine; ?></strong> studenti</p>
          <div class="container-fluid bg-white mt-2 mb-5 border table-responsive py-5 rounded-3 ">
            <table id="" class="table table-xl table-hover text-center align-middle border border-dark">
              <thead>
                <tr>
                  <th scope="col">Titolo</th>
                  <th scope="col">Disponibile?</th>
                  <th scope="col">Testo</th>
                </tr>
              </thead>
              <tbody class="bg-lighter">

                <?php
                //la query prende le tesine da tipitesine con idTipoCorso giusto e che non sono ancora state prese da degli studenti (tabella tesine)
                $query = "SELECT *
        FROM tipitesine
        WHERE idTipoCorso=? AND disabilitato=0 AND idTipoTesina NOT IN(
            SELECT t.idTipoTesina
            FROM tipitesine tt
            INNER JOIN tesine t on t.idTipoTesina=tt.idTipoTesina
            WHERE idCorso=?
        )
        ORDER BY titolo";
                $stmt = $link->prepare($query);
                $stmt->bind_param("ii", $idTipoCorso, $idCorso);
                $stmt->execute();
                $result = $stmt->get_result();


                while ($row = $result->fetch_assoc()) {
                  $rowCounter++;
                  $testo = $row["testo"];
                  $extra = $row["extra"];
                  $titolo = $row["titolo"];
                  $idTipoTesina = $row["idTipoTesina"];

                  if (!empty($extra) && $extra == $idCorso) $extra = "Si";
                  else if (!empty($extra) && $extra != $idCorso) {
                    $rowCounter--; //perchè non vale come row questa
                    continue;
                  }
                  //spiego la riga sopra, extra contiene l'idCorso da cui la tesina è stata creata,
                  //se non è il nostro caso skippiamo il resto del ciclo ed andiamo alla prossima row
                  else $extra = "No";

                  echo "
          <tr>
          <td>$titolo</td>
          <td><i class='bi bi-check display-6' style='color:green'></i></td>

          <td>
          <button class='btn btn-secondary control' id='$idTipoTesina' type='button' >Testo</button>
          </td>
          </tr>
          
          <tr class='show_hide' id='testo$idTipoTesina'>
          <td colspan=4 class='bg-white' style= 'font-family: courier new; text-align:left !important;'>$testo</td>
          </tr>
          "; //la tr di id testo$idTipoTesina viene mostrata solo se il relativo button $idTipoTesina viene premuto (vedi script js inclusi a inizio pagina per la meccanica precisa)
                }
                //ora ci aggiunto quelle già prese dagli studenti (ho fatto questa divisione per poterle avere in ordine)

                $query = "SELECT *
        FROM tipitesine
        WHERE idTipoCorso=? AND disabilitato=0 AND idTipoTesina IN(
            SELECT t.idTipoTesina
            FROM tipitesine tt
            INNER JOIN tesine t on t.idTipoTesina=tt.idTipoTesina
            WHERE idCorso=?
        )
        ORDER BY titolo";
                $stmt = $link->prepare($query);
                $stmt->bind_param("ii", $idTipoCorso, $idCorso);
                $stmt->execute();
                $result = $stmt->get_result();


                while ($row = $result->fetch_assoc()) {
                  $rowCounter++;
                  $testo = $row["testo"];
                  $titolo = $row["titolo"];
                  $extra = $row["extra"];
                  $idTipoTesina = $row["idTipoTesina"];

                  if (!empty($extra) && $extra == $idCorso) $extra = "Si";
                  else if (!empty($extra) && $extra != $idCorso) {
                    $rowCounter--; //perchè non vale come row questa
                    continue;
                  }
                  //spiego la riga sopra, extra contiene l'idCorso da cui la tesina è stata creata,
                  //se non è il nostro caso skippiamo il resto del ciclo ed andiamo alla prossima row
                  else $extra = "No";

                  echo "
          <tr>
          <td>$titolo</td>
          <td><i class='bi bi-x display-6' style='color:red'></i></td>

          <td>
          <button class='btn btn-secondary control' id='$idTipoTesina' type='button' >Testo</button>
          </td>
          </tr>
          
          <tr class='show_hide' id='testo$idTipoTesina'>
          <td colspan=4 class='bg-white' style= 'font-family: courier new; text-align:left !important;'>$testo</td>
          </tr>
          "; //la tr di id testo$idTipoTesina viene mostrata solo se il relativo button $idTipoTesina viene premuto (vedi script js inclusi a inizio pagina per la meccanica precisa)
                }
                ?>

              </tbody>
            </table>
            <?php if ($rowCounter == 0) echo ("<p class='text-center mt-5'>Non c'è nessuna tesina disponibile, puoi chiedere al professore di aggiungerne una per te!</p>"); ?>
          </div>
          <?php
          if($autoAssegnazione==1&&$rowCounter!=0) echo("<p class='my-3'>Se hai intenzione di svolgere la tesina <strong>da solo</strong> puoi assegnartela autonomamente:
            <br><br> <a class='btn btn-primary' href='richiediTesina.php?idCorso=$idCorso'>Auto assegnazione</a><br><br></p>")
          ?>
        </div>
      </div>
    </div>
</body>

</html>