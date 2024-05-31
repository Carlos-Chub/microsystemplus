<?php
//FUNCIONES GENERALES BENEQ EL MERO MERO CUAO
//BUSCA IDIOMA SEGUN CODIGO

use Complex\Functions;

function func_idiomas($id, $general)
{
    $idioma = "";
    $datos = mysqli_query($general, "SELECT cdescri FROM `tn_EtniaIdioma` WHERE Id_EtinIdiom = $id");
    while ($idiomas = mysqli_fetch_array($datos)) {
        $idioma = utf8_encode($idiomas["cdescri"]);
    }
    return array($idioma);
}
//valida campos $params: array con los datos o variables a validar, $valida: la condicion no permitida, por ejemplo "" para vacio
function validarcampo($params, $valida)
{
    $mensaje = "1";
    foreach ($params as $paramet) {
        if ($paramet == $valida) {
            $mensaje = "Llene todos los campos obligatorios";
        }
    }
    return $mensaje;
}

//VALIDAR NUMERO MINIMO Y MAXIMO PARA ETIQUETA NUMBER
function validar_limites($min, $max, $valor)
{
    $mensaje = "1";
    if ($valor < $min || $valor > $max) {
        $mensaje = "Ingrese un valor a partir de " . $min . " hasta " . $max;
    }
    return $mensaje;
}

//BUSCA departamento SEGUN CODIGO
function departamento($id)
{
    include '../../../includes/BD_con/db_con.php';
    $depar = " ";
    $datos = mysqli_query($general, "SELECT * FROM `departamentos` WHERE codigo_departamento = '$id'");
    while ($depa = mysqli_fetch_array($datos)) {
        $depar = utf8_encode($depa["nombre"]);
    }
    // mysqli_close($general);
    return $depar;
}


//BUSCA municipio SEGUN CODIGO
function municipio($id)
{
    include '../../../includes/BD_con/db_con.php';
    $muni = " ";
    $datos = mysqli_query($general, "SELECT * FROM `municipios` WHERE codigo_municipio = '$id'");
    while ($row = mysqli_fetch_array($datos)) {
        $muni = utf8_encode($row["nombre"]);
    }
    //mysqli_close($general);
    return $muni;
}
//BUSCA TIPO CUENTA POR ID
function tipocuenta($idtip, $tabla, $campo_tabla, $conexion)
{
    $tipo = "";
    $consulta = mysqli_query($conexion, "SELECT `$campo_tabla` FROM `$tabla` WHERE `ccodtip`=$idtip");
    while ($registro = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
        $tipo = utf8_encode($registro[$campo_tabla]);
    }
    return $tipo;
}

//ultima linea impresa en la libreta
function lastnumlin($codigo_cuenta, $nlibreta, $tabla, $campo, $conexion)
{
    if ($tabla == 'ahomcta' || $tabla == 'aprcta') {
        $ultimowhere = "";
    } else {
        $ultimowhere = " AND cestado!=2";
    }
    // include '../../includes/BD_con/db_con.php';
    $consultanum = mysqli_query($conexion, "SELECT MAX(`numlinea`) AS campo FROM `$tabla` WHERE `$campo`=$codigo_cuenta AND `nlibreta`= $nlibreta" . $ultimowhere);
    $ultimonum = 0;
    while ($ultimo = mysqli_fetch_array($consultanum, MYSQLI_ASSOC)) {
        $ultimonum = ($ultimo['campo']);
    }
    // mysqli_close($conexion);
    return $ultimonum;
}

