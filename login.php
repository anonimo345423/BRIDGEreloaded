<?php
session_start();
ob_start();
?>
<html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="favicon.ico">
  <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css" />
  <link rel="stylesheet" href="css/stileBootstrap.css">
  <title>Login</title>
</head>

<body class="bg-dark">
  <h1 class="text-center text-white mt-4 accent-color">Bridge Login</h1>
  <div class="container px-5 py-5 mt-5 bg-light rounded-3">
    <?php
    $msg = '';
    require_once("connection.php");

    //check post
    if (isset($_POST['login']) && !empty($_POST['username']) && !empty($_POST['password'])) {
      $username = strtolower(trim($_POST['username']));
      $psw = $_POST['password'];
      $query = "SELECT nome, cognome, permesso, username, psw, mail FROM login WHERE username=? AND psw=?";
      $stmt = $link->prepare($query);
      $pswHash = hash('sha512', $psw);
      $stmt->bind_param("ss", $username, $pswHash);
      $stmt->execute();
      $result = $stmt->get_result();
      if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        $_SESSION['nome'] = $row["nome"];
        $_SESSION['cognome'] = $row["cognome"];
        $_SESSION['permesso'] = $row["permesso"];
        $_SESSION['mail'] = $row["mail"];
        $_SESSION['username'] = $row["username"];
        $_SESSION['valid'] = true;
      } else $msg = '<br><div class="alert alert-danger" role="alert">
         Username o password sbagliati
       </div>';
    }

    //redirect
    if (isset($_SESSION['valid'])) {
      if ($_SESSION['permesso'] == 1) header("Location: utente/utente.php");
      else if ($_SESSION['permesso'] == 2) header("Location: docenteTutor/docenteTutor.php"); //tutor e docente hanno stesse pagine ma sidebar diverse
      else if ($_SESSION['permesso'] == 3) header("Location: docenteTutor/docenteTutor.php");
      else if ($_SESSION['permesso'] == 4) header("Location: admin/admin.php");
    }


    ?>

    <h2>Inserisci mail e password</h2>
    <p> Una volta fatto il login verrai reindirizzato alla tua pagina personale</p>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="floatingInput" name="username" placeholder="Matricola" required autofocus />
        <label for="floatingInput">Matricola</label>
      </div>
      <div class="form-floating mb-3">
        <input type="password" class="form-control" id="floatingPsw" name="password" placeholder="Password" required />
        <label for="floatingPsw">Password</label>
      </div>
      <div>
        <input class="btn btn-primary" type="submit" name="login" value="Login" />
        <span class="mx-3">Oppure</span>
        <a href="register.php" class="btn btn-outline-primary">Registrati</a>
      </div>
    </form>
  </div>
  <div class="container">
    <h5><?php echo $msg; ?></h5>
  </div>
  </div>

</body>

</html>