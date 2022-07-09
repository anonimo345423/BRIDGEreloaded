<?php
$h3 = "Gestisci tipologia di corso";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || $permesso != 4) {
  header("Location: ../login.php");
  exit();
}
if (!isset($_GET["idTipoCorso"])) {
  header("Location: gestisciTipoCorsoSelezione.php");
  exit();
} else $idTipoCorso = $_GET["idTipoCorso"];
?>
<html>

<body>
  <script src="../js/jquery.min.js"></script>
  <script src="../js/showHideCreaTipoCorso.js"></script>
  <div class="container-fluid">
    <div class="row">
      <?php

      require_once("../sidebar.php");
      $msg = '';
      //gestione post
      if (isset($_POST['modifica'])) {
        $nomeCorso = trim($_POST['nomeCorso']);
        $nomeCorso = str_replace(" ","_",$nomeCorso);
        if (!empty($_POST['maxStudentiTesine'])) $maxStudentiTesine = $_POST['maxStudentiTesine'];
        else $maxStudentiTesine = 0;
        if (!empty($_POST['nomeAccount'])) $nomeAccount = $_POST['nomeAccount'];
        else $nomeAccount = null;
        $docente = $_POST['docente'];
        if (!empty($_POST['tutor'])) $tutors = $_POST['tutor'];
        else $tutors = null;
        if (!empty($_POST['statiTesine'])) $statiTesine = $_POST['statiTesine'];
        else $statiTesine = null;
        if (!empty($_POST['numeroAccount'])) $numeroAccount = $_POST['numeroAccount'];
        else $numeroAccount = null;
        $sitoCorso = trim($_POST['sitoCorso']);

        $categorie = strtolower(trim($_POST['categorie']));
        $catArr=explode(" ",$categorie);
        $i=1;
        $catArrFinale=[];
        $len=count($catArr);
        foreach ($catArr as $cat){
          if($i!=$len) $catArrFinale[]=$i.$cat."|";
          else $catArrFinale[]=$i.$cat;
          $i++;
        }
        $categorie=implode("",$catArrFinale);        
        
        $estensione = str_replace(" ", "|",strtolower(trim($_POST['estensione'])));
        $tipoVoto = $_POST['tipoVoto'];
        if (!empty($_POST['correzione'])) $correzione = $_POST['correzione'];
        else $correzione = null;
        if($maxStudentiTesine!=0) $autoAssegnazione = $_POST['autoAssegnazione'];
        else $autoAssegnazione=0;


        //sql
        $query = "UPDATE tipicorsi set nomeCorso=?, maxStudentiTesine=?, nomeAccount=?, numeroAccount=?, docente=?, sitoCorso=?, categorie=?, estensione=?, tipoVoto=?, correzione=?, statiTesine=?, autoAssegnazione=? WHERE idTipoCorso=$idTipoCorso";
        $stmt = $link->prepare($query);
        $stmt->bind_param("sisissssssii", $nomeCorso, $maxStudentiTesine, $nomeAccount, $numeroAccount, $docente, $sitoCorso, $categorie, $estensione, $tipoVoto, $correzione, $statiTesine, $autoAssegnazione);
        try {
          $stmt->execute();
          $msg = '<br><div class="alert alert-success mb-5" role="alert">
               Tipologia di Corso modificata corretamente!
               </div>';
        }
        //se fallisce inserimento, primary key violata:
        catch (exception $e) {
          $msg = '<br><div class="alert alert-danger mb-5" role="alert">
            Errore nella modifica della tipologia di Corso! Attenzione che non esista gi&agrave; un corso con stesso nome per gli account
            </div>';
        }
        //ora inserisco tutti i tutor nella tabella
        $query="DELETE FROM tutoraggio WHERE idTipoCorso=$idTipoCorso";
        $link->query($query);
        foreach((array)$tutors as $tutor){
          $query="INSERT INTO tutoraggio (tutor,idTipoCorso)
          VALUES (?,?)";
          $stmt = $link->prepare($query);
          $stmt->bind_param("si", $tutor, $idTipoCorso);
          $stmt->execute();
        }
      }
      if (isset($_POST['elimina']) && isset($_POST['checkElimina'])) {
        $query = "DELETE FROM tipicorsi WHERE idTipoCorso=?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("i", $idTipoCorso);
        $result = $stmt->execute();
        if ($result == true) header("Location: gestisciTipoCorsoSelezione.php?msgShow=1"); //lo porto alla pagina di selezione con messaggio conferma
        else header("Location: gestisciTipoCorsoSelezione.php?msgShow=2");
      }


      //Qui invece mi procuro i parametri attuali prima della modifica per averceli pronti nel form visto dall'admin
      $query = "SELECT * from tipicorsi t LEFT JOIN login l on l.username=t.docente WHERE idTipoCorso=?";
      $stmt = $link->prepare($query);
      $stmt->bind_param("i", $idTipoCorso);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();

      $nomeCorso = $row["nomeCorso"];
      $nomeCorso = str_replace("_"," ",$nomeCorso);
      $maxStudentiTesine = $row['maxStudentiTesine'];
      if ($maxStudentiTesine == 0) $maxStudentiTesine = "";
      $nomeAccount = $row['nomeAccount'];
      $numeroAccount = $row['numeroAccount'];
      $statiTesine = $row['statiTesine'];
      $docente = $row['docente'];
      $tipoVoto = $row['tipoVoto'];
      $correzione = $row['correzione'];
      $sitoCorso = $row['sitoCorso'];
      $categorie = $row['categorie'];
      $categorieArr = explode("|", $categorie); 
      $categorie="";
      foreach($categorieArr as $cat){
        $categoriaSenzaNumero=substr($cat, 1);
        $categorie=$categorie."$categoriaSenzaNumero ";
      }
      $categorie=trim($categorie);
      $estensione = $row['estensione'];
      $nome = $row['nome'];
      $cognome = $row['cognome'];
      $autoAssegnazione=$row["autoAssegnazione"];
      $nomeDocente = $nome . " " . $cognome;

      //userò questi valori come value di default negli input del form

      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">
          <h3 class='mt-3'><?php echo $h3 ?></h3>
          <?php echo "$msg"; ?>
          <form action="<?php echo (htmlspecialchars($_SERVER['PHP_SELF']) . "?idTipoCorso=$idTipoCorso"); ?>" method="post">
          <p>Il nome del tipo di corso pu&ograve; contenere trattini "-" e caratteri alfanumerici</p>
            <div class="form-floating mb-3">
              <input type="text" pattern="[a-zA-Z0-9- ]+" class="form-control" id="nomeCorso" name="nomeCorso" placeholder="Nome del tipo di corso" value="<?php echo "$nomeCorso" ?>" required autofocus />
              <label for="nomeCorso">Nome del tipo di corso</label>
            </div>
            <div class="form-floating mb-3">
              <input type="number" class="form-control" id="control" name="maxStudentiTesine" placeholder="Studenti massimi per tesina (vuoto se non ci sono tesine)" value="<?php echo "$maxStudentiTesine" ?>" />
              <label for="maxStudentiTesine">Studenti massimi per tesina (vuoto se non ci sono tesine)</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control show_hide" id="nomeAccount" name="nomeAccount" placeholder="Nome account per tesine (facoltativo)" value="<?php echo "$nomeAccount" ?>" />
              <label for="nomeAccount" class="show_hide">Nome account per tesine (facoltativo)</label>
            </div>
            <div class="form-floating mb-3">
              <input type="number" class="form-control show_hide" id="numeroAccount" name="numeroAccount" placeholder="Numero account totali (facoltativo)" value="<?php echo "$numeroAccount" ?>" />
              <label for="maxStudentiTesine" class="show_hide">Numero account totali (facoltativo)</label>
            </div>
            <div class="form-floating mb-3">
              <input type="number" class="form-control show_hide" id="statiTesine" name="statiTesine" placeholder="Numero stati tesine" value="<?php echo "$statiTesine" ?>" />
              <label for="maxStudentiTesine" class="show_hide">Numero stati tesine</label>
            </div>
            <div class="show_hide">Vuoi che gli studenti possano autoassegnarsi la tesina?</div>
            <div class="row py-4 show_hide">
              <div class="col-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="autoAssegnazione" id="autoAssegnazione1" value="1" <?php if($autoAssegnazione==1) echo "checked='checked'"; ?>>
                  <label class="form-check-label" for="autoAssegnazione1">
                    Si
                  </label>
                </div>
              </div>
              <div class="col-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="autoAssegnazione" id="autoAssegnazione2" value="0" <?php if($autoAssegnazione==0) echo "checked='checked'"; ?>>
                  <label class="form-check-label" for="autoAssegnazione2">
                    No
                  </label>
                </div>
              </div>
            </div>

            <div class="mb-3">
              <label for="docente">Seleziona Docente</label>
              <select class="form-select py-2" id="docente" name="docente" required>
                <option selected style="display:none" value="<?php echo "$docente" ?>"><?php echo "$nomeDocente" ?></option>
                <?php
                //devo mettere le opzione per i docenti qui
                $query = "SELECT * FROM login WHERE permesso=3";
                $result = $link->query($query);
                while ($row = $result->fetch_assoc()) {
                  $username = $row["username"];
                  $nome = $row["nome"];
                  $cognome = $row["cognome"];
                  echo ("<option value='$username'>$nome $cognome</option>");
                }

              
                ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="tutor">Seleziona Tutor (facoltativo)</label>
              <select class="form-select py-2" id="tutor" name="tutor[]" multiple>
                <option value="">Nessuno</option>
                <?php //cerco tutor attuali
                $usernameTutorAttivi=[];
                $query="SELECT nome,cognome,username
                FROM tutoraggio t
                INNER JOIN login l on l.username=t.tutor
                WHERE idTipoCorso=$idTipoCorso";
                $result = $link->query($query);
                while($row = $result->fetch_assoc()) {
                  extract($row, EXTR_OVERWRITE);
                  $usernameTutorAttivi[]=$username;
                  echo("<option selected value='$username'>$nome $cognome</option>");
                }

                ?>
                
                <?php
                //devo mettere le opzione per i tutor qui
                $query = "SELECT * FROM login WHERE permesso=2";
                $result = $link->query($query);
                while ($row = $result->fetch_assoc()) {
                  $username = $row["username"];
                  $nome = $row["nome"];
                  $cognome = $row["cognome"];
                  if(!in_array($username,$usernameTutorAttivi)) echo ("<option value='$username'>$nome $cognome</option>"); //per non avere nomi tutor ripetuti
                }
                ?>
              </select>
            </div>
            <div class="mb-3">
              <label for="tipoVoto">Seleziona Tipo di voti</label>
              <select class="form-select py-2" id="tipoVoto" name="tipoVoto">
                <option selected style="display:none" value="<?php echo "$tipoVoto" ?>"><?php echo "$tipoVoto"; ?></option>
                <?php include("optionTipiVoti.html"); ?>
              </select>
            </div>
            <div class="mb-3">
              <label for="tipoVoto">Seleziona software plagiarismo</label>
              <select class="form-select py-2" id="correzione" name="correzione">
                <option selected style="display:none" value="<?php echo "$correzione" ?>"><?php if (!empty($correzione)) echo "$correzione";
                                                                                          else echo "Nessuno" ?></option>
                <?php include("optionCorrezioneModifica.html"); ?>
              </select>
            </div>
            <div class="form-floating mb-3">
              <input type="url" class="form-control" id="sitoCorso" name="sitoCorso" placeholder="Sito del corso" value="<?php echo "$sitoCorso" ?>" />
              <label for="sitoCorso">Sito del corso</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="categorie" name="categorie" placeholder="Categorie degli esercizi (separate da uno spazio)" value="<?php echo "$categorie" ?>" required />
              <label for="categorie">Categorie degli esercizi (separate da uno spazio)</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="estensione" name="estensione" placeholder="Estensione file esercizi" value="<?php echo str_replace("|"," ",$estensione) ?>" required />
              <label for="estensione">Estensione file esercizi</label>
            </div><br>


            <div class="row">
              <div class="col-2">
                <input class="btn btn-primary" type="submit" name="modifica" value="Modifica" />
              </div>
              <div class="col-9 text-end">
                <input class="form-check-input" type="checkbox" name="checkElimina" id="checkElimina">
                <label for="checkElimina">Conferma Eliminazione</label><br>
              </div>
              <div class="col-1">
                <input class="btn btn-danger" type="submit" name="elimina" value="Elimina" />
              </div>
            </div>
          </form><br>
          <p>Per le categorie per esempio se volessi categorie html,xml e mysql dovrei inserire 'html xml mysql'</p>
          <p>Per le estensioni inserire solo l'estensione senza punto, ad esempio per un file rar scriverei 'rar'</p>
        </div>


      </div>
    </div>
  </div>
</body>

</html>