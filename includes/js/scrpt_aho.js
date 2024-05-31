//LIMPIAR MODAL DE BENEFICIARIO
function printdiv(condi, idiv, dir, xtra) {
  loaderefect(1);
  dire = "aho/" + dir + ".php";
  $.ajax({
    url: dire,
    method: "POST",
    data: { condi, xtra },
    success: function (data) {
      loaderefect(0);
      $(idiv).html(data);
    }
  })
}
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
//---- -------------------------
function obtieneant(inputs, selects, radios, condi, id, archivo) {
  loaderefect(1);
  const inputs2 = []; const selects2 = []; const radios2 = [];

  inputs.forEach(function (valor, indice, array) {
    var dato = document.getElementById(valor).value;
    inputs2.push(dato);
  });

  //OBTIENE LOS DATOS DE LOS SELECT
  selects.forEach(function (valor, indice, array) {
    var e = document.getElementById(valor);
    var dato = e.options[e.selectedIndex].value;
    selects2.push(dato);
  });

  // OBTIENE LOS DATOS DE LOS RADIO BTN
  radios.forEach(function (valor, indice, array) {
    var dato = document.querySelector('input[name="' + valor + '"]:checked').value;
    radios2.push(dato);
  });
  generico(inputs2, selects2, radios2, inputs, selects, radios, condi, id, archivo);
}
var tabla;
//---- -------------------------
function obtiene(inputs, selects, radios, condi, id, archivo) {
  var inputs2 = []; var selects2 = []; var radios2 = [];
  inputs2 = getinputsval(inputs)
  selects2 = getselectsval(selects)
  radios2 = getradiosval(radios)
  generico(inputs2, selects2, radios2, inputs, selects, radios, condi, id, archivo);
}
//--
function generico(inputs, selects, radios, inputsn, selectsn, radiosn, condi, id, archivo) {
  $.ajax({
    url: "../src/cruds/crud_ahorro.php",
    method: "POST",
    data: { inputs, selects, radios, inputsn, selectsn, radiosn, condi, id, archivo },
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      const data2 = JSON.parse(data);
      if (data2[1] == "1") {
        Swal.fire({
          icon: 'success', title: 'Muy Bien!', text: data2[0]
        })
        if ((condi == "cdahommov" || condi == "crahommov")) {
          creaComprobante(data2);
          if (selects[0] == "1") {
            Swal.fire({
              title: 'Imprimir libreta?', showDenyButton: true, confirmButtonText: 'Imprimir', denyButtonText: `Cancelar`,allowOutsideClick: false
            }).then((result) => {
              if (result.isConfirmed) {
                creaLib(data2[2]);
              }
            })
          }
          printdiv2("#cuadro", id);
        } else if (condi == "liquidcrt" || condi=="printliquidcrt") {
          liquidcrt(data2);
          Swal.fire({
            title: 'Imprimir Comprobante?', showDenyButton: true, confirmButtonText: 'Imprimir', denyButtonText: `Cancelar`,
          }).then((result) => {
            if (result.isConfirmed) {
              comprobanteliquidcrt(data2);
            }
          })
          printdiv2("#cuadro", id);
        }
        else if ((condi == "create_aho_ben") || (condi == 'update_aho_ben')) {
          loaderefect(1);
          // tabla.ajax.reload();
          // traer_porcentaje_ben(id);
          cargar_datos_ben('lista_beneficiarios', id);
          loaderefect(0);
        }
        else if ((condi == 'reimpresion_recibo')) {
          // console.log(data2);
          creaComprobante(data2);
          cancelar_edit_recibo();
          printdiv2("#cuadro", id);
        }
        else {
          printdiv2("#cuadro", id);
        }
      }
      //SECCION DE REPORTES EN EXCEL Y PDF CON PHP---------------
      else if (data2[0] == "reportes_ahorros") {
        reportes_ahorros(data2);
      }
      else {
        Swal.fire({
          icon: 'error', title: '¡ERROR!', text: data2[0]
        })
      }
    },
    complete: function () {
      loaderefect(0);
    }
  })
}

