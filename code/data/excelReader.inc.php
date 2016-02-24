<?php
include_once $root."/code/data/invoice.inc.php";
require_once $root.'/code/thirdParty/excel/Classes/PHPExcel.php';

class excelReader
{
	public $error = "";
	public $invoices = array();
	public $invoiceFieldMapping = array("billto"=>"recipient", "date"=>"dt", "invoicenum"=>"invoiceNumber", "invoicenumber"=>"invoiceNumber", "desc"=>"description", "hourlyrate"=>"defaultHourlyRate", "vatrate"=>"defaultVatRate");
	
	function __construct($filename)
	{
		try {
			$objPHPExcel = PHPExcel_IOFactory::load($filename);
		} catch (Exception $e) {
			$this->error = $e->getMessage();
			return;
		}
		
		if(!is_object($objPHPExcel)) {
			$this->error = "No data found in sheet";
		} else {
			try {
				$numSheets = $objPHPExcel->getSheetCount();
				for($i = 0; $i < $numSheets; $i++) {
					$sheet = $objPHPExcel->getSheet($i);
					if(is_array($invoices = $this->createInvoices($sheet))) {
						foreach($invoices as $invoice)
							$this->invoices[] = $invoice;
					}
				}
			} catch (Exception $e) {
				$this->error = $e->getMessage();
			}
		}
	}
	
