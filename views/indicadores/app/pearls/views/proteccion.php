<?php
session_start();
include '../../../../../includes/Config/database.php';
include '../../../../../vws_indicadores/formulas.php';
$database = new Database($db_host, $db_name, $db_user, $db_password, $db_name_general);

$condi = $_POST["condi"];
switch ($condi) {
    case 'p1':
        /*rovisión para préstamos incobrables / provisión requerida para préstamos con morosidad >12 meses*/

        // try {
        //     // $database->openConnection();
        //     // $rows = $database->selectAll('ctb_fuente_fondos');

        // } catch (Exception $e) {
        //     echo "Error: " . $e->getMessage();
        // } finally {
        //     $database->closeConnection();
        // }

?>

    <?php
        break;
    case 'p2':
        /*P2. Provisión neta para préstamos incobrables / provisión requerida para préstamos morosos menor a 12 meses*/

    ?>

    <?php
        break;
    case 'p3':
        /* */

    ?>

    <?php
        break;
    case 'p4':
        /*Préstamos castigados / total cartera de préstamos promedio*/
        $xtra = $_POST["xtra"];
        // echo "<pre>";
        // echo print_r($xtra);
        // echo "</pre>";
        $year2 = ($xtra == 0) ? date('Y') : $xtra[1];
        $year1 = ($xtra == 0) ? $year2 - 1 : $xtra[0];

        $fechaini1 = ($year1 - 1) . '-12-31'; //AL 31 DE DICIEMBRE DEL AÑO ANTERIOR
        $fechafin1 = $year1 . '-12-31'; //AL 31 DE DICIEMBRE DEL AÑO  QUE ESTAMOS REVISANDO

        $fechaini2 = ($year2 - 1) . '-12-31'; //AL 31 DE DICIEMBRE DEL AÑO ANTERIOR
        $fechafin2 = $year2 . '-12-31'; //AL 31 DE DICIEMBRE DEL AÑO  QUE ESTAMOS REVISANDO

        // echo $fechaini1;
        // echo '<br>';
        // echo $fechafin1;
        // echo '<hr>';
        // echo $fechaini2;
        // echo '<br>';
        // echo $fechafin2;
        // return;

        $querygeneral = "SELECT IFNULL(SUM(saldokp),0) AS saldos FROM (
                    SELECT (cremi.NCapDes-IFNULL(kar.sum_KP, 0)) AS saldokp FROM cremcre_meta cremi 
                    INNER JOIN tb_cliente cli ON cli.idcod_cliente = cremi.CodCli 
                    INNER JOIN cre_productos prod ON prod.id = cremi.CCODPRD 
                    INNER JOIN tb_usuario usu ON usu.id_usu = cremi.CodAnal 
                    LEFT JOIN (
                        SELECT ccodcta, SUM(KP) AS sum_KP, SUM(interes) AS sum_interes FROM CREDKAR
                        WHERE dfecpro <= ? AND cestado != 'X' AND ctippag = 'P' GROUP BY ccodcta
                    ) AS kar ON kar.ccodcta = cremi.CCODCTA
                    WHERE (cremi.Cestado='F' OR cremi.Cestado='G') AND cremi.DFecDsbls <= ? AND (NCapDes-IFNULL(kar.sum_KP, 0)) > 0
            ) AS sumasaldos";
        $queryincobrables = "SELECT IFNULL(SUM(saldokp),0) saldos FROM (
            SELECT (cremi.NCapDes-IFNULL(kar.sum_KP, 0)) AS saldokp
            FROM cremcre_meta cremi 
            INNER JOIN tb_cliente cli ON cli.idcod_cliente = cremi.CodCli 
            INNER JOIN cre_productos prod ON prod.id = cremi.CCODPRD 
            INNER JOIN tb_usuario usu ON usu.id_usu = cremi.CodAnal 
            LEFT JOIN (
                SELECT ccodcta, SUM(KP) AS sum_KP, SUM(interes) AS sum_interes FROM CREDKAR
                WHERE dfecpro <= ? AND cestado != 'X' AND ctippag = 'P' GROUP BY ccodcta
            ) AS kar ON kar.ccodcta = cremi.CCODCTA
            WHERE cremi.Cestado='I' AND cremi.DFecDsbls <= ? AND cremi.fecincobrable<= ? AND (NCapDes-IFNULL(kar.sum_KP, 0))>0
            ) AS sumasaldos;";
        try {
            $database->openConnection();
            //AÑO 1
            $result = $database->getSingleResult($queryincobrables, [$fechafin1, $fechafin1, $fechafin1]);
            $a1 = $result['saldos'];
            $result = $database->getSingleResult($queryincobrables, [$fechaini1, $fechaini1, $fechaini1]);
            $b1 = $result['saldos'];
            $result = $database->getSingleResult($querygeneral, [$fechafin1, $fechafin1]);
            $c1 = $result['saldos'];
            $result = $database->getSingleResult($querygeneral, [$fechaini1, $fechaini1]);
            $d1 = $result['saldos'];
            //PRESTAMOS CASTIGADOS = a-b
            $castigados1 = bcdiv((floatval($a1) - floatval($b1)), 1, 4);
            //TOTAL CARTERA PRESTAMOS = (c+d)/2
            $totalcartera1 = bcdiv((floatval($c1) + floatval($d1)), 2, 4);
            //RESULTADO FINANCIERO = castigados1/totalcartera1
            $resultado1 = bcdiv((floatval($castigados1)), (floatval($totalcartera1)), 6);
            // $resultado1 = ($saldocartera1 != 0) ? $saldoincobrable1 / $saldocartera1 : 0;
            // $resultado2 = ($saldocartera2 != 0) ? $saldoincobrable2 / $saldocartera2 : 0;
            // $resultadogeneral = ($saldoincobrable2 - $saldoincobrable1) / (($saldocartera2 + $saldocartera1) / 2);
            //AÑO 2
            $result = $database->getSingleResult($queryincobrables, [$fechafin2, $fechafin2, $fechafin2]);
            $a2 = $result['saldos'];
            $result = $database->getSingleResult($queryincobrables, [$fechaini2, $fechaini2, $fechaini2]);
            $b2 = $result['saldos'];
            $result = $database->getSingleResult($querygeneral, [$fechafin2, $fechafin2]);
            $c2 = $result['saldos'];
            $result = $database->getSingleResult($querygeneral, [$fechaini2, $fechaini2]);
            $d2 = $result['saldos'];
            $database->closeConnection();

            //PRESTAMOS CASTIGADOS = a-b
            $castigados2 = bcdiv((floatval($a2) - floatval($b2)), 1, 4);
            //TOTAL CARTERA PRESTAMOS = (c+d)/2
            $totalcartera2 = bcdiv((floatval($c2) + floatval($d2)), 2, 4);
            //RESULTADO FINANCIERO = castigados1/totalcartera1
            $resultado2 = bcdiv((floatval($castigados2)), (floatval($totalcartera2)), 6);

            $interpretacion = "Al evaluar los préstamos castigados en relación al total de la cartera de préstamos bruta, 
            se evidencia que la cooperativa para el año " . $year1 . " posee un " . $resultado1 . "% y en el año " . $year2 . " presenta un castigo de " . $resultado2 . "; 
            denotándose un" . (($resultado2 > $resultado1) ? " incremento " : " decremento") . " en el periodo " . $year2 . ".";
        } catch (Exception $e) {
            echo "Error al ejecutar la consulta: " . $e->getMessage();
        }
    ?>
        <style>
            @keyframes moveAndRotateFormula {
                0% {
                    transform: translateX(+10px) rotate(10deg);
                }
                25% {
                    transform: translateX(10px) rotate(0deg);
                }
                50% {
                    transform: translateX(10px) rotate(-10deg);
                }
                75% {
                    transform: translateX(+10px) rotate(0deg);
                }
                100% {
                    transform: translateX(10px) rotate(10deg);
                }
            }

            #formula {
                position: absolute;
                z-index: 1;
                animation: moveAndRotateFormula 0.5s infinite;
            }
        </style>
        <div class="container">
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-center">
                    <span id="nombre" class="badge rounded-pill text-bg-primary fs-5">Préstamos castigados / total cartera de préstamos promedio</span>
                </div>
                <div class="col-12 d-flex justify-content-center">
                    <span id="formula" class="badge text-bg-secondary fs-4" style="background-color: #33FF33 !important;"> <?php echo $P4; ?></span>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-sm-6">
                    <div class="card text-bg-light contenedort" style="height: 100%;">
                        <div class="card-header d-flex justify-content-center">EJERCICIO 1</div>
                        <div class="card-body">
                            <div class="row" id="filfechas">
                                <div class="col-3 d-flex justify-content-center">
                                    <label for="year" class="form-label">
                                        <i class="fas fa-calendar-alt"></i> AÑO:
                                    </label>
                                </div>
                                <div class="col-9">
                                    <input type="number" id="year1" name="year1" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo $year1; ?>" required class="form-control" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card text-bg-light contenedort" style="height: 100%;">
                        <div class="card-header d-flex justify-content-center">EJERCICIO 2</div>
                        <div class="card-body">
                            <div class="row" id="filfechas">
                                <div class="col-3 d-flex justify-content-center">
                                    <label for="year" class="form-label">
                                        <i class="fas fa-calendar-alt"></i> AÑO:
                                    </label>
                                </div>
                                <div class="col-9">
                                    <input type="number" id="year2" name="year2" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo $year2; ?>" required class="form-control" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-items-md-center mt-3">
                <div class="col-12 align-items-center d-flex justify-content-center">
                    <button type="button" id="btnSave" class="btn btn-primary" onclick="printdiv('p4', `#cuadro`, `app/pearls/views/proteccion`, getinputsval(['year1', 'year2']));">
                        <i class="fa-solid fa-circle"></i> Procesar
                    </button>
                    <!-- <button type="button" class="btn btn-danger" onclick="printdiv2('#cuadro','0')">
                        <i class="fa-solid fa-ban"></i> Cancelar
                    </button> -->
                    <!-- <button type="button" class="btn btn-warning" onclick="salir()">
                        <i class="fa-solid fa-circle-xmark"></i> Salir
                    </button> -->
                </div>
            </div>
        </div>

        <div class="container mt-3">
            <div class="row">
                <div class="col-6">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title">Castigos acumulados</h5>
                                </div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center"><span class="badge text-bg-primary">a</span> Ejercicio en curso <span class="badge text-bg-danger"> <?php echo number_format($a1, 2, '.', ','); ?> </span> </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center"><span class="badge text-bg-primary">b</span> Ejercicio anterior <span class="badge text-bg-danger"> <?php echo number_format($b1, 2, '.', ','); ?> </span> </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title">Cartera de Préstamos Bruta</h5>
                                </div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center"><span class="badge text-bg-primary">c</span> Ejercicio en curso <span class="badge text-bg-primary"> <?php echo number_format($c1, 2, '.', ','); ?> </span> </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center"><span class="badge text-bg-primary">d</span> Ejercicio anterior <span class="badge text-bg-primary"> <?php echo number_format($d1, 2, '.', ','); ?> </span> </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 d-flex justify-content-center">
                            <span class="badge text-bg-warning fs-5">RESULTADO FINAL: <?php echo number_format($resultado1, 5, '.', ','); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title">Castigos acumulados</h5>
                                </div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center"><span class="badge text-bg-primary">a</span> Ejercicio en curso <span class="badge text-bg-danger"> <?php echo number_format($a2, 2, '.', ','); ?> </span> </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center"><span class="badge text-bg-primary">b</span> Ejercicio anterior <span class="badge text-bg-danger"> <?php echo number_format($b2, 2, '.', ','); ?> </span> </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title">Cartera de Préstamos Bruta</h5>
                                </div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center"><span class="badge text-bg-primary">c</span> Ejercicio en curso <span class="badge text-bg-primary"> <?php echo number_format($c2, 2, '.', ','); ?> </span> </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center"><span class="badge text-bg-primary">d</span> Ejercicio anterior <span class="badge text-bg-primary"> <?php echo number_format($d2, 2, '.', ','); ?> </span> </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 d-flex justify-content-center">
                            <span class="badge text-bg-warning fs-5">RESULTADO FINAL: <?php echo number_format($resultado2, 5, '.', ','); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container mt-3">
            <div class="row">
                <div class="col-sm-6">
                    <div class="card text-bg-light contenedort" style="height: 100%;">
                        <!-- <div class="card-header">EJERCICIO ANTERIOR</div> -->
                        <div class="card-body">
                            <div id="chart_div"></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card text-bg-light contenedort" style="height: 100%;">
                        <!-- <div class="card-header">EJERCICIO ANTERIOR</div> -->
                        <div class="card-body">
                            <div id="chart_div2"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="container mt-3">
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-center">
                    <div class="card text-bg-light contenedort" style="height: 100%;">
                        <div class="card-body">
                            <!-- <p class="badge rounded-pill text-bg-success text-justify" style="width: 100%;">
                                <?php echo $interpretacion; ?>
                            </p> -->
                            <p class="card-text"><?php echo $interpretacion; ?></p>
                            <!-- <p class="badge rounded-pill text-bg-success fs-6"></p> -->
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <script>
            var yearInputs = document.querySelectorAll('#year1, #year2');
            yearInputs.forEach(yearInput => {
                yearInput.addEventListener('input', () => {
                    if (yearInput.value.length > 4) {
                        yearInput.value = yearInput.value.slice(0, 4);
                    }
                });

                yearInput.addEventListener('blur', () => {
                    if (yearInput.value < 1900 || yearInput.value > <?php echo date('Y'); ?>) {
                        yearInput.setCustomValidity('Ingrese un año valido entre 1900 y el año actual.');
                    } else {
                        yearInput.setCustomValidity('');
                    }
                });
            });

            google.charts.load('current', {
                'packages': ['corechart', 'bar']
            });
            google.charts.setOnLoadCallback(drawChart);
            google.charts.setOnLoadCallback(drawChart2);

            function drawChart() {
                var data = new google.visualization.DataTable();
                var year1 = document.getElementById("year1").value;
                var year2 = document.getElementById("year2").value;
                data.addColumn('string', 'Topping');
                data.addColumn('number', 'Resultado');
                data.addRows([
                    [year1, <?php echo $resultado1; ?>],
                    [year2, <?php echo $resultado2; ?>],
                ]);

                // Set chart options
                var options = {
                    'title': 'Proteccion #4',
                    'subtitle': 'GENERAL RESULTADO',
                    'is3D': true,
                };
                var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
                chart.draw(data, options);
            }



            function drawChart2() {
                var year1 = document.getElementById("year1").value;
                var year2 = document.getElementById("year2").value;
                var data = google.visualization.arrayToDataTable([
                    ['Cartera', year1, year2],
                    ['Castigada', <?php echo $castigados1; ?>, <?php echo $castigados2; ?>],
                    ['Bruta', <?php echo $totalcartera1; ?>, <?php echo $totalcartera2; ?>],
                ]);

                var options = {
                    chart: {
                        title: 'Por cartera ',
                    },
                    bars: 'horizontal' // Required for Material Bar Charts.
                };

                var chart = new google.charts.Bar(document.getElementById('chart_div2'));

                chart.draw(data, google.charts.Bar.convertOptions(options));

                // var options = {
                //     title: 'Comparativa de carteras',
                //     vAxis: {
                //         title: '%'
                //     },
                //     hAxis: {
                //         title: 'CARTERA'
                //     },
                //     seriesType: 'bars',
                //     series: {
                //         5: {
                //             type: 'line'
                //         }
                //     }
                // };

                // var chart = new google.visualization.ComboChart(document.getElementById('chart_div2'));
                // chart.draw(data, options);
            }
        </script>
    <?php
        break;
    case 'p5':
        /* */

        // try {
        //     // $database->openConnection();
        //     // $rows = $database->selectAll('ctb_fuente_fondos');

        // } catch (Exception $e) {
        //     echo "Error: " . $e->getMessage();
        // } finally {
        //     $database->closeConnection();
        // }

    ?>

<?php
        break;
    case 'camels':
        echo 'echo camels';
        break;
    case 'otro':
        echo 'echo otros';
        break;
}

?>