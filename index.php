<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

$app = new Slim();

$app->config('debug', true);

//Rota da Pagina Inicial
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});
//Rota para Pagina Admin
$app->get('/admin', function(){

	//Verificar se o usuário está logado
	User::verifyLogin();

	$page = new PageAdmin();
	$page->setTpl("index");

});

$app->get('/admin/login', function (){

	$page = new PageAdmin([
		//Opções, Desabilitando o Header e Footer padrão
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("login");

});

//Validar o login // Receber o POST do formulário de login
$app->post("/admin/login", function() {

	User::login($_POST["login"], $_POST["password"]);
	// Se não gerar um erro, vai ser redirecionado para pagina de Administração
	header("Location: /admin");
	exit;

});
//Logout do admin
$app->get("/admin/logout", function(){

	User::logout();

	header("Location: /admin/login");
	exit;

});

//Tela de listar todos os usuários
$app->get("/admin/users", function() {

	//Verificar se o usuário está logado
	User::verifyLogin();

	// Array com toda lista de usuário
	$users = User::listAll();

	$page = new PageAdmin();
	$page->setTpl("users", array( //Passar a lista para o template
		"users"=>$users
	));

});
//Tela para criar usuários
$app->get("/admin/users/create", function() {

	//Verificar se o usuário está logado e é admin
	User::verifyLogin();

	$page = new PageAdmin();
	$page->setTpl("users-create");

});

//Rota para excluir um usuário do sistema
$app->get("/admin/users/:iduser/delete", function($iduser) {

	//Verificar se o usuário está logado e é admin
	User::verifyLogin();

	$user = new User();
	//Carregar o usuário pra ter certeza que ele ainda existe no banco
	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;

}); 

//Tela para atualizar usuários
// :iduser -> solicitar os dados de um usuário específico
$app->get('/admin/users/:iduser', function($iduser) {

	//Verificar se o usuário está logado e é admin
	User::verifyLogin();

	//Carrega os dados do usuário
	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));

});

//Rota para inserir os dados do usuário e enviar para o banco de dados
$app->post("/admin/users/create", function() {

	//Verificar se o usuário está logado e é admin
	User::verifyLogin();

	//Criar um usuário novo
	$user = new User();

	// Se ele foi definido o valor é 1
	// Se não foi definido o valor é 0
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
	exit;

});

//Rota para salvar os dados editado do usuário
$app->post("/admin/users/:iduser", function($iduser) {

	//Verificar se o usuário está logado e é admin
	User::verifyLogin();

	$user = new User();

	// Se ele foi definido o valor é 1
	// Se não foi definido o valor é 0
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	//Carregar os dados atuais
	//Trazer tudo do banco pra depois alterar
	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");
	exit;

});

//Rota para esqueceu a senha
$app->get('/admin/forgot', function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot");

});

//Rota pra pegar o email que o usuario mandou no formulário
$app->post('/admin/forgot', function() {

	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;

});

//Rota pra mostrar que o email foi enviado
$app->get('/admin/forgot/sent', function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-sent");

});

$app->get('/admin/forgot/reset', function() {

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

//Rota pra enviar a nova senha do usuário

$app->post('/admin/forgot/reset', function(){

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

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-reset-success");

});

//Rota pra acessar a pagina categorias
$app->get('/admin/categories', function () {

	User::verifyLogin();

	$categories = Category::listAll();

	$page = new PageAdmin();
	$page->setTpl("categories", [
		'categories'=>$categories
	]);

});

//Rota do template de cadastro de categorias
$app->get('/admin/categories/create', function () {

	User::verifyLogin();

	$page = new PageAdmin();
	$page->setTpl("categories-create");

});

//Rota pra pegar o nome da categoria que foi digitada no formulário e cadastrar no banco
$app->post('/admin/categories/create', function () {

	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");
	exit;

});

//Deletar uma categoria
$app->get('/admin/categories/:idcategory/delete', function($idcategory) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header("Location: /admin/categories");
	exit;

});

//Editar uma categoria
$app->get('/admin/categories/:idcategory', function($idcategory) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-update", array(
		'category'=>$category->getValues()
	));

});

//Rota pra pegar o nome que foi editado 
$app->post('/admin/categories/:idcategory', function($idcategory) {

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");
	exit;

});


$app->run();

 ?>