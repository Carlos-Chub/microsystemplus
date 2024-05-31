//#region FUNCIONES PARA FORMATOS DE LIBRETA
// MAIN
function impresion_libreta_main(datos, resta, ini, posini, posfin, saldoo, numi, file) {
    var inif = parseInt(datos[0][2]);
    var nfront = parseInt(datos[0][0]);
    var inid = parseInt(datos[0][3]);
    var ndors = parseInt(datos[0][1]);

    posac = 0;
    bandera = 0;
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [240, 300]
    };
    var doc = new jsPDF(opciones);
    doc.setFontSize(9);
    var i = posini;
    var tiptr;
    var saldo = saldoo;
    var monto = parseFloat(0);
    while (i < (datos[1].length)) {
        doc.setFontSize(9);
        num = parseInt(datos[1][i]['numlinea']);
        pos = (6 * (parseInt(num) - parseInt(resta))) + parseInt(ini);
        // doc.text(10, pos, "" + datos[1][i]['correlativo']);
        fecha = conviertefecha(datos[1][i]['dfecope']);
        // console.log(fecha);
        doc.text(10, pos, "" + fecha);
        // doc.text(36, pos, "" + datos[1][i]['cnumdoc']);
        monto = parseFloat(datos[1][i]['monto']);
        tiptr = datos[1][i]['ctipope'];
        //*** */
        if (tiptr == "R") {
            saldo = parseFloat(saldo) - parseFloat(monto);
            transaccion = "Retiro/Debito";

            doc.text(30, pos, transaccion);
            doc.text(64, pos, "" + pad(currency(monto)));
            doc.text(88, pos, "");
        }
        if (tiptr == "D") {
            saldo = parseFloat(saldo) + parseFloat(monto);
            transaccion = "Deposito/Intereses";
            doc.text(30, pos, transaccion);
            doc.text(64, pos, "");
            doc.text(88, pos, "" + pad(currency(monto)));
        }
        doc.text(113, pos, "" + pad(currency(saldo)));
        if (num >= posfin) {
            posac = i + 1;
            bandera = (num >= (nfront + ndors)) ? 0 : 1;
            break;
        }
        i++;
    }

    if (numi > nfront) {
        Swal.fire({
            title: 'Entrando en la reversa de libreta',
            showDenyButton: false,
            confirmButtonText: 'Listo Imprimir',
        }).then((result) => {
            if (result.isConfirmed) {
                //
                doc.autoPrint();
                window.open(doc.output('bloburl'));

            } else if (result.isDenied) {
                Swal.fire('Uff', 'Cancelado', 'success')
            }
        })
    }
    else {
        doc.autoPrint();
        window.open(doc.output('bloburl'));
    }
    if (bandera == 1) {
        //SE EJECUTA RECURSIVAMENTE
        window[file](datos, nfront, inid, posac, nfront + ndors, saldo, nfront + 1, file);
    }
}

function impresion_libreta_cooprode(datos, resta, ini, posini, posfin, saldoo, numi, file) {
    var inif = parseInt(datos[0][2]);
    var nfront = parseInt(datos[0][0]);
    var inid = parseInt(datos[0][3]);
    var ndors = parseInt(datos[0][1]);

    posac = 0;
    bandera = 0;
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [240, 300]
    };
    var doc = new jsPDF(opciones);
    doc.setFontSize(13);

    var i = posini;
    var tiptr;
    var saldo = saldoo;
    var monto = parseFloat(0);
    var pos = 67;
    while (i < (datos[1].length)) {
        num = parseInt(datos[1][i]['numlinea']);
        //pos = (6 * (parseInt(num) - parseInt(resta))) + parseInt(ini);
        doc.text(10, pos, "" + datos[1][i]['correlativo']);
        fecha = conviertefecha(datos[1][i]['dfecope']);
        // console.log(fecha);
        doc.text(16, pos, "" + fecha);
        doc.text(42, pos, "" + datos[1][i]['cnumdoc']);
        monto = parseFloat(datos[1][i]['monto']);
        tiptr = datos[1][i]['ctipope'];
        //*** */
        if (tiptr == "R") {
            saldo = parseFloat(saldo) - parseFloat(monto);
            doc.text(68, pos, "");
            doc.text(68, pos, "" + pad(currency(monto)));
        }
        if (tiptr == "D") {
            saldo = parseFloat(saldo) + parseFloat(monto);

            doc.text(94, pos, "" + pad(currency(monto)));
            doc.text(94, pos, "");
        }

        doc.text(120, pos, "" + pad(currency(saldo)));
        if (num >= posfin) {
            posac = i + 1;
            bandera = (num >= (nfront + ndors)) ? 0 : 1;
            break;
        }
        pos += 4;
        i++;
    }

    if (numi > nfront) {
        Swal.fire({
            title: 'Entrando en la reversa de libreta',
            showDenyButton: false,
            confirmButtonText: 'Listo Imprimir',
        }).then((result) => {
            if (result.isConfirmed) {
                //
                doc.autoPrint();
                window.open(doc.output('bloburl'));

            } else if (result.isDenied) {
                Swal.fire('Uff', 'Cancelado', 'success')
            }
        })
    }
    else {
        doc.autoPrint();
        window.open(doc.output('bloburl'));
    }

    if (bandera == 1) {
        //SE EJECUTA RECURSIVAMENTE
        window[file](datos, nfront, inid, posac, nfront + ndors, saldo, nfront + 1, file);
    }
}



