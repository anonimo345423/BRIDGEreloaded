<?php
$h3 = "Home docente";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || ($permesso != 2 && $permesso != 3)) {
  header("Location: ../login.php");
  exit();
}
$username = $_SESSION['username'];
$nome = $_SESSION['nome'];
$cognome = $_SESSION['cognome'];
$i = 1;
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
        <div class="container-fluid pt-5 text-center">
          <h3 class='mb-5 mt-3'><?php echo $h3 ?></h3>          Benvenuto nella home <?php echo $nome ?>, a sinistra puoi vedere le funzioni disponibili per te<br>
          Se qualche studente ha consegnato esercizi nuovi per uno dei tuoi corsi, vedrai una notifica qui sotto.<br>
          La vedrai anche se qualche studente non ha terminato la tesina entro la sua scadenza<br><br>

          <?php
          $testo="Per prima cosa bisogna fare la distinzione fra 'corso' e 'tipologia di corso':<br>
          <strong>1) </strong>Una tipologia di corso è per esempio analisi 1, un corso è analisi 1 2021-2022<br>
          La tipologia di corso ti viene assegnata dall'admin, mentre il corso lo crei tu con 'crea corso'<br><br>
          Quando crei un tipo di esercizio quel tipo di esercizio vale per il tipo di corso, ossia vale per tutti i corsi,
          quindi se hai il tipoCorso analisi 1 ed hai creato 2 corsi analisi 1 21-22 ed analisi 1 22-23, creando un tipo di esercizio esso
          sar&agrave; valido per entrambi i corsi e i futuri corsi creati. Vale lo stesso per i tipi di tesine<br><br>
          <strong>2) </strong>Siccome non tutti i professori vogliono che gli esercizi siano identici per ogni anno, il sistema pu&ograve; essere usato in maniera diversa:<br>
          Invece che creare un nuovo corso ogni anno bisognerà farsi creare dall'admin un nuovo tipo di corso ogni anno (con l'anno già incluso nel nome del tipo corso), cosicchè esercizi e tesine siano ogni anno resettati.
          <br>Una volta fatto ciò si creerà un corso senza inserire l'anno<br><br>
          Il sistema &egrave; stato pensato per il primo metodo d'uso, ma si può usare appunto anche nel secondo modo.<br>
          Per ogni dubbio riferirsi all'admin";
           if ($permesso == 3) echo"
           <button class='btn btn-info control my-5' id='uno' type='button' >Istruzioni d'uso</button>
           <div class='show_hide' id='testouno'> <p class='border border-secondary py-3 my-4'>$testo</p> </div><br>
           ";
          //esercizi DOCENTE
          if ($permesso == 3)
            $query = "SELECT *
        FROM corsi c
        INNER JOIN tipicorsi t on c.idTipoCorso=t.idTipoCorso
        WHERE docente='$username' AND notifica=1";
          else
            $query = "SELECT *
        FROM corsi c
        INNER JOIN tipicorsi tc on c.idTipoCorso=tc.idTipoCorso
        INNER JOIN tutoraggio t on t.idTipoCorso=tc.idTipoCorso
        WHERE t.tutor='$username' AND notifica=1";
          $result = $link->query($query);
          if ($result->num_rows > 0) echo "Ci sono <strong style='color: red;'>nuove consegne</strong> in:<br>";
          while ($row = $result->fetch_assoc()) {
            extract($row, EXTR_OVERWRITE);
            $nomeCorso = str_replace("_"," ",$nomeCorso);
            echo ("$i) $nomeCorso $anno<br>");
            $i++;
          } //scadenza tesine
          echo"<br><br>";
          $query = "SELECT scadenza,titolo,anno,nomeCorso,statiTesine statiMax, stato
        FROM tesine t
        INNER JOIN corsi c on c.idCorso=t.idCorso
        INNER JOIN tipicorsi tc on c.idTipoCorso=tc.idTipoCorso
        INNER JOIN tipitesine tt on tt.idTipoTesina=t.idTipoTesina
        WHERE docente='$username'";
          $result = $link->query($query);
          while ($row = $result->fetch_assoc()) {
            extract($row, EXTR_OVERWRITE);
            if ($scadenza == null) continue;
            $oggi = date("Y-m-d");
            if ($scadenza < $oggi && $stato-1<$statiMax) echo ("<strong style='color:red'>Attenzione</strong> Tesina con titolo '$titolo' del corso $nomeCorso $anno &egrave; scaduta<br>");
          }
          ?>
        </div>
      </div>
    </div>
  </div>
</body>

</html>