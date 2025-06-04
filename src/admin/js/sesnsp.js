document.addEventListener("DOMContentLoaded", function () {
    // Reset del formulario tras submit
    const searchForm = document.querySelector(".search-form");
if (searchForm && searchForm.id !== "excelForm") {
    searchForm.addEventListener("submit", function () {
        setTimeout(() => {
            this.reset();
        }, 500);
    });
}

    const form = document.getElementById("excelForm");
    const tipoTabla = document.getElementById("tipoTabla");

    if (form && tipoTabla) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();

            const fecha_inicial = form.fecha_inicial.value;
            const fecha_final = form.fecha_final.value;
            const tipo = tipoTabla.value;

            if (!fecha_inicial || !fecha_final || !tipo) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Completa todos los campos.'
                });
                return;
            }

            let generateUrl = "";
            switch (tipo) {
                case "carpetas":
                    generateUrl = "service/sesnsp/generate_excel_carpetas.php";
                    break;
                case "delitos":
                    generateUrl = "service/sesnsp/generate_excel_delitos.php";
                    break;
                case "victimas":
                    generateUrl = "service/sesnsp/generate_excel_victimas.php";
                    break;
                default:
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Opción no válida.'
                    });
                    return;
            }

            document.getElementById("loader").style.display = "flex";

            $.ajax({
                url: 'service/sesnsp/get_sesnsp_procedure_data.php',
                method: 'POST',
                data: {
                    fecha_inicial: fecha_inicial,
                    fecha_final: fecha_final,
                    type: tipo === "carpetas" ? 1 : (tipo === "delitos" ? 2 : 3)
                },
                success: function (response) {
                    document.getElementById("loader").style.display = "none";

                    let data;
                    try {
                        data = JSON.parse(response);
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al procesar datos recibidos.'
                        });
                        return;
                    }

                    if (!data || Object.keys(data).length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Sin datos',
                            text: 'No hay datos para mostrar en el rango seleccionado.'
                        });
                        return;
                    }

                    const formExcel = document.createElement("form");
                    formExcel.method = "POST";
                    formExcel.action = generateUrl;

                    const inputData = document.createElement("input");
                    inputData.type = "hidden";
                    inputData.name = "data";
                    inputData.value = JSON.stringify(data);

                    const inputFechaFinal = document.createElement("input");
                    inputFechaFinal.type = "hidden";
                    inputFechaFinal.name = "fecha_final";
                    inputFechaFinal.value = fecha_final;

                    formExcel.appendChild(inputData);
                    formExcel.appendChild(inputFechaFinal);
                    document.body.appendChild(formExcel);

                    formExcel.submit();
                    document.body.removeChild(formExcel);
                    // form.reset();
                },
                error: function () {
                    document.getElementById("loader").style.display = "none";
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al obtener datos desde el servidor.'
                    });
                }
            });
        });
    }
});