function impresion_libreta_primavera(datos, resta, ini, posini, posfin, saldoo, numi, file) {

    // console.log("impresion libreta main")
    // console.log(datos)
    var inif = parseInt(datos[0][2]);
    var nfront = parseInt(datos[0][0]);
    var inid = parseInt(datos[0][3]);
    var ndors = parseInt(datos[0][1]);

    posac = -5;
    bandera = 0;
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [240, 300]
    };
    var doc = new jsPDF(opciones);
    doc.setFontSize(9);
    //var i = 0;
    var i = posini;
    var tiptr;
    //var saldo = parseFloat(datos[0][4])*porsaldo;
    var saldo = saldoo;
    var monto = parseFloat(0);
    while (i < (datos[1].length)) {
        // console.log(datos[1][i]);
        doc.setFontSize(9);
        num = parseInt(datos[1][i]['numlinea']);
        pos = (6 * (parseInt(num) - parseInt(resta))) + parseInt(ini);
        // doc.text(10, pos, "" + datos[1][i]['correlativo']);
        fecha = conviertefecha(datos[1][i]['dfecope']);
        // console.log(fecha);
        doc.text(23, pos, "" + fecha);
        // doc.text(36, pos, "" + datos[1][i]['cnumdoc']);
        monto = parseFloat(datos[1][i]['monto']);
        tiptr = datos[1][i]['ctipope'];
        //*** */
        if (tiptr == "R") {
            saldo = parseFloat(saldo) - parseFloat(monto);
            transaccion = "Retiro/Debito";
            doc.text(41, pos, transaccion);
            doc.text(103, pos, "" + pad(currency(monto)));
            doc.text(79, pos, "");
        }
        if (tiptr == "D") {
            saldo = parseFloat(saldo) + parseFloat(monto);
            transaccion = "Deposito/Intereses";

            doc.text(41, pos, transaccion);
            doc.text(103, pos, "");
            doc.text(79, pos, "" + pad(currency(monto)));
        }
        doc.text(130, pos, "" + pad(currency(saldo)));
        if (num >= posfin) {
            posac = i + 1;
            bandera = (num >= (nfront + ndors)) ? 0 : 1;
            break;
        }
        i++;
    }
    if (numi > nfront) {
        // console.log("aki papu 2");
        Swal.fire({
            title: 'Entrando en la reversa de libreta',
            showDenyButton: false,
            confirmButtonText: 'Listo Imprimir',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                //
                doc.autoPrint();
                window.open(doc.output('bloburl'));

            } else if (result.isDenied) {
                Swal.fire('Uff', 'Cancelado', 'success')
            }
        })
    }
    else {
        // console.log("aki papu 3");
        doc.autoPrint();
        window.open(doc.output('bloburl'));
    }
    if (bandera == 1) {
        // console.log("aki papu 4");
        //SE EJECUTA RECURSIVAMENTE
        window[file](datos, nfront, inid, posac, nfront + ndors, saldo, nfront + 1, file);
    }

}


//#endregion

//#region FUNCIONES PARA FORMATO DE RETIROS Y DEPOSITOS
// MAIN
function impresion_recibo_dep_ret_main(datos) {
    alert("Impresion de recibo *1 ");
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [216, 300]
    };
    var doc = new jsPDF(opciones);
    var i = 1;
    var ini = 30;
    var margenizquierdo = 20;

    while (i < 2) {
        doc.setFontSize(12);
        doc.text(margenizquierdo, ini, 'Cuenta de aportación No. ' + datos[2]);
        doc.text(115, ini, 'Fecha doc: ' + datos[4]);
        doc.text(180, ini, '' + datos[5]);

        ini = ini + 7;
        doc.text(margenizquierdo, ini, 'Cliente: ' + datos[7]);

        ini = ini + 7;
        doc.text(margenizquierdo, ini, 'Operacion y No. Docto: ' + datos[5]);
        doc.text(150, ini, 'Monto: Q ' + datos[3]);

        ini = ini + 7;
        doc.text(60, ini, 'Operación: ' + datos[6]);

        doc.setFontSize(10);
        ini = ini + 7;
        doc.text(margenizquierdo, ini, 'Operador: ' + datos[8] + ' ' + datos[9]);
        ini = ini + 5;
        doc.text(margenizquierdo, ini, 'Fecha operación: ' + datos[10]);

        ini = ini + 40;
        i++;
    }
    doc.autoPrint();
    window.open(doc.output('bloburl'))
}

// PRIMAVERA -> FORMAS
function impresion_recibo_dep_ret_primavera(datos) {
    alert("Impresion de recibo *2");
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [216, 300]
    };
    var doc = new jsPDF(opciones);
    var fuente = 'Courier';
    doc.setFont(fuente);

    var i = 1;
    var ini = 40;
    var margenizquierdo = 10;
    while (i < 2) {
        doc.setFontSize(13);
        doc.setFontStyle('bold');
        doc.text(margenizquierdo, ini, 'Cuenta de aportación: ' + datos[2]);
        doc.text(115, ini, 'Fecha doc: ' + datos[4]);
        //doc.text(180, ini, '' + datos[5]);

        ini = ini + 7;
        doc.text(margenizquierdo, ini, 'Cliente: ' + datos[7]);

        ini = ini + 7;
        doc.text(margenizquierdo, ini, 'Operacion y No. Docto: ' + datos[5]);
        doc.text(150, ini, 'Monto: Q ' + datos[3]);

        ini = ini + 7;
        doc.text(60, ini, 'Operación: ' + datos[6]);

        doc.setFontSize(11);
        ini = ini + 7;
        doc.text(margenizquierdo, ini, 'Operador: ' + datos[8] + ' ' + datos[9]);
        ini = ini + 5;
        doc.text(margenizquierdo, ini, 'Fecha operación: ' + datos[10]);

        ini = ini + 40;
        i++;
    }

    doc.autoPrint();
    window.open(doc.output('bloburl'))
}


