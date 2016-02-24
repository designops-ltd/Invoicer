<?php include_once "code/core/all.inc.php";
$debugUpload = isset($_GET["debugUpload"]);
$debugResponse = isset($_GET["debugResponse"]);?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="Invoicer, xls to pdf invoicing web application">
		<meta name="author" content="Eye Design Communications">
		<meta name="keywords" content="Invoicer, invoicing web application, SME invoicing tool">
	    <meta name="robots" content="follow">
	    <title>Invoicer</title>
	    <link href='http://fonts.googleapis.com/css?family=Fjalla+One' rel='stylesheet' type='text/css'>
	    <link href='http://fonts.googleapis.com/css?family=Roboto+Condensed:400,100' rel='stylesheet' type='text/css'>
	    <link rel="stylesheet" href="css/master.css">
		<script type="text/javascript" src="code/script/jquery-2.1.3.min.js"></script>
		<script type="text/javascript" src="code/script/filedrop.js"></script>
		<script type="text/javascript" >
			$(document).ready(function() {
				$(".togglenav").click(function() {
					$("#site-wrapper").addClass("show-nav")
					return false;
				})
				$("#menuClose").click(function() {
					$("#site-wrapper").removeClass("show-nav")
					return false;
				})
				<?php if(!$debugUpload) {?>
				$("#upload").change(function() {
					showProcessing(true);
					$("#mainform").submit();
					$("#mainform")[0].reset();
				});
				$("#uploadLink").click(function() {
					$("#upload").trigger("click");
					return false;
				});
				<?php }?>
				$("#popupClose").click(function() {
					$("#overlay").hide();
					return false;
				});
				<?php if(!$debugUpload) {?>
				var zone = new FileDrop('module', {input: false});
				zone.event('send', function (files) {
					showProcessing(true);
				  	files.each(function (file) {
						file.event('done', function (xhr) {
							try {
								var json = $.parseJSON(xhr.responseText);
								if(!json) {
									window.alert("Error: "+xhr.responseText);
								} else {
									uploadFinished(json);
									showProcessing(false)
								}
							} catch (e) {
								window.alert("Error: "+xhr.responseText);
								showProcessing(false)
							}
						});

						file.event('error', function (e, xhr) {
							alert('Error uploading ' + this.name + ': ' + xhr.status + ', ' + xhr.statusText);
						});
						file.sendTo('upload.php?ajax');
					});
				});
				<?php }?>
			})
		
			function showProcessing(state)
			{
				if(state) {
					$("#plusSign").hide();
					$("#processing").show();
				} else {
					$("#plusSign").show();
					$("#processing").hide();
				}
			}
		
			function getFilenameFromEl(el, type) {
				return "invoice.php?mode="+type+"&rootFile="+encodeURIComponent(rootFile)+"&invoice="+encodeURIComponent($(el).closest("li").find("a.view:first").html().replace(".pdf", ""));
			}
		
			function formUploadFinished(json) {
				uploadFinished(json);
			}
		
			var rootFile;
		
			function uploadFinished(json) {
				showProcessing(false);
				if(json.rootFile)
					rootFile = json.rootFile;
				if(json.html) {
					$("#overlay .message").html(json.html);
					$("#overlay a.view").click(function() {
						this.href = getFilenameFromEl(this, "view");
						this.target = "_blank";
					});
					$("#overlay a.save").click(function() {
						this.href = getFilenameFromEl(this, "save");
					});
					$("#overlay").show();
				}
				if(json.error != undefined)
					window.alert(json.error);
			}
		</script>
	</head>
	<body>
		<div id="site-wrapper">
			<div id="site-canvas">
				<div id="site-info">
					<div id="info-wrapper">
						<span class="clear" title="Close">
						    <a href="#" id="menuClose">
							    <svg x="0px" y="0px" viewBox="0 0 14 14" enable-background="new 0 0 14 14" xml:space="preserve">
							 		<polygon fill="#F3F4F5" points="14,13.3 7.7,7 14,0.7 13.3,0 7,6.3 0.7,0 0,0.7 6.3,7 0,13.3 0.7,14 7,7.7 13.3,14 "></polygon>
								</svg>
						    </a>
						</span>
						<h2>INVOICER<sup>V1.0</sup></h2>
						<h3>XLS to PDF Invoicing Web App</h3>
						<p>Converts Excel cost spreadsheets into press quality PDF invoices. Drag and drop or upload your spreadsheet and within seconds invoices are ready for download.</p>
						<h3>How it works</h3>
						<ol>
							<li><a href="http://invoicer.eye-design.co.uk/templates/Sample-Costs-Spreadsheet.xls">Download</a> the sample spreadsheet</li>
							<li>Edit some line data</li>
							<li>Drag invoice into the page*</li>
						</ol>
						<p><em>*HTML5 browser required, fallback file browse function for older browsers.</em></p>
						<h3>Commercial Applications</h3>
						<p>Invoicer can be integrated into larger Enterprise / E-commerce workflow where dynamic PDF capabilities are required.</p>
						<p><a href="http://www.eye-design.co.uk/contact.php">Contact us</a> to discuss a commercial integration.</p>
					</div>
				</div>
				<div class="overlay fade" id="overlay" style="display:none">
			        <span class="clear" title="Close">
				    <a href="#" id="popupClose">
					    <svg x="0px" y="0px" viewBox="0 0 14 14" enable-background="new 0 0 14 14" xml:space="preserve">
					 		<polygon fill="#F3F4F5" points="14,13.3 7.7,7 14,0.7 13.3,0 7,6.3 0.7,0 0,0.7 6.3,7 0,13.3 0.7,14 7,7.7 13.3,14 "/>
						</svg>
				    </a>
				    </span>
			        <div class="message"></div>
			    </div>
				<?php if(!$debugUpload) {?>
			    	<div id="header">
				        <h1><a href="#" class="togglenav">INVOICER</a></h1>
				    </div>
					<a href="#" id="uploadLink">
				        <div id="module" title="drag and drop file or click to browse">
				            <svg x="0px" y="0px" viewBox="0 0 100 125" enable-background="new 0 0 100 125" xml:space="preserve">
				                <path fill="#646C7F" d="M39,2.4v25.9C39,34.6,34.6,39,28.3,39H2.4L39,2.4 M40,0L0,40h28.3C35.1,40,40,35.1,40,28.3V0L40,0z" />
				                <path fill="#646C7F" d="M88,0H44v1h44c6.1,0,11,4.9,11,11v101c0,6.1-4.9,11-11,11H12c-6.1,0-11-4.9-11-11V44H0v69c0,6.6,5.4,12,12,12h76c6.6,0,12-5.4,12-12V12C100,5.4,94.6,0,88,0z" />
				               	<polygon id="plusSign" fill="#646C7F" points="89,104 80,104 80,95 79,95 79,104 70,104 70,105 79,105 79,114 80,114 80,105 89,105" />
								<path display="inline" fill="#646C7F" id="processing" style="display:none" d="M80,95v1c4.5,0.3,8,4,8,8.5c0,4.7-3.8,8.5-8.5,8.5c-4.5,0-8.2-3.5-8.5-8h-1c0.3,5,4.4,9,9.5,9c5.2,0,9.5-4.3,9.5-9.5C89,99.4,85,95.3,80,95z">
									<animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 79.5 104.5" to="360 79.5 104.5" dur="0.5s" repeatCount="indefinite" />
								</path>
				            </svg>
				        </div>
					</a>
				<?php }?>
				<form id="mainform" method="post" enctype="multipart/form-data" action="upload.php" target= "uploadFrame"><input type="file" name="upload" id="upload" <?php if(!$debugUpload) {?>style="visibility:hidden"<?php }?>/><?php if($debugUpload) {?><input type="submit"/><?php }?></form>
				<iframe name="uploadFrame" style="width: 950px; height: 800px; <?php if(!$debugResponse) {?>display:none<?php }?>"></iframe>
			</div>
		</div>
	</body>
</html>