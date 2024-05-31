<?php
function infoEntidad($codUsu, $conexion)
{
    $consulta = mysqli_query($conexion, "SELECT cope.nomb_cor AS nomAge, cope.log_img AS imagenEnti
        FROM tb_agencia AS agen	
        INNER JOIN tb_usuario AS usu ON agen.id_agencia = usu.id_agencia
        INNER JOIN clhpzzvb_bd_general_coopera.info_coperativa AS cope ON agen.id_institucion = cope.id_cop
        WHERE   usu.id_usu =" . $codUsu);

    if (mysqli_error($conexion)) return;

    $infoEnti = mysqli_fetch_assoc($consulta);
    return $infoEnti;
}
//FUNCION PARA CONSULTAR SI HAY UN CIERRE PENDIENTE
function verificar_apertura_cierre($idusuario, $conexion, $bandera = 0, $fechainicio = "0000-00-00", $fechafin = "0000-00-00", $fechavalue = "0000-00-00")
{
    try {
        $resultado = ["0", "No se encontro info de la institucion, verifique", "No se encontro el rol de usuario","Realice una apertura de caja para iniciar sus labores", "Realice el cierre de caja pendiente para iniciar sus labores", "No se puede realizar esta acción porque ya se ha vencido el plazo para realizarlo", "1","1","1"];
        $stmtaux = $conexion->prepare("SELECT comprobar_cierre_caja(?,?,?,?,?,?) AS cierre");
        if (!$stmtaux) {
            throw new Exception("Error en la consulta de comprobar cierre" . $conexion->error);
        }
        $aux = date('Y-m-d');
        $aux2 = $idusuario;
        $stmtaux->bind_param("ssisss", $aux2, $aux, $bandera, $fechainicio, $fechafin, $fechavalue);
        if (!$stmtaux->execute()) {
            throw new Exception("Error al consultar comprobar cierre" . $stmtaux->error);
        }
        $result = $stmtaux->get_result();
        $rowdatos = $result->fetch_assoc();
        return [$rowdatos['cierre'], $resultado[$rowdatos['cierre']]];
    } catch (Exception $e) {
        //Captura el error
        $mensaje_error = $e->getMessage();
        return [0, $mensaje_error];
        $conexion->close();
    } finally {
        if ($stmtaux !== false) {
            $stmtaux->close();
        }
    }
}
