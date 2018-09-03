<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Category extends Model {

	//Listar todos os dados da tabela de usuário
	public static function listAll() 
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");

	}

	public function save() 
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(

			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory(),
		));

		$this->setData($results[0]);

		Category::updateFile();
	}

	public function get($idcategory) 
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [	
			":idcategory"=>$idcategory
		]);

		$this->setData($results[0]);

	}

	public function delete()
	{

		$sql = new Sql();

		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
				':idcategory'=>$this->getidcategory()
			]);

		Category::updateFile();
	}

	public static function updateFile()
	{

		$categies = Category::listAll();

		$html = [];

		foreach ($categies as $row) {
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
		}

		file_put_contents($_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));
	}

	//Metodo que traz todos os produtos
	//$related = true ->vai trazer o que os que estão relacionados com a categoria
	public function getProducts($related = true)
	{

		$sql = new Sql();

		if($related === true){

			return $sql->select("

					SELECT * FROM tb_products WHERE idproduct IN(
						SELECT a.idproduct
						FROM tb_products a
						INNER JOIN tb_productscategories b ON A.idproduct = b.idproduct
						WHERE b.idcategory = :idcategory
					);
				", [
					':idcategory'=>$this->getidcategory()
				]);
		} else {

			return $sql->select("

				SELECT * FROM tb_products WHERE idproduct NOT IN(
					SELECT a.idproduct
					FROM tb_products a
					INNER JOIN tb_productscategories b ON A.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory
				);
			", [
				':idcategory'=>$this->getidcategory()
			]);
		}
	}

	public function addProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES (:idcategory, :idproduct)",[
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
		]);
	}

	public function removeProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct",[
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
		]);
	}

}

?>