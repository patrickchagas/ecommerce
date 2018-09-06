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
$app->get("/checkout", function(){

	User::verifyLogin(false);

	$address = new Address();

	$cart = Cart::getFromSession();

	if (isset($_GET['zipcode'])) {

 		$_GET['zipcode'] = $cart->getdeszipcode();

 	}

	if (isset($_GET['zipcode'])) {

 		$address->loadFromCEP($_GET['zipcode']);

 		$cart->setdeszipcode($_GET['zipcode']);

 		$cart->save();

 		$cart->getCalculateTotal();
 	}

	if (!$address->getdesaddress()) $address->setdesaddress('');
	if (!$address->getdescomplement()) $address->setdescomplement('');
	if (!$address->getdesdistrict()) $address->setdesdistrict('');
	if (!$address->getdescity()) $address->setdescity('');
	if (!$address->getdesstate()) $address->setdesstate('');
	if (!$address->getdescountry()) $address->setdescountry('');
	if (!$address->getdeszipcode()) $address->setdeszipcode('');
	if (!$address->getdesnumber()) $address->setdesnumber('');

	$page = new Page();

	$page->setTpl("checkout", [

		'cart'=>$cart->getValues(),
		'address'=>$address->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Address::getMsgError()

	]);

});

//Enviar os campos digitados no checkout
$app->post('/checkout', function() {

	User::verifyLogin(false);
	
	if (!isset($_POST['zipcode']) || $_POST['zipcode'] === '') {
		Address::setMsgError("Informe o CEP.");
		header('Location: /checkout');
		exit;
	}
	if (!isset($_POST['desaddress']) || $_POST['desaddress'] === '') {
		Address::setMsgError("Informe o endereço.");
		header('Location: /checkout');
		exit;
	}

	if (!isset($_POST['desnumber']) || $_POST['desnumber'] === '') {
		Address::setMsgError("Informe o número.");
		header('Location: /checkout');
		exit;
	}

	if (!isset($_POST['desdistrict']) || $_POST['desdistrict'] === '') {
		Address::setMsgError("Informe o bairro.");
		header('Location: /checkout');
		exit;
	}
	if (!isset($_POST['descity']) || $_POST['descity'] === '') {
		Address::setMsgError("Informe a cidade.");
		header('Location: /checkout');
		exit;
	}
	if (!isset($_POST['desstate']) || $_POST['desstate'] === '') {
		Address::setMsgError("Informe o estado.");
		header('Location: /checkout');
		exit;
	}
	if (!isset($_POST['descountry']) || $_POST['descountry'] === '') {
		Address::setMsgError("Informe o país.");
		header('Location: /checkout');
		exit;
	}

	$user = User::getFromSession();

	$address = new Address();

	$_POST['deszipcode'] = $_POST['zipcode'];
	$_POST['idperson'] = $user->getidperson();

	$address->setData($_POST);

	$address->save();

	header("Location: /order");
	exit;

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

//Rota para esqueceu a senha
$app->get('/forgot', function() {

	$page = new Page();
	$page->setTpl("forgot");

});

//Rota pra pegar o email que o usuario mandou no formulário
$app->post('/forgot', function() {

	$user = User::getForgot($_POST["email"], false);

	header("Location: /forgot/sent");
	exit;

});

//Rota pra mostrar que o email foi enviado
$app->get('/forgot/sent', function() {

	$page = new Page();
	$page->setTpl("forgot-sent");

});

$app->get('/forgot/reset', function() {

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page();
	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

//Rota pra enviar a nova senha do usuário

$app->post('/forgot/reset', function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);

	//Metodo que vai falar pro banco de dados, que esse processo de recuperação já foi usado, pra ele não recuperar de novo mesmo que esteja ainda dentro dessa 1 hora
	User::setForgotUsed($forgot["idrecovery"]);

	$user =  new User();

	$user->get((int)$forgot["iduser"]);

	//Criptografar a senha
	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setPassword($password);

	$page = new Page();
	$page->setTpl("forgot-reset-success");

});

//Profile
$app->get('/profile', function() {

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile", [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()
	]);

});

//Profile - Edição de um usuário
$app->post('/profile' , function() {

	User::verifyLogin(false);

	if (!isset($_POST['desperson']) || $_POST['desperson'] === ''){
		
		User::setError("Preencha o seu nome!");

		header("Location: /profile");
		exit;

	}

	if (!isset($_POST['desemail']) || $_POST['desemail'] === ''){
		User::setError("Preencha o seu e-mail!");
		header("Location: /profile");
		exit;
	}

	$user = User::getFromSession();

	if ($_POST['desemail'] !== $user->getdesemail()) {

		if (User::checkLoginExist($_POST['desemail']) === true){

			User::setError("Esse e-mail já está cadastrado.");
			header("Location: /profile");
			exit;

		}

	}
	$_POST['iduser'] = $user->getiduser();
	$_POST['inadmin'] = $user->getinadmin();
	$_POST['despassword'] = $user->getdespassword();
	$_POST['deslogin'] = $_POST['desemail'];

	$user->setData($_POST);

	$user->save();

	$_SESSION[User::SESSION] = $user->getValues();

	User::setSuccess("Dados alterados com sucesso!");

	header('Location: /profile');
	exit;

});




?>