<?php
/* 
$bases_url = [
    // //'https://demo.sotecprotech.com/',               //BANA1
    // 'https://fape.sotecprotech.com/',               //BANA1
    // 'https://crediprendas.sotecprotech.com/',       //BANA1
    // 'https://coopim.sotecprotech.com/',             //BANA1
    // 'https://vidanueva.sotecprotech.com/',          //BANA1
    // 'https://nawal.microsystemplus.com/',         //AWS1
    // 'https://ciacreho.microsystemplus.com/',      //AWS1
    // //'https://sendero.microsystemplus.com/',       //AWS1
    // 'https://crediapoyemos.microsystemplus.com/', //AWS1
    // 'https://primavera.microsystemplus.com/',     //AWS1
    // 'https://adg.microsystemplus.com/',           //BANA2
    // 'https://altascumbres.microsystemplus.com/',  //BANA2
    // 'https://coditoto.microsystemplus.com/',      //BANA2
    // 'https://coopeadg.microsystemplus.com/',      //BANA2
    // 'https://coopibelen.microsystemplus.com/',      //BANA2
    // 'https://cooprode.microsystemplus.com/',      //BANA2
    // 'https://credimarq.microsystemplus.com/',     //BANA2
    // 'https://credireforma.microsystemplus.com/',  //BANA2
    // 'https://mueblex.microsystemplus.com/',       //BANA2
    // //'https://demo.microsystemplus.com/',          //BANA2
    // 'https://adif.amzmicrosystemplus.com/',       //AWS2
    // 'https://coopeadif.amzmicrosystemplus.com/',       //AWS2
    // //'https://cicre.amzmicrosystemplus.com/',      //AWS2
    // 'https://copefuente.amzmicrosystemplus.com/', //AWS2
    // 'https://corpocredit.amzmicrosystemplus.com/',//AWS2
    // 'https://credimass.amzmicrosystemplus.com/',    //AWS2
    // 'https://credisa.amzmicrosystemplus.com/',//AWS2
];
break;
return;
$files = [
    // //'calculo_mora.php',
    // //'inicio_de_mes.php'
];
$i = 0;
while ($i < count($bases_url)) {
    echo '\n INSTITUCION: ' . $bases_url[$i] . '\n';
    foreach ($files as $file) {
        $retorno = executescript($bases_url[$i] . 'src/funcphp/cron_tasks/' . $file);
        //eval($retorno);
        if ($retorno === false) {
            echo "Error al obtener la URL.\n";
        } else {
            echo "Contenido obtenido: " . $retorno . "\n";
        }
    }
    $i++;
}

function executescript($base_url)
{
    //$script_content = file_get_contents($base_url, false, $context);
    // $script_content = curl_get_file_contents($base_url);
    $c = curl_init();
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_URL, $base_url);
    $contents = curl_exec($c);
    curl_close($c);

    if ($contents) return $contents;
    else return FALSE;
} */