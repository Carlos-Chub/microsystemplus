<?php
session_start();
// Verificar la cookie de la última notificación
$show_notification = false;
if (!isset($_COOKIE['last_notificationtwo'])) {
    $show_notification = true;
    setcookie('last_notificationtwo', time(), time() + 86400, "/"); // 86400 segundos = 1 día
} else {
    $last_notificationtwo = $_COOKIE['last_notificationtwo'];
    if (time() - $last_notificationtwo > 86400) {
        $show_notification = true;
        setcookie('last_notificationtwo', time(), time() + 86400, "/");
    }
}


include_once '../../includes/Config/config.php';
if (!isset($_SESSION['usu'])) {
    header('location: ' . BASE_URL);
} else {
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modulo Creditos</title>
    <link rel="shortcut icon" type="image/x-icon" href="../../includes/img/favmicro.ico">
    <link rel="stylesheet" href="../../includes/css/style.css">
    <link rel="stylesheet" href="../../includes/css/styleCard.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <?php require_once '../../includes/incl.php'; ?>

</head>

<body class="<?= ($_SESSION['background'] == '1') ? 'dark' : ''; ?>">
    <?php
        require '../../src/menu/cre_menu.php';
        require '../infoEnti/infoEnti.php';

        $infoEnti = infoEntidad($_SESSION['id'], $conexion);
        ?>

    <!-- ----------- SECTION ------------------------- -->
    <section class="home">
        <div class="container" style="max-width: none !important;">
            <div class="row">
                <div class="col d-flex justify-content-start">
                    <div class="text">MODULO CREDITOS</div>
                </div>
                <div class="col d-flex justify-content-end">
                    <div class="text"><?= $infoEnti['nomAge'] ?></div>
                </div>
            </div>
           <!-- VERIFICACION DE PAGO -->
           <?php
if ($show_notification) {
    $sql = "SELECT cope.nomb_cor AS nomAge, estado_pag AS estado, fecha_pago 
            FROM tb_agencia AS agen	
            INNER JOIN tb_usuario AS usu ON agen.id_agencia = usu.id_agencia
            INNER JOIN clhpzzvb_bd_general_coopera.info_coperativa AS cope ON agen.id_institucion = cope.id_cop
            WHERE usu.id_usu =" . $_SESSION["id"];
    $result = mysqli_query($conexion, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $estado = $row['estado'];
        $fecha_pago = $row['fecha_pago'];
        $hoy = strtotime(date("Y-m-d"));
        // Calcula las fechas
        $fecha_pago_menos = date('Y-m-d', strtotime($fecha_pago . ' - 5 days'));
        $fecha_pago_mas = date('Y-m-d', strtotime($fecha_pago . ' + 5 days'));
        $fecha_pago_un_dia_antes = date('Y-m-d', strtotime($fecha_pago . ' - 1 day'));
        $fecha_pago_3 = date('Y-m-d', strtotime($fecha_pago . ' + 3 days'));
        $ultimo_dia_pago = date('Y-m-d', strtotime($fecha_pago_3 . ' + 1 day'));
        $fecha_pago_mas5 = date('Y-m-d', strtotime($fecha_pago . ' + 4 days'));
        // Verifica si hoy está dentro del rango  
        $fechanotify_mas5 = strtotime($fecha_pago_mas5);
        $fechanotify_menos = strtotime($fecha_pago_menos);
        $fechanotify_un_dia_antes = strtotime($fecha_pago_un_dia_antes);
        $fecha_pago_3_days = strtotime($fecha_pago_3);
        $case = '';
            // Determina el caso basado en las fechas
            }if ($fechanotify_menos <= $hoy && $hoy <= strtotime($fecha_pago)) {
            $case = 'dentro_rango_menos';
            } if ($hoy >= strtotime($fecha_pago . ' + 3 days') && $hoy <= strtotime($fecha_pago . ' + 4 days')) {
            $case = 'dentro_rango_mas';
            } 
        switch ($case) {
            case 'dentro_rango_menos':
                ?>
            <center>
                <div class="container">
                    <div class="cardalert">
                        <div class="header">
                            <div class="image_advert">
                                <svg aria-hidden="true" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                                    fill="none">
                                    <path
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                                        stroke-linejoin="round" stroke-linecap="round"></path>
                                </svg>
                            </div>
                            <div class="content">
                                <span class="title">AVISO</span>
                                <p class="message">Este es un recordatorio cordial de que tu pago está programado para
                                    efectuarse el día
                                    <?php echo date('d F Y', strtotime($fecha_pago)); ?></p>
                            </div>
                            <div class="actions">
                                <button class="desactivate btn btn-success" type="button" data-toggle="modal"
                                    data-target="#exampleModalCenter">
                                    <i class="fas fa-exclamation-triangle"></i> Ver detalles
                                </button>
                                <!-- Modal -->
                                <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog"
                                    aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLongTitle">Advertencia de pago
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                "Si necesitas asistencia adicional o tienes alguna pregunta, no dudes en
                                                contactarnos.
                                                Saludos cordiales,
                                                [SOTECPRO]"
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-warning"
                                                    data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </center>
            <?php
                break;
            case 'dentro_rango_mas':
                if ($hoy == strtotime($fecha_pago_mas5)) {
                   ?>
            <center>
                <div class="container">
                    <div class="cardalert">
                        <div class="header">
                            <div class="image">
                                <svg aria-hidden="true" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                                    fill="none">
                                    <path
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                                        stroke-linejoin="round" stroke-linecap="round"></path>
                                </svg>
                            </div>
                            <div class="content">
                                <span class="title"> Ultimo aviso
                                </span>
                                <p class="message">Mañana es el último día para renovar tu suscripción. Si no lo haces,
                                    tu cuenta se bloqueará automáticamente. </p>
                            </div>
                            <div class="actions">
                                <button class="desactivate btn btn-danger" type="button" data-toggle="modal"
                                    data-target="#exampleModalCenter">
                                    <i class="fas fa-exclamation-triangle"></i> Ver detalles
                                </button>
                                <!-- Modal -->
                                <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog"
                                    aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLongTitle">Alerta por falta de
                                                    pago</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                "Mañana vence tu suscripción. Si no la renuevas, tu cuenta se bloqueará.
                                                Recuerda que la restauración del sistema tomará aproximadamente 1 hora ,
                                                (dias habiles).
                                                Renueva ahora para evitar interrupciones en el servicio.
                                                Para cualquier pregunta o ayuda, estamos aquí para ti.
                                                [Sotecpro]"
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-warning"
                                                    data-dismiss="modal">Close</button>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </center>
            <?php
                }else {
                    ?>
            <center>
                <div class="container">
                    <div class="cardalert">
                        <div class="header">
                            <div class="image">
                                <svg aria-hidden="true" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                                    fill="none">
                                    <path
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                                        stroke-linejoin="round" stroke-linecap="round"></path>
                                </svg>
                            </div>
                            <div class="content">
                                <span class="title"> Estimado Usuario
                                </span>
                                <p class="message">parece que tienes una Factura pendiente, con fecha de pago del
                                    <?php echo date('d F Y', strtotime($fecha_pago)); ?> .El sistema se bloqueará el
                                    <?php echo date('d F Y', strtotime($fecha_pago_mas)); ?>. Favor de enviar la boleta
                                    de pago</p>
                            </div>
                            <div class="actions">
                                <button class="desactivate btn btn-danger" type="button" data-toggle="modal"
                                    data-target="#exampleModalCenter">
                                    <i class="fas fa-coins"></i>
                                </button>
                                <!-- Modal -->
                                <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog"
                                    aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLongTitle">Alerta por falta de
                                                    pago</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                "Si considera que esta notificación es incorrecta, le recomendamos que
                                                se comunique con nuestro equipo de soporte técnico. Alternativamente, le
                                                instamos a tomar las medidas necesarias, ya que para la fecha
                                                <?php echo date('d F Y', strtotime($fecha_pago_mas)); ?>, el sistema se
                                                encontrará bloqueado."
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-warning"
                                                    data-dismiss="modal">Close</button>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </center>
            <?php
    
                }

               
                break;

            default:
                // NO PASA NADA 
                break;
        }
    }
}
?>
            <div id="cuadro">
                <div class="d-flex flex-column h-100">
                    <div class="flex-grow-1">
                        <div class="row align-items-center"
                            style="max-width: none !important; height: calc(75vh) !important;">
                            <div class="row d-flex justify-content-center">
                                <div class="col-8">
                                    <div class="card">
                                        <div class="card-header text-center"> Bienvenido al módulo de creditos. </div>
                                        <div class="card-body d-flex justify-content-center">
                                            <div class="col-auto">

                                                <img src="<?= '../../' . $infoEnti['imagenEnti'] ?>" alt="" srcset=""
                                                    width="500">
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
                </div>
            </div>
        </div>
    </section>
    <!-- ESPACION DE ALERTA DE APERTURAS Y CIERRES DE CAJA -->
    <?php $verificacion_ape_cr = verificar_apertura_cierre($_SESSION['id'], $conexion); print_r($verificacion_ape_cr);
        print_r($verificacion_ape_cr); ?>

    <div aria-live="polite" aria-atomic="true" class="position-relative">
        <div class="toast-container top-0 end-0 pe-3" style="padding-top: 4rem !important;">
            <!-- Then put toasts within -->
            <div class="toast <?= ($verificacion_ape_cr[0] < 6) ? 'fade show' : ''; ?>" role="alert"
                aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <span class="btn btn-<?= ($verificacion_ape_cr[0] > 0) ? 'warning' : 'danger'; ?> btn-sm me-2"><i
                            class="fa-solid fa-triangle-exclamation"></i></span>
                    <strong class="me-auto">
                        <?= ($verificacion_ape_cr[0] > 0) ? '¡Advertencia!' : '¡Error!'; ?>
                    </strong>
                    <small class="text-muted">
                        En este momento
                    </small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerca"></button>
                </div>
                <div class="toast-body">
                    <span class="text-primary"><?= $verificacion_ape_cr[1]; ?></span>
                </div>
            </div>
        </div>
    </div>
    <!-- FIN DE ALERTA -->
    <div class="loader-container loading--show">
        <div class="loader"></div>
        <div class="loaderimg"></div>
        <div class="loader2"></div>
    </div>


    <script src="../../includes/js/script.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- <script type="text/javascript" src="../../includes/js/all.min.js"></script> -->
</body>

</html>
<?php

?>