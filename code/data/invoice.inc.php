<?php
include_once $root."/code/core/dataRow.inc.php";

class invoiceRow extends dataRow
{
	public function getSchema() {return invoiceDB::$schema;}	
	
	public $lines = array();
	public $projectLines = array();	
	
	public function addProjectLine($project, $line)
	{
		if(!is_array($this->projectLines[$project]))
			$this->projectLines[$project] = array();
		$this->projectLines[$project][] = $line;
		$line->invoice = $this;
	}
	
	public function createLinesFromProjectLines()
	{
		$this->lines = array();
		$filteredProjectLines = array();
		foreach($this->projectLines as $project => $projectLines) {
			$hasNonBlank = false;
			foreach($projectLines as $projectLine) {
				if($projectLine->description)
					$hasNonBlank = true;
			}
			if($hasNonBlank)
				$filteredProjectLines[$project] = $projectLines;
		}
		$useProjectHeadings = count($filteredProjectLines) > 1;
		$first = true;
		foreach($filteredProjectLines as $project => $projectLines) {
			if($useProjectHeadings) {
				if(!$first) {
					$blankLine = new invoiceLineRow();
					$blankLine->invoice = $this;
					$this->lines[] = $blankLine;
				}
				$headingLine = new invoiceLineRow();
				$headingLine->heading = true;
				$headingLine->description = $project;
				$headingLine->invoice = $this;
				$this->lines[] = $headingLine;
			}
			foreach($projectLines as $lines) {
				$this->lines[] = $lines;
			}
			$first = false;
		}
	}
	
	public function getTotals()
	{
		$net = 0;
		$vatTotals = array();
		foreach($this->lines as $line) {
			$netAmount = $line->getNetAmount();
			if(is_numeric($netAmount)) {
				$net += $netAmount;
				$vatRate = $line->getVatRate();
				if(!isset($vatTotals[$vatRate]))
					$vatTotals[$vatRate] = $netAmount;
				else
					$vatTotals[$vatRate] += $netAmount;
			}
		}
		$vat = 0;
		foreach($vatTotals as $vatRate => $vatAmount)
			$vat += round($vatAmount * $vatRate) / 100;
		
		return array("net"=>$net, "vat"=>$vat, "grand"=>$net + $vat);
	}
	
	function getCustomerName()
	{
		$recipientParts = explode("\n", $this->recipient);
		if(count($recipientParts) > 0)
			return $recipientParts[0];
	}
	
	function getRecipientLine2Onwards()
	{
		$recipientParts = explode("\n", $this->recipient);
		if(count($recipientParts) > 1) {
			array_shift($recipientParts);
			return implode("\n", $recipientParts);
		} else
			return "";
	}
}

class invoiceDB extends dataRowDB
{
	public static $schema;
	
	public static function checkAndConvertParam($param, $value, $lineNum)
	{
		switch($param) {
			case "dt":
				if(!isValidExcelDate($value))
					throw(new Exception("Row ".$lineNum.", date invalid"));
				return convertExcelDateToTimestamp($value);
			case "defaultHourlyRate":
				if(!is_numeric($value))
					throw(new Exception("Row ".$lineNum.", invalid hourly rate"));
				break;
			case "defaultVatRate":
				if(!is_numeric($value))
					throw(new Exception("Row ".$lineNum.", invalid vat rate"));
				break;
		}
		return $value;
	}
}

invoiceDB::$schema = array(
	"table"=>"invoice",
	"fields"=>array("id"=>DB_PRIMARY, "recipient"=>DB_TEXT, "filename"=>DB_TEXT, "invoiceNumber"=>DB_TEXT, "dt"=>DB_DATETIME, "description"=>DB_TEXT, "paymentTerms"=>DB_TEXT, "defaultVatRate"=>DB_TEXT, "defaultHourlyRate"=>DB_TEXT),
);

/////////////////

class invoiceLineRow extends dataRow
{	
	public function getSchema() {return invoiceLineDB::$schema;}
	
	public function getVatRate()
	{
		return $this->overrideVatRate === "" ? $this->invoice->defaultVatRate : $this->overrideVatRate;
	}
	
	public function getNetAmount()
	{
		if($this->netAmount == "/")
			return "";
		if($this->netAmount !== "")
			return $this->netAmount;
		if($this->hours !== "")
			return $this->hours * $this->getHourlyRate();
		return "";
	}
	
	public function getHourlyRate()
	{
		return $this->overrideHourlyRate === "" ? $this->invoice->defaultHourlyRate : $this->overrideHourlyRate;
	}
	
	public function getDescription($dateFormat, $incDateAndHours)
	{
		$ret = $this->description;
		if($this->dt && $incDateAndHours)
			$ret = date($dateFormat, $this->dt)." - ".$ret;
		if($this->ref)
			$ret = "[".$this->ref."] ".$ret;
		if($this->hours !== "" && $incDateAndHours)
			$ret .= " [".$this->hours.($this->hours == 1 ? "hr" : "hrs")."]";
		return $ret;
	}
	
	public function getDateText($dateFormat)
	{
		if($this->dt)
			return date($dateFormat, $this->dt);	
	}
	
	public function getHoursColumnText()
	{
		if($this->hours !== "")
			return $this->hours;
		if($this->netAmount)
			return "/";
	}
	
}

class invoiceLineDB extends dataRowDB
{
	public static $schema;
}

invoiceLineDB::$schema = array(
	"table"=>"invoiceLine",
	"fields"=>array("id"=>DB_PRIMARY, "deleted"=>DB_BOOL, "invoiceID"=>DB_INT, "description"=>DB_TEXT, "netAmount"=>DB_TEXT, "heading"=>DB_BOOL, "note"=>DB_BOOL, "ref"=>DB_TEXT, "dt"=>DB_DATETIME, "hours"=>DB_TEXT, "overrideVatRate"=>DB_TEXT, "overrideHourlyRate"=>DB_TEXT),
	"nonDescriptionDataFields"=>array("netAmount", "ref", "dt", "hours"),
);

?>