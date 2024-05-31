<?php
//FUNCIONES GENERALES PARA CONSULTAR
function consultar_datos($consulta, $conexion, $indice)
{

    $data = mysqli_query($conexion, $consulta);
    $aux = mysqli_error($conexion);
    if ($aux) {
        return [0, 'Hubo un error al consultar los datos. Indice: ' . $indice];
    }
    if (!$consulta) {
        return [0, 'Consulta no ejecutada correctamente. Indice: ' . $indice];
    }
    $bandera = 0;
    // $i = 0;
    while ($fila = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
        $datos = $fila;
        $bandera = 1;
        // $i++;
    }
    if ($bandera == 0) {
        $datos[] = [];
    }
    return [1, $bandera, $datos];
}

function consultar_datos_plus($consulta, $conexion, $indice)
{

    $data = mysqli_query($conexion, $consulta);
    $aux = mysqli_error($conexion);
    if ($aux) {
        return [0, 'Hubo un error al consultar los datos. Indice: ' . $indice];
    }
    if (!$consulta) {
        return [0, 'Consulta no ejecutada correctamente. Indice: ' . $indice];
    }
    $bandera = 0;
    // $i = 0;
    while ($fila = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
        $datos[] = $fila;
        $bandera = 1;
        // $i++;
    }
    if ($bandera == 0) {
        $datos[] = [];
    }
    return [1, $bandera, $datos];
}


//FUNCIONES DE SEGUNDO NIVEL - UTILIZAN LAS FUNCIONES GENERALES

function datos_titulares($conexion, $idcliente)
{
    $data2[] = [];
    $banderatipoact = false;
    //Consulta de tipo actuac y calidad
    $consultar = "SELECT cl.actu_Propio AS tipoActuacion, cl.actu_Propio AS calidadActua FROM tb_cliente cl WHERE cl.idcod_cliente='" . $idcliente . "'";
    $dataaux = consultar_datos($consultar, $conexion, 1);
    if ($dataaux[0] == 0) {
        return $dataaux;
    }
    $data2[0] = $dataaux[0];
    $data2[1] = $dataaux[1];
    foreach ($dataaux[2] as $key => $value) {
        if ($key == 'tipoActuacion' && $value != 'Ninguno') {
            $banderatipoact = true;
        }
        $data2[2][$key] = $value;
    }
    ($data2[2]['tipoActuacion'] != 'Ninguno') ? ($data2[2]['tipoActuacion'] = 2) : ($data2[2]['tipoActuacion'] = 1);
    unset($dataaux);

    //Consultar lugar
    $consultar = "SELECT ag.pais, ag.departamento, ag.municipio FROM tb_agencia ag WHERE ag.id_agencia='" . $_SESSION['id_agencia'] . "'";
    $dataaux = consultar_datos($consultar, $conexion, 2);
    if ($dataaux[0] == 0) {
        return $dataaux;
    }
    $data2[0] = $dataaux[0];
    $data2[1] = $dataaux[1];
    foreach ($dataaux[2] as $key => $value) {
        $data2[2]['lugar'][$key] = $value;
    }
    $data[2]['lugar']['departamento'] =  substr($data2[2]['lugar']['municipio'], 0, 2);
    unset($dataaux);

    //CONSULTAR FECHA
    $consultar = "SELECT cl.fecha_mod AS x_fecha FROM tb_cliente cl WHERE cl.idcod_cliente='" . $idcliente . "'";
    $dataaux = consultar_datos($consultar, $conexion, 3);
    if ($dataaux[0] == 0) {
        return $dataaux;
    }
    $data2[0] = $dataaux[0];
    $data2[1] = $dataaux[1];
    foreach ($dataaux[2] as $key => $value) {
        $data2[2]['fecha'] = str_replace('-', '', $value);
    }
    unset($dataaux);

    //CLIENTES
    $dataaux = datos_clientes($conexion, $idcliente);
    if ($dataaux[0] == 0) {
        return $dataaux;
    }
    $data2[0] = $dataaux[0];
    $data2[1] = $dataaux[1];
    $data2[2]['cliente'] = $dataaux[2];
    unset($dataaux);

    //REPRESENTANTE
    if ($banderatipoact) {
        $data2[2]['representante'] = [];
        $consultar = "SELECT cl.representante_name AS representante FROM tb_cliente cl WHERE cl.idcod_cliente='" . $idcliente . "'";
        $dataaux = consultar_datos($consultar, $conexion, 12);
        if ($dataaux[0] == 0) {
            return $dataaux;
        }
        //VALIDAR SI EL CLIENTE EXISTE
        $consultar = "SELECT cl.idcod_cliente FROM tb_cliente cl WHERE cl.idcod_cliente='" . $dataaux[2]['representante'] . "'";
        $dataaux2 = consultar_datos($consultar, $conexion, 13);
        if ($dataaux2[0] == 0) {
            return $dataaux2;
        }
        if ($dataaux2[1] == 1) {
            unset($dataaux2);
            //LLENAR LA INFORMACION DEL REPRESENTANTE
            $dataaux2 = datos_clientes($conexion, $dataaux[2]['representante']);
            if ($dataaux2[0] == 0) {
                return $dataaux2;
            }
            $data2[2]['representante'] = $dataaux2[2];
        }
    } else {
        $data2[2]['representante'] = [];
    }
    unset($dataaux);
    unset($dataaux2);

    //INFOECONOMICA
    $dataaux = datos_infoEconomica($conexion, $idcliente);
    if ($dataaux[0] == 0) {
        return $dataaux;
    }
    $data2[0] = $dataaux[0];
    $data2[1] = $dataaux[1];
    $data2[2]['infoEconomica'] = $dataaux[2];

    return $data2;

    unset($dataaux);
    unset($data2);
}

