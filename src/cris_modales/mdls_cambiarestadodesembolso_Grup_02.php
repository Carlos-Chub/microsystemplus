<!-- ---------------------------------TERMINA EL MODAL  -->
<div class="modal" id="modal_creditos_a_desembolsar_Grup">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Cambiar estado de Credito a desembolsar a Solicitud.</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <input type="text" id="id_modal_hidden" value="" readonly hidden>
            </div>
            <!-- Modal body -->
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tabla_creditos_desembolsar" class="table table-striped table-hover" style="width: 100% !important;">
                        <thead>
                            <tr>
                                <th scope="col">Codigo</th>
                                <th scope="col">Nombre de Grupo</th>
                                <th scope="col">Direccion</th>
                                <th scope="col">Ciclo</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="" onclick="cerrar_modal('#modal_creditos_a_desembolsar', 'hide', '#id_modal_hidden')">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- ---------------------------------TERMINA EL MODAL  -->

<script>
    // para cuentas de ahorro
    $(document).ready(function() {
        //generar la consulta para la tabla
        $('#tabla_creditos_desembolsar').on('search.dt').DataTable({
            "aProcessing": true,
            "aServerSide": true,
            "ordering": false,
            "lengthMenu": [
                [10, 15, -1],
                ['10 filas', '15 filas', 'Mostrar todos']
            ],
            "ajax": {
                url: '../../src/cruds/crud_credito_grup.php',
                type: "POST",
                beforeSend: function() {
                    loaderefect(1);
                },
                data: {
                    'condi': "listadp_desembolso_a_solicitud"
                },
                dataType: "json",
                complete: function() {
                    loaderefect(0);
                }
            },
            "bDestroy": true,
            "iDisplayLength": 10,
            "order": [
                [1, "desc"]
            ],
            "language": {
                "lengthMenu": "Mostrar _MENU_ registros",
                "zeroRecords": "No se encontraron registros",
                "info": " ",
                "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "infoFiltered": "(filtrado de un total de: _MAX_ registros)",
                "sSearch": "Buscar: ",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Ultimo",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "sProcessing": "Procesando..."
            }
        });
    });
</script>
