<?php
	include_once "code/core/all.inc.php";
	include_once $root."/code/data/invoice.inc.php";
	include_once $root."/code/data/excelReader.inc.php";
	
	function upload_shutdown()
	{
		if($GLOBALS["tempFile"] && file_exists($GLOBALS["tempFile"])) {
			unlink($GLOBALS["tempFile"]);
		}
	}
	register_shutdown_function("upload_shutdown");
	$response = array();
	
	if(isset($_GET["ajax"])) {
		$fileName = urldecode(@$_SERVER['HTTP_X_FILE_NAME']);
		$extension = pathinfo($fileName, PATHINFO_EXTENSION);
		$fileSource = $tempFile = $root."/temp/temp.".$extension;
		copy("php://input", $tempFile);
	} else {
		if(!isset($_FILES["upload"]) || $_FILES["upload"]["size"] == 0)
			$response["error"] = "No file found";
		$fileSource = $_FILES["upload"]["tmp_name"];
		$fileName = $_FILES["upload"]["name"];
	}
		
	if(!$response["error"]) {
		$reader = new excelReader($fileSource);
		if($reader->error) {
			$response["error"] = $reader->error;
		} else if(count($reader->invoices) == 0) {
			$response["error"] = "No invoices found";
		} else {
			session_start();
			$_SESSION["invoices_".$fileName] = $reader->invoices;
			$invoices = $reader->invoices;
			ob_start();
			include "includes/invoiceList.inc.php";
			$response["html"] = ob_get_contents();
			ob_clean();
			$response["rootFile"] = $fileName;
		}
	}

if(isset($_GET["ajax"])) {
	echo json_encode($response);
} else {?>
<html>
	<head>
		<script type="text/javascript">
			parent.formUploadFinished(<?php echo json_encode($response)?>);
		</script>
	</head>
	<body></body>
</html>
<?php }?>