function datos_clientes($conexion, $idcliente)
{
    //PRIMERA PARTE DEL CLIENTE
    $data2[] = [];
    $consultar = "SELECT cl.primer_last AS primerApellido, cl.segundo_last AS segundoApellido, cl.casada_last AS apellidoCasada, cl.primer_name AS primerNombre, cl.segundo_name AS segundoNombre, cl.tercer_name AS otrosNombres, cl.date_birth AS fechaNacimiento FROM tb_cliente cl WHERE cl.idcod_cliente='" . $idcliente . "'";
    $dataaux = consultar_datos($consultar, $conexion, 4);
    if ($dataaux[0] == 0) {
        return $dataaux;
    }

    $data2[0] = $dataaux[0];
    $data2[1] = $dataaux[1];
    foreach ($dataaux[2] as $key => $value) {
        $data2[2][$key] = $value;
    }
    $data2[2]['fechaNacimiento'] = str_replace('-', '', $data2[2]['fechaNacimiento']);

    //CONSULTAR NACIONALIDADES
    $consultar = "SELECT cl.nacionalidad, cl.otra_nacion FROM tb_cliente cl WHERE cl.idcod_cliente='" . $idcliente . "'";
    $dataaux = consultar_datos($consultar, $conexion, 5);
    if ($dataaux[0] == 0) {
        return $dataaux;
    }

    $data2[0] = $dataaux[0];
    $data2[1] = $dataaux[1];
    $i = 0;
    foreach ($dataaux[2] as $key => $value) {
        if ($value != 'nacionalidad2') {
            $data2[2]['nacionalidades'][$i] = $value;
            $i++;
        }
    }

    //CONSULTAR LUGAR DE NACIMIENTO
    $consultar = "SELECT cl.pais_nacio AS pais, cl.depa_nacio AS departamento, cl.muni_nacio AS municipio FROM tb_cliente cl WHERE cl.idcod_cliente='" . $idcliente . "'";
    $dataaux = consultar_datos($consultar, $conexion, 6);
    if ($dataaux[0] == 0) {
        return $dataaux;
    }

    $data2[0] = $dataaux[0];
    $data2[1] = $dataaux[1];
    foreach ($dataaux[2] as $key => $value) {
        $data2[2]['lugar'][$key] = $value;
    }
    $data2[2]['lugar']['departamento'] =  substr($data2[2]['lugar']['municipio'], 0, 2);

    //CONSULTAR CONDICION MIGRATORIA HASTA NUMERO DOCUMENTO IDENTIFICACION
    $consultar = "SELECT '' AS condicionMigratoria, '' AS otraCondicionMigratoria, cl.genero AS sexo, cl.estado_civil AS estadoCivil, cl.profesion AS profesionOficio, cl.type_doc AS tipoDocumentoIdentificacion, cl.no_identifica AS numeroDocumentoIdentificacion, cl.pais_extiende AS emisionPasaporte, cl.no_tributaria AS nit, '' AS telefonos, cl.email AS email, cl.aldea_reside AS direccionResidencia, '' AS residencia, cl.PEP AS PEP, cl.CPE AS CPE FROM tb_cliente cl WHERE cl.idcod_cliente='" . $idcliente . "'";
    $dataaux = consultar_datos($consultar, $conexion, 7);
    if ($dataaux[0] == 0) {
        return $dataaux;
    }

    $data2[0] = $dataaux[0];
    $data2[1] = $dataaux[1];
    foreach ($dataaux[2] as $key => $value) {
        $data2[2][$key] = $value;
    }
    ($data2[2]['sexo'] == 'M') ? ($data2[2]['sexo'] = 2) : ($data2[2]['sexo'] = 1);
    ($data2[2]['estadoCivil'] == 'CASADO') ? ($data2[2]['estadoCivil'] = 2) : ($data2[2]['estadoCivil'] = 1);
    ($data2[2]['tipoDocumentoIdentificacion'] == 'DPI') ? ($data2[2]['tipoDocumentoIdentificacion'] = 1) : ($data2[2]['tipoDocumentoIdentificacion'] = 2);
    ($data2[2]['PEP'] == 'No') ? ($data2[2]['PEP'] = 2) : ($data2[2]['PEP'] = 1);
    ($data2[2]['CPE'] == 'No') ? ($data2[2]['CPE'] = 2) : ($data2[2]['CPE'] = 1);

    // TELEFONOS
    $consultar = "SELECT cl.tel_no1 AS tel1, cl.tel_no2 AS tel2 FROM tb_cliente cl WHERE cl.idcod_cliente='" . $idcliente . "'";
    $dataaux = consultar_datos($consultar, $conexion, 8);
    if ($dataaux[0] == 0) {
        return $dataaux;
    }

    $data2[0] = $dataaux[0];
    $data2[1] = $dataaux[1];
    $i = 0;
    $data2[2]['telefonos'] = array();
    foreach ($dataaux[2] as $key => $value) {
        $data2[2]['telefonos'][$i] = $value;
        $i++;
    }

    // RESIDENCIA
    $consultar = "SELECT '' AS pais, cl.depa_reside AS departamento, cl.muni_reside AS municipio FROM tb_cliente cl WHERE cl.idcod_cliente='" . $idcliente . "'";
    $dataaux = consultar_datos($consultar, $conexion, 9);
    if ($dataaux[0] == 0) {
        return $dataaux;
    }

    $data2[0] = $dataaux[0];
    $data2[1] = $dataaux[1];
    $data2[2]['residencia'] = array();
    foreach ($dataaux[2] as $key => $value) {
        $data2[2]['residencia'][$key] = $value;
    }
    $data2[2]['residencia']['departamento'] =  substr($data2[2]['residencia']['municipio'], 0, 2);
    return $data2;

    unset($dataaux);
    unset($data2);
}