function impresion_recibo_dep_ret_coditoto(datos) {
    alert("Impresion de recibo");
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [216, 300]
    };
    var doc = new jsPDF(opciones);
    var fuente = 'Courier';
    doc.setFont(fuente);

    var i = 1;
    var ini = 40;
    var margenizquierdo = 80;
    while (i <= 3) {
        doc.setFontSize(11);
        doc.setFontStyle('bold');
        doc.text(margenizquierdo + 85, ini - 28, ' ' + datos[5]);
        doc.setFontSize(13);
        doc.text(margenizquierdo, ini - 10, 'Número de cuenta de Aportacion ' + datos[2]);
        doc.text(margenizquierdo, ini - 5, 'Fecha del documento: ' + datos[4]);
        doc.text(margenizquierdo, ini, 'Cliente: ' + datos[7]);
        doc.text(margenizquierdo, ini + 5, 'Operación y No. Docto: ' + datos[5]);
        doc.text(margenizquierdo, ini + 10, 'Monto: Q ' + datos[3]);
        doc.text(margenizquierdo, ini + 15, 'Operación: ' + datos[6]);

        doc.setFontSize(11);
        doc.text(margenizquierdo, ini + 20, 'Operador: ' + datos[8] + ' ' + datos[9]);
        doc.text(margenizquierdo, ini + 25, 'Fecha de la operación: ' + datos[10]);
        ini += 53;

        ini = ini + 40;
        i++;
    }

    doc.autoPrint();
    window.open(doc.output('bloburl'))
}

// CIACREHO ->FORMAS
function impresion_libreta_ciacreho(datos, resta, ini, posini, posfin, saldoo, numi, file) {
    var inif = parseInt(datos[0][2]);
    var nfront = parseInt(datos[0][0]);
    var inid = parseInt(datos[0][3]);
    var ndors = parseInt(datos[0][1]);

    posac = 0;
    bandera = 0;
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [240, 300]
    };
    var doc = new jsPDF(opciones);
    doc.setFontSize(9);

    var i = posini;
    var tiptr;
    var saldo = saldoo;
    var monto = parseFloat(0);
    while (i < (datos[1].length)) {
        doc.setFontSize(9);
        num = parseInt(datos[1][i]['numlinea']);
        pos = (6 * (parseInt(num) - parseInt(resta))) + parseInt(ini);
        // doc.text(10, pos, "" + datos[1][i]['correlativo']);
        fecha = conviertefecha(datos[1][i]['dfecope']);
        // console.log(fecha);
        doc.text(10, pos, "" + fecha);
        // doc.text(36, pos, "" + datos[1][i]['cnumdoc']);
        monto = parseFloat(datos[1][i]['monto']);
        tiptr = datos[1][i]['ctipope'];
        //*** */
        if (tiptr == "R") {
            saldo = parseFloat(saldo) - parseFloat(monto);
            transaccion = "Retiro/Debito";

            doc.text(30, pos, transaccion);
            doc.text(64, pos, "" + pad(currency(monto)));
            doc.text(88, pos, "");
        }
        if (tiptr == "D") {
            saldo = parseFloat(saldo) + parseFloat(monto);
            transaccion = "Deposito/Intereses";

            doc.text(30, pos, transaccion);
            doc.text(64, pos, "");
            doc.text(88, pos, "" + pad(currency(monto)));
        }
        doc.text(113, pos, "" + pad(currency(saldo)));
        if (num >= posfin) {
            posac = i + 1;
            bandera = (num >= (nfront + ndors)) ? 0 : 1;
            break;
        }
        i++;
    }

    if (numi > nfront) {
        Swal.fire({
            title: 'Entrando en la reversa de libreta',
            showDenyButton: false,
            confirmButtonText: 'Listo Imprimir',
        }).then((result) => {
            if (result.isConfirmed) {
                //
                doc.autoPrint();
                window.open(doc.output('bloburl'));

            } else if (result.isDenied) {
                Swal.fire('Uff', 'Cancelado', 'success')
            }
        })
    }
    else {
        doc.autoPrint();
        window.open(doc.output('bloburl'));
    }
    if (bandera == 1) {
        //SE EJECUTA RECURSIVAMENTE
        window[file](datos, nfront, inid, posac, nfront + ndors, saldo, nfront + 1, file);
    }
}

