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

$(document).on("click", "#togglePassword", function(e) {
    e.preventDefault();
    var type = $(this).parent().parent().find("#password").attr("type");
    if (type == "password") {
        $(this).removeClass("fa-regular fa-eye");
        $(this).addClass("fa-regular fa-eye-slash");
        $(this).parent().parent().find("#password").attr("type", "text");
    } else if (type == "text") {
        $(this).removeClass("fa-regular fa-eye-slash");
        $(this).addClass("fa-regular fa-eye");
        $(this).parent().parent().find("#password").attr("type", "password");
    }
});

//funcipon para iniciar sesion
function validarFormLogin() {
    // Campos de texto
    if ($("#usuario").val() == "") {
        // alert("El campo usuario no puede estar vacío");
        Swal.fire({
            icon: 'error',
            title: '¡ERROR!',
            text: "El campo usuario no puede estar vacío"
        });
        $("#nombre").focus();       // Esta función coloca el foco de escritura del usuario en el campo Nombre directamente.
        return false;
    }
    if ($("#password").val() == "") {
        // alert("El campo contraseña no puede estar vacío.");
        Swal.fire({
            icon: 'error',
            title: '¡ERROR!',
            text: "El campo contraseña no puede estar vacío"
        });
        $("#apellidos").focus();
        return false;
    }
    return true; // Si todo está correcto
}

$("#frmlogin").on('submit', function (e) {
    e.preventDefault();
    if (validarFormLogin()) {
        var dataForm = $(this).serialize();
        $.ajax({
            type: 'POST',
            url: 'src/cruds/crud_usuario.php',
            data: dataForm,
            dataType: 'json',
            beforeSend: function () {
                loaderefect(1);
            },
            success: function (data) {
                //console.log(data);
                // typeof variable !== 'undefined' ? console.log('La variable está definida') : console.log('La variable no está definida');
                icono = ("icon" in data) ? data.icon : 'error';
                titulo = ("title" in data) ? data.title : '¡ERROR!';

                loaderefect(0);
                // if(data[2]=== 'pago_pendiente'){
                //     Swal.fire({
                //         icon: 'warning',
                //         title: '¡Pago pendiente!',
                //         text: data[1]
                //     });
                //     return; 
                // }
                if (data[0]) {
                    redireccionar(data[2].puesto);
                    // if (data[2].puesto == ) {
                    // }
                } else {
                    Swal.fire({
                        icon: icono,
                        title: titulo,
                        text: data[1]
                    });
                }
            },
            error: function (xhr) {
                //console.log(xhr);
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
});

function redireccionar(valor) {
    $.ajax({
        type: 'POST',
        url: 'src/cruds/crud_usuario.php',
        data: { 'condi': 'redirect' },
        dataType: 'json',
        beforeSend: function () {
            loaderefect(1);
        },
        success: function (data) {
            loaderefect(0);
            const data2 = data;
            if (data2[1] == "1") {
                url = data2[0];
                $(location).attr("href", url);
            }
            else {
                Swal.fire({
                    icon: 'error',
                    title: '¡ERROR!',
                    text: data2[0]
                });
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

$("#eliminarsesion").click(function (e) {
    // console.log('ci');
    e.preventDefault();
    $.ajax({
        type: 'POST',
        url: 'src/cruds/crud_usuario.php',
        data: { 'condi': 'salir' },
        dataType: 'json',
        beforeSend: function () {
            loaderefect(1);
        },
        success: function (data) {
            loaderefect(0);
            // console.log(data);
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