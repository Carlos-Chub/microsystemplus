//#region printdivs
function printdiv(condi, idiv, dir, xtra) {
    loaderefect(1);
    dire = "views_reporte/" + dir + ".php";
    $.ajax({
        url: dire, method: "POST", data: { condi, xtra },
        success: function (data) {
            loaderefect(0);
            $(idiv).html(data);
        }
    })
}

//para recargar en el mismo archivo, solo mandar id del cuadro y el extra
function printdiv2(idiv, xtra) {
    loaderefect(1);
    condi = $("#condi").val();
    dir = $("#file").val();
    dire = "views_reporte/" + dir + ".php";
    $.ajax({
        url: dire, method: "POST", data: { condi, xtra },
        success: function (data) {
            loaderefect(0);
            $(idiv).html(data);
        }
    })
}

function printdiv2_1(idiv, xtra) {
    // console.log(idiv,xtra );
    // return;
  //  loaderefect(1);
    condi = $("#condi").val();
    dir = $("#file").val();
    dire = "views_reporte/" + dir + ".php";
    console.log(idiv,xtra,condi,dir,dire );
    return;
    $.ajax({
        url: dire, method: "POST", data: { condi, xtra },
        success: function (data) {
            loaderefect(0);
            $(idiv).html(data);
        }
    })
}



//#region obtener datos de inputs, selects, radios
//---------obtener datos de inputs.. pasar datos como vectores con el id de los inputs, y retorna array
function getinputsval(datos) {
    const inputs2 = [''];
    var i = 0;
    while (i < datos.length) {
        inputs2[i] = document.getElementById(datos[i]).value;
        i++;
    }
    return inputs2;
}
//---------obtener datos de selects.. pasar datos como vectores con el id de los selects, y retorna array
function getselectsval(datos) {
    const selects2 = [''];
    i = 0;
    while (i < datos.length) {
        var e = document.getElementById(datos[i]);
        selects2[i] = e.options[e.selectedIndex].value;
        i++;
    }
    return selects2;
}
//---------obtener datos de radios.. pasar datos como vectores con el name de los radios, y retorna array
function getradiosval(datos) {
    const radios2 = [''];
    i = 0;
    while (i < datos.length) {
        radios2[i] = document.querySelector('input[name="' + datos[i] + '"]:checked').value;
        i++;
    }
    return radios2;
}
//#endregion
//#region ajax generico
function obtiene(inputs, selects, radios, condi, id, archivo) {
    var inputs2 = []; var selects2 = []; var radios2 = [];
    inputs2 = getinputsval(inputs)
    selects2 = getselectsval(selects)
    radios2 = getradiosval(radios)
    generico(inputs2, selects2, radios2, condi, id, archivo);
}
//--
function generico(inputs, selects, radios, condi, id, archivo) {
    $.ajax({
        url: "../src/cruds/crud_menu_reporte.php",
        method: "POST",
        data: { inputs, selects, radios, condi, id, archivo },
        beforeSend: function () {
            loaderefect(1);
        },
        success: function (data) {
            const data2 = JSON.parse(data);
            //  console.log(data2);
            //  return;
            if (data2[1] == "1") {
                Swal.fire({ icon: 'success', title: 'Muy Bien!', text: data2[0] })
                printdiv2("#cuadro", id);
            }
            else {
                Swal.fire({ icon: 'error', title: '¡ERROR!', text: data2[0] })
            }
        },
        complete: function () {
            loaderefect(0);
        }
    })
}
//#endregion
//FUNCION GENERAL PARA LOS REPORTES download: 1 si, 0 no(lo muestra en una nueva ventana)
function reportes(datos, tipo, file, download) {
    loaderefect(1);
    var datosval = [];
    datosval[0] = getinputsval(datos[0]); datosval[1] = getselectsval(datos[1]); datosval[2] = getradiosval(datos[2]); datosval[3] = datos[3];
    var url = "views_reporte/reportes/" + file + ".php";
    $.ajax({
        url: url, async: true, type: "POST", dataType: "html", data: { datosval, tipo },
        success: function (data) {
            // console.log(data)
            loaderefect(0);
            var opResult = JSON.parse(data);
            // console.log(opResult)
            if (opResult.status == 1) {
                switch (download) {
                    case 0:
                        const ventana = window.open();
                        ventana.document.write("<object data='" + opResult.data + "' type='application/" + opResult.tipo + "' width='100%' height='100%'></object>")
                        break;
                    case 1:
                        var $a = $("<a href='" + opResult.data + "' download='" + opResult.namefile + "." + tipo + "'>");
                        $("body").append($a);
                        $a[0].click();
                        $a.remove();
                        break;
                }
                Swal.fire({ icon: 'success', title: 'Muy Bien!', text: opResult.mensaje })
            }
            else {
                Swal.fire({ icon: 'error', title: '¡ERROR!', text: opResult.mensaje })
            }
        }
    })
}
//#endregion
//#region FUNCIONES REUTILIZABLES}
function changedisabled(padre, status) {
    if (status == 0) $(padre).attr('disabled', 'disabled');
    else $(padre).removeAttr('disabled');
}
//#endregion

//#region 
function activar_select_cuentas(radio, estado, select) {
    if (radio.checked) {
      if (estado) {
        document.getElementById(select).disabled = estado;
        $("#" + select).val(0);
      }
      else {
        //cuando se seleccionan una cuenta se habilita el select
        document.getElementById(select).disabled = estado;
      }
    }
  }
//#endregion