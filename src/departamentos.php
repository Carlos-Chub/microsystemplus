<?php
$departa = mysqli_query($general, "SELECT * FROM `departamentos`");

         while ($municipalidad = mysqli_fetch_array($departa, MYSQLI_ASSOC)) { 
                $nombre =utf8_encode($municipalidad["nombre"]);
                $codigo_departa= utf8_encode($municipalidad["codigo_departamento"]);                   
                echo '<option value="'.$codigo_departa.'">'.$nombre.'</option>'; 
        }
?>