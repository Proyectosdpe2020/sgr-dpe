<?php session_start();
include('D:/xampp/htdocs/sgr-dpe/service/connection.php');
if($_SESSION['user_data']['id'] == 4 || $_SESSION['user_data']['id'] == 5 || $_SESSION['user_data']['id'] == 8){
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
    <link rel="stylesheet" href="assets/css/main.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo time(); ?>" />

    <link rel="stylesheet" href="../../css/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../css/dropdown-style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">

    <script src="../../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>

    <script src="../../js/script.js?v=<?php echo time(); ?>"></script>
    <script src="js/script.js?v=<?php echo time(); ?>"></script>
    
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
                <h1>PRODUCTO ESTADÍSTICO</h1>
            </div>

            <div class="inner">
                <section>
                    </br>
                    <div class="form-top-Buttons">
                        <button type="button" class="btn btn-outline-primary senap-menu-button left-button" id="municipioBtn" style="height:38px; width: 235px;" onclick="changeForm('municipio')">Reporte por municipio</button>
                        <button type="button" class="btn btn-outline-primary senap-menu-button right-button" id="distritoBtn" style="height:38px; width: 280px;" onclick="changeForm('distrito')">Reporte por distrito judicial</button>
                    </div>
                    <br>

                    <!-- Formulario para Municipio -->
                    <div id="municipioForm">
                        <form id="pdfFormMunicipio" method="POST" target="_blank">
                            <label for="startMonthMunicipio">Mes inicial: *</label>
                            <select id="startMonthMunicipio" name="mesInicio">
                                <option value="" selected disabled>-- Selecciona --</option>
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>

                            <label for="endMonthMunicipio">Mes final: *</label>
                            <select id="endMonthMunicipio" name="mesFin" required>
                                <option value="" selected disabled>-- Selecciona --</option>
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>

                            <label for="yearMunicipio">Año: *</label>
                            <select id="yearMunicipio" name="anio" required>
                                <option value="" selected disabled>-- Selecciona --</option>
                                <option value="2030">2030</option>
                                <option value="2029">2029</option>
                                <option value="2028">2028</option>
                                <option value="2027">2027</option>
                                <option value="2026">2026</option>
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                                <option value="2022">2022</option>
                                <option value="2021">2021</option>
                                <option value="2020">2020</option>
                                <option value="2019">2019</option>
                                <option value="2018">2018</option>
                                <option value="2017">2017</option>
                                <option value="2016">2016</option>
                                <option value="2015">2015</option>
                            </select>
                            </br>
                            <button type="button" class="btn btn-outline-secondary rounded-button" style="width: 253px;" onclick="setActionAndSubmit('service/producto_estadistico/generate_pdf_municipio.php')">Generar PDF por municipio</button>
                            <button type="button" class="btn btn-outline-success rounded-button" style="width: 260px;" onclick="setActionAndSubmit('service/producto_estadistico/generate_excel_municipio.php')">Generar EXCEL por municipio</button>
                        </form>
                    </div>

                    <!-- Formulario para Distrito Judicial -->
                    <div id="distritoForm" style="display: none;">
                        <form id="pdfFormDistrito" method="POST" target="_blank">
                            <label for="startMonthDistrito">Mes inicial: *</label>
                            <select id="startMonthDistrito" name="mesInicio" required>
                                <option value="" selected disabled>-- Selecciona --</option>
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>

                            <label for="endMonthDistrito">Mes final: *</label>
                            <select id="endMonthDistrito" name="mesFin" required>
                                <option value="" selected disabled>-- Selecciona --</option>
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>

                            <label for="yearDistrito">Año: *</label>
                            <select id="yearDistrito" name="anio" required>
                                <option value="" selected disabled>-- Selecciona --</option>
                                <option value="2030">2030</option>
                                <option value="2029">2029</option>
                                <option value="2028">2028</option>
                                <option value="2027">2027</option>
                                <option value="2026">2026</option>
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                                <option value="2022">2022</option>
                                <option value="2021">2021</option>
                                <option value="2020">2020</option>
                                <option value="2019">2019</option>
                                <option value="2018">2018</option>
                                <option value="2017">2017</option>
                                <option value="2016">2016</option>
                                <option value="2015">2015</option>
                            </select>
                            </br>
                            <button type="button" class="btn btn-outline-secondary rounded-button" style="width: 253px;" onclick="setActionAndSubmit('service/producto_estadistico/generate_pdf_distrito.php')">Generar PDF por distrito</button>
                            <button type="button" class="btn btn-outline-success rounded-button" style="width: 260px;" onclick="setActionAndSubmit('service/producto_estadistico/generate_excel_distrito.php')">Generar EXCEL por distrito</button>
                        </form>
                    </div>
                </section>
            </div>

            <script>
                function setActionAndSubmit(actionUrl) {
                    // Determina qué formulario se va a enviar
                    let formToSubmit;
                    if (document.getElementById('municipioForm').style.display !== 'none') {
                        formToSubmit = document.getElementById('pdfFormMunicipio');
                    } else {
                        formToSubmit = document.getElementById('pdfFormDistrito');
                    }

                    // Realiza la validación de los campos del formulario
                    const selects = formToSubmit.querySelectorAll('select');
                    for (let select of selects) {
                        if (!select.value) {
                            alert("Por favor, llena todos los campos obligatorios antes de continuar.");
                            return; // Detener la ejecución si algún campo no está completo
                        }
                    }

                    // Si todos los campos están completos, establece la acción y envía el formulario
                    formToSubmit.action = actionUrl;
                    formToSubmit.submit();
                }


                // Función para cambiar entre los formularios
                function changeForm(formType) {
                    // Obtener elementos de los formularios y botones
                    var municipioForm = document.getElementById('municipioForm');
                    var distritoForm = document.getElementById('distritoForm');
                    var municipioBtn = document.getElementById('municipioBtn');
                    var distritoBtn = document.getElementById('distritoBtn');

                    // Restablecer valores y ocultar los formularios
                    if (formType === 'municipio') {
                        municipioForm.style.display = 'block';
                        distritoForm.style.display = 'none';
                        municipioBtn.disabled = true;
                        distritoBtn.disabled = false;

                        // Restablecer formulario de Municipio
                        resetForm('pdfFormMunicipio');
                    } else {
                        municipioForm.style.display = 'none';
                        distritoForm.style.display = 'block';
                        municipioBtn.disabled = false;
                        distritoBtn.disabled = true;

                        // Restablecer formulario de Distrito
                        resetForm('pdfFormDistrito');
                    }
                }

                // Función para restablecer un formulario
                function resetForm(formId) {
                    var form = document.getElementById(formId);
                    form.reset(); // Restablece todos los campos del formulario
                    var selects = form.getElementsByTagName('select');
                    for (var i = 0; i < selects.length; i++) {
                        selects[i].selectedIndex = 0; // Restablecer a la opción por defecto
                    }
                }

                // Llamar a la función para que el formulario de "Municipio" se muestre por defecto
                document.addEventListener('DOMContentLoaded', function() {
                    changeForm('municipio');
                });

                function refreshPage() {
                    // Recargar la página principal
                    location.reload();
                }

                document.getElementById("pdfFormMunicipio").addEventListener("submit", function(event) {
                    const startMonth = document.getElementById("startMonthMunicipio").value;
                    const endMonth = document.getElementById("endMonthMunicipio").value;
                    const year = document.getElementById("yearMunicipio").value;

                    if (!startMonth || !endMonth || !year) {
                        event.preventDefault(); // Detiene el envío del formulario
                        alert("Por favor, llena todos los campos obligatorios antes de continuar.");
                    }
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
                        if($_SESSION['user_data']['id'] != 6){
?>
                            
                            <li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="senap.php">SENAP</a></li>
                            <li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="microdato.php">Microdato</a></li>
                            <li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="avp.php">Exportar base de datos histórica</a></li>
                            <li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="norma_tecnica.php">Norma técnica</a></li>
                            <li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="censo_procu.php">Censo procuración de justicia</a></li>
                            <li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="incidencia_sesesp.php">Incidencia delictiva SESESP</a></li>
                            <li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="validacion_victimas.php">Validación de víctimas</a></li>
                            <li class="selected"><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="#">Producto estadístico</a></li>
<?php
                        }
                        else{
?>
                            <li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="senap.php">SENAP</a></li>
                            <li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="censo_procu.php">Censo procuración de justicia</a></li>
                            <li class="selected"><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="#">Producto estadístico</a></li>
<?php										
                        }
?>
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