function printdiv2(idiv, xtra) {
  loaderefect(1);
  condi = condimodal();
  dir = filenow();
  dire = "aho/" + dir + ".php";
  $.ajax({
    url: dire,
    method: "POST",
    data: { condi, xtra },
    success: function (data) {
      loaderefect(0);
      $(idiv).html(data);
    }
  })
}

function printdiv3(condi, idiv, xtra) {
  loaderefect(1);
  dir = filenow();
  dire = "aho/" + dir + ".php";
  $.ajax({
    url: dire,
    method: "POST",
    data: { condi, xtra },
    success: function (data) {
      loaderefect(0);
      $(idiv).html(data);
    }
  })
}

function abrir_modal(id_modal, estado, id_hidden, dato) {
  $(id_modal).modal(estado);
  //pasar el dato
  $(id_hidden).val(dato);
}


function seleccionar_cuenta_ctb(id_hidden, id) {
  var valor = $(id_hidden).val();
  if (valor == 1) {
    printdiv3('cuenta__1', '#div_cuenta1', id)
  }
  else if (valor == 2) {
    printdiv3('cuenta__2', '#div_cuenta2', id)
  }
  cerrar_modal('#modal_nomenclatura', 'hide', '#id_modal_hidden')
}

function cerrar_modal(id_modal, estado, id_hidden) {
  $(id_modal).modal(estado);
  //pasar el dato
  $(id_hidden).val("");
}


function condimodal() {
  var condi = document.getElementById("condi").value;
  return condi;
}
function filenow() {
  var file = document.getElementById("file").value;
  return file;
}
//funcion para eliminar cualquier registro
function eliminar(ideliminar, dir, xtra, condi) {
  dire = "../src/cruds/" + dir + ".php";
  Swal.fire({
    title: '¿ESTA SEGURO DE ELIMINAR?', showDenyButton: true, confirmButtonText: 'Eliminar', denyButtonText: `Cancelar`,
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: dire,
        method: "POST",
        data: { condi, ideliminar },
        beforeSend: function () {
          loaderefect(1);
        },
        success: function (data) {
          const data2 = JSON.parse(data);
          if (data2[1] == "1") {
            Swal.fire('Correcto', data2[0], 'success');
            printdiv2("#cuadro", xtra);
          }
          else Swal.fire('Uff', data2[0], 'error')
        },
        complete: function () {
          loaderefect(0);
        }
      })
    }
  })
}
//cargar datos de selects o radiobuttons
function cargaselects(datos, ids) {
  if (document.getElementById("activado").checked == false) {
    document.getElementById("activado").checked = true;
    //console.log("holi1");
    var i = 0;
    ids.forEach(function (valores, indice, array) {
      document.getElementById(valores).value = datos[i];
      i++;
    });
  }
}

function correltipcuenta(tipo, ins, ofi) {
  dire = "../src/cruds/crud_ahorro.php";
  condi = "correl";
  var ant = document.getElementsByName('targets');
  // console.log(ant)
  i = 0;
  while (i < (ant.length)) {
    ant[i].className = 'tarjeta';
    i++;
  }
  var intro = document.getElementById('' + tipo);
  intro.className = 'tarjeta tarjeta-activa';
  $.ajax({
    url: dire,
    method: "POST",
    data: { condi, tipo, ins, ofi },
    success: function (data) {
      const data2 = JSON.parse(data);
      document.getElementById("correla").value = data2[0];
      document.getElementById("tasa").value = data2[1];
      document.getElementById("tipCuenta").value = tipo;
      document.getElementById("ccodofi").value = data2[2];
    }
  })
}

