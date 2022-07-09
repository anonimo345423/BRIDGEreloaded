<?php
$h3 = "Confronto 1 ad 1";
$rowCounter=0;
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || ($permesso != 2 && $permesso != 3)) {
  header("Location: ../login.php");
  exit();
}
$username = $_SESSION['username'];
if (!isset($_GET["comando"])) {
  header("Location: ../login.php");
  exit();
}
$comando = $_GET["comando"];
//dal comando trovo idTipoEsercizio, nomePrimoStudente (e cognome) ed idem per il secondo:

$cerco=explode(".",$comando);
$nomePrimoStudente=$cerco[3];
$cognomePrimoStudente=$cerco[2];
$idTipoEsercizio=$cerco[5];
$nomeSecondoStudente=$cerco[9];
$cognomeSecondoStudente=$cerco[8];

$comandoNoOptions=str_replace("-p -s -a ", "", $comando); //da qui prendo le parti uguali nel codice da sim
$output = shell_exec($comando);
$codici= shell_exec($comandoNoOptions);
$codiceArr=explode("\n",$codici);
$arrayFinale=[];
foreach ($codiceArr as $codice){
  $codice=$codice;
  $sottoArray=explode("|",$codice);
  foreach($sottoArray as $sot){
    $arrayFinale[]=$sot."\n";
  }
}
$i=0;
$codice1="";
$codice2="";
$arrayFinale = array_slice($arrayFinale, 6); 
foreach ($arrayFinale as $codice){
  if ($i%2==0) $codice1=$codice1.$codice;
  else $codice2=$codice2.$codice;
  $i++;
}
$codice1Arr=explode("\n",$codice1);
$codice2Arr=explode("\n",$codice2); //qui ho le linee uguali fra i 2 codici che userò per andare a sottolineare nel codice nativo


$finaleArr = []; //array da usare alla fine per stampare

//dal comando mi trovo i 2 percorsi dei file per mostrarli a schermo
$arr = explode("-a ", $comando);
$percorsi = $arr[1];
$percorsiArr = explode(" ", $percorsi);
$percorso1 = $percorsiArr[0];
$percorso2 = $percorsiArr[1];
//dall'output mi trovo le percentuali di copiaggio:
/*un output tipo è:
File C:\xampp\htdocs\BRIDGE/corsi/test.2021-2022/html.utenteC.utenteN.utente.3.c: 60 tokens, 18 lines (not NL-terminated)
File C:\xampp\htdocs\BRIDGE/corsi/test.2021-2022/html.Asd.Asd.utente2.3.c: 60 tokens, 18 lines (not NL-terminated)
Total input: 2 files (2 new, 0 old), 120 tokens

C:\xampp\htdocs\BRIDGE/corsi/test.2021-2022/html.utenteC.utenteN.utente.3.c consists for 100 % of C:\xampp\htdocs\BRIDGE/corsi/test.2021-2022/html.Asd.Asd.3.c material
C:\xampp\htdocs\BRIDGE/corsi/test.2021-2022/html.Asd.Asd.utente2.3.c consists for 100 % of C:\xampp\htdocs\BRIDGE/corsi/test.2021-2022/html.utenteC.utenteN.3.c material

con la seconda parte che non compare se non si sono copiati a vicenda, sfrutto il fatto che la parola "tokens\n\n" compare prima delle
righe con le percentuali di copiaggio per dividere la stringa in 2 e poi mi trovo sempre con explode nomi e cognomi
*/
$output = explode("tokens\n\n", $output);
$output = $output[1]; //ora ho le 2 righe, oppure 1 o 0 se ha copiato solo 1 o nessuno
if (!empty(explode("\n", $output)[0])) $riga1 = explode("\n", $output)[0];
if (!empty(explode("\n", $output)[1])) $riga2 = explode("\n", $output)[1];

if (!empty($riga1)) { //quanto user1 ha copiato user2
  $riga1 = explode(".", $riga1);

  $percent = $riga1[6];
  $percent = intval(explode(" ", $percent)[3]);
  if ($percent >= 30 && $percent < 50) $percent = "<strong class='text-primary'>$percent%</strong>";
  else if ($percent >= 50 && $percent < 70) $percent = "<strong class='text-warning'>$percent%</strong>";
  else if ($percent >= 70) $percent = "<strong class='text-danger'>$percent%</strong>";
  else $percent = "<strong>$percent</strong>"; //gerarchia è: nero, blu, giallo, rosso

  array_push($finaleArr, "$nomePrimoStudente $cognomePrimoStudente contiene il $percent dell'esercizio di $nomeSecondoStudente $cognomeSecondoStudente");
}
if (!empty($riga2)) { //quanto user2 ha copiato user1
  $riga2 = explode(".", $riga2);

  $percent = $riga2[6];
  $percent = explode(" ", $percent)[3];
  if ($percent >= 30 && $percent < 50) $percent = "<strong class='text-primary'>$percent%</strong>";
  else if ($percent >= 50 && $percent < 70) $percent = "<strong class='text-warning'>$percent%</strong>";
  else if ($percent >= 70) $percent = "<strong class='text-danger'>$percent%</strong>";
  else $percent = "<strong>$percent</strong>"; //gerarchia è: nero, blu, giallo, rosso

  array_push($finaleArr, "$nomeSecondoStudente $cognomeSecondoStudente contiene il $percent dell'esercizio di $nomePrimoStudente $cognomePrimoStudente");
}

$query="SELECT testo FROM tipiesercizi WHERE idTipoEsercizio=$idTipoEsercizio";
$result = $link->query($query);
$row = $result->fetch_assoc();
$testoEsercizio=$row["testo"];
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

          <h3 class="my-5 text-center"><?php echo $h3 ?></h3><br>
            <?php
            echo "<h5>Testo dell'esercizio:</h5>
            <p class='my-5' style= 'font-family: courier new'>$testoEsercizio</p><br><br>
            <div class='row text-center'>
            ";

            foreach ($finaleArr as $finale) {
              $finale = nl2br($finale);
              echo "<p>$finale</p>";
              $rowCounter++;
            }
            if($rowCounter==0) echo ("<strong>Nulla di copiato</strong>"); //cioè se non esce nessun dato sul copiarsi a vicenda
            echo ("</div>");
            echo ("<div class='row my-4'>");
            echo ("<br><br><div class='col-6 mt-5'>");
            echo ("<h5>Testo $nomePrimoStudente $cognomePrimoStudente:<br><br></h5><p style= 'font-family: courier new'>");
            $uno = file_get_contents($percorso1);
            foreach($codice1Arr as $codiceUno){
              $uno=str_replace(trim($codiceUno),"<strong>$codiceUno</strong>",$uno);
            }
            $uno = nl2br($uno);
            print $uno;
            echo ("</p></div>");

            echo ("<div class='col-6 mt-5'>");
            echo ("<h5>Testo $nomeSecondoStudente $cognomeSecondoStudente:<br><br></h5><p style= 'font-family: courier new'>");
            $due = file_get_contents($percorso2);
            foreach($codice2Arr as $codiceDue){
              $due=str_replace(trim($codiceDue),"<strong>$codiceDue</strong>",$due);
            }
            $due = nl2br($due);
            print $due;
            echo ("</p></div>");
            ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</body>

</html>