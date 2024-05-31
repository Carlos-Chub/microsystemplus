const body = document.querySelector('body'),
  sidebar = body.querySelector('nav'),
  toggle = body.querySelector(".toggle"),
  searchBtn = body.querySelector(".search-box"),
  modeSwitch = body.querySelector(".toggle-switch"),
  modeText = body.querySelector(".mode-text");

// ABRE Y CIERRA EL MENU DEL LATERAL
toggle.addEventListener("click", () => {
  sidebar.classList.toggle("close");
})

//funcion para el toogle 
function active_modo(bandera = 1, retornos = '..') {
  var color = "";
  if (body.classList.contains("dark")) {
    color = 0;
  } else {
    color = 1;
  }
  //Realizar una consulta ajax
  $.ajax({
    type: 'POST',
    url: retornos + '/src/cruds/crud_usuario.php',
    data: { 'condi': 'modo', 'color': color, 'bandera': bandera },
    dataType: 'json',
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      loaderefect(0);
      // console.log(data);
      if (data[2] == '1') {
        body.classList.add("dark");
        modeText.innerText = "Modo Claro";
      }
      else {
        body.classList.remove("dark");
        modeText.innerText = "Modo Oscuro";
      }
    },
    error: function (xhr) {
      loaderefect(0);
      Swal.fire({
        icon: 'error',
        title: '¡ERROR!',
        text: 'Codigo de error: ' + xhr.status + ', Información de error: ' + xhr.responseJSON
      });
    },
    complete: function () {
      loaderefect(0);
    },
  });
}

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

//#region FUNCION QUE EVALUA CUANDO CARGA TODOS LOS RECURSOS Y DESAPARECE EL LOADER
$(document).ready(function () {
  loaderefect(0);
  // active_modo(0);
  // console.log("hola");
});
//#endregion

function salir() {
  $(location).attr('href', 'index.php');
}
//#region LOADER
//FUNCION PARA EL EFECTO DEL LOADER
// function loaderefect(sh) {
//   const LOADING = document.querySelector('.loader-container');
//   switch (sh) {
//     case 1:
//       LOADING.classList.remove('loading--hide');
//       LOADING.classList.add('loading--show');
//       break;
//     case 0:
//       LOADING.classList.add('loading--hide');
//       LOADING.classList.remove('loading--show');
//       break;
//   }
// }
//#endregion

//script para eliminar la session
$("#eliminarsesion").click(function (e) {
  // console.log('ci');
  e.preventDefault();
  $.ajax({
    type: 'POST',
    url: '../src/cruds/crud_usuario.php',
    data: { 'condi': 'salir' },
    dataType: 'json',
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      loaderefect(0);
      window.location.reload();
    },
    error: function (xhr) {
      loaderefect(0);
      Swal.fire({
        icon: 'error',
        title: '¡ERROR!',
        text: 'Codigo de error: ' + xhr.status + ', Información de error: ' + xhr.responseJSON
      });
    },
    complete: function () {
      loaderefect(0);
    },
  });
});

$("#eliminarsesion2").click(function (e) {
  // console.log('ci');
  e.preventDefault();
  $.ajax({
    type: 'POST',
    url: '../../src/cruds/crud_usuario.php',
    data: { 'condi': 'salir' },
    dataType: 'json',
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      loaderefect(0);
      window.location.reload();
    },
    error: function (xhr) {
      loaderefect(0);
      Swal.fire({
        icon: 'error',
        title: '¡ERROR!',
        text: 'Codigo de error: ' + xhr.status + ', Información de error: ' + xhr.responseJSON
      });
    },
    complete: function () {
      loaderefect(0);
    },
  });
});

//******** codigo para depurar */
$(document).ready(function () {
  inyecCod();
});
//INYECTAR CODIGO 
function inyecCod(idElem = '#tbAlerta', condi = 'alertas', url = "../src/menu/alertatb.php") {
  // console.log(condi);
  $.ajax({
    url: url,
    type: "POST",
    data: {
      condi,
    },
    beforeSend: function () {
      loaderefect(1);
    },
    success: function (data) {
      if (condi === 'alertas') {
        if (data !== false) {
          consulta();
          timerLock();
          $(idElem).html(data);
          inyecCod(idElem = '', condi = 'dataTablef', url = "../src/menu/alertatb.php")
          return;
        }
      }
      if (condi === 'dataTablef') {
        const data2 = JSON.parse(data);
        for (con = 0; con < data2[0].length; con++) {
          if ($("#" + data2[0][con]).length > 0) {
            dataTable(data2[0][con]);
          }
        }
      }
    },
    complete: function () {
      loaderefect(0);
    }
  });
}