function aplicarcod(codigo) {
  var cod = document.getElementById(codigo).value;
  if (cod == "") { cod = "01"; }
  printdiv2("#cuadro", cod);
}
function habdeshab(hab, des) {
  var i = 0;
  while (i < hab.length) {
    document.getElementById(hab[i]).disabled = false;
    i++;
  }
  var i = 0;
  while (i < des.length) {
    document.getElementById(des[i]).disabled = true;
    i++;
  }
}
function tipdoc(ids) {
  banco = document.getElementById('region_cheque');
  switch (ids) {
    case "E":
      banco.style = "display:none";
      break;
    case "D":
      banco.style = "display:block";
      break;
    case "C":
      banco.style = "display:block";
      break;
    case "T":
      habdeshab(['ccodahodestino'], ['nrochq', 'tipchq', 'feccom', 'numpartida'])
      break;
  }
}
function buscar_cuentas() {
  idbanco = document.getElementById('bancoid').value;
  $.ajax({
    url: "../src/cruds/crud_bancos.php",
    method: "POST",
    data: { 'condi': 'buscar_cuentas', 'id': idbanco },
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      const data2 = JSON.parse(data);
      $("#cuentaid").empty();
      $("#cuentaid").append("<option value='0' selected disabled>Seleccione una cuenta</option>");
      if (data2[1] == "1") {
        for (var i = 0; i < data2[2].length; i++) {
          $("#cuentaid").append("<option value='" + data2[2][i]["id"] + "'>" + data2[2][i]["numcuenta"] + "</option>");
        }
      } else {
        Swal.fire({ icon: "error", title: "¡ERROR!", text: data2[0] });
      }
    },
    complete: function () {
      loaderefect(0);
    }
  })
}
//FUNCION PARA TRAER EL NUMERO DE CHEQUE EN AUTOMATICO
function cheque_automatico(id_cuenta_banco, id_reg_cheque) {
  $.ajax({
    url: "../src/cruds/crud_bancos.php",
    method: "POST",
    data: { 'condi': 'cheque_automatico', 'id_cuenta_banco': id_cuenta_banco, 'id_reg_cheque': id_reg_cheque },
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      const data2 = JSON.parse(data);
      $("#numcheque").val(data2[2]);
    },
    complete: function () {
      loaderefect(0);
    }
  })
}
function saveahommov(flag, usu, ofi, tipope) {
  if (flag == "") {
    // obtiene(['ccodaho', 'dfecope', 'cnumdoc', 'monto', 'numpartida', 'feccom', 'nrochq', 'name'], ['salida', 'tipdoc', 'tipchq'], ['nada'], 'cdahommov', '0', [usu, ofi, tipope]);
    obtiene(['ccodaho', 'dfecope', 'cnumdoc', 'monto', ''], ['salida', 'tipdoc', 'tipchq'], ['nada'], 'cdahommov', '0', [usu, ofi, tipope]);
  }
  else {
    Swal.fire({
      icon: 'error',
      title: '¡ERROR!',
      text: 'Debe seleccionar una cuenta'
    })
  }
}
function comprobanteliquidcrt(datos, file = 7, bandera = 1) {
  consultar_reporte(file, bandera).then(function (action) {
    //PARTE ENCARGADA DE RETORNAR EL NOMBRE DE LA FUNCION A EJECUTAR
    if (bandera == 1) {
      file = action;
    } else {
      file = file;
    }
    //EJECUTA FUNCION BASADO EN LA VARIABLE FILE
    window[file](datos);
  }).catch(function (error) {
    Swal.fire("Uff", error, "error");
  });
}
function liquidcrt(datos, file = 6, bandera = 1) {
  consultar_reporte(file, bandera).then(function (action) {
    //PARTE ENCARGADA DE RETORNAR EL NOMBRE DE LA FUNCION A EJECUTAR
    if (bandera == 1) {
      file = action;
    } else {
      file = file;
    }
    //EJECUTA FUNCION BASADO EN LA VARIABLE FILE
    window[file](datos);
  }).catch(function (error) {
    Swal.fire("Uff", error, "error");
  });
}

