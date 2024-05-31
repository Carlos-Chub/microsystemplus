<?php
session_start();
include '../../../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');
$hoy = date("Y-m-d");
//[[`finicio`,`ffin`],[`codofi`,`fondoid`],[`ragencia`,`rfondos`],[]]
$datos = $_POST["datosval"];
$inputs = $datos[0];
$selects = $datos[1];
$archivo = $datos[3];
$tipo = $_POST["tipo"];

$fecha_inicio = $inputs[0];
$fecha_fin = $inputs[1];
/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
+++++++++++++++++++++++++++++++++++++++ INFO DE LA INSTITUCION +++++++++++++++++++++++++++++++++++++++++++
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
if(!isset($_SESSION['id_agencia'])){
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}

$query = "SELECT ib.* FROM " . $db_name_general . ".info_coperativa ins 
INNER JOIN tb_agencia ag ON ag.id_institucion=ins.id_cop 
INNER JOIN " . $db_name_general . ".tb_infoinstituciones_buro ib ON ib.id_institucion=ins.id_cop 
WHERE ag.id_agencia=? AND ib.id_buro=1";
$response = executequery($query, [$_SESSION['id_agencia']], $conexion);
if (!$response[1]) {
    echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
    return;
}
$info = $response[0];
if (count($info) == 0) {
    echo json_encode(['status' => 0, 'mensaje' => 'Institucion no válida para el Buró']);
    return;
}

/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    ++++++++++++++++++++++ CONSULTA DE LOS CREDITOS VIGENTES HASTA LA FECHA FINAL+++++++++++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$query = "SELECT cremi.CCODCTA ccodcta,cremi.Cestado estado,cli.idcod_cliente ccodcli,cremi.NCapDes ncapdes, 
     IFNULL(((SELECT SUM(ncapita+nintere+OtrosPagos) cuota FROM Cre_ppg WHERE ccodcta=cremi.CCODCTA AND dfecven>=? GROUP BY Id_ppg LIMIT 1)
     UNION ALL
     (SELECT SUM(ncapita+nintere+OtrosPagos) cuota FROM Cre_ppg WHERE ccodcta=cremi.CCODCTA AND dfecven < ? GROUP BY Id_ppg ORDER BY dfecven DESC LIMIT 1) LIMIT 1),0) cuota_mes,
    IFNULL((SELECT SUM(ncapita) FROM Cre_ppg WHERE dfecven<=? AND ccodcta=cremi.CCODCTA GROUP BY ccodcta),0) capcalafec,
    IFNULL((SELECT SUM(KP) FROM CREDKAR WHERE dfecpro<=? AND ccodcta=cremi.CCODCTA AND cestado!='X' AND ctippag='P' GROUP BY ccodcta),0) cappag,
    CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(cre_dias_atraso(?,cremi.CCODCTA),'#',1),'_',1) AS SIGNED) atraso,
    IFNULL((SELECT cod_crediref FROM " . $db_name_general . ".tb_cre_periodos WHERE cod_msplus=cremi.NtipPerC),'O') periodo,
    IFNULL((SELECT cod_crediref FROM " . $db_name_general . ".tb_destinocredito WHERE id_DestinoCredito=cremi.Cdescre),'O') destino
    FROM cremcre_meta cremi 
  	INNER JOIN tb_cliente cli ON cli.idcod_cliente=cremi.CodCli
    INNER JOIN cre_productos prod ON prod.id=cremi.CCODPRD
    WHERE cremi.CESTADO='F' AND cremi.DFecDsbls<=? ORDER BY cremi.DFecDsbls;";

$response = executequery($query, [$fecha_inicio, $fecha_inicio, $fecha_fin, $fecha_fin, $fecha_fin, $fecha_fin], $conexion);
if (!$response[1]) {
    echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
    return;
}
// TRAE LOS CREDITOS CON ESTADO CANCELADOS 
$vigentes = $response[0]; // CONSULTA DE LOS CREDITOS VIGENTES HASTA LA FECHA FINAL

