//#region loader
function loaderefect(sh) {
    const LOADING = document.querySelector('.loader-container');
    switch (sh) {
        case 1:
            LOADING.classList.remove('loading--hide');
            LOADING.classList.add('loading--show');
            break;
        case 0:
            LOADING.classList.add('loading--hide');
            LOADING.classList.remove('loading--show');
            break;
    }
}
//#endregion
//#region printdivs
function printdiv(condi, idiv, dir, xtra) {
    loaderefect(1);
    dire = "conta/" + dir + ".php";
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
    dire = "conta/" + dir + ".php";
    $.ajax({
        url: dire, method: "POST", data: { condi, xtra },
        success: function (data) {
            loaderefect(0);
            $(idiv).html(data);
        }
    })
}
//
// function printdiv3(condi, idiv, xtra) {
//     loaderefect(1);
//     dir = filenow();
//     dire = "conta/" + dir + ".php";
//     $.ajax({
//         url: dire, method: "POST", data: { condi, xtra },
//         success: function (data) {
//             loaderefect(0);
//             $(idiv).html(data);
//         }
//     })
// }


function printdiv3(condi, idiv, xtra) {
    dir = $("#file").val();
    dire = "conta/" + dir + ".php";
    $.ajax({
        url: dire, method: "POST", data: { condi, xtra },
        beforeSend: function () {
            loaderefect(1);
        },
        success: function (data) {
            loaderefect(0);
            $(idiv).html(data);
            //Actualizar tabla
            if (condi == 'section_partidas_conta') {
                console.log('entro');
                var indice = table_partidas_aux.page.info().page;
                table_partidas_aux.ajax.reload(function () {
                    var total = table_partidas_aux.page.info().pages;
                    if (indice == total) {
                        indice--;
                    }
                    table_partidas_aux.page(indice).draw('page');
                });
            }
        },
        complete: function () {
            loaderefect(0);
        }
    })
}


//inprimir datos en inputs
function printdiv4(data, idinputs) {
    i = 0;
    while (i < data.length) {
        $("#" + idinputs[i]).val(data[i]);
        i++;
    }
}
//#endregion
//#region Modal Nomenclatura
function abrir_modal(id_modal, estado, id_hidden, dato) {
    $(id_modal).modal(estado);
    $(id_hidden).val(dato);
}

function seleccionar_cuenta_ctb(id_hidden, id) {
    var idsinp = $(id_hidden).val();
    let idinputs = idsinp.split(',');
    printdiv4(id, idinputs)
    cerrar_modal('#modal_nomenclatura', 'hide', '#id_modal_hidden')
}