//ultima linea impresa en la libreta
function lastcorrel($codigo_cuenta, $nlibreta, $tabla, $campo, $conexion)
{
    if ($tabla == 'ahomcta' || $tabla == 'aprcta') {
        $ultimowhere = "";
    } else {
        $ultimowhere = " AND cestado!=2";
    }
    // include '../../includes/BD_con/db_con.php';
    $consultanum = mysqli_query($conexion, "SELECT MAX(`correlativo`) AS campo FROM `$tabla` WHERE `$campo`= $codigo_cuenta AND `nlibreta`= $nlibreta" . $ultimowhere);
    $ultimonum = 0;
    while ($ultimo = mysqli_fetch_array($consultanum, MYSQLI_ASSOC)) {
        $ultimonum = ($ultimo['campo']);
    }
    // mysqli_close($conexion);
    return $ultimonum;
}
//
function numfront($tipcuenta, $tabla)
{
    include '../../includes/BD_con/db_con.php';
    $consultanum = mysqli_query($conexion, "SELECT `numfront` FROM `$tabla` WHERE `ccodtip`=$tipcuenta");
    $numfront = 0;
    while ($ultimo = mysqli_fetch_array($consultanum, MYSQLI_ASSOC)) {
        $numfront = ($ultimo['numfront']);
    }
    mysqli_close($conexion);
    return $numfront;
}

function numdorsal($tipcuenta, $tabla)
{
    include '../../includes/BD_con/db_con.php';
    $consultanum = mysqli_query($conexion, "SELECT `numdors` FROM `$tabla` WHERE `ccodtip`=$tipcuenta");
    $numdors = 0;
    while ($ultimo = mysqli_fetch_array($consultanum, MYSQLI_ASSOC)) {
        $numdors = ($ultimo['numdors']);
    }
    mysqli_close($conexion);
    return $numdors;
}

//BUSCA parentesco SEGUN CODIGO
function parenteco($id)
{
    include '../../includes/BD_con/db_con.php';
    $parent = "";
    $datos = mysqli_query($general, "SELECT * FROM `tb_parentesco` WHERE id_parent = $id");
    while ($row = mysqli_fetch_array($datos)) {
        $parent = utf8_encode($row["descripcion"]);
    }
    mysqli_close($general);
    return $parent;
}

//funcion para crear un correlativo total y parcial (AHOMCTA CODIGOS DE CUENTA)
function correlativo_general($nombre_tabla, $camp_tabla, $tipo_tabla, $campo_agencia, $tipo_cuenta, $conexion)
{
    //AGENCIA 
    $query = mysqli_query($conexion, "SELECT `$campo_agencia` agencia FROM `$tipo_tabla` WHERE ccodtip=$tipo_cuenta");
    $agencia = "001";
    while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
        $agencia = $row['agencia'];
    }
    //CORRELATIVO
    $consulta = mysqli_query($conexion, "SELECT MAX(SUBSTR(`$camp_tabla`,9,6)) campo FROM `$nombre_tabla` WHERE SUBSTR(`$camp_tabla`,7,2)=$tipo_cuenta");
    $ultimocorrel = "0";
    while ($ultimo = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
        $ultimocorrel = $ultimo['campo'];
    }
    $correlactual = ((int)$ultimocorrel) + 1;
    //genera codigo
    $generar = '001' . $agencia . $tipo_cuenta . (sprintf('%06d', $correlactual));
    return array($correlactual, $generar);
}

function dias_dif($fec_ini, $fec_fin)
{
    $dateDifference = abs(strtotime($fec_fin) - strtotime($fec_ini));
    $dias_diferencia = $dateDifference / (60 * 60 * 24);
    //$dias_diferencia = abs($dias_diferencia); //valor absoluto y quitar posible negativo
    $dias_diferencia = floor($dias_diferencia); //quito los decimales a los días de diferencia
    return $dias_diferencia;
}
//GASTOS EN CUOTAS
function gastoscuota($idproducto, $idc, $conexion)
{
    $consulta = mysqli_query($conexion, "SELECT cg.*, cm.CCODPRD, cm.MonSug, cm.CodCli,tipg.nombre_gasto,cm.NtipPerC tiperiodo,cm.noPeriodo,cl.short_name  FROM cremcre_meta cm 
    INNER JOIN cre_productos_gastos cg ON cm.CCODPRD=cg.id_producto 
    INNER JOIN cre_tipogastos tipg ON tipg.id=cg.id_tipo_deGasto
    INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
    WHERE cm.CCODCTA='$idc' AND cm.CCODPRD='$idproducto' AND tipo_deCobro=2 AND cg.estado=1");
    $datosgastos[] = [];
    $total = 0;
    $i = 0;
    while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
        $datosgastos[$i] = $fila;
        $i++;
    }
    if ($i == 0) {
        return null;
    }
    return $datosgastos;
}

