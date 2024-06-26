<?php
session_start();
include '../../includes/Config/database.php';
$condi = $_POST["condi"];

$database = new Database($db_host, $db_name, $db_user, $db_password, $db_name_general);
$database->openConnection();
$infoEnti = $database->joinQuery(
    ['tb_agencia'], // Tablas a unir
    ['cope.nomb_cor AS nomAge', 'cope.log_img AS imagenEnti'], // Columnas a seleccionar
    [
        ['type' => 'INNER', 'table' => 'tb_usuario', 'condition' => 'tb_usuario.id_agencia = tb_agencia.id_agencia'],
        ['type' => 'INNER', 'table' => 'clhpzzvb_bd_general_coopera.info_coperativa AS cope', 'condition' => 'cope.id_cop = tb_agencia.id_institucion']
    ], // Joins
    'tb_usuario.id_usu = ?', // Condición
    [$_SESSION['id']] // Parámetros
);
$database->closeConnection();
switch ($condi) {
    case 'pearls':
        // try {
        //     // $database->openConnection();
        //     // $rows = $database->selectAll('ctb_fuente_fondos');

        // } catch (Exception $e) {
        //     echo "Error: " . $e->getMessage();
        // } finally {
        //     $database->closeConnection();
        // }

?>
        <!-- <div class="card contenedort"> -->
        <div>
            <!-- <div class="card-header panelcolor"> Bienvenido al módulo de indicadores PEARLS. </div> -->
            <!-- <div class="container" style="max-width: none !important;"> -->
            <div style="max-width: none !important;">
                <div class="btn-group" id="nav_group" role="group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        PROTECCION
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" style="cursor: pointer;" onclick="printdiv('p1', `#cuadro`, `app/pearls/views/proteccion`, `0`)">P1</a></li>
                        <li><a class="dropdown-item" style="cursor: pointer;" onclick="printdiv('p2', `#cuadro`, `app/pearls/views/proteccion`, `0`)">P2</a></li>
                        <li><a class="dropdown-item" style="cursor: pointer;" onclick="printdiv('p3', `#cuadro`, `app/pearls/views/proteccion`, `0`)">P3</a></li>
                        <li><a class="dropdown-item" style="cursor: pointer;" onclick="printdiv('p4', `#cuadro`, `app/pearls/views/proteccion`, `0`)">P4</a></li>
                    </ul>
                </div>
                <div class="btn-group" id="nav_group" role="group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        ESTRUCTURA FE
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" style="cursor: pointer;" onclick="printdiv('p1', `#cuadro`, `app/pearls/views/proteccion`, `0`)">P1</a></li>
                        <li><a class="dropdown-item" style="cursor: pointer;" onclick="printdiv('p2', `#cuadro`, `app/pearls/views/proteccion`, `0`)">P2</a></li>
                        <li><a class="dropdown-item" style="cursor: pointer;" onclick="printdiv('p3', `#cuadro`, `app/pearls/views/proteccion`, `0`)">P3</a></li>
                        <li><a class="dropdown-item" style="cursor: pointer;" onclick="printdiv('p4', `#cuadro`, `app/pearls/views/proteccion`, `0`)">P4</a></li>
                    </ul>
                </div>
                <button type="button" class="btn btn-warning" onclick="window.location.reload();">RELOAD <i class="fa-solid fa-arrow-rotate-right"></i> </button>
                <div id="cuadro">
                    <div class="d-flex flex-column h-100">
                        <div class="flex-grow-1">
                            <div class="row align-items-center" style="max-width: none !important; height: calc(75vh) !important;">
                                <div class="row d-flex justify-content-center">
                                    <div class="col-auto">
                                        <img src="<?= '../../' . $infoEnti[0]['imagenEnti'] ?>" alt="" srcset="" width="500">
                                        <p class="displayed text-success text-center" style='font-family: "Garamond", serif;font-weight: bold;font-size: x-large;'>
                                            Sistema orientado para microfinanzas
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
        break;
    case 'camels':
    ?>
        <style>
            /* body {
                padding: 0;
                margin: 0;
                background: #596778;
                color: #EEEEEE;
                text-align: center;
                font-family: "Lato", sans-serif;
            } */

            /* @media screen and (max-width: 700px) {
                body {
                    padding: 170px 0 0 0;
                    width: 100%
                }
            } */

            /* a {
                color: inherit;
            } */

            .menu-item,
            .menu-open-button {
                background: #EEEEEE;
                border-radius: 100%;
                width: 80px;
                height: 80px;
                margin-left: -40px;
                position: absolute;
                color: #FFFFFF;
                text-align: center;
                line-height: 80px;
                -webkit-transform: translate3d(0, 0, 0);
                transform: translate3d(0, 0, 0);
                -webkit-transition: -webkit-transform ease-out 200ms;
                transition: -webkit-transform ease-out 200ms;
                transition: transform ease-out 200ms;
                transition: transform ease-out 200ms, -webkit-transform ease-out 200ms;
            }

            .menu-item {
                width: 150px;
                height: 150px;
            }

            .menu-open {
                display: none;
            }

            .lines {
                width: 25px;
                height: 3px;
                background: #596778;
                display: block;
                position: absolute;
                top: 50%;
                left: 50%;
                margin-left: -12.5px;
                margin-top: -1.5px;
                -webkit-transition: -webkit-transform 200ms;
                transition: -webkit-transform 200ms;
                transition: transform 200ms;
                transition: transform 200ms, -webkit-transform 200ms;
            }

            .line-1 {
                -webkit-transform: translate3d(0, -8px, 0);
                transform: translate3d(0, -8px, 0);
            }

            .line-2 {
                -webkit-transform: translate3d(0, 0, 0);
                transform: translate3d(0, 0, 0);
            }

            .line-3 {
                -webkit-transform: translate3d(0, 8px, 0);
                transform: translate3d(0, 8px, 0);
            }

            .menu-open:checked+.menu-open-button .line-1 {
                -webkit-transform: translate3d(0, 0, 0) rotate(45deg);
                transform: translate3d(0, 0, 0) rotate(45deg);
            }

            .menu-open:checked+.menu-open-button .line-2 {
                -webkit-transform: translate3d(0, 0, 0) scale(0.1, 1);
                transform: translate3d(0, 0, 0) scale(0.1, 1);
            }

            .menu-open:checked+.menu-open-button .line-3 {
                -webkit-transform: translate3d(0, 0, 0) rotate(-45deg);
                transform: translate3d(0, 0, 0) rotate(-45deg);
            }

            .menuother {
                margin: auto;
                position: absolute;
                top: 0;
                bottom: 0;
                left: 0;
                right: 0;
                width: 80px;
                height: 80px;
                text-align: center;
                box-sizing: border-box;
                font-size: 26px;
            }


            /* .menu-item {
   transition: all 0.1s ease 0s;
} */

            .menu-item:hover {
                background: #EEEEEE;
                color: #3290B1;
            }

            /* .menu-item:nth-child(3) {
                -webkit-transition-duration: 180ms;
                transition-duration: 180ms;
            }

            .menu-item:nth-child(4) {
                -webkit-transition-duration: 180ms;
                transition-duration: 180ms;
            } */

            .menu-item:nth-child(5) {
                -webkit-transition-duration: 180ms;
                transition-duration: 180ms;
            }

            .menu-item:nth-child(6) {
                -webkit-transition-duration: 180ms;
                transition-duration: 180ms;
            }

            .menu-item:nth-child(7) {
                -webkit-transition-duration: 180ms;
                transition-duration: 180ms;
            }

            .menu-item:nth-child(8) {
                -webkit-transition-duration: 180ms;
                transition-duration: 180ms;
            }

            .menu-item:nth-child(9) {
                -webkit-transition-duration: 180ms;
                transition-duration: 180ms;
            }

            .menu-open-button {
                z-index: 2;
                -webkit-transition-timing-function: cubic-bezier(0.175, 0.885, 0.32, 1.275);
                transition-timing-function: cubic-bezier(0.175, 0.885, 0.32, 1.275);
                -webkit-transition-duration: 400ms;
                transition-duration: 400ms;
                -webkit-transform: scale(1.1, 1.1) translate3d(0, 0, 0);
                transform: scale(1.1, 1.1) translate3d(0, 0, 0);
                cursor: pointer;
                box-shadow: 3px 3px 0 0 rgba(0, 0, 0, 0.14);
            }

            .menu-open-button:hover {
                -webkit-transform: scale(1.2, 1.2) translate3d(0, 0, 0);
                transform: scale(1.2, 1.2) translate3d(0, 0, 0);
            }

            .menu-open:checked+.menu-open-button {
                -webkit-transition-timing-function: linear;
                transition-timing-function: linear;
                -webkit-transition-duration: 200ms;
                transition-duration: 200ms;
                -webkit-transform: scale(0.8, 0.8) translate3d(0, 0, 0);
                transform: scale(0.8, 0.8) translate3d(0, 0, 0);
            }

            .menu-open:checked~.menu-item {
                -webkit-transition-timing-function: cubic-bezier(0.935, 0, 0.34, 1.33);
                transition-timing-function: cubic-bezier(0.935, 0, 0.34, 1.33);
            }

            /* UBICA CADA ICONO EN LA UBICACION QUE CORRESPONDE */
            .menu-open:checked~.menu-item:nth-child(3) {
                transition-duration: 180ms;
                -webkit-transition-duration: 180ms;
                /* -webkit-transform: translate3d(0.08361px, -104.99997px, 0); */
                -webkit-transform: translate3d(0.08361px, -150.99997px, 0);
                transform: translate3d(0.08361px, -150.99997px, 0);
            }

            .menu-open:checked~.menu-item:nth-child(4) {
                transition-duration: 280ms;
                -webkit-transition-duration: 280ms;
                -webkit-transform: translate3d(90.9466px, -52.47586px, 0);
                transform: translate3d(90.9466px, -52.47586px, 0);
            }

            .menu-open:checked~.menu-item:nth-child(5) {
                transition-duration: 380ms;
                -webkit-transition-duration: 380ms;
                -webkit-transform: translate3d(90.9466px, 52.47586px, 0);
                transform: translate3d(90.9466px, 52.47586px, 0);
            }

            .menu-open:checked~.menu-item:nth-child(6) {
                transition-duration: 480ms;
                -webkit-transition-duration: 480ms;
                -webkit-transform: translate3d(0.08361px, 104.99997px, 0);
                transform: translate3d(0.08361px, 104.99997px, 0);
            }

            .menu-open:checked~.menu-item:nth-child(7) {
                transition-duration: 580ms;
                -webkit-transition-duration: 580ms;
                -webkit-transform: translate3d(-90.86291px, 52.62064px, 0);
                transform: translate3d(-90.86291px, 52.62064px, 0);
            }

            .menu-open:checked~.menu-item:nth-child(8) {
                transition-duration: 680ms;
                -webkit-transition-duration: 680ms;
                -webkit-transform: translate3d(-91.03006px, -52.33095px, 0);
                transform: translate3d(-91.03006px, -52.33095px, 0);
            }

            .menu-open:checked~.menu-item:nth-child(9) {
                transition-duration: 780ms;
                -webkit-transition-duration: 780ms;
                -webkit-transform: translate3d(-0.25084px, -104.9997px, 0);
                transform: translate3d(-0.25084px, -104.9997px, 0);
            }

            .blue {
                background-color: #669AE1;
                box-shadow: 3px 3px 0 0 rgba(0, 0, 0, 0.14);
                text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.12);
            }

            .blue:hover {
                color: #669AE1;
                text-shadow: none;
            }

            .green {
                background-color: #70CC72;
                box-shadow: 3px 3px 0 0 rgba(0, 0, 0, 0.14);
                text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.12);
            }

            .green:hover {
                color: #70CC72;
                text-shadow: none;
            }

            .red {
                background-color: #FE4365;
                box-shadow: 3px 3px 0 0 rgba(0, 0, 0, 0.14);
                text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.12);
            }

            .red:hover {
                color: #FE4365;
                text-shadow: none;
            }

            .purple {
                background-color: #C49CDE;
                box-shadow: 3px 3px 0 0 rgba(0, 0, 0, 0.14);
                text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.12);
            }

            .purple:hover {
                color: #C49CDE;
                text-shadow: none;
            }

            .orange {
                background-color: #FC913A;
                box-shadow: 3px 3px 0 0 rgba(0, 0, 0, 0.14);
                text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.12);
            }

            .orange:hover {
                color: #FC913A;
                text-shadow: none;
            }

            .lightblue {
                background-color: #62C2E4;
                box-shadow: 3px 3px 0 0 rgba(0, 0, 0, 0.14);
                text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.12);
            }

            .lightblue:hover {
                color: #62C2E4;
                text-shadow: none;
            }

            .credit {
                margin: 24px 20px 120px 0;
                text-align: right;
                color: #EEEEEE;
            }

            .credit a {
                padding: 8px 0;
                color: #C49CDE;
                text-decoration: none;
                transition: all 0.3s ease 0s;
            }

            .credit a:hover {
                text-decoration: underline;
            }
        </style>
        <nav class="menuother">
            <input type="checkbox" checked href="#" class="menu-open" name="menu-open" id="menu-open" />
            <label class="menu-open-button" for="menu-open">
                <span class="lines line-1"></span>
                <span class="lines line-2"></span>
                <span class="lines line-3"></span>
            </label>

            <a href="#" class="menu-item blue"> <i class="fa fa-anchor"></i> </a>
            <a href="#" class="menu-item green"> <i class="fa fa-coffee"></i> </a>
            <a href="#" class="menu-item red"> <i class="fa fa-heart"></i> </a>
            <a href="#" class="menu-item purple"> <i class="fa fa-microphone"></i> </a>
            <a href="#" class="menu-item orange"> <i class="fa fa-star"></i> </a>
            <a href="#" class="menu-item lightblue"> <i class="fa fa-diamond"></i> </a>
        </nav>
        <script>
            function distribuirElementos() {
                const menu = document.querySelector('.menuother');
                const items = menu.querySelectorAll('.menu-item');
                const cantidadElementos = items.length;
                const radio = 200; // Radio del círculo
                const anguloEntreElementos = 360 / cantidadElementos;

                items.forEach((item, index) => {
                    const angulo = anguloEntreElementos * index;
                    const radianes = (angulo * Math.PI) / 180;
                    const x = Math.round(radio * Math.cos(radianes));
                    const y = Math.round(radio * Math.sin(radianes));
                    item.style.transform = `translate(${x}px, ${y}px)`;
                });
            }

            distribuirElementos();
        </script>
<?php
        break;
    case 'otro':
        echo 'echo otros';
        break;
}

?>