	private function createInvoices($sheet)
	{	
		$ret = array();
		$rows = $sheet->toArray();
		
		try {
			$currentSection = "";
			$sectionLinesRaw = array("defaults"=>array(), "sheets"=>array(), "lines"=>array());
			$newSectionNext = true;

			for($j = 0; $j < count($rows); $j++) {
				$row = $rows[$j];
				$emptyRow = true;
				for($i = 0; $i < count($row); $i++) {
					$cell = $row[$i];
					if($i == 0 && startsWith($cell, "*****"))
						$currentSection = "ignore";
					if(trim($cell) != "")
						$emptyRow = false;
				}
				if($currentSection != "ignore") {
					if($emptyRow && $currentSection != "lines")
						$newSectionNext = true;
					if(!$emptyRow && $newSectionNext) {
						if($currentSection == "") {
							$currentSection = "defaults";
						} else if($currentSection == "defaults") {
							if($this->isSheetsHeadings($row))	
								$currentSection = "sheets";
							else
								$currentSection = "lines";
						} else if($currentSection == "sheets") {
							$currentSection = "lines";
						}
						$newSectionNext = false;
						if(count($sectionLinesRaw[$currentSection]) > 0)
							echo "Problem";
					}
					if(!$emptyRow || $currentSection == "lines") {
						$sectionLinesRaw[$currentSection][$j] = $row;
					}
				}
			}
			if($currentSection == "")
				return false;

			$this->defaults = array();

			$projectIndex = array();

			$currentParam = "";
			$currentValue = "";
			foreach($sectionLinesRaw["defaults"] as $lineNum => $paramLine) {
				if($paramLine[0]) {
					if($currentParam)
						$this->setDefault($lineNum, $currentParam, $currentValue);
					$currentParam = strtolower(trim(str_replace(array(":", " "), "", $paramLine[0])));
					$currentValue = "";
				}
				if($currentParam)
					$currentValue .= ($currentValue ? "\n" : "") . $paramLine[1];
			}	
			if($currentParam)
				$this->setDefault($lineNum, $currentParam, $currentValue);

			if(count($sectionLinesRaw["sheets"]) == 0) {
				$invoice = new InvoiceRow();
				$invoice->filename = $sheet->getTitle();
				$ret[] = $invoice;
				foreach($this->defaults as $param => $value)
					$invoice->$param = $value;
				$projectIndex["catchall"] = $invoice;
			} else {
				reset($sectionLinesRaw["sheets"]);
				$headings = current($sectionLinesRaw["sheets"]);
				unset($sectionLinesRaw["sheets"][key($sectionLinesRaw["sheets"])]);
				foreach($sectionLinesRaw["sheets"] as $lineNum => $lineRaw) {
					$invoice = new InvoiceRow();
					$ret[] = $invoice;
					try {					
						foreach($this->defaults as $param => $value)
							$invoice->$param = $value;
						for($i = 0; $i < count($lineRaw); $i++) {
							$type = strtolower(trim(str_replace(array(":", " "), "", $headings[$i])));
							if(isset($this->invoiceFieldMapping[$type]))
								$type = $this->invoiceFieldMapping[$type];
								
							$cell = trim($lineRaw[$i]);
							if($cell !== "") {
								if($type == "projects") {
									foreach(explode(",", $cell) as $projectRaw) {
										if($project = trim($projectRaw)) {
											$projectIndex[$project] = $invoice;
											$invoice->projectLines[$project] = array();
										}
									}
								} else {
									$invoice->$type = invoiceDB::checkAndConvertParam($type, $cell, $lineNum);
								}
							} else if($type == "projects") {
								$projectIndex["catchall"] = $invoice;
							}
						}
					} catch (Exception $e) {
						$invoice->error = $e->getMessage();
					}
				}
			}

			if(count($sectionLinesRaw["lines"]) > 0) {			
				reset($sectionLinesRaw["lines"]);
				$headings = current($sectionLinesRaw["lines"]);
				unset($sectionLinesRaw["lines"][key($sectionLinesRaw["lines"])]);			

				$linesRaw = $sectionLinesRaw["lines"];
				foreach($linesRaw as $lineNum => $lineRaw) {
					$projectRaw = "";
					$dontInclude = false;
					$invoiceLine = new invoiceLineRow();
					for($i = 0; $i < count($lineRaw); $i++) {
						$cell = trim($lineRaw[$i]);
						if($cell !== "") {
							$type = strtolower(trim(str_replace(array(":", " "), "", $headings[$i])));
							switch($type) {
								case "hourlyrate":
									if(!is_numeric($cell) && $cell != "/")
										throw new Exception("Row ".$lineNum.", hourly rate invalid [".$cell."]");
									$invoiceLine->overrideHourlyRate = $cell;
									break;
								case "vatrate":
									if(!is_numeric($cell) && $cell != "/")
										throw new Exception("Row ".$lineNum.", vat rate invalid [".$cell."]");
									$invoiceLine->overrideVatRate = $cell;
									break;								
								case "netamount":
									if(!is_numeric($cell) && $cell != "/")
										throw new Exception("Row ".$lineNum.", net amount invalid [".$cell."]");
									$invoiceLine->netAmount = $cell;
									break;
								case "hours":
									if(!is_numeric($cell))
										throw new Exception("Row ".$lineNum.", hours invalid");
									$invoiceLine->hours = $cell;
									break;
								case "description":
								case "desc":
								case "servicedetails":
									$invoiceLine->description = $cell;
									break;
								case "ref":
									$invoiceLine->ref = $cell;
									break;
								case "date":
									if(!isValidExcelDate($cell))
										throw(new Exception("Row ".$lineNum.", date invalid: ".$cell));
									$invoiceLine->dt = convertExcelDateToTimestamp($cell);
									break;
								case "project":
									$projectRaw = $cell;
									break;
							}
						}
					}
					$hasNonDescriptionField = false;
					foreach(invoiceLineDB::$schema["nonDescriptionDataFields"] as $field) {
						if($invoiceLine->$field !== "")
							$hasNonDescriptionField = true;
					}
					if($invoiceLine->description && !$hasNonDescriptionField)
						$invoiceLine->heading = true;
					$project = trim($projectRaw);
					if(startsWith($project, "*"))
					    $dontInclude = true;
					if($dontInclude) {
    					; // no action
					} else if(isset($projectIndex[$project])) {
						$projectIndex[$project]->addProjectLine($project, $invoiceLine);
					} else if(isset($projectIndex["catchall"])) {
						$projectIndex["catchall"]->addProjectLine($project, $invoiceLine);
					} else {
						throw(new Exception("No catch all invoice"));
					}
				}
			}
		} catch (Exception $e) {
			if(count($ret) > 0) {
				for($i = 0; $i < count($ret); $i++)
					$ret[$i]->error = $e->getMessage();
			} else {
				throw($e);
			}
		}
		
		foreach($ret as $invoice)
			$invoice->createLinesFromProjectLines();
		
		return $ret;
	}
	
	private function isSheetsHeadings($headings)
	{
		foreach($headings as $heading) {
			if(strtolower($heading) == "projects")
				return true;
		}
		return false;
	}
	
	private function setDefault($lineNum, $param, $value)
	{
		if(isset($this->invoiceFieldMapping[$param]))
			$param = $this->invoiceFieldMapping[$param];
		$this->defaults[$param] = invoiceDB::checkAndConvertParam($param, $value, $lineNum);
	}
}
?>