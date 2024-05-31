<?php
session_start();
include '../../../includes/BD_con/db_con.php';
mysqli_set_charset($conexion, 'utf8');
include '../../../src/funcphp/func_gen.php';
require '../../../fpdf/fpdf.php';

if (!isset($_SESSION['id_agencia'])) {
    echo json_encode(['status' => 0, 'mensaje' => 'Sesion expirada, vuelve a iniciar sesion e intente nuevamente']);
    return;
}

$queryins = mysqli_query($conexion, "SELECT * FROM clhpzzvb_bd_general_coopera.info_coperativa ins
INNER JOIN tb_agencia ag ON ag.id_institucion=ins.id_cop where ag.id_agencia=" . $_SESSION['id_agencia']);
$info[] = [];
$j = 0;
while ($fil = mysqli_fetch_array($queryins)) {
    $info[$j] = $fil;
    $j++;
}
if ($j == 0) {
    echo json_encode(['status' => 0, 'mensaje' => 'Institucion asignada a la agencia no encontrada']);
    return;
}

$oficina = utf8_decode($info[0]["nom_agencia"]);
$institucion = utf8_decode($info[0]["nomb_comple"]);
$direccionins = utf8_decode($info[0]["muni_lug"]);
$emailins = $info[0]["emai"];
$telefonosins = $info[0]["tel_1"] . '   ' . $info[0]["tel_2"];;
$nitins = $info[0]["nit"];
$rutalogomicro = "../../../includes/img/logomicro.png";
$rutalogoins = "../../.." . $info[0]["log_img"];

/* $oficina = "Coban";
$institucion = "Cooperativa Integral De Ahorro y credito Imperial";
$direccionins = "Canton vipila zona 1";
$emailins = "fape@gmail.com";
$telefonosins = "502 43987876";
$nitins = "1323244234"; */
$usuario = $_SESSION['id'];

/* $rutalogomicro = "../../../includes/img/logomicro.png";
$rutalogoins = "../../../includes/img/fape.jpeg"; */

//$codigo = $_GET["id"];
$datos = $_POST["datosval"];
$inputs = $datos[0];
$archivo = $datos[3];

$codigo = $archivo[0];

$sql = mysqli_query($conexion, "SELECT cl.*, 
(IFNULL((SELECT pais.Pais  FROM clhpzzvb_bd_general_coopera.tb_pais pais WHERE cl.pais_nacio=pais.Abreviatura LIMIT 1),'--')) AS pais_nacio1,
(IFNULL((SELECT pais2.Pais  FROM clhpzzvb_bd_general_coopera.tb_pais pais2 WHERE cl.nacionalidad=pais2.Abreviatura LIMIT 1),'--')) AS nacionalidad1,
(IFNULL((SELECT pais3.Pais  FROM clhpzzvb_bd_general_coopera.tb_pais pais3 WHERE cl.otra_nacion=pais3.Abreviatura LIMIT 1),'--')) AS nacionalidad2,
(IFNULL((SELECT ng1.Negocio  FROM clhpzzvb_bd_general_coopera.tb_negocio ng1 WHERE cl.vivienda_Condi=ng1.id_Negocio LIMIT 1),'--')) AS vivienda2
FROM tb_cliente cl 
WHERE cl.idcod_cliente = '" . $codigo . "'");
$infocliente[] = [];
$j = 0;
while ($row = mysqli_fetch_array($sql)) {
    $infocliente[$j] = $row;
    $j++;
}
if ($j == 0) {
    echo json_encode(['status' => 0, 'mensaje' => 'Cliente no encontrado']);
    return;
}

$consultaIngresos = mysqli_query($conexion, "SELECT * FROM tb_ingresos WHERE id_cliente = '" . $codigo . "'");

//$row = mysqli_fetch_array($sql);


$nombre = $infocliente[0]['short_name'];

$rutaFoto = $infocliente[0]['url_img'];

// if ($rutaFoto == 'url' || $rutaFoto == '') {
//     $rutaFoto = '../../../includes/img/fotoClienteDefault.png';
// } else {
$rutaFoto = __DIR__ .  "/../../../../" . $rutaFoto;
if (!is_file($rutaFoto)) {
    $rutaFoto = '../../../includes/img/fotoClienteDefault.png';
}
$extension = pathinfo($rutaFoto, PATHINFO_EXTENSION);
// }

//CARGADO DE LA IMAGEN
// $imgurl = __DIR__ . '/../../../../../' . $infocliente[0]['url_img']; //esto no funciona
/* $imgurl = __DIR__ . '/../../../../' . $infocliente[0]['url_img']; //esto no funciona
$path = $infocliente[0]['url_img'];
$extension = pathinfo($path, PATHINFO_EXTENSION);
if (!is_file($imgurl)) {
    $rutaFoto = '../../../includes/img/fotoClienteDefault.png'; //funciona esto
} else {
    $imginfo   = getimagesize($imgurl);
    $mimetype  = $imginfo['mime'];
    $imageData = base64_encode(file_get_contents($imgurl));
    $rutaFoto = 'data:' . $mimetype . ';base64,' . $imageData;
    $rutaFoto =  $imgurl;
} */

$origen = $infocliente[0]['origen'];
$fecha = $infocliente[0]['date_birth'];
$fechaNacimiento = date("d-m-Y", strtotime($fecha)); //formatear fecha en dia/mes/año
$paisNac = $infocliente[0]['pais_nacio1'];
$deparNac = $infocliente[0]['depa_nacio'];
$muniNac = $infocliente[0]['muni_nacio'];
$genero = $infocliente[0]['genero'];
//genero del cliente
if ($genero == 'M') {
    $genero = 'MASCULINO';
} elseif ($genero == 'F') {
    $genero = 'FEMENINO';
} elseif ($genero == 'X') {
    $genero = 'NO DEFINIDO';
}

$estado_civil = $infocliente[0]['estado_civil'];
$profesion = $infocliente[0]['profesion'];

$tipoDocumento = $infocliente[0]['type_doc'];
$identificacion = $infocliente[0]['no_identifica'];
$paisExtiende = $infocliente[0]['pais_extiende'];
$depaExtiende = $infocliente[0]['depa_extiende'];
$muniExtiende = $infocliente[0]['muni_extiende'];
$nacionalidad = $infocliente[0]['nacionalidad1'];
$otraNacionalidad = $infocliente[0]['nacionalidad2'];
$direccion = $infocliente[0]['Direccion'];

$noNit = $infocliente[0]['no_tributaria'];
$email = $infocliente[0]['email'];
$iggs = $infocliente[0]['no_igss'];
$tel1 = $infocliente[0]['tel_no1'];
$tel2 = $infocliente[0]['tel_no2'];
$condicionVivienda = $infocliente[0]['vivienda2'];
$anioReside = $infocliente[0]['ano_reside'];
$conyuge = $infocliente[0]['Conyuge'];

$Nombrereferencia1 = $infocliente[0]['Nomb_Ref1'];
$Nombrereferencia2 = $infocliente[0]['Nomb_Ref2'];
$Nombrereferencia3 = $infocliente[0]['Nomb_Ref3'];
$telReferencia1 = $infocliente[0]['Tel_Ref1'];
$telReferencia2 = $infocliente[0]['Tel_Ref2'];
$telReferencia3 = $infocliente[0]['Tel_Ref3'];
$hijos = $infocliente[0]['hijos'];
$dependen = $infocliente[0]['dependencia'];
$telconyuge = ($infocliente[0]['telconyuge'] == " " || $infocliente[0]['telconyuge'] == NULL) ? ''.$infocliente[0]['telconyuge'] : " ";
$zona = ($infocliente[0]['zona'] == " " || $infocliente[0]['zona'] == NULL) ? ''.$infocliente[0]['zona'] : " ";
$barrio = ($infocliente[0]['barrio'] == " " || $infocliente[0]['barrio'] == NULL) ? ''.$infocliente[0]['barrio'] : " ";

$actuaPropio = $infocliente[0]['actu_Propio'];
$calidadActua = $infocliente[0]['repre_calidad'];

$propositoRelacion = $infocliente[0]['Rel_insti'];


class PDF extends FPDF
{
    public $institucion;
    public function __construct($institucion, $pathlogo, $pathlogoins, $oficina, $dire, $email, $tel, $nit, $user)
    {
        parent::__construct();
        $this->institucion = $institucion;
        $this->pathlogo = $pathlogo;
        $this->pathlogoins = $pathlogoins;
        $this->oficina = $oficina;
        $this->direccion = $dire;
        $this->email = $email;
        $this->telefonos = $tel;
        $this->nit = $nit;
        $this->user = $user;
    }

    // Cabecera de página
    function Header()
    {
        $hoy = date("Y-m-d H:i:s");
        // Logo 
        $this->Image($this->pathlogoins, 10, 8, 33);


        $this->SetFont('Arial', '', 8);
        // Movernos a la derecha
        //$this->Cell(80);

        // Título
        $this->Cell(0, 3, $this->institucion, 0, 1, 'C');
        $this->Cell(0, 3, $this->direccion, 0, 1, 'C');
        $this->Cell(0, 3, 'Email: ' . $this->email, 0, 1, 'C');
        $this->Cell(0, 3, 'Tel: ' . $this->telefonos, 0, 1, 'C');
        $this->Cell(0, 3, 'NIT: ' . $this->nit, 'B', 1, 'C');

        $this->SetFont('Arial', '', 7);
        $this->SetXY(-30, 5);
        $this->Cell(10, 2, $hoy, 0, 1, 'L');
        $this->SetXY(-25, 8);
        $this->Cell(10, 2, $this->user, 0, 1, 'L');

        // Salto de línea
        $this->Ln(15);
    }

    // Pie de página
    function Footer()
    {

        // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // Logo 
        $this->Image($this->pathlogo, 165, 275, 20);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

/* echo json_encode(['status' => 0, 'mensaje' => 'Cliente no encontrado']);
return; */
$fuente = "Arial";

$tamanioFuente = 9;
$tamanioTitulo = 11;
$tamanio_linea = 4; //altura de la linea/celda
$ancho_linea = 40; //anchura de la linea/celda
$espacio_blanco = 10; //tamaño del espacio en blanco entre cada celda
$ancho_linea2 = 35; //anchura de la linea/celda
$espacio_blanco2 = 4; //tamaño del espacio en blanco entre cada celda
// Creación del objeto de la clase heredada
$pdf = new PDF($institucion, $rutalogomicro, $rutalogoins, $oficina, $direccionins, $emailins, $telefonosins, $nitins, $usuario);
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->Rect(9, 62, 148, 31, 'D'); //CUADRO 1 DATOS GENERALES
$pdf->Rect(160, 62, 31, 31, 'D'); //CUADRO foto
$pdf->Rect(9, 94, 192, 33, 'D'); //CUADRO 2 DATOS GENERALES
$pdf->Rect(9, 128, 192, 33, 'D'); //CUADRO 3 DATOS GENERALES
// $pdf->Rect(9, 164, 192, 18, 'D'); //CUADRO 1 REFERENCIAS
$pdf->SetY(40);

$pdf->SetFont($fuente, 'B', $tamanioTitulo);
$pdf->Cell(0, 10, 'Codigo Cliente:  ' . $codigo, 0, 1);

$pdf->SetFillColor(204, 229, 255);
$pdf->Cell(0, 5, 'DATOS GENERALES', 0, 1, 'C', true);

$pdf->SetFont($fuente, '', $tamanioFuente);
$pdf->SetDrawColor(225, 226, 226);
$pdf->Image($rutaFoto, 161, 63, 0, 29, $extension);
// mb_strtoupper('sábado','utf-8');
$pdf->Cell(0, 6, 'Nombre Completo:  ' . utf8_decode(mb_strtoupper($nombre, 'utf-8')), 'B', 1, 'C'); //Nombre cliente
$pdf->Ln(2);
$pdf->Cell($ancho_linea, $tamanio_linea, 'Fecha de nacimiento', 0, 0, 'C'); //fecha nacimiento titulo
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, 'Sexo ', 0, 0, 'C'); //Sexo titulo
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, 'Estado civil', 0, 1, 'C'); //Estado civiio titulo


$pdf->SetfillColor(230, 235, 236);
$pdf->Cell($ancho_linea, $tamanio_linea, $fechaNacimiento, 0, 0, 'C', true); //fecha nacimiento  DATO
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, $genero, 0, 0, 'C', true); //sexo  DATO
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, $estado_civil, 0, 1, 'C', true); //estado civil  DATO