//libreta de copibelen 
function impresion_libreta_copefuente(datos, resta, ini, posini, posfin, saldoo, numi, file) {
    var inif = parseInt(datos[0][2]);
    var nfront = parseInt(datos[0][0]);
    var inid = parseInt(datos[0][3]);
    var ndors = parseInt(datos[0][1]);

    posac = 0;
    bandera = 0;
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [240, 300]
    };
    var doc = new jsPDF(opciones);
    doc.setFontSize(9);

    var i = posini;
    var tiptr;
    var saldo = saldoo;
    var monto = parseFloat(0);
    while (i < (datos[1].length)) {
        doc.setFontSize(9);
        num = parseInt(datos[1][i]['numlinea']);
        pos = (6 * (parseInt(num) - parseInt(resta))) + parseInt(ini);
        // doc.text(10, pos, "" + datos[1][i]['correlativo']);
        fecha = conviertefecha(datos[1][i]['dfecope']);
        // console.log(fecha);
        doc.text(10, pos, "" + fecha);
        // doc.text(36, pos, "" + datos[1][i]['cnumdoc']);
        monto = parseFloat(datos[1][i]['monto']);
        tiptr = datos[1][i]['ctipope'];
        //*** */
        if (tiptr == "R") {
            saldo = parseFloat(saldo) - parseFloat(monto);
            transaccion = "Retiro/Debito";

            doc.text(30, pos, transaccion);
            doc.text(64, pos, "" + pad(currency(monto)));
            doc.text(88, pos, "");
        }
        if (tiptr == "D") {
            saldo = parseFloat(saldo) + parseFloat(monto);
            transaccion = "Deposito/Intereses";

            doc.text(30, pos, transaccion);
            doc.text(64, pos, "");
            doc.text(88, pos, "" + pad(currency(monto)));
        }
        doc.text(113, pos, "" + pad(currency(saldo)));
        if (num >= posfin) {
            posac = i + 1;
            bandera = (num >= (nfront + ndors)) ? 0 : 1;
            break;
        }
        i++;
    }

    if (numi > nfront) {
        Swal.fire({
            title: 'Entrando en la reversa de libreta',
            showDenyButton: false,
            confirmButtonText: 'Listo Imprimir',
        }).then((result) => {
            if (result.isConfirmed) {
                //
                doc.autoPrint();
                window.open(doc.output('bloburl'));

            } else if (result.isDenied) {
                Swal.fire('Uff', 'Cancelado', 'success')
            }
        })
    }
    else {
        doc.autoPrint();
        window.open(doc.output('bloburl'));
    }
    if (bandera == 1) {
        //SE EJECUTA RECURSIVAMENTE
        window[file](datos, nfront, inid, posac, nfront + ndors, saldo, nfront + 1, file);
    }
}


function impresion_libreta_coditoto(datos, resta, ini, posini, posfin, saldoo, numi, file) {
    // console.log("impresion libreta main")
    // console.log(datos)
    var inif = parseInt(datos[0][2]);
    var nfront = parseInt(datos[0][0]);
    var inid = parseInt(datos[0][3]);
    var ndors = parseInt(datos[0][1]);

    posac = 0;
    bandera = 0;
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [240, 300]
    };
    var doc = new jsPDF(opciones);
    doc.setFontSize(9);
    //var i = 0;
    var i = posini;
    var tiptr;
    //var saldo = parseFloat(datos[0][4])*porsaldo;
    var saldo = saldoo;
    var monto = parseFloat(0);
    while (i < (datos[1].length)) {
        // console.log(datos[1][i]);
        doc.setFontSize(9);
        num = parseInt(datos[1][i]['numlinea']);
        pos = (6 * (parseInt(num) - parseInt(resta))) + parseInt(ini);
        // doc.text(10, pos, "" + datos[1][i]['correlativo']);
        fecha = conviertefecha(datos[1][i]['dfecope']);
        // console.log(fecha);
        doc.text(10, pos, "" + fecha);
        // doc.text(36, pos, "" + datos[1][i]['cnumdoc']);
        monto = parseFloat(datos[1][i]['monto']);
        tiptr = datos[1][i]['ctipope'];
        //*** */
        if (tiptr == "R") {
            saldo = parseFloat(saldo) - parseFloat(monto);
            transaccion = "Retiro/Debito";
            doc.text(30, pos, transaccion);
            doc.text(64, pos, "" + pad(currency(monto)));
            doc.text(88, pos, "");
        }
        if (tiptr == "D") {
            saldo = parseFloat(saldo) + parseFloat(monto);
            transaccion = "Deposito/Intereses";

            doc.text(30, pos, transaccion);
            doc.text(64, pos, "");
            doc.text(88, pos, "" + pad(currency(monto)));
        }
        doc.text(113, pos, "" + pad(currency(saldo)));
        if (num >= posfin) {
            // console.log("aki papu");
            posac = i + 1;
            bandera = (num >= (nfront + ndors)) ? 0 : 1;
            break;
        }
        i++;
    }
    if (numi > nfront) {
        // console.log("aki papu 2");
        Swal.fire({
            title: 'Entrando en la reversa de libreta',
            showDenyButton: false,
            confirmButtonText: 'Listo Imprimir',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                //
                doc.autoPrint();
                window.open(doc.output('bloburl'));

            } else if (result.isDenied) {
                Swal.fire('Uff', 'Cancelado', 'success')
            }
        })
    }
    else {
        // console.log("aki papu 3");
        doc.autoPrint();
        window.open(doc.output('bloburl'));
    }
    if (bandera == 1) {
        // console.log("aki papu 4");
        //SE EJECUTA RECURSIVAMENTE
        window[file](datos, nfront, inid, posac, nfront + ndors, saldo, nfront + 1, file);
    }
}

