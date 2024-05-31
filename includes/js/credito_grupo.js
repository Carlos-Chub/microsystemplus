function KillRowAux() {
    con = 0;
    while (codCu[con] != null) {
        //console.log('Dato encontrado '+codCu[con])
        killFila(codCu[con]);
        con++;
    }
}

//Funcion para eliminar una fila de plan de pago
function killFila(nametb) {
    var tabla = document.getElementById(nametb);
    var filas = tabla.getElementsByTagName('tr');
    var noFila = filas.length - 1;

    if (noFila != 1) {
        tabla.deleteRow(noFila);
        calPlanDePago(nametb);
    }

}
function salir() {
    $(location).attr('href', 'index.php');
  }

function actMasiva(vecGeneral, condi, extra) {
    $.ajax({
        url: "../../src/cruds/crud_credito_indi.php",
        type: "POST",
        data: { vecGeneral, condi, extra },
        beforeSend: function () {
            loaderefect(1);
        },
        success: function (data) {
            const data2 = JSON.parse(data);
            if (data2[1] == 1) {
                Swal.fire({ icon: 'success', title: 'Muy Bien!', text: data2[0] })
            }
            else {
                Swal.fire({ icon: 'error', title: '¡ERROR!', text: data2[1] })
            }
        },
        complete: function () {
            loaderefect(0);
        }
    });
}

//Funcion para eliminar uan fila y los datos en la base datos
function eliminarFila(ideliminar, condi, archivo = 0) {
    dire = "../../src/cruds/crud_credito_indi.php";
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
                        KillRowAux();
                        Swal.fire('Correcto', 'Eliminado', 'success');
                        var res = result.isConfirmed;
                        return res;
                    }
                    else Swal.fire('X(', data2[0], 'error')
                },
                complete: function () {
                    loaderefect(0);
                }
            })
        } else {
            var res = result.isConfirmed;
            return res;
        }
    })
}

function inyecCod(condi, extra = "0", url = "../../views/Creditos/cre_grupo/inyecCod/inyecCod.php") {
    return new Promise(function (resolve, reject) {
        $.ajax({
            url: url,
            type: "POST",
            data: { condi, extra },
            beforeSend: function () {
                loaderefect(1);
            }
        })
            .done(function (data) {
                resolve(data); // Resuelve la promesa con los datos recibidos
            })
            .fail(function (error) {
                reject(error); // Rechaza la promesa en caso de error
            })
            .always(function () {
            });
    });
}

$(document).ready(function () {
    loaderefect(0);
});

//#region printdivs
function printdiv(condi, idiv, dir, xtra) {
    loaderefect(1);
    dire = "./cre_grupo/" + dir + ".php";
    $.ajax({
        url: dire,
        method: "POST",
        data: { condi, xtra },
        success: function (data) {
            $(idiv).html(data);
            loaderefect(0);
        }
    })
}



//funcion para actualizar el estado de cuenta de  Analisis a Solicitud
function grup_cambiar_solicitud(button) {
    var idgrup = $(button).data('ccodcta').split(' ')[0];
    var NCiclo = $(button).data('ccodcta').split(' ')[1]; 
    Swal.fire({
        title: '¿Estás seguro de Regresar este Crédito Grupal a Solictud?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, regresar'
    }).then((result) => {
        if (result.isConfirmed) {
            console.log(idgrup,  NCiclo); 
            $.ajax({
                url: "../../src/cruds/update_estado_cred_grup.php",
                method: "POST",
                data: { idgrup: idgrup, NCiclo: NCiclo },
                success: function(response) {
                    Swal.fire({
                        icon: "success",
                        title: "Crédito cambiado a SOLICITUD ",
                        text: ""
                    }).then(() => {
                        location.reload();
                    });
                    console.log(response);
                },
                error: function(xhr, status, error) {
                    console.error(error); 
                }
            });
        }
    });
}


