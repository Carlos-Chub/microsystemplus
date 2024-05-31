//#region FUNCIONES PARA FORMATOS DE LIBRETA

//FUNCION PARA CONVERTIR MES A LETRAS
function convertir_mes(numeroMes) {
    var meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    var mes = "";
    if (!isNaN(numeroMes) && numeroMes >= 1 && numeroMes <= 12) {
        mes = meses[numeroMes - 1];
    }
    return mes;
}

// MAIN
function impresion_libreta_main(datos, resta, ini, posini, posfin, saldoo, numi, file) {
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
        doc.text(18, pos, "" + fecha);
        // doc.text(36, pos, "" + datos[1][i]['cnumdoc']);
        monto = parseFloat(datos[1][i]['monto']);
        tiptr = datos[1][i]['ctipope'];
        //*** */
        if (tiptr == "R") {
            saldo = parseFloat(saldo) - parseFloat(monto);
            transaccion = "Retiro/Debito";
            doc.text(36, pos, transaccion);
            doc.text(98, pos, "" + pad(currency(monto)));
            doc.text(74, pos, "");
        }
        if (tiptr == "D") {
            saldo = parseFloat(saldo) + parseFloat(monto);
            transaccion = "Deposito/Intereses";

            doc.text(36, pos, transaccion);
            doc.text(98, pos, "");
            doc.text(74, pos, "" + pad(currency(monto)));
        }
        doc.text(125, pos, "" + pad(currency(saldo)));
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

//libreta de copefunete
function impresion_libreta_copefuente(datos, resta, ini, posini, posfin, saldoo, numi, file) {
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
    var posvert;
    posvert = ini + 3;

    //var saldo = parseFloat(datos[0][4])*porsaldo;
    var saldo = saldoo;
    var monto = parseFloat(0);
    while (i < (datos[1].length)) {
        //console.log(datos[1][i]);
        //return;
        //posvert += 5; 

        doc.setFontSize(8);
        num = parseInt(datos[1][i]['numlinea']);
        pos = (6 * (parseInt(num) - parseInt(resta))) + parseInt(posvert);
        fecha = conviertefecha(datos[1][i]['dfecope']);
        doc.text(8, pos, "" + fecha);
        monto = parseFloat(datos[1][i]['monto']);
        tiptr = datos[1][i]['ctipope'];
        if (tiptr == "R") {
            saldo = parseFloat(saldo) - parseFloat(monto);
            transaccion = "Retiro/Debito";
            doc.text(28, pos, transaccion);
            doc.text(83, pos, "" + pad(currency(monto)));
            doc.text(58, pos, "");
        }
        if (tiptr == "D") {
            saldo = parseFloat(saldo) + parseFloat(monto);
            transaccion = "Deposito/Intereses";

            doc.text(28, pos, transaccion);
            doc.text(83, pos, "");
            doc.text(58, pos, "" + pad(currency(monto)));
        }
        doc.text(108, pos, "" + pad(currency(saldo)));


        if (num >= posfin) {
            posac = i + 1;
            bandera = (num >= (nfront + ndors)) ? 0 : 1;
            posvert += 5;
            break;
        }
        i++;

        // aqui se termina y aunemnta el bucle  

    }
    if (numi > nfront) {
        Swal.fire({
            title: 'Entrando en la reversa de libreta',
            showDenyButton: false,
            confirmButtonText: 'Listo Imprimir',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
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
        window[file](datos, nfront, inid, posac, nfront + ndors, saldo, nfront + 1, file, posvert += 4);
        //  console.log('1 datos ' , datos , '2 nfont' , inid , posac , nfront , '3 ndors' , ndors , 'suma anteriores',nfront + ndors, 'saldo'  , saldo ,'nfont +1 ' , nfront +1 , 'file', file);

    }
}



function impresion_libreta_copibelen(datos, resta, ini, posini, posfin, saldoo, numi, file) {
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
    var posvert;
    posvert = ini + 9;

    //var saldo = parseFloat(datos[0][4])*porsaldo;
    var saldo = saldoo;
    var monto = parseFloat(0);
    while (i < (datos[1].length)) {
        //console.log(datos[1][i]);
        //return;
        //posvert += 5; 

        doc.setFontSize(8);
        num = parseInt(datos[1][i]['numlinea']);
        pos = (6 * (parseInt(num) - parseInt(resta))) + parseInt(posvert);
        fecha = conviertefecha(datos[1][i]['dfecope']);
        doc.text(8, pos, "" + fecha);
        monto = parseFloat(datos[1][i]['monto']);
        tiptr = datos[1][i]['ctipope'];
        if (tiptr == "R") {
            saldo = parseFloat(saldo) - parseFloat(monto);
            transaccion = "Retiro/Debito";
            doc.text(28, pos, transaccion);
            doc.text(58, pos, "" + pad(currency(monto)));
            doc.text(83, pos, "");
        }
        if (tiptr == "D") {
            saldo = parseFloat(saldo) + parseFloat(monto);
            transaccion = "Deposito/Intereses";

            doc.text(28, pos, transaccion);
            doc.text(58, pos, "");
            doc.text(83, pos, "" + pad(currency(monto)));
        }
        doc.text(108, pos, "" + pad(currency(saldo)));


        if (num >= posfin) {
            posac = i + 1;
            bandera = (num >= (nfront + ndors)) ? 0 : 1;
            posvert += 5;
            break;
        }
        i++;

        // aqui se termina y aunemnta el bucle  

    }
    if (numi > nfront) {
        Swal.fire({
            title: 'Entrando en la reversa de libreta',
            showDenyButton: false,
            confirmButtonText: 'Listo Imprimir',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
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
        window[file](datos, nfront, inid, posac, nfront + ndors, saldo, nfront + 1, file, posvert += 4);
        //  console.log('1 datos ' , datos , '2 nfont' , inid , posac , nfront , '3 ndors' , ndors , 'suma anteriores',nfront + ndors, 'saldo'  , saldo ,'nfont +1 ' , nfront +1 , 'file', file);

    }
}



// CIACREHO -> FORMAS
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

    //var i = 0;
    var i = posini;
    var tiptr;
    //var saldo = parseFloat(datos[0][4])*porsaldo;
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

// COOPRODE -> FORMAS
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

    //var i = 0;
    var i = posini;
    var tiptr;
    //var saldo = parseFloat(datos[0][4])*porsaldo;
    var saldo = saldoo;
    var monto = parseFloat(0);
    var pos = 74;
    while (i < (datos[1].length)) {
        //console.log(datos[1][i]['numlinea']+' - '+resta+' - '+ini);
        num = parseInt(datos[1][i]['numlinea']);
        // pos = (4 * (parseInt(num) - parseInt(resta))) + parseInt(ini);
        doc.text(10, pos, "" + datos[1][i]['correlativo']);
        fecha = conviertefecha(datos[1][i]['dfecope']);
        // console.log(fecha);
        doc.text(16, pos, "" + fecha);
        doc.text(46, pos, "" + datos[1][i]['cnumdoc']);
        monto = parseFloat(datos[1][i]['monto']);
        tiptr = datos[1][i]['ctipope'];
        //*** */
        if (tiptr == "R") {
            saldo = parseFloat(saldo) - parseFloat(monto);
            // transaccion = "Retiro/Debito";
            // doc.text(51, pos, transaccion);
            doc.text(70, pos, "");
            doc.text(70, pos, "" + pad(currency(monto)));
        }
        if (tiptr == "D") {
            saldo = parseFloat(saldo) + parseFloat(monto);
            // transaccion = "Deposito/Intereses";
            // doc.text(51, pos, transaccion);
            doc.text(97, pos, "" + pad(currency(monto)));
            doc.text(97, pos, "");
        }

        doc.text(126, pos, "" + pad(currency(saldo)));
        if (num >= posfin) {
            posac = i + 1;
            bandera = (num >= (nfront + ndors)) ? 0 : 1;
            break;
        }
        i++;
        pos += 5;
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

function impresion_libreta_corpocredit(datos, resta, ini, posini, posfin, saldoo, numi, file) {

    console.log(datos)
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
        fecha = conviertefecha(datos[1][i]['dfecope']);
        doc.text(10, pos, "" + fecha);
        // doc.text(36, pos, "" + datos[1][i]['cnumdoc']);
        monto = parseFloat(datos[1][i]['monto']);
        tiptr = datos[1][i]['ctipope'];
        if (tiptr == "R") {
            saldo = parseFloat(saldo) - parseFloat(monto);
            transaccion = "Retiro/Debito";
            //esto es numero de documento
            doc.text(33, pos, (datos[1][i]['cnumdoc']));
            doc.text(90, pos, "" + pad(currency(monto)));
            doc.text(66, pos, "");
        }
        if (tiptr == "D") {
            saldo = parseFloat(saldo) + parseFloat(monto);
            transaccion = "Deposito/Intereses";
            //esto es numero de documento
            doc.text(33, pos, (datos[1][i]['cnumdoc']));
            doc.text(90, pos, "");
            doc.text(66, pos, "" + pad(currency(monto)));
        }
        doc.text(117, pos, "" + pad(currency(saldo)));
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
        doc.text(15, pos, "" + fecha);
        // doc.text(36, pos, "" + datos[1][i]['cnumdoc']);
        monto = parseFloat(datos[1][i]['monto']);
        tiptr = datos[1][i]['ctipope'];
        //*** */
        if (tiptr == "R") {
            saldo = parseFloat(saldo) - parseFloat(monto);
            transaccion = "Retiro/Debito";
            doc.text(35, pos ,"");
            doc.text(69, pos, transaccion);
            doc.text(93, pos, "" + pad(currency(monto))); 
        }
        if (tiptr == "D") {
            saldo = parseFloat(saldo) + parseFloat(monto);
            transaccion = "Deposito/Intereses";
            doc.text(35, pos, transaccion);
            doc.text(69, pos, "");
            doc.text(93, pos, "" + pad(currency(monto)));
        }
        doc.text(118, pos, "" + pad(currency(saldo)));
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

//#endregion

//#region FUNCIONES PARA FORMATO DE RETIROS Y DEPOSITOS
// MAIN
function impresion_recibo_dep_ret_main(datos) {
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
        doc.text(margenizquierdo, ini, 'Cuenta de ahorro No. ' + datos[2]);
        doc.text(110, ini, 'Fecha doc: ' + datos[4]);
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
    var margenizquierdo = 10;
    while (i < 2) {
        doc.setFontSize(13);
        doc.setFontStyle('bold');
        doc.text(margenizquierdo, ini, 'Cuenta de ahorro No. ' + datos[2]);
        doc.text(120, ini, 'Fecha doc: ' + datos[4]);
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
// CIACREHO
function impresion_recibo_dep_ret_ciacreho(datos) {
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
    var margenizquierdo = 10;
    while (i < 2) {
        doc.setFontSize(13);
        doc.setFontStyle('bold');
        doc.text(margenizquierdo, ini, 'Cuenta de ahorro No. ' + datos[2]);
        doc.text(120, ini, 'Fecha doc: ' + datos[4]);
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
        doc.text(margenizquierdo + 50, ini, 'Operador: ' + datos[8] + ' ' + datos[9]);
        ini = ini + 5;
        doc.text(margenizquierdo + 50, ini, 'Fecha op: ' + datos[10]);

        ini = ini + 40;
        i++;
    }
    doc.autoPrint();
    window.open(doc.output('bloburl'))
}

// COOPRODE -> FORMAS
function impresion_recibo_dep_ret_cooprode(datos) {
    // console.log(datos)
    // return;
    alert("Impresion de recibo");
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [240, 300]
    };
    var doc = new jsPDF(opciones);
    doc.setFontSize(12);
    var i = 1;
    var ini = 27;

    if (datos[6][0] === "D") {
        ini = ini + 3;
        while (i < 2) {
            doc.text(76, ini - 5, datos[14]);//dia
            doc.text(90, ini - 5, datos[13]);//fechas
            doc.text(100, ini - 5, datos[12]);//fechas

            ini = ini + 8;
            doc.text(34, ini - 7, datos[7]);//name

            ini = ini + 25;
            doc.text(12, ini, datos[6]);
            doc.text(178, ini, datos[3]);

            ini = ini + 35;
            doc.text(178, ini, datos[3]);//total

            ini = ini + 6;
            doc.text(36, ini, datos[11]);

            ini = ini + 40;
            i++;
        }
        doc.autoPrint();
        window.open(doc.output('bloburl'))
    }

    if (datos[6][0] === "R") {
        while (i < 2) {
            // ini = ini + 1;
            doc.text(75, ini, datos[14]);//DIA
            doc.text(88, ini, datos[13]);//ME1
            doc.text(100, ini, datos[12]);//AÑO

            ini = ini + 35;
            doc.text(25, ini, datos[6]);//Concepto
            doc.text(158, ini, "Q. " + datos[3]);//Sub total 
            doc.text(190, ini + 40, "Q. " + datos[3]);//Total 

            doc.text(50, ini + 37, datos[11]);//letras
            doc.text(32, ini + 44, datos[7])
            doc.text(18, ini + 50, datos[15]);//dpi

            ini = ini + 40;
            i++;
        }
        doc.autoPrint();
        window.open(doc.output('bloburl'))
    }
}
// CREDIMARQ -> FORMAS
function impresion_recibo_dep_ret_credimarq(datos) {
    alert("Impresion de recibo");
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
        doc.text(margenizquierdo, ini, 'Cuenta de ahorro No. ' + datos[2]);
        doc.text(110, ini, 'Fecha doc: ' + datos[4]);
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


// CORPOCREDIT
function impresion_recibo_dep_ret_copefuente(datos) {
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
    var margenizquierdo = 20;
    while (i < 2) {
        doc.setFontSize(10);

        decimal = datos[3].toString();
        if (decimal.indexOf('.') != -1) {
            var decimal = decimal.split('.')[1];
        }
        var espacios = '                      ';

        doc.text(margenizquierdo + 20, ini + 5, 'NOMBRE DEL ASOCIADO      ' + datos[7] + '    ' + datos[4]);
        doc.text(margenizquierdo, ini + 10, 'ASOCIADO No. ' + datos[15] + "           " + datos[6]);
        doc.text(margenizquierdo + 60, ini + 15, ' DESCRIPCION');

        const datoview = datos[6].charAt(0) === 'D' ? datos[3] : '';
        const datoview2 = datos[6].charAt(0) === 'R' ? datos[3] : '';
        const datoview3 = datos[6].charAt(0) === 'D' ? ' ' : '-';
        const datoview4 = datos[6].charAt(0) === 'R' ? ' ' : '-';

        //primwera columna 
        doc.text(margenizquierdo, ini + 20, 'INSCRIPCION' + espacios + '      ');
        doc.text(margenizquierdo, ini + 25, 'APORTACION' + espacios + '      ');
        doc.text(margenizquierdo, ini + 30, 'AHORRO CORRIENTE' + '            ' + espacios + datoview);
        doc.text(margenizquierdo, ini + 35, 'RETIRO DE AHORRO FIJO' + '     ' + espacios + datoview2);
        doc.text(margenizquierdo, ini + 40, 'INGRESOS VARIOS' + '                   ');
        doc.text(margenizquierdo, ini + 50, 'TOTAL EN LETRAS' + datos[11]);

        //SEGUNDA COLUMNA  Q and -
        doc.text(margenizquierdo + 8, ini + 20, espacios + espacios + 'Q' + espacios + '-' + espacios + espacios + espacios + 'Q' + espacios + '-');
        doc.text(margenizquierdo + 8, ini + 25, espacios + espacios + 'Q' + espacios + '-' + espacios + espacios + espacios + 'Q' + espacios + '-');
        doc.text(margenizquierdo + 8, ini + 30, espacios + espacios + 'Q' + espacios + datoview3 + espacios + espacios + espacios + 'Q' + espacios + '-');
        doc.text(margenizquierdo + 8, ini + 35, espacios + espacios + 'Q' + espacios + datoview4 + espacios + espacios + espacios + 'Q' + espacios + '-');
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


function impresion_recibo_dep_ret_corpocredit(datos) {
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
    var ini = 50;
    var margenizquierdo = 20;
    while (i < 2) {
        doc.setFontSize(12);

        decimal = datos[3].toString();
        if (decimal.indexOf('.') != -1) {
            var decimal = decimal.split('.')[1];
        }

        doc.text(margenizquierdo, ini - 6, 'Fecha ' + datos[4] + '  ' + hora);
        doc.text(margenizquierdo, ini, 'CORPOCREDIT R.L. /PRODUCTO: ' + datos[16]);
        doc.text(margenizquierdo, ini + 6, 'No. de Cuenta: ' + datos[2]);
        doc.text(margenizquierdo, ini + 12, 'No. de Boleta de Transaccion: ' + datos[5]);

        doc.text(margenizquierdo + 130, ini + 15, 'Efectivo: Q. ' + datos[3]);
        doc.text(margenizquierdo, ini + 19, 'Letras: ' + datos[11]);

        doc.text(margenizquierdo, ini + 28, 'C.I. ' + datos[18]);

        doc.text(margenizquierdo + 33, ini + 41, 'Asociado / A:  ' + datos[7]);
        doc.text(margenizquierdo + 33, ini + 47, 'Operación: ' + (datos[6] === "D" ? "DEPOSITO A CUENTA AHORRO" : (datos[6] === "R") ? "RETIRO A CUENTA AHORRO" : datos[6]));
        doc.text(margenizquierdo + 50, ini + 53, 'Usuario: ' + datos[17]);

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
        doc.text(margenizquierdo + 33, ini + 35, 'Operación: ' + (datos[6] === "D" ? "DEPOSITO A CUENTA AHORRO" : (datos[6] === "R") ? "RETIRO A CUENTA AHORRO" : datos[6]));
        doc.text(margenizquierdo + 50, ini + 40, 'Usuario: ' + datos[17]);

        ini = ini + 40;
        i++;

    }

    doc.autoPrint();
    window.open(doc.output('bloburl'))
}
//#endregion

//funcion copibelen 
function impresion_recibo_dep_ret_copibelen(datos) {
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
    var ini = 18;
    var margenizquierdo = 20;
    while (i < 2) {
        doc.setFontSize(12);

        decimal = datos[3].toString();
        if (decimal.indexOf('.') != -1) {
            var decimal = decimal.split('.')[1];
        }

        // doc.text(margenizquierdo+90, ini -6, 'Fecha: ' + fecha + '   ' +'Hora: ' + hora );
        // doc.text(margenizquierdo, ini, 'COPIBELEN' + datos[16]);
        ini += 6;
        doc.text(margenizquierdo + 32, ini, 'No. de Cuenta: ' + datos[2] + "    Fecha: " + datos[4] + ' ' + hora);
        doc.text(margenizquierdo + 32, ini + 6, 'No. de Boleta de Transaccion: ' + datos[5] + "           Efectivo: Q. " + datos[3]);
        doc.text(margenizquierdo + 32, ini + 12, 'Letras: ' + datos[11] + ' CON ' + decimal + '/100');
        doc.text(margenizquierdo + 15, ini + 24, 'Asociado / A: ' + datos[7]);
        doc.text(margenizquierdo + 15, ini + 30, 'Operación: ' + (datos[6] === "D" ? "DEPOSITO A CUENTA AHORRO" : (datos[6] === "R") ? "RETIRO A CUENTA AHORRO" : datos[6]));

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
    var margenizquierdo = 15;
    while (i < 2) {
        doc.setFontSize(10);

        decimal = datos[3].toString();
        if (decimal.indexOf('.') != -1) {
            var decimal = decimal.split('.')[1];
        }
        doc.text(margenizquierdo + 150, ini, datos[3]);//efectivo
        doc.text(margenizquierdo + 15, ini - 5, 'ADG  ' + fecha);//no de boleta
        doc.text(margenizquierdo + 8, ini + 3, 'ASOCIACION DE DESARROLLO GUATEMALTECO "ADG" '); //recibo de 
        doc.text(margenizquierdo + 8, ini + 8, ' 2a. Calle 01-0310 Zona 4 Tecpan Guatemala, Chimaltenango'); //DIRECCION
        doc.text(margenizquierdo + 15, ini + 15, datos[11] + ' CON ' + decimal + '/100');//en letras
        //ini += 6;
        doc.text(margenizquierdo, ini + 30, 'No. de Boleta de Transaccion: ' + datos[5]);

        doc.text(margenizquierdo, ini + 35, 'Asociado / A:      ' + datos[7]);
        doc.text(margenizquierdo, ini + 40, 'Operación: ' + (datos[6] === "D" ? "DEPOSITO A CUENTA AHORRO" : (datos[6] === "R") ? "RETIRO A CUENTA AHORRO" : datos[6]));
        doc.text(margenizquierdo, ini + 50, 'Operador: ' + datos[8] + ' ' + datos[9]);
        doc.text(margenizquierdo + 168, ini + 97, datos[3]);

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
        doc.text(margenizquierdo, ini - 10, 'Número de cuenta de ahorro ' + datos[2]);
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






//#endr

//#region FORMATO PARA CERTIFICADO INICIAL
//MAIN
function impresion_certificado_main(datos) {
    alert("Impresion de certificado");
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [240, 300]
    };
    var doc = new jsPDF(opciones);
    doc.setFontSize(12);

    doc.text(10, 20, 'Nombre ' + datos[0][1]);
    doc.text(97, 20, 'Cuenta ' + datos[0][2]);

    doc.text(10, 40, 'Direccion ' + datos[0][3]);
    doc.text(50, 40, 'Dpi ' + datos[0][4]);
    doc.text(90, 40, 'Tel ' + datos[0][5]);

    doc.text(10, 60, 'La cantidad de ' + datos[0][6]);

    doc.text(10, 80, 'Monto ' + datos[0][7]);
    doc.text(50, 80, 'plazo ' + datos[0][8]);
    doc.text(90, 80, 'Fec.deposito ' + datos[0][9]);

    doc.text(10, 100, 'Fec. vencimiento ' + datos[0][10]);
    doc.text(70, 100, 'tasa ' + datos[0][11]);

    doc.text(10, 120, 'Interes calcu ' + datos[0][12]);
    doc.text(70, 120, 'ipf ' + datos[0][13]);
    doc.text(90, 120, 'totalrecibir ' + datos[0][14]);

    var i = 1;
    var ini = 130;
    while (i < datos[1].length) {
        doc.text(10, ini, 'Nombres ' + datos[1][i]['nombre']);
        ini = ini + 10;

        doc.text(10, ini, 'Dpi. ' + datos[1][i]['dpi']);
        doc.text(90, ini, 'Nacimiento' + datos[1][i]['fecnac']);

        ini = ini + 10;
        doc.text(10, ini, 'Direccion: ' + datos[1][i]['direccion']);

        ini = ini + 10;
        doc.text(10, ini, 'Parentesco: ' + datos[1][i]['codparent']);

        ini = ini + 40;
        i++;
    }

    doc.text(40, 300, 'lugar y fecha ' + datos[0][15]);

    doc.autoPrint();
    window.open(doc.output('bloburl'))
}


//certificado Primavera
function impresion_certificado_primavera(datos) {
    // console.log(datos);
    // return;
    alert("Impresion de certificado");
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [240, 300]
    };
    var doc = new jsPDF(opciones);
    doc.setFontSize(12);
    doc.text(10, 30, 'Nombre del Asociado  :' + datos[0][1]);
    // doc.text(97, 28, 'Cuenta ' + datos[0][2]);
    doc.text(10, 40, 'Dpi ' + datos[0][4]);
    doc.text(10, 50, 'Direccion ' + datos[0][3]);
    doc.text(10, 60, 'Telefono: ' + datos[0][5]);
    var montoFormateado = Number(datos[0][7]).toLocaleString('es');
    doc.text(10, 70, 'Monto: ****' + montoFormateado + '****');
    doc.text(10, 80, 'La cantidad de ' + datos[0][6]);
    doc.text(10, 90, 'plazo ' + datos[0][8]);
    doc.text(10, 100, 'Fecha Inicio ' + datos[0][9]);
    doc.text(10, 110, 'Fecha Vencimiento ' + datos[0][10]);
    doc.text(10, 120, 'Tasa Interes ' + datos[0][11]);
    doc.text(70, 120, 'Interes calcu ' + datos[0][12].toFixed(2));
    doc.text(130, 120, 'IPF ' + datos[0][13].toFixed(2));
    //  doc.text(10, 130, 'totalrecibir ' + datos[0][14].toFixed(2));

    var i = 1;
    var ini = 140;
    while (i < datos[1].length) {
        doc.text(10, ini, 'BENEFICIARIO ' + datos[1][i]['nombre']);

        ini = ini + 10;

        i++;
    }
    doc.text(10, 160, 'lugar y fecha ' + datos[0][15]);



    doc.autoPrint();
    window.open(doc.output('bloburl'))
}
function impresion_certificado_coditoto(datos) {
    // console.log(datos);
    // return;
    alert("Impresion de certificado");
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [240, 300]
    };
    var doc = new jsPDF(opciones);
    doc.setFontSize(12);

    var i = 1;
    var ini = 182;
    while (i < datos[1].length) {
        doc.text(30, ini, '  ' + datos[1][i]['nombre']);
        ini = ini + 10;
        i++;
    }
    var array_fechasol = datos[0][15].split("-")
    var ano = array_fechasol[2];

    var mes = array_fechasol[1];
    var mes_convertido = convertir_mes(mes);
    var dia = array_fechasol[0];

    var espacios = '                      ';

    doc.text(50, 220, 'Cantón Xantún, Totonicapán      '+ ano + ' / ' +   mes_convertido  + ' / ' + dia);
    doc.autoPrint();
    window.open(doc.output('bloburl'))
}

//certificado copefuente
function impresion_certificado_copefuente(datos) {
    alert("Impresion de certificado");
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [240, 300]
    };

    // console.log(datos)
    // return;
    var doc = new jsPDF(opciones);
    doc.setFontSize(12);

    doc.setLineWidth(1.5); // Grosor de la línea 
    var margenIzquierdo = 15; // Margen izquierdo en mm
    var margenDerecho = 15; // Margen derecho en mm
    var anchoPagina = doc.internal.pageSize.width;
    var xInicio = margenIzquierdo;
    var xFinal = anchoPagina - margenDerecho;
    var vnum = parseFloat(datos[0][11]);
    var vdecimal = isNaN(vnum) ? '' : vnum.toFixed(2);
    var vint = parseFloat(datos[0][12]);
    var vinteres = isNaN(vint) ? '' : vint.toFixed(2);
    var vipf = parseFloat(datos[0][13]);
    var ipdf = isNaN(vipf) ? '' : vipf.toFixed(2);

    doc.line(xInicio, 55, xFinal, 55);
    doc.text(30, 52, 'CUENTA NO.' + datos[0][2]);
    doc.setFontSize(13);
    doc.text(100, 52, 'INFORMACION DE LA CUENTA');
    doc.setFontSize(12);
    //LINEA MARGEN 
    doc.text(15, 65, 'NO DE CUENTA ');
    doc.text(15, 70, 'ASOCIADO');
    doc.text(15, 75, 'TIPO DE CUENTA');
    doc.text(15, 80, 'TASA DE INTERES');
    doc.text(15, 85, 'FECHA APERTURA');
    doc.text(15, 90, 'BALANCE');
    //columna 2
    doc.text(80, 65, datos[0][2]);
    doc.text(80, 70, unescape(unescape(datos[0][1])));
    doc.text(80, 75, 'Ahorro a Plazo Fijo');
    doc.text(80, 80, vdecimal + ' %');
    doc.text(80, 85, datos[0][9]);
    doc.text(80, 90, 'Q.' + datos[0][7]);

    doc.setFontSize(13);
    doc.text(70, 102, 'INFORMACION DEL DEPOSITO A PLAZO FIJO ');
    doc.line(xInicio, 105, xFinal, 105);
    doc.setFontSize(12);


    doc.text(15, 116, 'Fecha inicial: ' + datos[0][9]);
    doc.text(15, 122, 'Capital inicial: ' + datos[0][7]);
    doc.text(15, 128, 'Capital inicial en letras: ' + datos[0][6]);
    doc.text(15, 134, 'Interes generado:' + vinteres);
    doc.text(75, 134, 'Ipf:' + ipdf);
    doc.text(115, 134, 'tasa ' + datos[0][11]);


    doc.setFontStyle('bold');
    doc.text(80, 140, 'DOCUMENTO NO NEGOCIABLE ');
    doc.setFontStyle('normal');

    doc.text(100, 115, 'Fecha de vencimiento: ' + datos[0][10]);
    doc.text(100, 122, 'Capital final: Q' + datos[0][11]);
    doc.setFontSize(13);
    doc.text(15, 148, 'BENEFICIARIO DE LA CUENTA ');
    doc.setFontSize(12);
    doc.line(xInicio, 150, xFinal, 150);
    var i = 1;
    var ini = 158;
    while (i < datos[1].length) {
        doc.text(15, ini, 'Nombres ' + datos[1][i]['nombre']);
        doc.text(90, ini, 'Dpi ' + datos[1][i]['dpi']);
        doc.text(140, ini, 'Parentesco ' + datos[1][i]['codparent']);
        ini = ini + 5;
        doc.text(15, ini, 'Direcciones ' + datos[1][i]['direccion']);

        ini = ini + 10;
        i++;
    }
    doc.autoPrint();
    window.open(doc.output('bloburl'))
}

function impresion_certificado_cooprode(datos) {
    alert("Impresión de certificado");
    var opciones = {
        orientation: 'p',
        unit: 'mm',
        format: [240, 300]
    };
    //req 1 ajuste de certificados
    var doc = new jsPDF(opciones);
    doc.setFontSize(14);
    var direcciondefaut = '';
    var fechaActual = obtenerFechaActual();

    doc.text(50, 35, ' ' + direcciondefaut);
    doc.text(93, 35, ' ' + fechaActual);
    doc.text(140, 47, ' ' + datos[0][1]);//titular
    doc.text(45, 47, ' ' + datos[0][2]);//cuentya
    doc.text(50, 64, ' ' + datos[0][6]);//cantidad en letras
    doc.text(100, 73, '' + datos[0][7]);//

    //regresa valores dia/mes/a;o
    var fechaDeposito = datos[0][9];
    var partesFechaDeposito = fechaDeposito.split('-');
    var FechaDeposito = partesFechaDeposito[2] + '-' + partesFechaDeposito[1] + '-' + partesFechaDeposito[0];
    doc.text(120, 84, '' + FechaDeposito);//fecha deposito

    //regresa valores dia/mes/a;o
    var dateorigin = datos[0][10];
    var partesFecha = dateorigin.split('-');
    var nuevaFecha = partesFecha[2] + '-' + partesFecha[1] + '-' + partesFecha[0];
    doc.text(50, 93, ' ' + nuevaFecha);//fecha vencimiento
    doc.text(175, 93, ' ' + Math.floor(datos[0][11]));//tasa

    var i = 1;
    var ini = 130;
    while (i < datos[1].length) {
        doc.text(10, ini, 'Nombres ' + datos[1][i]['nombre']);
        ini = ini + 10;
        doc.text(10, ini, 'Dpi. ' + datos[1][i]['dpi']);
        doc.text(90, ini, 'Nacimiento' + datos[1][i]['fecnac']);
        ini = ini + 10;
        doc.text(10, ini, 'Direccion: ' + datos[1][i]['direccion']);
        ini = ini + 10;
        doc.text(10, ini, 'Parentesco: ' + datos[1][i]['codparent']);
        ini = ini + 40;
        i++;
    }

    doc.autoPrint();
    window.open(doc.output('bloburl'))
}

//CORPOCREDIT
function impresion_certificado_corpocredit(datos) {
    alert("Impresion de certificado");
    var opciones = {
        orientation: "p",
        unit: "mm",
        format: [240, 300],
    };
    var doc = new jsPDF(opciones);
    doc.setFontSize(11);

    var oficina = datos[0][17];
    var recibo = datos[0][18];
    // doc.setFontStyle('bold');
    doc.text(159, 34, " " + datos[0][0]); //NO.
    doc.text(65, 34, 'C.I. ' + datos[0][16]); //C.I.
    doc.text(163, 90, " " + datos[0][2]); //CUENTA
    doc.text(38, 90, " " + datos[0][1]); //Nombre

    doc.text(39, 97, " " + datos[0][3]); //Direccion
    doc.text(120, 97, " " + datos[0][4]); //Dpi
    doc.text(163, 97, "  " + datos[0][5]); //Tel

    //conversion Cantidad en letras 
    let montoNumerico = parseFloat(datos[0][7]);
    let ptdecimal;

    //formatea decimales del monto y los muestra en  + total en letras
    if (!isNaN(montoNumerico)) {
        let monto = montoNumerico.toFixed(2);

        let partes = monto.split('.');
        let parteEntera = partes[0];
        ptdecimal = partes[1];

        let montoFormateado = ptdecimal + '/100';

        //  doc.text(140, 120, montoFormateado);
    } else {
        // console.error(' monto [][7]no es un número válido.');
    }

    doc.text(49, 104, " " + datos[0][6] + '  ' + ptdecimal + '/100'); // La cantidad en letras

    doc.text(34, 111, " " + datos[0][7]); // Monto
    doc.text(83, 111, " " + datos[0][8]); //plazo
    doc.text(155, 111, " " + datos[0][9]); //Fec.deposito
    //listo

    doc.text(58, 118, " " + datos[0][10]); //Fec. vencimiento

    var tasaFormateada = parseFloat(datos[0][11]).toFixed(2) + "%";
    doc.text(140, 118, " " + tasaFormateada); // tasa

    // Restablece el estilo de fuente a normal
    doc.setFontStyle("normal");

    doc.text(60, 126, " " + parseFloat(datos[0][12]).toFixed(2)); // Interes calcu
    doc.text(121, 126, " " + parseFloat(datos[0][13]).toFixed(2)); // ipf
    doc.text(167, 126, " " + parseFloat(datos[0][14]).toFixed(2)); //totalrecibir


    var i = 1;
    var ini = 125;

    while (i < datos[1].length) {
        var nombres = [];
        var dpis = [];
        var fechasNacimiento = [];
        var direcciones = [];
        var parentescos = [];

        // Recorre datos y agrupa 
        for (var j = i; j < i + 5 && j < datos[1].length; j++) {
            nombres.push(datos[1][j]["nombre"]);//nombres
            dpis.push(" " + datos[1][j]["dpi"]);//Dpi.
            direcciones.push(" " + datos[1][j]["direccion"]);//Direccion
            parentescos.push(" " + datos[1][j]["codparent"]); //Parentesco
        }
        fechasNacimiento.push(" " + datos[1][i]["fecnac"]); //Nacimiento

        doc.text(60, 136, " " + nombres.join(", ")); // Nombres
        doc.text(45, 143, " " + dpis.join(", ")); // DPI
        doc.text(160, 143, " " + fechasNacimiento.join(" ")); // Fechas de Nacimiento
        doc.text(50, 152, " " + direcciones.join(", ")); // Direcciones
        doc.text(50, 159, " " + parentescos.join(", ")); // Parentescos
        ini += 30;
        i += 5;
    }
    doc.text(150, 159, "Recibo: " + recibo);
    var lugaroficina = (oficina == "002") ? "Santa Cruz" : ((oficina == "003") ? "Cunén" : "Nebaj");

    doc.text(55, 244, lugaroficina + ", Quiché, " + datos[0][15]);  //lugar y fecha

    doc.autoPrint();
    window.open(doc.output("bloburl"));
}

//#endregion

//#region FUNCIONES PARA FORMATO DE LIQUIDACION DE CERTIFICADO
// MAIN
function impresion_liquidacion_certificado_main(datos) {
    alert('Insertar hoja de certificado')
    var opciones = {
        orientation: 'P',
        unit: 'mm',
        format: [240, 300]
    };
    var doc = new jsPDF(opciones);
    doc.setFontSize(12);
    var i = 1;
    var ini = 30;
    while (i < 4) {
        doc.text(10, ini, 'Certificado. ' + datos[2]);
        doc.text(97, ini, 'Fecha. ' + datos[3]);
        doc.text(145, ini, 'Codigo de cuenta: ' + datos[4]);

        ini = ini + 10;
        doc.text(10, ini, 'Cliente: ' + datos[5]);

        ini = ini + 10;
        doc.text(10, ini, 'Monto apertura: ' + datos[6]);
        doc.text(130, ini, 'Interes: Q ' + datos[7]);

        ini = ini + 12;
        doc.text(10, ini, 'ipf: ' + datos[8]);
        doc.text(40, ini, 'Monto en letras: ' + datos[9]);
        ini = ini + 10;
        doc.text(10, ini, 'Recibo: ' + datos[10]);
        ini = ini + 60;
        i++;
    }
    doc.autoPrint();
    window.open(doc.output('bloburl'))
}

function impresion_liquidacion_certificado_corpocredit(datos) {
    //'Datos', '1', $codcrt, $fechaliquidacion, $codaho, $nombrecli, $montoapr, $interescal, $ipfcalc, $texto_monto, $recibo,$dpi]
    alert('Insertar hoja de certificado')
    var opciones = {
        orientation: 'P',
        unit: 'mm',
        format: [240, 300]
    };
    var fecha = new Date(datos[3]);
    var dia = fecha.getDate() + 1;
    var mes = fecha.getMonth() + 1;
    var ano = fecha.getFullYear();

    var doc = new jsPDF(opciones);
    doc.setFontSize(9);
    var i = 1;
    var ini = 60;
    while (i < 2) {
        const cap_int = parseFloat(datos[6]) + parseFloat(datos[7]);
        const int_isr = parseFloat(datos[7]) - parseFloat(datos[8]);
        const tot_recibe = cap_int - parseFloat(datos[8]);
        doc.text(29, ini, 'Certificado. ' + datos[2]);//CERTIFICADO

        ini = ini + 4;
        doc.text(29, ini, 'Codigo de Cuenta: ' + datos[4]);//CODIGO DE CUENTAS

        ini = ini + 10;
        doc.text(73, ini, ' ' + dia);//DIA 
        doc.text(125, ini, ' ' + convertir_mes(mes));//MES
        doc.text(170, ini, ' ' + ano);//AÑO

        ini = ini + 12;
        doc.text(110, ini, ' ' + datos[9]);//MONTO EN LETRAS

        ini = ini + 10;
        doc.text(35, ini, ' ' + tot_recibe);//MONTO APERTURA/CAPITAL

        ini = ini + 12;
        doc.text(73, ini, ' ' + datos[6]);//MONTO APERTURA/CAPITAL 2

        ini = ini + 6;

        doc.text(73, ini, ' ' + datos[7]);//INTERES
        doc.text(108, ini, ' ' + cap_int);//CAPITAL + INTERES

        ini = ini + 6;

        doc.text(108, ini, ' ' + datos[8]);//IPF
        doc.text(172, ini, ' ' + parseFloat(int_isr).toFixed(2));//interes - isr

        ini = ini + 12;

        doc.text(108, ini, ' ' + tot_recibe);//capital e interes menos ipf

        ini = ini + 30;
        doc.text(44, ini, ' ' + datos[5]);//CLIENTE

        ini = ini + 12;
        doc.text(47, ini, ' ' + datos[11]);//DPI

        ini = ini + 12;
        doc.text(49, ini, ' ' + datos[10]);//RECIBO
        ini = ini + 60;
        i++;
    }
    doc.autoPrint();
    window.open(doc.output('bloburl'))
}

function impresion_liquidacion_certificado_primavera(datos) {
    alert('Insertar hoja de certificado')
    var opciones = {
        orientation: 'P',
        unit: 'mm',
        format: [240, 300]
    };
    var doc = new jsPDF(opciones);
    doc.setFontSize(12);
    var ini = 30;
    doc.text(10, ini, 'Certificado. ' + datos[2]);
    doc.text(97, ini, 'Fecha. ' + datos[3]);
    doc.text(145, ini, 'Codigo de cuenta: ' + datos[4]);

    ini = ini + 10;
    doc.text(10, ini, 'Cliente: ' + datos[5]);

    ini = ini + 10;
    doc.text(10, ini, 'Monto apertura: Q.' + datos[6]);
    doc.text(130, ini, 'Interes: Q ' + datos[7]);

    ini = ini + 12;
    doc.text(10, ini, 'ipf: ' + datos[8]);
    doc.text(40, ini, 'Monto en letras: ' + datos[9]);
    ini = ini + 10;
    doc.text(10, ini, 'Recibo: ' + datos[10]);

    doc.autoPrint();
    window.open(doc.output('bloburl'))
}
//#region

//#region FUNCIONES PARA FORMATO DE COMPROBANTE DE CERTIFICADO
// MAIN
function impresion_comprobante_certificado_main(datos) {
    var opciones = {
        orientation: 'P',
        unit: 'mm',
        format: [240, 300]
    };
    var doc = new jsPDF(opciones);
    doc.setFontSize(12);
    var i = 1;
    var ini = 30;
    while (i < 4) {
        doc.text(10, ini, 'Certificado. ' + datos[2]);
        doc.text(97, ini, 'Fecha. ' + datos[3]);
        doc.text(145, ini, 'Codigo de cuenta' + datos[4]);

        ini = ini + 10;
        doc.text(10, ini, 'Cliente: ' + datos[5]);

        ini = ini + 10;
        doc.text(10, ini, 'Monto apertura: ' + datos[6]);
        doc.text(130, ini, 'Interes: Q ' + datos[7]);

        ini = ini + 12;
        doc.text(10, ini, 'ipf: ' + datos[8]);
        doc.text(40, ini, 'Monto en letras: ' + datos[9]);
        ini = ini + 10;
        doc.text(10, ini, 'Recibo: ' + datos[10]);
        ini = ini + 60;
        i++;
    }
    doc.autoPrint();
    window.open(doc.output('bloburl'))
}
//#region 