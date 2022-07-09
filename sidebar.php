<?php
if (isset($_SESSION['permesso'])) {
  if ($_SESSION['permesso'] == 1) {
    echo ('<div class="col-2 bg-dark">');
    require_once("utente/sidebarUtente.html");
    echo ('</div>');
  } else if ($_SESSION['permesso'] == 2) {
    echo ('<div class="col-2 bg-dark">');
    require_once("docenteTutor/sidebarTutor.html");
    echo ('</div>');
  } else if ($_SESSION['permesso'] == 3) {
    echo ('<div class="col-2 bg-dark">');
    require_once("docenteTutor/sidebarDocente.html");
    echo ('</div>');
  } else if ($_SESSION['permesso'] == 4) {
    echo ('<div class="col-2 bg-dark">');
    require_once("admin/sidebarAdmin.html");
    echo ('</div>');
  }
}
