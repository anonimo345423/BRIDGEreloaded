<?php

//drop database if exists, then create:
$host = "localhost";
$user = "root";
$pswDB = "";
$link = mysqli_connect($host, $user, $pswDB);
if (mysqli_connect_errno()) { 
  echo "<p style=\"color:white;\">Errore di connessione al database.\n" . mysqli_connect_error() . "</p>";
  exit;
}
$query = "DROP DATABASE IF EXISTS bridge";
$result = mysqli_query($link, $query);
$query = "CREATE DATABASE bridge";
$result = mysqli_query($link, $query);




//connessione al db creato
require_once("connection.php");
//install, permesso 1 utente, 2 tutor, 3 docente, 4 admin
$login = "CREATE TABLE login (
nome VARCHAR(30) NOT NULL,
cognome VARCHAR(30) NOT NULL,
permesso INT(1) DEFAULT 1,
username VARCHAR(15) PRIMARY KEY,
psw VARCHAR(64) NOT NULL,
mail VARCHAR(50) UNIQUE
)";

$datiLogin = "INSERT INTO login (nome, cognome, permesso, username, psw, mail) VALUES 
('utenteN', 'utenteC', 1, 'utente','a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3','uno@gmail.com'),
('tutorN', 'tutorC', 2, 'tutor','a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3','due@gmail.com'), 
('docenteN', 'docenteC', 3, 'docente','a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3','tre@gmail.com'), 
('AdminN', 'adminC', 4, 'admin','a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3','quattro@gmail.com')";

mysqli_query($link, $login) or die(mysqli_error($link));
mysqli_query($link, $datiLogin) or die(mysqli_error($link));

//maxStudentiTesine indica il numero massimo di studenti per ogni tesina, se = 0 allora le tesine sono disabilitate.
//Categorie invece indica i tipi di categorie di esercizi per quel corso separate da '|'
$tipicorsi = "CREATE TABLE tipicorsi (
idTipoCorso INT(4) PRIMARY KEY AUTO_INCREMENT,
nomeCorso VARCHAR(60) NOT NULL UNIQUE,
maxStudentiTesine INT(1) DEFAULT 0,
nomeAccount VARCHAR(20) UNIQUE,
numeroAccount INT(4),
docente VARCHAR(15),
tipoVoto VARCHAR(15) NOT NULL,
correzione VARCHAR(15) DEFAULT null,
sitoCorso VARCHAR(120),
categorie VARCHAR(80),
statiTesine int(1) DEFAULT 0,
estensione VARCHAR(5) NOT NULL,
autoAssegnazione int(1) DEFAULT 1,
FOREIGN KEY (docente) REFERENCES login(username) ON DELETE SET NULL ON UPDATE CASCADE
)";

$datiTipiCorsi = "INSERT INTO tipicorsi (idTipoCorso, nomeCorso, maxStudentiTesine, nomeAccount, numeroAccount, docente, sitoCorso, categorie, estensione, tipoVoto, correzione, statiTesine) VALUES
(1000, 'test', 3, 'lweb', 40, 'docente', 'http://www.diag.uniroma1.it/marte/homepage/teachingmt.shtml','html|xml|mysql', 'rar', 'decimale', 'sim', 3)";

mysqli_query($link, $tipicorsi) or die(mysqli_error($link));
mysqli_query($link, $datiTipiCorsi) or die(mysqli_error($link));

$tutoraggio="CREATE TABLE tutoraggio (
tutor VARCHAR(15),
idTipoCorso INT(4),
PRIMARY KEY (tutor,idTipoCorso),
FOREIGN KEY (tutor) REFERENCES login(username) ON DELETE CASCADE ON UPDATE CASCADE,
FOREIGN KEY (idTipoCorso) REFERENCES tipicorsi(idTipoCorso) ON DELETE CASCADE 
)";
mysqli_query($link, $tutoraggio) or die(mysqli_error($link));


//il campo extra indica se la tesina è extra, null=no, un altro numero indica eventualmente il corso a cui fa riferimento. Notifica diventa 1 quando qualcuno consegna e poi torna 0 quando prof apre pagina corso
$corsi = "CREATE TABLE corsi (
    idCorso INT(5) PRIMARY KEY AUTO_INCREMENT,
    idTipoCorso INT(4) NOT NULL,
    anno VARCHAR(10),
    istruzoni varchar(2000),
    notifica INT(1) DEFAULT 0,
    archiviato INT(1) DEFAULT 0,
    UNIQUE (idTipoCorso,anno),
    FOREIGN KEY (idTipoCorso) REFERENCES tipicorsi(idTipoCorso) ON DELETE CASCADE
    )";

