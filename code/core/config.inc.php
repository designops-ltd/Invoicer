<?php 
global $cfg;

if(!isset($cfg) || !is_array($cfg)) {
	$cfg = array();

	$cfg["TEMPLATE_ONE_PAGE"]				= true;
	$cfg["TEMPLATE_GUIDE"]					= "Sample-InvoiceTemp-multipage-1.pdf";
	$cfg["TEMPLATE_GUIDE_FIRST_PAGE"]		= 1;
	$cfg["TEMPLATE_GUIDE_SUBSEQUENT_PAGE"]	= 2;
	$cfg["TEMPLATE"]						= "Sample-InvoiceTemp-multipage-1.pdf";
	$cfg["TEMPLATE_FIRST_PAGE"]				= 1;
	$cfg["TEMPLATE_SUBSEQUENT_PAGE"]		= 2;
	$cfg["TEMPLATE_NUDGE_Y"]				= -0.1;
	
	$cfg["DATE_FORMAT"]						= "d/m/Y";
	$cfg["POUNDS_EYEDESIGN"]				= true;
	
	$cfg["DATEBLOCK_LABELFONT"]				= "Arial";
	$cfg["DATEBLOCK_LABELFONTSIZE"]			= 10;
	$cfg["DATEBLOCK_VALUEFONT"]				= "Arial";
	$cfg["DATEBLOCK_VALUEFONTSIZE"]			= 10;
	$cfg["DATEBLOCK_VALUEFONTSTYLE"]		= "B";
	$cfg["DATEBLOCK_ORDER"]					= array("INVOICENO", "DATE", "PAGE");
	$cfg["DATEBLOCK_INVOICENO"]				= "Invoice no:";
	$cfg["DATEBLOCK_DATE"]					= "Invoice date:";
	$cfg["DATEBLOCK_PAGE"]					= "Page:";
	$cfg["DATEBLOCK_LEFT"]					= 75;
	$cfg["DATEBLOCK_VALUE_LEFT"]			= 97;
	$cfg["DATEBLOCK_TOP"]					= 22.6;
	$cfg["DATEBLOCK_LINEHEIGHT"]			= 4.5;
	
	$cfg["ADDRESSFONT"]						= "Arial";
	$cfg["ADDRESSFONTSIZE"]					= 10;
	$cfg["ADDRESSBLOCK_LEFT"]				= 12;
	$cfg["ADDRESSBLOCK_TOP"]				= 47;
	$cfg["ADDRESSBLOCK_LINE2_TOP"]			= 52;
	$cfg["ADDRESSBLOCK_WIDTH"]				= 94;
	$cfg["ADDRESSBLOCK_LINE_HEIGHT"]		= 4.2;
	
	$cfg["DESCBLOCK_HIDE"]					= true;
	
	$cfg["TABLE_TOP_FULLHEADER"]			= 68.00;
	$cfg["TABLE_TOP_HALFHEADER"]			= 38.6;
	$cfg["TABLE_BOTTOM"]					= 273.2;
	$cfg["TABLE_BOTTOM_NO_TOTALS"]			= 262;
	
	$cfg["TABLE_DATEANDHOURS"]				= true;
	$cfg["TABLE_HEADER_BORDER"]				= false;
	$cfg["TABLE_HEADER_FONT"]				= "ArialN";
	$cfg["TABLE_HEADER_FONTSIZE"]			= 14;
	$cfg["TABLE_HEADER_ALIGN"]				= "L";
	$cfg["TABLE_HEADER_TEXT_HEIGHT"]		= 4.8;
	$cfg["TABLE_HEADER_PADDING_TOP"]		= 1.9;
	$cfg["TABLE_HEADER_CELL_PADDING_LEFT"]	= 1.0;
	$cfg["TABLE_HEADER_CELL_HEIGHT"]		= 7;
	$cfg["TABLE_DATE"]						= "Date";
	$cfg["TABLE_DESC"]						= "Service details";
	$cfg["TABLE_HOURS"]						= "Hrs";
	$cfg["TABLE_AMOUNT"]					= "Amount";
	$cfg["TABLE_LINE_SIZE"]					= 0.1;
	$cfg["TABLE_LEFT"]						= 12.7;
	$cfg["TABLE_WIDTH"]						= 184.5;
	$cfg["TABLE_AMOUNT_WIDTH"]				= 23.2;
	$cfg["TABLE_HOURS_WIDTH"]				= 14.5;
	$cfg["TABLE_DATE_WIDTH"]				= 22.3;
	$cfg["TABLE_PADDING_TOP"]				= 1.3;
	
	$cfg["INVOICELINE_FONTSIZE"]			= 9;
	$cfg["INVOICELINE_FONT"]				= "Arial";
	$cfg["INVOICELINE_NOTEFONT"]			= "Arial";
	$cfg["INVOICELINE_HEADINGFONT"]			= "Arial";
	$cfg["INVOICELINE_HEADINGFONTSTYLE"]	= "B";
	$cfg["INVOICELINE_TEXT_HEIGHT"]			= 3.8;
	$cfg["DATE_PADDING_LEFT"]				= 1.0;
	$cfg["DESC_PADDING_LEFT"]				= 1.0;
	$cfg["DESC_PADDING_RIGHT"]				= 1;
	$cfg["HOURS_PADDING_LEFT"]				= 1.0;
	$cfg["INVOICELINE_PADDING_TOP"]			= 1.05;
	$cfg["INVOICELINE_PADDING_BOTTOM"]		= 1.0;
	
	$cfg["TOTALS_GRANDTOTAL_LINES"]			= true;
	$cfg["TOTALS_FONT"]						= "Arial";
	$cfg["TOTALS_FONTSIZE"]					= 9;
	$cfg["TOTALS_AMOUNT_FONT"]				= "Arial";
	$cfg["TOTALS_AMOUNT_FONTSIZE"]			= 10;
	$cfg["TOTALS_GRANDTOTAL_FONTSTYLE"]		= "B";
	$cfg["TOTALS_ITEMTOTAL"]				= "Sub total";
	$cfg["TOTALS_VAT"]						= "VAT";
	$cfg["TOTALS_GRANDTOTAL"]				= "Grand Total (GBP)";
	$cfg["TOTALS_ITEMTOTAL_CELL_HEIGHT"]	= 7.4;
	$cfg["TOTALS_VAT_CELL_HEIGHT"]			= 7.4;
	$cfg["TOTALS_GRANDTOTAL_CELL_HEIGHT"]	= 7.4;
	$cfg["AMOUNT_PADDING_RIGHT"]			= 1;
	$cfg["TOTALS_HEADINGS_PADDING_RIGHT"]	= 2.0;
	$cfg["TOTALS_HEADING_PADDING_TOP"]		= 1.8;	
	$cfg["TOTALS_AMOUNT_PADDING_TOP"]		= 1.7;
	$cfg["TOTALS_TEXT_HEIGHT"]				= 4.8;
	
	$cfg["PAYMENTTERMS"]					= "Payment terms:  ";
	$cfg["PAYMENTTERMS_LEFT"]				= 11.5;
	$cfg["PAYMENTTERMS_HEADING_FONT"]		= "Arial";
	$cfg["PAYMENTTERMS_HEADING_FONTSIZE"]	= 8;
	$cfg["PAYMENTTERMS_FONT"]				= "Arial";
	$cfg["PAYMENTTERMS_FONTSIZE"]			= 8;
	$cfg["PAYMENTTERMS_PADDING_TOP"]		= 9.4;
	$cfg["PAYMENTTERMS_TEXT_GAP"]			= 0;
	
	$cfg["THANKS"]							= "Thank you for your business";
	$cfg["THANKS_FONT"]						= "Arial";
	$cfg["THANKS_FONTSIZE"]					= 9;
	$cfg["THANKS_PADDING_TOP"]				= 2;
	$cfg["THANKS_LEFT"]						= 11.5;
}
?>