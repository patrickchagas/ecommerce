<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

//Rota da pagina de produtos do admin
$app->get('/admin/products', function () {

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "" ;
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if($search != ''){

		$pagination = Product::getPageSearch($search, $page);

	} else {

		$pagination = Product::getPage($page);

	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++) { 
		array_push($pages, [
			'href'=>'/admin/products?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);
	}

	$products = Product::listAll();

	$page = new PageAdmin();

	$page->setTpl("products", array(
		"products"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	));

});

//Rota da pagina de cadastrar produtos do admin
$app->get('/admin/products/create', function () {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("products-create");

});

//Rota pra enviar o que foi digitado no formulário de cadastrar(METODO POST)
$app->post('/admin/products/create', function () {

	User::verifyLogin();

	$product = new Product();

	//setar o que tiver vindo do POST
	$product->setData($_POST);

	$product->save();

	header("Location: /admin/products");
	exit;

});

//Rota para editar produto // Mostra os dados do produto
$app->get('/admin/products/:idproduct', function ($idproduct) {

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$page = new PageAdmin();

	$page->setTpl("products-update", array(
		// passar os dados do produto para o template
		'product'=>$product->getValues()
	));

});

//Rota para editar produto //Envia o que foi editado
$app->post('/admin/products/:idproduct', function ($idproduct) {

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$product->setData($_POST);

	$product->save();

	$product->setPhoto($_FILES["file"]);

	header("Location: /admin/products");
	exit;

});


//Rota pra deletar produto
$app->get('/admin/products/:idproduct/delete', function ($idproduct) {

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$product->delete();

	header("Location: /admin/products");
	exit;

});





?>