function creaComprobante(datos, file = 4, bandera = 1) {
  consultar_reporte(file, bandera).then(function (action) {
    //PARTE ENCARGADA DE RETORNAR EL NOMBRE DE LA FUNCION A EJECUTAR
    if (bandera == 1) {
      file = action;
    } else {
      file = file;
    }
    //EJECUTA FUNCION BASADO EN LA VARIABLE FILE
    window[file](datos);
  }).catch(function (error) {
    Swal.fire("Uff", error, "error");
  });
}

function creaLib(cod, file = 2, bandera = 1) {
  //CONSULTA PARA TRAER QUE REPORTE SE QUIERE
  var data2;
  consultar_reporte(file, bandera).then(function (action) {
    //PARTE ENCARGADA DE GENERAR EL REPORTE
    if (bandera == 1) {
      file = action;
    } else {
      file = file;
    }
    return consultar_movimientos_libreta(file, cod);
  }).then(function (action2) {
    // DATOS DE MOVIMIENTOS DE AHORRO
    data2 = (action2);
    // IDENTIFICADOR DE ARCHIVO DE OPERACIONES DE LIBRETA
    file = 3;
    return consultar_reporte(file, bandera);
  }).then(function (action3) {
    // INTERCAMBIO DE VARIABLES
    if (bandera == 1) {
      file = action3;
    } else {
      file = file;
    }
    // console.log("creaLib")
    // console.log(data2)
    //SE PREPARAN LOS DATOSecho json_encode([[$numfront,$numdors,$inifront,$inidors,$saldo],$array,$confirma]);
    var inif = parseInt(data2[0][2]);
    var nfront = parseInt(data2[0][0]);
    var inid = parseInt(data2[0][3]);
    var ndors = parseInt(data2[0][1]);

    numi = parseInt(data2[1][0]['numlinea']);//ANTERIOR: parseInt(data2[1][1]['numlinea']);
    numf = parseInt(data2[1][data2[1].length - 1]['numlinea']);
    saldo = parseFloat(data2[0][4]);

    resta = 0;
    ini = 0;
    posfin = 0;
    if (numi <= nfront) {
      resta = 0;
      ini = inif;
      posfin = nfront;
    }
    if (numi > nfront) {
      resta = nfront;
      ini = inid;
      posfin = nfront + ndors;
    }
    //EJECUTA FUNCION BASADO EN LA VARIABLE FILE
    // window[file](data2, resta, ini, 1, posfin, saldo, numi, file);anterior
    window[file](data2, resta, ini, 0, posfin, saldo, numi, file);
    // libprint(data2, resta, ini, 1, posfin, saldo, numi);
  }).catch(function (error) {
    Swal.fire("Uff", error, "error");
  });
}

function consultar_movimientos_libreta(file, cod) {
  return new Promise(function (resolve, reject) {
    dire = "../views/aho/reportes/" + file + ".php";
    condi = "lib";
    $.ajax({
      url: dire,
      method: "POST",
      data: { condi, cod },
      beforeSend: function () {
        loaderefect(1);
      },
      success: function (data) {
        const data2 = JSON.parse(data);
        if (data2[2] == "1") {
          resolve(data2);
        } else {
          reject("NO HAY DATOS PARA IMPRIMIR")
        }
      },
      complete: function () {
        loaderefect(0);
      },
    });
  });
}

//FUNCION QUE ABRE MODAL PARA LA EDICION DE RECIBO
function modal_edit_recibo(id_recibo, numdoc_ant, ccodaport, codusu) {
  $('#edicion_recibo').modal('show');
  document.getElementById("id_recibo").value = id_recibo;
  document.getElementById("id_codusu").value = codusu;
  document.getElementById("numdoc_modal_recibo_ant").value = numdoc_ant;
  document.getElementById("ccodaho_recibo").value = ccodaport;
}

//FUNCION PARA CANCELAR LA EDICION
function cancelar_edit_recibo() {
  document.getElementById("id_recibo").value = "";
  document.getElementById("id_codusu").value = "";
  document.getElementById("numdoc_modal_recibo_ant").value = "";
  document.getElementById("ccodaho_recibo").value = "";
  document.getElementById("numdoc_modal_recibo").value = "";
  $('#edicion_recibo').modal('hide');
}

