$(function () {//quel che fa questo .js è resize delle textareas quando il testo è + grande delle rows
  //questa prima parte quando c'è un input
  $(document).on('mousemove', 'textarea', function (e) {
    var a = $(this).offset().top + $(this).outerHeight() - 16,	//	top border of bottom-right-corner-box area
      b = $(this).offset().left + $(this).outerWidth() - 16;	//	left border of bottom-right-corner-box area
    $(this).css({
      cursor: e.pageY > a && e.pageX > b ? 'nw-resize' : ''
    });
  })

    .on('keyup', 'textarea', function (e) {

      while ($(this).outerHeight() < this.scrollHeight + parseFloat($(this).css("borderTopWidth")) + parseFloat($(this).css("borderBottomWidth"))) {
        $(this).height($(this).height() + 1);
      };
    });
});
$(document).ready(function () { //questa seconda quando si carica la pagina
  $('textarea').each(function(){
    $(this).height($(this)[0].scrollHeight );
  });
  $("textarea").css("overflow", "hidden");
  $("textarea").css("resize", "none");
});