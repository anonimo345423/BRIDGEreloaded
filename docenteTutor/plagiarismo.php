<?php
$h3 = "Plagiarismo";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || ($permesso != 2 && $permesso != 3)) {
  header("Location: ../login.php");
  exit();
}
$username = $_SESSION['username'];
if (!isset($_GET["idCorso"]) || !isset($_GET["idEsercizio"])) {
  header("Location: ../login.php");
  exit();
}
$idCorso = $_GET["idCorso"];
$idEsercizio = $_GET["idEsercizio"];

//$pathSim = "D:/Desktop/Tesi/c/"; //DA SETTARE
$pathSim="user/bin";

//cerco pathEserc
//QUERY nomeCorso e anno
$query = "SELECT nomeCorso,anno
FROM corsi c
INNER JOIN tipicorsi tc on tc.idTipoCorso=c.idTipoCorso
WHERE idCorso=?";
$stmt = $link->prepare($query);
$stmt->bind_param("i", $idCorso);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$nomeCorso = $row["nomeCorso"];
$nomeCorsoLeggibile = str_replace("_"," ",$nomeCorso);
$anno = $row["anno"];

//query DATI di idEsercizio
$query = "SELECT categoria, nome, cognome, t.idTipoEsercizio, l.username
  FROM tipiesercizi t
  INNER JOIN esercizi e on e.idTipoEsercizio=t.idTipoEsercizio
  INNER JOIN login l on e.username=l.username
  WHERE idEsercizio=?";
$stmt = $link->prepare($query);
$stmt->bind_param("i", $idEsercizio);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
extract($row, EXTR_OVERWRITE);

//file
$inizioPathEserc = dirname(__DIR__);
if($anno!="") $nomeCorso="$nomeCorso.$anno";
$pathEserc = "$inizioPathEserc/corsi/$nomeCorso";
$nomeUno = $categoria . "." . $cognome . "." . $nome . "." . $username . "." . $idTipoEsercizio;
//fine pathEserc
$files = scandir($pathEserc); //trovo tutti i file con idTipoEsercizio identico al mio, così da poterli confrontare in confronto tutti
$files = array_diff($files, array('.', '..'));
foreach($files as $file){
  $pezziFile=explode(".",$file);
  $idTipoEserc=$pezziFile[4];
  if($idTipoEserc!=$idTipoEsercizio) $files=array_diff($files, array($file));
  }
//INIZIO POST

if (isset($_POST["uno"])) {
  if (!empty($_POST["confronto"])) $nomeConfronto = $_POST["confronto"];
  else {
    header("Location: plagiarismo.php?idEsercizio=$idEsercizio&idCorso=$idCorso");
    exit();
  }
  //trovo esercizio da nomeConfronto
  $query = "SELECT nome,cognome,categoria,l.username
  FROM esercizi e
  INNER JOIN login l ON l.username=e.username
  INNER JOIN tipiesercizi te ON te.idTipoEsercizio=e.idTipoEsercizio
  WHERE te.idTipoEsercizio=$idTipoEsercizio AND e.idCorso=$idCorso AND l.username='$nomeConfronto'";
  $result = $link->query($query);
  $row = $result->fetch_assoc();
  extract($row, EXTR_OVERWRITE);
  $nomeDue = $categoria . "." . $cognome . "." . $nome . "." . $username . "." . $idTipoEsercizio;

  $confrontoUnoAdUno = "$pathSim" . "sim_c -p -s -a $pathEserc/$nomeUno.c $pathEserc/$nomeDue.c";
  header("Location: plagiarismoUno.php?comando=$confrontoUnoAdUno");
  exit();
}
if (isset($_POST["tutti"])) {
  
  $confrontoTutti = $pathSim . "sim_c -p -s -a -e $pathEserc/$nomeUno.c / "; //lo slash serve a dire confronto i file a sinistra con
  //tutti i file a destra, ma i file a destra non confrontarli fra loro, i file a destra dello slash sono aggiunti nel foreach
  foreach($files as $file){
    $confrontoTutti=$confrontoTutti."$pathEserc/$file ";
  }
  header("Location: plagiarismoTutti.php?comando=$confrontoTutti");
  exit();
}
if (isset($_POST["tuttiATutti"])) {
  
  $confrontoTuttiATutti = $pathSim . "sim_c -p -s -a -e ";
  foreach($files as $file){
    $confrontoTuttiATutti=$confrontoTuttiATutti."$pathEserc/$file ";
  }
  header("Location: plagiarismoTuttiATutti.php?comando=$confrontoTuttiATutti");
  exit();
}

//FINE POST

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
                    <h2 class='text-center'>Docente</h2>
          <h3 class='mb-4 mt-5'><?php echo $h3 ?></h3>
          <p class="my-5"> Nei grafici le frecce hanno vari colori per indicare quanto è stato copiato e vanno nel seguente ordine: nero, blu, giallo, rosso
              </p>
          <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . "?idEsercizio=$idEsercizio&idCorso=$idCorso"; ?>" method="post">
            <label for="confronto">Seleziona studente con cui fare il confronto (solo per confronto 1 ad 1)</label>
            <select class="form-select mb-5" id="confronto" name="confronto">
              <option selected value=""></option>
              <?php
              //Cerco tutti gli utenti che hanno consegnato degli esercizi dello stesso tipo di idEsercizio e nello stesso corso:
              $query = "SELECT l.username, nome, cognome
              FROM login l
              INNER JOIN esercizi e on e.username=l.username
              WHERE idTipoEsercizio=$idTipoEsercizio AND idCorso=$idCorso AND l.username!='$username'"; //$username è quello preso prima
              $result = $link->query($query);
              while ($row = $result->fetch_assoc()) {
                extract($row, EXTR_OVERWRITE);
                echo ("<option value='$username'>$nome $cognome $username</option>");
              }
              ?>
            </select>
            <div class="row text-center">
              <div class="col-2">
                <input class="btn btn-primary" type="submit" name="uno" value="Confronto 1 ad 1" />
              </div>
              <div class="col-8"></div>
              <div class="col-2">
                <input class="btn btn-primary" type="submit" name="tutti" value="Confronto con tutti" />
              </div>
            </div>
            <div class="row text-center my-5">
              <div class="col-5"></div>
              <div class="col-2">
                <input class="btn btn-danger" type="submit" name="tuttiATutti" value="Confronto tutti con tutti" />
              </div>
              <div class="col-5"></div>
              <p class="mt-5">Quest'ultimo comando confronta gli esercizi di tutti gli studenti con tutti gli altri, attenzione ad eseguirlo perch&egrave;
                può affaticare il server e richiedere molto tempo in presenza di molti esercizi consegnati
              </p>
              
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>

</html>