//formatear numeros en moneda
const currency = function (number) {
  return new Intl.NumberFormat('es-GT', { style: 'currency', currency: 'GTQ', minimumFractionDigits: 2 }).format(number);
};
//relleno
function pad(input) {
  var cadenaNumerica = '*************';
  var resultado = cadenaNumerica + input;
  return resultado = resultado.substring(resultado.length - cadenaNumerica.length);
}
function conviertefecha(fecharecibidatexto) {
  fec = fecharecibidatexto;

  anio = fec.substring(0, 4);
  mes = fec.substring(5, 7);
  dia = fec.substring(8, 10);

  ensamble = mes + "-" + dia + "-" + anio;
  fecha = new Date(ensamble).toLocaleDateString('es-GT');
  return fecha;
}

function statusaccount(id) {
  var win = window.open('aho/reportes/statusaccount.php?id=' + id, '_blank');
}
function editben(idahomben, bennom, bendpi, bendire, benparent, benfec, benporcent, bentel) {
  $('#databen').modal('show');
  document.getElementById("createben").style = "display:none;";
  document.getElementById("updateben").style = "display:yes;";
  document.getElementById("idben").value = idahomben;
  document.getElementById("benname").value = bennom;
  document.getElementById("bendpi").value = bendpi;
  document.getElementById("bendire").value = bendire;
  document.getElementById("bentel").value = bentel;
  document.getElementById("bennac").value = benfec;
  document.getElementById("benporcent").value = benporcent;
  document.getElementById("benporcentant").value = benporcent;
  document.getElementById("benparent").value = benparent;
}
//#region ahoprog
function ahoprogramado(tipo, ins, ofi) {
  correltipcuenta(tipo, ins, ofi)
  $('#aperahoprog').modal('show');
}
//-------codigo step by step
function btnclick(btn) {
  var $btn = $(btn),
    $step = $btn.parents('.modal-bodyy'),
    stepIndex = $step.index(),
    $pag = $('.modal-header span').eq(stepIndex);
  step1($step, $pag);

}
function step1($step, $pag) {
  // console.log('step1');
  // animate the step out
  $step.addClass('animate-out');

  // animate the step in
  setTimeout(function () {
    $step.removeClass('animate-out is-showing')
      .next().addClass('animate-in');
    $pag.removeClass('is-active')
      .next().addClass('is-active');
  }, 600);

  // after the animation, adjust the classes
  setTimeout(function () {
    $step.next().removeClass('animate-in')
      .addClass('is-showing');
  }, 1200);
}
function step3($step, $pag) {
  // animate the step out
  $step.parents('.modal-wrapp').addClass('animate-up');

  setTimeout(function () {
    $('.rerun-button').css('display', 'inline-block');
  }, 300);
}

//--- fin step by step
function calculoprog(btn) {
  var monto = document.getElementById("montoobj").value;
  var fini = document.getElementById("fini").value;
  var ffin = document.getElementById("ffin").value;
  var freq = document.querySelector('input[name="frec"]:checked').value;
  condi = "calculoprg";

  $.ajax({
    url: "../src/cruds/crud_ahorro.php",
    method: "POST",
    data: { condi, monto, fini, ffin, freq },
    success: function (data) {
      const data2 = JSON.parse(data);
      if (data2[4] == 1) {
        document.querySelector('#lblini').innerText = "Fecha de inicio: " + conviertefecha(data2[1])
        document.querySelector('#lblfin').innerText = "Fecha de finalizacion: " + conviertefecha(data2[2])
        document.querySelector('#lblmon').innerText = "Monto meta: " + currency(data2[3])
        $("#tbcuotas").html("");
        for (var i = 0; i < data2[0].length; i++) {
          var tr = `<tr>
          <td>`+ data2[0][i][0] + `</td>
          <td style="text-align: right;">`+ conviertefecha(data2[0][i][1]) + `</td>
          <td style="text-align: right;">`+ currency(data2[0][i][2]) + `</td>
          <td style="text-align: right;">`+ currency(data2[0][i][3]) + `</td>
        </tr>`;
          $("#tbcuotas").append(tr)
        }
        btnclick(btn);
      }
      else {
        Swal.fire({
          icon: 'error', title: 'Error!', text: data2[0]
        })
      }
    }
  })
}
//#endregion
function pagintere(pagintere) {
  switch (pagintere) {
    case '1':
      habdeshab([], ['bancom', 'cuentacor'])
      break;
    case '2':
      habdeshab([], ['bancom', 'cuentacor'])
      break;
    case '3':
      habdeshab(['bancom', 'cuentacor'], [])
      break;
  }
}
function pignora(pignora) {
  switch (pignora) {
    case 'S':
      habdeshab(['codpres'], [])
      break;
    case 'N':
      habdeshab([], ['codpres'])
      break;
  }
}