function datos_infoEconomica($conexion, $idcliente)
{
    //CONSULTAR BASE
    $data2[] = [];
    $consultar = "SELECT SUM(ti.sueldo_base) AS montoIngresos, '' AS fuentesIngresos, (SELECT cl.relac_propo FROM tb_cliente cl WHERE cl.idcod_cliente='$idcliente') AS propositoRC FROM tb_ingresos ti WHERE ti.id_cliente='" . $idcliente . "'";
    $dataaux = consultar_datos($consultar, $conexion, 10);
    if ($dataaux[0] == 0) {
        return $dataaux;
    }

    $data2[0] = $dataaux[0];
    $data2[1] = $dataaux[1];
    foreach ($dataaux[2] as $key => $value) {
        $data2[2][$key] = $value;
    }

    //CONSULTAR FUENTES INGRESOS
    $consultar = "SELECT ti.Tipo_ingreso AS fuente, ti.nombre_empresa AS nombre FROM tb_ingresos ti WHERE ti.id_cliente='" . $idcliente . "'";
    $dataaux = consultar_datos_plus($consultar, $conexion, 11);
    if ($dataaux[0] == 0) {
        return $dataaux;
    }

    $data2[0] = $dataaux[0];
    $data2[1] = $dataaux[1];
    $i = 0;
    $data2[2]['fuentesIngresos'] = array();
    if ($dataaux[1] == 1) {
        foreach ($dataaux[2] as $key => $item) {
            $data2[2]['fuentesIngresos'][$key]['fuente'] = $item['fuente'];
            if ($item['fuente'] == 1) {
                $data2[2]['fuentesIngresos'][$key]['informacion']['nombreComercial'] = $item['nombre'];
            } elseif ($item['fuente'] == 2) {
                $data2[2]['fuentesIngresos'][$key]['informacion']['nombreEmpleador'] = $item['nombre'];
            } else {
                $data2[2]['fuentesIngresos'][$key]['informacion']['otrasFuentesIngreso'] = $item['nombre'];
            }
        }
    }
    return $data2;

    unset($dataaux);
    unset($data2);
}