$pdf->Ln(2);
$pdf->Cell($ancho_linea, $tamanio_linea, 'Pais de nacimiento', 0, 0, 'C'); //pais nacimiento titulo
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, 'Departamento de nacimiento', 0, 0, 'C'); //departament nacimiento titulo
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, 'Municipio de nacimiento', 0, 1, 'C'); //muni nacimiento titulo




$pdf->Cell($ancho_linea, $tamanio_linea, $paisNac, 0, 0, 'C', true); //pais nacimiento  DATO
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, utf8_decode(departamento($deparNac)), 0, 0, 'C', true); //departamento nacimiento  DATO
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, utf8_decode(municipio($muniNac)), 0, 1, 'C', true); //municipio nacimiento dato

$pdf->Ln(2);

$pdf->Cell($ancho_linea, $tamanio_linea, 'Profesion u Oficio', 0, 1, 'C'); //Profesion titulo

$pdf->Cell($ancho_linea, $tamanio_linea, utf8_decode($profesion), 0, 1, 'C', true); //profesion  DATO

$pdf->Ln(5);

$pdf->Cell($ancho_linea, $tamanio_linea, 'Condicion Migratoria', 0, 0, 'C'); //condicion migratoria titulo
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, 'Nacionalidad', 0, 0, 'C'); //Nacionalidad titulo
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, 'Otra nacionalidad', 0, 1, 'C'); //otra nacionalidad titulo