function calcfecven(cond) {
  var plazo = document.getElementById("plazo").value
  var fecven = document.getElementById("fecven").value
  var fecapr = document.getElementById("fecaper").value
  var mon = document.getElementById("monapr").value
  var int = document.getElementById("tasint").value
  var days = document.getElementById("dayscalc").value
  condi = "calfec";
  $.ajax({
    url: "../src/cruds/crud_ahorro.php",
    method: "POST",
    data: { condi, fecapr, plazo, mon, int, cond,fecven,days },
    success: function (data) {
      const data2 = JSON.parse(data);
      if (data2[0] == '1' && cond == 1) {
        document.getElementById("moncal").value = data2[1]
        document.getElementById("intcal").value = data2[2]
        document.getElementById("totcal").value = data2[3]
      }
      if (data2[0] == '1' && cond == 2) {
        document.getElementById("fecven").value = data2[1]
      }
      if (data2[0] == '1' && cond == 3) {
        document.getElementById("plazo").value = data2[1]
      }
      if (data2[0] == '0') {
        var toastLive = document.getElementById('toastalert')
        document.getElementById("body_text").innerHTML = data2[1]
        var toast = new bootstrap.Toast(toastLive)
        toast.show()
      }

    }
  })
}
function penalizacion(interescalc) {
  var mon = interescalc;
  var monapr = document.getElementById("monapr").value;
  var porcpena = document.getElementById("porc_pena").value;
  (porcpena == "") ? porcpena = 0 : porcpena = porcpena;
  var penaliza = mon * (porcpena / 100);
  var moncal = mon - penaliza;

  var ipf = moncal * 0.10;
  var total = moncal - ipf;
  var totaltodo = (parseFloat(monapr) + parseFloat(moncal)) - (parseFloat(ipf) + parseFloat(penaliza));

  document.getElementById("penaliza").value = parseFloat(penaliza.toFixed(2));
  document.getElementById("moncal").value = parseFloat(moncal.toFixed(2));
  document.getElementById("intcal").value = parseFloat(ipf.toFixed(2));
  document.getElementById("totcal").value = parseFloat(total.toFixed(2));
  document.getElementById("totaltodo").value = parseFloat(totaltodo.toFixed(2));
}

function printcrt(idcrt, crud, condi) {
  //CONSULTA DE LA FUNCION A EJECUTAR
  file = 5; bandera = 1;
  consultar_reporte(file, bandera).then(function (action) {
    //PARTE ENCARGADA DE GENERAR EL REPORTE
    if (bandera == 1) {
      file = action;
    } else {
      file = file;
    }
    //CONSULTAR DATOS A IMPRIMIR
    $.ajax({
      url: "../src/cruds/" + crud + ".php",
      method: "POST",
      data: { condi, idcrt },
      beforeSend: function () {
        loaderefect(1);
      },
      success: function (data) {
        const data2 = JSON.parse(data);
        //GENERAR LA IMPRESION DEL CERTIFICADO
        window[file](data2);
      },
      complete: function () {
        loaderefect(0);
      }
    })
  }).catch(function (error) {
    Swal.fire("Uff", error, "error");
  });
}