function datos_productos($conexion, $idcliente)
{
    //CONSULTAR PRODUCTOS DE AHORRO
    $data2[] = [];
    $consultar = "SELECT aho.codigo_usu AS lugar, aho.fecha_apertura AS fecha, 'Ahorro' AS tipo, aht.nombre AS nombre, aht.cdescripcion AS descripcion, aho.ccodaho AS identificador, cl.short_name AS nombreContrata, 'GTQ' AS moneda, (SELECT mv.monto AS valor FROM ahommov mv WHERE mv.cestado!=2 AND mv.ccodaho=aho.ccodaho AND mv.ctipope='D' AND mv.correlativo=(SELECT MIN(mv.correlativo) FROM ahommov mv WHERE mv.cestado!=2 AND mv.ccodaho=aho.ccodaho AND mv.ctipope='D')) AS valor, '' AS otrosFirmantes, aho.ccodaho AS beneficiarios, IFNULL(calcularsaldocuentaahom(aho.ccodaho),0)  AS saldo FROM ahomcta aho
    INNER JOIN tb_cliente cl ON aho.ccodcli = cl.idcod_cliente
    INNER JOIN ahomtip aht ON aht.ccodtip = SUBSTR(aho.ccodaho, 7, 2)
    WHERE aho.estado='A' AND cl.idcod_cliente ='" . $idcliente . "'";

    $consultar2 = "SELECT ag.pais, ag.departamento, ag.municipio FROM tb_usuario us 
    INNER JOIN tb_agencia ag ON us.id_agencia=ag.id_agencia
    WHERE us.id_usu";

    $consultar3 = "SELECT bn.nombre AS nombre, bn.dpi AS identificacion, bn.direccion AS direccion, pr.descripcion AS parentesco, bn.fecnac AS nacimiento, bn.porcentaje AS porcentaje, bn.telefono AS telefono FROM ahomben bn 
    INNER JOIN clhpzzvb_bd_general_coopera.tb_parentesco pr ON bn.codparent=pr.id_parent
    WHERE bn.codaho";
    $bandera = false;
    $dataaux = consultar_datos_por_tipos_productos($conexion, $consultar, $consultar2, $consultar3, $bandera);
    if ($dataaux[0] == 0) {
        return $dataaux;
    }

    $data2[0] = $dataaux[0];
    $data2[1] = $dataaux[1];
    $i = 0;
    if ($dataaux[3] != 0) {
        foreach ($dataaux[2] as $key => $item) {
            $data2[2][$i] = $item;
            $i++;
        }
    }
    // unset($dataaux);

    //CONSULTAR PRODUCTOS DE APORTACIONES
    $consultar = "SELECT apr.codigo_usu AS lugar, apr.fecha_apertura AS fecha, 'Aportación' AS tipo, apt.nombre AS nombre, apt.cdescripcion AS descripcion, apr.ccodaport AS identificador, cl.short_name AS nombreContrata, 'GTQ' AS moneda, (SELECT mv.monto AS valor FROM aprmov mv WHERE mv.ccodaport=apr.ccodaport AND cestado!=2 AND mv.ctipope='D' AND mv.correlativo=(SELECT MIN(mv.correlativo) FROM aprmov mv WHERE mv.ccodaport=apr.ccodaport AND mv.ctipope='D' AND cestado!=2)) AS valor, '' AS otrosFirmantes, apr.ccodaport AS beneficiarios, IFNULL(calcularsaldocuentaprt(apr.ccodaport),0) AS saldo FROM aprcta apr
    INNER JOIN tb_cliente cl ON apr.ccodcli = cl.idcod_cliente
    INNER JOIN aprtip apt ON apt.ccodtip = apr.ccodtip
    WHERE apr.estado='A' AND cl.idcod_cliente ='" . $idcliente . "'";

    $consultar3 = "SELECT bn.nombre AS nombre, bn.dpi AS identificacion, bn.direccion AS direccion, pr.descripcion AS parentesco, bn.fecnac AS nacimiento, bn.porcentaje AS porcentaje, bn.telefono AS telefono FROM aprben bn 
    INNER JOIN clhpzzvb_bd_general_coopera.tb_parentesco pr ON bn.codparent=pr.id_parent
    WHERE bn.codaport";
    $bandera = false;
    $dataaux = consultar_datos_por_tipos_productos($conexion, $consultar, $consultar2, $consultar3, $bandera);
    if ($dataaux[0] == 0) {
        return $dataaux;
    }

    $data2[0] = $dataaux[0];
    $data2[1] = $dataaux[1];
    if ($dataaux[3] != 0) {
        foreach ($dataaux[2] as $key => $item) {
            $data2[2][$i] = $item;
            $i++;
        }
    }

    //CONSULTAR PRODUCTOS DE CREDITOS
    $consultar = "SELECT cm.CODAgencia AS lugar, DATE(cm.DfecSol) AS fecha, 'Crédito' AS tipo, pr.nombre AS nombre, pr.descripcion AS descripcion, cm.CCODCTA AS identificador, cl.short_name AS nombreContrata, 'GTQ' AS moneda, cm.MonSug AS valor, '' AS otrosFirmantes, cm.CodCli AS beneficiarios, '' AS saldo FROM cremcre_meta cm
    INNER JOIN cre_productos pr ON cm.CCODPRD= pr.id
    INNER JOIN tb_cliente cl ON cm.CodCli=cl.idcod_cliente
    WHERE cm.Cestado='F' AND cm.CodCli ='" . $idcliente . "'";

    $consultar2 = "SELECT ag.pais, ag.departamento, ag.municipio FROM tb_agencia ag WHERE ag.cod_agenc";

    $consultar3 = "SELECT bn.nombre AS nombre, bn.dpi AS identificacion, bn.direccion AS direccion, pr.descripcion AS parentesco, bn.fecnac AS nacimiento, bn.porcentaje AS porcentaje, bn.telefono AS telefono FROM ahomben bn 
    INNER JOIN clhpzzvb_bd_general_coopera.tb_parentesco pr ON bn.codparent=pr.id_parent
    WHERE bn.codaho";
    $bandera = true;
    $dataaux = consultar_datos_por_tipos_productos($conexion, $consultar, $consultar2, $consultar3, $bandera);
    if ($dataaux[0] == 0) {
        return $dataaux;
    }

    $data2[0] = $dataaux[0];
    $data2[1] = $dataaux[1];
    if ($dataaux[3] != 0) {
        foreach ($dataaux[2] as $key => $item) {
            $data2[2][$i] = $item;
            $i++;
        }
    }

    // //CONSULTAR FUENTES INGRESOS
    $data2[3] = $i; //retornar la I, para ver si tiene algun producto
    return $data2;

    unset($data2);
    unset($dataaux);
}

