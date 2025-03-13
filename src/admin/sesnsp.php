<?php session_start();
include('D:/xampp/htdocs/sgr-dpe/service/connection.php');
if ($_SESSION['user_data']['id'] == 5) {
    header('Location: validacion_victimas.php');
    exit();
}
?>
<!DOCTYPE HTML>
<html>

<head>
    <title>SBN</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="pragma" content="no-cache" />

    <link rel="shortcut icon" href="../../assets/img/fge.png" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <link rel="stylesheet" href="assets/css/styles.css" />

    <link rel="stylesheet" href="../../css/styles.css">
    <link rel="stylesheet" href="../../css/dropdown-style.css">
    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <script src="../../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>

    <script src="../../js/script.js"></script>
    <script src="js/script.js"></script>
    <style>
        .loader-div {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loader-svg {
            width: 20%;
            height: 20%;
        }

        .btn-outline-secondary {
            background-color: rgb(0, 139, 204);
            color: #FFF;
            border-color: rgb(0, 139, 204);
            text-align: center;
            box-shadow: 2px 2px 3px #999;
        }

        .btn-outline-secondary:hover {
            background-color: white;
            color: rgb(0, 139, 204);
            border-color: rgb(0, 139, 204);
            text-align: center;
            box-shadow: 2px 2px 3px #999;
        }

        .btn-outline-success {
            background-color: #0C9;
            color: #FFF;
            border-color: #0C9;
            text-align: center;
            box-shadow: 2px 2px 3px #999;
        }

        .btn-outline-success:hover {
            background-color: white;
            color: #0C9;
            border-color: #0C9;
            text-align: center;
            box-shadow: 2px 2px 3px #999;
        }
    </style>
</head>

<body class="is-preload">

    <div class="loader-div" id="loader">
        <img class="loader-svg" src="../../assets/img/loader.svg" alt="Cargando...">
    </div>


    <div id="wrapper">
        <div id="main">
            <header id="navbar">
                <div class="dropdown username-section">
                    <div class="dropbtn">
                        <img onclick="myFunction()" src="../../assets/img/user.png" alt="">
                        <div onclick="myFunction()">
                            <div id="username"><?php echo $_SESSION['user_data']['name'] . ' ' . $_SESSION['user_data']['paternal_surname'] . ' ' . $_SESSION['user_data']['maternal_surname']; ?></div>
                            <div id="role">Administrador</div>
                        </div>
                    </div>

                    <div id="myDropdown" class="dropdown-content">
                        <a onclick="closeSession()">Cerrar Sesión</a>
                    </div>
                </div>
            </header>

            <div class="background-header">
                <h1>CONSULTA CNI</h1>
            </div>

            <div class="inner">
                <section>
                    <br>
                    <br>
                    <form class="search-form" action="service/sesnsp/generate_excel_sesnsp.php" method="POST">
                        <div class="form-row">
                            <div class="col-md-6 form-group">
                                <label style="font-weight:bold">Fecha inicial: *</label>
                                <input id="main-search-search-month" name="fecha_inicial" type="date" class="form-control" required="true">
                            </div>
                            <br>
                            <div class="col-md-6 form-group">
                                <label style="font-weight:bold">Fecha final: *</label>
                                <input id="main-search-search-month" name="fecha_final" type="date" class="form-control" required="true">
                            </div>
                        </div>
                        <br>
                        <div class="form-buttons">
                            <button type="submit" class="btn btn-outline-success rounded-button">Generar EXCEL</button>
                        </div>
                    </form>
                </section>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    document.querySelector(".search-form").addEventListener("submit", function() {
                        setTimeout(() => {
                            this.reset();
                        }, 500); // Se limpia después de medio segundo
                    });
                });
            </script>

        </div>

        <div id="sidebar">
            <div class="inner">
                <nav id="menu">
                    <header class="major">
                        <h2><a id="text-logo">FGE</a>&nbsp;&nbsp;&nbsp;BASES NACIONALES</h2>
                    </header>

                    <ul>
                        <?php
                        if ($_SESSION['user_data']['id'] != 6) {
                        ?>
                            <li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="senap.php">SENAP</a></li>
                            <li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="microdato.php">Microdato</a></li>
                            <li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="avp.php">Exportar base de datos histórica</a></li>
                            <li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="norma_tecnica.php">Norma técnica</a></li>
                            <li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="censo_procu.php">Censo procuración de justicia</a></li>
                            <li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="incidencia_sesesp.php">Incidencia delictiva SESESP</a></li>
                            <li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="validacion_victimas.php">Validación de víctimas</a></li>
                            <li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="producto_estadistico.php">Producto estadístico</a></li>
                            <li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="sedena.php">Consulta SEDENA</a></li>
                        <?php
                        }
                        ?>
                        <li class="selected"><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="#">Consulta CNI</a></li>
                    </ul>
                </nav>

                <footer id="footer">
                    <!--<p>Sistema de gestion de reportes v1-21.08.04</p>-->
                </footer>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/browser.min.js"></script>
    <script src="assets/js/breakpoints.min.js"></script>
    <script src="assets/js/util.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>