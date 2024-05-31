<?php
session_start();
include '../../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
date_default_timezone_set('America/Guatemala');
$condi = $_POST["condi"];

switch ($condi) {
    case 'admin_agencia':
        $codusu = $_SESSION['id'];
        $id = $_POST["xtra"];
?>
        <!-- CONFIGURACION PARA RECARGAR LA PAGINA -->
        <input type="text" id="file" value="usuario_03" style="display: none;">
        <input type="text" id="condi" value="admin_agencia" style="display: none;">

        <!-- ini -->
        <div class="card mb-3" style="width: 100%;">
            <div class="card-header">
                Parametrización de agencias
            </div>
            <div class="card-body">

                <!-- IMPRESION DE LA TABLA -->
                <div id="tb_parametrizacion_agencia"></div>
            </div>
        </div>

        <!-- ini js -->
        <script>
            $(document).ready(function() {
                inyecCod('#tb_parametrizacion_agencia', 'tbParametrizacionAgencia');
            });
            var datoID = 0;

            function capID(idEle) {
                datoID = $("#" + idEle).text();
            }

            function datos(datos) {
                datos.push(datoID);
                obtiene([], [], [], 'parametrizaAgencia', '', datos);
            }
            function cerrarModal(idEle){
                $("#"+idEle).modal("hide");
            }
        </script>
<?php
        include_once "../../../../src/cris_modales/mdls_nomenclatura1.php";
        break; //FIN DE CASE
}
?>