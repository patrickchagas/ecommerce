<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model {

	const SESSION = "User";

	public static function login($login, $password)  {

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		// Se não encontrou nenhum LOGIN
		if (count($results) === 0 ) 
		{
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}
		// Dados do usuario, primeiro registro que foi encontrado
		$data = $results[0];

		//Verificar a senha do usuário
		if (password_verify($password, $data["despassword"]) === true){

			$user = new User();

			$user->setData($data);

			//Criar sessão
			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else {

			throw new \Exception("Usuário inexistente ou senha inválida.");
		}
	}

	public static function verifyLogin($inadmin = true)
	{	
		//Se a SESSSION não foi definida
		if(
			!isset($_SESSION[User::SESSION]) 
			|| 
			!$_SESSION[User::SESSION] 
			|| 
			//Verificar o Id do usuário
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
			//Verificar se o usuário também pode acessar a administração
		    ||
		    (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
			
		) {

			header("Location: /admin/login");
			exit;

		}
	}

	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;

	}


}

?>