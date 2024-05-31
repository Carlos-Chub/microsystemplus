

<div class="modal" id="modal_pagos_cre_individuales">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">Pago de créditos individuales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <!-- <input type="text" id="id_modal_hidden" value="" readonly hidden> -->
            </div>
            <!-- Modal body -->
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tabla_pagos_individuales" class="table table-striped table-hover" style="width: 100% !important;">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Cod. Crédito</th>
                                <th scope="col">Cod. Cliente</th>
                                <th scope="col">Nombre cliente</th>
                                <th scope="col">No. Ciclo</th>
                                <th scope="col">Dia de Pago</th>
                                <th scope="col">Capital</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- ---------------------------------TERMINA EL MODAL  -->

<script>
    $(document).ready(function() {
        $('#tabla_pagos_individuales').on('search.dt').DataTable({
            "aProcessing": true,
            "aServerSide": true,
            "ordering": false,
            "lengthMenu": [
                [10, 15, -1],
                ['10 filas', '15 filas', 'Mostrar todos']
            ],
            "ajax": {
                url: '../../src/cruds/crud_caja.php',
                type: "POST",
                beforeSend: function() {
                    loaderefect(1);
                },
                data: {
                    'condi': 'list_pagos_individuales'
                },
                dataType: "json",
                complete: function() {
                    // console.log(data)
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

        // $.ajax({
        //   url: "../../src/cruds/crud_caja.php",
        //   method: "POST",
        //   data: {
        //     'condi': 'list_pagos_individuales'
        //   },
        //   success: function(data) {
        //     console.log(data)
        //     const data2 = JSON.parse(data);
        //     console.log(data2); 
        //   }
        // })
    });
</script>