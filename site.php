<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;

//Rota da Pagina Inicial
$app->get('/', function() {

	// listar os produtos que estão no banco
	$products = Product::listAll();
    
	$page = new Page();

	$page->setTpl("index", array(
		'products'=>Product::checkList($products)
	));

});

//Rota para categorias menu da tela do usuário
$app->get('/categories/:idcategory', function($idcategory) {

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>Product::checkList($category->getProducts())
	]);
});


?>