/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    ++++++++++++++++++++++ CONSULTA DE LOS CREDITOS CANCELADOS EN EL RANGO DE FECHAS DADO+++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$query = "SELECT cremi.CCODCTA ccodcta,cremi.Cestado estado,cli.idcod_cliente ccodcli,cremi.NCapDes ncapdes, 
     IFNULL(((SELECT SUM(ncapita+nintere+OtrosPagos) cuota FROM Cre_ppg WHERE ccodcta=cremi.CCODCTA AND dfecven>=? GROUP BY Id_ppg LIMIT 1)
     UNION ALL
     (SELECT SUM(ncapita+nintere+OtrosPagos) cuota FROM Cre_ppg WHERE ccodcta=cremi.CCODCTA AND dfecven < ? GROUP BY Id_ppg ORDER BY dfecven DESC LIMIT 1) LIMIT 1),0) cuota_mes,
     IFNULL((SELECT SUM(ncapita) FROM Cre_ppg WHERE dfecven<=? AND ccodcta=cremi.CCODCTA GROUP BY ccodcta),0) capcalafec,
     IFNULL((SELECT SUM(KP) FROM CREDKAR WHERE dfecpro<=? AND ccodcta=cremi.CCODCTA AND cestado!='X' AND ctippag='P' GROUP BY ccodcta),0) cappag,
     CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(cre_dias_atraso(?,cremi.CCODCTA),'#',1),'_',1) AS SIGNED) atraso,
     IFNULL((SELECT cod_crediref FROM " . $db_name_general . ".tb_cre_periodos WHERE cod_msplus=cremi.NtipPerC),'O') periodo,
     IFNULL((SELECT cod_crediref FROM " . $db_name_general . ".tb_destinocredito WHERE id_DestinoCredito=cremi.Cdescre),'O') destino
     FROM cremcre_meta cremi 
     INNER JOIN tb_cliente cli ON cli.idcod_cliente=cremi.CodCli
     INNER JOIN cre_productos prod ON prod.id=cremi.CCODPRD
     WHERE cremi.CESTADO='G' AND cremi.fecha_operacion BETWEEN ? AND ? ORDER BY cremi.DFecDsbls;";

$response = executequery($query, [$fecha_inicio, $fecha_inicio, $fecha_fin, $fecha_fin, $fecha_fin, $fecha_inicio, $fecha_fin], $conexion);
if (!$response[1]) {
    echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
    return;
}
$cancelados = $response[0];  // CONSULTA DE LOS CREDITOS CANCELADOS 
$flag = ((count($vigentes) + count($cancelados)) > 0) ? true : false;
if (!$flag) {
    echo json_encode(['status' => 0, 'mensaje' => 'No hay datos en la fecha indicada']);
    return;
}

/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    ++++++++++++++++ CONSULTA DE LOS CLIENTES QUE ESTAN CON CREDITOS EN EL RANGO DE FECHAS DADO ++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$query = "SELECT idcod_cliente,id_tipoCliente tipo,primer_name,segundo_name, tercer_name,primer_last, segundo_last,
     casada_last,date_birth,genero,estado_civil,type_doc,no_identifica,no_tributaria,no_igss,nacionalidad,muni_extiende,
     IFNULL((SELECT cod_crediref FROM " . $db_name_general . ".tb_municipios WHERE codigo_municipio=cli.muni_extiende),'10001') municipio,
     tel_no1,tel_no2,cli.Direccion,
     IFNULL((SELECT cod_crediref FROM " . $db_name_general . ".tb_municipios WHERE codigo_municipio=cli.muni_reside),'X') codigo_postal
     FROM tb_cliente cli WHERE idcod_cliente IN 
     (SELECT DISTINCT CodCli FROM cremcre_meta WHERE (CESTADO='F' AND DFecDsbls<=?) OR (CESTADO='G' AND fecha_operacion BETWEEN ? AND ?));";

