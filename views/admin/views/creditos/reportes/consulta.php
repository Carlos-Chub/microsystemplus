<?php
/* ********************************************************** 
  AQUI SE VAN A AGREGAR TODAS LAS CONSULTAS DEL BURO 
  DESPUES SE PUEDEN CREAR VISTAS Y PROCEDIMIENTOS 
  Y TAMBIEN OPTIMIZAR LAS CONSULTAS CON SERVER ASIDE
 ********************************************************** */

  /* +++++++++++++++++++++++++++++++++++++++ INFO DE LA INSTITUCION +++++++++++++++++++++++++++++++++++++++++++ */
  $queryInsti = "SELECT ib.* FROM " . $db_name_general . ".info_coperativa ins 
  INNER JOIN tb_agencia ag ON ag.id_institucion=ins.id_cop 
  INNER JOIN " . $db_name_general . ".tb_infoinstituciones_buro ib ON ib.id_institucion=ins.id_cop 
  WHERE ag.id_agencia=? AND ib.id_buro=1";
  /*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */

  /* +++++++++++++++ CONSULTA DE LOS CLIENTES QUE ESTAN CON CREDITOS EN EL RANGO DE FECHAS DADO +++++++++++++++ */
    $qryCli ="SELECT * FROM clientesCrediref WHERE (CESTADO='F' AND DFecDsbls <= ? )
    OR (CESTADO='G' AND fecha_operacion BETWEEN ? AND ? )";
/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */

/* +++++++++ CONSULTA DE TODAS LAS GARANTIAS DE LOS CREDITOS QUE ESTAN EN EL RANGO DE FECHAS DADO +++++++++ */
    $qryGaranti = "SELECT DISTINCT idGarantia,idTipoGa,idCliente,id_cremcre_meta,descripcionGarantia,
    IFNULL((SELECT cod_crediref FROM " . $db_name_general . ".tb_tiposgarantia WHERE id_TiposGarantia=gar.idTipoGa),'099') codgarantia
    FROM cli_garantia gar INNER JOIN tb_garantias_creditos creg ON creg.id_garantia=gar.idGarantia WHERE creg.id_cremcre_meta IN 
    (SELECT CCODCTA FROM cremcre_meta WHERE (CESTADO='F' AND DFecDsbls<=?) OR 
    (CESTADO='G' AND fecha_operacion BETWEEN ? AND ?));";
/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */

/*++++++++++++++++++++++ CONSULTA DE LOS CLIENTES QUE ESTAN COMO FIADORES +++++++++++++++++++++++++++++ */
$qryFiador = "SELECT cli.*, creg.id_garantia, gar.idGarantia FROM clientesCrediref cli
LEFT JOIN cli_garantia gar ON cli.idcod_cliente=gar.descripcionGarantia 
INNER JOIN tb_garantias_creditos creg ON creg.id_garantia=gar.idGarantia
WHERE (CESTADO='F' AND DFecDsbls <= ? ) OR (CESTADO='G' AND fecha_operacion BETWEEN ? AND ? )";

/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
  ++++++++++++++++++++++ CONSULTA DE LOS CREDITOS VIGENTES HASTA LA FECHA FINAL ++++++++++++++++++++++++++++
  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
  function CreQUERY($qrydata, $db_name_general ){   
    $qryCre = "SELECT cremi.CCODCTA ccodcta, cremi.Cestado estado, cli.idcod_cliente ccodcli, cremi.NCapDes ncapdes,
    COALESCE(
      ( SELECT SUM(ncapita + nintere + OtrosPagos) cuota
        FROM Cre_ppg
        WHERE ccodcta = cremi.CCODCTA AND (dfecven >= ? OR dfecven < ?)
        GROUP BY Id_ppg
        ORDER BY dfecven DESC
        LIMIT 1	), 0) cuota_mes,
    COALESCE(
      (	SELECT SUM(ncapita)
        FROM Cre_ppg
        WHERE dfecven <= ? AND ccodcta = cremi.CCODCTA
        GROUP BY ccodcta
      ), 0) capcalafec,
    COALESCE(
      (	SELECT SUM(KP)
        FROM CREDKAR
        WHERE dfecpro <= ? AND ccodcta = cremi.CCODCTA AND cestado != 'X' AND ctippag = 'P'
        GROUP BY ccodcta
      ), 0) cappag,
    CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(cre_dias_atraso(?, cremi.CCODCTA), '#', 1), '_', 1) AS SIGNED) atraso,
    COALESCE(
      (	SELECT cod_crediref
        FROM ".$db_name_general.".tb_cre_periodos
        WHERE cod_msplus = cremi.NtipPerC
      ), 'O') periodo,
    COALESCE(
      (	SELECT cod_crediref
        FROM ".$db_name_general.".tb_destinocredito
        WHERE id_DestinoCredito = cremi.Cdescre
      ), 'O') destino
    FROM  cremcre_meta cremi
    INNER JOIN tb_cliente cli ON cli.idcod_cliente = cremi.CodCli
    INNER JOIN cre_productos prod ON prod.id = cremi.CCODPRD
    WHERE cremi.CESTADO='".$qrydata[0]."' AND ".$qrydata[1]." 
    ORDER BY cremi.DFecDsbls; ";
  
  return $qryCre;
}
/* ++++++++++++++++++++++ CONSULTA executequery FUNCTION +++++++++++++++++++++++++++++ */
    function executequery($query, $params, $conexion) {
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
    // Cerrar la conexión  //mysqli_close($conexion);
    return [$data, true];
}

function DiasMes($anio, $mes) {
  $primerDia = date('Y-m-01', strtotime("$anio-$mes"));
  $ultimoDia = date('Y-m-t', strtotime("$anio-$mes"));

  return [
      'primer_dia' => $primerDia,
      'ultimo_dia' => $ultimoDia,
  ];
}



  /*  CONSULTA DE CLIENTES***** VER 1
SELECT idcod_cliente,id_tipoCliente AS tipo,
    primer_name, segundo_name, tercer_name, primer_last, segundo_last, casada_last, date_birth,
    genero, estado_civil, type_doc, no_identifica, no_tributaria, no_igss, nacionalidad, muni_extiende,
    tel_no1, tel_no2, cli.Direccion,
    COALESCE(
    (SELECT cod_crediref FROM clhpzzvb_bd_general_coopera.tb_municipios WHERE codigo_municipio = cli.muni_extiende),'10001') AS municipio,
    COALESCE(
    (SELECT cod_crediref FROM clhpzzvb_bd_general_coopera.tb_municipios WHERE codigo_municipio = cli.muni_reside),'X') AS codigo_postal
  FROM  tb_cliente cli
  WHERE idcod_cliente IN (
    SELECT CodCli FROM cremcre_meta WHERE CESTADO = 'F' OR CESTADO = 'G' AND (CESTADO = 'F' AND DFecDsbls <= ?) 
    OR (CESTADO = 'G' AND fecha_operacion BETWEEN ? AND ?) ) 
    */

    
/*************************** MOSTRAR RESULTADOS DE LAS CONSULTAS ****************************************
	echo json_encode(['status' => 0, 'mensaje' =>  $query ]);
	return ;
/*********************************************************************************************************

    */