function consultar_datos_por_tipos_productos($conexion, $consulta, $second_consulta, $third_consulta, $bandera)
{
    //CONSULTAR TIPO DE PRODUCTO
    $data2[] = [];
    $consultar = $consulta;
    $dataaux = consultar_datos_plus($consultar, $conexion, 14);
    if ($dataaux[0] == 0) {
        return $dataaux;
    }

    $data2[0] = $dataaux[0];
    $data2[1] = $dataaux[1];
    $i = 0;
    if ($dataaux[1] == 1) {
        foreach ($dataaux[2] as $key => $item) {
            foreach ($item as $key2 => $value) {
                $data2[2][$i][$key2] = $value;
                if ($key2 == 'lugar') {
                    //CONSULTAR LUGAR
                    $consultar = $second_consulta . "='" . $value . "'";
                    $dataaux2 = consultar_datos($consultar, $conexion, 15);
                    if ($dataaux2[0] == 0) {
                        return $dataaux2;
                    }

                    $data2[0] = $dataaux2[0];
                    $data2[1] = $dataaux2[1];
                    $data2[2][$i][$key2] = array();
                    foreach ($dataaux2[2] as $key3 => $value2) {
                        $data2[2][$i][$key2][$key3] = $value2;
                    }
                }
                if ($key2 == 'fecha') {
                    $data2[2][$i][$key2] = str_replace('-', '', $value);
                }
                if ($key2 == 'valor') {
                    $data2[2][$i][$key2] = number_format($value, 2);
                }
                if ($key2 == 'otrosFirmantes') {
                    $data2[2][$i][$key2] = array();
                }
                if ($key2 == 'beneficiarios') {
                    // CONSULTAR BENEFICIARIOS --ACA SE DEBERIA LLAMAR LA FUNCION DE DATOS TITULARES PERO DE MOMENTO NO
                    if (!$bandera) {
                        $consultar = $third_consulta . "='" . $value . "'";
                        $dataaux2 = consultar_datos_plus($consultar, $conexion, 16);
                        if ($dataaux2[0] == 0) {
                            return $dataaux2;
                        }

                        $data2[0] = $dataaux2[0];
                        $data2[1] = $dataaux2[1];
                        $data2[2][$i][$key2] = array();
                        if ($dataaux2[1] == 1) {
                            foreach ($dataaux2[2] as $key3 => $value2) {
                                $data2[2][$i][$key2][$key3] = $value2;
                                if ($key3 == 'nacimiento') {
                                    $data2[2][$i][$key2][$key3] = str_replace('-', '', $value2);
                                }
                            }
                        }
                    } else {
                        $dataaux2 = datos_titulares($conexion, $value);
                        if ($dataaux2[0] == 0) {
                            return $dataaux2;
                        }

                        $data2[0] = $dataaux2[0];
                        $data2[1] = $dataaux2[1];
                        $data2[2][$i][$key2] = array();
                        $data2[2][$i][$key2] = $dataaux2[2];
                    }
                }
                if ($key2 == 'saldo') {
                    unset($data2[2][$i][$key2]);
                }
            }
            $i++;
        }
    }

    //CONSULTAR FUENTES INGRESOS
    $data2[3] = $i; //retornar la I, para ver si tiene algun producto
    return $data2;

    unset($dataaux);
    unset($data2);
}

