<?php
?>

<button id="download" onclick="reportes([[], [], [], []], 'pdf', 'nota_desembolso', 1)">Descargar PDF</button>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function reportes(datos, tipo, file, download) {
        var url = "prueba2.php";
        $.ajax({
            url: url,
            async: true,
            type: "POST",
            dataType: "html",
            success: function(data) {
                var opResult = JSON.parse(data);
                if (opResult.status == 1) {
                    switch (download) {
                        case 0:
                            const ventana = window.open();
                            ventana.document.write(
                                "<object data='" +
                                opResult.data +
                                "' type='application/" +
                                opResult.tipo +
                                "' width='100%' height='100%'></object>"
                            );
                            break;
                        case 1:
                            var $a = $(
                                "<a href='" +
                                opResult.data +
                                "' download='" +
                                opResult.namefile +
                                "." +
                                opResult.tipo +
                                "'>"
                            );
                            $("body").append($a);
                            $a[0].click();
                            $a.remove();
                            break;
                    }
                    console.log('listoooooo');
                } else {
                    console.log('errrorrrrr');
                }
            },
        });
    }
</script>