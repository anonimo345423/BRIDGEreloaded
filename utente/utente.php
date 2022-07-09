<?php //LA PAGINA era impostata per cambiare anche username, nome e cognome, ma faceva + danno che altro quindi è stato disabilitato
//in seguito alla fine della pagina.
$h3 = "Cosa &egrave; Bridge?";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || $permesso != 1) {
  header("Location: ../login.php");
  exit();
}
$username = $_SESSION["username"];
?>
<html>

<body>
  <div class="container-fluid">
    <div class="row">
      <?php
      require_once("../sidebar.php");
      //gestione post
      if (isset($_POST['modifica'])) {
        $mail = strtolower(trim($_POST['mail']));
        //update sql in base a se ho o meno psw
        if (!empty($_POST['psw'])) {
          $psw = trim($_POST['psw']);
          $psw = hash('sha512', $psw);
          $query = "UPDATE login set mail=?, psw=? WHERE username=?";
          $stmt = $link->prepare($query);
          $stmt->bind_param("sss", $mail, $psw, $username);
          $stmt->execute();
        } else {
          $query = "UPDATE login set mail=? WHERE username=?";
          $stmt = $link->prepare($query);
          $stmt->bind_param("ss", $mail, $username);
          $stmt->execute();
        }
        $self = $_SERVER['PHP_SELF'];
        header("Location: $self?succ=1"); //aggiorno dati
        exit();
      }
      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">
          <?php
          if(isset($_GET["succ"])) echo '<div class="alert alert-success" role="alert">
          Dati modificati
         </div>';
          ?>
          <h3><?php echo $h3 ?></h3>
          <p><?php 
          $query="SELECT messaggio FROM homestudente";
          $result = $link->query($query);
          $row = $result->fetch_assoc();
          if(!empty($row)) extract($row, EXTR_OVERWRITE);
          if(!empty($messaggio)) echo $messaggio;
          ?>
          </p>

          <?php
          $query = "SELECT *
        FROM login
        WHERE username=?";
          $stmt = $link->prepare($query);
          $stmt->bind_param("s", $username);
          $stmt->execute();
          $result = $stmt->get_result();
          $row = $result->fetch_assoc();
          if (!empty($row)) extract($row);

          //ho disattivato nome e cognome perchè cambiandoli si creano problemi con gli esercizi consegnati
          ?>

          <form class="container" style="max-width: 800px; !important" action="<?php echo (htmlspecialchars($_SERVER['PHP_SELF'])); ?>" method="post">
            <h3>I tuoi dati:</h3>
            <br>
            <div class="form-floating mb-3 row">
              <input type="text" class="form-control" id="usernameNew" name="usernameNew" placeholder="Matricola" value="<?php echo "$username" ?>" required disabled />
              <label for="usernameNew">Matricola</label>
            </div>
            <div class="form-floating mb-3 row">
              <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome" value="<?php echo "$nome" ?>" required disabled />
              <label for="nome">Nome</label>
            </div>
            <div class="form-floating mb-3 row">
              <input type="text" class="form-control" id="cognome" name="cognome" placeholder="Cognome" value="<?php echo "$cognome" ?>" required disabled />
              <label for="cognome">Cognome</label>
            </div>

            <div class="form-floating mb-3 row">
              <input type="text" class="form-control" id="mail" name="mail" placeholder="Mail istituzionale" value="<?php echo "$mail" ?>" required/>
              <label for="mail">Mail istituzionale</label>
            </div>
            <div class="form-floating mb-3 row">
              <input type="password" class="form-control" id="psw" name="psw" placeholder="Password (vuoto per non modificare)" />
              <label for="psw">Password (lascia vuoto per non modificare)</label>
            </div>

            <br>
            <div class="row">
              <div class="col-2">
                <input class="btn btn-success" type="submit" name="modifica" value="modifica" />
              </div>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</body>

</html>