$pdf->Cell($ancho_linea, $tamanio_linea, utf8_decode($origen), 0, 0, 'C', true); //
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, utf8_decode($nacionalidad), 0, 0, 'C', true); //Nacionalidad dato
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, utf8_decode($otraNacionalidad), 0, 1, 'C', true); //otra nacionalidad DATO

$pdf->Ln(4);

$pdf->Cell($ancho_linea2, $tamanio_linea, 'Doc de Identificacion ', 0, 0, 'C'); //Doc De identificacio titulo
$pdf->Cell($espacio_blanco2, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea2, $tamanio_linea, 'Numero de ' . $tipoDocumento, 0, 0, 'C'); //numero de identificacion titulo
$pdf->Cell($espacio_blanco2, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea2, $tamanio_linea, 'Extendido', 0, 0, 'C'); //pais extendido titulo
$pdf->Cell($espacio_blanco2, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea2, $tamanio_linea, 'Departamento', 0, 0, 'C'); //Departamento extendido titulo
$pdf->Cell($espacio_blanco2, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea2, $tamanio_linea, 'Municipio', 0, 1, 'C'); //Departamento extendido titulo

$pdf->Cell($ancho_linea2, $tamanio_linea, $tipoDocumento, 0, 0, 'C', true); //doc  DATO
$pdf->Cell($espacio_blanco2, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea2, $tamanio_linea, $identificacion, 0, 0, 'C', true); //numero doc DATO
$pdf->Cell($espacio_blanco2, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea2, $tamanio_linea, $paisExtiende, 0, 0, 'C', true); //pais extendido0 DATO
$pdf->Cell($espacio_blanco2, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea2, $tamanio_linea, utf8_decode(departamento($deparNac)), 0, 0, 'C', true); //departamento extendido DATO
$pdf->Cell($espacio_blanco2, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea2, $tamanio_linea, utf8_decode(municipio($muniNac)), 0, 1, 'C', true); //departamento extendido DATO

