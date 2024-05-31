<?php
require 'serversideplus.php';
$whereextra = $_GET['whereextra'];
$table_data->get('vista_bancos', 'id', array('id', 'numcom', 'feccnt', 'debe', 'moncheque', 'estado', 'numchq'), [0, 1, 1, 1, 1, 0, 0], $whereextra);
