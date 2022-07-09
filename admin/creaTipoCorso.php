<?php
$h3 = "Crea tipologia di corso";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || $permesso != 4) {
  header("Location: ../login.php");
  exit();
}
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
      if (isset($_GET["error"])) $msg = '<br><div class="alert alert-danger" role="alert">
        Impossibile inserire! Magari esiste gi&agrave; un corso con questo nome o con lo stesso nome per gli account delle tesine
        </div>';
      //gestione post
      if (isset($_POST['crea'])) {
        $nomeCorso = trim(htmlspecialchars($_POST["nomeCorso"], ENT_QUOTES));
        $nomeCorso = str_replace(" ", "_", $nomeCorso);
        if (!empty($_POST['maxStudentiTesine'])) $maxStudentiTesine = $_POST['maxStudentiTesine'];
        else $maxStudentiTesine = 0;
        if (!empty($_POST['nomeAccount'])) $nomeAccount = $_POST['nomeAccount'];
        else $nomeAccount = null;
        if (!empty($_POST['numeroAccount'])) $numeroAccount = $_POST['numeroAccount'];
        else $numeroAccount = null;
        $docente = $_POST['docente'];
        if (!empty($_POST['tutor'])) $tutors = $_POST['tutor'];
        else $tutors = null;
        $tipoVoto = $_POST['tipoVoto'];
        $correzione = $_POST['correzione'];
        if ($correzione == "nessuno") $correzione = null;
        $sitoCorso = trim(htmlspecialchars($_POST["sitoCorso"], ENT_QUOTES));
        $categorie = strtolower(trim($_POST['categorie']));


        $catArr = explode(" ", $categorie);
        $i = 1;
        $catArrFinale = [];
        $len = count($catArr);
        foreach ($catArr as $cat) {
          if ($i != $len) $catArrFinale[] = $i . $cat . "|";
          else $catArrFinale[] = $i . $cat;
          $i++;
        }
        $categorie = implode("", $catArrFinale);
        $estensione = str_replace(" ", "|",strtolower(trim($_POST['estensione'])));
        if (!empty($_POST['statiTesine'])) $statiTesine = $_POST['statiTesine'];
        else $statiTesine = 0;
        if($maxStudentiTesine!=0) $autoAssegnazione = $_POST['autoAssegnazione'];
        else $autoAssegnazione=0;

        $msg = '<br><div class="alert alert-success" role="alert">
                Tipologia di Corso creata corretamente!
                </div>';

        //sql
        $query = "INSERT INTO tipicorsi (nomeCorso, maxStudentiTesine, nomeAccount, numeroAccount, docente, sitoCorso, categorie, estensione, tipoVoto, correzione, statiTesine, autoAssegnazione) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $link->prepare($query);
        $stmt->bind_param("sisissssssii", $nomeCorso, $maxStudentiTesine, $nomeAccount, $numeroAccount, $docente, $sitoCorso, $categorie, $estensione, $tipoVoto, $correzione, $statiTesine, $autoAssegnazione);
        try {
          $stmt->execute();
        }
        //se fallisce inserimento, primary key violata:
        catch (exception $e) {
          $self = $_SERVER['PHP_SELF'];
          header("Location: $self?error=1");
        }
        //ora inserisco tutti i tutor nella tabella, prima però prendo idTipoCorso
        $query = "SELECT idTipoCorso FROM tipicorsi WHERE nomeCorso=?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("s", $nomeCorso);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $idTipoCorso = $row["idTipoCorso"];
        foreach ((array)$tutors as $tutor) {
          $query = "INSERT INTO tutoraggio (tutor,idTipoCorso)
          VALUES (?,?)";
          $stmt = $link->prepare($query);
          $stmt->bind_param("si", $tutor, $idTipoCorso);
          $stmt->execute();
        }
      }


      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">
          <?php echo $msg; ?>

          <h3 class='mb-5 mt-3'><?php echo $h3 ?></h3>
          <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
          <p>Il nome del tipo di corso pu&ograve; contenere trattini "-" e caratteri alfanumerici</p>
            <div class="form-floating mb-3">
              <input type="text" pattern="[a-zA-Z0-9- ]+" class="form-control" id="nomeCorso" name="nomeCorso" placeholder="Nome del tipo di corso" required autofocus />
              <label for="nomeCorso">Nome del tipo di corso</label>
            </div>

            <div class="form-floating mb-3">
              <input type="number" class="form-control" id="control" name="maxStudentiTesine" placeholder="Studenti massimi per tesina (vuoto se non ci sono tesine)" />
              <label for="maxStudentiTesine">Studenti massimi per tesina (vuoto se non ci sono tesine)</label>
            </div>
            <div class="form-floating mb-3">
              <input type="number" class="form-control show_hide" id="statiTesine" name="statiTesine" placeholder="Numero stati tesine" />
              <label for="statiTesine" class="show_hide">Numero stati tesine</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control show_hide" id="nomeAccount" name="nomeAccount" placeholder="Nome account per tesine (facoltativo)" />
              <label for="nomeAccount" class="show_hide">Nome account per tesine (facoltativo)</label>
            </div>
            <div class="form-floating mb-3">
              <input type="number" class="form-control show_hide" id="numeroAccount" name="numeroAccount" placeholder="Numero account totali (facoltativo)" />
              <label for="numeroAccount" class="show_hide">Numero account totali (facoltativo)</label>
            </div>
            <div class="show_hide">Vuoi che gli studenti possano autoassegnarsi la tesina?</div>
            <div class="row py-4 show_hide">
              <div class="col-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="autoAssegnazione" id="autoAssegnazione1" value="1" checked="checked">
                  <label class="form-check-label" for="autoAssegnazione1">
                    Si
                  </label>
                </div>
              </div>
              <div class="col-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="autoAssegnazione" value="0" id="autoAssegnazione2">
                  <label class="form-check-label" for="autoAssegnazione2">
                    No
                  </label>
                </div>
              </div>
            </div>


            <div class="mb-3">
              <label for="docente">Seleziona Docente</label>
              <select class="form-select py-2" id="docente" name="docente" required>
                <option disabled selected style="display:none"></option>
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
              <label for="tutor">Seleziona Tutor (facoltativo, anche più di uno usando il tasto ctrl)</label>
              <select class="form-select py-2" id="tutor" name="tutor[]" multiple>
                <option selected>Nessuno</option>
                <?php
                //devo mettere le opzione per i tutor qui
                $query = "SELECT * FROM login WHERE permesso=2";
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
              <label for="tipoVoto">Seleziona Tipo di voti</label>
              <select class="form-select py-2" id="tipoVoto" name="tipoVoto" required>
                <option disabled selected style="display:none"></option>
                <!--devo mettere le opzione per i tipi di voti-->
                <?php include("optionTipiVoti.html"); ?>
              </select>
            </div>
            <div class="mb-3">
              <label for="tipoVoto">Seleziona software plagiarismo</label>
              <select class="form-select py-2" id="correzione" name="correzione">
                <!--devo mettere le opzione per i tipi di sw-->
                <?php include("optionCorrezione.html"); ?>
              </select>
            </div>
            <div class="form-floating mb-3">
              <input type="url" class="form-control" id="sitoCorso" name="sitoCorso" placeholder="Sito del corso" />
              <label for="sitoCorso">Sito del corso</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="categorie" name="categorie" placeholder="Categorie degli esercizi (separate da uno spazio)" required />
              <label for="categorie">Categorie degli esercizi (separate da uno spazio)</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="estensione" name="estensione" placeholder="Estensione file esercizi" required />
              <label for="estensione">Estensione file esercizi</label>
            </div>
            

            <div>
              <input class="btn btn-primary" type="submit" name="crea" value="Crea" />
            </div>
          </form><br>
          <p>Per le categorie per esempio se volessi categorie html,xml e mysql dovrei inserire 'html xml mysql'<br>
            Gli esercizi saranno mostrati agli studenti per ordine alfabetico delle categorie, se si vuole un determinato ordinate chiamare le categorie (1)nomePrima (2)nomeSeconda ecc...</p>
          <p>Per le estensioni inserire una o più estensioni senza punto, ad esempio per accettate file rar e zip scriverei 'rar zip'<br>
          se l'estensione è composta (tipo .tar.gz) omettere solo il primo punto in questo modo: 'tar.gz'</p>
        </div>


      </div>
    </div>
  </div>
</body>

</html>