<?php
#	--------------------------------------------
#	Overview
#	Login to ISI Schedule
#	If successful, then redirect to isi.schedule.editor.php, which lists assignments.
#		
#	To Do
#		
#	--------------------------------------------
#	Issue:
#	--------------------------------------------
$temp = count($_POST);
$input = '';
if ($temp >0) {
	$input = $_POST['isi_input'];
	if ($input != '') { $output = password_hash($input, PASSWORD_DEFAULT); }
}

?>
<html>
<head>
<title>ISI Schedule IDB V1.4</title>
<META HTTP-EQUIV="Expires" CONTENT="Tue, 01 Jan 1980 1:00:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">

<link rel="stylesheet" href="includes/css/bootstrap.min.css">
<link rel="stylesheet" href="includes/css/uploader.css">

<!--	AJAX/JSON	-->
<script type="text/javascript" src="includes/jquery_211.js"></script>
<script type="text/javascript" src="includes/js/bootstrap.min.js"></script>
<script>

//	Execute conversion
function executeIDB() {
	tempObject = document.getElementById('isiform');
	tempObject.submit();
}




</script>
</head>
<body style="margin-top: 20px;">
<!--	<br><br><font color=#CC0000>Note: AK Diagnosing. Extra messages issued.</font><br><br>	-->
<!--	<br><br><font color=#CC0000>Note: AK Tinkering.</font><br><br>	-->
<div class="container">
<!--	<h1>Photo Uploader</h1>	-->
	<font size=+3>ISI Photos Schedule</font>&nbsp;&nbsp;(2020-07-04)
<div class="row"> 
<div class="col-xs-12">
<form id="isiform" action="isi.schedule.idb.php" method="post">

<div class="form-group" style="width:450px">
	<label>Input</label>
<?php
	print "<input class='form-control' type='text' value='{$input}' name='isi_input' id='isi_input' size='30'>";
?>
	</div>

<div class="form-group" style="width:450px">
	<label>Output</label>
<?php
	print "<textarea class='form-control' rows='3' cols='60' id='isi_output' name='isi_output'>{$output}</textarea>";
?>
	</div>
<div class="form-group" style="width:450px">
<input class='btn btn-default' type='button' id='isi_execute' value='Execute' onClick='javascript:executeIDB();'>
</div>


</form>
<br><br>

      <center><font size=-2>&#169;&nbsp;2020-<script>dateyy = new Date();document.write(dateyy.getFullYear());</script> 
       Andrew Katsampes</font></center>

</body>
</html>