function regre_cred_grup(button) {
    var idgrup = $(button).data('ccodcta').split(' ')[0];
    var NCiclo = $(button).data('ccodcta').split(' ')[1]; 
    Swal.fire({
        title: '¿Estás seguro de Regresar este Credito Grupal a Aprobacion?',
        text: 'Esta acción no se puede deshacer. Y se eliminaran datos Importantes',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, regresar'
    }).then((result) => {
        if (result.isConfirmed) {
            console.log(idgrup, NCiclo); 
            $.ajax({
                url: "../../src/cruds/update_estado_desem_grup.php",
                method: "POST",
                data: { idgrup: idgrup, NCiclo: NCiclo },
                success: function(response) {
                    Swal.fire({
                        icon: "success",
                        title: "El grupo se encuentra en Aprobacion",
                        text: "",
                    });
                    location.reload();
                    console.log(response);
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.error(error); 
                }
            });
        }
    });
}


function delete_cred_grup(button) {
    var idgrup = $(button).data('ccodcta').split(' ')[0];
    var NCiclo = $(button).data('ccodcta').split(' ')[1]; 
    Swal.fire({
        title: '¿Estás seguro de Eliminar el Crédito?',
        text: 'Esta acción no se puede deshacer. Y se eliminaran datos Importantes',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar'
    }).then((result) => {
        if (result.isConfirmed) {
            console.log(idgrup, NCiclo); 
            $.ajax({
                url: "../../src/cruds/update_estado_cred_grup.php",
                method: "POST",
                data: { idgrup: idgrup, NCiclo: NCiclo },
                success: function(response) {
                    Swal.fire({
                        icon: "success",
                        title: "Crédito ELIMINADO",
                        text: "",
                    });
                    location.reload();
                    console.log(response);
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.error(error); 
                    // Manejar errores de la solicitud AJAX si es necesario
                }
            });
        }
    });
}





function printdiv2(idiv, xtra) {
    loaderefect(1);
    condi = $("#condi").val();
    dir = $("#file").val();
    dire = "cre_grupo/" + dir + ".php";
    $.ajax({
        url: dire, method: "POST", data: { condi, xtra },
        success: function (data) {
            loaderefect(0);
            $(idiv).html(data);
        }
    })
}
function printdiv5(id_hidden, valores) {
    //ver si sacar el dato de un idhidden o directamente un toString
    var cadena = id_hidden.substr(0, 1);
    if (cadena == "#") {
        //todo el input
        var todo = ($(id_hidden).val()).split("/");
    }
    else {
        //todo la cadena
        var todo = id_hidden.split("/");
    }

    //se extraen los nombres de los inputs
    var nomInputs = (todo[0].toString()).split(",");
    //se extraen los rangos
    var rangos = (todo[1].toString()).split(",");
    //se extrae el separador
    var separador = todo[2].toString();

    //todo lo relacionado a la habilitacion o deshabilitacion
    var habilitar = [];
    var deshabilitar = [];
    if (todo[3].toString() != "#") {
        habilitar = (todo[3].toString()).split(",")
    }
    if (todo[4].toString() != "#") {
        deshabilitar = (todo[4].toString()).split(",")
    }
    habilitar_deshabilitar(habilitar, deshabilitar);
    //----fin de la habilitacion y deshabilitacion

    //todo lo relacionado con show y hide de elementos
    var mostrar = [];
    var ocultar = [];
    if (todo[5].toString() != "#") {
        mostrar = (todo[5].toString()).split(",")
    }
    if (todo[6].toString() != "#") {
        ocultar = (todo[6].toString()).split(",")
    }
    mostrar_nomostrar(mostrar, ocultar);
    //fin de los elementos hidden o visible

    // tratar de validar o unir campos para mandarlos a un solo input
    var contador = 0;
    for (var index = 0; index < nomInputs.length; index++) {
        if (rangos[index] !== 'A') {
            var aux = rangos[index].toString();
            var arrayaux = aux.split("-");
            var concatenacion = "";
            for (var index2 = arrayaux[0]; index2 <= arrayaux[1]; index2++) {
                if (index2 === arrayaux[0]) {
                    concatenacion = concatenacion + valores[index2 - 1];
                } else {
                    concatenacion = concatenacion + " " + separador + " ";
                    concatenacion = concatenacion + valores[index2 - 1];
                }
                contador++;
            }
            if (nomInputs[index] != "") {
                $("#" + nomInputs[index]).val(concatenacion);
            }
        } else {
            if (nomInputs[index] != "") {
                $("#" + nomInputs[index]).val(valores[contador]);
            }
            contador++;
        }
    }
}
function abrir_modal(id_modal, id_hidden, dato) {
    $(id_modal).modal('show');
    $(id_hidden).val(dato);
}