function consultar_reporte(file, bandera) {
  return new Promise(function (resolve, reject) {
    if (bandera == 0) {
      resolve('Aprobado');
    }
    $.ajax({
      url: "../src/cruds/crud_ahorro.php",
      method: "POST",
      data: { 'condi': 'consultar_reporte', 'id_descripcion': file },
      beforeSend: function () {
        loaderefect(1);
      },
      success: function (data) {
        const data2 = JSON.parse(data);
        if (data2[1] == "1") {
          resolve(data2[2]);
        } else {
          reject(data2[0]);
        }
      },
      complete: function () {
        loaderefect(0);
      },
    });
  });
}

function reportes(datos, tipo, file, download = 1, bandera = 0) {
  var datosval = [];
  datosval[0] = getinputsval(datos[0]);
  datosval[1] = getselectsval(datos[1]);
  datosval[2] = getradiosval(datos[2]);
  datosval[3] = datos[3];
  //CONSULTA PARA TRAER QUE REPORTE SE QUIERE
  consultar_reporte(file, bandera).then(function (action) {
    //PARTE ENCARGADA DE GENERAR EL REPORTE
    if (bandera == 1) {
      file = action;
    } else {
      file = file;
    }
    var url = "aho/reportes/" + file + ".php";
    $.ajax({
      url: url,
      async: true,
      type: "POST",
      dataType: "html",//html
      contentType: "application/x-www-form-urlencoded",
      data: { datosval, tipo },
      beforeSend: function () {
        loaderefect(1);
      },
      success: function (data) {
        console.log(data);
        var opResult = JSON.parse(data);
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
      },
      complete: function () {
        loaderefect(0);
      },
    });
    //-------------------------------------FIN SEGUNDA FUNCION
  }).catch(function (error) {
    Swal.fire("Uff", error, "error");
  });
}

//FUNCION GENERAL PARA LOS REPORTES
function reportes_ahorros(data1) {
  //  console.log(data1);
  //  return;
  loaderefect(1);
  $.ajax({
    url: 'aho/reportes/' + data1[1] + '.php',
    async: true,
    type: "POST",
    dataType: "html",
    contentType: "application/x-www-form-urlencoded",
    data: { data: data1 },
    success: function (data) {
      // console.log(data);
      loaderefect(0);
      var opResult = JSON.parse(data);
      var $a = $("<a>");
      $a.attr("href", opResult.data);
      $("body").append($a);
      $a.attr("download", data1[1] + "_" + data1[4] + "." + data1[3]);
      $a[0].click();
      $a.remove();
    }
  })
}

//FUNCION PARA ACTIVAR Y DESACTIVAR UN SELECT CUANDO SE PRESIONA UN RADIO BUTTON
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

//FUNCION PARA ACTIVAR Y DESACTIVAR INPUTS CUANDO SE PRESION UN RADIO BUTTON
function activar_input_dates(radio, estado, dateInicial, dateFinal) {
  if (radio.checked) {
    if (estado) {
      document.getElementById(dateInicial).disabled = estado;
      document.getElementById(dateFinal).disabled = estado;
      var date1 = document.getElementById(dateInicial);
      date1.value = formato_fecha();
      var date2 = document.getElementById(dateFinal);
      date2.value = formato_fecha();

    }
    else {
      //cuando se seleccionan una cuenta se habilita el select
      document.getElementById(dateInicial).disabled = estado;
      document.getElementById(dateFinal).disabled = estado;
    }
  }
}

function formato_fecha() {
  let yourDate = new Date()
  yourDate.toISOString().split('T')[0]
  const offset = yourDate.getTimezoneOffset()
  yourDate = new Date(yourDate.getTime() - (offset * 60 * 1000))
  return yourDate.toISOString().split('T')[0]
}

