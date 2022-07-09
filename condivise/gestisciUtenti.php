<?php
$h3 = "Gestisci utenti";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || $permesso < 3) {
  header("Location: ../login.php");
  exit();
}
if (!isset($_GET["username"])) {
  header("Location: gestisciUtentiSelezione.php");
  exit();
} else $username = $_GET["username"];
?>
<html>

<body>
  <div class="container-fluid">
    <div class="row">
      <?php

      require_once("../sidebar.php");
      //gestione post
      if (isset($_POST['modifica'])) {
        $username = trim(strtolower($_POST['username']));
        $usernameNew = trim(strtolower($_POST['usernameNew']));
        $nome = trim(ucfirst(strtolower($_POST['nome'])));
        $permesso = intval($_POST['permesso']);
        $cognome = trim(ucfirst(strtolower($_POST['cognome'])));
        $mail = strtolower(trim($_POST['mail']));
        //update sql in base a se ho o meno psw
        if (!empty($_POST['psw'])) {
          $psw = trim($_POST['psw']);
          $psw = hash('sha512', $psw);
          $query = "UPDATE login set username=?, nome=?, permesso=?, cognome=?, mail=?, psw=? WHERE username=?";
          $stmt = $link->prepare($query);
          $stmt->bind_param("ssissss", $usernameNew, $nome, $permesso, $cognome, $mail, $psw, $username);
          $result = $stmt->execute();
        } else {
          $query = "UPDATE login set username=?, nome=?, permesso=?, cognome=?, mail=? WHERE username=?";
          $stmt = $link->prepare($query);
          $stmt->bind_param("ssisss", $usernameNew, $nome, $permesso, $cognome, $mail, $username);
          $result = $stmt->execute();
        }
        $self = $_SERVER['PHP_SELF'];
        if ($result == true) header("Location: $self?username=$usernameNew&msgShow=1"); //Lo porto sulla pagina col giusto username nuovo
        else header("Location: $self?username=$usernameNew&msgShow=2");
      }
      if (isset($_POST['elimina']) && isset($_POST['checkElimina'])) {
        $username = trim(strtolower($_POST['username']));
        $query = "DELETE FROM login WHERE username=?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("s", $username);
        $result = $stmt->execute();
        if ($result == true) header("Location: gestisciUtentiSelezione.php?msgShow=1"); //Lo porto sulla pagina di selezione con messaggio di ok
        else header("Location: gestisciUtentiSelezione.php?msgShow=2");
      }


      //Qui invece mi procuro i parametri attuali prima sub del form per averceli pronti nel form visto dall'admin
      $query = "SELECT * from login WHERE username=?";
      $stmt = $link->prepare($query);
      $stmt->bind_param("s", $username);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();

      $nome = $row['nome'];
      $permesso = $row['permesso'];
      $cognome = $row['cognome'];
      $mail = $row['mail'];
      $psw = $row['psw'];
      $nome = $row['nome'];
      $cognome = $row['cognome'];
      if ($permesso == 1) $nomePermesso = "Utente";
      if ($permesso == 2) $nomePermesso = "Tutor";
      if ($permesso == 3) $nomePermesso = "Docente";
      if ($permesso == 4) $nomePermesso = "Admin";
      //userò questi valori come value di default negli input del form

      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">
        <?php if (isset($_GET["msgShow"]) && $_GET["msgShow"] == 1) echo '<br><div class="alert alert-success" role="alert">
                Utente modificato corretamente!
                </div>';
          if (isset($_GET["msgShow"]) && $_GET["msgShow"] == 2) echo '<br><div class="alert alert-danger" role="alert">
                Errore modifica utente
                </div>';
          ?>
          <?php if($_SESSION["permesso"]==3) echo "<h2 class='text-center'>Docente</h2>"; ?>
          <h3 class='mb-5 mt-3'><?php echo $h3 ?></h3>          <!--Aggiungo una div hidden col vecchio username per la gestione del post-->

          <?php
          if ($permesso == 1) echo "<div class='p'><strong>Attenzione:</strong> cambiando nome, cognome o matricola gli esercizi consegnati dallo studente non verranno più trovati
        in maniera automatica dal sistema in quanto rimarranno memorizzati con i dati vecchi.<br>Cambiarli perciò solo se l'utente non ha ancora consegnato nulla o in accordo con un amministratore di sistema</div><br>";
          ?>

          <form action="<?php echo (htmlspecialchars($_SERVER['PHP_SELF']) . "?username=$username"); ?>" method="post">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="usernameNew" name="usernameNew" placeholder="Matricola" value="<?php echo "$username" ?>" required autofocus />
              <label for="usernameNew">Matricola</label>
            </div>
            <div class="form-floating mb-3 d-none">
              <input type="text" class="form-control" id="username" name="username" placeholder="Matricola" value="<?php echo "$username" ?>" required />
              <label for="username">Matricola</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome" value="<?php echo "$nome" ?>" required />
              <label for="nome">Nome</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="cognome" name="cognome" placeholder="Cognome" value="<?php echo "$cognome" ?>" required />
              <label for="cognome">Cognome</label>
            </div>

            <div class="mb-3">
              <label for="permesso">Seleziona permesso</label>
              <select class="form-select py-2" id="permesso" name="permesso" required>
                <option selected style="display:none" value="<?php echo "$permesso" ?>"><?php echo "$nomePermesso" ?></option>
                <option value="1">Utente</option>
                <option value="2">Tutor</option>
                <?php
                if ($_SESSION["permesso"] == 4) echo "<option value='3'>Docente</option>
                <option value='4'>Admin</option>"; //per far si che docente non possa fare upscaling di permessi
                ?>
              </select>
            </div>

            <div class="form-floating mb-3">
              <input type="mail" class="form-control" id="mail" name="mail" placeholder="Mail" value="<?php echo "$mail" ?>" required />
              <label for="mail">Mail</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="psw" name="psw" placeholder="Password (vuoto per non modificare)" />
              <label for="psw">Password (lasciare vuoto per non modificare)</label>
            </div>


            <div class="row">
              <div class="col-2">
                <input class="btn btn-success" type="submit" name="modifica" value="Modifica" />
              </div>
              <div class="col-9 text-end">
                <input type="checkbox" name="checkElimina" id="checkElimina">
                <label for="checkElimina">Conferma eliminazione</label><br>
              </div>
              <div class="col-1">
                <input class="btn btn-danger" type="submit" name="elimina" value="Elimina" />
              </div>
            </div>
          </form>
          

        </div>


      </div>
    </div>
  </div>
</body>

</html>