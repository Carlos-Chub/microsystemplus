//Funcion para eliminar una fila de plan de pago
function killFila() {
  var tabla = document.getElementById("tbPlanPagos");
  var filas = tabla.getElementsByTagName("tr");
  var noFila = filas.length - 1;

  fila = parseInt($("#" + noFila + "idCon").text());
  filaData = parseInt($("#" + noFila + "idData").text());

  if (noFila > 0) {
    tabla.deleteRow(noFila);
    calPlanDePago();
  }
}

function eliminarFila(ideliminar, condi, archivo = 0) {
  //alert('eliminando fila')
  dire = "../../src/cruds/crud_credito_indi.php";
  //alert(ideliminar + ' ' + condi + ' ' + archivo);
  //dire = "../../src/cruds/crud_admincre.php";
  Swal.fire({
    title: "¿ESTA SEGURO DE ELIMINAR?",
    showDenyButton: true,
    confirmButtonText: "Eliminar",
    denyButtonText: `Cancelar`,
  }).then((result) => {
    //console.log(result.isConfirmed)
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
            killFila();
            Swal.fire("Correcto", "Eliminado", "success");
            var res = result.isConfirmed;
            return res;
          } else Swal.fire("X(", data2[0], "error");
        },
        complete: function () {
          loaderefect(0);
        },
      });
    } else {
      var res = result.isConfirmed;
      return res;
    }
  });
}

//Funcion para recoger los datos de la tabla
function capDataTb(nameEle, tipo) {
  var elementos = document.querySelectorAll(
    "" + tipo + '[name="' + nameEle + '[]"]'
  );
  var valores = [];
  elementos.forEach(function (elemento) {
    if (tipo === "input") valores.push(elemento.value);
    if (tipo === "td") valores.push(elemento.textContent);
  });
  return valores;
}

function actMasiva(matriz, condi, extra) {
  //console.log(matriz);
  var matrizJSON = JSON.stringify(matriz);
  // console.log(matrizJSON);
  $.ajax({
    url: "../../src/cruds/crud_credito_indi.php",
    type: "POST",
    data: { matriz: matrizJSON, condi, extra },
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      const data2 = JSON.parse(data);
      // console.log(data);
      if (data2[1] == 1) {
        Swal.fire({ icon: "success", title: "Muy Bien!", text: data2[0] });
      } else {
        Swal.fire({ icon: "error", title: "¡ERROR!", text: data[0] });
      }
    },
    complete: function () {
      loaderefect(0);
    },
  });
}

function inyecCod111(
  idElem,
  condi,
  extra = "0",
  url = "../../src/cruds/crud_credito_indi.php"
) {
  $.ajax({
    url: url,
    type: "POST",
    data: { condi, extra },
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      $(idElem).html(data);
    },
    complete: function () {
      loaderefect(0);
    },
  });
}

function inyecCod(
  idElem,
  condi,
  extra = "0",
  url = "../../src/cruds/crud_credito_indi.php"
) {
  $.ajax({
    url: url,
    type: "POST",
    data: { condi, extra },
    beforeSend: function () {
      loaderefect(1);
    },
  })
    .done(function (data) {
      // console.log(data);
      // return;

      if (condi === "PlanPagos") {
        $(idElem).html(data);
      }
      if (condi === "modal_aho_plz") {
        $(idElem).html(data);
      }
      if (condi === "cu_aho" || condi === "cu_apr") {
        $(idElem).html(data);
      }
      if (condi === "consulta_cre") {
        $(idElem).html(data);
      }
      if (condi === "cre_productos") {
        $(idElem).html(data);
      }
    })
    .always(function () {
      loaderefect(0);
    });
}

function opInyec(op) {
  switch (op) {
    case 0:
      inyecCod(
        "#consulta_cre",
        "consulta_cre",
        (extra = "0"),
        (url = "../../views/Creditos/cre_indi/inyecCod/inyecCod.php")
      );
      break;
    case 1:
      inyecCod(
        "#consulta_cre_producto",
        "cre_productos",
        (extra = "0"),
        (url = "../../views/Creditos/cre_indi/inyecCod/inyecCod.php")
      );
      break;
  }
}

function convertir_tabla_a_datatable(id_tabla) {
  $("#" + id_tabla)
    .on("search.dt")
    .DataTable({
      lengthMenu: [
        [5, 10, 15, -1],
        ["5 filas", "10 filas", "15 filas", "Mostrar todos"],
      ],
      language: {
        lengthMenu: "Mostrar _MENU_ registros",
        zeroRecords: "No se encontraron registros",
        info: " ",
        infoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
        infoFiltered: "(filtrado de un total de: _MAX_ registros)",
        sSearch: "Buscar: ",
        oPaginate: {
          sFirst: "Primero",
          sLast: "Ultimo",
          sNext: "Siguiente",
          sPrevious: "Anterior",
        },
        sProcessing: "Procesando...",
      },
    });
}

function capData(dataPhp, dataJava) {
  console.log("Data de php--> " + dataPhp);
  console.log("Data de js--> " + dataJava);

  console.log("Inicio aqui");
  let data = dataPhp.split("||");
  for (let i = 0; i < dataJava.length; i++) {
    console.log("Dato a insertar: " + dataJava[i]);
    if ($(dataJava[i]).is("input")) {
      $(dataJava[i]).val(data[i]);
    }
    if ($(dataJava[i]).is("label")) {
      $(dataJava[i]).text(data[i]);
    }
    if ($(dataJava[i]).is("textarea")) {
      $(dataJava[i]).val(data[i]);
    }
  }
}

//Funcion para capturar datos
function capDataEsp(dataPhp, dataJava = 0, pos = []) {
  let data = dataPhp.split("||");

  console.log("Data de PHP--> " + data);
  console.log("Data de JS--> " + dataJava);

  if (pos.length == 0) dataPos = dataJava.length;
  else dataPos = pos.length;

  for (let i = 0; i < dataPos; i++) {
    if ($(dataJava[i]).is("input")) {
      $(dataJava[i]).val(data[pos[i]]);
    }
    if ($(dataJava[i]).is("label")) {
      $(dataJava[i]).text(data[pos[i]]);
    }
    if ($(dataJava[i]).is("textarea")) {
      $(dataJava[i]).val(data[pos[i]]);
    }
  }
}

//Mostrar la tabla de garantia.
function tbGarantias(idCliente) {
  $("#tbGarantias")
    .on("search.dt")
    .DataTable({
      aProcessing: true,
      aServerSide: true,
      ordering: false,
      lengthMenu: [
        [5, 10, 15, -1],
        ["5 filas", "10 filas", "15 filas", "Mostrar todos"],
      ],
      ajax: {
        url: "../../src/cruds/crud_credito_indi.php",
        type: "POST",
        beforeSend: function () {
          loaderefect(1);
        },
        data: {
          condi: "tablaGarantias",
          id: idCliente,
        },
        dataType: "json",
        complete: function (data) {
          loaderefect(0);
        },
      },
      bDestroy: true,
      iDisplayLength: 10,
      order: [[1, "desc"]],
      language: {
        lengthMenu: "Mostrar _MENU_ registros",
        zeroRecords: "No se encontraron registros",
        info: " ",
        infoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
        infoFiltered: "(filtrado de un total de: _MAX_ registros)",
        sSearch: "Buscar: ",
        oPaginate: {
          sFirst: "Primero",
          sLast: "Ultimo",
          sNext: "Siguiente",
          sPrevious: "Anterior",
        },
        sProcessing: "Procesando...",
      },
    });
}

