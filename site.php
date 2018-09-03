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

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	$category = new Category();

	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$pages = [];

	for ($i=1; $i <= $pagination['pages']; $i++) { 
		array_push($pages, [
			'link'=>'/categories/' .$category->getidcategory(). '?page=' .$i,
			'page'=>$i			
		]);
	}

	$page = new Page();

	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>$pagination["data"],
		'page'=>$pages
	]);
});

//Detalhes do Produto
$app->get('/products/:desurl', function($desurl) {

	$product = new Product();

	$product->getFromURL($desurl);

	$page = new Page();

	$page->setTpl("product-detail", [
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
	]);


});


?>