//function to form the following accounting item code
function getnumcom($userid, $conexion)
{
    $numcom = 'Query error';
    $datos = mysqli_query($conexion, "SELECT ctb_codigo_poliza(" . $userid . ") numcom");
    while ($row = mysqli_fetch_array($datos)) {
        $numcom = $row["numcom"];
    }
    return $numcom;
}
function getnumcompdo($userid, $database)
{
    $result = $database->getAllResults("SELECT ctb_codigo_poliza(?) numcom", [$userid]);
    if (empty($result)) {
        $numcom = 'Query error';
    } else {
        $numcom = $result[0]['numcom'];
    }
    return $numcom;
}
//GENERACION DE CODIGO DE CUENTA DE CREDITO ANTERIOR, YA NO SE UTILIZA
function getcrecodcta($userid, $tipo, $conexion)
{
    $ccodcta = "Error en la generacion del codigo de cuenta";
    $flag = 0;
    $datos = mysqli_query($conexion, "SELECT cre_cod_cuenta(" . $userid . ",'" . $tipo . "') ccodcta");
    while ($row = mysqli_fetch_array($datos)) {
        $flag = $row["ccodcta"];
    }
    $ccodcta = ($flag == 0) ? $ccodcta : $flag;
    $flag = ($flag == 0) ? 0 : 1;
    return [$flag, $ccodcta];
}
//GENERACION DE CODIGO DE CUENTA DE CREDITO
function getcrecodcuenta($agenciaid, $tipo, $conexion)
{
    $ccodcta = "Error en la generacion del codigo de cuenta";
    $flag = 0;
    $datos = mysqli_query($conexion, "SELECT cre_crecodcta(" . $agenciaid . ",'" . $tipo . "') ccodcta");
    while ($row = mysqli_fetch_array($datos)) {
        $flag = $row["ccodcta"];
    }
    $ccodcta = ($flag == 0) ? $ccodcta : $flag;
    $flag = ($flag == 0) ? 0 : 1;
    return [$flag, $ccodcta];
}
//GENERACION DE CODIGO DE CUENTA DE AHORRO
function getccodaho($agenciaid, $tipo, $conexion)
{
    $ccodcta = "Error en la generacion del codigo de cuenta";
    $flag = 0;
    $datos = mysqli_query($conexion, "SELECT aho_ccodaho(" . $agenciaid . ",'" . $tipo . "') ccodaho");
    while ($row = mysqli_fetch_array($datos)) {
        $flag = $row["ccodaho"];
    }
    $ccodcta = ($flag == 0) ? $ccodcta : $flag;
    $flag = ($flag == 0) ? 0 : 1;
    return [$flag, $ccodcta];
}
//GENERACION DE CODIGO DE CUENTA DE APORTACIONES
function getccodaport($agenciaid, $tipo, $conexion)
{
    $ccodcta = "Error en la generacion del codigo de cuenta";
    $flag = 0;
    $datos = mysqli_query($conexion, "SELECT apr_ccodaho(" . $agenciaid . ",'" . $tipo . "') ccodaho");
    while ($row = mysqli_fetch_array($datos)) {
        $flag = $row["ccodaho"];
    }
    $ccodcta = ($flag == 0) ? $ccodcta : $flag;
    $flag = ($flag == 0) ? 0 : 1;
    return [$flag, $ccodcta];
}
//GENERACION DE CODIGO DE CLIENTE
function getcodcli($userid, $conexion)
{
    $codcli = "Error en la generacion del codigo del Cliente";
    $flag = 0;
    $datos = mysqli_query($conexion, "SELECT cli_codcliente(" . $userid . ") codcli");
    while ($row = mysqli_fetch_array($datos)) {
        $flag = $row["codcli"];
    }
    $codcli = ($flag == 0) ? $codcli : $flag;
    $flag = ($flag == 0) ? 0 : 1;
    return [$flag, $codcli];
}
//  nueva FUNCION PARA GENERAR ID CLIENTE POR LA AGENCIA  getcodcli() SERA DESACTUALIZADO 
function cli_gencodcliente($agencia, $conexion)     /* ᕕ(⌐■_■)ᕗ ♪♬ */
{
    $codcli = "Error en la generacion del codigo del Cliente";
    $flag = 0;
    $datos = mysqli_query($conexion, "SELECT cli_gencodcliente(" . $agencia . ") codcli");
    while ($row = mysqli_fetch_array($datos)) {
        $flag = $row["codcli"];
    }
    $codcli = ($flag == 0) ? $codcli : $flag;
    $flag = ($flag == 0) ? 0 : 1;
    return [$flag, $codcli];
}


