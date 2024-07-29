<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('Config.php');
require_once('Mysql.php');
include('BlingApi.php');
include('BlingModel.php');

$bling = new Bling();


#pega produto por código
#$bling->getProductByCode('14-1310');


#pega lista de produtos por página (100)
$bling->listProducts();

#pega numero de páginas para paginacao
#$bling->getNumbOfPages();
#Bling::refreshToken();

#$bling->api();
#$bling->requestApi();


?>

