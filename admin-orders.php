<?php

Use \Hcode\PageAdmin;
Use \Hcode\Model\User;
Use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;

//Status do pedido
$app->get('/admin/orders/:idorder/status', function($idorder) {

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$page = new PageAdmin();

	$page->setTpl('order-status', [
		'order'=>$order->getValues(),
		'status'=>OrderStatus::listAll(),
		'msgSuccess'=>Order::getSuccess(),
		'msgError'=>Order::getError()
	]);
});

//Salvar a edição de um Pedido
$app->post('/admin/orders/:idorder/status', function($idorder) {

	User::verifyLogin();

	if (!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0){
		Order::setError("Informe o status atual");
		header("Location: /admin/orders/".$idorder."/status");
		exit;
	}

	$order = new Order();

	$order->get((int)$idorder);

	$order->setidstatus((int)$_POST['idstatus']);

	$order->save();

	Order::setSuccess("Status atualizado");
	header("Location: /admin/orders/".$idorder."/status");
	exit;

});

//Excluir um pedido
$app->get('/admin/orders/:irorder/delete', function($idorder) {

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$order->delete();

	header("Location: /admin/orders");
	exit;

});

//Ver detalhes de um pedido
$app->get('/admin/orders/:idorder', function($idorder) {

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$cart = $order->getCart();

	$page = new PageAdmin();

	$page->setTpl('order', [
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
	]);
});

//Rota pra acessar os pedidos
$app->get('/admin/orders', function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("orders", [
		"orders"=>Order::listAll()
	]);

});


?>