function datos_perfil_economico($conexion, $idcliente)
{
    $data2[] = [];
    $consultar = "SELECT (IF((ti.fecha_sys=DATE(ti.created_at)),1,2)) AS actualizacion, DATE(ti.created_at) AS fecha, ti.id_cliente AS negocioPropio, ti.id_cliente AS relacionDependencia, ti.id_cliente AS otrosIngresos, ti.id_cliente AS perfilTransaccional  FROM  tb_ingresos ti
    WHERE ti.id_ingre_dependi=(SELECT ti2.id_ingre_dependi FROM tb_ingresos ti2 WHERE ti2.created_at=(SELECT MAX(ti3.created_at) FROM tb_ingresos ti3 WHERE ti3.id_cliente='" . $idcliente . "') AND ti2.id_cliente='" . $idcliente . "' LIMIT 1)";

    $dataaux = consultar_datos($consultar, $conexion, 17);
    if ($dataaux[0] == 0) {
        return $dataaux;
    }

    $data2[0] = $dataaux[0];
    $data2[1] = $dataaux[1];
    if ($dataaux[1] == 0) {
        $data2[3] = 0;
        return $data2;
    }

    foreach ($dataaux[2] as $key => $value) {
        $data2[2][$key] = $value;
        //FORMATEAR FECHA
        if ($key == 'fecha') {
            $data2[2][$key] = str_replace('-', '', $value);
        }
        //CONSULTAR NEGOCIO PROPIO
        if ($key == 'negocioPropio') {
            $consultar = "SELECT ti.nombre_empresa AS nombreComercial, (SELECT act.Titulo FROM clhpzzvb_bd_general_coopera.tb_ActiEcono act WHERE act.id_ActiEcono=ti.actividad_economica) AS principalActividadEconomica, ti.fecha_labor AS fechaInscripcionNegocio, ti.no_registro AS numeroRegistro, ti.folio AS folio, ti.libro AS libro, ti.direc_negocio AS direccionNegocio, '' AS lugar, 'GT' AS pais, ti.depa_negocio AS departamento, ti.muni_negocio AS municipio, '' AS ingresos, 'GTQ' AS tipoMoneda, ti.sueldo_base AS montoAproximado  
            FROM tb_ingresos ti WHERE ti.Tipo_ingreso=1 AND ti.id_cliente='" . $value . "'";
            $dataaux2 = consultar_datos_plus($consultar, $conexion, 18);
            if ($dataaux2[0] == 0) {
                return $dataaux2;
            }
            $data2[2][$key] = array();
            if ($dataaux2[1] != 0) {
                $data2[2][$key] = procesamiento_3_perfiles_economicos($dataaux2[2], 8, 3, 12, 2);
            }
        }
        //CONSULTAR RELACION DEPENDENCIA
        if ($key == 'relacionDependencia') {
            $consultar = "SELECT ti.sector_Econo AS sector, ti.nombre_empresa AS nombreEmpleador, (SELECT act.Titulo FROM clhpzzvb_bd_general_coopera.tb_ActiEcono act WHERE act.id_ActiEcono=ti.actividad_economica) AS principalActividadEconomicaEmpleador, ti.puesto_ocupa AS puestoDesempeña, ti.direc_negocio AS direccionEmpleador, '' AS lugar, 'GT' AS pais, ti.depa_negocio AS departamento, ti.muni_negocio AS municipio, '' AS ingresos, 'GTQ' AS tipoMoneda, ti.sueldo_base AS montoAproximado  
            FROM tb_ingresos ti WHERE ti.Tipo_ingreso=2 AND ti.id_cliente='" . $value . "'";
            $dataaux2 = consultar_datos_plus($consultar, $conexion, 19);
            if ($dataaux2[0] == 0) {
                return $dataaux2;
            }
            $data2[2][$key] = array();
            if ($dataaux2[1] != 0) {
                $data2[2][$key] = procesamiento_3_perfiles_economicos($dataaux2[2], 6, 3, 10, 2);
            }
        }
        //OTROS INGRESOS
        if ($key == 'otrosIngresos') {
            $consultar = "SELECT ti.condi_negocio AS tiposOtrosIngresos, ti.nombre_empresa AS detalleOtrosIngresos, '' AS ingresos, 'GTQ' AS tipoMoneda, ti.sueldo_base AS montoAproximado  
            FROM tb_ingresos ti WHERE ti.Tipo_ingreso=3 AND ti.id_cliente='" . $value . "'";
            $dataaux2 = consultar_datos_plus($consultar, $conexion, 19);
            if ($dataaux2[0] == 0) {
                return $dataaux2;
            }
            $data2[2][$key] = array();
            if ($dataaux2[1] != 0) {
                $data2[2][$key] = procesamiento_3_perfiles_economicos($dataaux2[2], 0, 0, 3, 2);
            }
        }
        //PERFIL TRANSACCIONAL
        if ($key == 'perfilTransaccional') {
            //AHORROS
            $consultar = "SELECT '' AS fecha, aht.nombre AS productoServicio, 'GTQ' AS tipoMoneda, calcular_saldo_mensual_aho(aho.ccodaho, '" . date('Y-m-d') . "') AS montoPromedioMensual, '' AS principalesUbicacionesGeograficas,
            (SELECT ag.pais FROM tb_usuario us INNER JOIN tb_agencia ag ON us.id_agencia=ag.id_agencia WHERE us.id_usu=aho.codigo_usu) AS pais,
            (SELECT SUBSTRING(ag.municipio, 1, 2) FROM tb_usuario us INNER JOIN tb_agencia ag ON us.id_agencia=ag.id_agencia WHERE us.id_usu=aho.codigo_usu) AS departamento,
            (SELECT ag.municipio FROM tb_usuario us INNER JOIN tb_agencia ag ON us.id_agencia=ag.id_agencia WHERE us.id_usu=aho.codigo_usu) AS municipio
            FROM ahomcta aho
            INNER JOIN tb_cliente cl ON aho.ccodcli = cl.idcod_cliente
            INNER JOIN ahomtip aht ON aht.ccodtip = SUBSTR(aho.ccodaho, 7, 2)
            WHERE aho.estado='A' AND cl.idcod_cliente='" . $value . "'";
            $dataaux2 = consultar_datos_plus($consultar, $conexion, 20);
            if ($dataaux2[0] == 0) {
                return $dataaux2;
            }
            $data2[2][$key] = array();
            $i = 0;
            $data3 = array();
            if ($dataaux2[1] != 0) {
                $data3 = procesamiento_perfilTransaccional($dataaux2[2], 5, 3);
                foreach ($data3 as $key2 => $item2) {
                    $data2[2][$key][$i] = $item2;
                    $i++;
                }
            }
            // APORTACIONES
            $consultar = "SELECT '' AS fecha, apt.nombre AS productoServicio, 'GTQ' AS tipoMoneda, calcular_saldo_mensual_aprt(apr.ccodaport, '" . date('Y-m-d') . "') AS montoPromedioMensual, '' AS principalesUbicacionesGeograficas,
            (SELECT ag.pais FROM tb_usuario us INNER JOIN tb_agencia ag ON us.id_agencia=ag.id_agencia WHERE us.id_usu=apr.codigo_usu) AS pais,
            (SELECT SUBSTRING(ag.municipio, 1, 2) FROM tb_usuario us INNER JOIN tb_agencia ag ON us.id_agencia=ag.id_agencia WHERE us.id_usu=apr.codigo_usu) AS departamento,
            (SELECT ag.municipio FROM tb_usuario us INNER JOIN tb_agencia ag ON us.id_agencia=ag.id_agencia WHERE us.id_usu=apr.codigo_usu) AS municipio
            FROM aprcta apr
            INNER JOIN tb_cliente cl ON apr.ccodcli = cl.idcod_cliente
            INNER JOIN aprtip apt ON apt.ccodtip = apr.ccodtip
            WHERE apr.estado='A' AND cl.idcod_cliente ='" . $value . "'";
            $dataaux2 = consultar_datos_plus($consultar, $conexion, 20);
            if ($dataaux2[0] == 0) {
                return $dataaux2;
            }
            $data3 = array();
            if ($dataaux2[1] != 0) {
                $data3 = procesamiento_perfilTransaccional($dataaux2[2], 5, 3);
                foreach ($data3 as $key2 => $item2) {
                    $data2[2][$key][$i] = $item2;
                    $i++;
                }
            }
            // CREDITOS
            $consultar = "SELECT '' AS fecha, pr.nombre AS nombre, 'GTQ' AS tipoMoneda, calcular_saldo_mensual_creditos(cm.CCODCTA, '" . date('Y-m-d') . "') AS montoPromedioMensual, '' AS principalesUbicacionesGeograficas,
            (SELECT ag.pais FROM tb_agencia ag WHERE ag.cod_agenc=cm.CODAgencia) AS pais,
            (SELECT SUBSTRING(ag.municipio, 1, 2) FROM tb_agencia ag WHERE ag.cod_agenc=cm.CODAgencia) AS departamento,
            (SELECT ag.municipio FROM tb_agencia ag WHERE ag.cod_agenc=cm.CODAgencia) AS municipio
            FROM cremcre_meta cm
            INNER JOIN cre_productos pr ON cm.CCODPRD= pr.id
            WHERE cm.Cestado='F' AND cm.CodCli='" . $value . "'";
            $dataaux2 = consultar_datos_plus($consultar, $conexion, 20);
            if ($dataaux2[0] == 0) {
                return $dataaux2;
            }
            $data3 = array();
            if ($dataaux2[1] != 0) {
                $data3 = procesamiento_perfilTransaccional($dataaux2[2], 5, 3);
                foreach ($data3 as $key2 => $item2) {
                    $data2[2][$key][$i] = $item2;
                    $i++;
                }
            }

            unset($data3);
        }
    }

    $data2[3] = 1;
    return $data2;

    unset($data2);
    unset($dataaux);
}

