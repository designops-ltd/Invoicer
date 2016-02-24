<?php 
define("DB_PRIMARY", 			0x000001);
define("DB_BOOL", 				0x000002);
define("DB_INT",				0x000003);
define("DB_INTNOTNULL",			0x000004);
define("DB_TEXT",				0x000005);
define("DB_DATETIME",			0x000006);
define("DB_FLOAT",				0x000007);
define("DB_TYPEMASK",			0x0000FF);

class dataRow
{
	public function __construct()
	{
	}
	
	public function __get($field)
	{
		return "";
	}
	
	public function __set($field, $value)
	{
		$this->$field = $value;
	}
	
	public function getSchema()
	{
		return array("fields"=>array(), "table"=>"");
	}
			
	public function getFields()
	{
		$schema = $this->getSchema();
		return $schema["fields"];
	}

	public function getTable()
	{
		$schema = $this->getSchema();
		return $schema["table"];
	}	
}

class dataRowDB
{	
}
?>