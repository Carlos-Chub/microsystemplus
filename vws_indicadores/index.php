<?php

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
    <meta http-equiv="Expires" content="0"/>
    <title>INDICADORES</title>

    <link rel="stylesheet" href="../includes/css/style.css">
    <?php require_once '../includes/incl.php'; ?>
    <script src="../includes/js/perlas.js"> </script>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script> 
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script> 

    </head>
    <body class="<?= ($_SESSION['background'] == '1') ? 'dark' : ''; ?>">
    <?php require '../src/menu/menu_perlas.php'; ?>

    <section class="home">
        <div class="text">INDICADORES</div>
        <div class="container " id="cuadro">  
        <div class="card crdbody">
        <div class="card-header panelcolor"> Bienvenido al módulo de indicadores PEARLS. </div>
        <div class="card-body"> --  </div>
        </div> </div>
    </section>

</body>
</html>