function getnumcnrocuo($ccodcta, $conexion)
{
    $cnrocuo = 'Query error';
    $datos = mysqli_query($conexion, "SELECT IFNULL(MAX(ck.CNROCUO),0)+1 AS correlrocuo FROM CREDKAR ck WHERE ck.CCODCTA='" . $ccodcta . "'");
    while ($row = mysqli_fetch_array($datos)) {
        $cnrocuo = $row["correlrocuo"];
    }
    return $cnrocuo;
}
//COMPROBACION DEL MES CONTABLE
function comprobar_cierre($userid, $fecha, $conexion)
{
    $mensajes = ['Mes Contable Cerrado, no se puede completar la operacion', 'Mes abierto :)', 'Oficina no existe o usuario invalido', 'Mes contable no existe', 'Estado del mes contable invalido'];
    $flag = 0;
    $estado = 0;
    $datos = mysqli_query($conexion, "SELECT comprobar_cierre(" . $userid . ",'" . $fecha . "') estado");
    while ($row = mysqli_fetch_array($datos)) {
        $flag = $row["estado"];
    }
    $estado = ($flag == 1) ? 1 : 0;
    return [$estado, $mensajes[$flag]];
}
//funcion para obtener el registro insertado
function get_id_insertado($conexion)
{
    $id = 'Query error';
    $datos = mysqli_query($conexion, "SELECT LAST_INSERT_ID() AS last_id");
    while ($row = mysqli_fetch_array($datos)) {
        $id = $row["last_id"];
    }
    return $id;
}

//FUNCION PARA OBTENER EL ID DE AHOMCTB, CUENTA1, CUENTA2
function get_ctb_nomenclatura($tabla, $campcondi2, $idcondi1, $idcondi2, $conexion)
{
    $id = 'X';
    $cuenta1 = 'X';
    $cuenta2 = 'X';
    $datos = mysqli_query($conexion, "SELECT ctb.id, ctb.id_cuenta1, ctb.id_cuenta2 FROM `$tabla` ctb WHERE ctb.id_tipo_cuenta = $idcondi1 AND ctb.`$campcondi2`=$idcondi2");
    while ($row = mysqli_fetch_array($datos)) {
        $id = $row["id"];
        $cuenta1 = $row["id_cuenta1"];
        $cuenta2 = $row["id_cuenta2"];
    }
    return array($id, $cuenta1, $cuenta2);
}

//FUNCION PARA OBTENER EL ID DE AHOMCTB, CUENTA1, CUENTA2
function get_ctb_nomenclatura2($tabla, $campcondi2, $idcondi1, $idcondi2, $id_reg_ant, $conexion)
{
    $id = 'X';
    $datos = mysqli_query($conexion, "SELECT ctb.id FROM `$tabla` ctb WHERE ctb.id_tipo_cuenta = '$idcondi1' AND ctb.`$campcondi2`='$idcondi2' AND ctb.id != '$id_reg_ant'");
    while ($row = mysqli_fetch_array($datos)) {
        $id = $row["id"];
    }
    return $id;
}

//obtener el id de ahotipdoc en base a codtip
function get_id_tipdoc($TipoDoc, $tabla, $conexion)
{
    $id = 'X';
    $datos = mysqli_query($conexion, "SELECT ctb.id FROM `$tabla` ctb WHERE ctb.codtip = '$TipoDoc'");
    while ($row = mysqli_fetch_array($datos)) {
        $id = $row["id"];
    }
    return $id;
}

