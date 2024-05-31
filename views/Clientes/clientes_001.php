<?php
session_start();
include '../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
mysqli_set_charset($general, 'utf8');

$condi = $_POST["condi"];
switch ($condi) {
    case 'create_cliente_natural': {
            $codusu = $_SESSION['id'];
            $id_agencia = $_SESSION['id_agencia'];
            $codagencia = $_SESSION['agencia'];
            $xtra = $_POST["xtra"];
            /*** NEGROY AGENCIA SELECT (ㆆ _ ㆆ) *** */
            $SelectAgenci = 'd-none';  // CAMBIAR A 'd-none' SI SE DESEA OCULTAR. 1(SIN COMILLAS) PARA MOSTRAR
            // Consulta para obtener las opciones del select agencia
            $query = "SELECT id_agencia,cod_agenc,nom_agencia FROM tb_agencia";
            $listAgencia = mysqli_query($conexion, $query);
            /*** NEGROY AGENCIA SELECT FIN *** */
            $bandera = false;
            $datos[] = [];
            $isfile = false;
            $i = 0;
            if ($xtra != 0) {
                $consulta = mysqli_query($conexion, "SELECT * FROM tb_cliente tc WHERE tc.estado='1' AND tc.idcod_cliente='$xtra'");
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $datos[$i] = $fila;
                    //CARGADO DE LA IMAGEN
                    $imgurl = __DIR__ . '/../../../' . $fila['url_img'];
                    if (!is_file($imgurl)) {
                        $isfile = false;
                        $src = '../includes/img/fotoClienteDefault.png';
                    } else {
                        $isfile = true;
                        $imginfo   = getimagesize($imgurl);
                        $mimetype  = $imginfo['mime'];
                        $imageData = base64_encode(file_get_contents($imgurl));
                        $src = 'data:' . $mimetype . ';base64,' . $imageData;
                    }
                    $i++;
                    $bandera = true;
                }
            }
?>
            <!--Aho_0_PrmtrzcAhrrs Inicio de Ahorro Sección 0 Parametros cuentas ahorro-->
            <input type="text" id="file" value="clientes_001" style="display: none;">
            <input type="text" id="condi" value="create_cliente_natural" style="display: none;">
            <div class="text" style="text-align:center"><?= ($bandera) ? 'ACTUALIZACIÓN ' : 'INGRESO '; ?> DE CLIENTE</div>
            <div class="card">
                <div class="card-header"><?= ($bandera) ? 'Actualización ' : 'Ingreso '; ?> de cliente</div>
                <div class="card-body" style="padding-bottom: 0px !important;">
                    <!-- seleccion de cliente y su credito-->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Información de personal</b></div>
                            </div>
                        </div>
                        <!-- NEGROY SELECT DE AGENCIA  ⊂(◉‿◉)つ -->
                        <div class="row <?= $SelectAgenci ?>">
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <?php
                                    echo '<select class="form-select" name="agencia" id="agencidplus">';
                                    if ($SelectAgenci === 1) {
                                        while ($row = $listAgencia->fetch_assoc()) {
                                            $selected = ($row['id_agencia'] == $codagencia) ? 'selected' : '';
                                            echo '<option value="' . $row['cod_agenc'] . '" ' . $selected . '>' . $row['nom_agencia'] . '</option>';
                                        }
                                    } else {
                                        echo '<option value="' . $codagencia . '" selected>--</option>';
                                    } ?>
                                    </select>
                                    <label for="agencia">Selecciona una agencia:</label>
                                </div>
                            </div>
                        </div>
                        <!-- NEGROY SELECT DE AGENCIA -->
                        <?php if ($bandera) { ?>
                            <div class="row">
                                <div class="col">
                                    <div class="text-center"><span class="text-primary">Codigo cliente:
                                            <b><?php echo $datos[0]['idcod_cliente']; ?></b></span></div>
                                </div>
                            </div>
                            <div class="row justify-content-center">
                                <div class="col-6 col-sm-6 col-md-2 mt-2 d-flex align-items-center">
                                    <div class="mx-auto">
                                        <img id="vistaPrevia" class="img-thumbnail" src="<?php if ($bandera) {
                                                                                                echo $src;
                                                                                            } else {
                                                                                                echo $src;
                                                                                            } ?>" style="max-width:120px; max-height:130px;">
                                    </div>
                                </div>
                            </div>
                            <?php if ($isfile) { ?>
                                <div class="row">
                                    <div class="col mb-2 mt-2">
                                        <div class="input-group">
                                            <button class="btn btn-outline-danger" type="button" id="inputGroupFileAddon04" onclick="eliminar_plus(['<?= $imgurl; ?>','<?= $datos[0]['idcod_cliente']; ?>'], `<?= $datos[0]['idcod_cliente']; ?>`, `delete_image_cliente`, `¿Está seguro de eliminar la foto?`)">
                                                <i class="fa-solid fa-trash"></i></i>Borrar Foto
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php }; ?>
                            <div class="row">
                                <div class="col mb-2 mt-2">
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="fileuploadcli" aria-describedby="inputGroupFileAddon04" aria-label="Upload" onchange="LeerImagen(this)">
                                        <button class="btn btn-outline-primary" type="button" id="inputGroupFileAddon04" onclick="CargarImagen('fileuploadcli','<?= $datos[0]['idcod_cliente']; ?>');"><i class="fa-solid fa-sd-card me-2"></i>Guardar</button>
                                    </div>
                                </div>
                            </div>
                        <?php }; ?>

                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="nom1" placeholder="Primer nombre" <?php if ($bandera) {
                                                                                                                        echo 'value="' . $datos[0]['primer_name'] . '"';
                                                                                                                    } ?> onkeyup="concatenarValores(['nom1','nom2','nom3'],['ape1','ape2','ape3'],1,'#nomcorto'); concatenarValores(['ape1','ape2','ape3'],['nom1','nom2','nom3'],2,'#nomcompleto')" oninput="validateInputname(this)" pattern="[A-Za-z]+" title="Solo se permiten letras" required>
                                    <label for="cliente">Primer nombre</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="nom2" placeholder="Segundo nombre" <?php if ($bandera) {
                                                                                                                        echo 'value="' . $datos[0]['segundo_name'] . '"';
                                                                                                                    } ?> onkeyup="concatenarValores(['nom1','nom2','nom3'],['ape1','ape2','ape3'],1,'#nomcorto'); concatenarValores(['ape1','ape2','ape3'],['nom1','nom2','nom3'],2,'#nomcompleto')" oninput="validateInputname(this)" pattern="[A-Za-z]+" title="Solo se permiten letras">
                                    <label for="cliente">Segundo nombre</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="nom3" placeholder="Tercer nombre" <?php if ($bandera) {
                                                                                                                        echo 'value="' . $datos[0]['tercer_name'] . '"';
                                                                                                                    } ?> onkeyup="concatenarValores(['nom1','nom2','nom3'],['ape1','ape2','ape3'],1,'#nomcorto'); concatenarValores(['ape1','ape2','ape3'],['nom1','nom2','nom3'],2,'#nomcompleto')" oninput="validateInputname(this)" pattern="[A-Za-z]+" title="Solo se permiten letras">
                                    <label for="cliente">Tercer nombre</label>
                                </div>
                            </div>
                        </div>

                        <!-- apellidos -->
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="ape1" placeholder="Primer apellido" <?php if ($bandera) {
                                                                                                                        echo 'value="' . $datos[0]['primer_last'] . '"';
                                                                                                                    } ?> onkeyup="concatenarValores(['nom1','nom2','nom3'],['ape1','ape2','ape3'],1,'#nomcorto'); concatenarValores(['ape1','ape2','ape3'],['nom1','nom2','nom3'],2,'#nomcompleto')" oninput="validateInputlastname(this)" pattern="[A-Za-z]+" title="Solo se permiten letras">
                                    <label for="cliente">Primer apellido</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="ape2" placeholder="Segundo apellido" <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['segundo_last'] . '"';
                                                                                                                        } ?> onkeyup="concatenarValores(['nom1','nom2','nom3'],['ape1','ape2','ape3'],1,'#nomcorto'); concatenarValores(['ape1','ape2','ape3'],['nom1','nom2','nom3'],2,'#nomcompleto')" oninput="validateInputlastname(this)" pattern="[A-Za-z]+" title="Solo se permiten letras">
                                    <label for="cliente">Segundo apellido</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="ape3" placeholder="Tercer apellido" <?php if ($bandera) {
                                                                                                                        echo 'value="' . $datos[0]['casada_last'] . '"';
                                                                                                                    } ?> onkeyup="concatenarValores(['nom1','nom2','nom3'],['ape1','ape2','ape3'],1,'#nomcorto'); concatenarValores(['ape1','ape2','ape3'],['nom1','nom2','nom3'],2,'#nomcompleto')" oninput="validateInputlastname(this)" pattern="[A-Za-z]+" title="Solo se permiten letras">
                                    <label for="cliente">Apellido de casada</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="nomcorto" placeholder="Nombre corto" readonly disabled <?php if ($bandera) {
                                                                                                                                            echo 'value="' . $datos[0]['short_name'] . '"';
                                                                                                                                        } ?>>
                                    <label for="nomcorto">Nombre corto</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="nomcompleto" placeholder="Nombre completo" readonly disabled <?php if ($bandera) {
                                                                                                                                                    echo 'value="' . $datos[0]['compl_name'] . '"';
                                                                                                                                                } ?>>
                                    <label for="nomcompleto">Nombre completo</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="genero">
                                        <option value="0" selected>Seleccione un género</option>
                                        <option value="M">Hombre</option>
                                        <option value="F">Mujer</option>
                                        <option value="X">No definido</option>
                                    </select>
                                    <label for="genero">Género</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="estcivil" onchange="toggleConyugueInput()">
                                        <option value="0" selected>Seleccione un estado civil</option>
                                        <option value="SOLTERO">Soltero</option>
                                        <option value="CASADO">Casado(a)</option>
                                    </select>
                                    <label for="estcivil">Estado civil</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="profesion" placeholder="Profesión" <?php if ($bandera) {
                                                                                                                        echo 'value="' . $datos[0]['profesion'] . '"';
                                                                                                                    } ?>oninput="validateInputprofesion(this)" pattern="[A-Za-z]+" title="Solo se permiten letras">
                                    <label for="profesion">Profesión</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-3">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="email" class="form-control" id="email" placeholder="Email" <?php if ($bandera) {
                                                                                                                echo 'value="' . $datos[0]['email'] . '"';
                                                                                                            } ?> oninput="validateEmail(this)" title="example@.com">
                                    <label for="email">Email</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="conyugue" placeholder="Conyuge" <?php if ($bandera) {
                                                                                                                    echo 'value="' . $datos[0]['Conyuge'] . '"';
                                                                                                                } ?> oninput="validateInputlibre(this)" disabled>
                                    <label for="conyugue">Cónyuge</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-3">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="telconyuge" placeholder="Tel Conyuge" <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['telconyuge'] . '"';
                                                                                                                        } ?> oninput="validateInputtelreF(this)" disabled>
                                    <label for="telconyuge">Tel Cónyuge</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- NACIMIENTO -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Nacimiento</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="date" class="form-control" id="fechanacimiento" placeholder="Fecha de nacimiento" <?php if ($bandera) {
                                                                                                                                        echo 'value="' . $datos[0]['date_birth'] . '"';
                                                                                                                                    } ?> onchange="calcularEdad_plus(this.value, '#edad')">
                                    <label for="fechanacimiento">Fecha de nacimiento</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="number" class="form-control" id="edad" placeholder="Edad" readonly disabled>
                                    <label for="edad">Edad</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="origen">
                                        <option value="0" selected>Seleccione un origen</option>
                                        <option value="Residente">Residente</option>
                                        <option value="Extranjero">Extranjero</option>
                                    </select>
                                    <label for="origen">Origen</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="paisnac" onchange="ocultar_muni_dep(this.value)">
                                        <option value="0" selected>Seleccione un país</option>
                                        <?php
                                        $selected = "";
                                        $pais = mysqli_query($general, "SELECT * FROM `tb_pais`");
                                        while ($fila = mysqli_fetch_array($pais, MYSQLI_ASSOC)) {
                                            if ($bandera) {
                                                ($datos[0]['pais_nacio'] == $fila['Abreviatura']) ? $selected = "selected" : $selected = "";
                                            }
                                            $nombre = ($fila["Pais"]);
                                            $codpais = $fila["Abreviatura"];
                                            echo '<option value="' . $codpais . '"  ' . $selected . '>' . $nombre . '</option>';
                                        } ?>
                                    </select>
                                    <label for="paisnac">País</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="depnac" onchange="buscar_municipios('buscar_municipios', '#muninac', this.value)">
                                        <option value="0" selected>Seleccione un departamento</option>
                                        <?php
                                        $selected = "";
                                        $departa = mysqli_query($general, "SELECT * FROM `departamentos`");
                                        while ($municipalidad = mysqli_fetch_array($departa, MYSQLI_ASSOC)) {
                                            if ($bandera) {
                                                ($datos[0]['depa_nacio'] == $municipalidad['codigo_departamento']) ? $selected = "selected" : $selected = "";
                                            }
                                            $nombre = ($municipalidad["nombre"]);
                                            $codigo_departa = $municipalidad["codigo_departamento"];
                                            echo '<option value="' . $codigo_departa . '"' . $selected . '>' . $nombre . '</option>';
                                        } ?>
                                    </select>
                                    <label for="depnac">Departamento</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="muninac">
                                        <option value="0" selected>Seleccione un municipio</option>
                                    </select>
                                    <label for="muninac">Municipio</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="dirnac" placeholder="Dirección nacimiento" <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['aldea'] . '"';
                                                                                                                            } ?>oninput="validateInputlibre(this)">
                                    <label for="dirnac">Dirección nacimiento</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DOCUMENTO DE INDENTIFICACION -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Documento de identificación</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="docextend" onchange="ocultar_nit(this.value)">
                                        <option value="Guatemala" selected>Guatemala</option>
                                        <option value="Extranjero">Extranjero</option>
                                    </select>
                                    <label for="docextend">Documento extendido en:</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="tipodoc">
                                        <option value="DPI" selected>DPI</option>
                                        <option value="PASAPORTE">Pasaporte</option>
                                    </select>
                                    <label for="tipodoc">Tipo de documento</label>
                                </div>
                            </div>
                            <!-- NEGROY DPI PRUEBAS -->
                            <?php
                            $dpi = "000000000000";
                            $cli = "1";
                            if ($bandera) {
                                $dpi = $datos[0]['no_identifica'];
                                $cli = $datos[0]['idcod_cliente'];
                            } ?>
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="numberdoc" placeholder="Número de documento" onblur="dpivalidate(this.value, '<?= $cli ?>')" value="<?= $dpi ?>" oninput="validateInputdpi(this)">
                                    <label for="numberdoc">Número de documento</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="tipoidentri" onchange="updateTributario()">
                                        <option value="NIT" selected>NIT</option>
                                        <option value="CUI">CUI</option>
                                    </select>
                                    <label for="tipoidentri">Tipo de indent. tributaria</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="numbernit" placeholder="Número tributario" <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['no_tributaria'] . '"';
                                                                                                                            } ?> oninput="validateInputdniOrNIT(this)">
                                    <label for="numbernit">Número tributario</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="number" class="form-control" id="afiliggs" placeholder="Afiliación IGGS" <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['no_igss'] . '"';
                                                                                                                            } ?>oninput="validateInputlibre(this)">
                                    <label for="afiliggs">Afiliación IGGS</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="nacionalidad">
                                        <option value="0">Seleccione una nacionalidad</option>
                                        <?php
                                        $selected = "";
                                        $pais = mysqli_query($general, "SELECT * FROM `tb_pais`");
                                        while ($fila = mysqli_fetch_array($pais, MYSQLI_ASSOC)) {
                                            if ($bandera) {
                                                ($datos[0]['nacionalidad'] == $fila['Abreviatura']) ? $selected = "selected" : $selected = "";
                                            }
                                            $nombre = utf8_decode($fila["Pais"]);
                                            $codpais = $fila["Abreviatura"];
                                            echo '<option value="' . $codpais . '"' . $selected . '>' . $nombre . '</option>';
                                        } ?>
                                    </select>
                                    <label for="nacionalidad">Nacionalidad</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DOMICILIO -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Domicilio</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="condicion">
                                        <?php
                                        $selected = "";
                                        $condicasa = mysqli_query($general, "SELECT * FROM `tb_negocio`");
                                        while ($fila = mysqli_fetch_array($condicasa, MYSQLI_ASSOC)) {
                                            if ($bandera) {
                                                ($datos[0]['vivienda_Condi'] == $fila['id_Negocio']) ? $selected = "selected" : $selected = "";
                                            }
                                            $nombre = ($fila["Negocio"]);
                                            $codigo = $fila["id_Negocio"];
                                            echo '<option value="' . $codigo . '"' . $selected . '>' . $nombre . '</option>';
                                        } ?>
                                    </select>
                                    <label for="condicion">Condición de vivienda</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="number" class="form-control" id="reside" placeholder="Reside desde" <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['ano_reside'] . '"';
                                                                                                                        } ?>oninput="validateInputlibre(this)">
                                    <label for="reside">Reside desde</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="depdom" onchange="buscar_municipios('buscar_municipios', '#munidom', this.value)">
                                        <option value="0" selected>Seleccione un departamento</option>
                                        <?php
                                        $selected = "";
                                        $departa = mysqli_query($general, "SELECT * FROM `departamentos`");
                                        while ($municipalidad = mysqli_fetch_array($departa, MYSQLI_ASSOC)) {
                                            if ($bandera) {
                                                ($datos[0]['depa_reside'] == $municipalidad['codigo_departamento']) ? $selected = "selected" : $selected = "";
                                            }
                                            $nombre = ($municipalidad["nombre"]);
                                            $codigo_departa = $municipalidad["codigo_departamento"];
                                            echo '<option value="' . $codigo_departa . '"' . $selected . '>' . $nombre . '</option>';
                                        } ?>
                                    </select>
                                    <label for="depdom">Departamento</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="munidom">
                                        <option value="0" selected>Seleccione un municipio</option>
                                    </select>
                                    <label for="munidom">Municipio</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="dirviv" placeholder="Dirección de vivienda" <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['Direccion'] . '"';
                                                                                                                            } ?>oninput="validateInputlibre(this)">
                                    <label for="dirviv">Dirección de vivienda</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="refviv" placeholder="Referencia" <?php if ($bandera) {
                                                                                                                        echo 'value="' . $datos[0]['aldea_reside'] . '"';
                                                                                                                    } ?>oninput="validateInputlibre(this)">
                                    <label for="refviv">Referencia</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="zonaviv" placeholder="Zona" <?php if ($bandera) {
                                                                                                                echo 'value="' . $datos[0]['zona'] . '"';
                                                                                                            } ?>oninput="validateInputlibre(this)">
                                    <label for="zonaviv">Zona</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="barrioviv" placeholder="Colonia o Barrio" <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['barrio'] . '"';
                                                                                                                            } ?>oninput="validateInputlibre(this)">
                                    <label for="barrioviv">Colonia o Barrio</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="tel1" placeholder="Telefono 1" <?php if ($bandera) {
                                                                                                                    echo 'value="' . $datos[0]['tel_no1'] . '"';
                                                                                                                } ?>oninput="validateInputtelreF(this)">
                                    <label for="tel1">Telefono 1</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="tel2" placeholder="Telefono 2" <?php if ($bandera) {
                                                                                                                    echo 'value="' . $datos[0]['tel_no2'] . '"';
                                                                                                                } ?>oninput="validateInputtelreF(this)">
                                    <label for="tel2">Telefono 2</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-4 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="actpropio" onchange="ocultar_actuacion_propia(this.value)">
                                        <option value="1" selected>Si</option>
                                        <option value="2">No</option>
                                    </select>
                                    <label for="actpropio">¿Actua en nombre propio?</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-8 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="representante" placeholder="Representante" <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['representante_name'] . '"';
                                                                                                                            } ?>oninput="validateInputlibre(this)">
                                    <label for="representante">Representante</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="actcalidad">
                                        <option value="ninguno" selected>Ninguno</option>
                                        <option value="mandatario">Mandatario</option>
                                        <option value="potestad">P. Potestad</option>
                                        <option value="tutor">Tutor</option>
                                        <option value="otros">Otros</option>
                                    </select>
                                    <label for="actcalidad">¿Calidad que actua?</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ADICIONAL -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Adicional</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="otranacionalidad">
                                        <option value="0">Seleccione una nacionalidad</option>
                                        <?php
                                        $selected = "";
                                        $pais = mysqli_query($general, "SELECT * FROM `tb_pais`");
                                        while ($fila = mysqli_fetch_array($pais, MYSQLI_ASSOC)) {
                                            if ($bandera) {
                                                ($datos[0]['otra_nacion'] == $fila['Abreviatura']) ? $selected = "selected" : $selected = "";
                                            }
                                            $nombre = utf8_decode($fila["Pais"]);
                                            $codpais = $fila["Abreviatura"];
                                            echo '<option value="' . $codpais . '"' . $selected . '>' . $nombre . '</option>';
                                        } ?>
                                    </select>
                                    <label for="otranacionalidad">Otra nacionalidad</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="etnia">
                                        <?php
                                        $selected = "";
                                        $consulta = mysqli_query($general, "SELECT * FROM `tb_etnia`");
                                        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                            if ($bandera) {
                                                ($datos[0]['idioma'] == $fila['id']) ? $selected = "selected" : $selected = "";
                                            }
                                            $nombre = ($fila["nombre"]);
                                            $id = $fila["id"];
                                            echo '<option value="' . $id . '"' . $selected . '>' . $nombre . '</option>';
                                        } ?>
                                    </select>
                                    <label for="etnia">Etnia idioma</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="religion">
                                        <?php
                                        $selected = "";
                                        $consulta = mysqli_query($general, "SELECT * FROM `tb_religion`");
                                        while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                                            if ($bandera) {
                                                ($datos[0]['id_religion'] == $fila['id']) ? $selected = "selected" : $selected = "";
                                            }
                                            $nombre = ($fila["nombre"]);
                                            $id = $fila["id"];
                                            echo '<option value="' . $id . '"' . $selected . '>' . $nombre . '</option>';
                                        } ?>
                                    </select>
                                    <label for="etnia">Religión</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="educacion">
                                        <option value="no educacion">No Educación</option>
                                        <option value="primaria">Primaria</option>
                                        <option value="basico">Basico</option>
                                        <option value="diversificado">Diversificado</option>
                                        <option value="tecnico">Tecnico</option>
                                        <option value="universidad">Universidad</option>
                                        <option value="master">Master</option>
                                    </select>
                                    <label for="educacion">Educación</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="relinsti">
                                        <option value="Creditos">Creditos </option>
                                        <option value="Ahorros">Ahorros</option>
                                        <option value="Aportaciones">Aportaciones</option>
                                        <option value="Tarjeta credito">Tarjeta credito</option>
                                        <option value="Inversiones">Inversiones</option>
                                    </select>
                                    <label for="relinsti">Relación institucional</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-floating mb-2 mt-2">
                                            <input type="text" class="form-control" id="refn1" placeholder="Ref. Nombre" <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['Nomb_Ref1'] . '"';
                                                                                                                            } ?>oninput="validateInputlibre(this)">
                                            <label for="refn1">Ref. Nombre 1</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-2 mt-2">
                                            <input type="text" class="form-control" id="ref1" placeholder="Ref. Teléfono" <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['Tel_Ref1'] . '"';
                                                                                                                            } ?>oninput="validateInputtelreF(this)" title="Ejemplo: +502 12345678 / 12345678 ">
                                            <label for="ref1">Ref. Telefono 1</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-floating mb-2 mt-2">
                                            <input type="text" class="form-control" id="refn2" placeholder="Ref. Nombre 2" <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['Nomb_Ref2'] . '"';
                                                                                                                            } ?>oninput="validateInputlibre(this)">
                                            <label for="refn2">Ref. Nombre 2</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-2 mt-2">
                                            <input type="text" class="form-control" id="ref2" placeholder="Ref. Teléfono 2" <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['Tel_Ref2'] . '"';
                                                                                                                            } ?>oninput="validateInputtelreF(this)" title="Ejemplo: +502 12345678 / 12345678 ">
                                            <label for="ref2">Ref. Telefono 2</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-floating mb-2 mt-2">
                                            <input type="text" class="form-control" id="refn3" placeholder="Ref. Nombre 3" <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['Nomb_Ref3'] . '"';
                                                                                                                            } ?>oninput="validateInputlibre(this)">
                                            <label for="refn3">Ref. Nombre 3</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-2 mt-2">
                                            <input type="text" class="form-control" id="ref3" placeholder="Ref. Teléfono 3" <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['Tel_Ref3'] . '"';
                                                                                                                            } ?>oninput="validateInputtelreF(this)" title="Ejemplo: +502 12345678 / 12345678 ">
                                            <label for="ref3">Ref. Telefono 3</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row m-1">
                            <div class="col-12 col-sm-12 col-md-4 border border-primary">
                                <div class="row">
                                    <div class="col-12 mt-1">
                                        <span class="badge text-bg-primary">¿Sabe leer?</span>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="leer" id="flexRadioDefault1" checked value="Si">
                                            <label class="form-check-label" for="flexRadioDefault1">
                                                Si
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="leer" id="flexRadioDefault2" value="No">
                                            <label class="form-check-label" for="flexRadioDefault2">
                                                No
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 border border-primary">
                                <div class="row">
                                    <div class="col-12 mt-1">
                                        <span class="badge text-bg-primary">¿Sabe escribir?</span>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="escribir" id="flexRadioDefault1" checked value="Si">
                                            <label class="form-check-label" for="flexRadioDefault1">
                                                Si
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="escribir" id="flexRadioDefault2" value="No">
                                            <label class="form-check-label" for="flexRadioDefault2">
                                                No
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 border border-primary">
                                <div class="row">
                                    <div class="col-12 mt-1">
                                        <span class="badge text-bg-primary">Firma</span>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="firma" id="flexRadioDefault1" checked value="Si">
                                            <label class="form-check-label" for="flexRadioDefault1">
                                                Si
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="firma" id="flexRadioDefault2" value="No">
                                            <label class="form-check-label" for="flexRadioDefault2">
                                                No
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row m-1">
                            <div class="col-12 col-sm-12 col-md-8 border border-primary">
                                <div class="row">
                                    <div class="col-12 mt-1">
                                        <div class="badge text-bg-primary badge-sm-4">¿El cliente es PEP?</div>
                                        <div class="badge text-bg-primary badge-sm-4">Tiene paresteco o es asociado cercano a una
                                            PEP</div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="pep" id="flexRadioDefault1" checked value="Si">
                                            <label class="form-check-label" for="flexRadioDefault1">
                                                Si
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="pep" id="flexRadioDefault2" value="No">
                                            <label class="form-check-label" for="flexRadioDefault2">
                                                No
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-4 border border-primary">
                                <div class="row">
                                    <div class="col-12 mt-1">
                                        <span class="badge text-bg-primary">¿El cliente es CPE?</span>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="cpe" id="flexRadioDefault1" checked value="Si">
                                            <label class="form-check-label" for="flexRadioDefault1">
                                                Si
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="cpe" id="flexRadioDefault2" value="No">
                                            <label class="form-check-label" for="flexRadioDefault2">
                                                No
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-3">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="number" class="form-control" id="hijos" placeholder="No hijos" <?php if ($bandera) {
                                                                                                                    echo 'value="' . $datos[0]['hijos'] . '"';
                                                                                                                } ?>>
                                    <label for="hijos">No hijos</label>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="number" class="form-control" id="dependencia" placeholder="número de personas de relación de dependencia del cliente" <?php if ($bandera) {
                                                                                                                                                                            echo 'value="' . $datos[0]['dependencia'] . '"';
                                                                                                                                                                        } ?>>
                                    <label for="dependencia">Número de personas de relación de dependencia del cliente</label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="codinterno" placeholder="Código de control interno" <?php if ($bandera) {
                                                                                                                                        echo 'value="' . $datos[0]['control_interno'] . '"';
                                                                                                                                    } ?>>
                                    <label for="codinterno">Código de control interno</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container" style="max-width: 100% !important;">
                    <div class="row justify-items-md-center">
                        <div class="col align-items-center mb-3 ms-2" id="modal_footer">
                            <?php if (!$bandera) { ?>
                                <button class="btn btn-outline-success mt-2" onclick="obtiene_plus([`nom1`,`nom2`,`nom3`,`ape1`,`ape2`,`ape3`,`profesion`,`email`,`conyugue`,`fechanacimiento`,`edad`,`dirnac`,`numberdoc`,`numbernit`,`afiliggs`,`reside`,`dirviv`,`refviv`,`representante`,`refn1`,`ref1`,`refn2`,`ref2`,`refn3`,`ref3`,`tel1`,`tel2`,`telconyuge`,`zonaviv`,`barrioviv`,`hijos`,`dependencia`,`codinterno`],[`genero`,`estcivil`,`origen`,`paisnac`,`depnac`,`muninac`,`docextend`,`tipodoc`,`tipoidentri`,`nacionalidad`,`condicion`,`depdom`,`munidom`,`actpropio`,`actcalidad`,`otranacionalidad`,`etnia`,`religion`,`educacion`,`relinsti`,`agencidplus`],[`leer`,`escribir`,`firma`,`pep`,`cpe`],`create_cliente_natural`,`0`,['<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar cliente</button>
                                <button type="button" class="btn btn-outline-danger mt-2" onclick="printdiv2_plus('#cuadro','0')">
                                    <i class="fa-solid fa-ban"></i> Cancelar
                                </button>
                            <?php } ?>
                            <?php if ($bandera) { ?>
                                <button class="btn btn-outline-primary mt-2" onclick="obtiene_plus([`nom1`,`nom2`,`nom3`,`ape1`,`ape2`,`ape3`,`profesion`,`email`,`conyugue`,`fechanacimiento`,`edad`,`dirnac`,`numberdoc`,`numbernit`,`afiliggs`,`reside`,`dirviv`,`refviv`,`representante`,`refn1`,`ref1`,`refn2`,`ref2`,`refn3`,`ref3`,`tel1`,`tel2`,`telconyuge`,`zonaviv`,`barrioviv`,`hijos`,`dependencia`,`codinterno`],[`genero`,`estcivil`,`origen`,`paisnac`,`depnac`,`muninac`,`docextend`,`tipodoc`,`tipoidentri`,`nacionalidad`,`condicion`,`depdom`,`munidom`,`actpropio`,`actcalidad`,`otranacionalidad`,`etnia`,`religion`,`educacion`,`relinsti`,`agencidplus`],[`leer`,`escribir`,`firma`,`pep`,`cpe`],`update_cliente_natural`,'<?= $xtra; ?>',['<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>','<?= $xtra; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Actualizar cliente</button>

                                <button type="button" class="btn btn-outline-danger mt-2" onclick="printdiv('Editar_Cliente', '#cuadro', 'clientes_001', '0')">
                                    <i class="fa-solid fa-ban"></i> Cancelar
                                </button>
                            <?php } ?>
                            <!-- boton para solicitar credito -->
                            <button type="button" class="btn btn-outline-warning mt-2" onclick="salir()">
                                <i class="fa-solid fa-circle-xmark"></i> Salir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <script>
                function validateInputname(input) {
                    var inputValue = input.value.trim(); // Remueve espacios 
                    // Verificar si el valor no está vacío
                    if (inputValue === '') {
                        input.classList.remove('is-valid', 'is-invalid'); // Remover clases de validación
                    } else {
                        // Verificar 
                        if (/^[A-Za-zñÑáéíóúÁÉÍÓÚ]+$/.test(inputValue)) {
                            input.classList.remove('is-invalid');
                            input.classList.add('is-valid');
                        } else {
                            input.classList.remove('is-valid');
                            input.classList.add('is-invalid');
                        }
                    }
                }

                function validateInputprofesion(input) {
                    var inputValue = input.value.trim();
                    if (inputValue === '') {
                        input.classList.remove('is-valid', 'is-invalid');
                    } else {
                        if (/^[A-Za-zñÑáéíóúÁÉÍÓÚ ]+$/.test(inputValue)) {
                            input.classList.remove('is-invalid');
                            input.classList.add('is-valid');
                        } else {
                            input.classList.remove('is-valid');
                            input.classList.add('is-invalid');
                        }
                    }
                }

                function validateInputlastname(input) {
                    var inputValue = input.value.trim();

                    if (inputValue === '') {
                        input.classList.remove('is-valid', 'is-invalid');
                    } else {

                        if (/^[A-Za-zñÑáéíóúÁÉÍÓÚ, ]+$/.test(inputValue)) {
                            input.classList.remove('is-invalid');
                            input.classList.add('is-valid');
                        } else {
                            input.classList.remove('is-valid');
                            input.classList.add('is-invalid');
                        }
                    }
                }

                function toggleConyugueInput() {
                    var estadoCivilSelect = document.getElementById('estcivil');
                    var conyugueInput = document.getElementById('conyugue');
                    var telconyugueInput = document.getElementById('telconyuge');

                    if (estadoCivilSelect.value === 'CASADO') {
                        conyugueInput.disabled = false;
                        telconyugueInput.disabled = false;
                    } else {
                        conyugueInput.disabled = true;
                        telconyugueInput.disabled = true;
                    }
                }

                function validateInput(input) {
                    var inputValue = input.value.trim();
                    if (inputValue === '') {
                        input.classList.remove('is-valid', 'is-invalid');
                    } else {
                        if (/^[A-Za-zñÑáéíóúÁÉÍÓÚ,]+$/.test(inputValue)) {
                            input.classList.remove('is-invalid');
                            input.classList.add('is-valid');
                        } else {
                            input.classList.remove('is-valid');
                            input.classList.add('is-invalid');
                        }
                    }
                }

                function validateEmail(input) {
                    var email = input.value.trim();
                    if (email === '') {
                        input.classList.remove('is-valid', 'is-invalid');
                    } else {
                        var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (regex.test(email)) {
                            input.classList.remove('is-invalid');
                            input.classList.add('is-valid');
                        } else {
                            input.classList.remove('is-valid');
                            input.classList.add('is-invalid');
                        }
                    }
                }

                function validateInputlibre(input) {
                    var inputValue = input.value.trim();
                    if (inputValue === '') {
                        input.classList.remove('is-valid', 'is-invalid');
                    } else {
                        if (/^[a-zA-ZñÑáéíóúÁÉÍÓÚ,0-9\-\.\/\,\s]+$/.test(inputValue)) {
                            input.classList.remove('is-invalid');
                            input.classList.add('is-valid');
                        } else {
                            input.classList.remove('is-valid');
                            input.classList.add('is-invalid');
                        }
                    }
                }

                function validateInputdpi(input) {
                    var inputValue = input.value.trim();
                    if (inputValue === '') {
                        input.classList.remove('is-valid', 'is-invalid');
                    } else {
                        if (/^\d{13}$/.test(inputValue)) {
                            input.classList.remove('is-invalid');
                            input.classList.add('is-valid');
                        } else {
                            input.classList.remove('is-valid');
                            input.classList.add('is-invalid');
                        }
                    }
                }

                function validateInputtel(input) {
                    var inputValue = input.value.trim();
                    if (inputValue === '') {
                        input.classList.remove('is-valid', 'is-invalid');
                    } else {
                        if (/^\d{8}$/.test(inputValue)) {
                            input.classList.remove('is-invalid');
                            input.classList.add('is-valid');
                        } else {
                            input.classList.remove('is-valid');
                            input.classList.add('is-invalid');
                        }
                    }
                }

                function validateInputtelreF(input) {
                    var inputValue = input.value.trim();
                    if (inputValue === '') {
                        input.classList.remove('is-valid', 'is-invalid');
                    } else {
                        var numeroSinPrefijo = inputValue.replace(/\+\d+\s?/, '').trim();
                        if (/^\d{8,20}$/.test(numeroSinPrefijo)) {
                            input.classList.remove('is-invalid');
                            input.classList.add('is-valid');
                        } else {
                            input.classList.remove('is-valid');
                            input.classList.add('is-invalid');
                        }
                    }
                }

                function updateTributario() {
                    var tipoidentri = document.getElementById('tipoidentri');
                    var numberdoc = document.getElementById('numberdoc');
                    var numbernit = document.getElementById('numbernit');

                    if (tipoidentri.value === 'CUI') {
                        numbernit.value = numberdoc.value;
                        numbernit.maxLength = numberdoc.value
                            .length;
                    } else {
                        numbernit.value = '<?php echo $bandera ? $datos[0]['no_tributaria'] : ''; ?>';
                        numbernit.maxLength = 15;
                    }
                }


                //SELECCIONAR LOS CHECKBOXS DESPUES DE CARGAR EL DOM
                $(document).ready(function() {
                    ocultar_actuacion_propia(1);
                    <?php if ($bandera) { ?>
                        // concatenarValores(['nom1', 'nom2', 'nom3'], ['ape1', 'ape2', 'ape3'], 1, '#nomcorto');
                        // concatenarValores(['ape1', 'ape2', 'ape3'], ['nom1', 'nom2', 'nom3'], 2, '#nomcompleto');
                        seleccionarValueSelect('#genero', '<?= $datos[0]['genero']; ?>');
                        seleccionarValueSelect('#estcivil', '<?= $datos[0]['estado_civil']; ?>');
                        seleccionarValueSelect('#origen', '<?= $datos[0]['origen']; ?>');
                        calcularEdad_plus('<?= $datos[0]['date_birth'] ?>', '#edad');
                        seleccionarValueSelect('#docextend', '<?= $datos[0]['pais_extiende']; ?>');
                        seleccionarValueSelect('#tipodoc', '<?= $datos[0]['type_doc']; ?>');
                        ocultar_nit('<?= $datos[0]['pais_extiende']; ?>')
                        ejecutarDespuesDeBuscarMunicipios('buscar_municipios', '#muninac', '<?= $datos[0]['depa_nacio']; ?>',
                            '<?= $datos[0]['muni_nacio']; ?>');
                        ejecutarDespuesDeBuscarMunicipios('buscar_municipios', '#munidom', '<?= $datos[0]['depa_reside']; ?>',
                            '<?= $datos[0]['muni_reside']; ?>');
                        seleccionarValueSelect('#tipoidentri', '<?= $datos[0]['identi_tribu']; ?>');
                        seleccionarValueSelect('#actpropio', '<?= $datos[0]['actu_Propio']; ?>');
                        ocultar_actuacion_propia('<?= $datos[0]['repre_calidad']; ?>');
                        seleccionarValueSelect('#actcalidad', '<?= $datos[0]['repre_calidad']; ?>');
                        seleccionarValueSelect('#educacion', '<?= $datos[0]['educa']; ?>');
                        seleccionarValueSelect('#relinsti', '<?= $datos[0]['Rel_insti']; ?>');
                        seleccionarValueRadio('leer', '<?= $datos[0]['leer']; ?>');
                        seleccionarValueRadio('escribir', '<?= $datos[0]['escribir']; ?>');
                        seleccionarValueRadio('firma', '<?= $datos[0]['firma']; ?>');
                        seleccionarValueRadio('pep', '<?= $datos[0]['PEP']; ?>');
                        seleccionarValueRadio('cpe', '<?= $datos[0]['CPE']; ?>');
                    <?php }; ?>
                });
            </script>
        <?php
        }
        break;

    case 'create_perfil_economico': {
            $codusu = $_SESSION['id'];
            $id_agencia = $_SESSION['id_agencia'];
            $codagencia = $_SESSION['agencia'];
            $xtra = $_POST["xtra"];

            $bandera = false;
            $banderaingresos = false;
            $datos[] = [];
            $ingresos[] = [];

            $i = 0;
            if ($xtra != 0) {
                $consulta = mysqli_query($conexion, "SELECT cl.idcod_cliente AS codcli, cl.short_name AS nombre, cl.no_identifica AS dpi, cl.Direccion AS direccion, cl.date_birth AS fechacumple,  cl.tel_no1 AS telefono, cl.genero AS genero 
                FROM tb_cliente cl WHERE cl.estado=1 AND cl.idcod_cliente='$xtra'");
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $genero = ($fila['genero'] == 'F') ? 'Femenino' : 'Masculino';
                    $datos[$i] = $fila;
                    $datos[$i]['genero2'] = $genero;
                    $i++;
                    $bandera = true;
                }
                $i = 0;
                $consulta = mysqli_query($conexion, "SELECT ti.id_ingre_dependi AS idtipo, ti.Tipo_ingreso AS tipoingreso, ti.nombre_empresa AS nombreempresa, ti.direc_negocio AS direcnegocio, ti.sueldo_base AS sueldobase, ti.detalle_ingreso AS detalle_ingreso FROM tb_cliente tc INNER JOIN tb_ingresos ti ON tc.idcod_cliente = ti.id_cliente WHERE tc.idcod_cliente ='$xtra'");
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $tipoingreso = ($fila['tipoingreso'] == '1') ? 'Propio' : (($fila['tipoingreso'] == '2') ? 'En dependencia' : 'Otros');
                    $ingresos[$i] = $fila;
                    $ingresos[$i]['tipoingreso2'] = $tipoingreso;
                    $i++;
                    $banderaingresos = true;
                }
            }

            // echo '<pre>';
            // print_r($ingresos);
            // echo '</pre>';
        ?>
            <!--Aho_0_PrmtrzcAhrrs Inicio de Ahorro Sección 0 Parametros cuentas ahorro-->
            <input type="text" id="file" value="clientes_001" style="display: none;">
            <input type="text" id="condi" value="create_perfil_economico" style="display: none;">
            <div class="text" style="text-align:center">PERFIL ECONÓMICO</div>
            <div class="card">
                <div class="card-header">Ingreso de perfil económico</div>
                <div class="card-body" style="padding-bottom: 0px !important;">
                    <!-- INFORMACION DE CLIENTE -->
                    <!-- seleccion de cliente y su credito-->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Información de cliente</b></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="nomcli" placeholder="Nombre del cliente" readonly <?php if ($bandera) {
                                                                                                                                        echo 'value="' . $datos[0]['nombre'] . '"';
                                                                                                                                    } ?>>
                                    <label for="nomcli">Nombre del cliente</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <button type="button" class="btn btn-primary pt-3 pb-3 mb-2 mt-2 col-12 col-sm-12" data-bs-toggle="modal" data-bs-target="#buscar_cli_gen"><i class="fa-solid fa-magnifying-glass-plus me-2"></i>Buscar cliente</button>
                            </div>
                        </div>
                        <!-- cargo, nombre agencia y codagencia  -->
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-3">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="codcli" placeholder="Código de cliente" readonly <?php if ($bandera) {
                                                                                                                                        echo 'value="' . $datos[0]['codcli'] . '"';
                                                                                                                                    } ?>>
                                    <label for="codcli">Código de cliente</label>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="dpi" placeholder="DPI" readonly <?php if ($bandera) {
                                                                                                                    echo 'value="' . $datos[0]['dpi'] . '"';
                                                                                                                } ?>>
                                    <label for="dpi">DPI</label>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-md-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="direccion" placeholder="Dirección" readonly <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['direccion'] . '"';
                                                                                                                            } ?>>
                                    <label for="direccion">Dirección</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-3 mt-2">
                                    <input type="date" class="form-control" id="fechacumple" placeholder="Fecha de nacimiento" readonly <?php if ($bandera) {
                                                                                                                                            echo 'value="' . $datos[0]['fechacumple'] . '"';
                                                                                                                                        } ?>>
                                    <label for="fechacumple">Fecha de nacimiento</label>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-3 mt-2">
                                    <input type="text" class="form-control" id="telefono" placeholder="Teléfono" readonly <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['telefono'] . '"';
                                                                                                                            } ?>>
                                    <label for="telefono">Teléfono</label>
                                </div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-3 mt-2">
                                    <input type="text" class="form-control" id="genero" placeholder="Género" readonly <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['genero2'] . '"';
                                                                                                                        } ?>>
                                    <label for="genero">Género</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TABLA PARA LOS DISTINTOS TIPOS DE INGRESOS -->
                    <div class="container contenedort">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Ingresos del cliente</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="table-responsive">
                                    <table class="table nowrap table-hover table-border" id="tb_perfiles_economicos" style="width: 100% !important;">
                                        <thead class="text-light table-head-aprt">
                                            <tr style="font-size: 0.9rem;">
                                                <th>Tipo de Ingreso</th>
                                                <th>Nombre Empresa</th>
                                                <th>Dirección</th>
                                                <th>Ingresos</th>
                                                <th>Accciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-group-divider" style="font-size: 0.9rem !important;">
                                            <?php
                                            if ($bandera == false) { ?>
                                                <tr>
                                                    <td colspan="5" style="text-align: center;" class='alert alert-success' role='alert'>Debe seleccionar un cliente</td>
                                                </tr>

                                            <?php } else if ($banderaingresos == false && $bandera) { ?>
                                                <tr>
                                                    <td colspan="5" style="text-align: center;" class='alert alert-success' role='alert'>El cliente no tiene ningun tipo de ingreso</td>
                                                </tr>
                                            <?php } else { ?>
                                                <?php for ($i = 0; $i < count($ingresos); $i++) { ?>
                                                    <tr>
                                                        <td><?= ($ingresos[$i]["tipoingreso2"]) ?></td>
                                                        <td><?= ($ingresos[$i]["nombreempresa"]) ?></td>
                                                        <td><?= ($ingresos[$i]["tipoingreso"] == '3') ? $ingresos[$i]["detalle_ingreso"] : ($ingresos[$i]["direcnegocio"]) ?>
                                                        </td>
                                                        <td><?= ($ingresos[$i]["sueldobase"]) ?></td>
                                                        <td>
                                                            <button type="button" class="btn btn-success btn-sm" onclick="printdiv3_plus('section_tipos_ingresos', '#contenedor_tipos_ingresos',['<?= $ingresos[$i]['idtipo'] ?>','<?= $xtra ?>'])"><i class="fa-solid fa-eye"></i></button>
                                                            <button type="button" class="btn btn-danger btn-sm" onclick="obtiene_plus([],[],[],`delete_perfil_economico`,'<?= $xtra; ?>',['<?= $ingresos[$i]['idtipo']; ?>','<?= $xtra; ?>','<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>'])"><i class="fa-solid fa-trash"></i></button>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- NAVBAR PARA LOS DISTINTOS TIPOS DE INGRESOS -->
                    <div class="container contenedort pt-3" id="contenedor_tipos_ingresos">

                    </div>
                </div>
                <div class="container" style="max-width: 100% !important;">
                    <div class="row justify-items-md-center">
                        <div class="col align-items-center mb-3 ms-2" id="modal_footer">
                            <!-- boton para solicitar credito -->
                            <button type="button" class="btn btn-outline-danger mt-2" onclick="printdiv2_plus('#cuadro','0')">
                                <i class="fa-solid fa-ban"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-outline-warning mt-2" onclick="salir()">
                                <i class="fa-solid fa-circle-xmark"></i> Salir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <script>
                //SELECCIONAR LOS CHECKBOXS DESPUES DE CARGAR EL DOM
                $(document).ready(function() {
                    //ocultar_actuacion_propia(1);
                    <?php if ($xtra == 0) { ?>
                        printdiv3_plus('section_tipos_ingresos', '#contenedor_tipos_ingresos', ['0', '<?= $xtra; ?>']);
                    <?php } ?>
                    <?php if ($xtra != 0) { ?>
                        printdiv3_plus('section_tipos_ingresos', '#contenedor_tipos_ingresos', ['-1', '<?= $xtra; ?>']);
                    <?php } ?>
                });
            </script>
            <?php include_once "../../src/cris_modales/mdls_cli.php"; ?>
        <?php
        }
        break;
    case 'section_tipos_ingresos': {
            $codusu = $_SESSION['id'];
            $id_agencia = $_SESSION['id_agencia'];
            $codagencia = $_SESSION['agencia'];
            $xtra = $_POST["xtra"];

            $bandera = false;
            $banderagen = false;
            $datos[] = [];
            $nav1 = "";
            $nav2 = "";
            $nav3 = "";
            $navhead1 = "";
            $navhead2 = "";
            $navhead3 = "";
            $content1 = "";
            $content2 = "";
            $content3 = "";
            $rad1 = "";
            $rad2 = "";

            if ($xtra[0] == -1) {
                $banderagen = true;
                $xtra[0] = 0;
            }

            $i = 0;
            if ($xtra[0] != 0) {
                $consulta = mysqli_query($conexion, "SELECT ti.*, IFNULL((SELECT act.id_ActiEcono AS idactecono FROM clhpzzvb_bd_general_coopera.tb_ActiEcono act WHERE act.id_ActiEcono = ti.actividad_economica),'-') AS idactecono,
                    IFNULL((SELECT act.Titulo AS nomactecono FROM clhpzzvb_bd_general_coopera.tb_ActiEcono act WHERE act.id_ActiEcono = ti.actividad_economica),'-') AS nomactecono  
                    FROM tb_cliente tc INNER JOIN tb_ingresos ti ON tc.idcod_cliente = ti.id_cliente WHERE ti.id_ingre_dependi ='$xtra[0]'");
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $tipoingreso = ($fila['Tipo_ingreso'] == '1') ? 'Propio' : (($fila['Tipo_ingreso'] == '2') ? 'En dependencia' : 'Otros');
                    $datos[$i] = $fila;
                    $datos[$i]['tipoingreso2'] = $tipoingreso;
                    $i++;
                    $bandera = true;
                }
                if ($bandera) {
                    if ($datos[0]['Tipo_ingreso'] == '1') {
                        $nav1 = "active";
                        $content1 = "show active";
                        $navhead1 = "";
                        $navhead2 = "disabled";
                        $navhead3 = "disabled";
                    } elseif ($datos[0]['Tipo_ingreso'] == '2') {
                        $nav2 = "active";
                        $content2 = "show active";
                        $navhead1 = "disabled";
                        $navhead2 = "";
                        $navhead3 = "disabled";
                    } elseif ($datos[0]['Tipo_ingreso'] == '3') {
                        $nav3 = "active";
                        $content3 = "show active";
                        $navhead1 = "disabled";
                        $navhead2 = "disabled";
                        $navhead3 = "";
                    }
                }
                //validar patente
                if ($bandera) {
                    if ($datos[0]['patente'] == 'si') {
                        $rad1 = "checked";
                    } elseif ($datos[0]['patente'] == 'no') {
                        $rad2 = "checked";
                    } elseif ($datos[0]['patente'] == null || $datos[0]['patente'] == '') {
                        $rad2 = "checked";
                    }
                }
            }
            if ($xtra[0] == 0) {
                $nav1 = "active";
                $content1 = "show active";
                $rad1 = "checked";
            }

        ?>
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link <?= $nav1 ?> <?= $navhead1 ?>" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">Ingresos
                        propios</button>
                    <button class="nav-link <?= $nav2 ?> <?= $navhead2 ?>" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">Ingresos en dependencia</button>
                    <button class="nav-link <?= $nav3 ?> <?= $navhead3 ?>" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-contact" aria-selected="false">Otros ingresos</button>
                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <!-- INGRESOS PROPIOS -->
                <div class="tab-pane fade <?= $content1 ?>" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab" tabindex="0">
                    <!-- FORMULARIO PARA INGRESOS PROPIOS -->
                    <div class="row">
                        <div class="col-12 col-sm-12">
                            <div class="form-floating mb-2 mt-2">
                                <input type="text" class="form-control" id="nomnegocio" placeholder="Nombre negocio" <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['nombre_empresa'] . '"';
                                                                                                                        } ?>>
                                <label for="nomnegocio">Nombre negocio</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-12 col-md-5">
                            <div class="input-group mb-2 mt-2">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="actecono" placeholder="Mora" readonly <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['nomactecono'] . '"';
                                                                                                                        } ?>>
                                    <input type="text" class="form-control" id="idactecono" placeholder="Mora" readonly hidden <?php if ($bandera) {
                                                                                                                                    echo 'value="' . $datos[0]['idactecono'] . '"';
                                                                                                                                } ?>>
                                    <label for="actecono">Actividad económica</label>
                                </div>
                                <span type="button" class="input-group-text bg-primary text-white" id="bt_act" onclick="abrir_modal('#modal_acteconomica', '#id_modal_hidden', 'idactecono,actecono/A,A/'+'/#/#/#/#')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="form-floating mb-2 mt-2">
                                <input type="date" class="form-control" id="fecinscri" placeholder="Fecha de inicio/inscripción" value="<?php echo ($bandera) ? (($datos[0]['fecha_labor'] == null || $datos[0]['fecha_labor'] == '') ? date("Y-m-d") : $datos[0]['fecha_labor']) : date("Y-m-d"); ?>">
                                <label for="fecinscri">Fecha inicio</label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="row">
                                <div class="col-12 mt-1">
                                    <span class="badge text-bg-primary">¿Tiene patente?</span>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="patente" id="flexRadioDefault1" <?= $rad1; ?> value="si" onclick="habilitar_deshabilitar(['registro','folio','libro'],[])">
                                        <label class="form-check-label" for="flexRadioDefault1">
                                            Si
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="patente" id="flexRadioDefault2" <?= $rad2; ?> value="no" onclick="habilitar_deshabilitar([],['registro','folio','libro']); limpiarhabdes([],['registro','folio','libro'])">
                                        <label class="form-check-label" for="flexRadioDefault2">
                                            No
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-floating mb-2 mt-2">
                                <input type="text" class="form-control" id="registro" placeholder="Registro" <?php if ($bandera) {
                                                                                                                    echo 'value="' . $datos[0]['no_registro'] . '"';
                                                                                                                } ?>>
                                <label for="registro">Registro</label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-floating mb-2 mt-2">
                                <input type="text" class="form-control" id="folio" placeholder="Folio" <?php if ($bandera) {
                                                                                                            echo 'value="' . $datos[0]['folio'] . '"';
                                                                                                        } ?>>
                                <label for="folio">Folio</label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-floating mb-2 mt-2">
                                <input type="text" class="form-control" id="libro" placeholder="Libro" <?php if ($bandera) {
                                                                                                            echo 'value="' . $datos[0]['libro'] . '"';
                                                                                                        } ?>>
                                <label for="libro">Libro</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-floating mb-2 mt-2">
                                <input type="number" class="form-control" id="telefono" placeholder="Telefono" <?php if ($bandera) {
                                                                                                                    echo 'value="' . $datos[0]['telefono_negocio'] . '"';
                                                                                                                } ?>>
                                <label for="telefono">Telefono</label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-floating mb-2 mt-2">
                                <select class="form-select" id="condicionlocal">
                                    <?php
                                    $selected = "";
                                    ($datos[0]['condi_negocio'] == null || $datos[0]['condi_negocio'] == '') ? $datos[0]['condi_negocio'] = 1 : ' ';
                                    $traerCondicion = mysqli_query($general, "SELECT * FROM `tb_negocio`");
                                    while ($r = mysqli_fetch_array($traerCondicion, MYSQLI_ASSOC)) {
                                        if ($bandera) {
                                            ($datos[0]['condi_negocio'] == $r[0]['id_Negocio']) ? $selected = "selected" : $selected = "";
                                        }
                                        $idc = utf8_encode($r["id_Negocio"]);
                                        $neg = utf8_encode($r["Negocio"]);
                                        echo ' <option value="' . $idc . '" ' . $selected . ' >' . $neg . '</option>';
                                    }
                                    ?>
                                </select>
                                <label for="etnia">Condición local</label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-floating mb-2 mt-2">
                                <input type="text" class="form-control" id="ingresos" placeholder="Ingresos mensual estimado" <?php if ($bandera) {
                                                                                                                                    echo 'value="' . $datos[0]['sueldo_base'] . '"';
                                                                                                                                } ?>>
                                <label for="ingresos">Ingresos mensual estimado</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-4">
                            <div class="form-floating mb-2 mt-2">
                                <select class="form-select" id="deppropio" onchange="buscar_municipios('buscar_municipios', '#munipropio', this.value)">
                                    <option value="0" selected>Seleccione un departamento</option>
                                    <?php
                                    $selected = "";
                                    $departa = mysqli_query($general, "SELECT * FROM `departamentos`");
                                    while ($municipalidad = mysqli_fetch_array($departa, MYSQLI_ASSOC)) {
                                        if ($bandera) {
                                            ($datos[0]['depa_negocio'] == $municipalidad["codigo_departamento"]) ? $selected = "selected" : $selected = "";
                                        }
                                        $nombre = ($municipalidad["nombre"]);
                                        $codigo_departa = $municipalidad["codigo_departamento"];
                                        echo '<option value="' . $codigo_departa . '" ' . $selected . '>' . $nombre . '</option>';
                                    } ?>
                                </select>
                                <label for="deppropio">Departamento</label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="form-floating mb-2 mt-2">
                                <select class="form-select" id="munipropio">
                                    <option value="0" selected>Seleccione un municipio</option>
                                </select>
                                <label for="munipropio">Municipio</label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="form-floating mb-2 mt-2">
                                <input type="number" class="form-control" id="noempleados" placeholder="# Empleados" <?php if ($bandera) echo 'value="' . $datos[0]['empleados'] . '"'; ?>>
                                <label for="noempleados"># Empleados</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-floating mb-2 mt-2">
                                <input type="text" class="form-control" id="direccion" placeholder="Dirección" <?php if ($bandera) {
                                                                                                                    echo 'value="' . $datos[0]['direc_negocio'] . '"';
                                                                                                                } ?>>
                                <label for="direccion">Dirección</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-floating mb-2 mt-2">
                                <input type="text" class="form-control" id="referencia" placeholder="Referencia" <?php if ($bandera) echo 'value="' . $datos[0]['referencia'] . '"'; ?>>
                                <label for="referencia">Referencia</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-2 d-flex justify-content-center">
                            <?php if ($banderagen) { ?>
                                <button class="btn btn-outline-success mt-2" onclick="obtiene_plus([`nomnegocio`,`idactecono`,`actecono`,`fecinscri`,`registro`,`folio`,`libro`,`telefono`,`ingresos`,`direccion`,`noempleados`,`referencia`],[`condicionlocal`,`deppropio`,`munipropio`],[`patente`],`create_ingreso_propio`,'<?= $xtra[1]; ?>',['<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>','<?= $xtra[1]; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar</button>
                            <?php } ?>
                            <?php if ($bandera) { ?>
                                <button class="btn btn-outline-primary mt-2" onclick="obtiene_plus([`nomnegocio`,`idactecono`,`actecono`,`fecinscri`,`registro`,`folio`,`libro`,`telefono`,`ingresos`,`direccion`,`noempleados`,`referencia`],[`condicionlocal`,`deppropio`,`munipropio`],[`patente`],`update_ingreso_propio`,'<?= $xtra[1]; ?>',['<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>','<?= $xtra[1]; ?>','<?= $xtra[0]; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Actualizar</button>
                                <button class="btn btn-outline-danger mt-2" onclick="printdiv3_plus('section_tipos_ingresos', '#contenedor_tipos_ingresos', ['-1', '<?= $xtra[1]; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Cancelar actualización</button>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <!-- INGRESOS DEPENDIENTES -->
                <div class="tab-pane fade <?= $content2 ?>" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab" tabindex="0">
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-floating mb-2 mt-2">
                                <select id="sector" class="form-select">
                                    <option value="1">Sector Público</option>
                                    <option value="2">Sector Privado</option>
                                </select>
                                <label for="sector">Sector</label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-floating mb-2 mt-2">
                                <input type="text" class="form-control" id="nomnegocio2" placeholder="Nombre empresa" <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['nombre_empresa'] . '"';
                                                                                                                        } ?>>
                                <label for="nomnegocio2">Nombre empresa</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-4 col-md-4">
                            <div class="input-group mb-2 mt-2">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="actecono2" placeholder="Mora" readonly <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['nomactecono'] . '"';
                                                                                                                        } ?>>
                                    <input type="text" class="form-control" id="idactecono2" placeholder="Mora" readonly hidden <?php if ($bandera) {
                                                                                                                                    echo 'value="' . $datos[0]['idactecono'] . '"';
                                                                                                                                } ?>>
                                    <label for="actecono">Actividad económica</label>
                                </div>
                                <span type="button" class="input-group-text bg-primary text-white" id="bt_act" onclick="abrir_modal('#modal_acteconomica', '#id_modal_hidden', 'idactecono2,actecono2/A,A/'+'/#/#/#/#')"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4 col-md-4">
                            <div class="form-floating mb-2 mt-2">
                                <input type="text" class="form-control" id="puesto" placeholder="Puesto en la empresa" <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['puesto_ocupa'] . '"';
                                                                                                                        } ?>>
                                <label for="puesto">Puesto en la empresa</label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4 col-md-4">
                            <div class="form-floating mb-2 mt-2">
                                <input type="date" class="form-control" id="fecinicio" placeholder="Fecha de inicio" <?php echo ($bandera) ? 'value="' . $datos[0]['fecha_labor'] . '"' : date('Y-m-d'); ?>>
                                <label for="puesto">Fecha Inicio</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-floating mb-2 mt-2">
                                <input type="text" class="form-control" id="direccion_dependencia" placeholder="Dirección de la empresa" <?php if ($bandera) {
                                                                                                                                                echo 'value="' . $datos[0]['direc_negocio'] . '"';
                                                                                                                                            } ?>>
                                <label for="direccion_dependencia">Direccion de la empresa</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-12 col-md-4">
                            <div class="form-floating mb-2 mt-2">
                                <select class="form-select" id="deppropio2" onchange="buscar_municipios('buscar_municipios', '#munipropio2', this.value)">
                                    <option value="0" selected>Seleccione un departamento</option>
                                    <?php
                                    $selected = "";
                                    $departa = mysqli_query($general, "SELECT * FROM `departamentos`");
                                    while ($municipalidad = mysqli_fetch_array($departa, MYSQLI_ASSOC)) {
                                        if ($bandera) {
                                            ($datos[0]['depa_negocio'] == $municipalidad["codigo_departamento"]) ? $selected = "selected" : $selected = "";
                                        }
                                        $nombre = ($municipalidad["nombre"]);
                                        $codigo_departa = $municipalidad["codigo_departamento"];
                                        echo '<option value="' . $codigo_departa . '" ' . $selected . '>' . $nombre . '</option>';
                                    } ?>
                                </select>
                                <label for="deppropio2">Departamento</label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-floating mb-2 mt-2">
                                <select class="form-select" id="munipropio2">
                                    <option value="0" selected>Seleccione un municipio</option>
                                </select>
                                <label for="munipropio2">Municipio</label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-floating mb-2 mt-2">
                                <input type="text" class="form-control" id="monto_dependencia" placeholder="Ingreso mensual" <?php if ($bandera) {
                                                                                                                                    echo 'value="' . $datos[0]['sueldo_base'] . '"';
                                                                                                                                } ?>>
                                <label for="monto_dependencia">Ingreso mensual</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-2 d-flex justify-content-center">
                            <?php if ($banderagen) { ?>
                                <button class="btn btn-outline-success mt-2" onclick="obtiene_plus([`nomnegocio2`,`idactecono2`,`actecono2`,`puesto`,`monto_dependencia`,`direccion_dependencia`,`fecinicio`],[`sector`,`deppropio2`,`munipropio2`],[],`create_ingreso_dependiente`,'<?= $xtra[1]; ?>',['<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>','<?= $xtra[1]; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar</button>
                            <?php } ?>
                            <?php if ($bandera) { ?>
                                <button class="btn btn-outline-primary mt-2" onclick="obtiene_plus([`nomnegocio2`,`idactecono2`,`actecono2`,`puesto`,`monto_dependencia`,`direccion_dependencia`,`fecinicio`],[`sector`,`deppropio2`,`munipropio2`],[],`update_ingreso_dependiente`,'<?= $xtra[1]; ?>',['<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>','<?= $xtra[1]; ?>','<?= $xtra[0]; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Actualizar</button>
                                <button class="btn btn-outline-danger mt-2" onclick="printdiv3_plus('section_tipos_ingresos', '#contenedor_tipos_ingresos', ['-1', '<?= $xtra[1]; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Cancelar actualización</button>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade <?= $content3 ?>" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab" tabindex="0">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-floating mb-2 mt-2">
                                <select id="otros_ingresos1" class="form-select">
                                    <option value="Actividades profesionales">Actividades profesionales</option>
                                    <option value="Manutención">Manutención</option>
                                    <option value="Rentas">Rentas</option>
                                    <option value="Jubilación">Jubilación</option>
                                    <option value="Otros">Otros</option>
                                </select>
                                <label for="otros_ingresos1">Tipos de ingresos</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-8">
                            <div class="form-floating mb-2 mt-2">
                                <input type="text" class="form-control" id="det_ingreso" placeholder="Detalle ingreso" <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['detalle_ingreso'] . '"';
                                                                                                                        } ?>>
                                <label for="det_ingreso">Detalle ingreso</label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="form-floating mb-2 mt-2">
                                <input type="text" class="form-control" id="monto_otros3" placeholder="Monto aproximado" <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['sueldo_base'] . '"';
                                                                                                                            } ?>>
                                <label for="monto_otros3">Monto aproximado</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-2 d-flex justify-content-center">
                            <?php if ($banderagen) { ?>
                                <button class="btn btn-outline-success mt-2" onclick="obtiene_plus([`det_ingreso`,`monto_otros3`],[`otros_ingresos1`],[],`create_otros_ingresos`,'<?= $xtra[1]; ?>',['<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>','<?= $xtra[1]; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar</button>
                            <?php } ?>
                            <?php if ($bandera) { ?>
                                <button class="btn btn-outline-primary mt-2" onclick="obtiene_plus([`det_ingreso`,`monto_otros3`],[`otros_ingresos1`],[],`update_otros_ingresos`,'<?= $xtra[1]; ?>',['<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>','<?= $xtra[1]; ?>','<?= $xtra[0]; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Actualizar</button>
                                <button class="btn btn-outline-danger mt-2" onclick="printdiv3_plus('section_tipos_ingresos', '#contenedor_tipos_ingresos', ['-1', '<?= $xtra[1]; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Cancelar actualización</button>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                //SELECCIONAR LOS CHECKBOXS DESPUES DE CARGAR EL DOM
                $(document).ready(function() {
                    //CARGADO DE CHECKBOXS
                    <?php if ($rad2 != 'checked') { ?>
                        habilitar_deshabilitar(['registro', 'folio', 'libro'], []);
                    <?php } else { ?>
                        habilitar_deshabilitar([], ['registro', 'folio', 'libro'])
                    <?php } ?>
                    <?php if ($bandera && $datos[0]['Tipo_ingreso'] == '1') { ?>
                        ejecutarDespuesDeBuscarMunicipios('buscar_municipios', '#munipropio', '<?= $datos[0]['depa_negocio']; ?>',
                            '<?= $datos[0]['muni_negocio']; ?>');
                    <?php } ?>
                    <?php if ($bandera && $datos[0]['Tipo_ingreso'] == '2') { ?>
                        seleccionarValueSelect('#sector',
                            '<?= ($datos[0]['sector_Econo'] == null || $datos[0]['sector_Econo'] == '') ? 1 : $datos[0]['sector_Econo']; ?>'
                        );
                    <?php } ?>
                    <?php if ($bandera && $datos[0]['Tipo_ingreso'] == '2') { ?>
                        ejecutarDespuesDeBuscarMunicipios('buscar_municipios', '#munipropio2', '<?= $datos[0]['depa_negocio']; ?>',
                            '<?= $datos[0]['muni_negocio']; ?>');
                    <?php } ?>
                    <?php if ($bandera && $datos[0]['Tipo_ingreso'] == '3') { ?>
                        seleccionarValueSelect('#otros_ingresos1', '<?= $datos[0]['nombre_empresa']; ?>');
                    <?php } ?>
                });
            </script>
        <?php
        }
        break;
    case 'create_cliente_juridico': {
            $codusu = $_SESSION['id'];
            $id_agencia = $_SESSION['id_agencia'];
            $codagencia = $_SESSION['agencia'];
            $xtra = $_POST["xtra"];

            $bandera = false;
            $bandera_socios = false;
            $datos[] = [];
            $socios[] = [];

            $i = 0;
            if ($xtra != 0) {
                $consulta = mysqli_query($conexion, "SELECT tc.idcod_cliente AS codcli, tc.short_name AS nombre, tc.compl_name AS nomcompleto, tc.no_identifica AS registro, tc.representante_name AS representante, tc.date_birth AS fechafun, tc.depa_reside AS departamento, tc.muni_reside AS municipio, tc.aldea_reside AS referencia, tc.Direccion AS direccion FROM tb_cliente tc WHERE tc.idcod_cliente='$xtra'");
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $datos[$i] = $fila;
                    $i++;
                    $bandera = true;
                }
                $i = 0;
                $consulta = mysqli_query($conexion, "SELECT * FROM tb_socios_juri tsj WHERE tsj.id_clnt_ntral ='$xtra'");
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $socios[$i] = $fila;
                    $i++;
                    $bandera_socios = true;
                }
            }
            // echo '<pre>';
            // print_r($socios);
            // echo '</pre>';
        ?>
            <!--Aho_0_PrmtrzcAhrrs Inicio de Ahorro Sección 0 Parametros cuentas ahorro-->
            <input type="text" id="file" value="clientes_001" style="display: none;">
            <input type="text" id="condi" value="create_cliente_juridico" style="display: none;">
            <div class="text" style="text-align:center">INGRESO DE CLIENTES JURIDICOS</div>
            <div class="card">
                <div class="card-header">Ingreso de cliente jurídico</div>
                <div class="card-body" style="padding-bottom: 0px !important;">
                    <!-- TABLA PARA LOS CLIENTES JURIDICOS -->
                    <div class="container contenedort">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Listado de clientes jurídicos</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="table-responsive">
                                    <table class="table nowrap table-hover table-border" id="tb_clientes_juridicos" style="width: 100% !important;">
                                        <thead class="text-light table-head-aprt">
                                            <tr style="font-size: 0.9rem;">
                                                <th>Código</th>
                                                <th>Nombre comercial</th>
                                                <th>Registro sociedad</th>
                                                <th>F. Fundación</th>
                                                <th>Accciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-group-divider" style="font-size: 0.9rem !important;">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- INFORMACION DE CLIENTE -->
                    <!-- seleccion de cliente y su credito-->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Información de cliente jurídico</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="codcli" placeholder="Código de cliente" hidden readonly <?php if ($bandera) {
                                                                                                                                            echo 'value="' . $datos[0]['codcli'] . '"';
                                                                                                                                        } ?>>

                                    <input type="text" class="form-control" id="razonsocial" placeholder="Razón social" <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['nombre'] . '"';
                                                                                                                        } ?>>
                                    <label for="razonsocial">Razón social</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-7">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="razoncomercial" placeholder="Nombre comercial" <?php if ($bandera) {
                                                                                                                                    echo 'value="' . $datos[0]['nomcompleto'] . '"';
                                                                                                                                } ?>>
                                    <label for="razoncomercial">Nombre comercial</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-5">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="registrosociedad" placeholder="No. Registro sociedad" <?php if ($bandera) {
                                                                                                                                            echo 'value="' . $datos[0]['registro'] . '"';
                                                                                                                                        } ?>>
                                    <label for="registrosociedad">No. Registro sociedad</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-7">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="representantelegal" placeholder="Representante legal" <?php if ($bandera) {
                                                                                                                                            echo 'value="' . $datos[0]['representante'] . '"';
                                                                                                                                        } ?>oninput="validateInputlibre(this)">
                                    <label for="representantelegal">Representante legal</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-5">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="date" class="form-control" id="fechafundacion" placeholder="Fecha fundación" <?php if ($bandera) {
                                                                                                                                    echo 'value="' . $datos[0]['fechafun'] . '"';
                                                                                                                                } ?>>
                                    <label for="fechafundacion">Fecha fundación</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="depclientejuridico" onchange="buscar_municipios('buscar_municipios', '#municlientejuridico', this.value)">
                                        <option value="0" selected>Seleccione un departamento</option>
                                        <?php
                                        $selected = "";
                                        $departa = mysqli_query($general, "SELECT * FROM `departamentos`");
                                        while ($municipalidad = mysqli_fetch_array($departa, MYSQLI_ASSOC)) {
                                            if ($bandera) {
                                                ($datos[0]['departamento'] == $municipalidad["codigo_departamento"]) ? $selected = "selected" : $selected = "";
                                            }
                                            $nombre = ($municipalidad["nombre"]);
                                            $codigo_departa = $municipalidad["codigo_departamento"];
                                            echo '<option value="' . $codigo_departa . '" ' . $selected . '>' . $nombre . '</option>';
                                        } ?>
                                    </select>
                                    <label for="depclientejuridico">Departamento</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="municlientejuridico">
                                        <option value="0" selected>Seleccione un municipio</option>
                                    </select>
                                    <label for="municlientejuridico">Municipio</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="referenciajuridica" placeholder="Referencia" <?php if ($bandera) {
                                                                                                                                    echo 'value="' . ((isset($datos[0]['referencia'])) ? $datos[0]['referencia'] : '-') . '"';
                                                                                                                                } ?>>
                                    <label for="referenciajuridica">Referencia</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="direccionjuridica" placeholder="Domicilio fiscal" <?php if ($bandera) {
                                                                                                                                        echo 'value="' . $datos[0]['direccion'] . '"';
                                                                                                                                    } ?>>
                                    <label for="direccionjuridica">Domicilio fiscal</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- NAVBAR PARA LOS DISTINTOS TIPOS DE INGRESOS -->
                    <div class="container contenedort pt-3">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Nombres de socios principales</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6  mb-2 mt-2">
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon2"><i class="fa-solid fa-users-line"></i></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="nompresidente" placeholder="Presidente(a)" <?php if ($bandera) {
                                                                                                                                    echo 'value="' . ((isset($socios[0]['name_socio'])) ? $socios[0]['name_socio'] : '-') . '"';
                                                                                                                                } ?>>
                                        <label for="nompresidente">Presidente</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 mb-2 mt-2">
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon2"><i class="fa-solid fa-users-line"></i></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="nomvicepresidente" placeholder="Vicepresidente(a)" <?php if ($bandera) {
                                                                                                                                            echo 'value="' . ((isset($socios[1]['name_socio'])) ? $socios[1]['name_socio'] : '-') . '"';
                                                                                                                                        } ?>>
                                        <label for="nomvicepresidente">Vicepresidente(a)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6 mb-2 mt-2">
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon2"><i class="fa-solid fa-users-line"></i></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="nomsecretario" placeholder="Secretario(a)" <?php if ($bandera) {
                                                                                                                                    echo 'value="' . ((isset($socios[2]['name_socio'])) ? $socios[2]['name_socio'] : '-') . '"';
                                                                                                                                } ?>>
                                        <label for="nomsecretario">Secretario(a)</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 mb-2 mt-2">
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon2"><i class="fa-solid fa-users-line"></i></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="nomtesorero" placeholder="Tesorero(a)" <?php if ($bandera) {
                                                                                                                                echo 'value="' . ((isset($socios[3]['name_socio'])) ? $socios[3]['name_socio'] : '-') . '"';
                                                                                                                            } ?>>
                                        <label for="nomtesorero">Tesorero(a)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6 mb-2 mt-2">
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon2"><i class="fa-solid fa-users-line"></i></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="nomvocal1" placeholder="Vocal 1" <?php if ($bandera) {
                                                                                                                            echo 'value="' . ((isset($socios[4]['name_socio'])) ? $socios[4]['name_socio'] : '-') . '"';
                                                                                                                        } ?>>
                                        <label for="nomvocal1">Vocal 1</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 mb-2 mt-2">
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon2"><i class="fa-solid fa-users-line"></i></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="nomvocal2" placeholder="Vocal 2" <?php if ($bandera) {
                                                                                                                            echo 'value="' . ((isset($socios[5]['name_socio'])) ? $socios[5]['name_socio'] : '-') . '"';
                                                                                                                        } ?>>
                                        <label for="nomvocal2">Vocal 2</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container" style="max-width: 100% !important;">
                    <div class="row justify-items-md-center">
                        <div class="col align-items-center mb-3 ms-2" id="modal_footer">
                            <?php if (!$bandera) { ?>
                                <button class="btn btn-outline-success mt-2" onclick="obtiene_plus([`razonsocial`,`razoncomercial`,`registrosociedad`,`representantelegal`,`fechafundacion`,`referenciajuridica`,`direccionjuridica`,`nompresidente`,`nomvicepresidente`,`nomsecretario`,`nomtesorero`,`nomvocal1`,`nomvocal2`],[`depclientejuridico`,`municlientejuridico`],[],`create_cliente_juridico`,`0`,['<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar</button>
                            <?php } ?>
                            <?php if ($bandera) { ?>
                                <button class="btn btn-outline-primary mt-2" onclick="obtiene_plus([`razonsocial`,`razoncomercial`,`registrosociedad`,`representantelegal`,`fechafundacion`,`referenciajuridica`,`direccionjuridica`,`nompresidente`,`nomvicepresidente`,`nomsecretario`,`nomtesorero`,`nomvocal1`,`nomvocal2`],[`depclientejuridico`,`municlientejuridico`],[],`update_cliente_juridico`,`0`,['<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>','<?= $datos[0]['codcli']; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Actualizar</button>
                            <?php } ?>
                            <!-- boton para solicitar credito -->
                            <button type="button" class="btn btn-outline-danger mt-2" onclick="printdiv2_plus('#cuadro','0')">
                                <i class="fa-solid fa-ban"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-outline-warning mt-2" onclick="salir()">
                                <i class="fa-solid fa-circle-xmark"></i> Salir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                //SELECCIONAR LOS CHECKBOXS DESPUES DE CARGAR EL DOM
                $(document).ready(function() {
                    $("#tb_clientes_juridicos").DataTable({
                        "processing": true,
                        "serverSide": true,
                        "sAjaxSource": "../src/server_side/clientes_juridicos.php",
                        "columnDefs": [{
                            "data": 0,
                            "targets": 4,
                            render: function(data, type, row) {
                                return `<button type="button" class="btn btn-success btn-sm" onclick="printdiv2('#cuadro','${data}')" >Editar</button>`;
                            }
                        }, ],
                        "lengthMenu": [
                            [5, 10, 25, 50, 100],
                            ['5', '10', '25', '50', '100']
                        ],
                        "bDestroy": true,
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
                            "sProcessing": "Procesando..."
                        }

                    });
                    <?php if ($bandera) { ?>
                        ejecutarDespuesDeBuscarMunicipios('buscar_municipios', '#municlientejuridico',
                            '<?= $datos[0]['departamento']; ?>', '<?= $datos[0]['municipio']; ?>');
                    <?php } ?>
                });
            </script>
        <?php
        }
        break;
    case 'list_clientes':
        ?>
        <input type="text" id="file" value="clientes_001" style="display: none;">
        <input type="text" id="condi" value="list_clientes" style="display: none;">
        <div class="text" style="text-align:center">FICHAR CLIENTES</div>
        <div class="card">
            <div class="card-header">Listado de clientes</div>
            <div class="card-body" style="padding-bottom: 0px !important;">
                <!-- TABLA PARA LOS CLIENTES JURIDICOS -->
                <div class="row border-bottom">
                    <div class="col mb-2">
                        <div class="table-responsive">
                            <table class="table nowrap table-hover table-border" id="tb_clientes" style="width: 100% !important;">
                                <thead class="text-light table-head-aprt">
                                    <tr style="font-size: 0.9rem;">
                                        <th>Código</th>
                                        <th>Nombre Completo</th>
                                        <th>No. Identificación</th>
                                        <th>Fec. Nacimiento</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="table-group-divider" style="font-size: 0.9rem !important;">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container" style="max-width: 100% !important;">
                <div class="row justify-items-md-center">
                    <div class="col align-items-center mb-3 ms-2" id="modal_footer">
                        <!-- boton para solicitar credito -->
                        <button type="button" class="btn btn-outline-danger mt-2" onclick="printdiv2_plus('#cuadro','0')">
                            <i class="fa-solid fa-ban"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-outline-warning mt-2" onclick="salir()">
                            <i class="fa-solid fa-circle-xmark"></i> Salir
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $("#tb_clientes").DataTable({
                    "processing": true,
                    "serverSide": true,
                    "sAjaxSource": "../src/server_side/clientes_reporte.php",
                    "columnDefs": [{
                        "data": 0,
                        "targets": 4,
                        render: function(data, type, row) {
                            return `
                                            <button class="btn btn-outline-success" onclick="fichacli('${data}')" >Ficha PDF</button>
                                            <button  class="btn btn-outline-warning" onclick="generar_json('${data}')" >JSON</button>
                                        `;
                        }

                    }, ],
                    "bDestroy": true,
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
                        "sProcessing": "Procesando..."
                    }

                });
            });
        </script>
    <?php
        break;

    case 'Editar_Cliente':
    ?>
        <input type="text" id="file" value="clientes_001" style="display: none;">
        <input type="text" id="condi" value="Editar_Cliente" style="display: none;">
        <div class="text" style="text-align:center">EDICION DE CLIENTES</div>
        <div class="card">
            <div class="card-header">Listado de clientes</div>
            <div class="card-body" style="padding-bottom: 0px !important;">
                <!-- TABLA PARA LOS CLIENTES JURIDICOS -->
                <div class="row border-bottom">
                    <div class="col mb-2">
                        <div class="table-responsive">
                            <table class="table nowrap table-hover table-border" id="tb_clientes" style="width: 100% !important;">
                                <thead class="text-light table-head-aprt">
                                    <tr style="font-size: 0.9rem;">
                                        <th>Código</th>
                                        <th>Nombre Completo</th>
                                        <th>No. Identificación</th>
                                        <th>Fec. Nacimiento</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="table-group-divider" style="font-size: 0.9rem !important;">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container" style="max-width: 100% !important;">
                <div class="row justify-items-md-center">
                    <div class="col align-items-center mb-3 ms-2" id="modal_footer">
                        <!-- boton para solicitar credito -->
                        <button type="button" class="btn btn-outline-danger mt-2" onclick="printdiv2_plus('#cuadro','0')">
                            <i class="fa-solid fa-ban"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-outline-warning mt-2" onclick="salir()">
                            <i class="fa-solid fa-circle-xmark"></i> Salir
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $("#tb_clientes").DataTable({
                    "processing": true,
                    "serverSide": true,
                    "sAjaxSource": "../src/server_side/clientes_reporte.php",
                    "columnDefs": [{
                        "data": 0,
                        "targets": 4,
                        render: function(data, type, row) {
                            return `
                                            <button type="button"  class="btn btn-outline-warning" onclick="printdiv('create_cliente_natural', '#cuadro', 'clientes_001', '${data}')" >Editar</button>
                                        `;
                        }

                    }, ],
                    "bDestroy": true,
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
                        "sProcessing": "Procesando..."
                    }

                });
            });
        </script>
    <?php
        break;
    case 'Delete_Cliente':
    ?>
        <input type="text" id="file" value="clientes_001" style="display: none;">
        <input type="text" id="condi" value="Delete_Cliente" style="display: none;">
        <div class="text" style="text-align:center">ELIMINACION DE CLIENTES</div>
        <div class="card">
            <div class="card-header">Listado de clientes</div>
            <div class="card-body" style="padding-bottom: 0px !important;">
                <!-- TABLA PARA LOS CLIENTES JURIDICOS -->
                <div class="row border-bottom">
                    <div class="col mb-2">
                        <div class="table-responsive">
                            <table class="table nowrap table-hover table-border" id="tb_clientes" style="width: 100% !important;">
                                <thead class="text-light table-head-aprt">
                                    <tr style="font-size: 0.9rem;">
                                        <th>Código</th>
                                        <th>Nombre Completo</th>
                                        <th>No. Identificación</th>
                                        <th>Fec. Nacimiento</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="table-group-divider" style="font-size: 0.9rem !important;">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container" style="max-width: 100% !important;">
                <div class="row justify-items-md-center">
                    <div class="col align-items-center mb-3 ms-2" id="modal_footer">
                        <!-- boton para solicitar credito -->
                        <button type="button" class="btn btn-outline-danger mt-2" onclick="printdiv2_plus('#cuadro','0')">
                            <i class="fa-solid fa-ban"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-outline-warning mt-2" onclick="salir()">
                            <i class="fa-solid fa-circle-xmark"></i> Salir
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $("#tb_clientes").DataTable({
                    "processing": true,
                    "serverSide": true,
                    "sAjaxSource": "../src/server_side/clientes_reporte.php",
                    "columnDefs": [{
                        "data": 0,
                        "targets": 4,
                        render: function(data, type, row) {
                            return `
                                            <button type="button" class="btn btn-outline-danger" onclick="printdiv('delete_user', '#cuadro', 'clientes_001', '${data}')">
                                            <i class="fas fa-trash"></i> Eliminar
                                            </button>

                                        `;
                        }

                    }, ],
                    "bDestroy": true,
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
                        "sProcessing": "Procesando..."
                    }

                });
            });
        </script>
        <?php
        break;

    case 'delete_user': {
            $codusu = $_SESSION['id'];
            $id_agencia = $_SESSION['id_agencia'];
            $codagencia = $_SESSION['agencia'];
            $xtra = $_POST["xtra"];

            $bandera = false;
            $datos[] = [];
            $isfile = false;
            $i = 0;
            if ($xtra != 0) {
                $consulta = mysqli_query($conexion, "SELECT * FROM tb_cliente tc WHERE tc.estado='1' AND tc.idcod_cliente='$xtra'");
                while ($fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC)) {
                    $datos[$i] = $fila;
                    //CARGADO DE LA IMAGEN
                    $imgurl = __DIR__ . '/../../../' . $fila['url_img'];
                    if (!is_file($imgurl)) {
                        $isfile = false;
                        $src = '../includes/img/fotoClienteDefault.png';
                    } else {
                        $isfile = true;
                        $imginfo   = getimagesize($imgurl);
                        $mimetype  = $imginfo['mime'];
                        $imageData = base64_encode(file_get_contents($imgurl));
                        $src = 'data:' . $mimetype . ';base64,' . $imageData;
                    }
                    $i++;
                    $bandera = true;
                }
            }
        ?>
            <!--Aho_0_PrmtrzcAhrrs Inicio de Ahorro Sección 0 Parametros cuentas ahorro-->
            <input type="text" id="file" value="clientes_001" style="display: none;">
            <input type="text" id="condi" value="create_cliente_natural" style="display: none;">
            <div class="text" style="text-align:center"><?= ($bandera) ? 'ELIMINACION ' : 'INGRESO '; ?> DE CLIENTE</div>
            <div class="card">
                <div class="card-header"><?= ($bandera) ? 'Eliminacion ' : 'Ingreso '; ?> de cliente</div>
                <div class="card-body" style="padding-bottom: 0px !important;">
                    <!-- seleccion de cliente y su credito-->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Verifica los datos a eliminar</b></div>
                            </div>
                        </div>
                        <?php if ($bandera) { ?>
                            <div class="row">
                                <div class="col">
                                    <div class="text-center"><span class="text-primary">Codigo cliente:
                                            <b><?php echo $datos[0]['idcod_cliente']; ?></b></span></div>
                                </div>
                            </div>
                            <div class="row justify-content-center">
                                <div class="col-6 col-sm-6 col-md-2 mt-2 d-flex align-items-center">
                                    <div class="mx-auto">
                                        <img id="vistaPrevia" class="img-thumbnail" src="<?php if ($bandera) {
                                                                                                echo $src;
                                                                                            } else {
                                                                                                echo $src;
                                                                                            } ?>" style="max-width:120px; max-height:130px;">
                                    </div>
                                </div>
                            </div>
                            <?php if ($isfile) { ?>
                                <div class="row">
                                    <div class="col mb-2 mt-2">
                                        <div class="input-group">
                                            <button class="btn btn-outline-danger" type="button" id="inputGroupFileAddon04" onclick="eliminar_plus(['<?= $imgurl; ?>','<?= $datos[0]['idcod_cliente']; ?>'], `<?= $datos[0]['idcod_cliente']; ?>`, `delete_image_cliente`, `¿Está seguro de eliminar la foto?`)">
                                                <i class="fa-solid fa-trash"></i>Borrar Foto
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php }; ?>
                            <div class="row">
                                <div class="col mb-2 mt-2">
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="fileuploadcli" aria-describedby="inputGroupFileAddon04" aria-label="Upload" onchange="LeerImagen(this)">
                                        <button class="btn btn-outline-primary" type="button" id="inputGroupFileAddon04" onclick="CargarImagen('fileuploadcli','<?= $datos[0]['idcod_cliente']; ?>')" disabled>
                                            <i class="fa-solid fa-sd-card me-2"></i>Guardar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php }; ?>

                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="nom1" placeholder="Primer nombre" <?php if ($bandera) {
                                                                                                                        echo 'value="' . $datos[0]['primer_name'] . '"disabled';
                                                                                                                    } ?> onkeyup="concatenarValores(['nom1','nom2','nom3'],['ape1','ape2','ape3'],1,'#nomcorto'); concatenarValores(['ape1','ape2','ape3'],['nom1','nom2','nom3'],2,'#nomcompleto')">
                                    <label for="cliente">Primer nombre</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="nom2" placeholder="Segundo nombre" <?php if ($bandera) {
                                                                                                                        echo 'value="' . $datos[0]['segundo_name'] . '"disabled';
                                                                                                                    } ?> onkeyup="concatenarValores(['nom1','nom2','nom3'],['ape1','ape2','ape3'],1,'#nomcorto'); concatenarValores(['ape1','ape2','ape3'],['nom1','nom2','nom3'],2,'#nomcompleto')">
                                    <label for="cliente">Segundo nombre</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="nom3" placeholder="Tercer nombre" <?php
                                                                                                                    // Verificar si existe la variable $bandera antes de utilizarla para evitar errores
                                                                                                                    if ($bandera) {
                                                                                                                        echo 'value="' . $datos[0]['tercer_name'] . '" disabled';
                                                                                                                    }
                                                                                                                    ?> onkeyup="concatenarValores(['nom1','nom2','nom3'],['ape1','ape2','ape3'],1,'#nomcorto'); concatenarValores(['ape1','ape2','ape3'],['nom1','nom2','nom3'],2,'#nomcompleto')">
                                    <label for="nom3">Tercer nombre</label>
                                </div>
                            </div>

                        </div>

                        <!-- apellidos -->
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="ape1" placeholder="Primer apellido" <?php if ($bandera) {
                                                                                                                        echo 'value="' . $datos[0]['primer_last'] . '"disabled';
                                                                                                                    } ?> onkeyup="concatenarValores(['nom1','nom2','nom3'],['ape1','ape2','ape3'],1,'#nomcorto'); concatenarValores(['ape1','ape2','ape3'],['nom1','nom2','nom3'],2,'#nomcompleto')">
                                    <label for="cliente">Primer apellido</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="ape2" placeholder="Segundo apellido" <?php if ($bandera) {
                                                                                                                            echo 'value="' . $datos[0]['segundo_last'] . '"disabled';
                                                                                                                        } ?> onkeyup="concatenarValores(['nom1','nom2','nom3'],['ape1','ape2','ape3'],1,'#nomcorto'); concatenarValores(['ape1','ape2','ape3'],['nom1','nom2','nom3'],2,'#nomcompleto')">
                                    <label for="cliente">Segundo apellido</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="ape3" placeholder="Tercer apellido" <?php if ($bandera) {
                                                                                                                        echo 'value="' . $datos[0]['casada_last'] . '"disabled';
                                                                                                                    } ?> onkeyup="concatenarValores(['nom1','nom2','nom3'],['ape1','ape2','ape3'],1,'#nomcorto'); concatenarValores(['ape1','ape2','ape3'],['nom1','nom2','nom3'],2,'#nomcompleto')">
                                    <label for="cliente">Apellido de casada</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="nomcorto" placeholder="Nombre corto" readonly disabled <?php if ($bandera) {
                                                                                                                                            echo 'value="' . $datos[0]['short_name'] . '"disabled';
                                                                                                                                        } ?>>
                                    <label for="nomcorto">Nombre corto</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="nomcompleto" placeholder="Nombre completo" readonly disabled <?php if ($bandera) {
                                                                                                                                                    echo 'value="' . $datos[0]['compl_name'] . '"disabled';
                                                                                                                                                } ?>>
                                    <label for="nomcompleto">Nombre completo</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="genero" disabled>
                                        <option value="0" selected>Seleccione un género</option>
                                        <option value="M">Hombre</option>
                                        <option value="F">Mujer</option>
                                        <option value="X">No definido</option>
                                    </select>
                                    <label for="genero">Género</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="estcivil" disabled>
                                        <option value="0" selected>Seleccione un estado civil</option>
                                        <option value="SOLTERO">Soltero</option>
                                        <option value="CASADO">Casado(a)</option>
                                    </select>
                                    <label for="estcivil">Estado civil</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="profesion" placeholder="Profesión" <?php if ($bandera) {
                                                                                                                        echo 'value="' . $datos[0]['profesion'] . '" disabled';
                                                                                                                    } ?>>
                                    <label for="profesion">Profesión</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-3">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="email" class="form-control" id="email" placeholder="Email" <?php if ($bandera) {
                                                                                                                echo 'value="' . $datos[0]['email'] . '" disabled';
                                                                                                            } ?>>
                                    <label for="email">Email</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-3">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="conyugue" placeholder="Conyugue" <?php if ($bandera) {
                                                                                                                        echo 'value="' . $datos[0]['Conyuge'] . '" disabled';
                                                                                                                    } ?>>
                                    <label for="conyugue">Cónyuge</label>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- DOCUMENTO DE INDENTIFICACION -->
                    <div class="container contenedort" style="max-width: 100% !important;">
                        <div class="row">
                            <div class="col">
                                <div class="text-center mb-2"><b>Documento de identificación</b></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="docextend" onchange="ocultar_nit(this.value)" disabled>
                                        <option value="Guatemala" selected>Guatemala</option>
                                        <option value="Extranjero">Extranjero</option>
                                    </select>
                                    <label for="docextend">Documento extendido en:</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="tipodoc" disabled>
                                        <option value="DPI" selected>DPI</option>
                                        <option value="PASAPORTE">Pasaporte</option>
                                    </select>
                                    <label for="tipodoc">Tipo de documento</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="numberdoc" placeholder="Número de documento" <?php if ($bandera) {
                                                                                                                                    echo 'value="' . $datos[0]['no_identifica'] . '" disabled';
                                                                                                                                } ?>>
                                    <label for="numberdoc">Número de documento</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="tipoidentri" disabled>
                                        <option value="NIT" selected>NIT</option>
                                        <option value="CUI">CUI</option>
                                    </select>
                                    <label for="tipoidentri">Tipo de indent. tributaria</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="text" class="form-control" id="numbernit" placeholder="Número tributario" <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['no_tributaria'] . '" disabled';
                                                                                                                            } ?>>
                                    <label for="numbernit">Número tributario</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <input type="number" class="form-control" id="afiliggs" placeholder="Afiliación IGGS" <?php if ($bandera) {
                                                                                                                                echo 'value="' . $datos[0]['no_igss'] . '" disabled';
                                                                                                                            } ?>>
                                    <label for="afiliggs">Afiliación IGGS</label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-floating mb-2 mt-2">
                                    <select class="form-select" id="nacionalidad" disabled>
                                        <option value="0">Seleccione una nacionalidad</option>
                                        <?php
                                        $selected = "";
                                        $pais = mysqli_query($general, "SELECT * FROM `tb_pais`");
                                        while ($fila = mysqli_fetch_array($pais, MYSQLI_ASSOC)) {
                                            if ($bandera) {
                                                ($datos[0]['nacionalidad'] == $fila['Abreviatura']) ? $selected = "selected" : $selected = "";
                                            }
                                            $nombre = utf8_decode($fila["Pais"]);
                                            $codpais = $fila["Abreviatura"];
                                            echo '<option value="' . $codpais . '"' . $selected . '>' . $nombre . '</option> ';
                                        } ?>
                                    </select>
                                    <label for="nacionalidad">Nacionalidad</label>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="container" style="max-width: 100% !important;">
                    <div class="row justify-items-md-center">
                        <div class="col align-items-center mb-3 ms-2" id="modal_footer">
                            <?php if (!$bandera) { ?>
                                <button class="btn btn-outline-success mt-2" onclick="obtiene_plus([`nom1`,`nom2`,`nom3`,`ape1`,`ape2`,`ape3`,`profesion`,`email`,`conyugue`,`fechanacimiento`,`edad`,`dirnac`,`numberdoc`,`numbernit`,`afiliggs`,`reside`,`dirviv`,`refviv`,`representante`,`refn1`,`ref1`,`refn2`,`ref2`,`refn3`,`ref3`,`tel1`,`tel2`],[`genero`,`estcivil`,`origen`,`paisnac`,`depnac`,`muninac`,`docextend`,`tipodoc`,`tipoidentri`,`nacionalidad`,`condicion`,`depdom`,`munidom`,`actpropio`,`actcalidad`,`otranacionalidad`,`etnia`,`religion`,`educacion`,`relinsti`],[`leer`,`escribir`,`firma`,`pep`,`cpe`],`create_cliente_natural`,`0`,['<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>'])"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar cliente</button>
                                <button type="button" class="btn btn-outline-danger mt-2" onclick="printdiv2_plus('#cuadro','0')">
                                    <i class="fa-solid fa-ban"></i> Cancelar
                                </button>
                            <?php } ?>
                            <?php if ($bandera) { ?>
                                <!-- eliminar boton -->

                                <button class="btn btn-outline-danger mt-2" onclick="eliminar_plus(['<?= $codusu; ?>','<?= $id_agencia; ?>','<?= $codagencia; ?>','<?= $xtra; ?>'], `0`, `delete_cliente_natural`, `¿Está seguro de eliminar el cliente <?= $xtra; ?>?`)"><i class="fa-solid fa-floppy-disk me-2"></i>Eliminar cliente</button>

                                <button type="button" class="btn btn-outline-danger mt-2" onclick="printdiv('Delete_Cliente', '#cuadro', 'clientes_001', '0')">
                                    <i class="fa-solid fa-ban"></i> Cancelar
                                </button>
                            <?php } ?>
                            <!-- boton para solicitar credito -->
                            <button type="button" class="btn btn-outline-warning mt-2" onclick="salir()">
                                <i class="fa-solid fa-circle-xmark"></i> Salir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <script>
                //SELECCIONAR LOS CHECKBOXS DESPUES DE CARGAR EL DOM
                $(document).ready(function() {
                    ocultar_actuacion_propia(1);
                    <?php if ($bandera) { ?>
                        // concatenarValores(['nom1', 'nom2', 'nom3'], ['ape1', 'ape2', 'ape3'], 1, '#nomcorto');
                        // concatenarValores(['ape1', 'ape2', 'ape3'], ['nom1', 'nom2', 'nom3'], 2, '#nomcompleto');
                        seleccionarValueSelect('#genero', '<?= $datos[0]['genero']; ?>');
                        seleccionarValueSelect('#estcivil', '<?= $datos[0]['estado_civil']; ?>');
                        seleccionarValueSelect('#origen', '<?= $datos[0]['origen']; ?>');
                        calcularEdad_plus('<?= $datos[0]['date_birth'] ?>', '#edad');
                        seleccionarValueSelect('#docextend', '<?= $datos[0]['pais_extiende']; ?>');
                        seleccionarValueSelect('#tipodoc', '<?= $datos[0]['type_doc']; ?>');
                        ocultar_nit('<?= $datos[0]['pais_extiende']; ?>')
                        ejecutarDespuesDeBuscarMunicipios('buscar_municipios', '#muninac', '<?= $datos[0]['depa_nacio']; ?>',
                            '<?= $datos[0]['muni_nacio']; ?>');
                        ejecutarDespuesDeBuscarMunicipios('buscar_municipios', '#munidom', '<?= $datos[0]['depa_reside']; ?>',
                            '<?= $datos[0]['muni_reside']; ?>');
                        seleccionarValueSelect('#tipoidentri', '<?= $datos[0]['identi_tribu']; ?>');
                        seleccionarValueSelect('#actpropio', '<?= $datos[0]['actu_Propio']; ?>');
                        ocultar_actuacion_propia('<?= $datos[0]['repre_calidad']; ?>');
                        seleccionarValueSelect('#actcalidad', '<?= $datos[0]['repre_calidad']; ?>');
                        seleccionarValueSelect('#educacion', '<?= $datos[0]['educa']; ?>');
                        seleccionarValueSelect('#relinsti', '<?= $datos[0]['Rel_insti']; ?>');
                        seleccionarValueRadio('leer', '<?= $datos[0]['leer']; ?>');
                        seleccionarValueRadio('escribir', '<?= $datos[0]['escribir']; ?>');
                        seleccionarValueRadio('firma', '<?= $datos[0]['firma']; ?>');
                        seleccionarValueRadio('pep', '<?= $datos[0]['PEP']; ?>');
                        seleccionarValueRadio('cpe', '<?= $datos[0]['CPE']; ?>');
                    <?php }; ?>
                });
            </script>
        <?php
        }
        break;
    case "huella_cli":
        ?>


        <!-- ini card-->
        <div class="card  container mt-3">
            <div class="card-header font-weight-bold">
                <h3>Registro de huellas digitales</h3>
            </div>

            <div class="row">
                <div class="card col-lg-6 col-md-11" style="max-width: 100% !important;">
                    <!-- ini fila -->
                    <div class="row d-flex justify-content-center">
                        <div class="col-lg-12 col-md-12 mt-2">
                            <label class="form-label fw-bold">Cliente seleccionado</label><br>
                            <div class="input-group mb-3">
                                <button class="btn btn-warning" type="button" id="button-addon1" onclick="abrir_modal_cualquiera('#mdl_consulta_cre')"><i class="fa-solid fa-users"></i> Buscar</button>
                                <input id="codCre" type="text" class="form-control" placeholder="Cliente" aria-label="Example text with button addon" aria-describedby="button-addon1" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- BUTON NUEVO REGISTRO -->
                    <div class="row">
                        <div class="col-lg-12 col-md-12 mt-2">
                            <button type="button" class="btn btn-success"><i class="fa-solid fa-folder-plus"></i> nuevo registro</button>
                        </div>
                    </div>
                    <!-- SELECT MANO Y DEDO -->
                    <div class="row d-flex justify-content-center">
                        <div class="col-lg-6 col-md-12 mt-2">
                            <label class="form-label fw-bold">Seleccione una mano</label>
                            <select id="tipocred" class="form-select" aria-label="Default select example" onchange="controlSelect('tipocred')">
                                <option value="izquierda" selected="selectd">Mano Izquierda</option>
                                <option value="derecha">Mano Derecha</option>
                            </select>
                        </div>

                        <div class="col-lg-6 col-md-12 mt-2">
                            <label class="form-label fw-bold">Seleccionar un dedo</label>
                            <select id="tipocred" class="form-select" aria-label="Default select example" onchange="controlSelect('tipocred')">
                                <option value="pulgar" selected="selectd">Pulgar</option>
                                <option value="ìndice">Ìndice</option>
                                <option value="medio">Medio</option>
                                <option value="anular">Anular</option>
                                <option value="meñique">Meñique</option>
                            </select>
                        </div>
                    </div>

                    <!-- AREA DONDE VA APARECER LA HUELLA DIGITAL -->
                    <div class="row mt-3">
                        <div class="col d-flex justify-content-center">
                            <div class="card" style="width:175x">
                                <img class="card-img-top" src="https://c0.klipartz.com/pngpicture/1000/646/gratis-png-logo-huella-digital-computadora-iconos-digito-diseno.png" alt="Card image" height="200">
                                <div class="card-body d-flex justify-content-center">
                                    <a href="#" class="btn btn-primary" onclick="controlHuella('activarSensor')"><i class="fa-solid fa-fingerprint"></i> capturar huella</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AREA DE BOTONES -->
                    <div class="row mt-3">
                        <div class="col md-3 d-flex justify-content-center">
                            <button type="button" class="btn btn-outline-danger"><i class="fa-solid fa-ghost"></i> Cancelar</button>
                            <button type="button" class="btn btn-outline-success"><i class="fa-regular fa-floppy-disk"></i> Guardar</button>
                            <button type="button" class="btn btn-outline-primary"><i class="fa-solid fa-file-pen"></i> Actualizar</button>
                        </div>
                    </div>


                    <!-- fin fila -->
                </div>

                <div class="card border-primary col-lg-6 col-md-11" style="max-width: 100% !important;">
                    <!-- ini fila -->
                    <div class="row col-lg-12">
                        <select name="" id=""></select>
                    </div>
                    <!-- fin fila -->
                </div>
            </div>

        </div>

        <!-- fin card-->
<?php
        break;
}

?>