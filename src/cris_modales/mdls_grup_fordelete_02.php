<div class="modal fade" id="buscargrupo_for_delete" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content custom-modal-content" style="margin-left: 0%;">
            <div class="modal-header">
                <h4 class="modal-title">Búsqueda de Grupo para eliminar</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table id="grupos" class="display" style="width: 100%">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre del Grupo</th>
                            <th>Dirección</th>
                            <th>Ciclo</th>
                            <th>Estado</th>
                            <th>Eliminar</th>
                            <th>Cambiar</th>
                        </tr>
                    </thead>
                    <tbody id="categoria_tb">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <!-- <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button> -->
            </div>
        </div>
    </div>
</div>

<script>
function loadconfig(status1, status2) {
    $('#grupos').DataTable({
        "aProcessing": true,
        "aServerSide": true,
        "ordering": true,
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
                'condi2': "credgrupo_for_delete",
                status1,
                status2
            },
            dataType: "json",
            complete: function(data) {
                loaderefect(0);
                // console.log(data)
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

// destruir el modal al cerrar
$('#buscargrupo_for_delete').on('hidden.bs.modal', function (e) {
    $('#categoria_tb').empty(); 
});


// CARGA DE DATOS EN TABLA
// $(document).ready(function() {
//     loadconfig("all", "all");
// });
</script>
