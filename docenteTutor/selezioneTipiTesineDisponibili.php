<?php
$h3 = "Assegna tesine (selezione tesina)";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
$rowCounter = 0;
if (!isset($_SESSION['valid']) || ($permesso != 2 && $permesso != 3)) {
  header("Location: ../login.php");
  exit();
}
$username = $_SESSION['username']
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
          //se non ho l'idCorso, lo ottengo da selezioneCorso
          $self = basename(__FILE__, '.php'); //nome del file php
          if (!isset($_GET["idCorso"])) {
            header("Location: selezioneCorso.php?to=$self");
            exit();
          } else $idCorso = $_GET["idCorso"];
          ?>

          <h2 class='text-center'>Docente</h2>
          <h3 class='mb-4 mt-5'><?php echo $h3 ?></h3>          <?php
          if (isset($_GET["msg"]) && $_GET["msg"] == 1) echo ('<div class="alert alert-success" role="alert">
        Tesina assegnata!
       </div>');
          else if (isset($_GET["msg"]) && $_GET["msg"] == 2) echo ('<div class="alert alert-danger" role="alert">
        Tesina non assegnata!
       </div>');
          if (isset($_GET["msg"]) && $_GET["msg"] == 3) echo ('<div class="alert alert-success" role="alert">
        Tesina eliminata!
       </div>');
          else if (isset($_GET["msg"]) && $_GET["msg"] == 4) echo ('<div class="alert alert-danger" role="alert">
        Tesina non eliminata!
       </div>');
          ?>
          <div class="container-fluid bg-white mt-2 mb-5 border table-responsive py-5 pb-3 rounded-3 ">
          <?php
            //TABELLA
            require_once("../filterTable.html"); ?>
            <table id="myTable" class="table table-xl table-hover text-center align-middle border border-dark">
              <thead>
                <tr>
                  <th scope="col">Titolo</th>
                  <th scope="col">Proposta da studente?</th>
                  <th scope="col">Assegnata a</th>
                  <th scope="col">Testo</th>
                  <th scope="col">Seleziona</th>
                </tr>
              </thead>
              <tbody class="bg-lighter">

                <?php

                $idCorso = $_GET["idCorso"];
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


                //PRIMA query prende le tesine da tipitesine con idTipoCorso giusto e che non sono ancora state prese da degli studenti (tabella tesine)
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
                  $idTipoTesina = $row["idTipoTesina"];
                  $testo = $row["testo"];
                  $titolo = $row["titolo"];
                  $extra = $row["extra"];
                  if (!empty($extra) && $extra == $idCorso) $extra = "Si";
                  else if (!empty($extra) && $extra != $idCorso) {
                    $rowCounter--; //perchè non vale come row questa
                    continue;
                  }
                  //spiego la riga sopra: extra contiene l'idCorso da cui la tesina è stata creata,
                  //se non è il nostro caso skippiamo il resto del ciclo ed andiamo alla prossima row
                  else $extra = "No";


                  echo "
          <tr>
          <td>$titolo</td>
          <td>$extra</td>
          <td>-</td>
          <td>
          <button class='btn btn-secondary control' id='$idTipoTesina' type='button' >Testo</button>
          </td>
          <td>
          <a class='btn btn-primary' href='assegnaTesine.php?idTipoTesina=$idTipoTesina&idCorso=$idCorso&idTipoCorso=$idTipoCorso'>Assegna</a>
          </td>
          </tr>
          
          <tr class='show_hide' id='testo$idTipoTesina'>
          <td colspan=5 class='bg-white' style= 'font-family: courier new; text-align:left !important;'>$testo</td>
          </tr>
          "; //la tr di id testo$idTipoTesina viene mostrata solo se il relativo button $idTipoTesina viene premuto (vedi script js inclusi a inizio pagina per la meccanica precisa)
                }

                //SECONDA QUERY prende le tesine di questo idCorso e che sono state prese da degli studenti (tabella tesine)
                $query = "SELECT *
        FROM tesine t
        INNER JOIN tipitesine tt on t.idTipoTesina=tt.idTipoTesina
        WHERE idCorso=?
        ORDER BY titolo";
                $stmt = $link->prepare($query);
                $stmt->bind_param("i", $idCorso);
                $stmt->execute();
                $result = $stmt->get_result();


                while ($row = $result->fetch_assoc()) {
                  $rowCounter++;
                  $idTipoTesina = $row["idTipoTesina"];
                  $testo = $row["testo"];
                  $titolo = $row["titolo"];
                  $extra = $row["extra"];
                  if (!empty($extra) && $extra == $idCorso) $extra = "Si";
                  else if (!empty($extra) && $extra != $idCorso) {
                    $rowCounter--; //perchè non vale come row questa
                    continue;
                  }
                  //spiego la riga sopra: extra contiene l'idCorso da cui la tesina è stata creata,
                  //se non è il nostro caso skippiamo il resto del ciclo ed andiamo alla prossima row
                  else $extra = "No";
                  //trovo gli studenti con quell'idCorso e idTipoTesina (nel caso in cui sia già assegnata)
                  $studenti = "";
                  $query = "SELECT nome,cognome,l.username
          FROM studentitesina s
          INNER JOIN tesine t on t.idTesina=s.idTesina
          INNER JOIN login l ON l.username=s.username
          WHERE idCorso=$idCorso AND idTipoTesina=$idTipoTesina";
                  $resultNomi = $link->query($query);
                  while ($rowNomi = $resultNomi->fetch_assoc())
                    $studenti = $studenti . $rowNomi["nome"] . " " . $rowNomi["cognome"] . " " . $rowNomi["username"] . "<br>";

                  echo "
          <tr>
          <td>$titolo</td>
          <td>$extra</td>
          <td>$studenti</td>
          <td>
          <button class='btn btn-secondary control' id='$idTipoTesina' type='button' >Testo</button>
          </td>
          <td>
          <a class='btn btn-warning' href='gestioneTesineAssegnate.php?idTipoTesina=$idTipoTesina&idCorso=$idCorso&idTipoCorso=$idTipoCorso'>Modifica assegnazione</a>
          </td>
          </tr>
          
          <tr class='show_hide' id='testo$idTipoTesina'>
          <td colspan=5 class='bg-white' style= 'font-family: courier new; text-align:left !important;'>$testo</td>
          </tr>
          "; //la tr di id testo$idTipoTesina viene mostrata solo se il relativo button $idTipoTesina viene premuto (vedi script js inclusi a inizio pagina per la meccanica precisa)
                }
                ?>

              </tbody>
            </table>
          </div>
          <?php
          if ($rowCounter == 0) echo "<p class='text-center'>Nessuna tesina da assegnare</p>";
          ?>
        </div>
      </div>
    </div>
  </div>
</body>

</html>