$(document).ready(function (e) {
  $('.show_hide').hide(); //inizia nascosto, poi chiama funzione se qualche tasto viene premuto:
  delete localStorage.vecchioId; //pulisco localstorage

  $('.control').click(function () {
    $('.show_hide').hide(); //nasconde di nuovo tutto, così da averne max 1 aperto per volta
    id = $(this).attr('id');
    id = "#testo" + id;

    if(localStorage.getItem('vecchioId')) vecchioId=localStorage.getItem('vecchioId');
    else vecchioId=0; //se non ho un id settato, lo setto 0
    //document.write("<div>"+vecchioId+"</div>");

    if(id!=vecchioId) {
      $(id).fadeIn("slow");
      localStorage.setItem('vecchioId', id); //salvo l'id così se la prossima volta viene premuto, so che voglio che venga chiuso e non aperto
    }
    else localStorage.setItem('vecchioId', 0); //se arrivo qui vuol dire che avevo id=vecchioId, allora nascondo quel che devo e reimposto vecchioId globale=0
  });
});