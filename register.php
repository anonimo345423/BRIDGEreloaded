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
  <title>Registrati</title>
</head>

<body class="bg-dark">
  <h1 class="text-center text-white mt-4 accent-color">Bridge Registrazione</h1>
  <div class="container px-5 py-5 mt-5 bg-light rounded-3">
    <?php
    $msg = '';

    require_once("connection.php");

    //check post e gestione dati
    if (isset($_POST['register']) && !empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['password2']) && !empty($_POST['mail']) && !empty($_POST['nome']) && !empty($_POST['cognome'])) {
      $username = strtolower(trim($_POST['username']));
      $psw = $_POST['password'];
      $psw2 = $_POST['password2'];
      $pswHash = hash('sha512', $psw);
      $mail = strtolower(trim($_POST['mail']));
      $nome = ucfirst(strtolower(trim($_POST['nome'])));
      $cognome = ucfirst(strtolower(trim($_POST['cognome'])));

      //Preparo i check, check matricola
      $query = "SELECT nome, cognome, permesso, username, psw, mail FROM login WHERE username=?";
      $stmt = $link->prepare($query);
      $stmt->bind_param("s", $username);
      $stmt->execute();
      $result = $stmt->get_result();

      //check mail
      $query = "SELECT nome, cognome, permesso, username, psw, mail FROM login WHERE mail=?";
      $stmt = $link->prepare($query);
      $stmt->bind_param("s", $mail);
      $stmt->execute();
      $result2 = $stmt->get_result();


      //check effettivi
      if ($result->num_rows > 0) $msg = '<br><div class="alert alert-danger" role="alert">
            Questa matricola è già stata usata, prova ad usare la funzione di <a href="recuperoPsw.php">recupero password</a>
            </div>';
      else if ($result2->num_rows > 0) $msg = '<br><div class="alert alert-danger" role="alert">
            Questa mail è già stata usata, prova ad usare la funzione di <a href="recuperoPsw.php">recupero password</a>
            </div>';
      //check psw e conferma psw uguali
      else if ($psw != $psw2) $msg = '<br><div class="alert alert-danger" role="alert">
            Le password non corrispondono
            </div>';
      else {
        //sql
        $query = "INSERT INTO login (username,psw,nome,cognome,mail) VALUES(?,?,?,?,?)";
        $stmt = $link->prepare($query);
        $stmt->bind_param("sssss", $username, $pswHash, $nome, $cognome, $mail);
        $stmt->execute();
        //session
        $_SESSION['nome'] = $nome;
        $_SESSION['cognome'] = $cognome;
        $_SESSION['permesso'] = 1;
        $_SESSION['mail'] = $mail;
        $_SESSION['username'] = $username;
        $_SESSION['valid'] = true;
        header("Location: login.php");
        exit();

        
      }
    }

    //redirect
    if (isset($_SESSION['valid'])) {
      header("Location: login.php");
      exit();
    }


    ?>

    <h2>Inserisci mail e password</h2>
    <p> Una volta fatto il login verrai reindirizzato alla tua pagina personale</p>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome" value="<?php if (isset($nome)) echo $nome ?>" required autofocus />
        <label for="nome">Nome</label>
      </div>
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="cognome" name="cognome" placeholder="Cognome" value="<?php if (isset($cognome)) echo $cognome ?>" required />
        <label for="cognome">Cognome</label>
      </div>
      <div class="form-floating mb-3">
        <input type="email" class="form-control" id="mail" name="mail" placeholder="Mail istituzionale" value="<?php if (isset($mail)) echo $mail ?>" required />
        <label for="mail">Mail istituzionale</label>
      </div>
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="username" name="username" placeholder="Matricola" value="<?php if (isset($username)) echo $username ?>" required />
        <label for="username">Matricola</label>
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
        <input class="btn btn-primary" type="submit" name="register" value="Registrati" />
        <span class="mx-3">Oppure</span>
        <a href="login.php" class="btn btn-outline-primary">Login</a>
      </div>
    </form>
  </div>
  <div class="container">
    <h5><?php echo $msg; ?></h5>
  </div>
  </div>

</body>

</html>