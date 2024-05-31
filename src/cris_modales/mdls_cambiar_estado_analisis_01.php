<div class="modal" id="modal_analisis_01">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Cambio de estado de credito Analisis a Solicitud</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <input type="text" id="id_modal_hidden" value="" readonly hidden>
            </div>
            <!-- Modal body -->
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tabla_clientes_a_analizar" class="table table-striped table-hover" style="width: 100% !important;">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Cod. Crédito</th>
                                <th>Nombre cliente</th>
                                <th>Direccion</th>
                                <th>Ciclo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="" onclick="cerrar_modal('#modal_analisis_01', 'hide', '#id_modal_hidden')">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tabla_clientes_a_analizar').on('search.dt').DataTable({
            "aProcessing": true,
            "aServerSide": true,
            "ordering": false,
            "lengthMenu": [
                [10, 15, -1],
                ['10 filas', '15 filas', 'Mostrar todos']
            ],
            "ajax": {
                url: '../../src/cruds/crud_credito_indi.php',
                type: "POST",
                beforeSend: function() {
                    loaderefect(1);
                },
                data: {
                    'condi': "cambiar_estado_analisis"
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