$pdf->Ln(2);

$pdf->Cell($ancho_linea, $tamanio_linea, 'No de NIT', 0, 0, 'C'); //nit titulo
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea * 2, $tamanio_linea, 'Email', 0, 0, 'C'); //email titulo
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, 'Afiliacion IGGS', 0, 1, 'C'); //iggs  titulo

$pdf->Cell($ancho_linea, $tamanio_linea, $noNit, 0, 0, 'C', true); //nit DATO
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea * 2, $tamanio_linea, $email, 0, 0, 'C', true); //email  DATO
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, $iggs, 0, 1, 'C', true); //iggs DATO

$pdf->Ln(5);

$pdf->Cell($ancho_linea * 2, $tamanio_linea, 'Direccion', 0, 0, 'C'); //direccion titulo
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, 'Telefono 1', 0, 0, 'C'); //tel1 titulo
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, 'Telefono 2', 0, 1, 'C'); //tel2 titulo

$pdf->Cell($ancho_linea * 2, $tamanio_linea, utf8_decode($direccion), 0, 0, 'C', true); //direccion DATO
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, $tel1, 0, 0, 'C', true); //tel1 DATO
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, $tel2, 0, 1, 'C', true); //tel2 DATO

$pdf->Ln(2);

$pdf->Cell($ancho_linea, $tamanio_linea, 'Condicion de la vivienda', 0, 0, 'C'); //condicion vivienda titulo
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, utf8_decode('Año Vivienda'), 0, 0, 'C'); //año vivienda titulo
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea * 2, $tamanio_linea, 'Nombre de Conyuge', 0, 1, 'C'); //nombre conyuge titulo