function procesamiento_3_perfiles_economicos($dataaux, $start1, $end1, $start2, $end2)
{
    foreach ($dataaux as $key2 => $item) {
        foreach ($item as $key3 => $value2) {
            $dataaux[$key2][$key3] = $value2;
            if ($key3 == 'lugar') {
                $dataaux[$key2][$key3] = agrupar_keys($item, $start1, $end1);
            }
            if ($key3 == 'ingresos') {
                $dataaux[$key2][$key3] = agrupar_keys($item, $start2, $end2);
            }
        }
        $eliminar = array(array_slice(array_keys($item), $start1, $end1), array_slice(array_keys($item), $start2, $end2));
        foreach ($eliminar[0] as $clave) {
            unset($dataaux[$key2][$clave]);
        }
        foreach ($eliminar[1] as $clave) {
            unset($dataaux[$key2][$clave]);
        }
    }
    return $dataaux;
    unset($dataaux);
}

function agrupar_keys($item, $start, $end)
{
    $keys = array_slice(array_keys($item), $start, $end);
    $values = array_slice($item, $start, $end);
    return array_combine($keys, $values);
}

function procesamiento_perfilTransaccional($dataaux, $start1, $end1)
{
    foreach ($dataaux as $key2 => $item) {
        foreach ($item as $key3 => $value2) {
            $dataaux[$key2][$key3] = $value2;
            if ($key3 == 'fecha') {
                $dataaux[$key2][$key3] = str_replace('-', '', date('Y-m-d'));
            }
            if ($key3 == 'principalesUbicacionesGeograficas') {
                $dataaux[$key2][$key3] = agrupar_keys($item, $start1, $end1);
            }
        }
        $eliminar = array(array_slice(array_keys($item), $start1, $end1));
        foreach ($eliminar[0] as $clave) {
            unset($dataaux[$key2][$clave]);
        }
    }
    return $dataaux;
    unset($dataaux);
}