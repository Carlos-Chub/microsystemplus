<?php
session_start();
$usuario = $_SESSION["id"];
$id_agencia = $_SESSION['id_agencia'];
date_default_timezone_set('America/Guatemala');
$hoy = date("Y-m-d");
$hoy2 = date("Y-m-d H:i:s");
include '../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');
include '../../../src/funcphp/valida.php';


$condi = $_POST["condi"];
switch ($condi) {
    case 'solicitud':
        $datpost = $_POST["xtra"];
        $extra = $datpost[0];
        $bandera = "fasdfsadf";
        $cicloact = 0;
        if ($extra != 0) {
            $existentes[] = [];
            $compr = mysqli_query($conexion, 'SELECT crems.CCODCTA,cli.short_name, crems.Cestado, crems.NCiclo FROM cremcre_meta crems 
            INNER JOIN tb_cliente cli ON cli.idcod_cliente=crems.CodCli
            WHERE crems.CCodGrupo="' . $extra . '" AND crems.TipoEnti="GRUP" AND (crems.Cestado="A" OR crems.Cestado="D" OR crems.Cestado="E")');
            $k = 0;
            $bandera = "";
            while ($dd = mysqli_fetch_array($compr, MYSQLI_ASSOC)) {
                $existentes[$k] = $dd;
                $bandera = "Grupo Con Creditos en Proceso, debe cancelarlos o proseguir con los mismos";
                $k++;
            }

            if ($bandera == "") {
                //CREDITOS DEL GRUPO
                $datos[] = [];
                $datagrup = mysqli_query($conexion, 'SELECT grup.id_grupos,grup.codigo_grupo,grup.NombreGrupo,grup.direc,cli.idcod_cliente,cli.short_name,cli.url_img,cli.date_birth,cli.genero,cli.estado_civil,cli.no_identifica,cli.no_tributaria
                    FROM tb_cliente_tb_grupo cligr 
                    INNER JOIN tb_grupo grup ON grup.id_grupos=cligr.Codigo_grupo
                    INNER JOIN tb_cliente cli ON cli.idcod_cliente=cligr.cliente_id 
                    WHERE cligr.Codigo_grupo="' . $extra . '" AND cligr.estado=1');
                $bandera = "Grupo sin Integrantes";
                $i = 0;
                while ($da = mysqli_fetch_array($datagrup, MYSQLI_ASSOC)) {
                    $datos[$i] = $da;
                    $i++;
                    $bandera = "";
                }
                //CICLO ACTUAL DEL GRUPO, NO ES DEL CLIENTE

                if ($bandera == "") {
                    $datacre = mysqli_query($conexion, 'SELECT MAX(crems.NCiclo) cicloact FROM cremcre_meta crems WHERE crems.CCodGrupo=' . $extra . '');
                    while ($da = mysqli_fetch_array($datacre, MYSQLI_ASSOC)) {
                        $cicloact = $da["cicloact"];
                    }
                    $cicloact = ($cicloact == NULL) ? 0 : $cicloact;
                }
            }
        }

?>
        <input type="text" readonly hidden value='solicitud' id='condi'>
        <input type="text" readonly hidden value='grup001' id='file'>
        <div class="card crdbody contenedort">
            <div class="card-header" style="text-align:left">
                <h4>SOLICITUD DE CREDITOS GRUPAL</h4>
            </div>
            <div class="card-body">
                <div class="row contenedort">
                    <h5>Detalle de Grupo</h5>
                    <div class="row mb-3">
                        <div class="col-sm-5">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <span class="input-group-addon col-8">Nombre Grupo</span>
                                <?php if ($bandera == "") echo '<span style="font-size:1rem;width:min(25rem,90%);" class="badge rounded-pill text-bg-success">' . $datos[0]["NombreGrupo"] . '</span>'; ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <label for="codgrup" class="input-group-addon">Codigo de Grupo</label>
                                <?php if ($bandera == "") echo '<span style="font-size:1rem;width:min(9rem,90%);" class="badge rounded-pill text-bg-success">' . $datos[0]["codigo_grupo"] . '</span>'; ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <br>
                            <button type="button" onclick="loadconfig('all','all')" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#buscargrupo">
                                <i class="fa-solid fa-magnifying-glass"></i> Buscar Grupo
                            </button>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <span class="input-group-addon col-8">Direccion</span>
                                <?php if ($bandera == "") echo '<span style="font-size:1rem;width:min(25rem,90%);" class="badge rounded-pill text-bg-success">' . $datos[0]["direc"] . '</span>'; ?>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <label for="nciclo" class="input-group-addon">Ciclo</label>
                                <input type="number" class="form-control " id="nciclo" readonly value="<?php echo $cicloact + 1; ?>">
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <label class="input-group-addon fw-bold">Analista</label>

                            <select class="form-select" name="" id="codanal">
                                <?php
                                //$consulta = mysqli_query($conexion, "SELECT CONCAT(nombre, ' ', apellido) AS nameusu , id_usu FROM tb_usuario WHERE puesto='ANA' AND id_agencia IN( SELECT id_agencia FROM tb_usuario WHERE id_usu=$usuario)");
                                $consulta = mysqli_query($conexion, "SELECT CONCAT(nombre, ' ', apellido) AS nameusu , id_usu FROM tb_usuario WHERE puesto='ANA'");
                                echo '<option value="0" selected disabled>Seleccione un Asesor</option>';
                                while ($dtas = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                    $nombre = $dtas["nameusu"];
                                    $id_usu = $dtas["id_usu"];
                                    echo '<option value="' . $id_usu . '">' . $nombre . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <?php if ($bandera != "" && $extra != "0") {
                        echo '<div class="alert alert-danger col-7" role="alert">' . $bandera . '';
                        if ($k > 0) {
                            echo '<ol class="list-group list-group-numbered">';
                            $i = 0;
                            $estados = ['A' => 'SOLICITUD', 'D' => 'ANALISIS', 'E' => 'APROBACION'];
                            while ($i < count($existentes)) {
                                echo '<li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                  <div class="fw-bold">' . $existentes[$i]["CCODCTA"] . '</div>
                                  ' . $existentes[$i]["short_name"] . '
                                </div>
                                <span class="badge bg-danger rounded-pill">Estado: ' . $estados[$existentes[$i]["Cestado"]] . '</span>
                                <span class="badge bg-primary rounded-pill">Ciclo: ' . $existentes[$i]["NCiclo"] . '</span>
                              </li>';
                                $i++;
                            }
                            echo '</ol>';
                        }
                        echo '</div>';
                    }
                    ?>
                </div>
                <div class="row contenedort" style="background-image: url(https://mdbootstrap.com/img/Photos/new-templates/glassmorphism-article/img9.jpg);">
                    <h5>CIENTES DEL GRUPO</h5>
                    <div class="accordion" id="cuotas">
                        <?php
                        if ($bandera == "") {
                            $j = 0;
                            while ($j < count($datos)) {
                                $codcli = $datos[$j]["idcod_cliente"];
                                $name = $datos[$j]["short_name"];
                                $fecnac = date("d-m-Y", strtotime($datos[$j]["date_birth"]));
                                $urlimg = $datos[$j]["url_img"];
                                $genero = $datos[$j]["genero"];
                                $estadocivil = $datos[$j]["estado_civil"];
                                $dpi = $datos[$j]["no_identifica"];
                                $nit = $datos[$j]["no_tributaria"];
                                $idit = "data" . $j;
                                $imgurl = __DIR__ . '/../../../../../' . $urlimg;
                                if (!is_file($imgurl)) {
                                    $src = '../../includes/img/fotoClienteDefault.png';
                                } else {
                                    $imginfo   = getimagesize($imgurl);
                                    $mimetype  = $imginfo['mime'];
                                    $imageData = base64_encode(file_get_contents($imgurl));
                                    $src = 'data:' . $mimetype . ';base64,' . $imageData;
                                }

                        ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <div class="row">
                                            <div class="col-12">
                                                <button id="<?php echo 'bt' . $j; ?>" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#<?php echo $idit; ?>" aria-expanded="true" aria-controls="<?php echo $idit; ?>">
                                                    <div class="row" style="width:100%;font-size: 0.90rem;">
                                                        <div class="col-2">
                                                            <img width="80" height="80" id="vistaPrevia" src="<?php echo $src; ?>" /><br />
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <span class="input-group-addon"><?php echo $codcli; ?></span>
                                                                </div>
                                                                <div class="col-12">
                                                                    <input id="<?php echo 'ccodcli' . $j; ?>" type="text" value="<?php echo $codcli; ?>" hidden>
                                                                    <span class="input-group-addon"><?php echo strtoupper($name); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="row">
                                                                <span class="input-group-addon">Identificacion</span>
                                                                <span class="input-group-addon"><?php echo $dpi; ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-2">
                                                            <div class="row">
                                                                <span class="input-group-addon">Fecha Nacimiento</span>
                                                                <span class="input-group-addon"><?php echo $fecnac; ?></span>
                                                            </div>

                                                        </div>
                                                        <div class="col-2">
                                                            <div class="row">
                                                                <span class="input-group-addon">Genero</span>
                                                                <span class="input-group-addon"><?php echo $genero; ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                    </h2>
                                    <div id="<?php echo $idit; ?>" class="accordion-collapse collapse" data-bs-parent="#cuotas">
                                        <div class="accordion-body">
                                            <div class="row mb-3" style="font-size: 0.90rem;">
                                                <div class="col-sm-6">
                                                    <label class="input-group-addon fw-bold">Monto Solicitado</label>
                                                    <input type="number" step="0.01" class="form-control" placeholder="000.00" id="<?php echo 'monsol' . $j; ?>">
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="input-group-addon fw-bold">Destino Credito</label>
                                                    <select class="form-select" name="" id="<?php echo 'descre' . $j; ?>">
                                                        <?php DestinoCre($general); ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mb-3" style="font-size: 0.90rem;">
                                                <div class="col-sm-6">
                                                    <label class="input-group-addon fw-bold">Sector Economico</label>
                                                    <select class="form-select" name="" id="<?php echo 'sectorecono' . $j; ?>" onchange="SctrEcono('#<?php echo 'actecono' . $j; ?>', this.value,'#ActvEcn')">
                                                        <option value="0">Seleccionar un sector Economico</option>
                                                        <?php
                                                        $sect = mysqli_query($general, "SELECT id_SectoresEconomicos, SectoresEconomicos FROM `tb_sectoreseconomicos`");
                                                        while ($sse = mysqli_fetch_array($sect, MYSQLI_ASSOC)) {
                                                            $idSctr = $sse["id_SectoresEconomicos"];
                                                            $SctrEcono = $sse["SectoresEconomicos"];
                                                            echo '<option value="' . $idSctr . '">' . $SctrEcono . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="input-group-addon fw-bold">Actividad Economica</label>
                                                    <input type="text" class="form-control" id="ActvEcn" readonly hidden>
                                                    <select class="form-select" name="" id="<?php echo 'actecono' . $j; ?>">
                                                        <option value="0" selected disabled>Seleccione Actividad Economica</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php
                                $j++;
                            }
                        }
                        ?>

                    </div>
                </div>
            </div>
            <div class="row justify-items-md-center">
                <div class="col align-items-center" id="modal_footer">
                    <?php
                    if ($bandera == "") {
                        echo '<button type="button" class="btn btn-outline-success" onclick="savesol(' . ($j - 1) . ',' . $usuario . ',' . $extra . ',`' . $_SESSION['agencia'] . '`)">
                            <i class="fa fa-floppy-disk"></i> Guardar
                        </button>';
                    }
                    ?>
                    <button type="button" class="btn btn-outline-danger" onclick="printdiv2('#cuadro', '0')">
                        <i class="fa-solid fa-ban"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="salir()">
                        <i class="fa-solid fa-circle-xmark"></i> Salir
                    </button>
                    <!--                      <button onclick="reportes([[], [], [], [archivo[0], archivo[1]]], `pdf`, `ficha_aprobacion`, 0)">asdfas</button> -->
                </div>
            </div>
        </div>
    <?php
        break;
    case 'analisis':
        $datpost = $_POST["xtra"];
        /*         echo ('<pre>');
        print_r($datpost);
        echo ('</pre>'); */
        $extra = $datpost[0];
        $bandera = "Grupo sin cuentas por Analizar";
        if ($extra != "0") {
            $numciclo = $datpost[1];
            //CREDITOS DEL GRUPO
            $datos[] = [];
            $datacre = mysqli_query($conexion, 'SELECT gru.NombreGrupo,gru.direc,gru.codigo_grupo,cli.url_img, cli.short_name,cli.idcod_cliente,cli.date_birth,cli.no_identifica, 
            cre.CCODCTA,cre.Cestado,cre.NCiclo,cre.MontoSol,cre.CodAnal,cre.CCODPRD,cre.CtipCre,cre.NtipPerC,cre.DfecPago,cre.noPeriodo,cre.Dictamen,cre.MonSug,cre.DFecDsbls,cre.NIntApro
            From cremcre_meta cre
            INNER JOIN tb_cliente cli ON cli.idcod_cliente=cre.CodCli
            INNER JOIN tb_grupo gru ON gru.id_grupos=cre.CCodGrupo
            WHERE cre.TipoEnti="GRUP" AND (cre.CESTADO="A" OR cre.CESTADO="D") AND cre.CCodGrupo="' . $extra . '" AND cre.NCiclo=' . $numciclo);

            $i = 0;
            while ($da = mysqli_fetch_array($datacre, MYSQLI_ASSOC)) {
                $datos[$i] = $da;
                $i++;
                $bandera = "";
            }
        }
        $firstcuota = ($bandera == "" && $datos[0]["Cestado"] == "D") ? $datos[0]["DfecPago"] : $hoy;
        $fecdes = ($bandera == "" && $datos[0]["Cestado"] == "D") ? $datos[0]["DFecDsbls"] : $hoy;
    ?>
        <input type="text" readonly hidden value='analisis' id='condi'>
        <input type="text" readonly hidden value='grup001' id='file'>
        <div class="card crdbody contenedort">
            <div class="card-header" style="text-align:left">
                <h4>ANALISIS DE CREDITOS GRUPAL</h4>
            </div>
            <div class="card-body">
                <div class="row contenedort">
                    <h5>Detalle de Grupo</h5>
                    <div class="row mb-3">
                        <div class="col-sm-5">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <span class="input-group-addon col-8">Nombre Grupo</span>
                                <?php if ($bandera == "") echo '<span style="font-size:1rem;width:min(25rem,90%);" class="badge rounded-pill text-bg-success">' . $datos[0]["NombreGrupo"] . '</span>'; ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <label for="codgrup" class="input-group-addon">Codigo de Grupo</label>
                                <?php if ($bandera == "") echo '<span style="font-size:1rem;width:min(9rem,90%);" class="badge rounded-pill text-bg-success">' . $datos[0]["codigo_grupo"] . '</span>'; ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <br>
                            <button id="findgrupo" onclick="loadconfig('any',['A','D'])" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#buscargrupo">
                                <i class="fa-solid fa-magnifying-glass"></i> Buscar Grupo
                            </button>
                        </div>

                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <span class="input-group-addon col-8">Direccion</span>
                                <?php if ($bandera == "") echo '<span style="font-size:1rem;width:min(25rem,90%);" class="badge rounded-pill text-bg-success">' . $datos[0]["direc"] . '</span>'; ?>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <label for="nciclo" class="input-group-addon">Ciclo</label>
                                <input type="number" class="form-control" id="nciclo" readonly value="<?php if ($bandera == "") echo $numciclo; ?>">
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <label class="input-group-addon fw-bold">Analista</label>

                            <select class="form-select" name="" id="codanal">
                                <?php
                                //$consulta = mysqli_query($conexion, "SELECT CONCAT(nombre, ' ', apellido) AS nameusu , id_usu FROM tb_usuario WHERE puesto='ANA' AND id_agencia IN( SELECT id_agencia FROM tb_usuario WHERE id_usu=$usuario)");
                                $consulta = mysqli_query($conexion, "SELECT CONCAT(nombre, ' ', apellido) AS nameusu , id_usu FROM tb_usuario WHERE puesto='ANA'");
                                echo '<option value="0">Seleccione un Asesor</option>';
                                $selected = "";
                                while ($dtas = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                    $nombre = $dtas["nameusu"];
                                    $id_usu = $dtas["id_usu"];
                                    $selected = ($datos[0]["CodAnal"] == $id_usu) ? " selected" : "";
                                    echo '<option value="' . $id_usu . '" ' . $selected . '>' . $nombre . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <?php

                            ?>
                        </div>
                    </div>
                    <?php if ($extra != "0" && $bandera != "") {
                        echo '<div class="alert alert-danger" role="alert">' . $bandera . '</div>';
                    } else if ($extra != "0") {
                        $est = ($datos[0]["Cestado"] == "D") ? " ANALIZADO" : "SOLICITADO";
                        echo 'ESTADO: ' . $est;
                    }
                    ?>
                </div>
                <div class="row contenedort">
                    <div class="card-body">
                        <div class="row crdbody">
                            <div class="form-group col-md-3">
                                <button type="button" class="btn btn-outline-primary" title="Buscar Grupo" onclick="abrir_modal('#findcredlin', '#id_modal_hidden', 'idprod,codprod,nameprod,descprod,tasaprod,maxprod,fondo/A,A,A,A,A,A,A/'+'/#/#/#/#')">
                                    <i class="fa-solid fa-magnifying-glass"> </i>Buscar Linea de Credito </button>
                            </div>
                        </div>
                        <div class="alert alert-primary" role="alert">
                            <?php
                            $prd = 0;
                            if ($bandera == "" && $datos[0]["Cestado"] == "D") {
                                $dprod[] = [];
                                $codproducto = $datos[0]['CCODPRD'];
                                $qe = mysqli_query($conexion, "SELECT pro.id,pro.cod_producto,pro.nombre nompro,pro.descripcion descriprod,ff.descripcion fondesc,pro.tasa_interes, pro.monto_maximo
                                FROM cre_productos pro
                                INNER JOIN ctb_fuente_fondos ff ON ff.id=pro.id_fondo WHERE pro.estado=1 AND pro.id=" . $codproducto . "");
                                $k = 0;
                                while ($da = mysqli_fetch_array($qe, MYSQLI_ASSOC)) {
                                    $dprod[$k] = $da;
                                    $k++;
                                    $prd = 1;
                                }
                            }
                            ?>

                            <div class="row crdbody">
                                <div class="col-sm-3">
                                    <div class="">
                                        <span class="fw-bold">Codigo Producto</span>
                                        <input type="number" class="form-control" id="idprod" value="<?php if ($prd == 1) echo $dprod[0]["id"]; ?>" readonly hidden>
                                        <input type="text" class="form-control" id="codprod" value="<?php if ($prd == 1) echo $dprod[0]["cod_producto"]; ?>" readonly>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <span class="fw-bold">Nombre</span>
                                    <input type="text" class="form-control" id="nameprod" readonly value="<?php if ($prd == 1) echo $dprod[0]["nompro"]; ?>">
                                </div>
                                <div class="form-group col-sm-3">
                                    <span class="fw-bold">%Interes Asignado</span>
                                    <input type="number" step="0.01" class="form-control" id="tasaprod" value="<?php if ($bandera == "") echo $datos[0]["NIntApro"]; ?>">
                                </div>
                            </div>
                            <div class="row crdbody">
                                <div class="form-group col-sm-6">
                                    <span class="fw-bold">Descripción</span>
                                    <input type="text" class="form-control" id="descprod" readonly value="<?php if ($prd == 1) echo $dprod[0]["descriprod"]; ?>">
                                </div>

                                <div class=" col-sm-3">
                                    <span class="fw-bold">Monto Maximo</span>
                                    <input type="number" step="0.01" class="form-control" id="maxprod" readonly value="<?php if ($prd == 1) echo $dprod[0]["monto_maximo"]; ?>">
                                </div>
                                <div class="col-sm-3">
                                    <span class="fw-bold">Fuente de fondos</span>
                                    <input type="text" class="form-control" id="fondo" readonly value="<?php if ($prd == 1) echo $dprod[0]["fondesc"]; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row contenedort">
                    <div class="col-12">
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <span class="fw-bold">Tipo de Crédito</span>
                                <select id="tipcre" class="form-select" onchange="creperi('tpscre2','#alrtpnl','cre_indi_01',this.value)">
                                    <option value="0" selected disabled>Seleccione tipo de Crédito</option>
                                    <?php tpscre(); ?>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <span class="fw-bold">Tipo de Periodo</span>
                                <select id="peri" class="form-select">
                                    <option selected disabled value="0">Seleccionar Tipo de Periodo</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <span class="fw-bold">Fecha primer Cuota</span>
                                <input type="date" class="form-control" id="fecinit" value="<?php echo  $firstcuota; ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <span class="fw-bold">No. Cuotas</span>
                                <input type="number" class="form-control" id="nrocuo" value="<?php if ($bandera == "") echo $datos[0]["noPeriodo"]; ?>">
                            </div>
                            <div class="col-sm-4">
                                <span class="fw-bold">Fecha Desembolso</span>
                                <input type="date" class="form-control" id="fecdes" value="<?php echo $fecdes; ?>">
                            </div>
                            <div class="col-sm-4">
                                <span class="fw-bold">Dictamen??</span>
                                <input type="text" class="form-control" id="dictmn" value="<?php if ($bandera == "") echo $datos[0]["Dictamen"]; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row crdbody">
                        <div class="col-sm-12">
                            <div class="input-group" id="tipsMEns">
                                <div class="alert alert-success" role="alert" id="alrtpnl">
                                    <h4>Seleccione un tipo de crédito </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-items-md-center">
                        <div class="col align-items-center">
                            <?php
                            if ($bandera == "") {
                                echo '  <button type="button" class="btn btn-outline-success" onclick="saveanal(' . (count($datos) - 1) . ',' . $numciclo . ',' . $extra . ',`' . $_SESSION['agencia'] . '`)">
                                            <i class="fa fa-floppy-disk"></i> Guardar Cambios
                                        </button>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="row contenedort" style="background-image: url(https://mdbootstrap.com/img/Photos/new-templates/glassmorphism-article/img9.jpg);">
                    <h5>CIENTES DEL GRUPO</h5>
                    <div class="accordion" id="cuotas">
                        <?php
                        if ($bandera == "") {
                            $j = 0;
                            while ($j < count($datos)) {
                                $ccodta = $datos[$j]["CCODCTA"];
                                $estado = $datos[$j]["Cestado"];
                                $codcli = $datos[$j]["idcod_cliente"];
                                $name = $datos[$j]["short_name"];
                                $fecnac = date("d-m-Y", strtotime($datos[$j]["date_birth"]));
                                $urlimg = $datos[$j]["url_img"];
                                $dpi = $datos[$j]["no_identifica"];
                                $monsol = $datos[$j]["MontoSol"];
                                $monsug = $datos[$j]["MonSug"];
                                $idit = "data" . $j;
                                $imgurl = __DIR__ . '/../../../../../' . $urlimg;
                                if (!is_file($imgurl)) {
                                    $src = '../../includes/img/fotoClienteDefault.png';
                                } else {
                                    $imginfo   = getimagesize($imgurl);
                                    $mimetype  = $imginfo['mime'];
                                    $imageData = base64_encode(file_get_contents($imgurl));
                                    $src = 'data:' . $mimetype . ';base64,' . $imageData;
                                }
                        ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <div class="row">
                                            <div class="col-12">
                                                <button id="<?php echo 'bt' . $j; ?>" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#<?php echo $idit; ?>" aria-expanded="true" aria-controls="<?php echo $idit; ?>">
                                                    <div class="row" style="width:100%;font-size: 0.90rem;">
                                                        <div class="col-2">
                                                            <img width="80" height="80" id="vistaPrevia" src="<?php echo $src; ?>" /><br />
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <span class="input-group-addon"><?php echo $ccodta; ?></span>
                                                                </div>
                                                                <div class="col-12">
                                                                    <input id="<?php echo 'ccodcta' . $j; ?>" type="text" value="<?php echo $ccodta; ?>" hidden>
                                                                    <span class="input-group-addon"><?php echo strtoupper($name); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="row">
                                                                <span class="input-group-addon">Identificacion</span>
                                                                <span class="input-group-addon"><?php echo $dpi; ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-2">
                                                            <div class="row">
                                                                <span class="input-group-addon">Fecha Nacimiento</span>
                                                                <span class="input-group-addon"><?php echo $fecnac; ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                    </h2>
                                    <div id="<?php echo $idit; ?>" class="accordion-collapse collapse" data-bs-parent="#cuotas">
                                        <div class="accordion-body">
                                            <div class="row mb-3" style="font-size: 0.90rem;">
                                                <div class="col-sm-2">
                                                    <label class="input-group-addon fw-bold">Monto Solicitado</label>
                                                    <input type="number" step="0.01" class="form-control" placeholder="000.00" value="<?php echo  $monsol; ?>" disabled>
                                                </div>
                                                <div class="col-sm-2">
                                                    <label class="input-group-addon fw-bold">Monto A Aprobar</label>
                                                    <input id="<?php echo 'monapr' . $j; ?>" type="number" step="0.01" class="form-control" placeholder="000.00" value="<?php echo  $monsug; ?>">
                                                </div>
                                                <div class="col-sm-2">
                                                    <br>
                                                    <?php
                                                    if ($estado == "D") {
                                                        echo '<button type="button" class="btn btn-warning" onclick="reportes([[],[],[],[`' . $ccodta . '`]], `pdf`, `../../cre_indi/reportes/planPago`,0)">Plan de pagos</button>';
                                                    } else {
                                                        echo 'Guarde los cambios para poder visualizar el plan de pago';
                                                    }
                                                    ?>
                                                </div>
                                                <!-- <div class="col-sm-2">
                                                    <br>
                                                    <?php
                                                    // if ($estado == "D") {
                                                    //     echo '<button type="button" class="btn btn-warning" onclick="reportes([[],[],[],[`' . $ccodta . '`]], `pdf`, `20`,0,1)">Dictamen </button>';
                                                    // } else {
                                                    //     echo 'Guarde los cambios para poder visualizar el dictamen';
                                                    // }
                                                    ?>
                                                </div> -->
                                                <div class="col-sm-4">
                                                    <?php
                                                    if ($estado == "D") {
                                                        echo 'Debe guardar cada cambio que haga para visualizar el plan de pagos con los datos actualizados';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php
                                $j++;
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="row justify-items-md-center">
                <div class="col align-items-center" id="modal_footer">
                    <?php
                    if ($bandera == "") {
                        echo '<button type="button" class="btn btn-outline-success" onclick="saveanal(' . ($j - 1) . ',' . $numciclo . ',' . $extra . ',`' . $_SESSION['agencia'] . '`)">
                            <i class="fa fa-floppy-disk"></i> Guardar Cambios
                        </button>';
                    }
                    ?>
                    <button type="button" class="btn btn-outline-danger" onclick="printdiv2('#cuadro', '0')">
                        <i class="fa-solid fa-ban"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="window.location.reload();">
                        <i class="fa-solid fa-circle-xmark"></i> Salir
                    </button>
                </div>
            </div>
        </div>
        <!-- <button type="button" onclick="reportes([[], [], [], ['0070010200000005']], `pdf`, `../../cre_indi/reportes/dictamen`, 0)">ksjdfhjshdjh</button> -->
        <script>
            function update(val1, val2) {
                loaderefect(1);
                dire = "../../views/Creditos/cre_indi/cre_indi_01.php";
                creperi('tpscre2', '#alrtpnl', 'cre_indi_01', val1);
                $("#tipcre option[value='" + val1 + "']").attr("selected", true);
                $.ajax({
                    url: dire,
                    method: "POST",
                    data: {
                        condi: 'prdscre',
                        xtra: val1
                    },
                    success: function(data) {
                        $('#peri').html(data);
                        $("#peri option[value='" + val2 + "']").attr("selected", true);
                        loaderefect(0);
                    }
                })
            }
            <?php
            if ($bandera == "" && $datos[0]["Cestado"] == "D") {
                echo "update('" . $datos[0]["CtipCre"] . "','" . $datos[0]["NtipPerC"] . "');";
            }
            ?>
        </script>
    <?php
        break;
    case 'aprobacion':

        $datpost = $_POST["xtra"];
        /*         echo ('<pre>');
            print_r($datpost);
            echo ('</pre>'); */
        $extra = $datpost[0];
        $bandera = "Grupo sin cuentas por Aprobar";
        if ($extra != "0") {
            $numciclo = $datpost[1];
            //CREDITOS DEL GRUPO
            $datos[] = [];
            $datacre = mysqli_query($conexion, 'SELECT gru.NombreGrupo,gru.direc,gru.codigo_grupo,cli.url_img, cli.short_name,cli.idcod_cliente,cli.date_birth,cli.no_identifica, 
                cre.CCODCTA,cre.Cestado,cre.NCiclo,cre.MontoSol,cre.CodAnal,cre.CCODPRD,cre.CtipCre,cre.NtipPerC,cre.DfecPago,cre.noPeriodo,cre.Dictamen,cre.MonSug,cre.DFecDsbls,cre.NIntApro
                From cremcre_meta cre
                INNER JOIN tb_cliente cli ON cli.idcod_cliente=cre.CodCli
                INNER JOIN tb_grupo gru ON gru.id_grupos=cre.CCodGrupo
                WHERE cre.TipoEnti="GRUP" AND (cre.CESTADO="D" OR cre.CESTADO="D") AND cre.CCodGrupo="' . $extra . '" AND cre.NCiclo=' . $numciclo);

            $i = 0;
            while ($da = mysqli_fetch_array($datacre, MYSQLI_ASSOC)) {
                $datos[$i] = $da;
                $i++;
                $bandera = "";
            }
        }
    ?>
        <input type="text" readonly hidden value='aprobacion' id='condi'>
        <input type="text" readonly hidden value='grup001' id='file'>
        <div class="card crdbody contenedort">
            <div class="card-header" style="text-align:left">
                <h4>APROBACION DE CREDITOS GRUPAL</h4>
            </div>
            <div class="card-body">
                <div class="row contenedort">
                    <h5>Detalle de Grupo</h5>
                    <div class="row mb-3">
                        <div class="col-sm-5">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <span class="input-group-addon col-8">Nombre Grupo</span>
                                <?php if ($bandera == "") echo '<span style="font-size:1rem;width:min(25rem,90%);" class="badge rounded-pill text-bg-success">' . $datos[0]["NombreGrupo"] . '</span>'; ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <label for="codgrup" class="input-group-addon">Codigo de Grupo</label>
                                <?php if ($bandera == "") echo '<span style="font-size:1rem;width:min(9rem,90%);" class="badge rounded-pill text-bg-success">' . $datos[0]["codigo_grupo"] . '</span>'; ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <br>
                            <button id="findgrupo" onclick="loadconfig('any',['D'])" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#buscargrupo">
                                <i class="fa-solid fa-magnifying-glass"></i> Buscar Grupo
                            </button>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <span class="input-group-addon col-8">Direccion</span>
                                <?php if ($bandera == "") echo '<span style="font-size:1rem;width:min(25rem,90%);" class="badge rounded-pill text-bg-success">' . $datos[0]["direc"] . '</span>'; ?>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <label for="nciclo" class="input-group-addon">Ciclo</label>
                                <input type="number" class="form-control" id="nciclo" readonly value="<?php if ($bandera == "") echo $numciclo; ?>">
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <label class="input-group-addon fw-bold">Analista</label>

                            <select class="form-select" name="" id="codanal" disabled>
                                <?php
                                $consulta = mysqli_query($conexion, "SELECT CONCAT(nombre, ' ', apellido) AS nameusu , id_usu FROM tb_usuario WHERE id_agencia IN( SELECT id_agencia FROM tb_usuario WHERE id_usu=$usuario)");
                                echo '<option value="0">Seleccione un Asesor</option>';
                                $selected = "";
                                while ($dtas = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                    $nombre = $dtas["nameusu"];
                                    $id_usu = $dtas["id_usu"];
                                    $selected = ($datos[0]["CodAnal"] == $id_usu) ? " selected" : "";
                                    echo '<option value="' . $id_usu . '" ' . $selected . '>' . $nombre . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <?php if ($extra != "0" && $bandera != "") {
                        echo '<div class="alert alert-danger" role="alert">' . $bandera . '</div>';
                    } else if ($extra != "0") {
                        $est = ($datos[0]["Cestado"] == "D") ? " ANALIZADO" : "SOLICITADO";
                        echo 'ESTADO: ' . $est;
                    }
                    ?>
                </div>
                <div class="row contenedort">
                    <div class="card-body">
                        <div class="alert alert-primary" role="alert">
                            <?php
                            $prd = 0;
                            if ($bandera == "" && $datos[0]["Cestado"] == "D") {
                                $dprod[] = [];
                                $codproducto = $datos[0]['CCODPRD'];
                                $qe = mysqli_query($conexion, "SELECT pro.id,pro.cod_producto,pro.nombre nompro,pro.descripcion descriprod,ff.descripcion fondesc,pro.tasa_interes, pro.monto_maximo
                                FROM cre_productos pro
                                INNER JOIN ctb_fuente_fondos ff ON ff.id=pro.id_fondo WHERE pro.estado=1 AND pro.id=" . $codproducto . "");
                                $k = 0;
                                while ($da = mysqli_fetch_array($qe, MYSQLI_ASSOC)) {
                                    $dprod[$k] = $da;
                                    $k++;
                                    $prd = 1;
                                }
                            }
                            ?>

                            <div class="row crdbody">
                                <div class="col-sm-3">
                                    <div class="">
                                        <span class="fw-bold">Codigo Producto</span>
                                        <input type="number" class="form-control" id="idprod" readonly hidden>
                                        <input type="number" class="form-control" id="codprod" value="<?php if ($prd == 1) echo $dprod[0]["cod_producto"]; ?>" readonly>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <span class="fw-bold">Nombre</span>
                                    <input type="text" class="form-control" id="nameprod" readonly value="<?php if ($prd == 1) echo $dprod[0]["nompro"]; ?>">
                                </div>
                                <div class="form-group col-sm-3">
                                    <span class="fw-bold">%Interes Anual asignado</span>
                                    <input type="number" step="0.01" class="form-control" id="tasaprod" readonly value="<?php if ($bandera == "") echo $datos[0]["NIntApro"]; ?>">
                                </div>
                            </div>
                            <div class="row crdbody">
                                <div class="form-group col-sm-6">
                                    <span class="fw-bold">Descripción</span>
                                    <input type="text" class="form-control" id="descprod" readonly value="<?php if ($prd == 1) echo $dprod[0]["descriprod"]; ?>">
                                </div>

                                <div class=" col-sm-3">
                                    <span class="fw-bold">Monto Maximo</span>
                                    <input type="number" step="0.01" class="form-control" id="maxprod" readonly value="<?php if ($prd == 1) echo $dprod[0]["monto_maximo"]; ?>">
                                </div>
                                <div class="col-sm-3">
                                    <span class="fw-bold">Ahorro</span>
                                    <input type="text" step="0.01" class="form-control" id="fondo" readonly value="<?php if ($prd == 1) echo $dprod[0]["fondesc"]; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row contenedort">
                    <div class="col-12">
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <span class="fw-bold">Tipo de Crédito</span>
                                <input type="text" class="form-control" id="tipcre" value="<?php if ($bandera == "") echo tip_cre_peri($datos[0]["CtipCre"]); ?>" readonly>
                            </div>
                            <div class="col-sm-4">
                                <span class="fw-bold">Tipo de Periodo</span>
                                <input type="text" class="form-control" id="tipper" value="<?php if ($bandera == "") echo tip_cre_peri($datos[0]["NtipPerC"]); ?>" readonly>
                            </div>
                            <div class="col-sm-4">
                                <span class="fw-bold">Fecha primer Cuota</span>
                                <input type="date" class="form-control" id="fecinit" value="<?php if ($bandera == "") echo $datos[0]["DfecPago"]; ?>" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <span class="fw-bold">No. Cuotas</span>
                                <input type="text" class="form-control" id="nrocuo" value="<?php if ($bandera == "") echo $datos[0]["noPeriodo"]; ?>" readonly>
                            </div>
                            <div class="col-sm-4">
                                <span class="fw-bold">Fecha Desembolso</span>
                                <input type="date" class="form-control" id="fecdes" value="<?php if ($bandera == "") echo $datos[0]["DFecDsbls"]; ?>" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6
                            ">
                                <span class="fw-bold">Contrato</span>
                                <select id="contraIndi" class="form-select">
                                    <option selected value="C">Contrato individual</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="row contenedort" style="background-image: url(https://mdbootstrap.com/img/Photos/new-templates/glassmorphism-article/img9.jpg);">
                    <h5>CIENTES DEL GRUPO</h5>
                    <div class="accordion" id="cuotas">
                        <?php
                        if ($bandera == "") {
                            $j = 0;
                            while ($j < count($datos)) {
                                $ccodta = $datos[$j]["CCODCTA"];
                                $estado = $datos[$j]["Cestado"];
                                $codcli = $datos[$j]["idcod_cliente"];
                                $name = $datos[$j]["short_name"];
                                $fecnac = date("d-m-Y", strtotime($datos[$j]["date_birth"]));
                                $urlimg = $datos[$j]["url_img"];
                                $dpi = $datos[$j]["no_identifica"];
                                $monsol = $datos[$j]["MontoSol"];
                                $monsug = $datos[$j]["MonSug"];
                                $idit = "data" . $j;
                                $imgurl = __DIR__ . '/../../../../../' . $urlimg;
                                if (!is_file($imgurl)) {
                                    $src = '../../includes/img/fotoClienteDefault.png';
                                } else {
                                    $imginfo   = getimagesize($imgurl);
                                    $mimetype  = $imginfo['mime'];
                                    $imageData = base64_encode(file_get_contents($imgurl));
                                    $src = 'data:' . $mimetype . ';base64,' . $imageData;
                                }
                        ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <div class="row">
                                            <div class="col-12">
                                                <button id="<?php echo 'bt' . $j; ?>" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#<?php echo $idit; ?>" aria-expanded="true" aria-controls="<?php echo $idit; ?>">
                                                    <div class="row" style="width:100%;font-size: 0.90rem;">
                                                        <div class="col-2">
                                                            <img width="80" height="80" id="vistaPrevia" src="<?php echo $src; ?>" /><br />
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <span class="input-group-addon"><?php echo $ccodta; ?></span>
                                                                </div>
                                                                <div class="col-12">
                                                                    <input id="<?php echo 'ccodcta' . $j; ?>" type="text" value="<?php echo $ccodta; ?>" hidden>
                                                                    <span class="input-group-addon"><?php echo strtoupper($name); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="row">
                                                                <span class="input-group-addon">Identificacion</span>
                                                                <span class="input-group-addon"><?php echo $dpi; ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-2">
                                                            <div class="row">
                                                                <span class="input-group-addon">Fecha Nacimiento</span>
                                                                <span class="input-group-addon"><?php echo $fecnac; ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                    </h2>
                                    <div id="<?php echo $idit; ?>" class="accordion-collapse collapse" data-bs-parent="#cuotas">
                                        <div class="accordion-body">
                                            <div class="row mb-3" style="font-size: 0.90rem;">
                                                <div class="col-sm-3">
                                                    <label class="input-group-addon fw-bold">Monto Solicitado</label>
                                                    <input type="number" step="0.01" class="form-control" placeholder="000.00" value="<?php echo  $monsol; ?>" disabled>
                                                </div>
                                                <div class="col-sm-3">
                                                    <label class="input-group-addon fw-bold">Monto Aprobado</label>
                                                    <input id="<?php echo 'monapr' . $j; ?>" type="number" step="0.01" class="form-control" placeholder="000.00" value="<?php echo  $monsug; ?>" disabled>
                                                </div>
                                                <div class="col-sm-6">
                                                    <br>
                                                    <div class="row justify-items-md-center">
                                                        <div class="col align-items-center">
                                                            <button type="button" class="btn btn-warning" onclick="reportes([[],[],[],[`<?php echo  $ccodta; ?>`]], `pdf`, `../../cre_indi/reportes/planPago`,0)">Plan
                                                                de pagos</button>
                                                            <!-- <button type="button" class="btn btn-warning" onclick="reportes([[],[],[],[`<?php //echo  $ccodta; 
                                                                                                                                                ?>`]], `pdf`, `19`,0,1)"> Generar contrato </button> -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php
                                $j++;
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="row justify-items-md-center">
                <div class="col align-items-center" id="modal_footer">
                    <?php
                    if ($bandera == "") {
                        echo '<button type="button" class="btn btn-outline-success" onclick="saveapro(' . ($j - 1) . ',' . ($extra) . ',' . ($numciclo) . ')">
                                <i class="fa fa-floppy-disk"></i> Aprobar Creditos
                            </button>';
                    }
                    ?>
                    <button type="button" class="btn btn-outline-danger" onclick="printdiv2('#cuadro', '0')">
                        <i class="fa-solid fa-ban"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="window.location.reload();">
                        <i class="fa-solid fa-circle-xmark"></i> Salir
                    </button>
                    <!-- <button type="button" class="btn btn-outline-success" onclick="reportes([[],[],[],[10,1,1]], `pdf`, `ficha_aprobacion`, 0)">
                        <i class="fa fa-floppy-disk"></i> PROBAR FICHA APROBACION
                    </button> -->
                </div>
            </div>
        </div>
    <?php
        break;
    case 'desembolso':

        $datpost = $_POST["xtra"];
        /*         echo ('<pre>');
                print_r($datpost);
                echo ('</pre>'); */
        $extra = $datpost[0];
        $bandera = "Grupo sin cuentas por Desembolbar";
        if ($extra != "0") {
            $numciclo = $datpost[1];
            //CREDITOS DEL GRUPO
            $datos[] = [];
            $datacre = mysqli_query($conexion, ' SELECT gru.NombreGrupo,gru.direc,gru.codigo_grupo,cli.url_img, cli.short_name,cli.idcod_cliente,cli.date_birth,cli.no_identifica, 
            cre.CCODCTA,cre.Cestado,cre.NCiclo,cre.MontoSol,cre.CodAnal,cre.CCODPRD,cre.CtipCre,cre.NtipPerC,cre.DfecPago,cre.noPeriodo,cre.Dictamen,cre.MonSug,cre.DFecDsbls,
            pro.id_fondo,ff.descripcion descfondo
            From cremcre_meta cre
            INNER JOIN tb_cliente cli ON cli.idcod_cliente=cre.CodCli
            INNER JOIN tb_grupo gru ON gru.id_grupos=cre.CCodGrupo
            INNER JOIN cre_productos pro ON pro.id=cre.CCODPRD
            INNER JOIN ctb_fuente_fondos ff ON ff.id=pro.id_fondo
            WHERE cre.TipoEnti="GRUP" AND (cre.CESTADO="E" OR cre.CESTADO="E") AND cre.CCodGrupo="' . $extra . '" AND cre.NCiclo=' . $numciclo . ' ORDER BY cre.CCODCTA');

            $i = 0;
            while ($da = mysqli_fetch_array($datacre, MYSQLI_ASSOC)) {
                $datos[$i] = $da;
                $i++;
                $bandera = "";
            }
        }
    ?>
        <input type="text" readonly hidden value='desembolso' id='condi'>
        <input type="text" readonly hidden value='grup001' id='file'>
        <div class="card crdbody contenedort">
            <div class="card-header" style="text-align:left">
                <h4>DESEMBOLSO DE CREDITOS GRUPAL</h4>
            </div>
            <div class="card-body">
                <div class="row contenedort">
                    <h5>Detalle de Grupo</h5>
                    <div class="row">
                        <div class="col-sm-5">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <span class="input-group-addon col-8">Nombre Grupo</span>
                                <?php if ($bandera == "") echo '<span style="font-size:1rem;width:min(25rem,90%);" class="badge rounded-pill text-bg-success">' . $datos[0]["NombreGrupo"] . '</span>'; ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <label for="codgrup" class="input-group-addon">Codigo de Grupo</label>
                                <?php if ($bandera == "") echo '<span style="font-size:1rem;width:min(9rem,90%);" class="badge rounded-pill text-bg-success">' . $datos[0]["codigo_grupo"] . '</span>'; ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <br>
                            <button id="findgrupo" onclick="loadconfig01('any',['E'])" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#buscargrupo">
                                <i class="fa-solid fa-magnifying-glass"></i> Buscar Grupo
                            </button>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <span class="input-group-addon col-8">Direccion</span>
                                <?php if ($bandera == "") echo '<span style="font-size:1rem;width:min(25rem,90%);" class="badge rounded-pill text-bg-success">' . $datos[0]["direc"] . '</span>'; ?>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <label for="nciclo" class="input-group-addon">Ciclo</label>
                                <input type="number" class="form-control" id="nciclo" readonly value="<?php if ($bandera == "") echo $numciclo; ?>">
                            </div>
                        </div>
                    </div>
                    <?php if ($extra != "0" && $bandera != "") {
                        echo '<div class="alert alert-danger" role="alert">' . $bandera . '</div>';
                    } else if ($extra != "0") {
                        $est = ($datos[0]["Cestado"] == "E") ? " APROBADO" : " ";
                        echo 'ESTADO: ' . $est;
                    }
                    ?>
                </div>
                <div class="row contenedort">
                    <?php
                    $prd = 0;
                    if ($bandera == "" && $datos[0]["Cestado"] == "E") {
                        $codproducto = $datos[0]['CCODPRD'];
                        $qe = mysqli_query($conexion, "SELECT pro.id,pro.cod_producto,pro.nombre nompro,pro.descripcion descriprod,ff.descripcion fondesc,pro.tasa_interes, pro.monto_maximo
                        FROM cre_productos pro
                        INNER JOIN ctb_fuente_fondos ff ON ff.id=pro.id_fondo WHERE pro.estado=1 AND pro.id=" . $codproducto . "");
                        $k = 0;
                        while ($da = mysqli_fetch_array($qe, MYSQLI_ASSOC)) {
                            $dprod[$k] = $da;
                            $k++;
                            $prd = 1;
                        }
                    }
                    ?>

                    <div class="row crdbody">
                        <div class="col-sm-2">
                            <div class="">
                                <span class="fw-bold">Codigo Producto</span>
                                <input type="number" class="form-control" id="idprod" readonly hidden>
                                <input type="text" class="form-control" id="codprod" value="<?php if ($prd == 1) echo $dprod[0]["cod_producto"]; ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group col-sm-5">
                            <span class="fw-bold">Nombre</span>
                            <input type="text" class="form-control" id="nameprod" readonly value="<?php if ($prd == 1) echo $dprod[0]["nompro"]; ?>">
                        </div>
                        <div class="form-group col-sm-5">
                            <span class="fw-bold">Descripcion Producto</span>
                            <input type="text" class="form-control" id="desprod" readonly value="<?php if ($prd == 1) echo $dprod[0]["descriprod"]; ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <span class="fw-bold">Tipo de Crédito</span>
                            <input type="text" class="form-control" id="tipcre" value="<?php if ($bandera == "") echo tip_cre_peri($datos[0]["CtipCre"]); ?>" readonly>
                        </div>
                        <div class="col-sm-4">
                            <span class="fw-bold">Tipo de Periodo</span>
                            <input type="text" class="form-control" id="tipper" value="<?php if ($bandera == "") echo tip_cre_peri($datos[0]["NtipPerC"]); ?>" readonly>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <label for="tip_doc">Tipo de desembolso</label>
                            <select class="form-select" id="tipo_desembolso" aria-label="Tipo de desembolso" onchange="showhide(this.value)">
                                <option selected value="1">Efectivo</option>
                                <option value="2">Cheque</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row contenedort" style="display:none;" id="region_cheque">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <label for="bancoid">Banco</label>
                            <select class="form-select" id="bancoid" onchange="buscar_cuentas()">
                                <option value="0" selected disabled>Seleccione un Banco</option>
                                <?php
                                $bancos = mysqli_query($conexion, "SELECT * FROM tb_bancos WHERE estado='1'");
                                while ($banco = mysqli_fetch_array($bancos)) {
                                    echo '<option  value="' . $banco['id'] . '">' . $banco['id'] . " - " . $banco['nombre'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label for="cuentaid">No. de Cuenta</label>
                            <select class="form-select" id="cuentaid">
                                <option value="0">Seleccione una cuenta</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row contenedort" style="background-image: url(https://mdbootstrap.com/img/Photos/new-templates/glassmorphism-article/img9.jpg);">
                    <h5>CIENTES DEL GRUPO</h5>
                    <div class="accordion" id="cuotas">
                        <?php
                        if ($bandera == "") {
                            $j = 0;
                            while ($j < count($datos)) {
                                $ccodta = $datos[$j]["CCODCTA"];
                                $estado = $datos[$j]["Cestado"];
                                $codcli = $datos[$j]["idcod_cliente"];
                                $name = $datos[$j]["short_name"];
                                $fecnac = date("d-m-Y", strtotime($datos[$j]["date_birth"]));
                                $dpi = $datos[$j]["no_identifica"];
                                $monsol = $datos[$j]["MontoSol"];
                                $monsug = $datos[$j]["MonSug"];
                                $idit = "data" . $j;

                        ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <div class="row">
                                            <div class="col-12">
                                                <button id="<?php echo 'bt' . $j; ?>" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#<?php echo $idit; ?>" aria-expanded="true" aria-controls="<?php echo $idit; ?>">
                                                    <div class="row" style="width:100%;font-size: 0.90rem;">
                                                        <div class="col-3">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <span class="input-group-addon"><?php echo $ccodta; ?></span>
                                                                </div>
                                                                <div class="col-12">
                                                                    <input id="<?php echo 'ccodcta' . $j; ?>" type="text" value="<?php echo $ccodta; ?>" hidden>
                                                                    <span class="input-group-addon"><?php echo strtoupper($name); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <label class="input-group-addon fw-bold">Monto Aprobado</label>
                                                            <input id="<?php echo 'monapr' . $j; ?>" type="number" step="0.01" class="form-control" placeholder="000.00" value="<?php echo  $monsug; ?>" disabled>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <label class="input-group-addon fw-bold">Descuentos</label>
                                                            <input id="<?php echo 'mondesc' . $j; ?>" type="number" step="0.01" class="form-control" placeholder="000.00" value="0" disabled>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <label class="input-group-addon fw-bold">A Entregar</label>
                                                            <input id="<?php echo 'monentrega' . $j; ?>" type="number" step="0.01" class="form-control" placeholder="000.00" value="<?php echo  $monsug; ?>" disabled>
                                                        </div>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                    </h2>
                                    <div id="<?php echo $idit; ?>" class="accordion-collapse collapse" data-bs-parent="#cuotas">
                                        <div class="accordion-body">
                                            <div class="row mb-3">
                                                <div class="col-sm-9">
                                                    <label for="glosa">Concepto</label>
                                                    <textarea class="form-control" id="<?php echo 'glosa' . $j; ?>" rows="1" placeholder="Concepto">DESEMBOLSO DE CRÉDITO A NOMBRE DE <?php echo strtoupper($name); ?></textarea>
                                                </div>
                                                <div class="col-sm-3 classchq" id="divcheque" style="display: none;">
                                                    <label for="numcheque">No. de Cheque</label>
                                                    <input type="number" class="form-control" id="<?php echo 'numcheque' . $j; ?>" placeholder="No. cheque">
                                                </div>
                                            </div>
                                            <div class="row mb-3" style="font-size: 0.90rem;">
                                                <h6>DETALLE DE DESCUENTOS</h6>
                                                <div class="table-responsive">
                                                    <table id="<?php echo 'tabla_gastos_desembolso' . $j; ?>" class="table" style="width: 100% !important;">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">#</th>
                                                                <th scope="col"></th>
                                                                <th scope="col">Descripción de gasto</th>
                                                                <th scope="col">ant</th>
                                                                <th scope="col">Monto</th>
                                                                <th scope="col"></th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
                                            <script>
                                                <?php
                                                echo 'mostrar_tabla_gastos(`' . $ccodta . '`,' . $j . ');';
                                                echo 'consultar_gastos_monto(`' . $ccodta . '`,' . $j . ');';
                                                ?>
                                            </script>
                                        </div>
                                    </div>
                                </div>
                        <?php
                                $j++;
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="row justify-items-md-center">
                <div class="col align-items-center" id="modal_footer">
                    <?php
                    if ($bandera == "") {
                        $datacres = json_encode($datos);
                    ?>
                        <button type="button" class="btn btn-outline-success" onclick="sds()">
                            <i class="fa fa-floppy-disk"></i> Desembolsar Creditos
                        </button>
                    <?php
                    } else {
                        echo '<div style="display:none;" id="divcheque"></div>';
                    }
                    ?>
                    <button type="button" class="btn btn-outline-danger" onclick="printdiv2(' #cuadro', '0' )">
                        <i class="fa-solid fa-ban"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="window.location.reload();">
                        <i class="fa-solid fa-circle-xmark"></i> Salir
                    </button>
                </div>
            </div>
        </div>
        <script>
            <?php
            if ($bandera == "") {
            ?>

                function sds() {
                    savedesem(<?php echo ($j - 1); ?>, <?php echo ($extra); ?>, <?php echo ($numciclo); ?>,
                        `<?php echo $datos[0]['DFecDsbls']; ?>`, <?php echo $usuario; ?>, <?php echo $id_agencia; ?>,
                        <?php echo json_encode($datos); ?>)
                }
            <?php
            }
            ?>
        </script>
    <?php
        break;
    case 'comprobantechq':
        $datpost = $_POST["xtra"];
        /*         echo ('<pre>');
        print_r($datpost);
        echo ('</pre>'); */
        $extra = $datpost[0];
        $datos = $datpost[1];
        $idchq = $datpost[2];
        $porcheque = $datpost[3];
        $bandera = "Grupo sin cuentas por Desembolbar";
        if ($extra != "0") {
            $numciclo = $datpost[1];
            //CREDITOS DEL GRUPO
            /*  $datos[] = [];
                $datacre = mysqli_query($conexion, 'SELECT gru.NombreGrupo,gru.direc,gru.codigo_grupo,cli.url_img, cli.short_name,cli.idcod_cliente,cli.date_birth,cli.no_identifica, 
                cre.CCODCTA,cre.Cestado,cre.NCiclo,cre.MontoSol,cre.CodAnal,cre.CCODPRD,cre.CtipCre,cre.NtipPerC,cre.DfecPago,cre.P_ahoCr,cre.noPeriodo,cre.Dictamen,cre.MonSug,cre.DFecDsbls,
                pro.id_fondos,ff.descripcion descfondo
                From cremcre_meta cre
                INNER JOIN tb_cliente cli ON cli.idcod_cliente=cre.CodCli
                INNER JOIN tb_grupo gru ON gru.id_grupos=cre.CCodGrupo
                INNER JOIN productos pro ON pro.ccodprdct=cre.CCODPRD
                INNER JOIN ctb_fuente_fondos ff ON ff.id=pro.id_fondos
                WHERE cre.TipoEnti="GRUP" AND (cre.CESTADO="E" OR cre.CESTADO="E") AND cre.CCodGrupo="' . $extra . '" AND cre.NCiclo=' . $numciclo . ' ORDER BY cre.CCODCTA');
    
                $i = 0;
                while ($da = mysqli_fetch_array($datacre, MYSQLI_ASSOC)) {
                    $datos[$i] = $da;
                    $i++;
                    $bandera = "";
                } */
        }
        $bandera = "";
    ?>
        <input type="text" readonly hidden value='comprobantechq' id='condi'>
        <input type="text" readonly hidden value='grup001' id='file'>
        <div class="card crdbody contenedort">
            <div class="card-header" style="text-align:left">
                <h4>IMPRESION DE COMPROBANTES DE DESEMBOLSOS DE CREDITOS GRUPAL</h4>
            </div>
            <div class="card-body">
                <div class="row contenedort">
                    <h5>Detalle de Grupo</h5>
                    <div class="row">
                        <div class="col-sm-5">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <span class="input-group-addon col-8">Nombre Grupo</span>
                                <?php if ($bandera == "") echo '<span style="font-size:1rem;width:min(25rem,90%);" class="badge rounded-pill text-bg-success">' . $datos[0]["NombreGrupo"] . '</span>'; ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <label for="codgrup" class="input-group-addon">Codigo de Grupo</label>
                                <?php if ($bandera == "") echo '<span style="font-size:1rem;width:min(9rem,90%);" class="badge rounded-pill text-bg-success">' . $datos[0]["codigo_grupo"] . '</span>'; ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <span class="input-group-addon col-8">Direccion</span>
                                <?php if ($bandera == "") echo '<span style="font-size:1rem;width:min(25rem,90%);" class="badge rounded-pill text-bg-success">' . $datos[0]["direc"] . '</span>'; ?>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="row" style="display:grid;align-content:center; align-items: center;">
                                <label for="nciclo" class="input-group-addon">Ciclo</label>
                                <input type="number" class="form-control" id="nciclo" readonly value="<?php if ($bandera == "") echo $datos[0]["NCiclo"]; ?>">
                            </div>
                        </div>
                    </div>
                    <?php if ($extra != "0" && $bandera != "") {
                        echo '<div class="alert alert-danger" role="alert">' . $bandera . '</div>';
                    } else if ($extra != "0") {
                        echo 'ESTADO: DESEMBOLSADOS';
                    }
                    ?>
                </div>

                <div class="row contenedort" style="background-image: url(https://mdbootstrap.com/img/Photos/new-templates/glassmorphism-article/img9.jpg);">
                    <h5>CLIENTES DEL GRUPO</h5>
                    <div class="accordion" id="cuotas">
                        <?php
                        if ($bandera == "") {
                            $j = 0;
                            while ($j < count($datos)) {
                                $ccodta = $datos[$j]["CCODCTA"];
                                $estado = $datos[$j]["Cestado"];
                                $codcli = $datos[$j]["idcod_cliente"];
                                $name = $datos[$j]["short_name"];
                                $fecnac = date("d-m-Y", strtotime($datos[$j]["date_birth"]));
                                $dpi = $datos[$j]["no_identifica"];
                                $monsol = $datos[$j]["MontoSol"];
                                $monsug = $datos[$j]["MonSug"];
                                $idit = "data" . $j;

                        ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <div class="row">
                                            <div class="col-12">
                                                <div id="<?php echo 'bt' . $j; ?>" class="accordion-button collapsed" aria-expanded="true">
                                                    <div class="row" style="width:100%;font-size: 0.90rem;">
                                                        <div class="col-sm-3">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <span class="input-group-addon"><?php echo $ccodta; ?></span>
                                                                </div>
                                                                <div class="col-12">
                                                                    <input id="<?php echo 'ccodcta' . $j; ?>" type="text" value="<?php echo $ccodta; ?>" hidden>
                                                                    <span class="input-group-addon"><?php echo strtoupper($name); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <label class="input-group-addon fw-bold">Monto Aprobado</label>
                                                            <input id="<?php echo 'monapr' . $j; ?>" type="number" step="0.01" class="form-control" placeholder="000.00" value="<?php echo  $monsug; ?>" disabled>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="row justify-items-md-center">
                                                                <div class="col align-items-center">
                                                                    <button type="button" class="btn btn-outline-primary" onclick="reportes([[], [], [], ['<?php echo $ccodta; ?>']], 'pdf', '18', 0,1);">
                                                                        <i class="fa-solid fa-dog"></i> Nota de Desembolso
                                                                    </button>
                                                                    <?php if ($porcheque == 1) {
                                                                        echo '<button id="chq' . $j . '" type="button" class="btn btn-outline-success" onclick="reportes([[],[],[],[' . $idchq[$j] . ']], `pdf`, `13`,0,1); hidee(' . $j . ')">
                                                                            <i class="fa-solid fa-money-check-dollar"></i></i>Cheque
                                                                        </button>';
                                                                    }

                                                                    ?>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </h2>
                                    <div id="<?php echo $idit; ?>" class="accordion-collapse collapse" data-bs-parent="#cuotas">
                                        <div class="accordion-body">
                                        </div>
                                    </div>
                                </div>
                        <?php
                                $j++;
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="row justify-items-md-center">
                <div class="col align-items-center" id="modal_footer">
                    <button type="button" class="btn btn-outline-danger" onclick="window.location.reload();">
                        <i class="fa-solid fa-circle-xmark"></i> Salir
                    </button>
                </div>
            </div>
        </div>
        <script>
            function hidee(id) {
                document.getElementById("chq" + id).style.display = "none";
            }
        </script>
<?php
        break;
} ?>