$response = executequery($query, [$fecha_fin, $fecha_inicio, $fecha_fin], $conexion);
if (!$response[1]) {
    echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
    return;
}
$clientes = $response[0]; // CONSULTA DE LOS CLIENTES QUE ESTAN CON CREDITOS  

/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    ++++++++++ CONSULTA DE TODAS LAS GARANTIAS DE LOS CREDITOS QUE ESTAN EN EL RANGO DE FECHAS DADO ++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$query = "SELECT DISTINCT idGarantia,idTipoGa,idCliente,id_cremcre_meta,descripcionGarantia,
     IFNULL((SELECT cod_crediref FROM " . $db_name_general . ".tb_tiposgarantia WHERE id_TiposGarantia=gar.idTipoGa),'099') codgarantia
     FROM cli_garantia gar INNER JOIN tb_garantias_creditos creg ON creg.id_garantia=gar.idGarantia WHERE creg.id_cremcre_meta IN 
     (SELECT CCODCTA FROM cremcre_meta WHERE (CESTADO='F' AND DFecDsbls<=?) OR 
     (CESTADO='G' AND fecha_operacion BETWEEN ? AND ?));";

$response = executequery($query, [$fecha_fin, $fecha_inicio, $fecha_fin], $conexion);
if (!$response[1]) {
    echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
    return;
}
$garantias = $response[0]; //CONSULTA DE TODAS LAS GARANTIAS DE LOS CREDITOS

/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++++++++++++++++ CONSULTA DE LOS CLIENTES QUE ESTAN COMO FIADORES +++++++++++++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$query = "SELECT DISTINCT idcod_cliente,id_tipoCliente tipo,primer_name,segundo_name, tercer_name,primer_last, segundo_last,
     casada_last,date_birth,genero,estado_civil,type_doc,no_identifica,no_tributaria,no_igss,nacionalidad, muni_extiende, 
     IFNULL((SELECT cod_crediref FROM " . $db_name_general . ".municipios WHERE codigo_municipio=cli.muni_extiende),'10001') municipio,
     tel_no1,tel_no2,cli.Direccion,
     IFNULL((SELECT cod_crediref FROM " . $db_name_general . ".tb_municipios WHERE codigo_municipio=cli.muni_reside),'X') codigo_postal
     FROM cli_garantia gar 
 	 INNER JOIN tb_garantias_creditos creg ON creg.id_garantia=gar.idGarantia
 	 INNER JOIN tb_cliente cli ON cli.idcod_cliente=gar.descripcionGarantia 
 	 WHERE creg.id_cremcre_meta IN 
     (SELECT CCODCTA FROM cremcre_meta WHERE (CESTADO='F' AND DFecDsbls<=?) OR (CESTADO='G' AND fecha_operacion BETWEEN ? AND ?));";

$response = executequery($query, [$fecha_fin, $fecha_inicio, $fecha_fin], $conexion);
if (!$response[1]) {
    echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
    return;
}
$fiadores = $response[0];  // CONSULTA DE LOS CLIENTES FIADORES

/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++++++++++++++++ TRAE TODOS LOS TIPOS DE COMPORTAMIENTOS DE CREDITO +++++++++++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
$query = "SELECT * FROM tb_condicioncredito where cod_crediref <> ?;";
$response = executequery($query, ['iu'], $general);
if (!$response[1]) {
    echo json_encode(['status' => 0, 'mensaje' => $response[0]]);
    return;
}
$estados = $response[0]; // TRAE TODOS LOS TIPOS DE COMPORTAMIENTOS DE CREDITO

switch ($tipo) {
    case 'txt':
        printpdf($datos, $info, $vigentes, $cancelados, $clientes, $garantias, $fiadores, $estados);
        break;
}