function impresion_libreta_copeadif(datos, resta, ini, posini, posfin, saldoo, numi, file) {
    // console.log("impresion libreta main")
    // console.log(datos)\
    var inif = parseInt(datos[0][2]);
    var nfront = parseInt(datos[0][0]);
    var inid = parseInt(datos[0][3]);
    var ndors = parseInt(datos[0][1]);

    posac = -5;
    bandera = 0;
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [240, 300]
    };
    var doc = new jsPDF(opciones);
    doc.setFontSize(9);
    //var i = 0;
    var i = posini;
    var tiptr;
    //var saldo = parseFloat(datos[0][4])*porsaldo;
    var saldo = saldoo;
    var monto = parseFloat(0);
    while (i < (datos[1].length)) {
        // console.log(datos[1][i]);
        doc.setFontSize(9);
        num = parseInt(datos[1][i]['numlinea']);
        pos = (6 * (parseInt(num) - parseInt(resta))) + parseInt(ini);
        // doc.text(10, pos, "" + datos[1][i]['correlativo']);
        fecha = conviertefecha(datos[1][i]['dfecope']);
        // console.log(fecha);
        doc.text(53, pos, "" + fecha);
        // doc.text(36, pos, "" + datos[1][i]['cnumdoc']);
        monto = parseFloat(datos[1][i]['monto']);
        tiptr = datos[1][i]['ctipope'];
        //*** */
        if (tiptr == "R") {
            saldo = parseFloat(saldo) - parseFloat(monto);
            transaccion = "Retiro/Debito";
            doc.text(71, pos, transaccion);
            doc.text(133, pos, "" + pad(currency(monto)));
            doc.text(109, pos, "");
        }
        if (tiptr == "D") {
            saldo = parseFloat(saldo) + parseFloat(monto);
            transaccion = "Deposito/Intereses";

            doc.text(71, pos, transaccion);
            doc.text(133, pos, "");
            doc.text(109, pos, "" + pad(currency(monto)));
        }
        doc.text(160, pos, "" + pad(currency(saldo)));
        if (num >= posfin) {
            posac = i + 1;
            bandera = (num >= (nfront + ndors)) ? 0 : 1;
            break;
        }
        i++;
    }
    if (numi > nfront) {
        // console.log("aki papu 2");
        Swal.fire({
            title: 'Entrando en la reversa de libreta',
            showDenyButton: false,
            confirmButtonText: 'Listo Imprimir',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                //
                doc.autoPrint();
                window.open(doc.output('bloburl'));

            } else if (result.isDenied) {
                Swal.fire('Uff', 'Cancelado', 'success')
            }
        })
    }
    else {
        // console.log("aki papu 3");
        doc.autoPrint();
        window.open(doc.output('bloburl'));
    }
    if (bandera == 1) {
        // console.log("aki papu 4");
        //SE EJECUTA RECURSIVAMENTE
        window[file](datos, nfront, inid, posac, nfront + ndors, saldo, nfront + 1, file);
    }

}


//end libretas

// CIACREHO
function impresion_recibo_dep_ret_ciacreho(datos) {
    alert("Impresion de recibo *3");
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [216, 300]
    };
    var doc = new jsPDF(opciones);
    var fuente = 'Courier';
    doc.setFont(fuente);

    var i = 1;
    var ini = 40;
    var margenizquierdo = 10;
    while (i < 2) {
        doc.setFontSize(13);
        doc.setFontStyle('bold');
        doc.text(margenizquierdo, ini, 'Cuenta de aportación: ' + datos[2]);
        doc.text(115, ini, 'Fecha doc: ' + datos[4]);
        //doc.text(180, ini, '' + datos[5]);

        ini = ini + 7;
        doc.text(margenizquierdo, ini, 'Cliente: ' + datos[7]);

        ini = ini + 7;
        doc.text(margenizquierdo, ini, 'Operacion y No. Docto: ' + datos[5]);
        doc.text(150, ini, 'Monto: Q ' + datos[3]);

        ini = ini + 7;
        doc.text(60, ini, 'Operación: ' + datos[6]);

        doc.setFontSize(11);
        ini = ini + 7;
        doc.text(margenizquierdo, ini, 'Operador: ' + datos[8] + ' ' + datos[9]);
        ini = ini + 5;
        doc.text(margenizquierdo, ini, 'Fecha operación: ' + datos[10]);

        ini = ini + 40;
        i++;
    }

    doc.autoPrint();
    window.open(doc.output('bloburl'))
}
// COOPRODE
function impresion_recibo_dep_ret_cooprode(datos) {
    alert("Impresion de recibo *4");
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [240, 300]
    };
    var doc = new jsPDF(opciones);
    doc.setFontSize(12);
    var i = 1;
    var ini = 43;
    while (i < 2) {
        doc.text(66, ini, datos[14]);
        doc.text(80, ini, datos[13]);
        doc.text(92, ini, datos[12]);

        ini = ini + 8;
        doc.text(23, ini, datos[7]);

        ini = ini + 25;
        doc.text(12, ini, datos[6]);
        doc.text(167, ini, datos[3]);

        ini = ini + 35;
        doc.text(167, ini, datos[3]);

        ini = ini + 6;
        doc.text(36, ini, datos[11]);

        ini = ini + 40;
        i++;
    }
    doc.autoPrint();
    window.open(doc.output('bloburl'))
}
// CORPOCREDIT aportaciones
function impresion_recibo_dep_ret_corpocredit(datos) {
    alert("Impresion de recibo");
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [216, 300],

    };

    //  console.log(datos);
    //  return;
    var doc = new jsPDF(opciones);
    var fechaActual = new Date();
    // Obtener la fecha y hora 
    var fecha = fechaActual.toLocaleDateString();
    var hora = fechaActual.toLocaleTimeString();


    var i = 1;
    var ini = 50;
    var margenizquierdo = 20;
    while (i < 2) {
        doc.setFontSize(12);

        decimal = datos[3].toString();
        if (decimal.indexOf('.') != -1) {
            var decimal = decimal.split('.')[1];
        }
        var Tot_efectivo = 0;
        var apr = 0;

        doc.text(margenizquierdo, ini - 6, "Fecha: " + datos[10]);
        doc.text(margenizquierdo, ini, 'CORPOCREDIT R.L. /PRODUCTO: ' + datos[17]);
        ini += 6;
        doc.text(margenizquierdo, ini, 'No. de Cuenta: ' + datos[2]);
        doc.text(margenizquierdo, ini + 6, 'No. de Boleta de Transaccion: ' + datos[5]);
        ini += 6;
        var apr = parseFloat(datos[3]);
        doc.text(margenizquierdo, ini + 6, 'Letras: ' + datos[19]);
        doc.text(margenizquierdo + 107, ini + 12, "Aportacion:                Q." + datos[3]);
        doc.text(margenizquierdo + 101, ini + 18, ((datos[16] > 0) ? "     Cuota de ingreso:      Q. " + parseFloat(datos[16]).toFixed(2) : ""));

        var total_format = parseFloat(datos[18]).toFixed(2);
        doc.text(margenizquierdo + 107, ini + 24, "Efectivo:                     Q." + total_format);

        doc.text(margenizquierdo + 20, ini + 30, 'Asociado / A:' + datos[7]);
        doc.text(margenizquierdo + 140, ini + 30, "C.I. " + datos[21]);
        doc.text(margenizquierdo + 22, ini + 36, 'Operación: ' + ((datos[6] === "D") ? "DEPOSITO A CUENTA " : ((datos[6] === "R") ? "RETIRO A CUENTA " : datos[6])));
        doc.text(margenizquierdo + 50, ini + 42, 'Usuario: ' + datos[20]);

        ini = ini + 40;
        i++;
    }

    doc.autoPrint();
    window.open(doc.output('bloburl'))
}