//FUNCION PARA GENERACION DE GLOSAS PARA APORTACIONES Y AHORROS
//FUNCIONES PARA RECUPERAR EL TIPO DE TRASACCION 
function glosa_obtenerTipoTransaccion($id, $tabla, $campo_tabla, $conexion)
{
    $transaccion = "#TIPO DE DOCUMENTO NO ENCONTRADO#";
    $consulta = mysqli_query($conexion, "SELECT `$campo_tabla` FROM `$tabla` WHERE `id_tipo`=$id");
    while ($registro = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
        $transaccion = $registro[$campo_tabla];
    }
    return $transaccion;
}

//FUNCION PARA OBTENER MODULO DE APRT O AHORRO
function glosa_obtenerTipoModulo($pos)
{
    $array = array("AHORRO", "APORTACIÓN");
    $longitud = count($array);
    if ($pos < 0 || $pos >= $longitud) {
        return "#TIPO DE MODULO NO ENCONTRADO#";
    }
    return ($array[$pos]);
}

//FUNCION PARA OBTENER NOMBRE DEL CLIENTE MEDIANTE EL ID
function glosa_obtenerNomCliente($id, $conexion)
{
    $name = "#NOMBRE NO ENCONTRADO#";
    $consulta = mysqli_query($conexion, "SELECT `short_name` FROM `tb_cliente` WHERE `idcod_cliente`=$id");
    while ($registro = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
        $name =  utf8_decode(mb_strtoupper($registro['short_name'], 'utf-8'));
    }
    return $name;
}

//FUNCION QUE DEVUELVE TIPO DE MOVIMIENTO
function glosa_obtenerMovimiento($pos)
{
    $array = array("DEPÓSITO", "RETIRO", "ACREDITACIÓN DE INTERESES", "RETENCIÓN DE ISR", "PROVISION DE INTERESES");
    $longitud = count($array);

    if ($pos < 0 || $pos >= $longitud) {
        return "#TIPO DE MOVIMIENTO NO ENCONTRADO#";
    }
    return $array[$pos];
}

//FUNCION QUE DUELVE EL NUMERO DE RECIBO
function glosa_obtenerRecibo($codigo)
{
    return "CON RECIBO NO. " . $codigo;
}

//FUNCION QUE DEVUELVE CONECTORES
function glosa_obtenerConector($pos)
{
    $array = array("DE", "CON", "A", "AL");
    $longitud = count($array);

    if ($pos < 0 || $pos >= $longitud) {
        return "#TIPO DE CONECTOR NO ENCONTRADO#";
    }
    return $array[$pos];
}

//FUNCION PARA DEVOLVER UN ESPACIO EN BLANCO
function glosa_obtenerEspacio()
{
    return " ";
}

//Conversion de numeros a letras en el apartado de fechas
function fechletras($date)
{
    $date = substr($date, 0, 10);
    $numeroDia = date('d', strtotime($date));
    $mes = date('F', strtotime($date));
    $anio = date('Y', strtotime($date));
    $meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    $nombreMes = str_replace($meses_EN, $meses_ES, $mes);
    return $numeroDia . " de " . $nombreMes . " de " . $anio;
}

function calcular_edad($fecha)
{
    // Separar la fecha en año, mes y día
    $partes_fecha = explode("-", $fecha, 3);
    $anio = $partes_fecha[0];
    $mes = $partes_fecha[1];
    $dia = $partes_fecha[2];

    // Obtener la fecha actual
    $hoy = getdate();
    $anio_actual = $hoy["year"];
    $mes_actual = $hoy["mon"];
    $dia_actual = $hoy["mday"];

    // Calcular la edad
    $edad = $anio_actual - $anio;

    // Ajustar la edad si aún no ha pasado el cumpleaños de este año
    if ($mes_actual < $mes || ($mes_actual == $mes && $dia_actual < $dia)) {
        $edad--;
    }

    return $edad;
}

