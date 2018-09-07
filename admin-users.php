<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

//Tela de listar todos os usuários
$app->get("/admin/users", function() {

	//Verificar se o usuário está logado
	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "" ;
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if($search != ''){

		$pagination = User::getPageSearch($search, $page);

	} else {

		$pagination = User::getPage($page);

	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++) { 
		array_push($pages, [
			'href'=>'/admin/users?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);
	}

	$page = new PageAdmin();

	$page->setTpl("users", array( //Passar a lista para o template
		"users"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
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


?>