$datiCorsi = "INSERT INTO corsi (idCorso, idTipoCorso, anno, notifica) VALUES
(10000, 1000, '2021-2022', 0), (10001, 1000, '2020-2021', 0)";
mysqli_query($link, $corsi) or die(mysqli_error($link));
mysqli_query($link, $datiCorsi) or die(mysqli_error($link));

$iscritto = "CREATE TABLE iscritto (
    idCorso INT(5) AUTO_INCREMENT,
    username VARCHAR(15) NOT NULL,
    FOREIGN KEY (idCorso) REFERENCES corsi(idCorso) ON DELETE CASCADE,
    FOREIGN KEY (username) REFERENCES login(username) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (idCorso,username)
    )";
mysqli_query($link, $iscritto) or die(mysqli_error($link));

//categoria indica di che categoria è quell'esercizio, basato sulle categorie presenti nel corso
//un esercizio disabilitato è come se fosse eliminato ma rimane per chi lo ha fatto nei vecchi anni
$tipiesercizi = "CREATE TABLE tipiesercizi (
    idTipoEsercizio INT(4) PRIMARY KEY AUTO_INCREMENT,
    idTipoCorso INT(4) NOT NULL,
    obbligatorio INT(1) DEFAULT 0,
    categoria VARCHAR(30),
    testo VARCHAR(5000),
    disabilitato INT(1) DEFAULT 0,
    FOREIGN KEY (idTipoCorso) REFERENCES tipicorsi(idTipoCorso) ON DELETE CASCADE
    )";

$datiTipiEsercizi = "INSERT INTO tipiesercizi (idTipoEsercizio, idTipoCorso, obbligatorio, categoria, testo) VALUES
(1, 1000, 1, 'xml', 'obbligXml1'), (2, 1000, 1, 'xml','obbligXml2 testo lungo e noioso per dare un esempio bla bla bla testo lungo e noioso per dare un esempio bla bla bla testo lungo e noioso per dare un esempio bla bla bla testo lungo e noioso per dare un esempio bla bla bla testo lungo e noioso per dare un esempio bla bla bla testo lungo e noioso per dare un esempio bla bla bla testo lungo e noioso per dare un esempio bla bla bla'),
(3, 1000, 1, 'html', 'htmlObblig1'),(4, 1000, 0, 'xml', 'nonObbligXml1'),(5, 1000, 1, 'mysql', 'obbligMysql')";
mysqli_query($link, $tipiesercizi) or die(mysqli_error($link));
mysqli_query($link, $datiTipiEsercizi) or die(mysqli_error($link));

//una tesina disabilitata è come se fosse eliminato ma rimane per chi lo ha fatto nei vecchi anni
$tipitesine = "CREATE TABLE tipitesine (
    idTipoTesina INT(4) PRIMARY KEY AUTO_INCREMENT,
    idTipoCorso INT(4) NOT NULL,
    titolo VARCHAR(30) NOT NULL UNIQUE,
    extra INT(5),
    testo VARCHAR(5000),
    mesiScadenza INT(2) DEFAULT 0,
    disabilitato INT(1) DEFAULT 0,
    FOREIGN KEY (idTipoCorso) REFERENCES tipicorsi(idTipoCorso) ON DELETE CASCADE,
    FOREIGN KEY (extra) REFERENCES corsi(idCorso) ON DELETE CASCADE
    )";
$datiTipiTesine = "INSERT INTO tipitesine (idTipoTesina, idTipoCorso, extra, titolo, testo, mesiScadenza) VALUES
(1, 1000, null, 'Tesina1','Testotesina1', 8), (2, 1000, null, 'Tesina2','Testotesina2',0),
(3, 1000, null, 'Tesina3','Testotesina3',0)";
mysqli_query($link, $tipitesine) or die(mysqli_error($link));
mysqli_query($link, $datiTipiTesine) or die(mysqli_error($link));

//il vincolo di 1 esercizio obbligatorio per categoria viene tenuto saldo in php, visto che qui non ci si può riferire alla categoria.
$esercizi = "CREATE TABLE esercizi (
    idEsercizio INT(4) PRIMARY KEY AUTO_INCREMENT,
    idTipoEsercizio INT(4) NOT NULL,
    username VARCHAR(15) NOT NULL,
    idCorso INT(5) NOT NULL,
    dataOra DATETIME NOT NULL,
    numeroConsegne int(3) DEFAULT 1, 
    stato INT(1) DEFAULT 1,
    voto INT(2),
    UNIQUE(idCorso,idTipoEsercizio,username),
    FOREIGN KEY (idTipoEsercizio) REFERENCES tipiesercizi(idTipoEsercizio) ON DELETE CASCADE,
    FOREIGN KEY (username) REFERENCES login(username) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (idCorso) REFERENCES corsi(idCorso) ON DELETE CASCADE
    )"; //lo stato dell'esercizio non va confuso con lo stato di avanzamento delle tesine