//copibelen
function impresion_recibo_dep_ret_copibelen(datos) {
    alert("Impresion de recibo");
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [216, 300],

    };

    //  console.log(datos);
    //  return;
    var doc = new jsPDF(opciones);
    var fechaActual = new Date();
    // Obtener la fecha y hora 
    var fecha = fechaActual.toLocaleDateString();
    var hora = fechaActual.toLocaleTimeString();
    var i = 1;
    var ini = 30;
    var margenizquierdo = 20;
    while (i < 2) {
        doc.setFontSize(10);

        decimal = datos[3].toString();
        if (decimal.indexOf('.') != -1) {
            var decimal = decimal.split('.')[1];
        }
        var espacios = '                      ';
        const datoview0 = datos[16] ? parseFloat(datos[16]).toFixed(2) : '          -';
        const datoview = datos[6].charAt(0) === 'D' ? datos[3] : '';
        const datoview2 = datos[6].charAt(0) === 'R' ? datos[3] : '';
        const datoview3 = datos[6].charAt(0) === 'D' ? ' ' : '-';
        const datoview4 = datos[6].charAt(0) === 'R' ? ' ' : '-';


        doc.text(margenizquierdo + 20, ini + 5, 'NOMBRE DEL ASOCIADO      ' + datos[7] + '    ' + datos[4]);
        doc.text(margenizquierdo, ini + 10, 'ASOCIADO No. ' + datos[15] + "           " + datos[6]);
        doc.text(margenizquierdo + 60, ini + 15, ' DESCRIPCION');

        //primwera columna 
        doc.text(margenizquierdo, ini + 20, 'INSCRIPCION' + espacios + espacios + datoview);
        doc.text(margenizquierdo, ini + 25, 'APORTACION' + espacios + espacios + datoview0);
        doc.text(margenizquierdo, ini + 30, 'RETIRO DE APORTACION' + espacios + datoview2);
        doc.text(margenizquierdo, ini + 35, 'AHORRO CORRIENTE' + '            ' + espacios);
        doc.text(margenizquierdo, ini + 40, 'RETIRO DE AHORRO FIJO' + '     ');
        doc.text(margenizquierdo, ini + 45, 'INGRESOS VARIOS' + '                   ');
        doc.text(margenizquierdo, ini + 55, 'TOTAL EN LETRAS; ' + datos[11]);

        //SEGUNDA COLUMNA  Q and -
        doc.text(margenizquierdo + 8, ini + 20, espacios + espacios + 'Q' + espacios + datoview3 + espacios + espacios + espacios + 'Q' + espacios + '-');
        doc.text(margenizquierdo + 8, ini + 25, espacios + espacios + 'Q' + espacios + espacios + espacios + espacios + ' Q' + espacios + '-');
        doc.text(margenizquierdo + 8, ini + 30, espacios + espacios + 'Q' + espacios + datoview4 + espacios + espacios + espacios + 'Q' + espacios + '-');
        doc.text(margenizquierdo + 8, ini + 35, espacios + espacios + 'Q' + espacios + '-' + espacios + espacios + espacios + 'Q' + espacios + '-');
        doc.text(margenizquierdo + 8, ini + 40, espacios + espacios + 'Q' + espacios + '-' + espacios + espacios + espacios + 'Q' + espacios + '-');

        //TERCERA COLUMNA 
        doc.text(margenizquierdo + 90, ini + 20, 'CAPITAL');
        doc.text(margenizquierdo + 90, ini + 25, 'INTERES');
        doc.text(margenizquierdo + 90, ini + 30, 'MORA');
        doc.text(margenizquierdo + 90, ini + 35, 'DESEMBOLSO');
        doc.text(margenizquierdo + 90, ini + 40, 'SALDO ACTUAL');

        ini = ini + 40;
        i++;
    }

    doc.autoPrint();
    window.open(doc.output('bloburl'))
}