function cerrar_modal(id_modal, estado, id_hidden) {
    $(id_modal).modal(estado);
    $(id_hidden).val("");
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

function verifi() {
    // Obtener valores del DOM
    var ccodcta = document.getElementById("ccodcta").value;
    var id = document.getElementById("id").value;


    // Realizar la verificación de existencia
    $.ajax({
        url: "../src/cruds/crud_ctb.php",
        method: "POST",
        data: {
            'condi': "add_clase",
            'ccodcta': ccodcta,
            'id': id 
        },
        success: function (data) {

            var resultado = JSON.parse(data);

            if (resultado.existe) {
                Swal.fire({
                    icon: "error",
                    showCloseButton: true,
                    title: "Ya se registró esta cuenta contable",
                    text: "Verifique los datos",
                });
            } else if (resultado.exito) {
                Swal.fire({
                    icon: "success",
                    showCloseButton: true,
                    title: "Cuenta contable registrada ",
                    text: "",
                });
            }
        },
        error: function (status, error) {
            console.error("Error en la solicitud AJAX:", error);
        },
    });
}




function salir() {
   
}



function x(button) {
    
    var ccodcta = $(button).data('ccodcta');
    var nciclo = $(button).data('nciclo');

    console.log(ccodcta, nciclo);

    Swal.fire({
      title: '¿Guardar nueva cuenta contable?',
      text: '',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: 'success',
      cancelButtonColor: '#FF3333',
      confirmButtonText: 'Sí'
      
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
            url: "../src/cruds/crud_ctb.php",
            method: "POST",
          data: { ccodcta: ccodcta, id: id },
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

//--
function generico(inputs, selects, radios, condi, id, archivo) {
    $.ajax({
        url: "../src/cruds/crud_ctb.php",
        method: "POST",
        data: { inputs, selects, radios, condi, id, archivo },
        beforeSend: function () {
            loaderefect(1);
        },
        success: function (data) {
            // console.log(data);
            const data2 = JSON.parse(data);
            // console.log(data2);
            if (data2[1] == "1") {
                Swal.fire({ icon: 'success', title: 'Muy Bien!', text: data2[0] })
                if (condi == 'cpoliza' || condi == 'upoliza') {
                    printdiv3('section_partidas_conta', '#contenedor_section', '0');
                } else {
                    printdiv2("#cuadro", id);
                }
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
//#region funciones reutilzables
//desactiva o activa elementos: padre:mandar id, name o lo que sea pa identificar    status:1 activar, 0: desactivar
function changedisabled(padre, status) {
    if (status == 0) $(padre).attr('disabled', 'disabled');
    else $(padre).removeAttr('disabled');
}
//funcion para eliminar cualquier registro
function eliminar(ideliminar, dir, xtra, condi) {
    dire = "../src/cruds/" + dir + ".php";
    Swal.fire({
        title: '¿ESTA SEGURO DE ELIMINAR?', showDenyButton: true, confirmButtonText: 'Eliminar', denyButtonText: `Cancelar`,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: dire, method: "POST", data: { condi, ideliminar },
                beforeSend: function () {
                    loaderefect(1);
                },
                success: function (data) {
                    const data2 = JSON.parse(data);
                    if (data2[1] == "1") {
                        Swal.fire('Correcto', data2[0], 'success');
                        if (condi == 'dpoliza') {
                            printdiv3('section_partidas_conta', '#contenedor_section', '0');
                        } else {
                            printdiv2("#cuadro", xtra);
                        }
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
//FUNCION GENERAL PARA LOS REPORTES download: 1 si, 0 no(lo muestra en una nueva ventana)
function reportes(datos, tipo, file, download) {
    loaderefect(1);
    var datosval = [];
    datosval[0] = getinputsval(datos[0]); datosval[1] = getselectsval(datos[1]); datosval[2] = getradiosval(datos[2]); datosval[3] = datos[3];
    var url = "conta/reportes/" + file + ".php";
    $.ajax({
        url: url, async: true, type: "POST", dataType: "html", data: { datosval, tipo },
        success: function (data) {
            // console.log(data)
            loaderefect(0);
            var opResult = JSON.parse(data);
            console.log(opResult);
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
                Swal.fire({ icon: 'error', html: opResult.mensaje, title: '¡ERROR!' })
            }
        }
    })
}
//#endregion
//#region partidas Diario
var countrow = 0;
var countid = 0;
var datoseliminados = [];
function newrow() {
    if (validanewrow() == 1) {
        countrow++;
        countid++;
        var t = $('#Cuentas').DataTable();
        t.row.add([countrow, countid, genbtn('cuenta' + countid), input('debe' + countid, 'number', 'yes'), input('habe' + countid, 'number', 'yes')]).draw(false);
        var column = t.column(1);
        column.visible(false);
    }
    else {
        Swal.fire({ icon: 'error', title: '¡ERROR!', text: 'Hay registros sin completarse, verique que se hayan ingresado montos y se hayan seleccionado cuentas.' })
    }
}

//NEWROW PARA DEPOSITO A BANCOS
function newrow2(datas) {
    if (validanewrow() == 1) {
        countrow++;
        countid++;
        var t = $('#Cuentas').DataTable();
        t.row.add([countrow, countid, genbtn('cuenta' + countid), input('debe' + countid, 'number', 'yes'), input('habe' + countid, 'number', 'yes'), genfondos(datas, countid)]).draw(false);
        var column = t.column(1);
        column.visible(false);
    }
    else {
        Swal.fire({ icon: 'error', title: '¡ERROR!', text: 'Hay registros sin completarse, verique que se hayan ingresado montos y se hayan seleccionado cuentas.' })
    }
}
function genfondos(datafondos, count) {
    i = 0;
    fondoselect = '<select class="form-select" id="fondoid' + count + '">';
    while (i < datafondos.length) {
        fondoselect += '<option value="' + datafondos[i]['id'] + '">' + datafondos[i]['descripcion'] + '</option>';
        i++;
    }
    fondoselect += '</select>';
    return fondoselect;
}

//verifica que se hayan ingresado datos en cada fila
function validanewrow() {
    var rows = 1;
    var aux1 = 0;
    while (rows <= countid) {
        var mm = datoseliminados.includes(rows);
        if (mm == false) {
            aux1 = getinputsval(['debe' + (rows), 'habe' + (rows), 'cuenta' + (rows)]);
            if ((aux1[2] == "") || (aux1[0] == "" && aux1[1] == "")) {
                return 0;
            }
        }
        rows++;
    }
    return 1;
}
function genbtn(id) {
    return '<div class="input-group"><input style="display:none;" type="text" class="form-control" id="id' + id + '"><input type="text" readonly class="form-control" id="' + id + '"><button class="btn btn-outline-success" type="button" onclick="abrir_modal(`#modal_nomenclatura`, `show`, `#id_modal_hidden`, `id' + id + ',' + id + '`)" title="Buscar Cuenta contable"><i class="fa fa-magnifying-glass"></i></button></div>';
}
function input(id, type) {
    return '<div class="input-group"><span class="input-group-text">Q</span><input style="text-align: right;" type="' + type + '" step="0.01" class="form-control" id="' + id + '" onblur="validadh(this.id,this.value)"></div>';
}
function validadh(id, value) {
    var lado = id.substr(0, 4);
    var numero = id.substr(4, 3);
    var contra = (lado == 'debe') ? getinputsval(['habe' + numero]) : getinputsval(['debe' + numero]);
    var advertencia = "";
    if (value != "" && value != 0 && contra[0] != "" && contra[0] != 0) {
        advertencia = 'No se puede agregar una cantidad en este lado de la cuenta, verifique';
    }
    // if (value < 0) {
    //     advertencia = 'No se admiten negativos, verifique';
    // }
    if (advertencia != "") {
        Swal.fire({ icon: 'error', title: '¡ERROR!', text: advertencia })
        $("#" + id).val("");
        return;
    }
    if (value != "") {
        var valor = parseFloat(value)
        $("#" + id).val(valor.toFixed(2));
    }
    totaldh();
}
function totaldh() {
    var rows = 1;
    var totdebe = 0;
    var tothaber = 0;
    var pibo = 0;
    while (rows <= countid) {
        var mm = datoseliminados.includes(rows);
        if (mm == false) {
            pibo = getinputsval(['debe' + (rows), 'habe' + (rows)]);
            // console.log("pibo " + pibo)
            totdebe += parseFloat((pibo[0] == "") ? 0 : pibo[0]);
            tothaber += parseFloat((pibo[1] == "") ? 0 : pibo[1]);
        }
        rows++;
    }
    $("#totdebe").val(totdebe.toFixed(2));
    $("#tothaber").val(tothaber.toFixed(2));
}
function deletefila() {
    var t = $('#Cuentas').DataTable();
    var data = t.row('.selected').data();
    if (data != undefined) {
        t.row('.selected').remove().draw(false);
        datoseliminados.push(parseInt(data[1]))
        var num = 0;
        $('#Cuentas tbody tr').each(function () {
            $(this).find('td').eq(0).text(num + 1);
            num++;
        });
        countrow = num;
        counter = num;
        totaldh();
    }
}
function reinicio(num) {
    countrow = num;
    countid = num;
    datoseliminados = [];
}
function savecom(usuario, condio, idr) {
    loaderefect(1)
    if (validanewrow() == 0) {
        Swal.fire({ icon: 'error', title: '¡ERROR!', text: 'Hay registros sin completarse, verifique que se hayan ingresado montos y se hayan seleccionado cuentas.' })
        loaderefect(0);
        return;

    }
    var datainputsd = [''];
    var datainputsh = [''];
    var datacuentas = [''];
    var datafondos = [''];
    var datainputs = [];
    var dataselects = [];
    var rows = 1;
    var fila = 0;
    var pibo = 0;
    while (rows <= countid) {
        var mm = datoseliminados.includes(rows);
        if (mm == false) {
            pibo = getinputsval(['debe' + (rows), 'habe' + (rows), 'idcuenta' + (rows), 'fondoid' + (rows)]);
            datainputsd[fila] = (pibo[0] == "") ? 0 : pibo[0];
            datainputsh[fila] = (pibo[1] == "") ? 0 : pibo[1];
            datacuentas[fila] = pibo[2];
            datafondos[fila] = pibo[3];
            fila++;
        }
        rows++;
    }
    datainputs = getinputsval(['datedoc', 'datecont', 'glosa', 'totdebe', 'tothaber', 'numdoc'])
    // dataselects = getinputsval(['codofi', 'fondoid', 'idtipo_poliza'])
    dataselects = getinputsval(['codofi', 'idtipo_poliza'])
    generico([datainputs, datainputsd, datainputsh, datacuentas, datafondos], dataselects, 1, condio, '0', [usuario, idr]);
}
//#endregion

//#region FUNCION PARA CARGAR UNA TABLA TIPO DataTable
function convertir_tabla_a_datatable(id_tabla) {
    $('#' + id_tabla).on('search.dt')
        .DataTable({
            "lengthMenu": [
                [3, 5, 10, 15, -1],
                ['3 filas', '5 filas', '10 filas', '15 filas', 'Mostrar todos']
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


function prueba() {
    $("#tipo").val("R");
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
            $("#" + nomInputs[index]).val(concatenacion);
        } else {
            $("#" + nomInputs[index]).val(valores[contador]);
            contador++;
        }
    }
}

//FUNCION PARA CARGAR EL DATATABLE DE DATOS DE CUENTAS CONTABLES
var tabla;
function cargar_datos_cuenta(codusu) {
    tabla = $('#tb_nomenclatura').on('search.dt').DataTable({
        "aProcessing": true,
        "aServerSide": true,
        "ordering": false,
        "lengthMenu": [
            [3, 5, 10, 15, -1],
            ['3 filas', '5 filas', '10 filas', '15 filas', 'Mostrar todos']
        ],
        "ajax": {
            url: '../src/cruds/crud_ctb.php',
            type: "POST",
            data: {
                'condi': "lista_cuentas_contables", 'codusu': codusu
            },
            dataType: "json",
        },
        "bDestroy": true,
        "iDisplayLength": 3,
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

//#region FUNCION QUE EVALUA CUANDO CARGA TODOS LOS RECURSOS Y DESAPARECE EL LOADER
$(document).ready(function () {
    loaderefect(0);
});
//#endregion

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

function HabDes_boton(valor) {
    if (valor == 1) {
        $('#btGuardar').hide();
        $('#btEditar').show();
    }
    if (valor == 0) {
        $('#btGuardar').show();
        $('#btEditar').hide();
    }
}

//FUNCION PARA BUSCAR Clase de Cuentas
$(document).ready(function() {
    $('#clase_add').select2({
        placeholder: 'Selecciona una cuenta',
        allowClear: true
    });
});