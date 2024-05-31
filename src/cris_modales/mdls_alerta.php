<!-- Modal -->
<div class="modal fade" id="myModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Alertas</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <!-- ini tabal -->
                <div class="table-responsive">
                    <table id="tabla_plan_pagos" class="table table-striped table-hover" style="width: 100% !important; font-size: 0.9rem !important;">
                        <thead>
                            <tr>
                                <th scope="col">Alerta </th>
                                <th scope="col">Cod. cliente </th>
                                <th scope="col">Cliente </th>
                                <th scope="col">Cuenta </th>
                                <th scope="col">Mensaje </th>
                                <th scope="col">Fecha </th>
                                <th scope="col">Acción </th>
                            </tr>
                        </thead>
                        <!-- ini info -->
                        <tbody id="tbAlerta">
                            <!-- INI de la información -->
                        </tbody>
                    </table>

                    <!-- fin info -->
                    </table>
                </div>
                <!-- fin tabal -->

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>