function impresion_recibo_dep_ret_adg(datos) {
    alert("Impresion de recibo");

    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [216, 300]
    };
    //   console.log(datos);
    //   return;

    var doc = new jsPDF(opciones);
    var fechaActual = new Date();
    // Obtener la fecha y hora 
    var fecha = fechaActual.toLocaleDateString();
    var hora = fechaActual.toLocaleTimeString();
    var i = 1;
    var ini = 30;
    var cantidad = 0;
    var aportacion = 0;
    var margenizquierdo = 15;
    while (i < 2) {
        doc.setFontSize(10);

        decimal = datos[3].toString();
        if (decimal.indexOf('.') != -1) {
            var decimal = decimal.split('.')[1];
        }
        if (datos[3]) {
            cantidad = parseFloat(datos[3].replace(',', ''));
        } if (datos[16]) {
            aportacion = parseFloat(datos[16].replace(',', ''));
        }
        // Calcular la suma
        var total = cantidad + aportacion;
        doc.text(margenizquierdo + 150, ini, total.toString());//efectivo
        doc.text(margenizquierdo + 15, ini - 5, 'ADG  ' + fecha);//no de boleta
        doc.text(margenizquierdo + 8, ini + 3, 'ASOCIACION DE DESARROLLO GUATEMALTECO "ADG" '); //recibo de 
        doc.text(margenizquierdo + 8, ini + 8, ' 2a. Calle 01-0310 Zona 4 Tecpan Guatemmala, Chimaltenango'); //DIRECCION
        doc.text(margenizquierdo + 15, ini + 15, datos[11] + ' CON ' + decimal + '/100');//en letras
        //ini += 6;
        doc.text(margenizquierdo, ini + 30, 'No. de Boleta de Transaccion: ' + datos[5]);
        doc.text(margenizquierdo, ini + 35, 'Asociado / A:  ' + datos[7]);
        doc.text(margenizquierdo, ini + 40, 'Operación: ' + (datos[6] === "D" ? "DEPOSITO A CUENTA AHORRO" : (datos[6] === "R") ? "RETIRO A CUENTA AHORRO" : datos[6]));
        doc.text(margenizquierdo, ini + 45, 'Cantidad:: ');
        doc.text(margenizquierdo + 168, ini + 45, 'Q.' + datos[3]);
        doc.text(margenizquierdo, ini + 50, 'Aportacion: ');
        doc.text(margenizquierdo + 168, ini + 50, 'Q.' + datos[16]);
        doc.text(margenizquierdo, ini + 60, 'Operador: ' + datos[8] + ' ' + datos[9]);

        doc.text(margenizquierdo + 168, ini + 97, total.toString());
        ini = ini + 40;
        i++;

    }

    doc.autoPrint();
    window.open(doc.output('bloburl'))
}


function impresion_recibo_dep_ret_credysa(datos) {
    alert("Impresion de recibo");
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [216, 300]
    };
    //   console.log(datos);
    //   return;

    var doc = new jsPDF(opciones);
    var fechaActual = new Date();
    // Obtener la fecha y hora 
    var fecha = fechaActual.toLocaleDateString();
    var hora = fechaActual.toLocaleTimeString();


    var i = 1;
    var ini = 45;
    var margenizquierdo = 20;
    while (i < 2) {
        doc.setFontSize(12);

        decimal = datos[3].toString();
        if (decimal.indexOf('.') != -1) {
            var decimal = decimal.split('.')[1];
        }

        doc.text(margenizquierdo, ini - 6, 'Fecha ' + datos[4] + '  ' + hora);
        doc.text(margenizquierdo, ini, 'CREDYSA S.A. /PRODUCTO: ' + datos[16]);
        doc.text(margenizquierdo, ini + 6, 'No. de Cuenta: ' + datos[2]);
        doc.text(margenizquierdo, ini + 12, 'No. de Boleta de Transaccion: ' + datos[5]);

        doc.text(margenizquierdo + 130, ini + 15, 'Efectivo: Q. ' + datos[3]);
        doc.text(margenizquierdo, ini + 19, 'Letras: ' + datos[11]);

       // doc.text(margenizquierdo, ini + 28, 'C.I. ' + datos[18]);

        doc.text(margenizquierdo + 33, ini + 30, 'Asociado / A:  ' + datos[7]);
        doc.text(margenizquierdo + 33, ini + 35, 'Operación: ' + (datos[6] === "D" ? "DEPOSITO A CUENTA" : (datos[6] === "R") ? "RETIRO A CUENTA " : datos[6]));
        doc.text(margenizquierdo + 50, ini + 40, 'Usuario: ' +  datos[8] + ' ' + datos[9]);

        ini = ini + 40;
        i++;

    }

    doc.autoPrint();
    window.open(doc.output('bloburl'))
}

//#endregion

//#region FORMATO PARA CERTIFICADO INICIAL
//MAIN
function impresion_certificado_main(datos) {
    alert("Impresion de certificado de aportación");
    //configuraciones generales del tamaño del reporte
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [240, 300]
    };
    //configuraciones generales
    var doc = new jsPDF(opciones);
    doc.setFontSize(12);
    doc.text(20, 20, 'Certificacion a: ' + datos[1]);
    //ciclo for para recorrer a los beneficiarios
    var beneficiarios = "";
    const max_caracteres = 10;
    for (let i = 1; i < datos[0].length; i++) {
        beneficiarios = beneficiarios + datos[0][i]['nombre'];
        if (i !== ((datos[0].length) - 1)) {
            beneficiarios = beneficiarios + ", ";
        }
    }
    //se redimensiona, es como un multicel
    var splitTitle = doc.splitTextToSize(beneficiarios, 180);
    doc.text(20, 30, splitTitle);
    //la fecha se divide
    var array_fechasol = datos[2].split("-")
    var ano = array_fechasol[2];
    //se transforma el mes en letras
    var mes = array_fechasol[1];
    var mes_convertido = convertir_mes(mes);
    var dia = array_fechasol[0];
    //se muestra la fecha
    doc.text(20, 40, 'Fecha: ' + dia + '       ' + mes_convertido + '      ' + ano);
    //se muestra el codigo del certificado
    doc.text(20, 50, datos[3]);
    //se muestra el monto en numeros
    doc.text(20, 60, datos[4]);
    //se muestra el monto en letras
    var splitTitle1 = doc.splitTextToSize(datos[5], 180);
    doc.text(20, 70, splitTitle1);
    //se muestra si es impresion o reimpresion
    doc.text(20, 80, datos[6]);

    doc.autoPrint();
    window.open(doc.output('bloburl'))
}