function abrir_modal_for_delete(id_modal, id_hidden, dato) {

    $(id_modal).modal("show");
    $(id_hidden).val(dato);
    console.log(id_modal, id_hidden, dato);
    return;
  }

//funcion para eliminar Creditos ya desembolsados
function enviarDelete(button) {
    var ccodcta = $(button).data('ccodcta');
  
    Swal.fire({
      title: '¿Estás seguro de Eliminar el Credito?',
      text: 'Esta acción no se puede deshacer. y se eliminaran datos Importantes',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar'
    }).then((result) => {
      if (result.isConfirmed) {
        //  procede con la eliminación.
        $.ajax({
          url: "../../src/cruds/update_estado_cuenta.php",
          method: "POST",
          data: { ccodcta: ccodcta },
          success: function (data) {
            Swal.fire({
              icon: "success",
              title: "Crédito cambiado a Solicitud",
              text: "",
            }).then(() => {
              location.reload();
            });
          },
          error: function (status, error) {
            console.error("Error en la solicitud AJAX:", error);
          },
        });
      }
    });
  }
  //funcion para eliminar Creditos ya desembolsados
function enviarAprobacion(button) {
    var ccodcta = $(button).data('ccodcta');
  
    Swal.fire({
      title: '¿Estás seguro de Regresar este credito a Aprobacion?',
      text: 'Esta acción no se puede deshacer. y se eliminaran datos Importantes',
      icon: 'warning',
      iconColor: '#C70039',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#FF3333',
      confirmButtonText: 'Sí, regresar'
      
    }).then((result) => {
      if (result.isConfirmed) {
        //  procede con la eliminación.
        $.ajax({
          url: "../../src/cruds/update_estado_aprobacion.php",
          method: "POST",
          data: { ccodcta: ccodcta },
          success: function (data) {
            
            Swal.fire({
              icon: "success",
              title: "Crédito cambiado a APROBACIÓN ",
              text: "",
            }).then(() => {
              location.reload();
            });
          },
          error: function (status, error) {
            console.error("Error en la solicitud AJAX:", error);
          },
        });
      }
    });
  }
  

function select_item(id_hidden, valores) {
    printdiv5(id_hidden, valores);
}

