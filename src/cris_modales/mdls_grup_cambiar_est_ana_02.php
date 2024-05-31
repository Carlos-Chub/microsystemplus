<div class="modal fade" id="buscargrupo2" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="margin-left: 0%;">
            <div class="modal-header">
                <h4 class="modal-title">Cambiar estado de Crédito a Solicitud</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table id="grupos" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre del Grupo</th>
                            <th>Dirección</th>
                            <th>Ciclo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="categoria_tb">
                    </tbody>
                </table>
            </div>
            <!-- <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
            </div> -->
        </div>
    </div>
</div>

<script>
//destruir el modal al cerrar 
$('#buscargrupo2').on('hidden.bs.modal', function (e) {
        $('#categoria_tb').empty(); 
    });

function loadconfig0(status1, status2) {
    $('#grupos').on('search.dt').DataTable({
        "aProcessing": true,
        "aServerSide": true,
        "ordering": false,
        "lengthMenu": [
            [10, 15, -1],
            ['10 filas', '15 filas', 'Mostrar todos']
        ],
        "ajax": {
            url: '../../src/cris_modales/fun_modal.php',
            type: "POST",
            beforeSend: function() {
                loaderefect(1);
            },
            data: {
                'condi2': "grupos_a_solicitud",
                status1,
                status2
            },
            dataType: "json",
            complete: function(data) {
                loaderefect(0);
                //console.log(data)
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
}
//CARGA DE DATOS EN TABLA
// $(document).ready(function() {
//   loadconfig("all","all");
// });
</script>
