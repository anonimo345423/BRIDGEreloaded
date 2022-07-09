<?php
$h3 = "Confronto Uno con tutti";
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
/*devo invertire il comando per ottenere le percentuali al contrario, quindi passare da una cosa tipo:
D:/Desktop/Tesi/c/sim_c -p -s -a -e C:\xampp\htdocs\BRIDGE/corsi/c.2021-2022/cat2.Utente.Utente.utente.8.c 
/ File .c della cartella
ad una cosa tipo:
D:/Desktop/Tesi/c/sim_c -p -S -e File .c della cartella 
/ C:\xampp\htdocs\BRIDGE/corsi/c.2021-2022/cat2.Utente.Utente.utente.8.c
*/
$cercoComando=explode("-e ", $comando);
$cercoComando1=explode(" / ", $cercoComando[1]);
$comandoInvertito=str_replace("-s -a", "-S -e", $cercoComando[0]).$cercoComando1[1]." / ".$cercoComando1[0];
$output = shell_exec($comando);
$output2 = shell_exec($comandoInvertito);

//dall'output mi trovo le percentuali di copiaggio:
$output = explode("tokens\n\n", $output);
$output = $output[1]; //ora ho le n righe
$output2 = explode("tokens\n\n", $output2);
$output2 = $output2[1]; //ora ho le n righe

$i = 0;
$j=0;
$digraph="digraph G {
  size =\"100,100\";
  bgcolor=\"#f8f9fa\";
  ";

while (!empty(explode("\n", $output)[$i])) {
  $riga = explode("\n", $output)[$i];
  $riga = explode(".", $riga);
  $cognome1 = $riga[2]; //utente selezionato
  $nome1 = $riga[3]; //utente selezionato

  $percent = $riga[6];
  $percent = intval(explode(" ", $percent)[3]);

  $color="[color=black]";
  if ($percent >= 30 && $percent < 50) $color=" [color=mediumblue]";
  else if ($percent >= 50 && $percent < 70) $color=" [color=gold]";
  else if ($percent >= 70) $color=" [color=crimson]"; //gerarchia è: nero, blu, giallo, rosso
  
  $cognome2 = $riga[8];
  $nome2 = $riga[9];
  
  if (!($cognome1 == $cognome2 && $nome1 == $nome2 && $percent == 100)&&$percent>15){
    $digraph=$digraph."edge $color;
    $nome1$cognome1 -> $nome2$cognome2 [label=\"$percent%\"];
    $nome1$cognome1 [label=\"$nome1 $cognome1\"];
    ";
  }
  // condizione if per evitare che mostri che ha copiato il 100% di se stesso

  $i++;
}
while (!empty(explode("\n", $output2)[$j])) {
  $riga = explode("\n", $output2)[$j];
  $riga = explode(".", $riga);
  $cognome1 = $riga[2]; 
  $nome1 = $riga[3];

  $percent = $riga[6];
  $percent = intval(explode(" ", $percent)[3]);

  $color="[color=black]";
  if ($percent >= 30 && $percent < 50) $color=" [color=mediumblue]";
  else if ($percent >= 50 && $percent < 70) $color=" [color=gold]";
  else if ($percent >= 70) $color=" [color=crimson]"; //gerarchia è: nero, blu, giallo, rosso
  
  $cognome2 = $riga[8]; //utente selezionato
  $nome2 = $riga[9]; //utente selezionato
  
  if (!($cognome1 == $cognome2 && $nome1 == $nome2 && $percent == 100)&&$percent>15){
    $digraph=$digraph."edge $color;
    $nome1$cognome1 -> $nome2$cognome2 [label=\"$percent%\"];
    $nome1$cognome1 [label=\"$nome1 $cognome1\"];
    ";
  }
  // condizione if per evitare che mostri che ha copiato il 100% di se stesso

  $j++;
}
$digraph=$digraph."}";
//file .dot
$myfile = fopen("input.dot", "w") or die("Unable to open file!");
fwrite($myfile, $digraph);
fclose($myfile);
$dir=__DIR__;

//$pathDot="D:\Programmi\Graphviz\bin\\";
$pathDot="/usr/bin/";
$comandoGrafico="$pathDot"."dot -Tsvg $dir\input.dot";
$grafico=shell_exec($comandoGrafico);
$grafico=str_replace("width=","",$grafico);
$grafico=str_replace("height=","",$grafico);
//unlink("input.dot"); viene fatto dopo
?>

<html>
<body>
  <script src="../js/jquery.min.js"></script>
  <script src="../js/maxHeightSvg.js"></script>
  <div class="container-fluid">
    <div class="row">
      <?php
      require_once("../sidebar.php");
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">

          <h3 class="mb-4 mt-5 text-center"><?php echo $h3 ?></h3><br>
          <div class="row text-center">
            <div class="col-12">
              <?php
              echo $grafico;
              if(empty($grafico)) echo "Non c'è codice simile presente fra gli studenti";
              unlink("input.dot");
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>