//funcion para agregar beneficiario desde la ventana principal
function crear_editar_beneficiario(ccodaho, nombre) {
  if (ccodaho === "0" || nombre === "") {
    Swal.fire({
      icon: 'error',
      title: '¡ERROR!',
      text: 'Debe seleccionar una cuenta de ahorro'
    });
    return;
  }
  cargar_datos_ben('lista_beneficiarios', ccodaho);
  $('#databen').modal('show');
  document.getElementById("ccodaho_modal").value = ccodaho;
  document.getElementById("name_modal").value = nombre;

}

function cargar_datos_ben(condi, id) {
  traer_porcentaje_ben(id);
  tabla = $('#tabla_ben').dataTable({
    "aProcessing": true, //activamos el procedimiento del datatable
    "aServerSide": true, //paginacion y filrado realizados por el server
    "searching": false,
    "paging": false,
    "ordering": false,
    "info": false,
    "ajax": {
      url: '../src/cruds/crud_ahorro.php',
      type: "POST",
      data: {
        'condi': condi, 'l_codaho': id
      },
      dataType: "json",
    },
    "bDestroy": true,
    "iDisplayLength": 10, //paginacion
    "order": [
      [0, "desc"]
    ] //ordenar (columna, orden)
  }).DataTable();
}

function traer_porcentaje_ben(id) {
  loaderefect(1);
  dire = "../src/cruds/crud_ahorro.php";
  $.ajax({
    url: dire,
    type: "POST",
    data: { 'condi': 'obtener_total_ben', 'l_codaho2': id },
    dataType: "JSON",
    success: function (data) {
      loaderefect(0);
      document.querySelector('#total').innerText = 'Total: ' + data + '%';
      limpiar_modal_ben();
    }
  });
}

function limpiar_modal_ben() {
  document.getElementById("createben").style = "display:yes;";
  document.getElementById("updateben").style = "display:none;";
  document.getElementById("idben").value = "";
  document.getElementById("benname").value = "";
  document.getElementById("bendpi").value = "";
  document.getElementById("bendire").value = "";
  document.getElementById("bentel").value = "";
  var date1 = document.getElementById('bennac');
  date1.value = formato_fecha();
  document.getElementById("benporcent").value = "";
  document.getElementById("benporcentant").value = "";
  document.getElementById("benparent").value = "";
}

function cancelar_crear_editar_beneficiario(condi, id) {
  loaderefect(1);
  dire = "../src/cruds/crud_ahorro.php";
  $.ajax({
    url: dire,
    type: "POST",
    data: { 'condi': 'obtener_total_ben', 'l_codaho2': id },
    dataType: "JSON",
    success: function (data) {
      loaderefect(0);
      if ((data == null || data == "")) {
        printdiv2('#cuadro', id)
        $('#databen').modal('hide');
      }
      //cuando el porcentaje es 100, despues de existir un registro
      else if (data == 100) {
        printdiv2('#cuadro', id)
        $('#databen').modal('hide');
        //cuando el porcentaje no es 100
      } else {
        Swal.fire({
          icon: 'error',
          title: '¡ERROR!',
          text: 'Tiene que ajustar a los beneficiarios para que en total sumen 100%, de lo contrario no puede salir de la ventana'
        });
        limpiar_modal_ben();
      }
    }
  });
}

//#region FUNCION PARA CARGAR UNA TABLA TIPO DataTable
function convertir_tabla_a_datatable(id_tabla) {
  $('#' + id_tabla).on('search.dt')
    .DataTable({
      "lengthMenu": [
        [5, 10, 15, -1],
        ['5 filas', '10 filas', '15 filas', 'Mostrar todos']
      ],
      "language": {
        "lengthMenu": "Mostrar _MENU_ registros",
        "zeroRecords": "No se encontraron registros",
        "info": " ",
        "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
        "infoFiltered": "(filtrado de un total de: _MAX_ registros)",
        "sSearch": "Buscar: ",
        "oPaginate": {
          "sFirst": "Primero",
          "sLast": "Ultimo",
          "sNext": "Siguiente",
          "sPrevious": "Anterior"
        },
        "sProcessing": "Procesando...",
      },
    });
}
//#endregion