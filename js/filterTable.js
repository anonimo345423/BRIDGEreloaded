function filterTable() {
  input = document.getElementById("myInput");
  filter = input.value.toUpperCase();
  flag = 0;
  $('#myTable tbody tr').each(function (index, tr) {
    $(tr).find('td').each(function (index, td) {
      if ($(td).text().toUpperCase().indexOf(filter) > -1) flag = 1;
    });
    if (flag == 0) $(tr).hide();
    else {
      if($(tr).attr("class")!="show_hide") $(tr).show();
    }
    flag = 0; //perchè passo alla prossima tr e rivoglio la flag a 0
  });
  flag = 0;
  $('#myTable2 tbody tr').each(function (index, tr) {
    $(tr).find('td').each(function (index, td) {
      if ($(td).text().toUpperCase().indexOf(filter) > -1) flag = 1;
    });
    if (flag == 0) $(tr).hide();
    else {
      if($(tr).attr("class")!="show_hide") $(tr).show();
    }
    flag = 0; //perchè passo alla prossima tr e rivoglio la flag a 0
  });
}
