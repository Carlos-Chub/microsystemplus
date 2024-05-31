<?php
session_start();
include '../../includes/BD_con/db_con.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset=UTF-8>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--borrar estas 3 lineas al terminar desarrollo-->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title>INDICADORES</title>

    <link rel="stylesheet" href="../../includes/css/style.css">
    <?php require_once '../../includes/incl.php'; ?>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

</head>

<body class="<?= ($_SESSION['background'] == '1') ? '' : ''; ?>">
    <?php require '../infoEnti/infoEnti.php';

    $infoEnti = infoEntidad($_SESSION['id'], $conexion);
    ?>
    <!-- ESTE ES MENU PARA LOS INDICADOS  -->

    <nav class="sidebar ">
        <header>
            <div class="image-text">
                <span class="image">
                    <img src="../../includes/img/logomicro.png" alt="">
                </span>

                <div class="text logo-text">
                    <span class="name">COOPERANAME</span>
                    <span class="profession">SOTECPRO</span>
                </div>
            </div>

            <i class='bx bx-chevron-right toggle'></i>
        </header>
        <!--aqui inicia el menu-->
        <div class="menu-bar">
            <div class="menu">
                <li class="search-box">
                </li>
                <ul class="menu-links">
                    <li class="nav-link">
                        <a style="cursor: pointer;" onclick="printdiv('pearls', '#cuadroprincipal', 'data', '0')">
                            <i class="fa-solid fa-p fa-xl" id="ico2"></i>
                            <span class="text nav-text" id="txtmenu"> PEARLS </span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a style="cursor: pointer;" onclick="printdiv('camels', '#cuadroprincipal', 'data', '0')">
                            <i class="fa-solid fa-e fa-xl" id="ico2"></i>
                            <span class="text nav-text" id="txtmenu">CASSDSFSA</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a onclick="printdiv('otro', '#cuadroprincipal', 'data', '0')">
                            <i class="fa-solid fa-a fa-xl" id="ico2"></i>
                            <span class="text nav-text" id="txtmenu">ACSD</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a onclick="printdiv('rendimiento', '#cuadro', 'perlas_crud', '0')">
                            <i class="fa-solid fa-r fa-xl" id="ico2"></i>
                            <span class="text nav-text" id="txtmenu">Rendimiento</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a onclick="printdiv('liquidez', '#cuadro', 'perlas_crud', '0')">
                            <i class="fa-solid fa-l fa-xl" id="ico2"></i>
                            <span class="text nav-text" id="txtmenu">Liquidez</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a onclick="printdiv('señales', '#cuadro', 'perlas_crud', '0')">
                            <i class="fa-solid fa-s fa-xl" id="ico2"></i>
                            <span class="text nav-text" id="txtmenu">Señales</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="bottom-content">
                <li class="">
                    <a href="../login.php">
                        <i class='bx bx-log-out icon'></i>
                        <span class="text nav-text">Cerrar Session</span>
                    </a>
                </li>
                <li class="mode">
                    <div class="sun-moon">
                        <i class='bx bx-moon icon moon'></i>
                        <i class='bx bx-sun icon sun'></i>
                    </div>
                    <span class="mode-text text">Modo Oscuro</span>
                    <div class="toggle-switch">
                        <span class="switch"></span>
                    </div>
                </li>
            </div>
        </div>
        <!---aqui finaliza el menu -->
    </nav>

    <section class="home">
        <div class="row">
            <div class="col d-flex justify-content-start">
                <div class="text">INDICADORES</div>
            </div>
            <div class="col d-flex justify-content-end">
                <div class="text"><?= $infoEnti['nomAge'] ?></div>
            </div>
        </div>
        <div class="container" id="cuadroprincipal">
            <div class="d-flex flex-column h-100">
                <div class="flex-grow-1">
                    <div class="row align-items-center" style="max-width: 100% !important; height: calc(75vh) !important;">
                        <div class="row d-flex justify-content-center">
                            <div class="col-auto">
                                <img src="../../includes/img/imgindicador2.jpg" alt="indicadores img" srcset="" width="500">
                                <p class="displayed text-success text-center" style='font-family: "Garamond", serif;
                                    font-weight: bold;
                                    font-size: x-large;'> Sistema orientado para microfinanzas </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="loader-container loading--show">
        <div class="loader"></div>
        <div class="loaderimg"></div>
        <div class="loader2"></div>
    </div>
    <script>
        function printdiv(condi, idiv, dir, xtra) {
            loaderefect(1);
            dire = "" + dir + ".php";
            $.ajax({
                url: dire,
                method: "POST",
                data: {
                    condi,
                    xtra
                },
                success: function(data) {
                    $(idiv).html(data);
                    loaderefect(0);
                }
            })
        }

        function printdiv2(idiv, xtra) {
            loaderefect(1);
            condi = $("#condi").val();
            dir = $("#file").val();
            dire = "views_reporte/" + dir + ".php";
            $.ajax({
                url: dire,
                method: "POST",
                data: {
                    condi,
                    xtra
                },
                success: function(data) {
                    loaderefect(0);
                    $(idiv).html(data);
                }
            })
        }

        function loaderefect(sh) {
            const LOADING = document.querySelector('.loader-container');
            switch (sh) {
                case 1:
                    LOADING.classList.remove('loading--hide');
                    LOADING.classList.add('loading--show');
                    break;
                case 0:
                    LOADING.classList.add('loading--hide');
                    LOADING.classList.remove('loading--show');
                    break;
            }
        }

        function getinputsval(datos) {
            const inputs2 = [''];
            var i = 0;
            while (i < datos.length) {
                inputs2[i] = document.getElementById(datos[i]).value;
                i++;
            }
            return inputs2;
        }

        function getselectsval(datos) {
            const selects2 = [''];
            i = 0;
            while (i < datos.length) {
                var e = document.getElementById(datos[i]);
                selects2[i] = e.options[e.selectedIndex].value;
                i++;
            }
            return selects2;
        }

        function getradiosval(datos) {
            const radios2 = [''];
            i = 0;
            while (i < datos.length) {
                radios2[i] = document.querySelector('input[name="' + datos[i] + '"]:checked').value;
                i++;
            }
            return radios2;
        }

        function obtiene(inputs, selects, radios, condi, id, archivo, urlfile) {
            var inputs2 = [];
            var selects2 = [];
            var radios2 = [];
            inputs2 = getinputsval(inputs)
            selects2 = getselectsval(selects)
            radios2 = getradiosval(radios)
            generico(inputs2, selects2, radios2, condi, id, archivo, urlfile);
        }

        function generico(inputs, selects, radios, condi, id, archivo, urlfile) {
            $.ajax({
                url: urlfile,
                method: "POST",
                data: {
                    inputs,
                    selects,
                    radios,
                    condi,
                    id,
                    archivo
                },
                beforeSend: function() {
                    loaderefect(1);
                },
                success: function(data) {
                    const data2 = JSON.parse(data);
                    if (data2[1] == "1") {
                        Swal.fire({
                            icon: 'success',
                            title: 'Muy Bien!',
                            text: data2[0]
                        })
                        // printdiv2("#cuadro", id);
                        printdiv('p1', `#cuadro`, `app/pearls/views/proteccion`, `0`)
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '¡ERROR!',
                            text: data2[0]
                        })
                    }
                },
                complete: function() {
                    loaderefect(0);
                }
            })
        }
        $(document).ready(function() {
            loaderefect(0);
        });
    </script>
</body>

</html>