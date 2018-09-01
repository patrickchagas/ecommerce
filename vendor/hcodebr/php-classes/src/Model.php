<?php

namespace Hcode;

class Model {

	//Vão ter todos os valores dos campos que a gente tem dentro do nosso objeto 
	// No caso Objeto User(Os dados do usuario)
	private $values = [];

	public function __call($name, $args)
	{

		//Identificar se foi chamado o GET ou SET
		$method = substr($name, 0, 3);
		//Nome do Campo que foi chamado
		$fieldName= substr($name, 3, strlen($name));

		switch($method) 
		{
			case "get":
				return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : NULl ;
			break;

			case "set":
				// ARGS nesse caso, é o valor que foi passado pra esse atributo
				$this->values[$fieldName] = $args[0];
			break;
		}

	}

	//Fazer os setters de automatico de todos os campos que veio do banco de dados e não de forma estatica
	public function setData($data = array()) 
	{
		foreach ($data as $key => $value) {

			$this->{"set".$key}($value);
		}
	}

	//Coloca os dados dentro da SESSION(sessão) e retornamos ele
	public function getValues()
	{
		return $this->values;
	}


}

?>