// CORPOCREDIT
function impresion_certificado_corpocredit(datos) {
    // console.log(datos);
    //  return; 
    alert("Impresion de certificado de aportación");
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [240, 300]
    };
    var doc = new jsPDF(opciones);
    doc.setFontSize(12);
    var codcuenta = datos[9];
    var norecibo = datos[8];
    var controlinterno = datos[10];

    doc.text(90, 66, ' ' + codcuenta); // CODCUENTA
    doc.text(92, 72, ' ' + datos[1]); // NOMBRE
    doc.text(55, 79, 'C.I. ' + controlinterno);//COD INTERNO
    doc.text(132, 79, norecibo);// RECIBO CAJA
    var beneficiarios = "";
    const max_caracteres = 10;
    for (let i = 1; i < datos[0].length; i++) {
        beneficiarios = beneficiarios + datos[0][i]['nombre'];
        if (i !== ((datos[0].length) - 1)) {
            beneficiarios = beneficiarios + ", ";
        }
    }
    var splitTitle1 = doc.splitTextToSize(datos[5], 180);
    doc.text(115, 87, splitTitle1);//cantidad en letras
    var splitTitle = doc.splitTextToSize(beneficiarios, 180);

    doc.text(23, 92, datos[4]);//monto
    var array_fechasol = datos[2].split("-")
    var ano = array_fechasol[2];
    var mes = array_fechasol[1];
    var mes_convertido = convertir_mes(mes);
    var dia = array_fechasol[0];

    var message = datos[6] === "R" ? " R  (Reimpreso)" : "I (Original)";
    // doc.text(160, 136, message);

    doc.text(20, 149, splitTitle); //beneficiario
    doc.text(60, 156, ' Nebaj Quiché, ' + dia + '       ' + mes_convertido + '      ' + ano); //Fecha

    doc.autoPrint();
    window.open(doc.output('bloburl'))
}

// ADIF
function impresion_certificado_adif(datos) {
    // console.log(datos);
    //  return; 
    alert("Impresion de certificado de aportación");
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [240, 300]
    };
    var doc = new jsPDF(opciones);
    doc.setFontSize(12);
    doc.text(170, 25, datos[3]);//cod
    doc.text(25, 88, ' ' + datos[1]); // NOMBRE
    doc.text(75, 95, ' ' + datos[7]); // DPI

    var beneficiarios = "";
    const max_caracteres = 10;
    for (let i = 1; i < datos[0].length; i++) {
        beneficiarios = beneficiarios + datos[0][i]['nombre'];
        if (i !== ((datos[0].length) - 1)) {
            beneficiarios = beneficiarios + ", ";
        }
    }
    var splitTitle1 = doc.splitTextToSize(datos[5], 180);//EN LETRAS
    doc.text(115, 115, splitTitle1);
    var splitTitle = doc.splitTextToSize(beneficiarios, 180);
    doc.text(25, 120, '(Q.' + datos[4] + ')');//monto

    var array_fechasol = datos[2].split("-")
    var ano = array_fechasol[2];
    var mes = array_fechasol[1];
    var mes_convertido = convertir_mes(mes);
    var dia = array_fechasol[0];
    var message = datos[6] === "R" ? " R  (Reimpreso)" : "I (Original)";

    //doc.text(160, 136, message);

    // doc.text(20, 145, splitTitle);
    // doc.text(60, 154, datos[2]); //Fecha
    doc.autoPrint();
    window.open(doc.output('bloburl'))
}

function impresion_certificado_coditoto(datos) {
    alert("Impresion de certificado de aportación");
    //configuraciones generales del tamaño del reporte
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [240, 300]
    };
    //configuraciones generales
    var doc = new jsPDF(opciones);
    doc.setFontSize(12);
    var margenIzquierdo = 80;

    //nombre 
    doc.text(margenIzquierdo, 100, ' ' + datos[1]);

    //ciclo for para recorrer a los beneficiarios
    var beneficiarios = "";
    const max_caracteres = 10;
    for (let i = 1; i < datos[0].length; i++) {
        beneficiarios = beneficiarios + datos[0][i]['nombre'];
        if (i !== ((datos[0].length) - 1)) {
            beneficiarios = beneficiarios + ", ";
        }
    }
    //se redimensiona, es como un multicel
    var splitTitle = doc.splitTextToSize(beneficiarios, 180);
    doc.text(20, 205, splitTitle);

    //la fecha se divide
    var array_fechasol = datos[2].split("-")
    var ano = array_fechasol[2];

    var mes = array_fechasol[1];
    var mes_convertido = convertir_mes(mes);
    var dia = array_fechasol[0];

    var espacios = '                      ';

    doc.text(118, 235, dia + espacios + '     ' + mes_convertido + '      ' + espacios + ano);


    // //se muestra el codigo del certificado
    // doc.text(20, 150, datos[3]);
    // //se muestra el monto en numeros
    // doc.text(20, 160, datos[4]);
    // //se muestra el monto en letras
    // var splitTitle1 = doc.splitTextToSize(datos[5], 180);
    // doc.text(20, 170, splitTitle1);
    // //se muestra si es impresion o reimpresion
    // doc.text(20, 180, datos[6]);

    doc.autoPrint();
    window.open(doc.output('bloburl'))
}


//#endregion

//#region FUNCIONES PARA FORMATO DE LIQUIDACION DE CERTIFICADO
// MAIN
//#region

//#region FUNCIONES PARA FORMATO DE COMPROBANTE DE CERTIFICADO
// MAIN
//#region 