<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

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



?>