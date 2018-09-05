<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;

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

//Rota pra acessar o carrinho de compras
$app->get('/cart', function() {

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl('cart', [
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);

});

//Rota pra adicionar um produto ao carrinho
$app->get('/cart/:idproduct/add' , function($idproduct) {

	$product = new Product();

	$product->get((int)$idproduct);	

	$cart = Cart::getFromSession();

	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	for ($i=0; $i < $qtd; $i++) { 

		$cart->addProduct($product);
	}

	header("Location: /cart");
	exit;

});

//Rota pra remover APENAS UM produto do carrinho
$app->get('/cart/:idproduct/minus' , function($idproduct) {

	$product = new Product();

	$product->get((int)$idproduct);	

	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;

});


//Rota pra remover TODOS produtos do carrinho
$app->get('/cart/:idproduct/remove' , function($idproduct) {

	$product = new Product();

	$product->get((int)$idproduct);	

	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);

	header("Location: /cart");
	exit;

});

//Rota pra enviar o CEP pra calcular o frete
$app->post('/cart/freight', function() {

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	header("Location: /cart");
	exit;

});

//Checkout
$app->get('/checkout', function () {

	User::verifyLogin(false);

	$cart = Cart::getFromSession();

	$address = new Address();

	$page = new Page();

	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues()
	]);

});

//Login do site
$app->get('/login', function () {

	$page = new Page();

	$page->setTpl("login", [
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
		'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']
	]);

});

//Pegar os dados digitados nos campos de login
$app->post('/login', function() {

	try{

		User::login($_POST['login'], $_POST['password']);

	} catch(Exception $e) {

		User::setError($e->getMessage());
	}	

	header("Location: /checkout");
	exit;

});

//Deslogar do site
$app->get('/logout', function() {

	User::logout();

	header("Location: /login");
	exit;

});

//Enviar os dados digitados no campo de cadastro
$app->post('/register', function() {

	//não perder o que foi digitado no campo
	$_SESSION['registerValues'] = $_POST;

	if (!isset($_POST['name']) || $_POST['name'] == '') {

		User::setErrorRegister("Preencha o seu nome.");
		header("Location: /login");
		exit;
	}

	if (!isset($_POST['email']) || $_POST['email'] == '') {

		User::setErrorRegister("Preencha o seu email.");
		header("Location: /login");
		exit;
	}

	if (!isset($_POST['password']) || $_POST['password'] == '') {

		User::setErrorRegister("Preencha a senha.");
		header("Location: /login");
		exit;
	}

	if (User::checkLoginExist($_POST['email']) === true) {

		User::setErrorRegister("Este e-mail já está sendo usado por outro usuário");
		header("Location: /login");
		exit;

	}

	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone']
	]);

	$user->save();

	User::login($_POST['email'], $_POST['password']);

	header('Location: /checkout');
	exit;

});



?>