$pdf->Cell($ancho_linea, $tamanio_linea, $condicionVivienda, 0, 0, 'C', true); //condicion vivienda DATO
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, $anioReside, 0, 0, 'C', true); //año vivienda DATO
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea * 2, $tamanio_linea, $conyuge, 0, 1, 'C', true); //nombre conyuge dato
$pdf->Ln(2);

$pdf->Cell($ancho_linea, $tamanio_linea, 'Zona', 0, 0, 'C'); //condicion vivienda titulo
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, 'Barrio', 0, 0, 'C'); //año vivienda titulo
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea * 2, $tamanio_linea, 'Tel. de Conyuge', 0, 1, 'C'); //nombre conyuge titulo

$pdf->Cell($ancho_linea, $tamanio_linea, $zona, 0, 0, 'C', true); //condicion vivienda DATO
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea, $tamanio_linea, $barrio, 0, 0, 'C', true); //año vivienda DATO
$pdf->Cell($espacio_blanco, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea * 2, $tamanio_linea, $telconyuge, 0, 1, 'C', true); //nombre conyuge dato

$pdf->Ln(4);

$pdf->SetFillColor(204, 229, 255);
$pdf->Cell(0, 5, 'REFERENCIAS', 0, 1, 'C', true);

$pdf->Ln(4);

$pdf->Cell($ancho_linea2 * 1.7, $tamanio_linea, 'Referencia 1', 0, 0, 'C'); //referencias titulo
$pdf->Cell($espacio_blanco2, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea2 * 1.7, $tamanio_linea, 'Referencia 2', 0, 0, 'C'); //referencias titulo
$pdf->Cell($espacio_blanco2, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea2 * 1.7, $tamanio_linea, 'Referencia 3', 0, 1, 'C'); //referencias titulo

$pdf->SetfillColor(230, 235, 236);
$pdf->Cell($ancho_linea2 * 1.7, $tamanio_linea, utf8_decode($Nombrereferencia1), 0, 0, 'C', true); //referencia nombre DATO
$pdf->Cell($espacio_blanco2, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea2 * 1.7, $tamanio_linea, utf8_decode($Nombrereferencia2), 0, 0, 'C', true);  //referencia nombre  DATO
$pdf->Cell($espacio_blanco2, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea2 * 1.7, $tamanio_linea, utf8_decode($Nombrereferencia3), 0, 1, 'C', true); //referencia nombre  DATO

$pdf->Cell($ancho_linea2 * 1.7, $tamanio_linea, $telReferencia1, 0, 0, 'C', true); //referencia tel DATO
$pdf->Cell($espacio_blanco2, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea2 * 1.7, $tamanio_linea, $telReferencia2, 0, 0, 'C', true);  //referencia tel DATO
$pdf->Cell($espacio_blanco2, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea2 * 1.7, $tamanio_linea, $telReferencia3, 0, 1, 'C', true); //referencia tel DATO

$pdf->Ln(6);
$pdf->SetFillColor(204, 229, 255);
$pdf->Cell(0, 5, 'ADICIONALES', 0, 1, 'C', true);

// $pdf->Ln(3);

$pdf->Cell($ancho_linea2 * 1.7, $tamanio_linea, 'No. hijos', 0, 0, 'C'); //referencias titulo
$pdf->Cell($espacio_blanco2, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea2 * 2, $tamanio_linea, utf8_decode('Personas de relación de dependencia'), 0, 1, 'C'); //referencias titulo

$pdf->SetfillColor(230, 235, 236);
$pdf->Cell($ancho_linea2 * 1.7, $tamanio_linea, $hijos, 0, 0, 'C', true); //referencia nombre DATO
$pdf->Cell($espacio_blanco2, $tamanio_linea, '', 0, 0, 'C'); //espacio
$pdf->Cell($ancho_linea2 * 2, $tamanio_linea, $dependen, 0, 0, 'C', true);  //referencia nombre  DATO


$pdf->Ln(6);

