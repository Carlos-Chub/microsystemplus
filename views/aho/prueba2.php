<?php
$file_path =  __DIR__ . '/../../../' . 'imgcoope.microsystemplus.com/otrosingresos/demo/38/38.pdf';
$path_parts = pathinfo($file_path);
$extension = $path_parts['extension'];
$image = ["jpg", "jpeg", "pjpeg", "png", "gif"];
$archivos = ["pdf"];

$key = in_array($extension, $image);
$compdata = ($key) ? "image" : "";
if (!$key) {
      $key = in_array($extension, $archivos);
      $compdata = ($key) ? "application" : "";
}

ob_start();
readfile($file_path);
$pdfData = ob_get_contents();
ob_end_clean();

$opResult = array(
      'status' => 1,
      'mensaje' => 'Impresion de encabezado generado correctamente',
      'namefile' => "download",
      'tipo' => $extension,
      'data' => "data:$compdata/$extension;base64," . base64_encode($pdfData)
);
echo json_encode($opResult);