//FUNCION PARA CONSULTAR SI HAY UN CIERRE PENDIENTE
function comprobar_cierre_caja($idusuario, $conexion, $bandera = '1', $fechainicio = "0000-00-00", $fechafin = "0000-00-00", $fechavalue = "0000-00-00")
{
    try {
        $resultado = ["0", "No se encontro info de la institucion, verifique", "No se encontro el rol de usuario", "Realice una apertura de caja para iniciar sus labores", "Realice el cierre de caja pendiente para iniciar sus labores", "No se puede realizar esta acción porque ya se ha vencido el plazo para realizarlo", "1", "1", "1"];
        $stmtaux = $conexion->prepare("SELECT comprobar_cierre_caja(?,?,?,?,?,?) AS cierre");
        if (!$stmtaux) {
            throw new Exception("Error en la consulta de comprobar cierre" . $conexion->error);
        }
        $aux = date('Y-m-d');
        $aux2 = $idusuario;
        $stmtaux->bind_param("ssisss", $aux2, $aux, $bandera, $fechainicio, $fechafin, $fechavalue);
        if (!$stmtaux->execute()) {
            throw new Exception("Error al consultar comprobar cierre" . $stmtaux->error);
        }
        $result = $stmtaux->get_result();
        $rowdatos = $result->fetch_assoc();
        return [$rowdatos['cierre'], $resultado[$rowdatos['cierre']]];
    } catch (Exception $e) {
        //Captura el error
        $mensaje_error = $e->getMessage();
        return [0, $mensaje_error];
        $conexion->close();
    } finally {
        if ($stmtaux !== false) {
            $stmtaux->close();
        }
    }
}
function validateDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function logerrores($mensaje, $file1, $line1, $file2 = 0, $line2 = 0)
{
    $archivoLog = __DIR__ . '/../../logs/errores.log';
    if (!file_exists($archivoLog)) {
        // Si no existe, crear el archivo con permisos de escritura
        $creado = touch($archivoLog);
        if (!$creado) {
            return 'Archivo de registro no creado, verificar';
        }
    }
    $codigoError = rand(1000, 9999);
    $mensajelog = "Error [" . date("Y-m-d H:i:s") . "] [Código: $codigoError] - Error en el archivo: " . $file1 . " Linea " . $line1 . "; file secundario:" . $file2 . " en la línea " . $line2 . ": " . $mensaje . PHP_EOL;
    error_log($mensajelog, 3, $archivoLog);
    return $codigoError;
}
/**
 * La función calcula la diferencia en días entre dos fechas convirtiéndolas en objetos DateTime
 * y considerando un año como 360 días y un mes como 30 días.
 * 
 * @param fecha1 Fecha inicial
 * @param fecha2 Fecha final
 * 
 * @return integer Valor absoluto de la diferencia en dias enteros
 */

function diferenciaEnDias($fecha1, $fecha2) {
    // Convertir las fechas en objetos DateTime
    $fecha1 = new DateTime($fecha1);
    $fecha2 = new DateTime($fecha2);
    
    // Descomponer las fechas en años, meses y días
    $ano1 = (int)$fecha1->format('Y');
    $mes1 = (int)$fecha1->format('m');
    $dia1 = (int)$fecha1->format('d');
    
    $ano2 = (int)$fecha2->format('Y');
    $mes2 = (int)$fecha2->format('m');
    $dia2 = (int)$fecha2->format('d');
    
    // Calcular la diferencia en años, meses y días
    $anoDiff = $ano2 - $ano1;
    $mesDiff = $mes2 - $mes1;
    $diaDiff = $dia2 - $dia1;
    
    // Convertir todo a días considerando meses de 30 días y años de 360 días
    $diferenciaEnDias = $anoDiff * 360 + $mesDiff * 30 + $diaDiff;
    
    return abs($diferenciaEnDias);
}
function agregarMes($fecha, $meses = 1)
{
    $nuevaFecha = strtotime("+$meses month", strtotime($fecha));
    return date('Y-m-d', $nuevaFecha);
}
function diasDelMes($fecha)
{
    $anio = date('Y', strtotime($fecha));
    $mes = date('m', strtotime($fecha));
    
    // Crear una fecha temporal con el primer día del siguiente mes
    $primerDiaSiguienteMes = strtotime('+1 month', strtotime($anio . '-' . $mes . '-01'));
    
    // Restar un día para obtener el último día del mes actual
    $ultimoDiaDelMes = date('d', strtotime('-1 day', $primerDiaSiguienteMes));
    
    return $ultimoDiaDelMes;
}