<?php

use \Hcode\Page;

//Rota da Pagina Inicial
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});


?>