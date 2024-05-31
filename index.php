<?php
session_start();
include 'includes/Config/config.php';
if (isset($_SESSION['usu'])) {
    header('location: ' . BASE_URL . 'views/');
} else {
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MICROSYSTEM</title>
        <link rel="shortcut icon" type="image/x-icon" href="includes/img/favmicro.ico">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
        <!-- FONT AWESOME -->
        <link rel="stylesheet" href="includes/css/estiloslog.css">

    </head>

    <body>
        <div class="container" style="display: flex; height: 100vh; max-width: none !important;">
            <div class="row">
                <div class="col-12 col-md-6 d-flex align-items-center" style="padding-top: 3rem; padding-bottom: 3rem; padding-left: 4rem; padding-right: 4rem !important;">
                    <!-- seccion de login -->
                    <div class="row">
                        <!-- titulo de login -->
                        <div class="row">
                            <div class="col-12">
                                <h3 class="fs-2 text-center text-primary"><b>Iniciar Sesión</b></h3>
                            </div>
                        </div>
                        <!-- imagen de microsystem -->
                        <div class="row d-flex justify-content-center">
                            <div class="col-auto">
                                <img src="includes/img/fondologo.png" alt="logo" width="40%" class="mx-auto d-block">
                            </div>
                        </div>
                        <!-- titulo de aplicacion -->
                        <div class="row mt-2">
                            <div class="col-12">
                                <h5 style="font-size: 1rem;" class="text-center text-secondary">Por favor inicia sesión con tu cuenta</h5>
                            </div>
                        </div>
                        <form method="POST" id="frmlogin">
                            <div class="row mt-2">
                                <div class="col-12">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-primary bg-gradient text-white"><i class="fa-solid fa-user"></i></span>
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="usuario" placeholder="Usuario" name="usuario">
                                            <label for="nombre">Usuario</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-primary bg-gradient text-white"><i class="fa-solid fa-lock"></i></span>
                                        <div class="form-floating">
                                            <input type="password" class="form-control border-end-0" id="password" placeholder="Contraseña" name="password">
                                            <label for="nombre">Contraseña</label>
                                        </div>
                                        <span class="input-group-text bg-transparent border-start-0 text-primary"><i class="fa-regular fa-eye" id="togglePassword"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 d-flex justify-content-center mt-4">
                                    <input name="condi" value="acceso" hidden>
                                    <button type="submit" id="btnEnviar" class="col btn btn-primary"><b>LOG IN</b></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- seccion de informacion -->
                <div class="col-12 col-md-6 d-flex align-items-center" style="background-color: #2444ac !important; padding-top: 3rem; padding-bottom: 3rem; padding-left: 4.3rem; padding-right: 4.3rem !important;">
                    <div>
                        <div class="row d-flex justify-content-center">
                            <div class="col-auto">
                                <img src="includes/img/MICROSYSTEMW.png" alt="logo" width="70%" class="mx-auto d-block">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <h5 style="font-size: 1.3rem;" class="text-center text-success">Soluciones Tecnologicas Profesionales</h5>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5 style="font-size: 3rem; font-weight: bold;" class="text-center text-white">S O T E C P R O</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <!-- efecto de loader -->
        <div class="loader-container loading--hide">
            <div class="loader"></div>
            <div class="loaderimg"></div>
            <div class="loader2"></div>
        </div>

        <!-- javascript del archivo -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous">
        </script>
        <!-- <script src="https://kit.fontawesome.com/6acb25b06f.js" crossorigin="anonymous"></script> -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <!-- libreria sweet alert -->
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script type="text/javascript" src="includes/js/scriptlog.js"></script>
        <script type="text/javascript" src="<?php echo BASE_URL; ?>/includes/js/all.min.js"></script>
    </body>

    </html>
<?php
}
?>