function consulta() {
  condi = 'con_alt';
  idElem = '#id_con_alt';
  
  return new Promise((resolve, reject) => {
    $.ajax({
      url: "../src/menu/alertatb.php",
      method: "POST",
      data: {
        condi,
      },
      success: function (data) {
        data = JSON.parse(data);
        if (data[1] == 1) {
          $(idElem).text(data[0]);
          resolve(data[1]); // Resolvemos la promesa con el valor de data[1]
        } else {
          resolve(0); // Si no hay datos, resolvemos con 0
        }
      },
      error: function(xhr, status, error) {
        reject(error); // En caso de error, rechazamos la promesa
      }
    });
  });
}

function timerLock() {
  var intervalo = setInterval(function () {
    consulta().then(function (data) {
      if (data == 0) {
        clearInterval(intervalo); // Detener el intervalo si data es 1
      }
    }).catch(function (error) {
      console.error('Error en la consulta:', error);
    });
  }, 60000);
}

function dataTable(id_tabla) {
  $('#tb' + id_tabla).on('search.dt')
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

function obtieneAux(datos, archivo) {
  var condi = 'proceIVE';
  genericoAux(datos, archivo, condi);
}

//ASEPTAR LOS DATOS DEL IVE 
function genericoAux(datos, archivo, condi, url = "../src/cruds/crud_alerta.php") {
  $.ajax({
    url: url,
    method: "POST",
    data: {
      datos,
      archivo,
      condi
    },
    beforeSend: function () {
      loaderefect(1);
    },

    success: function (data) {
      const data2 = JSON.parse(data);

      if (data2[1] == "1") {
        if (condi === "validar_usuario_por_interes") {
          // console.log(datos[0][1]);
          inte = $('#' + datos[0][3]).val();
          datos[0].push(inte);
          genericoAux([datos[0]], [''], 'act_interes', '../../src/cruds/crud_credito_indi.php')
        }
        else if (condi === "act_interes") {
          Swal.fire({
            icon: "success",
            title: "Muy Bien!",
            text: data2[0]
          });
        }
        else if (condi === "proceIVE") {
          Swal.fire({
            icon: "success",
            title: "Muy Bien!",
            text: data2[0]
          });
          cerrarModal();
          inyecCod();
        }


      } else {
        if (condi === "validar_usuario_por_interes") {
          $('#' + datos[0][3]).val(datos[0][4]);
        }
        Swal.fire({
          icon: "error",
          title: "¡ERROR!",
          text: data2[0]
        });
      }
    },
    complete: function () {
      loaderefect(0);
    },
  });
}

function cerrarModal() {
  $("#modal_alt").modal('hide');
}

function abrirModal() {
  $("#modal_alt").modal('show');
}

/*
  Alerta cuando un usuario necesita realizar una modificacion y se tienen que autenticar
*/

function validaInteres(extra){
  int_act = parseFloat($('#'+extra[3]).val());
  // if( int_act > parseFloat(extra[4])){
  //   $('#'+extra[3]).val(extra[4]);
  //   Swal.fire({
  //     icon: "error",
  //     title: "¡ERROR!",
  //     text: "El nuevo interés tiene que ser menor al interés actual… :)"
  //   });
  // }else{
    alertaRestrincion(extra);
  // }
}

async function alertaRestrincion(extra) {

  const swalWithBootstrapButtons = Swal.mixin({
    customClass: {
      confirmButton: "btn btn-success",
      cancelButton: "btn btn-danger"
    },
    buttonsStyling: false
  });

  const result = await swalWithBootstrapButtons.fire({
    title: "ALERTA?",
    text: "¿Está seguro de cambiar el interés?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Guardar cambios",
    cancelButtonText: "Cancelar",
    reverseButtons: true,
    allowOutsideClick: false
  });

  if (result.isConfirmed) {

    // const { value: password } = await Swal.fire({
    //   title: "Ingrese su contraseña",
    //   input: "password",
    //   inputLabel: "Contraseña",
    //   inputPlaceholder: "Ingrese su contraseña",
    //   inputAttributes: {
    //     maxlength: "30",
    //     autocapitalize: "off",
    //     autocorrect: "off"
    //   },
    //   allowOutsideClick: false
    // });

    // if (password.length > 0) {
    //   loaderefect(0);
    //   genericoAux([extra, password], [''], 'validar_usuario_por_interes', '../../src/cruds/crud_usuario.php')
    // }else{
    //   $('#'+extra[3]).val(extra[4]);
    // }
    //--REQ--ADG--1-- No validar usuario al modificar plan de pagos
    datos=[extra];
    inte = $('#'+extra[3]).val(); 
    datos[0].push(inte);
    genericoAux([datos[0]], [''], 'act_interes', '../../src/cruds/crud_credito_indi.php')

  } else if (result.dismiss === Swal.DismissReason.cancel) {
    $('#' + extra[3]).val(extra[4]);
    swalWithBootstrapButtons.fire({
      title: "Cancelado",
      text: "Se cancelo la actualización del interes :)",
      icon: "error"
    });

  }
}