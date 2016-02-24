<?php
	include_once "code/core/all.inc.php";
	include_once $root."/code/data/invoice.inc.php";
	include_once $root."/code/core/invoicePdf.inc.php";
	
	if(isset($_GET["rootFile"])) {
		session_start();
		if(isset($_SESSION["invoices_".$_GET["rootFile"]])) {
			$invoices = $_SESSION["invoices_".$_GET["rootFile"]];
			foreach($invoices as $invoice) {
				if($invoice->filename == $_GET["invoice"]) {
					$downloadMode = $_GET["mode"] == "view" ? "view" : "save";
					
					$invoicePdf = new invoicePdf($invoice);
						
					$invoicePdf->pdf->Output($invoice->filename.".pdf", $downloadMode == "view" ? "I": "D");
					exit;
				}
			}
		}
	}
	echo "Invoice not found";
?>