$pdf->SetFillColor(204, 229, 255);
$pdf->Cell(0, 5, 'INFORMACION ECONOMICA DEL SOLICITANTE', 0, 1, 'C', true);

$pdf->Cell($ancho_linea, $tamanio_linea, 'Proposito de relacion: ' . $propositoRelacion, 0, 1, 'C'); //direccion DATO

$pdf->Ln(3);

$pdf->SetFillColor(555, 255, 204);
$pdf->Cell(25, $tamanio_linea, 'Tipo de fuente', 1, 0, 'C', true); //
$pdf->Cell(55, $tamanio_linea, 'Nombre', 1, 0, 'C', true);
$pdf->Cell(35, $tamanio_linea, 'Puesto', 1, 0, 'C', true);
$pdf->Cell(50, $tamanio_linea, 'Direccion', 1, 0, 'C', true);
$pdf->Cell(25, $tamanio_linea, 'Monto Ingresos', 1, 1, 'C', true);

//ingresos
// $ingresos = mysqli_fetch_array($consultaIngresos);

// if (empty($ingresos)) {
// } else {
//     $tipoIngreso = $ingresos['Tipo_ingreso'];
//     $nombreEmpresa = $ingresos['nombre_empresa'];
//     $puesto = $ingresos['puesto_ocupa'];
//     $direccionEmpresa = $ingresos['direc_negocio'];
//     $montoIngreso = $ingresos['sueldo_base'];
// }
//$tipoIngreso = isset($ingresos['tipo_ingreso']) ?: 'dato';


while ($ingresos = mysqli_fetch_array($consultaIngresos, MYSQLI_ASSOC)) {
    if ($ingresos['Tipo_ingreso'] == '1') {
        $tipoIngreso = 'Independiente';
    } elseif ($ingresos['Tipo_ingreso'] == '2') {
        $tipoIngreso = 'Dependiente';
    } else {
        $tipoIngreso = 'Otros';
    }
    $nombreEmpresa = utf8_encode($ingresos['nombre_empresa']);
    $puesto = utf8_encode($ingresos['puesto_ocupa']);
    $direccionEmpresa = utf8_encode($ingresos['direc_negocio']);
    $montoIngreso = utf8_encode($ingresos['sueldo_base']);

    $pdf->Cell(25, $tamanio_linea, $tipoIngreso, 1, 0, 'C');
    $pdf->Cell(55, $tamanio_linea, $nombreEmpresa, 1, 0, 'C');
    $pdf->Cell(35, $tamanio_linea, $puesto, 1, 0, 'C');
    $pdf->Cell(50, $tamanio_linea, $direccionEmpresa, 1, 0, 'C');
    $pdf->Cell(25, $tamanio_linea, $montoIngreso, 1, 1, 'C');
}
//fin ingresos



$pdf->SetFont($fuente, '', ($tamanioFuente - 1));

$pdf->Ln(6);

$pdf->SetFillColor(204, 229, 255);
$pdf->Cell(0, 5, 'PRODUCTOS', 0, 1, 'C', true);

$pdf->Ln(3);
// $pdf->CellFit($ancho_linea2 + 13, $tamanio_linea + 1, $bd_ccodaport, 'B', 0, 'C', 0, '', 1, 0); // cuenta