/*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++++++++++++++++++++++++ FUNCION PARA LA GENERACION DE PDF ++++++++++++++++++++++++++++++++++++++++
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
function printpdf($datos, $info, $vigentes, $cancelados, $clientes, $garantias, $fiadores, $estados)
{
    $codigoinstitucion = ($info[0]["codigo"]);

    $fechainicio = $datos[0][0];
    $fechafin = $datos[0][1];

    $filename = "reporte_" . $fechainicio . 'CRD.txt';
    /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        ++++++++++++++++++++++++ PRIMER APARTADO: SECCION A; REGISTRO DE IDENTIFICACION ++++++++++++++++++++++++++
        ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    $header = [
        'A',
        transformer($codigoinstitucion, 4, ' '),
        'A', //A: CREDITOS DE MICROFINANZAS
        date("Ymd", strtotime($fechafin))
    ];

    $contenido_a = implode('', $header);
    $contenido_a .= "\n";

    /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        +++++++++++++ SEGUNDO APARTADO: SECCION B; REGISTRO DE IDENTIFICACION CREDITICIA +++++++++++++++++++++++++
        ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    $archivo = fopen($filename, "w");
    fwrite($archivo, $contenido_a);

    $contenido_b = "";
    $contenido_c = "";
    $contenido_general = "";

    $i = 0;
    while ($i < count($vigentes)) {
        $cuenta = $vigentes[$i]['ccodcta'];
        $ccodcli = $vigentes[$i]['ccodcli'];
        $contenido_b = datoscreditos($cuenta, $vigentes, $garantias, $estados);
        $contenido_b .= "\n";
        fwrite($archivo, $contenido_b);

        $indexcli = array_search($ccodcli, array_column($clientes, 'idcod_cliente'));
        gen_cliente($indexcli, $clientes, $archivo);

        //FIADOR DATOS GENERALES
        $indexgar = array_search($cuenta, array_column($garantias, 'id_cremcre_meta'));
        if ($indexgar !== false) {
            $coddescripcion = $garantias[$indexgar]['descripcionGarantia'];
            $codfiador = array_search($coddescripcion, array_column($fiadores, 'idcod_cliente'));
            if ($codfiador !== false) {
                gen_cliente($codfiador, $fiadores, $archivo);
            }
        }

        //fwrite($archivo, "\n"); //ELIMINAR DESPUES DE COMPLETAR EL REPORTE
        $i++;
    }

    //CANCELADOS
    // fwrite($archivo, "CANCELADOS\n");
    $i = 0;
    while ($i < count($cancelados)) {
        $cuenta = $cancelados[$i]['ccodcta'];
        $ccodcli = $cancelados[$i]['ccodcli'];
        $contenido_b = datoscreditos($cuenta, $cancelados, $garantias, $estados);
        $contenido_b .= "\n";
        fwrite($archivo, $contenido_b);

        $indexcli = array_search($ccodcli, array_column($clientes, 'idcod_cliente'));
        gen_cliente($indexcli, $clientes, $archivo);
        //FIADOR DATOS GENERALES
        $indexgar = array_search($cuenta, array_column($garantias, 'id_cremcre_meta'));
        if ($indexgar !== false) {
            $coddescripcion = $garantias[$indexgar]['descripcionGarantia'];
            $codfiador = array_search($coddescripcion, array_column($fiadores, 'idcod_cliente'));
            if ($codfiador !== false) {
                gen_cliente($codfiador, $fiadores, $archivo);
            }
        }

        //fwrite($archivo, "\n"); //ELIMINAR DESPUES DE COMPLETAR EL REPORTE
        $i++;
    }

    /*  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        ++++++++++++++++++++++++ INICIO DE CREACION DE ARCHIVO Y LLENADO DEL CONTENIDO RESUELTO ++++++++++++++++++
        ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    fclose($archivo);

    ob_start();
    readfile($filename);
    $doc_data = ob_get_contents();
    ob_end_clean();

    unlink($filename); //ELIMINAR EL ARCHIVO TEMPORAL
    $opResult = array(
        'status' => 1,
        'mensaje' => 'Reporte generado correctamente',
        'namefile' => $filename,
        'tipo' => "txt",
        'data' => "data:application/pdf;base64," . base64_encode($doc_data)
    );
    echo json_encode($opResult);
    return;
}
function gen_cliente($indice, $datos, $archivo)
{
    $contenido_c = datosclientes($datos[$indice]['idcod_cliente'], $datos, 'F');
    $contenido_c .= "\n";
    fwrite($archivo, $contenido_c);

    //CAMPOS OPCIONALES DIRECCIONES
    $direccion = direccion($datos[$indice], 'D');
    $contenido_d = ($direccion === false) ? '' : $direccion . "\n";
    fwrite($archivo, $contenido_d);

    //CAMPOS OPCIONALES TELEFONOS
    $tel = telefono($datos[$indice], 'C');
    $contenido_e = ($tel === false) ? '' : $tel . "\n";
    fwrite($archivo, $contenido_e);

    $tel = telefono($datos[$indice], 'D');
    $contenido_e = ($tel === false) ? '' : $tel . "\n";
    fwrite($archivo, $contenido_e);
}

function direccion($data, $uso)
{
    if (strlen(trim($data['Direccion'])) < 1) {
        return false;
    }
    $contenido = [
        'D',
        transformer(quitar_tildes(strtoupper($data['Direccion'])), 75, ' '),
        ($data['codigo_postal'] == "X") ? transformer(' ', 5, ' ') : transformer($data['codigo_postal'], 5, ' '),
        $uso
    ];
    return implode('', $contenido);
    // return  $contenido;
}
function telefono($data, $uso)
{
    //c :celular(tel1) d:domicilio(tel2)
    $tel = ($uso == "C") ? $data['tel_no1'] : $data['tel_no2'];
    if (strlen(trim($tel)) < 6) {
        return false;
    }
    $contenido = [
        'E',
        transformer($tel, 10, ' '),
        $uso,
    ];
    return implode('', $contenido);
}
function datosclientes($codcli, $datos, $vinculo)
{
    $indice = array_search($codcli, array_column($datos, 'idcod_cliente'));
    $nombre1 = trim(strtoupper($datos[$indice]['primer_name']));
    $nombre2 = trim(strtoupper($datos[$indice]['segundo_name'])) . ' ' . trim(strtoupper($datos[$indice]['tercer_name']));
    $nombre3 = trim(strtoupper($datos[$indice]['tercer_name']));

    $apellido1 = trim(strtoupper($datos[$indice]['primer_last']));
    $apellido2 = trim(strtoupper($datos[$indice]['segundo_last']));
    $apellidocasada = trim(strtoupper($datos[$indice]['casada_last']));

    $genero = $datos[$indice]['genero'];
    $estado_civil = $datos[$indice]['estado_civil'];
    $igss = $datos[$indice]['no_igss'];
    $nit = $datos[$indice]['no_tributaria'];
    $fecnac = $datos[$indice]['date_birth'];
    $nacionalidad = $datos[$indice]['nacionalidad'];
    $dpi = $datos[$indice]['no_identifica'];
    $codpostal = $datos[$indice]['municipio'];
    $contenido = [
        'C',
        'I', //PARA PERSONA INDIVIDUAL ES I, E PARA EMPRESAS O PERSONA JURIDICA
        $vinculo,
        transformer(quitar_tildes($apellido1), 25, ' '),
        transformer(quitar_tildes($nombre1), 25, ' '),
        transformer(quitar_tildes($apellido2), 25, ' '),
        transformer(quitar_tildes($nombre2), 25, ' '),
        transformer(quitar_tildes($apellidocasada), 25, ' '),
        (mb_strlen($genero) > 0) ? (($genero == 'F' || $genero == 'M') ? $genero : ' ') : ' ',
        (mb_strlen($estado_civil) > 0) ? substr($estado_civil, 0, 1) : ' ',
        transformer(' ', 15, ' '), //CEDULA
        transformer(' ', 20, ' '), //PASAPORTE
        transformer($igss, 20, ' '),
        transformer(' ', 20, ' '), //LICENCIA
        transformer($nit, 12, ' '),
        (validateDate($fecnac, 'Y-m-d')) ? transformer(date("Ymd", strtotime($fecnac)), 8, ' ') : transformer(' ', 8, ' '),
        (mb_strlen($nacionalidad) > 0) ? transformer($nacionalidad, 2, ' ') : 'GT',
        transformer($codcli, 20, ' '),
        transformer($codpostal, 5, ' '),
        transformer($dpi, 13, ' '),
    ];
    return implode('', $contenido);
}
function validateDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function datoscreditos($cuenta, $datos, $garantias, $tipoestados)
{
    $indice = array_search($cuenta, array_column($datos, 'ccodcta'));
    $indicegarantia = array_search($cuenta, array_column($garantias, 'id_cremcre_meta'));
    $tipocuenta = ($indicegarantia === false) ? '099' : $garantias[$indicegarantia]['codgarantia'];
    $status = statuscredito($datos[$indice]['atraso'], $tipoestados, $datos[$indice]['estado']);
    $saldo = ($datos[$indice]['ncapdes'] - $datos[$indice]['cappag']);
    $saldovencido = ($datos[$indice]['capcalafec'] - $datos[$indice]['cappag']);
    $saldovencido = ($saldovencido > 0) ? $saldovencido : 0;

    $contenido = [
        'B',
        $tipocuenta,
        transformer($cuenta, 20, ' '),
        $status,
        '320', //MONEDA: 320 QUETZALES ; 840 DOLARES 
        transformer(' ', 50, ' '),
        transformer($datos[$indice]['ncapdes'], 12, 0, 1),
        transformer($saldo, 12, 0, 1),
        transformer($saldovencido, 12, 0, 1),
        transformer($datos[$indice]['cuota_mes'], 12, 0, 1),
        $datos[$indice]['periodo'],
        $datos[$indice]['destino'],
    ];

    return implode('', $contenido);
}
function transformer($texto, $longitud, $caracter, $condi = 0)
{
    $dire = STR_PAD_RIGHT;
    if ($condi == 1) {
        $dire = STR_PAD_LEFT;
        $texto = number_format($texto, 2, '.', '');
    }
    $cadena = str_pad($texto, $longitud, $caracter, $dire);
    return trim($cadena, "\n\r");
}

function statuscredito($atraso, $parametros, $statusmplus)
{
    if ($statusmplus == "F") {
        $estado = '0';
        if ($atraso > 0) {
            $fila = array_keys((array_filter($parametros, function ($var) use ($atraso) {
                return ($atraso >= $var['min_dia'] && $atraso <= $var['max_dia']);
            })));
            $estado = (count($fila) > 0) ? $parametros[$fila[0]]['cod_crediref'] : '0';
        }
    } else {
        $estado = 'X';
    }
    return $estado;
}
function executequery($query, $params, $conexion)
{
    $stmt = $conexion->prepare($query);
    $aux = mysqli_error($conexion);
    if ($aux) {
        return ['ERROR: ' . $aux, false];
    }
    $types = '';
    $bindParams = [];
    $bindParams[] = &$types;
    foreach ($params as &$param) {
        $types .= 's';
        $bindParams[] = &$param;
    }
    call_user_func_array(array($stmt, 'bind_param'), $bindParams);
    if (!$stmt->execute()) {
        return ["Error en la ejecución de la consulta: " . $stmt->error, false];
    }
    $data = [];
    $resultado = $stmt->get_result();
    $i = 0;
    while ($fila = $resultado->fetch_assoc()) {
        $data[$i] = $fila;
        $i++;
    }
    $stmt->close();
    return [$data, true];
}
function quitar_tildes($texto)
{

    $no_acentos = array("á", "é", "í", "ó", "ú", "Á", "É", "Í", "Ó", "Ú", "ñ", "Ñ");
    $acentos = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "n", "N");
    $texto = str_replace($no_acentos, $acentos, $texto);
    return $texto;
}