function cerrar_modal(id_modal, estado, id_hidden) {
    $(id_modal).modal(estado);
    $(id_hidden).val("");
}
//#endregion
//#region beneq 
function opencollapse(i) {
    event.stopPropagation();
    if (i >= 0) {
        if ($('#collaps' + i).hasClass('collapse')) {
            $('.accordion-collapse').addClass('collapse');
            $('#collaps' + i).removeClass("collapse");
        } else {
            $('.accordion-collapse').addClass('collapse');
        }
    }
    if (i.toString().substring(0, 1) == "s") {
        if ($('#' + i).is(':checked')) {
            changedisabled('#bt' + i.substring(1, 2) + ' .form-control', 1);
        } else {
            changedisabled('#bt' + i.substring(1, 2) + ' .form-control', 0);
        }
    }
}
function changedisabled(padre, status) {
    if (status == 0) $(padre).attr('disabled', 'disabled');
    else $(padre).removeAttr('disabled');
}
function mostrar_nomostrar(mostrar, ocultar) {
    var i = 0;
    while (i < mostrar.length) {
        document.getElementById(mostrar[i]).style.display = "block";
        i++;
    }
    var i = 0;
    while (i < ocultar.length) {
        document.getElementById(ocultar[i]).style.display = "none";
        i++;
    }
}
function habilitar_deshabilitar(hab, des) {
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
function generico(inputs, selects, radios, condi, id, archivo, filecrud) {
    $.ajax({
        url: "../../src/cruds/" + filecrud + ".php",
        method: "POST",
        data: { inputs, selects, radios, condi, id, archivo },
        beforeSend: function () {
            loaderefect(1);
        },
        success: function (data) {
            // console.log(data);
            const data2 = JSON.parse(data);
            // console.log(data2)
            if (data2[1] == "1") {
                Swal.fire({ icon: 'success', title: 'Muy Bien!', text: data2[0] })
                printdiv2('#cuadro', id);
                if (condi == 'desemgrupal') printdiv('comprobantechq', '#cuadro', 'grup001', [archivo[0], archivo[5], data2[2], data2[3]]);
                if (condi == 'analgrupal') reportes([[], [], [], [id[0], id[1]]], `pdf`, `ficha_analisis`, 0);
                if (condi == 'aprobgrupal') reportes([[], [], [], [archivo[0], archivo[1]]], `pdf`, `ficha_aprobacion`, 0);
                if (condi == 'soligrupal') reportes([[], [], [], [archivo[1], inputs[1][0]]], `pdf`, `ficha_solicitud`, 0);
            }
            else { Swal.fire({ icon: 'error', title: '¡ERROR!', text: data2[0] }) }
        },
        complete: function () {
            loaderefect(0);
        }
    })
}

function consultar_reporte(file, bandera) {
    return new Promise(function (resolve, reject) {
        if (bandera == 0) {
            resolve('Aprobado');
        }
        $.ajax({
            url: "../../src/cruds/crud_credito.php",
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

function reportes(datos, tipo, file, download, bandera = 0) {
    var datosval = [];
    datosval[0] = getinputsval(datos[0]); datosval[1] = getselectsval(datos[1]); datosval[2] = getradiosval(datos[2]); datosval[3] = datos[3];
    //CONSULTA PARA TRAER QUE REPORTE SE QUIERE
    fileaux=file;
    consultar_reporte(file, bandera).then(function (action) {
        //PARTE ENCARGADA DE GENERAR EL REPORTE
        if (bandera == 1) {
            file = action;
        } else {
            file = file;
        }
        //INICIO DE REPORTE
        if (fileaux==18 || fileaux==19 || fileaux==20) {
            var url = "cre_indi/reportes/" + file + ".php";
        }else if (fileaux==13) {
            var url = "../bancos/reportes/" + file + ".php";
        } else{
            var url = "cre_grupo/reportes/" + file + ".php";
        }
        $.ajax({
            url: url, async: true, type: "POST", dataType: "html", data: { datosval, tipo },
            beforeSend: function () {
                loaderefect(1);
            },
            success: function (data) {
                var opResult = JSON.parse(data);
                //console.log(opResult)
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
            }
        })
        //FIN DE REPORTE
    }).catch(function (error) {
        Swal.fire("Uff", error, "error");
    });
}

function savesol(cant, user, idgrup, idofi) {
    var datainputs = [];
    var rows = 0;
    var filas = [];
    while (rows <= cant) {
        filas = getinputsval(['ccodcli' + (rows), 'monsol' + (rows), 'descre' + (rows), 'sectorecono' + (rows), 'actecono' + (rows)]);
        datainputs[rows] = filas;
        rows++;
    }
    datadetal = getinputsval(['nciclo', 'codanal']);
    generico([datainputs, datadetal], 0, 0, 'soligrupal', [0], [user, idgrup, idofi], 'crud_credito');
}
function saveanal(cant, ciclo, idgrup, idofi) {
    var datainputs = [];
    var rows = 0;
    var filas = [];
    while (rows <= cant) {
        filas = getinputsval(['ccodcta' + (rows), 'monapr' + (rows)]);
        datainputs[rows] = filas;
        rows++;
    }
    datadetal = getinputsval(['idprod', 'maxprod', 'tipcre', 'peri', 'fecinit', 'nrocuo', 'fecdes', 'dictmn', 'tasaprod']);
    generico([datainputs, datadetal], 0, 0, 'analgrupal', [idgrup, ciclo], [idofi], 'crud_credito');
}
function saveapro(cant, idgrupo, nciclo) {
    var datainputs = [];
    var rows = 0;
    var filas = [];
    while (rows <= cant) {
        filas = getinputsval(['ccodcta' + (rows)]);
        datainputs[rows] = filas;
        rows++;
    }
    generico(datainputs, 0, 0, 'aprobgrupal', [0, 0], [idgrupo, nciclo], 'crud_credito');
}
function savedesem(cant, idgrup, ciclo, fecdes, idusu, oficina, datos) {
    var datainputs = [];
    var rows = 0;
    var filas = [];
    var filgas = [];
    while (rows <= cant) {
        filgas = [];
        filas = getinputsval(['ccodcta' + (rows), 'glosa' + (rows), 'numcheque' + (rows), 'monapr' + (rows), 'mondesc' + (rows)]);
        //GASTOS
        nocuenta = filas[0].substring(8, 16);
        k = 0;
        while (k != (-1)) {
            if (!!document.getElementById('idg_' + (k) + '_' + (nocuenta)));
            else break;
            filgas[k] = getinputsval(['idg_' + (k) + '_' + (nocuenta), 'mon_' + (k) + '_' + (nocuenta), 'con_' + (k) + '_' + (nocuenta)]);
            filgas[k][1] = numOr0(filgas[k][1]);
            k++;
        }
        filas[5] = filgas;
        datainputs[rows] = filas;
        //FIN GASTOS
        rows++;
    }
    datadetal = getinputsval(['tipo_desembolso', 'bancoid', 'cuentaid']);
    // console.log(datainputs);
    // console.log(datadetal);
    generico([datainputs, datadetal], 0, 0, 'desemgrupal', 0, [idgrup, ciclo, fecdes, idusu, oficina, datos], 'crud_credito');
}
let numOr0 = n => isNaN(parseFloat(n)) ? 0 : parseFloat(n);
function summon(id) {
    let rows = id.substring(7, 9)
    let filas = getinputsval(['capital' + (rows), 'interes' + (rows), 'monmora' + (rows), 'ahorrop' + (rows), 'otrospg' + (rows)]);
    $('#totalpg' + rows).val(filas.reduce((a, b) => numOr0(a) + numOr0(b)));
    var i = 0; let filtot = [];
    while (i != (-1)) {
        filtot[i] = getinputsval(['totalpg' + i]);
        i = (!!document.getElementById('totalpg' + (i + 1))) ? i + 1 : (-1);
    }
    $('#totalgen').val(filtot.reduce((a, b) => numOr0(a) + numOr0(b)));
}
function summongas(nocuenta, id) {
    var i = 0; let filtot = [];
    while (i != (-1)) {
        filtot[i] = getinputsval(['mon_' + (i) + '_' + (nocuenta)]);
        i = (!!document.getElementById('mon_' + (i + 1) + '_' + (nocuenta))) ? i + 1 : (-1);
    }
    let gastos = filtot.reduce((a, b) => numOr0(a) + numOr0(b));
    let capital = $('#monapr' + id).val();
    $('#mondesc' + id).val(gastos);
    $('#monentrega' + id).val(capital - gastos);
}
//#endregion
function creperi(condi, idiv, dir, xtra) {
    dire = "../Creditos/cre_indi/" + dir + ".php";
    $.ajax({
        url: dire,
        method: "POST",
        data: { condi, xtra },
        success: function (data) {
            $(idiv).html(data);
        }
    })
    printdiv('prdscre', '#peri', '../cre_indi/' + dir, xtra);
}
function showhide(seleccion) {
    var data = ['none', 'block'];
    document.getElementById('region_cheque').style.display = data[seleccion - 1];
    const nodeList = document.getElementsByClassName("classchq");
    for (let i = 0; i < nodeList.length; i++) {
        nodeList[i].style.display = data[seleccion - 1];
    }
}
function buscar_cuentas() {
    idbanco = document.getElementById('bancoid').value;
    //consultar a la base de datos
    $.ajax({
        url: "../../src/cruds/crud_credito_indi.php",
        method: "POST",
        data: { 'condi': 'buscar_cuentas', 'id': idbanco },
        beforeSend: function () {
            loaderefect(1);
        },
        success: function (data) {
            const data2 = JSON.parse(data);
            if (data2[1] == "1") {
                $("#cuentaid").empty();
                for (var i = 0; i < data2[2].length; i++) {
                    $("#cuentaid").append("<option value='" + data2[2][i]['id'] + "'>" + data2[2][i]['numcuenta'] + "</option>");
                }
            }
            else {
                $("#cuentaid").empty();
                $("#cuentaid").append("<option value='0'>Seleccione una cuenta</option>");
                Swal.fire({ icon: 'error', title: '¡ERROR!', text: data2[0] })
            }
        },
        complete: function () {
            loaderefect(0);
        }
    })
}
//MOSTRAR GASTOS EN LA TABLA
function mostrar_tabla_gastos(codcredito, id) {
    $('#tabla_gastos_desembolso' + id).on('search.dt').DataTable({
        "searching": false,
        "paging": false,
        "aProcessing": true,
        "aServerSide": true,
        "ordering": false,
        "lengthMenu": [
            [10, 20, 30, -1],
            ['5 filas', '10 filas', '15 filas', 'Mostrar todos']
        ],
        "ajax": {
            url: '../../src/cruds/crud_credito_indi.php',
            type: "POST",
            beforeSend: function () {
                loaderefect(1);
            },
            data: {
                'condi': "lista_gastos", 'id': codcredito, 'filcuenta': id
            },
            dataType: "json",
            complete: function (data) {
                // console.log(data);
                loaderefect(0);
            }
        },
        "bDestroy": true,
        "iDisplayLength": 10,
        "order": [
            [1, "desc"]
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
            "sProcessing": "Procesando..."
        }
    });

}
//CONSULTAR GASTOS ADMINISTRATIVOS
function consultar_gastos_monto(codcredito, id) {
    //consultar a la base de datos
    $.ajax({
        url: "../../src/cruds/crud_credito_indi.php",
        method: "POST",
        data: { 'condi': 'gastos_desembolsos', 'id': codcredito },
        beforeSend: function () {
            loaderefect(1);
        },
        success: function (data) {
            const data2 = JSON.parse(data);
            //console.log(data2);
            if (data2[1] == "1") {
                //imprimir en los inputs
                $("#monapr" + id).val(data2[2]);
                $("#mondesc" + id).val(data2[3]);
                $("#monentrega" + id).val(data2[4]);
                //cantidad_a_letras(data2[4]);
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


// para OBTENER LOS VALORES de ACTI/SECTOR ECONO
function SctrEcono(id, dtass, agregado) {
    var condi = "SctrEcono";
    //  alert (dtass);
    $.ajax({
        url: "../../src/general.php",
        method: "POST",
        data: { dtass, condi },
        beforeSend: function () {
            loaderefect(1);
        },
        success: function (data) {
            $(id).html(data);
            $(agregado).val("");
        },
        complete: function () {
            loaderefect(0);
        }
    })
}