mysqli_query($link, $esercizi) or die(mysqli_error($link));
$commentiesercizi = "CREATE TABLE commentiesercizi (
    idCommentoEsercizio INT(4) PRIMARY KEY AUTO_INCREMENT,
    idEsercizio INT(4) NOT NULL,
    dataOraCommento DATETIME NOT NULL,
    numeroConsegna int(3) NOT NULL,
    riservato INT(1) DEFAULT 0,
    commento VARCHAR(5000),
    FOREIGN KEY (idEsercizio) REFERENCES esercizi(idEsercizio) ON DELETE CASCADE
)"; //dataOraCommento si riferisce alla data e ora di quando l'esercizio è stato consegnato l'ultima volta
mysqli_query($link, $commentiesercizi) or die(mysqli_error($link));


//La scadenza si calcola in php all'assegnazione come dataOggi+mesi scadenza, se come scadenza viene messo mai si lascia null
$tesine = "CREATE TABLE tesine (
    idTesina INT(4) PRIMARY KEY AUTO_INCREMENT,
    idTipoTesina INT(4) NOT NULL,
    idCorso INT(5) NOT NULL,
    stato INT(1) DEFAULT 1,
    scadenza DATE,
    UNIQUE(idCorso,idTipoTesina),
    FOREIGN KEY (idTipoTesina) REFERENCES tipitesine(idTipoTesina) ON DELETE CASCADE,
    FOREIGN KEY (idCorso) REFERENCES corsi(idCorso) ON DELETE CASCADE
    )";
$commentitesine = "CREATE TABLE commentitesine (
    idCommentoTesina INT(4) PRIMARY KEY AUTO_INCREMENT,
    idTesina INT(4) NOT NULL,
    dataOraCommento DATETIME NOT NULL,
    statoCommento int(1) NOT NULL,
    riservato INT(1) DEFAULT 0,
    commento VARCHAR(5000),
    FOREIGN KEY (idTesina) REFERENCES tesine(idTesina) ON DELETE CASCADE
)";

mysqli_query($link, $tesine) or die(mysqli_error($link));
mysqli_query($link, $commentitesine) or die(mysqli_error($link));
//questa tabella indica gli studenti assegnati ad una tesina, 1 tesina può avere n studenti (fino a maxStudentiTesina definita in tipicorsi)
$studentitesina = "CREATE TABLE studentitesina(
    idStudenteTesina INT(4) PRIMARY KEY AUTO_INCREMENT,
    idTesina INT(4) NOT NULL,
    username VARCHAR(15) NOT NULL,
    account VARCHAR(30) UNIQUE,
    UNIQUE (idTesina,username),
    FOREIGN KEY (idTesina) REFERENCES tesine(idTesina) ON DELETE CASCADE,
    FOREIGN KEY (username) REFERENCES login(username) ON DELETE CASCADE ON UPDATE CASCADE
    )";
mysqli_query($link, $studentitesina) or die(mysqli_error($link));

$home="CREATE TABLE homestudente(
  messaggio VARCHAR(3000) PRIMARY KEY
  )";
  mysqli_query($link, $home) or die(mysqli_error($link));

$trigger = "
create trigger delete_iscritto
    before delete on iscritto
    for each row
    BEGIN
    DELETE FROM esercizi WHERE idCorso=OLD.idCorso AND username=OLD.username;
    DELETE FROM tesine WHERE idCorso=OLD.idCorso AND idTesina IN (SELECT idTesina FROM studentitesina WHERE idTesina IN (SELECT idTesina FROM studentitesina where username=OLD.username) GROUP BY idTesina HAVING count(idTesina)=1);
    DELETE studentitesina FROM studentitesina INNER JOIN tesine on tesine.idTesina=studentitesina.idTesina 
    WHERE idCorso=OLD.idCorso AND username=OLD.username;
    END;"; //questo è un trigger che parte quando uno studente viene disiscritto, eliminando i suoi esercizi e la sua tesina da quel corso
    //elimina la tesina solo se gli studenti che ci lavorano sono in numero=1, ossia se ci lavora uno solo,
    //se ci lavora + di 1 persona allora lascia la tesina ed elimina da studentitesina
mysqli_query($link, $trigger) or die(mysqli_error($link));
