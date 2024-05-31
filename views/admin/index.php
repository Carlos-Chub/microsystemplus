<?php
session_start();
include '../../includes/Config/config.php';
if (!isset($_SESSION['usu'])) {
    header('location: ' . BASE_URL);
} else {
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta http-equiv="Permissions-Policy" content="interest-cohort=()">
        <meta charset=UTF-8>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!--borrar estas 3 lineas al terminar desarrollo-->
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
        <title>Administrador</title>
        <link rel="shortcut icon" type="image/x-icon" href="../../includes/img/favmicro.ico">
        <link rel="stylesheet" href="../../includes/css/style.css">
        <?php
        require_once '../../includes/incl.php';
        ?>
    </head>

    <body class="<?= ($_SESSION['background'] == '1') ? 'dark' : ''; ?>">
        <?php
        require '../../src/menu/menu_admin.php';
        require '../infoEnti/infoEnti.php';

        $infoEnti = infoEntidad($_SESSION['id'], $conexion);
        ?>

        <section class="home">
            <div class="container" style="max-width: none !important;">
                <div class="row">
                    <div class="col d-flex justify-content-start">
                        <div class="text">SOTECPRO ADMIN</div>
                    </div>
                    <div class="col d-flex justify-content-end">
                        <div class="text"><?= $infoEnti['nomAge'] ?></div>
                    </div>
                </div>
                <div id="cuadro">
                    <div class="d-flex flex-column h-100">
                        <div class="flex-grow-1">
                            <div class="row align-items-center" style="max-width: none !important; height: calc(75vh) !important;">
                                <div class="row d-flex justify-content-center">
                                    <div class="col-auto">
                                        <img src="<?= '../../' . $infoEnti['imagenEnti'] ?>" alt="" srcset="" width="500">
                                        <p class="displayed text-success text-center" style='font-family: "Garamond", serif;
                      font-weight: bold;
                      font-size: x-large;'> Sistema orientado para microfinanzas </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </section>
        <!-- div contenedor para el efecto de loader -->
        <div class="loader-container loading--hide">
            <div class="loader"></div>
            <div class="loaderimg"></div>
            <div class="loader2"></div>
        </div>

        <script src="../../includes/js/script.js"></script>
        <!-- <script src="../../includes/js/all.min.js"></script> -->
    </body>

    </html>
<?php
}
?>