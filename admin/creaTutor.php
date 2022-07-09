<?php
$h3 = "Crea tutor";
require_once("../htmlHead.php");
$permesso = $_SESSION['permesso'];
if (!isset($_SESSION['valid']) || $permesso != 4) {
  header("Location: ../login.php");
  exit();
}
?>
<html>

<body>
  <div class="container-fluid">
    <div class="row">
      <?php
      require_once("../sidebar.php");
      $msg = '';


      //check post e gestione dati
      if (isset($_POST['register'])) {
        $username = strtolower(trim($_POST['username']));
        $psw = $_POST['password'];
        $psw2 = $_POST['password2'];
        $pswHash = hash('sha512', $psw);
        $mail = strtolower(trim($_POST['mail']));
        $nome = ucfirst(strtolower(trim($_POST['nome'])));
        $cognome = ucfirst(strtolower(trim($_POST['cognome'])));

        //Preparo i check, check matricola
        $query = "SELECT username FROM login WHERE username=?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        //check mail
        $query = "SELECT mail FROM login WHERE mail=?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("s", $mail);
        $stmt->execute();
        $result2 = $stmt->get_result();


        //check effettivi
        if ($result->num_rows > 0) $msg = '<br><div class="alert alert-danger" role="alert">
            Questo username è già stata usato
            </div>';
        else if ($result2->num_rows > 0) $msg = '<br><div class="alert alert-danger" role="alert">
            Questa mail è già stata usata
            </div>';
        //check psw e conferma psw uguali
        else if ($psw != $psw2) $msg = '<br><div class="alert alert-danger" role="alert">
            Le password non corrispondono
            </div>';
        else {
          $msg = '<br><div class="alert alert-success" role="alert">
                     Tutor registrato!
                    </div>';

          //sql
          $query = "INSERT INTO login (username,psw,nome,cognome,mail,permesso) VALUES(?,?,?,?,?,2)";
          $stmt = $link->prepare($query);
          $stmt->bind_param("sssss", $username, $pswHash, $nome, $cognome, $mail);
          $stmt->execute();
          unset($nome);
          unset($cognome);
          unset($mail);
          unset($username);
        }
      }

      ?>
      <div class="col-10 bg-light">
        <div class="container-fluid pt-5">
          <h5><?php echo $msg; ?></h5>
          
          <h3 class='mb-5 mt-3'><?php echo $h3 ?></h3>          <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome" value="<?php if (isset($nome)) echo $nome ?>" required autofocus />
              <label for="nome">Nome</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="cognome" name="cognome" placeholder="Cognome" value="<?php if (isset($cognome)) echo $cognome ?>" required />
              <label for="cognome">Cognome</label>
            </div>
            <div class="form-floating mb-3">
              <input type="email" class="form-control" id="mail" name="mail" placeholder="Mail" value="<?php if (isset($mail)) echo $mail ?>" required />
              <label for="mail">Mail</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?php if (isset($username)) echo $username ?>" required />
              <label for="username">Username</label>
            </div>
            <div class="form-floating mb-3">
              <input type="password" class="form-control" id="password" name="password" placeholder="Password" required />
              <label for="password">Password</label>
            </div>
            <div class="form-floating mb-3">
              <input type="password" class="form-control" id="password2" name="password2" placeholder="Conferma Password" required />
              <label for="password2">Conferma Password</label>
            </div>


            <div>
              <input class="btn btn-primary" type="submit" name="register" value="Registra" />
            </div>
          </form>
        </div>
        <div class="container-fluid">
        </div>
      </div>


    </div>
  </div>
  </div>
</body>

</html>