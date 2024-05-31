//FUNCION PRINCIPAL PARA MOSTRAR VENTANAS EN EL APARTADO DE PERLAS
function printdiv(condi, idiv, dir, xtra) {
  dire = "./"+dir+".php";
    $.ajax({
      url: dire,
      method: "POST",
      data: { condi, xtra},
      success: function (data) {
        $(idiv).html(data);
      }
    })
  }