$(document).ready(function (e) {
  $('.show_hide').hide(); //inizia nascosto, poi chiama funzione se qualche tasto viene premuto:

  $('#control').keyup(function () {
    // If value is not empty
    if ($(this).val().length == 0 || $(this).val() < 0) {
      // Hide the element
      $('.show_hide').fadeOut("slow");
    } else {
      // Otherwise show it
      $('.show_hide').slideDown("slow");
    }
  }).keyup();
});