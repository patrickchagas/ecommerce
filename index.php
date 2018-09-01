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



$app->run();

 ?>