//Funciona para controlar el tipo de garatia cuando es PersonaL
function opr_tipoGarantia() {
  var tipoGarantia = document.getElementById("selecTipoGa").value;
  switch (tipoGarantia) {
    case "1":
      $("#colDireccion").show();
      $("#bus_aho_plz").hide();
      $("#busFiador").show();
      $("#selecTipoDoc").val(1);
      $("#selecTipoDoc option:not(:selected)").attr("disabled", true);
      $("#idDescip").text(
        "El código del fiador será ingresado automáticamente cuando lo seleccione"
      );
      $("#descrip").attr("readonly", true);
      $("#descrip").val("");
      $("#conteInt").hide();
      $("#valorComer").val(0);
      $("#montoAvaluo").val(0);
      $("#monntoGra").val(0);
      break;

    case "3":
      $("#colDireccion").show();
      $("#conteInt").show();
      $("#busFiador").hide();
      $("#selecTipoDoc option:not(:selected)").attr("disabled", false);
      $("#selecTipoDoc").val(0);
      $("#selecTipoDoc option[value='8']").prop("disabled", false);
      $("#selecTipoDoc option[value='1']").prop("disabled", true);
      $("#idDescip").text("Descripción de la garantia");
      $("#descrip").attr("readonly", false);
      break;

    default:
      $("#colDireccion").show();
      $("#colComer").show();
      $("#colAvaluo").show();
      $("#conteInt").show();
      $("#busFiador").hide();
      $("#bus_aho_plz").hide();
      $("#selecTipoDoc option:not(:selected)").attr("disabled", false);
      $("#selecTipoDoc").val(0);
      $("#selecTipoDoc option[value='1']").prop("disabled", true);
      $("#selecTipoDoc option[value='8']").prop("disabled", true);
      $("#idDescip").text("Descripción de la garantia");
      $("#descrip").attr("readonly", false);
      break;
  }
}

function opr_tipoDoc() {
  var tipoDoc = document.getElementById("selecTipoDoc").value;
  switch (tipoDoc) {
    case "8":
      $("#colComer").hide();
      $("#colAvaluo").hide();
      $("#colDireccion").hide();
      $("#monntoGra").prop("disabled", true);
      $("#bus_aho_plz").show();
      $("#idDescip").text("Codígo de cuenta de ahorro de plazo fijo");
      $("#descrip").attr("readonly", true);
      var data = $("#codCliente").val();
      inyecCod(
        "#modal_aho_plz",
        "modal_aho_plz",
        data,
        "cre_indi/inyecCod/inyecCod.php"
      );
      break;
    default:
      $("#bus_aho_plz").hide();
      break;
  }
}

