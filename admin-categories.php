<?php

use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

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

//Rota para categorias menu da tela do usuário
$app->get('/categories/:idcategory', function($idcategory) {

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>[]
	]);

});


?>