$pdf->SetFillColor(555, 255, 204);
$pdf->CellFit($ancho_linea2, $tamanio_linea, ' Tipo', 1, 0, 'C', true, '', 1, 0);
$pdf->CellFit($ancho_linea2 * 2 + 5, $tamanio_linea, 'Descripcion', 1, 0, 'C', true, '', 1, 0);
$pdf->CellFit($ancho_linea, $tamanio_linea, 'Cuenta', 1, 0, 'C', true, '', 1, 0);
$pdf->CellFit($ancho_linea, $tamanio_linea, 'Monto Inicial', 1, 1, 'C', true, '', 1, 0);
// $pdf->Ln(5);
//productos
//consulta a cuentas de ahorro
$consulta1 = mysqli_query($conexion, "SELECT 'Ahorro' AS tipo, aht.nombre AS descripcion, aho.ccodaho AS cuenta, IFNULL(calcularsaldocuentaahom(aho.ccodaho),0)  AS saldo FROM ahomcta aho
INNER JOIN tb_cliente cl ON aho.ccodcli = cl.idcod_cliente
INNER JOIN ahomtip aht ON aht.ccodtip = SUBSTR(aho.ccodaho, 7, 2)
WHERE aho.estado='A' AND cl.idcod_cliente = '" . $codigo . "'");
//consulta a cuentas de aportaciones
$consulta2 = mysqli_query($conexion, "SELECT 'Aportación' AS tipo, apt.nombre AS descripcion, apr.ccodaport AS cuenta, IFNULL(calcularsaldocuentaprt(apr.ccodaport),0) AS saldo FROM aprcta apr
INNER JOIN tb_cliente cl ON apr.ccodcli = cl.idcod_cliente
INNER JOIN aprtip apt ON apt.ccodtip = apr.ccodtip
WHERE apr.estado='A' AND cl.idcod_cliente ='" . $codigo . "'");
//Consulta a cuentas de creditos 
$consulta3 = mysqli_query($conexion, "SELECT 'Crédito' AS tipo, pr.descripcion AS descripcion, cm.CCODCTA AS cuenta, cm.MonSug AS saldo FROM cremcre_meta cm
INNER JOIN cre_productos pr ON cm.CCODPRD= pr.id
WHERE cm.Cestado='F' AND cm.CodCli = '" . $codigo . "'");
//unificar el resultado de las 3 consultas
$datos1[] = [];
$datos2[] = [];
$datos3[] = [];

$bandera = false;
$bandera2 = false;
$bandera3 = false;
$i = 0;
while ($fila = mysqli_fetch_array($consulta1, MYSQLI_ASSOC)) {
    $datos1[$i] = $fila;
    $datos1[$i]['numero'] = $i + 1;
    $i++;
    $bandera = true;
}
$i = 0;
while ($fila = mysqli_fetch_array($consulta2, MYSQLI_ASSOC)) {
    $datos2[$i] = $fila;
    $datos2[$i]['numero'] = $i + 1;
    $i++;
    $bandera2 = true;
}
$i = 0;
while ($fila = mysqli_fetch_array($consulta3, MYSQLI_ASSOC)) {
    $datos3[$i] = $fila;
    $datos3[$i]['numero'] = $i + 1;
    $i++;
    $bandera3 = true;
}

$j = 0;
$k = 0;
if ($bandera) {
    foreach ($datos1 as $dato) {
        // $k++;
        // // $j=$dato['tipo'];
        // // break;
        $pdf->CellFit($ancho_linea2, $tamanio_linea, utf8_decode($dato['tipo']), 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2 * 2 + 5, $tamanio_linea, utf8_decode($dato['descripcion']), 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea, $tamanio_linea, $dato['cuenta'], 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea, $tamanio_linea, $dato['saldo'], 1, 1, 'C', '', '0', 1, 0);
    }
}

if ($bandera2) {
    foreach ($datos2 as $dato) {
        $pdf->CellFit($ancho_linea2, $tamanio_linea, utf8_decode($dato['tipo']), 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2 * 2 + 5, $tamanio_linea, utf8_decode($dato['descripcion']), 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea, $tamanio_linea, $dato['cuenta'], 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea, $tamanio_linea, $dato['saldo'], 1, 1, 'C', 0, '', 1, 0);
    }
}

if ($bandera3) {
    foreach ($datos3 as $dato) {
        $pdf->CellFit($ancho_linea2, $tamanio_linea, utf8_decode($dato['tipo']), 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea2 * 2 + 5, $tamanio_linea, utf8_decode($dato['descripcion']), 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea, $tamanio_linea, $dato['cuenta'], 1, 0, 'C', 0, '', 1, 0);
        $pdf->CellFit($ancho_linea, $tamanio_linea, $dato['saldo'], 1, 1, 'C', 0, '', 1, 0);
    }
}

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFillColor(0, 0, 0);
$pdf->SetDrawColor(0, 0, 0);
$pdf->firmas(1, [strtoupper(utf8_decode($nombre))], 'Arial');
//fin productos
//$pdf->Output();
ob_start();
$pdf->Output();
$pdfData = ob_get_contents();
ob_end_clean();

$opResult = array(
    'status' => 1,
    'mensaje' => 'Reporte generado correctamente',
    'namefile' => "Ficha de cliente",
    'tipo' => "pdf",
    'data' => "data:application/pdf;base64," . base64_encode($pdfData),
);
echo json_encode($opResult);