//funcion para eliminar Creditos ya desembolsados
function enviarAprobacion(button) {
  var ccodcta = $(button).data("ccodcta");

  Swal.fire({
    title: "¿Estás seguro de Regresar este credito a Aprobacion?",
    text: "Esta acción no se puede deshacer. y se eliminaran datos Importantes",
    icon: "warning",
    iconColor: "#C70039",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#FF3333",
    confirmButtonText: "Sí, regresar",
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

//funcion para eliminar Creditos ya desembolsados
function enviarDelete(button) {
  var ccodcta = $(button).data("ccodcta");

  Swal.fire({
    title: "¿Estás seguro de Eliminar el Credito?",
    text: "Esta acción no se puede deshacer. y se eliminaran datos Importantes",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sí, eliminar",
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

//funcion para actualizar el estado de cuenta de  Analisis a Solicitud
function enviarPOST(button) {
  var ccodcta = $(button).data("ccodcta");
  Swal.fire({
    title: "¿Estás seguro de Regresar este Crédito a Solicitud?",
    text: "Esta acción no se puede deshacer.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sí, regresar",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "../../src/cruds/update_estado_cuenta.php",
        method: "POST",
        data: { ccodcta: ccodcta },
        success: function (data) {
          // console.log(data);
          $(ccodcta).html(data);
          Swal.fire({
            icon: "success",
            title: "Crédito cambiado a Solicitud",
            text: "",
          }).then(() => {
            location.reload();
          });
        },
        error: function (xhr, status, error) {
          console.error("Error en la solicitud AJAX:", error);
        },
      });
    }
  });
}

//cambiar estado de Aprobacion
function enviarAprob(button) {
  var ccodcta = $(button).data("ccodcta");
  Swal.fire({
    title: "¿Estás seguro de Regresar este Crédito a Solicitud?",
    text: "Esta acción no se puede deshacer.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sí, regresar",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "../../src/cruds/update_estado_cuenta.php",
        method: "POST",
        data: { ccodcta: ccodcta },
        success: function (data) {
          // console.log(data);
          // return;
          $(ccodcta).html(data);
          Swal.fire({
            icon: "success",
            title: "Crédito cambiado a Solicitud ",
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

//cambiar estado de Desembolso
function enviarDesem(button) {
  var ccodcta = $(button).data("ccodcta");
  Swal.fire({
    title: "¿Estás seguro de Regresar este Crédito a Solicitud?",
    text: "Esta acción no se puede deshacer.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sí, regresar",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "../../src/cruds/update_estado_cuenta.php",
        method: "POST",
        data: { ccodcta: ccodcta },
        success: function (data) {
          // console.log(data);
          $(ccodcta).html(data);
          Swal.fire({
            icon: "success",
            title: "Crédito cambiado a Solicitud",
            text: "",
          }).then(() => {
            location.reload();
          });
        },
        error: function (xhr, status, error) {
          console.error("Error en la solicitud AJAX:", error);
        },
      });
    }
  });
}

//Editar Garantia
function editargarantia(datos) {
  dato = datos.split("||");
  printdiv2("#cuadro", dato[0], 1);
}

//Capturar cliente
function cerrarModal(modalCloss) {
  $(modalCloss).modal("hide"); // CERRAR MODAL
}

//Capturar fiador
function capfiador(datos) {
  dato = datos.split("||");
  $("#fiador1").val(dato[2]);
  $("#descrip").val(dato[1]);
  $("#modalFiador").modal("hide"); // CERRAR MODAL
}

/* Para selecionar una municio en especifico */
function escogerMuni(idmuni, iddepa, idM) {
  //alert(iddepa);
  aux = 0;
  var condi = "escogerMuni";
  $.ajax({
    url: "../../src/general.php",
    method: "POST",
    data: { iddepa: iddepa, condi: condi, idM: idM },
    success: function (data) {
      $(idmuni).html(data);
    },
  });
}

/* PARA LOS SELECT QUE TIENEN, QUE BUSCAR A LOS DEPARTAMENTOS */
function municipio(idmuni, iddepa) {
  //alert(iddepa);
  aux = 0;
  var condi = "departa";
  $.ajax({
    url: "../../src/general.php",
    method: "POST",
    data: { iddepa: iddepa, condi: condi },
    success: function (data) {
      $(idmuni).html(data);
    },
  });
}

function focus(data) {
  $(data).focus();
}

//Para cargar los archivos en el fronend
function readImage(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function (e) {
      $("#vistaPrevia").attr("src", e.target.result); // Renderizamos la imagen
    };
    reader.readAsDataURL(input.files[0]);
  }
}

//Para guardar la imagene y su ruta en la db
function saveimage() {
  var ccodcli = document.getElementById("idGarantia").value; //Se obtienen el codigo de la grantia
  if (ccodcli != "") {
    var fileImage = $("#foto").val();
    if (fileImage != "") {
      var form_data = new FormData();
      var condi = "ingresoimg";
      form_data.append("ccodcli", ccodcli);
      form_data.append("condi", condi);
      form_data.append("fileImage", document.getElementById("foto").files[0]);

      let aja = new XMLHttpRequest();
      let progresbar = document.getElementById("barprogress");
      progresbar.style = "display: yes";
      let progresbardiv = document.getElementById("progressdiv");
      let progresbarcancel = document.getElementById("cancelprogress");

      aja.upload.addEventListener("progress", function (e) {
        let porcentaje = (e.loaded / e.total) * 100;
        progresbar.style = "Width:" + Math.round(porcentaje) + "%";
        if (porcentaje >= 100) {
        }
      });

      aja.onreadystatechange = function () {
        if (aja.readyState == 4 && aja.status == 200) {
          var data = $.parseJSON(aja.responseText);
          var uploadResult = data[1];

          if (uploadResult == "1") {
            progresbardiv.style = "display: yes";
            progresbar.style = "display: none";
            progresbarcancel.style = "display: none";
            Swal.fire({
              icon: "success",
              title: "Muy Bien!",
              text: data[0],
            });
          }

          if (uploadResult == "0") {
            progresbardiv.style = "display: none";
            progresbarcancel.style = "display: yes";
            Swal.fire({
              icon: "warning",
              title: "Error!",
              text: data[0],
            });
          }
        }
      };
      aja.open("POST", "../../src/cruds/crud_credito_indi.php");
      aja.send(form_data);
    } else {
      Swal.fire({
        icon: "warning",
        title: "Error!",
        text: "No se ingreso archivo de imagen",
      });
    }
  } else {
    Swal.fire({
      icon: "warning",
      title: "Error!",
      text: "No se selecciono un cliente",
    });
  }
}

//Scrip para eliminar
//funcion para eliminar una garantia
function eliminar(ideliminar) {
  var codCli = $("#codCliente").val();
  condi = "eliminaGarantia";
  archivo = $("#idUser").val();
  dire = "../../src/cruds/crud_credito_indi.php";

  Swal.fire({
    title: "¿ESTA SEGURO DE ELIMINAR?",
    showDenyButton: true,
    confirmButtonText: "Eliminar",
    denyButtonText: `Cancelar`,
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: dire,
        method: "POST",
        data: { condi, ideliminar, archivo },
        beforeSend: function () {
          loaderefect(1);
        },
        success: function (data) {
          const data2 = JSON.parse(data);

          if (data2[1] == "1") {
            Swal.fire("Correcto", "Eliminado", "success");
            tbGarantias(codCli);
          } else Swal.fire("X(", data2[0], "error");
        },
        complete: function () {
          loaderefect(0);
        },
      });
    }
  });
}

//#region LOADER
//FUNCION PARA EL EFECTO DEL LOADER
function loaderefect(sh) {
  const LOADING = document.querySelector(".loader-container");
  switch (sh) {
    case 1:
      LOADING.classList.remove("loading--hide");
      LOADING.classList.add("loading--show");
      break;
    case 0:
      LOADING.classList.add("loading--hide");
      LOADING.classList.remove("loading--show");
      break;
  }
}
//#endregion
//MODULO DE CREDITOS PARA EL INGRESO DE DATOS
function printdiv2(idiv, xtra, op = 0) {
  condi = $("#condi").val();
  dir = $("#file").val();
  dire = "./cre_indi/" + dir + ".php";
  option = op;
  $.ajax({
    url: dire,
    method: "POST",
    data: { condi, xtra, option },
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      $(idiv).html(data);
    },
    complete: function () {
      loaderefect(0);
    },
  });
}

//Inicia la funcion de data table
//data table
function inicializarDataTable(idTabla) {
  $("#" + idTabla).DataTable({
    order: [
      [0, "desc"],
      [1, "desc"],
    ],
    lengthMenu: [
      [5, 10, 15, -1],
      ["5 filas", "10 filas", "15 filas", "Mostrar todos"],
    ],
    language: {
      lengthMenu: "Mostrar _MENU_ registros",
      zeroRecords: "No se encontraron registros",
      info: " ",
      infoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
      infoFiltered: "(filtrado de un total de: _MAX_ registros)",
      sSearch: "Buscar: ",
      oPaginate: {
        sFirst: "Primero",
        sLast: "Ultimo",
        sNext: "Siguiente",
        sPrevious: "Anterior",
      },
      sProcessing: "Procesando...",
    },
  });
}

//FUNCION PRINTDIV UTILIZADA PARA MOSTRAR LOS DATOS FALTANTES
function printdiv(condi, idiv, dir, xtra) {
  dire = "./cre_indi/" + dir + ".php";
  $.ajax({
    url: dire,
    method: "POST",
    data: { condi, xtra },
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      $(idiv).html(data);
    },
    complete: function () {
      loaderefect(0);
    },
  });
}
// REVISAR ESTA FUNCION
function creperi(condi, idiv, dir, xtra, callback) {
  printdiv("prdscre", "#peri", dir, xtra);
  dire = "./cre_indi/" + dir + ".php";
  $.ajax({
    url: dire,
    method: "POST",
    data: { condi, xtra },
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      $(idiv).html(data);
      if (typeof callback === "function") {
        callback();
      }
    },
  });
}

//FUNCIONES PARA CREACION DE GARANTIAS DESDE CREDITOS
function abrir_modal_cualquiera(identificador) {
  console.log("Inicio");
  $(identificador).modal("show");
}

function cerrar_modal_cualquiera(identificador) {
  $(identificador).modal("hide");
}

function abrir_modal_garantias(identificador, valor) {
  abrir_modal_cualquiera(identificador);
}
//FUNCION PARA ABRIR MODALES EN DONDE SE LES PASE UN PARAMETRO A UN INPUT TEXT
function abrir_modal_cualquiera_con_valor(
  identificador,
  id_hidden,
  valores,
  campos
) {
  $(identificador).modal("show");
  $(id_hidden).val(valores);
  var datos = obtener_valores_modal_hidden(id_hidden);
  for (let index = 0; index < datos.length; index++) {
    $(campos[index]).val(datos[index]);
  }
}

