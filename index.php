<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

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

$app->run();

 ?>