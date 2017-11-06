<?php 
	include_once $root."/code/thirdParty/pdf/fpdi.php";
	
	class invoicePdf
	{
		public function __construct($invoice)
		{
			global $root, $cfg;

			$this->invoice = $invoice;

			$this->pdf = new FPDI();
			$this->pdf->SetAutoPageBreak(false);
			$this->pdf->AddFont('Trebuchet');
			$this->pdf->AddFont('TrebuchetB');
			$this->pdf->AddFont('TrebuchetI');
			$this->pdf->AddFont('TrebuchetBI');
			$this->pdf->AddFont('Arial');
			$this->pdf->AddFont('ArialN');
			$this->pdf->AliasNbPages();
			
			$this->useGuides = false;
			$this->debugColour = false;
						
			$this->doCalcs();
			
			$src = $this->useGuides ? $cfg["TEMPLATE"] : $cfg["TEMPLATE"];
			$this->templateFirstPage = $this->useGuides ? $cfg["TEMPLATE_GUIDE_FIRST_PAGE"] : $cfg["TEMPLATE_FIRST_PAGE"];
			$this->templateSubsequentPage = $this->useGuides ? $cfg["TEMPLATE_GUIDE_SUBSEQUENT_PAGE"] : $cfg["TEMPLATE_SUBSEQUENT_PAGE"];
			$this->pdf->setSourceFile($root."/templates/".$src);
			
			$this->pdf->setTextColor(0, 0, 0);
			$this->pdf->SetXY(0, 0);
			
			$invoiceLines = $this->invoice->lines;
			
			$this->hasPence = false;
			
			foreach($invoiceLines as $invoiceLine) {
				$this->setInvoiceLineFont($invoiceLine);
				$invoiceLine->height = $this->pdf->GetMultiCellHeight($cfg["TEXT_DESC_WIDTH"], $cfg["INVOICELINE_TEXT_HEIGHT"], utf8_decode($invoiceLine->getDescription($cfg["DATE_FORMAT"], !$cfg["TABLE_DATEANDHOURS"])));
				$invoiceLine->paddedHeight = $invoiceLine->height + $cfg["INVOICELINE_PADDING_TOP"] + $cfg["INVOICELINE_PADDING_BOTTOM"];
				$lineHeightTotal += $invoiceLine->paddedHeight;
				$amount = $invoiceLine->getNetAmount();
				if(is_numeric($amount) && floor($amount) != $amount)
					$this->hasPence = true;
			}
			$this->totals = $this->invoice->getTotals();
			foreach(array("net", "vat", "grand") as $field) {
				if(floor($this->totals[$field]) != $this->totals[$field])
					$this->hasPence = true;
			}

			$first = true;
			$pageNum = 1;
			while(count($invoiceLines) > 0) {
				$last = $this->createPage($first, $invoiceLines, $pageNum);
				$first = false;
				$pageNum++;
			}
			if($first || !$last) {
				$line = new invoiceLineRow();
				$line->note = true;
				$line->description = "Totals only on this page";
				$invoiceLines[] = $line;
				$this->createPage($first, $invoiceLines, $pageNum);
			}
		}
		
		public function createPage($first, &$invoiceLines, $pageNum)
		{
			global $cfg;
		
			$tableTop = $first ? $cfg["TABLE_TOP_FULLHEADER"] : $cfg["TABLE_TOP_HALFHEADER"];
			$linesTop = $tableTop + $cfg["TABLE_HEADER_CELL_HEIGHT"] + $cfg["TABLE_PADDING_TOP"];
		
			$linesSpaceTotals = $this->getLineSpace($linesTop, true);
			$linesSpaceNoTotals = $this->getLineSpace($linesTop, false);
			
			if($this->debugColour) {
				$this->pdf->SetDrawColor(0xFF, 0, 0);
				$this->pdf->Line(0, $linesTop, 100, $linesTop);
				$this->pdf->SetDrawColor(0xFF, 0xFF, 0);
				$this->pdf->Line(0, $linesTop + $linesSpaceTotals, 100, $linesTop + $linesSpaceTotals);
				$this->pdf->SetDrawColor(0, 0, 0xFF);
				$this->pdf->Line(0, $linesTop + $linesSpaceNoTotals, 100, $linesTop + $linesSpaceNoTotals);
			}
					
			$pageLines = array();
			$linesHeight = 0;
			while(count($invoiceLines) > 0) {
				$paddedHeight = is_numeric($invoiceLines[0]->paddedHeight) ? $invoiceLines[0]->paddedHeight : 0;
				if($linesHeight + $paddedHeight < $linesSpaceNoTotals) {
					$linesHeight += $paddedHeight;
					$pageLines[] = array_shift($invoiceLines);
				} else {
					break;
				}
			}
			$last = $linesHeight <= $linesSpaceTotals;

			$this->addPageFromTemplate($first ? $this->templateFirstPage : $this->templateSubsequentPage);
			$this->outputHeader($first, $first && $last ? -1 : $pageNum);
			
			$tableBottomY = $this->outputTable($tableTop, $cfg["TABLE_HEADER_CELL_HEIGHT"] + $cfg["TABLE_PADDING_TOP"] + ($last ? $linesSpaceTotals : $linesSpaceNoTotals));
			$this->outputLines($linesTop, $pageLines);
			if($last)
				$this->outputTableFooter($tableBottomY);
				
			return $last;
		}
		
		public function addPageFromTemplate($templatePage)
		{
			global $cfg;
			$pageId = $this->pdf->importPage($templatePage, '/MediaBox');   

			$this->pdf->AddPage("P", "A4");
			$this->pdf->useTemplate($pageId, null, $cfg["TEMPLATE_NUDGE_Y"]);
		}
		
		public function getLineSpace($top, $totals)
		{
			global $cfg;
			if($cfg["TABLE_BOTTOM_NO_TOTALS"])
				$bottom = $totals ? $cfg["TABLE_BOTTOM"] - $cfg["TOTALS_COMBINED_HEIGHT"] : $cfg["TABLE_BOTTOM_NO_TOTALS"];
			else
				$bottom = $cfg["TABLE_BOTTOM"] - ($totals ? $cfg["TOTALS_COMBINED_HEIGHT"] : 0);
			return $bottom - $top;
		}
		
		public function doCalcs()
		{
			global $cfg;
			$cfg["TABLE_RIGHT"] 			= $cfg["TABLE_LEFT"] + $cfg["TABLE_WIDTH"];
			$cfg["TABLE_AMOUNT_LEFT"]		= $cfg["TABLE_RIGHT"] - $cfg["TABLE_AMOUNT_WIDTH"];
			if($cfg["TABLE_DATEANDHOURS"]) {
				$cfg["TABLE_HOURS_LEFT"]	= $cfg["TABLE_RIGHT"] - $cfg["TABLE_AMOUNT_WIDTH"] - $cfg["TABLE_HOURS_WIDTH"];
				$cfg["TABLE_DESC_LEFT"]		= $cfg["TABLE_LEFT"] + $cfg["TABLE_DATE_WIDTH"];
				$cfg["TABLE_DESC_WIDTH"]	= $cfg["TABLE_HOURS_LEFT"] - $cfg["TABLE_DESC_LEFT"];
				$cfg["TEXT_DATE_LEFT"]		= $cfg["TABLE_LEFT"] + $cfg["DATE_PADDING_LEFT"];
				$cfg["TEXT_HOURS_LEFT"]		= $cfg["TABLE_HOURS_LEFT"] + $cfg["HOURS_PADDING_LEFT"];
			} else {
				$cfg["TABLE_DESC_LEFT"]		= $cfg["TABLE_LEFT"];
				$cfg["TABLE_DESC_WIDTH"]	= $cfg["TABLE_AMOUNT_LEFT"] - $cfg["TABLE_DESC_LEFT"];
			}
			$cfg["TEXT_DESC_LEFT"]			= $cfg["TABLE_DESC_LEFT"] + $cfg["DESC_PADDING_LEFT"];
			$cfg["TEXT_DESC_WIDTH"]			= $cfg["TABLE_DESC_WIDTH"] - $cfg["DESC_PADDING_LEFT"] - $cfg["DESC_PADDING_RIGHT"];
			$cfg["TOTALS_COMBINED_HEIGHT"]	=  $cfg["TOTALS_ITEMTOTAL_CELL_HEIGHT"]	+ $cfg["TOTALS_VAT_CELL_HEIGHT"] + $cfg["TOTALS_GRANDTOTAL_CELL_HEIGHT"];			
		}
		
		public function outputHeader($full, $pageNum)
		{
			global $cfg;
			
			if($this->debugColour)
				$this->pdf->setTextColor(0, 0, 0xFF);
			else
				$this->pdf->setTextColor(0, 0, 0);
			
			$textHeight = $cfg["DATEBLOCK_LINEHEIGHT"];
			
			$dateBlockLeft = $cfg["DATEBLOCK_LEFT"];
			$dateBlockTop = $cfg["DATEBLOCK_TOP"];
			
			$oldLMargin = $this->pdf->lMargin;
			$this->pdf->setLeftMargin($dateBlockLeft);
			
			$dateItems = array();
			foreach($cfg["DATEBLOCK_ORDER"] as $itemName) {
				if($itemName == "DATE")
					$dateItems[$cfg["DATEBLOCK_DATE"]] = date($cfg["DATE_FORMAT"], $this->invoice->dt);
				else if($itemName == "INVOICENO")
					$dateItems[$cfg["DATEBLOCK_INVOICENO"]] = $this->invoice->invoiceNumber;
				else if($itemName == "PAGE" && $pageNum != -1)
					$dateItems[$cfg["DATEBLOCK_PAGE"]] = $pageNum." of {nb}";
			}

			$this->pdf->SetXY($dateBlockLeft, $dateBlockTop);			
			foreach($dateItems as $heading => $value) {
				if($cfg["DATEBLOCK_VALUE_LEFT"])
					$this->pdf->SetX($dateBlockLeft);
				$this->pdf->SetFont($cfg["DATEBLOCK_LABELFONT"], '', $cfg["DATEBLOCK_LABELFONTSIZE"]);			
				$this->pdf->Write($textHeight, $heading." ");
				if($cfg["DATEBLOCK_VALUE_LEFT"])
					$this->pdf->SetX($cfg["DATEBLOCK_VALUE_LEFT"]);
				$this->pdf->SetFont($cfg["DATEBLOCK_VALUEFONT"], $cfg["DATEBLOCK_VALUEFONTSTYLE"], $cfg["DATEBLOCK_VALUEFONTSIZE"]);
				$this->pdf->Write($textHeight, $value."\n");
			}
		
			$this->pdf->setLeftMargin($oldLMargin);	
			
			if($full) {
				$this->pdf->SetFont($cfg["ADDRESSFONT"], '', $cfg["ADDRESSFONTSIZE"]);
				
				$this->pdf->SetXY($cfg["ADDRESSBLOCK_LEFT"], $cfg["ADDRESSBLOCK_TOP"]);
				if($cfg["ADDRESSBLOCK_LINE2_TOP"]) {
					$this->pdf->MultiCell($cfg["ADDRESSBLOCK_WIDTH"], $cfg["ADDRESSBLOCK_LINE_HEIGHT"], $this->invoice->getCustomerName(), $this->debugColour ? 1 : 0, "L");					
					$this->pdf->SetXY($cfg["ADDRESSBLOCK_LEFT"], $cfg["ADDRESSBLOCK_LINE2_TOP"]);
					$this->pdf->MultiCell($cfg["ADDRESSBLOCK_WIDTH"], $cfg["ADDRESSBLOCK_LINE_HEIGHT"], $this->invoice->getRecipientLine2Onwards(), $this->debugColour ? 1 : 0, "L");					
				} else {
					$this->pdf->MultiCell($cfg["ADDRESSBLOCK_WIDTH"], $cfg["ADDRESSBLOCK_LINE_HEIGHT"], $this->invoice->recipient, $this->debugColour ? 1 : 0, "L");					
				}
				
				if(!$cfg["DESCBLOCK_HIDE"]) {
					$this->pdf->SetXY($cfg["DESCBLOCK_LEFT"], $cfg["DESCBLOCK_TOP"]);
					$this->pdf->MultiCell($cfg["DESCBLOCK_WIDTH"], $cfg["DESCBLOCK_LINE_HEIGHT"], $this->invoice->description, $this->debugColour ? 1 : 0, "L");
				}
			}
		}
		
		public function outputTable($tableTop, $tableHeight)
		{
			global $cfg;
			
			$textHeight = $cfg["TABLE_HEADER_TEXT_HEIGHT"];
			$tableTLX = $cfg["TABLE_LEFT"];
			$tableTLY = $tableTop;
			$tableWidth = $cfg["TABLE_WIDTH"];
			
			$tableTRX = $cfg["TABLE_RIGHT"];
			$tableBRY = $tableTLY + $tableHeight;
			
			if($this->debugColour) {
				$this->pdf->setTextColor(0xFF, 0, 0);
			} else {
				$this->pdf->setTextColor(0, 0, 0);
			}
			$this->pdf->SetFont($cfg["TABLE_HEADER_FONT"], '', $cfg["TABLE_HEADER_FONTSIZE"]);
			$headerTextY = $tableTop + $cfg["TABLE_HEADER_PADDING_TOP"];
			if($cfg["TABLE_DATEANDHOURS"]) {
				$this->pdf->SetXY($cfg["TABLE_LEFT"] + $cfg["TABLE_HEADER_CELL_PADDING_LEFT"], $headerTextY);
				$this->pdf->Cell($cfg["TABLE_DATE_WIDTH"], $textHeight, $cfg["TABLE_DATE"], 0, 0, $cfg["TABLE_HEADER_ALIGN"]);
			}
			$this->pdf->SetXY($cfg["TABLE_DESC_LEFT"] + $cfg["TABLE_HEADER_CELL_PADDING_LEFT"], $headerTextY);
			$this->pdf->Cell($cfg["TABLE_DESC_WIDTH"], $textHeight, $cfg["TABLE_DESC"], 0, 0, $cfg["TABLE_HEADER_ALIGN"]);
			if($cfg["TABLE_DATEANDHOURS"]) {
				$this->pdf->SetXY($cfg["TABLE_HOURS_LEFT"] + $cfg["TABLE_HEADER_CELL_PADDING_LEFT"], $headerTextY);
				$this->pdf->Cell($cfg["TABLE_HOURS_WIDTH"], $textHeight, $cfg["TABLE_HOURS"], 0, 0, $cfg["TABLE_HEADER_ALIGN"]);
			}
			$this->pdf->SetXY($cfg["TABLE_AMOUNT_LEFT"] + $cfg["TABLE_HEADER_CELL_PADDING_LEFT"], $headerTextY);
			$this->pdf->Cell($cfg["TABLE_AMOUNT_WIDTH"], $textHeight, $cfg["TABLE_AMOUNT"], 0, 0, $cfg["TABLE_HEADER_ALIGN"]);
		
			if($this->debugColour) {
				$this->pdf->SetDrawColor(0xFF, 0, 0);
			} else {
				$this->pdf->SetDrawColor(0, 0, 0);
			}
			$topDividerY = $tableTLY + $cfg["TABLE_HEADER_CELL_HEIGHT"];
			if($cfg["TABLE_HEADER_BORDER"]) {
				$this->pdf->SetLineWidth(0.5);
				$this->pdf->Line($tableTLX + 0.16, $tableTLY, $tableTRX- 0.16, $tableTLY);
				$borderTop = $tableTLY;
			} else {
				$borderTop = $topDividerY;
			}
			$this->pdf->SetLineWidth($cfg["TABLE_LINE_SIZE"]);
			if($cfg["TABLE_HEADER_BORDER"])
				$this->pdf->Line($tableTLX, $topDividerY, $tableTRX, $topDividerY);				
			
			$this->pdf->Rect($tableTLX, $borderTop, $tableWidth, $tableBRY - $borderTop);
			
			// v dividers
			$this->pdf->Line($cfg["TABLE_AMOUNT_LEFT"], $borderTop, $cfg["TABLE_AMOUNT_LEFT"], $tableBRY);
			if($cfg["TABLE_DATEANDHOURS"]) {
				$this->pdf->Line($cfg["TABLE_DESC_LEFT"], $borderTop, $cfg["TABLE_DESC_LEFT"], $tableBRY);
				$this->pdf->Line($cfg["TABLE_HOURS_LEFT"], $borderTop, $cfg["TABLE_HOURS_LEFT"], $tableBRY);
			}
			
			return $tableBRY;
		}
		
		public function outputTableFooter($footerTop)
		{
			global $cfg;
			$tableTRX = $cfg["TABLE_RIGHT"];
			$costDividerX = $cfg["TABLE_AMOUNT_LEFT"];
			
			$totalsHeight = $cfg["TOTALS_COMBINED_HEIGHT"];
			$totalsLCellWidth = 20;
			$totalsLCellX = $costDividerX - $totalsLCellWidth - $cfg["TOTALS_HEADINGS_PADDING_RIGHT"];
			$totalsRCellWidth = $cfg["TABLE_AMOUNT_WIDTH"] - $cfg["AMOUNT_PADDING_RIGHT"];
			$totalsRCellX = $costDividerX;
						
			$this->pdf->Line($costDividerX, $footerTop, $costDividerX, $footerTop + $totalsHeight);
			$this->pdf->Line($tableTRX, $footerTop, $tableTRX, $footerTop + $totalsHeight);
			
			$currentY = $footerTop + $cfg["TOTALS_ITEMTOTAL_CELL_HEIGHT"];
			$this->pdf->Line($costDividerX, $currentY, $tableTRX, $currentY);
			$currentY += $cfg["TOTALS_VAT_CELL_HEIGHT"];
			if($cfg["TOTALS_GRANDTOTAL_LINES"]) {
				$this->pdf->SetLineWidth(0.5);
				$lineAdjustment = 0.2;
			} else {
				$lineAdjustment = 0;
			}
			$this->pdf->Line($costDividerX + $lineAdjustment, $currentY, $tableTRX - $lineAdjustment, $currentY);
			$currentY += $cfg["TOTALS_GRANDTOTAL_CELL_HEIGHT"];
			$this->pdf->Line($costDividerX + $lineAdjustment, $currentY, $tableTRX - $lineAdjustment, $currentY);
			
			$this->pdf->SetFont($cfg["TOTALS_FONT"], '', $cfg["TOTALS_FONTSIZE"]);
			
			$textHeight = $cfg["TOTALS_TEXT_HEIGHT"];
			$currentY = $footerTop + $cfg["TOTALS_HEADING_PADDING_TOP"];
			$this->pdf->SetXY($totalsLCellX, $currentY);
			$this->pdf->Cell($totalsLCellWidth, $textHeight, $cfg["TOTALS_ITEMTOTAL"], 0, 0, "R");
			$currentY += $cfg["TOTALS_ITEMTOTAL_CELL_HEIGHT"];
			$this->pdf->SetXY($totalsLCellX, $currentY);
			$this->pdf->Cell($totalsLCellWidth, $textHeight, $cfg["TOTALS_VAT"], 0, 0, "R");
			if($cfg["TOTALS_GRANDTOTAL_FONTSTYLE"])
				$this->pdf->SetFont($cfg["TOTALS_FONT"], $cfg["TOTALS_GRANDTOTAL_FONTSTYLE"], $cfg["TOTALS_FONTSIZE"]);
			$currentY += $cfg["TOTALS_VAT_CELL_HEIGHT"];
			$this->pdf->SetXY($totalsLCellX, $currentY);
			$this->pdf->Cell($totalsLCellWidth, $textHeight, $cfg["TOTALS_GRANDTOTAL"], 0, 0, "R");
												
			if($this->invoice->paymentTerms) {
				$oldLMargin = $this->pdf->lMargin;
				$oldRMargin = $this->pdf->rMargin;
				$this->pdf->setLeftMargin($cfg["PAYMENTTERMS_LEFT"]);
				$this->pdf->setRightMargin($this->pdf->w - $costDividerX + $totalsLCellWidth + 10);
				
				$this->pdf->SetFont($cfg["PAYMENTTERMS_HEADING_FONT"], '', $cfg["PAYMENTTERMS_HEADING_FONTSIZE"]);
				$this->pdf->SetXY($cfg["PAYMENTTERMS_LEFT"], $footerTop + $cfg["PAYMENTTERMS_PADDING_TOP"]);
				$this->pdf->Write($textHeight, $cfg["PAYMENTTERMS"]);
				$this->pdf->SetFont($cfg["PAYMENTTERMS_FONT"], '', $cfg["PAYMENTTERMS_FONTSIZE"]);
				if($cfg["PAYMENTTERMS_TEXT_GAP"])
					$this->pdf->SetY($this->pdf->GetY() + $cfg["PAYMENTTERMS_TEXT_GAP"]);
				
				$this->pdf->Write($textHeight, $this->invoice->paymentTerms);
				$this->pdf->setLeftMargin($oldLMargin);
				$this->pdf->setRightMargin($oldRMargin);
			}
			if($cfg["THANKS"]) {
				$this->pdf->SetFont($cfg["THANKS_FONT"], '', $cfg["THANKS_FONTSIZE"]);
				$this->pdf->SetXY($cfg["THANKS_LEFT"], $footerTop + $cfg["THANKS_PADDING_TOP"]);
				$this->pdf->Write($textHeight, $cfg["THANKS"]);
			}
			
			$this->pdf->SetFont($cfg["TOTALS_AMOUNT_FONT"], '', $cfg["TOTALS_AMOUNT_FONTSIZE"]);
			$currentY = $footerTop + $cfg["TOTALS_AMOUNT_PADDING_TOP"];
			$this->pdf->SetXY($totalsRCellX, $currentY);
			$this->pdf->Cell($totalsRCellWidth, $textHeight, $this->formatPounds($this->totals["net"]), 0, 0, "R");
			$currentY += $cfg["TOTALS_ITEMTOTAL_CELL_HEIGHT"];
			$this->pdf->SetXY($totalsRCellX, $currentY);
			$this->pdf->Cell($totalsRCellWidth, $textHeight, $this->formatPounds($this->totals["vat"]), 0, 0, "R");
			$currentY += $cfg["TOTALS_VAT_CELL_HEIGHT"];
			if($cfg["TOTALS_GRANDTOTAL_FONTSTYLE"])
				$this->pdf->SetFont($cfg["TOTALS_AMOUNT_FONT"], $cfg["TOTALS_GRANDTOTAL_FONTSTYLE"], $cfg["TOTALS_AMOUNT_FONTSIZE"]);
			
			$this->pdf->SetXY($totalsRCellX, $currentY);
			$this->pdf->Cell($totalsRCellWidth, $textHeight, $this->formatPounds($this->totals["grand"]), 0, 0, "R");
		}
		
		public function setInvoiceLineFont($invoiceLine)
		{
			global $cfg;
			if($invoiceLine->note) {
				$this->pdf->SetFont($cfg["INVOICELINE_NOTEFONT"], '', $cfg["INVOICELINE_FONTSIZE"]);
			} else if($invoiceLine->heading) {
				$this->pdf->SetFont($cfg["INVOICELINE_HEADINGFONT"], $cfg["INVOICELINE_HEADINGFONTSTYLE"], $cfg["INVOICELINE_FONTSIZE"]);
			} else {
				$this->pdf->SetFont($cfg["INVOICELINE_FONT"], '', $cfg["INVOICELINE_FONTSIZE"]);
			}
		}
		
		public function outputLines($startY, $invoiceLines)
		{
			global $cfg;
			$tableTLX = $cfg["TABLE_LEFT"];
			
			if($this->debugColour)
				$this->pdf->setTextColor(0, 0xFF, 0);	
			else
				$this->pdf->setTextColor(0, 0, 0);
			$currentY = $startY;
			foreach($invoiceLines as $invoiceLine) {
				if($this->debugColour) {
					$this->pdf->SetDrawColor(0, 0, 0xFF);
					$this->pdf->Line($tableTLX, $currentY, $cfg["TABLE_RIGHT"], $currentY);
				}
				
				$currentY += $cfg["INVOICELINE_PADDING_TOP"];
				if($this->debugColour) {
					$this->pdf->SetDrawColor(0xFF, 0, 0xFF);
					$this->pdf->Line($tableTLX, $currentY, $cfg["TABLE_RIGHT"], $currentY);
				}
				$this->setInvoiceLineFont($invoiceLine);
				if($cfg["TABLE_DATEANDHOURS"]) {
					$this->pdf->SetXY($cfg["TEXT_DATE_LEFT"], $currentY);
					$this->pdf->MultiCell($cfg["TEXT_DATE_WIDTH"], $cfg["INVOICELINE_TEXT_HEIGHT"], utf8_decode($invoiceLine->getDateText($cfg["DATE_FORMAT"])), 0, "L");
				}
				$this->pdf->SetXY($cfg["TEXT_DESC_LEFT"], $currentY);
				$this->pdf->MultiCell($cfg["TEXT_DESC_WIDTH"], $cfg["INVOICELINE_TEXT_HEIGHT"], utf8_decode($invoiceLine->getDescription($cfg["DATE_FORMAT"], !$cfg["TABLE_DATEANDHOURS"])), 0, "L");
				if($cfg["TABLE_DATEANDHOURS"]) {
					$this->pdf->SetXY($cfg["TEXT_HOURS_LEFT"], $currentY);
					$this->pdf->MultiCell($cfg["TEXT_HOURS_WIDTH"], $cfg["INVOICELINE_TEXT_HEIGHT"], utf8_decode($invoiceLine->getHoursColumnText()), 0, "L");
				}
				
				if($invoiceLine->note || $invoiceLine->heading)
					$this->pdf->SetFont($cfg["INVOICELINE_FONT"], '', $cfg["INVOICELINE_FONTSIZE"]);
				
				$totalsRCellWidth = $cfg["TABLE_AMOUNT_WIDTH"] - $cfg["AMOUNT_PADDING_RIGHT"];
				$this->pdf->SetXY($cfg["TABLE_AMOUNT_LEFT"], $currentY);
				$this->pdf->Cell($totalsRCellWidth, $cfg["INVOICELINE_TEXT_HEIGHT"], $this->formatPounds($invoiceLine->getNetAmount()), 0, 0, "R");

				$currentY += is_numeric($invoiceLine->height) ? $invoiceLine->height : 0;
				if($this->debugColour) {
					$this->pdf->SetDrawColor(0, 0xFF, 0xFF);
					$this->pdf->Line($tableTLX, $currentY, $cfg["TABLE_RIGHT"], $currentY);
				}
				$currentY += $cfg["INVOICELINE_PADDING_BOTTOM"];
			}
		}
		
		public function formatPounds($numStr)
		{
			global $cfg;
			if($cfg["POUNDS_EYEDESIGN"])
				return is_numeric($numStr) ? number_format($numStr, 2, ".", ",") : $numStr;
			else
				return is_numeric($numStr) ? chr(163).($this->hasPence ? number_format($numStr, 2) : number_format($numStr)) : $numStr;
		}
	}
?>