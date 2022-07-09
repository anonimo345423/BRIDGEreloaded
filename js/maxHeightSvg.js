$(document).ready(function () { // quando si carica la pagina
  maxHeight=$( window ).height()*80/100; //ossia altezza massima pari all' 80% dell'altezza della pagina
  if($("svg").height()>maxHeight) $("svg").height(maxHeight); //cioè se è troppo alto, accorcia
});