function cerrar_modal_cualquiera_con_valor(identificador, id_hidden, campos) {
  $(identificador).modal("hide");
  $(id_hidden).val("");
  for (let index = 0; index < campos.length; index++) {
    $(campos[index]).val("");
  }
}

function obtener_valores_modal_hidden(id_hidden) {
  var todo = $(id_hidden).val().split(",");
  return todo;
}

//FUNCION PARA GRABAR O CANCELAR LA CANCELACION DE UN CREDITO
function grabar_cancelar_credito(id_hidden, xtra) {
  var ideliminar = obtener_valores_modal_hidden(id_hidden);
  var rechazo = $("#rechazoid").val();
  ideliminar.push(rechazo);
  // console.log(ideliminar);
  eliminar_mejorado(
    ideliminar,
    "0",
    "rechazar_individual",
    "¿Esta de seguro de cancelar el crédito?"
  );
}

function consultar_reporte(file, bandera) {
  return new Promise(function (resolve, reject) {
    if (bandera == 0) {
      resolve("Aprobado");
    }
    $.ajax({
      url: "../../src/cruds/crud_credito_indi.php",
      method: "POST",
      data: { condi: "consultar_reporte", id_descripcion: file },
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

//FUNCION PARA LOS REPORTES EN CREDITOS INDIVIDUALES
function reportes(datos, tipo, file, download, bandera = 0) {
  var datosval = [];
  datosval[0] = getinputsval(datos[0]);
  datosval[1] = getselectsval(datos[1]);
  datosval[2] = getradiosval(datos[2]);
  datosval[3] = datos[3];
  //console.log(datosval);
  //CONSULTA PARA TRAER QUE REPORTE SE QUIERE
  fileaux = file;
  consultar_reporte(file, bandera)
    .then(function (action) {
      //PARTE ENCARGADA DE GENERAR EL REPORTE
      if (bandera == 1) {
        file = action;
      } else {
        file = file;
      }
      var url = "cre_indi/reportes/" + file + ".php";
      $.ajax({
        url: url,
        async: true,
        type: "POST",
        contentType: "application/x-www-form-urlencoded; charset=UTF-8",
        dataType: "html",
        data: { datosval, tipo },
        beforeSend: function () {
          loaderefect(1);
        },
        success: function (data) {
          var opResult = JSON.parse(data);
          if (opResult.status == 1) {
            switch (download) {
              case 0:
                const ventana = window.open();
                ventana.document.write(
                  "<object data='" +
                  opResult.data +
                  "' type='application/" +
                  opResult.tipo +
                  "' width='100%' height='100%'></object>"
                );
                break;
              case 1:
                var $a = $(
                  "<a href='" +
                  opResult.data +
                  "' download='" +
                  opResult.namefile +
                  "." +
                  tipo +
                  "'>"
                );
                $("body").append($a);
                $a[0].click();
                $a.remove();
                break;
            }
            if (fileaux != 18 && fileaux != 19 && fileaux != 20) {
              Swal.fire({
                icon: "success",
                title: "Muy Bien!",
                text: opResult.mensaje,
              });
            }
          } else {
            Swal.fire({
              icon: "error",
              title: "¡ERROR!",
              text: opResult.mensaje,
            });
          }
        },
        complete: function () {
          loaderefect(0);
        },
      });
      //-------------------------------------FIN SEGUNDA FUNCION
    })
    .catch(function (error) {
      Swal.fire("Uff", error, "error");
    });
}

//RELACION CON EL DESEMBOLSO DE CRÉDITOS INDIVIDUALES PARA ELIMINAR
function abrir_modal_for_delete(id_modal, id_hidden, dato) {
  $(id_modal).modal("show");
  $(id_hidden).val(dato);
  console.log(id_modal, id_hidden, dato);
  return;
}

//RELACION CON EL DESEMBOLSO DE CRÉDITOS INDIVIDUALES
function abrir_modal(id_modal, id_hidden, dato) {
  $(id_modal).modal("show");
  $(id_hidden).val(dato);
}

//seleccionar cuenta mejorado
function seleccionar_cuenta_ctb2(id_hidden, valores) {
  printdiv5(id_hidden, valores);
}
function seleccionar_credito_a_desembolsar(id_hidden, valores) {
  printdiv5(id_hidden, valores);
}

function cerrar_modal(id_modal, estado, id_hidden) {
  $(id_modal).modal(estado);
  $(id_hidden).val("");
}

function printdiv5(id_hidden, valores) {
  //ver si sacar el dato de un idhidden o directamente un toString
  var cadena = id_hidden.substr(0, 1);
  if (cadena == "#") {
    //todo el input
    var todo = $(id_hidden).val().split("/");
  } else {
    //todo la cadena
    var todo = id_hidden.split("/");
  }

  //se extraen los nombres de los inputs
  var nomInputs = todo[0].toString().split(",");
  //se extraen los rangos
  var rangos = todo[1].toString().split(",");
  //se extrae el separador
  var separador = todo[2].toString();

  //todo lo relacionado a la habilitacion o deshabilitacion
  var habilitar = [];
  var deshabilitar = [];
  if (todo[3].toString() != "#") {
    habilitar = todo[3].toString().split(",");
  }
  if (todo[4].toString() != "#") {
    deshabilitar = todo[4].toString().split(",");
  }
  habilitar_deshabilitar(habilitar, deshabilitar);
  //----fin de la habilitacion y deshabilitacion

  //todo lo relacionado con show y hide de elementos
  var mostrar = [];
  var ocultar = [];
  if (todo[5].toString() != "#") {
    mostrar = todo[5].toString().split(",");
  }
  if (todo[6].toString() != "#") {
    ocultar = todo[6].toString().split(",");
  }
  mostrar_nomostrar(mostrar, ocultar);
  //fin de los elementos hidden o visible

  // tratar de validar o unir campos para mandarlos a un solo input
  var contador = 0;
  for (var index = 0; index < nomInputs.length; index++) {
    if (rangos[index] !== "A") {
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

function mostrar_nomostrar(mostrar, ocultar) {
  var i = 0;
  while (i < mostrar.length) {
    document.getElementById(mostrar[i]).style.display = "block";
    i++;
  }
  var i = 0;
  while (i < ocultar.length) {
    // p.removeAttribute("hidden");
    document.getElementById(ocultar[i]).removeAttribute("hidden");
    document.getElementById(ocultar[i]).style.display = "none";
    i++;
  }
}

//CONSULTAR GASTOS ADMINISTRATIVOS
function consultar_gastos_monto(codcredito) {
  //consultar a la base de datos
  $.ajax({
    url: "../../src/cruds/crud_credito_indi.php",
    method: "POST",
    data: { condi: "gastos_desembolsos", id: codcredito },
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      const data2 = JSON.parse(data);
      // console.log(data2);
      if (data2[1] == "1") {
        //imprimir en los inputs
        $("#ccapital").val(data2[2]);
        $("#gastos").val(data2[3]);
        $("#desembolsar").val(data2[4]);
        $("#cantidad").val(data2[4]);
        $("#paguese").val(data2[5]);
        cantidad_a_letras(data2[4]);
      } else {
        Swal.fire({ icon: "error", title: "¡ERROR!", text: data2[0] });
      }
    },
    complete: function () {
      loaderefect(0);
    },
  });
}

//MOSTRAR GASTOS EN LA TABLA
function mostrar_tabla_gastos(codcredito) {
  $("#tabla_gastos_desembolso")
    .on("search.dt")
    .DataTable({
      aProcessing: true,
      aServerSide: true,
      ordering: false,
      lengthMenu: [
        [5, 10, 15, -1],
        ["5 filas", "10 filas", "15 filas", "Mostrar todos"],
      ],
      ajax: {
        url: "../../src/cruds/crud_credito_indi.php",
        type: "POST",
        beforeSend: function () {
          loaderefect(1);
        },
        data: {
          condi: "lista_gastos",
          id: codcredito,
          filcuenta: 0,
        },
        dataType: "json",
        complete: function () {
          loaderefect(0);
        },
      },
      bDestroy: true,
      iDisplayLength: 10,
      order: [[1, "desc"]],
      language: {
        lengthMenu: "Mostrar _MENU_ registros",
        zeroRecords: "No se encontraron registros",
        info: " ",
        infoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
        infoFiltered: "(filtrado de un total de: _MAX_ registros)",
        sSearch: "Buscar: ",
        oPaginate: {
          sFirst: "Primero",
          sLast: "Ultimo",
          sNext: "Siguiente",
          sPrevious: "Anterior",
        },
        sProcessing: "Procesando...",
      },
    });
}

function buscar_cuentas() {
  idbanco = document.getElementById("bancoid").value;
  //consultar a la base de datos
  $.ajax({
    url: "../../src/cruds/crud_credito_indi.php",
    method: "POST",
    data: { condi: "buscar_cuentas", id: idbanco },
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      const data2 = JSON.parse(data);
      // console.log(data2);
      if (data2[1] == "1") {
        $("#cuentaid").empty();
        for (var i = 0; i < data2[2].length; i++) {
          $("#cuentaid").append(
            "<option value='" +
            data2[2][i]["id"] +
            "'>" +
            data2[2][i]["numcuenta"] +
            "</option>"
          );
        }
      } else {
        $("#cuentaid").empty();
        $("#cuentaid").append("<option value=''></option>");
        Swal.fire({ icon: "error", title: "¡ERROR!", text: data2[0] });
      }
    },
    complete: function () {
      loaderefect(0);
    },
  });
}

function buscar_actividadeconomica(idsector) {
  //consultar a la base de datos
  $.ajax({
    url: "../../src/cruds/crud_credito_indi.php",
    method: "POST",
    data: { condi: "buscar_actividadeconomica", id: idsector },
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      const data2 = JSON.parse(data);
      // console.log(data2);
      if (data2[1] == "1") {
        $("#actividadeconomica").empty();
        for (var i = 0; i < data2[2].length; i++) {
          $("#actividadeconomica").append(
            "<option value='" +
            data2[2][i]["id"] +
            "'>" +
            data2[2][i]["descripcion"] +
            "</option>"
          );
        }
      } else {
        $("#actividadeconomica").empty();
        $("#actividadeconomica").append(
          "<option value='0' selected>Seleccione una actividad económica</option>"
        );
      }
    },
    complete: function () {
      loaderefect(0);
    },
  });
}

function cantidad_a_letras(monto) {
  // monto = document.getElementById('cantidad').value;
  //redondear a dos decimales
  montoredondeado = parseFloat(monto).toFixed(2);
  //separar decimales
  var numero_formateado = montoredondeado.split(".");
  texto = numeroALetras(Number(numero_formateado[0]), {
    plural: " ",
    singular: " ",
  });
  $("#numletras").val(texto + numero_formateado[1] + "/100");
}

//FUNCION PARA MOSTRAR Y OCULTAR DIV
function ocultar_div_desembolso(seleccion) {
  if (seleccion == "1") {
    document.getElementById("region_cheque").style.display = "none";
    document.getElementById("region_transferencia").style.display = "none";
  }
  if (seleccion == "2") {
    document.getElementById("region_cheque").style.display = "block";
    document.getElementById("region_transferencia").style.display = "none";
    //buscar_cuentas();
  }

  if (seleccion == "3") {
    document.getElementById("region_transferencia").style.display = "block";
    document.getElementById("region_cheque").style.display = "none";
    buscar_cuentas_ahorro_cli();
  }
}

//#endregion
//#region ajax generico
function obtiene(inputs, selects, radios, condi, id, archivo) {
  loaderefect(1);
  var inputs2 = [];
  var selects2 = [];
  var radios2 = [];
  inputs2 = getinputsval(inputs);
  selects2 = getselectsval(selects);
  radios2 = getradiosval(radios);
  // console.log("Datos procesados y enviados");
  generico(inputs2, selects2, radios2, condi, id, archivo);
}
//--
function generico(inputs, selects, radios, condi, id, archivo) {
  // console.log("Inputs " + inputs + " selects " + selects + " radios " + radios); return;
  $.ajax({
    url: "../../src/cruds/crud_credito_indi.php",
    method: "POST",
    data: { inputs, selects, radios, condi, id, archivo },
    beforeSend: function () {
      loaderefect(1);
    },

    success: function (data) {
      const data2 = JSON.parse(data);
      if (data2[1] == "1") {
        Swal.fire({ icon: "success", title: "Muy Bien!", text: data2[0] });
        //Garantias
        if (condi === "restructuracionPpg") {
          limpiarForm(['formulario'])
          ocultaHabilita(["card1", "card2"], 0)
          return;
        }
        if (condi === "insertarGarantia") {
          tbGarantias(inputs[0]);
          return;
        }
        if (condi === "actualizaGarantia") {
          tbGarantias(inputs[6]);
          return;
        }
        //SECCION DE DESEMBOLSOS PARA LOS CREDITOS INDIVIDUALES
        if (condi == "create_desembolso") {
          reportes([[], [], [], [data2[2]]], "pdf", 18, 0, 1);
          if (data2[3] == "cheque") {
            if (data2[5] != "") {
              cheque_desembolso([[], [], [], [data2[4]]], "pdf", 13, 0, 1);
            } else {
              Swal.fire({
                icon: "success",
                title: "Muy Bien!",
                text: "Desembolso con cheque generado correctamente, debe ir al apartado de emisión de cheques para realizar la impresión de este",
              });
            }
          }
          printdiv2("#cuadro", id);
        }

        //SECCION DE IMPRESION DE FICHA DE SOLICITUD
        if (condi == "create_solicitud") {
          reportes([[], [], [], [data2[2]]], `pdf`, "ficha_solicitud", 0);
        }
        //SECCION DE IMPRESION DE FICHA DE ANALISIS
        if (condi == "create_analisis") {
          reportes([[], [], [], [data2[2]]], `pdf`, "ficha_analisis", 0);
          imp_documentos_legales(
            [[], [], [], [data2[2]]],
            "pdf",
            "20",
            0,
            "dictamen",
            1
          );
        }
        if (condi == "create_aprobacion") {
          reportes([[], [], [], [data2[2]]], `pdf`, "ficha_aprobacion", 0);
          imp_documentos_legales(
            [[], [], [], [data2[2]]],
            "pdf",
            "19",
            0,
            "contrato",
            1
          );
        }
        printdiv2("#cuadro", id);
      } else if (data2[1] == "2") {
        Swal.fire({ icon: "warning", title: "¡Alerta!", text: data2[0] });
      } else {
        Swal.fire({ icon: "error", title: "¡ERROR!", text: data2[0] });
        tbGarantias(inputs[0]);
      }
    },
    complete: function () {
      loaderefect(0);
    },
  });
}

function eliminar_mejorado(ideliminar, xtra, condi, pregunta) {
  Swal.fire({
    title: pregunta,
    showDenyButton: true,
    confirmButtonText: "Confirmar",
    denyButtonText: `Cancelar`,
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "../../src/cruds/crud_credito_indi.php",
        method: "POST",
        data: { condi, ideliminar },
        beforeSend: function () {
          loaderefect(1);
        },
        success: function (data) {
          const data2 = JSON.parse(data);
          if (data2[1] == "1") {
            Swal.fire("Correcto", data2[0], "success");
            if (condi == "rechazar_individual") {
              cerrar_modal_cualquiera_con_valor(
                "#modal_cancelar_credito",
                "#id_hidden",
                [`#credito`, `#nombre`]
              );
            }
            printdiv2("#cuadro", xtra);
          } else Swal.fire("Uff", data2[0], "error");
        },
        complete: function () {
          loaderefect(0);
        },
      });
    }
  });
}

//FIN DE DESEMBOLSO DE CREDITOS INDIVIDUALES

//IMPRESION DE CONTRATO O DICTAMEN
function imp_documentos_legales(
  datos,
  tipo,
  file,
  download,
  documento,
  bandera = 0
) {
  fileaux = file;
  consultar_reporte(file, bandera)
    .then(function (action) {
      //PARTE ENCARGADA DE GENERAR EL REPORTE
      if (bandera == 1) {
        file = action;
      } else {
        file = file;
      }
      //INICIO DE REPORTE
      Swal.fire({
        allowOutsideClick: false,
        title: "¿Desea generar la impresión del " + documento + "?",
        showDenyButton: true,
        confirmButtonText: "Si",
        denyButtonText: `No`,
      }).then((result) => {
        if (result.isConfirmed) {
          var datosval = [];
          datosval[0] = getinputsval(datos[0]);
          datosval[1] = getselectsval(datos[1]);
          datosval[2] = getradiosval(datos[2]);
          datosval[3] = datos[3];
          var url = "cre_indi/reportes/" + file + ".php";
          $.ajax({
            url: url,
            async: true,
            type: "POST",
            dataType: "html",
            data: { datosval, tipo },
            beforeSend: function () {
              loaderefect(1);
            },
            success: function (data) {
              // console.log(data);
              var opResult = JSON.parse(data);
              if (opResult.status == 1) {
                switch (download) {
                  case 0:
                    const ventana = window.open();
                    ventana.document.write(
                      "<object data='" +
                      opResult.data +
                      "' type='application/" +
                      opResult.tipo +
                      "' width='100%' height='100%'></object>"
                    );
                    break;
                  case 1:
                    var $a = $(
                      "<a href='" +
                      opResult.data +
                      "' download='" +
                      opResult.namefile +
                      "." +
                      tipo +
                      "'>"
                    );
                    $("body").append($a);
                    $a[0].click();
                    $a.remove();
                    break;
                }
                Swal.fire({
                  icon: "success",
                  title: "Muy Bien!",
                  text: opResult.mensaje,
                });
              } else {
                Swal.fire({
                  icon: "error",
                  title: "¡ERROR!",
                  text: opResult.mensaje,
                });
              }
            },
            complete: function () {
              loaderefect(0);
            },
          });
        }
      });
      //FIN DE REPORTE
    })
    .catch(function (error) {
      Swal.fire("Uff", error, "error");
    });
}

//FUNCION PARA IMPRIMIR CHEQUE AL MOMENTO DE DESEMBOLSAR
function cheque_desembolso(datos, tipo, file, download, bandera = 0) {
  consultar_reporte(file, bandera)
    .then(function (action) {
      //PARTE ENCARGADA DE GENERAR EL REPORTE
      if (bandera == 1) {
        file = action;
      } else {
        file = file;
      }
      //ESPACIO QUE GENERA EL REPORTE
      Swal.fire({
        allowOutsideClick: false,
        title: "¿Desea generar la impresión del cheque?",
        showDenyButton: true,
        confirmButtonText: "Si",
        denyButtonText: `No`,
      }).then((result) => {
        if (result.isConfirmed) {
          var datosval = [];
          datosval[0] = getinputsval(datos[0]);
          datosval[1] = getselectsval(datos[1]);
          datosval[2] = getradiosval(datos[2]);
          datosval[3] = datos[3];
          var url = "../bancos/reportes/" + file + ".php";
          $.ajax({
            url: url,
            async: true,
            type: "POST",
            dataType: "html",
            data: { datosval, tipo },
            beforeSend: function () {
              loaderefect(1);
            },
            success: function (data) {
              // console.log(data);
              var opResult = JSON.parse(data);
              if (opResult.status == 1) {
                switch (download) {
                  case 0:
                    const ventana = window.open();
                    ventana.document.write(
                      "<object data='" +
                      opResult.data +
                      "' type='application/" +
                      opResult.tipo +
                      "' width='100%' height='100%'></object>"
                    );
                    break;
                  case 1:
                    var $a = $(
                      "<a href='" +
                      opResult.data +
                      "' download='" +
                      opResult.namefile +
                      "." +
                      tipo +
                      "'>"
                    );
                    $("body").append($a);
                    $a[0].click();
                    $a.remove();
                    break;
                }
                Swal.fire({
                  icon: "success",
                  title: "Muy Bien!",
                  text: opResult.mensaje,
                });
              } else {
                Swal.fire({
                  icon: "error",
                  title: "¡ERROR!",
                  text: opResult.mensaje,
                });
              }
            },
            complete: function () {
              loaderefect(0);
            },
          });
        }
      });
      //FIN DE ESPACIO PARA REPORTE
    })
    .catch(function (error) {
      Swal.fire("Uff", error, "error");
    });
}

//FUNCION PARA BUSCAR CUENTAS DE AHORRO DEL CLIENTE SELECCIONADO
function buscar_cuentas_ahorro_cli() {
  codcli = document.getElementById("id_cod_cliente").value;
  if (codcli != "") {
    //consultar a la base de datos
    $.ajax({
      url: "../../src/cruds/crud_credito_indi.php",
      method: "POST",
      data: { condi: "buscar_cuentas_ahorro_cli", id: codcli },
      beforeSend: function () {
        loaderefect(1);
      },
      success: function (data) {
        const data2 = JSON.parse(data);
        if (data2[1] == "1") {
          $("#cuentaaho").empty();
          for (var i = 0; i < data2[2].length; i++) {
            $("#cuentaaho").append(
              "<option value='" +
              data2[2][i]["ccodaho"] +
              "'>" +
              data2[2][i]["ccodaho"] +
              " - " +
              data2[2][i]["nombre"] +
              "</option>"
            );
          }
        } else {
          $("#cuentaaho").empty();
          $("#cuentaaho").append(
            "<option value=''>Seleccione una cuenta de ahorro</option>"
          );
          Swal.fire({ icon: "error", title: "¡ERROR!", text: data2[0] });
        }
      },
      complete: function () {
        loaderefect(0);
      },
    });
  }
}

//FUNCION PARA CREAR CONCEPTO POR DEFAULT
function concepto_default(nombre, ccodcta) {
  nombremayusculas = nombre.toUpperCase();
  // console.log(nombremayusculas);
  texto = "DESEMBOLSO DE CRÉDITO A NOMBRE DE " + nombremayusculas;
  $("#glosa").val(texto);
}

//FUNCION PARA RECOLECTAR CHECKBOXS MARCADOS
function recoletar_checks() {
  var permisos = [];
  var index = 0;
  var checkboxsubmenus = document.getElementsByClassName("S");
  for (var checkboxs of checkboxsubmenus) {
    if (checkboxs.checked) {
      permisos[index] = checkboxs.value;
      index++;
    }
  }
  return permisos;
}

//FUNCION PARA MARCAR LAS GARANTIAS MARCADAS
function marcar_garantias_recuperadas(data) {
  // console.log(data);
  for (let index = 0; index < data.length; index++) {
    var check = document.getElementById("S_" + data[index]["id"]);
    check.checked = true;
  }
}

function suma_garantias_de_chequeados(idinput) {
  var checkboxsubmenus = document.getElementsByClassName("S");
  var total = 0;
  for (var checkboxs of checkboxsubmenus) {
    if (checkboxs.checked) {
      var valor = document.getElementById("MA_" + checkboxs.value).innerText;
      total = total + Number(valor);
    }
  }
  $(idinput).val(total);
}

let numOr0 = (n) => (isNaN(parseFloat(n)) ? 0 : parseFloat(n));
function summongas(nocuenta, id) {
  var i = 0;
  let filtot = [];
  while (i != -1) {
    filtot[i] = getinputsval(["mon_" + i + "_" + nocuenta]);
    i = !!document.getElementById("mon_" + (i + 1) + "_" + nocuenta)
      ? i + 1
      : -1;
  }
  let gastos = filtot.reduce((a, b) => numOr0(a) + numOr0(b));
  let capital = $("#ccapital").val();
  $("#gastos").val(gastos);
  $("#desembolsar").val(capital - gastos);
  $("#cantidad").val(capital - gastos);
  cantidad_a_letras(capital - gastos);

  // VALIDAR SI ES UNA MIXTO QUE SE VUELVA EL CHEQUE IGUAL AL MONTO
  var condi = document.getElementById("condi").value;
  if (condi == "INDI_DESEM_MULTI") {
    calcularCheque();
    //alert("HOLA "+ condi);
  }
}
function savedesem(idusu, idagencia) {
  var filgas = [];
  let cuenta = $("#codcredito").val();
  let nocuenta = cuenta.substring(8, 16);
  filgas[0]=[0,0,0,0]
  k = 0;
  while (k != -1) {
    if (!!document.getElementById("idg_" + k + "_" + nocuenta));
    else break;
    filgas[k] = getinputsval([
      "idg_" + k + "_" + nocuenta,
      "mon_" + k + "_" + nocuenta,
      "con_" + k + "_" + nocuenta,
      "ant_" + k + "_" + nocuenta,
    ]);
    filgas[k][1] = numOr0(filgas[k][1]);
    k++;
  }
  console.log(filgas)
  console.log(idPro_gas)
  console.log(afec)
  console.log(ahorro)
  obtiene(
    [
      `id_cod_cliente`,
      `nomcli`,
      `codagencia`,
      `codproducto`,
      `codcredito`,
      `ccapital`,
      `gastos`,
      `desembolsar`,
      `cantidad`,
      `numcheque`,
      `paguese`,
      `numletras`,
      `glosa`,
    ],
    [`tipo_desembolso`, `negociable`, `bancoid`, `cuentaid`, `cuentaaho`],
    [],
    `create_desembolso`,
    `0`,
    [idusu, idagencia, filgas, idPro_gas, afec, ahorro]
  );
}

//*************************************INI
//BUSCA LAS CUENTAS DE AHO Y APR DE UN CLIENTE
var idPro_gas = 0;
var afec = 0;
var ahorro = 0;
function bus_ahoVin(codCli) {
  // Usando jQuery para obtener el valor seleccionado del radio button
  var fila = $('input[name="data_tipcu"]:checked').val();
  var data_fila = document.getElementById(fila);

  // Hacer algo con el valor seleccionado
  if (data_fila.cells[4].innerText === "Cuenta de Ahorro") {
    idPro_gas = data_fila.cells[0].innerText;
    afec = 1;
    inyecCod(
      "#tip_cu",
      "cu_aho",
      codCli,
      (url = "../../src/cris_modales/mdls_aho_apr.php")
    );
  } else {
    inyecCod(
      "#tip_cu",
      "cu_apr",
      codCli,
      (url = "../../src/cris_modales/mdls_aho_apr.php")
    );
    afec = 2;
    idPro_gas = data_fila.cells[0].innerText;
  }
}
//PARA SELECCIONAR EL TIPO DE CUENTA
function selec_cu() {
  var fila = $('input[name="cu_aho_apr"]:checked').val();
  var data_fila = document.getElementById(fila);
  ahorro = data_fila.cells[1].innerText;
}
//PARA OMITIR LA CUENTA DE AHORRO VINCULADO...
function omitir_aho_vin() {
  idPro_gas = 0;
  ahorro = 0;
  afec = 0;
  ac_even("aho_vin", "vista", 0);
}
//VALIDA DATOS DE AHORROS VINCULADOS
function val_aho_vin() {
  if (idPro_gas > 0 && ahorro == 0) {
    $("#ar_ahoVin").focus();
    // Obtenemos la posición del contenedor y hacemos que la página se desplace hacia esa posición
    var posicion = $("#ar_ahoVin").offset().top;
    $("html, body").animate({ scrollTop: posicion }, 1000);

    Swal.fire({
      icon: "question",
      title: "Ahorro vinculado…",
      text: "Favor de seleccionar una cuenta o puede omitir el proceso haciendo click en el booton.",
    });
    return false;
  } else {
    return true;
  }
}
//*************************************FIN

// NEGROY FUNCION DE DESEMBOLSO MULTIPLE
function calcularCheque() {
  // Obtener los valores actuales
  var total = parseFloat(document.getElementById("desembolsar").value);
  var efectivo = parseFloat(document.getElementById("MontoEFECTIVO").value);

  // Validar que el efectivo no sea mayor al total
  if (efectivo <= total) {
    // Calcular el monto en cheque
    var cheque = total - efectivo;

    // Actualizar el valor del input de cheque
    document.getElementById("MontoCHEQUE").value = cheque.toFixed(2);
    document.getElementById("cantidad").value = cheque.toFixed(2);
  } else {
    alert("¡Error! El monto en efectivo no puede ser mayor al total.");
    // Reiniciar el valor del input de efectivo
    document.getElementById("MontoEFECTIVO").value = "0";
    document.getElementById("MontoCHEQUE").value = total;
  }
  cantidad_a_letras(cheque);
}

// MULTI DESEMBOLSO BTN
function saveMultiDsmbls(idusu, idagencia) {
  var filgas = [];
  let cuenta = $("#codcredito").val();
  let nocuenta = cuenta.substring(8, 16);

  // Validación de MontoCHEQUE
  let montoCheque = $("#MontoCHEQUE").val(); // Obtener el valor del input MontoCHEQUE
  if (!montoCheque) {
    Swal.fire({
      icon: "error",
      title: "¡Alerta!",
      text: "Por favor ingrese el monto del EFECTIVO.",
    });
    return; // Salir de la función
  }
  // Validate that the "cuentaid" has a selected value before proceeding
  let cuentaid = $("#cuentaid").val();
  let numcheque = $("#numcheque").val();
  if (!cuentaid) {
    Swal.fire({
      icon: "error",
      title: "¡Alerta!",
      text: "Seleccione una cuenta antes de continuar.",
    });
    return;
  }

  if (!numcheque) {
    Swal.fire({
      icon: "error",
      title: "¡Alerta!",
      text: "AGREGUE UN No DE CHEQUE.",
    });
    return;
  }

  filgas[0]=[0,0,0,0];
  k = 0;
  while (k != -1) {
    if (!!document.getElementById("idg_" + k + "_" + nocuenta));
    else break;
    filgas[k] = getinputsval([
      "idg_" + k + "_" + nocuenta,
      "mon_" + k + "_" + nocuenta,
      "con_" + k + "_" + nocuenta,
      "ant_" + k + "_" + nocuenta,
    ]);
    filgas[k][1] = numOr0(filgas[k][1]);
    k++;
  }
  obtiene(
    [
      `id_cod_cliente`,
      `nomcli`,
      `codagencia`,
      `codproducto`,
      `codcredito`,
      `ccapital`,
      `gastos`,
      `desembolsar`,
      `cantidad`,
      `numcheque`,
      `paguese`,
      `numletras`,
      `glosa`,
      `MontoEFECTIVO`,
      `MontoCHEQUE`,
    ],
    [`desembolso1`, `negociable`, `bancoid`, `cuentaid`, `cuentaaho`],
    [],
    `create_desembolso`,
    `0`,
    [idusu, idagencia, filgas, idPro_gas, afec, ahorro]
  );
}

function ac_even(namEle, op_eve, op) {
  switch (op_eve) {
    case "vista":
      op == 0 ? $("#" + namEle).hide() : $("#" + namEle).show();
      break;
  }
}
/*****************************************************
 ******RESTRUCTURACION DE PLAN DE PAGO ***************
 ******************************************************/
function restruc(op) {
  switch (op) {
    case 1:
      codCre = $("#codCre").val();
      if (codCre === "") {
        msjAlert(
          "warning",
          "Primero tiene que seleccionar un credito",
          "¡Alerta!"
        );
        return false;
      } else {
        ocultaHabilita(["card1", "card2"], 1);
        msjAlert("success", "A continuación, puede realizar los cambios necesarios. Ojo un crédito no se puede restructurar por segunda vez. ", "¡Restructuración!");
        return true;
      }
      break;
    case 2:
      interes = $("#interes").val();
      if (interes === "" || interes < 1) {
        msjAlert("warning", "El interes tiene que ser mayor 0", "¡Alerta!");
        return false;
      } else {
        return true;
      }
      break;
    case 3:
      if (restruc(2) === false) return false;

      plazo = $("#plazo").val()
      if (plazo === "" || plazo < 1) {
        msjAlert("warning", "El plazo tiene que ser mayor 0", "¡Alerta!");
        return false;
      } else {
        obtiene(['codCre', 'codProducto', 'interes', 'salRestruturacion', 'fecSigPago', 'plazo', 'idProduc', 'fecUltPago'], ['tipocred', 'periodo'], [], 'restructuracionPpg', '', '')
        $("#btnGua").prop("disabled", true)
      }
      break;
    case 4:
      restruc(2);
      if ($("#plazo").val() === "" || plazo < 1) {
        msjAlert("warning", "El plazo tiene que ser mayor 0", "¡Alerta!");
        return false;
      }
      reportes([['codCre', 'cliente', 'codProducto', 'salRestruturacion', 'interes', 'fecDes', 'fecSigPago', 'plazo'], ['tipocred', 'periodo'], [], []], 'pdf', 'planPago_restructuracion', 0)
      $("#btnGua").prop("disabled", false)
      break;
  }
}

function msjAlert(tipo, msj, title) {
  Swal.fire({
    icon: tipo,
    title: title,
    text: msj,
  });
}

function ocultaHabilita(nameElemento, op) {
  for (con = 0; con < nameElemento.length; con++) {
    if (op == 0) {
      $("#" + nameElemento[con]).hide();
    }
    if (op == 1) {
      $("#" + nameElemento[con]).show();
    }
  }
}

function limpiarForm(nameElemento) {
  for (con = 0; con < nameElemento.length; con++) {
    document.getElementById('formulario').reset();
    ocultaHabilita(["card1", "card2"], 0);
  }
}

function controlSelect(nameSelect) {
  var data = $("#" + nameSelect).val();
  console.log(data);
  if (data === "Amer") {
    $("#periodo option[value='1D']").prop("disabled", true);
    $("#periodo option[value='7D']").prop("disabled", true);
    $("#periodo option[value='15D']").prop("disabled", true);
    $("#periodo option[value='14D']").prop("disabled", true);
    $("#periodo option[value='1M']").prop("disabled", false);
    $("#periodo").val("1M");
  } else {
    $("#periodo option[value='1D']").prop("disabled", false);
    $("#periodo option[value='7D']").prop("disabled", false);
    $("#periodo option[value='15D']").prop("disabled", false);
    $("#periodo option[value='14D']").prop("disabled", false);
    $("#periodo option[value='1M']").prop("disabled", false);
  }
}