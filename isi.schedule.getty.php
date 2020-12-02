<?php
#
#	List assignments needing Getty MEIDs
#
#
#
#	Load in the settings and includes
include ("includes/isi.schedule.tools.php");
?>
<html>
<head>
<title>ISI Schedule Getty List V1.6</title>
<META HTTP-EQUIV="Expires" CONTENT="Tue, 01 Jan 1980 1:00:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<meta http-equiv="Cache-control" content="no-cache">
<!-- <script type="text/javascript" src="transfer_settings/isi.schedule.js"></script>	-->
<!-- <script type="text/javascript" src="transfer_settings/jquery_211.js"></script>	-->
<link rel="stylesheet" href="includes/css/bootstrap.min.css">
<script>
//	Issue diagnostic console.log messages.
//var isConsoleTrace = false;
var isConsoleTrace = true;

function linkConfirm() {
	tempObject = document.getElementById('formgetty');
	tempObject.submit();
}

</script>
</head>
<body style="margin-top: 20px;">
<div class="container">
	<font size=+3>Assignments: Getty List</font>&nbsp;&nbsp;<script type="text/javascript">if (isConsoleTrace) {document.write("<font size=-1 color=#3300FF>"); }</script>(2020-09-03)</font>
</div>
<form id='formgetty' action='isi.schedule.confirm.php' method='post'>
<input type=hidden name='isi_request' value='gettyMEIDs'>
<input type='hidden' name='data' id='data' value='none'>
<center>
<table border='0' width='100%'>
<tr><td width='2%'>&nbsp;</td><td width='3%'>&nbsp;</td><td width='1%'>&nbsp;</td><td width='25%'>&nbsp;</td><td width='1%'>&nbsp;</td><td width='6%'>&nbsp;</td><td width='1%'>&nbsp;</td><td width='3%'>&nbsp;</td><td width='2%'>&nbsp;</td></tr>
<tr><td>&nbsp;</td><td>MEID</td><td>&nbsp;</td><td>Game / Event</td><td>&nbsp;</td><td>Location</td><td>&nbsp;</td><td>Photographer</td><td>&nbsp;</td></tr>
<?php
#	GET logic
$temp = count($_GET);
if ($temp > 0) {
#	Collect GET input
	$temp = $_GET['data'];
	$temp = base64_decode($temp);
	$tempArray = explode("&data=", $temp);	
	$max = count($tempArray);
	for ($i = 1; $i < $max; $i++) {
		$temp = $i +1;
		$dataArray[$tempArray[$i]] = $tempArray[$temp];
	}
$testGallery = urldecode($dataArray['gallery']);
}
$isVerified = false;
#	Collect assignments without MEID, but flagged for MEID processing.
#	Open the database
	$connection = openDB();
#	Collect assignments
	$query = "select assign_id as 'assignID', assign.assign_gallery as 'galleryName', assign.assign_location as 'location', assign.assign_status as 'status', DATE_FORMAT(assign.assign_date, '%c/%e/%Y') as 'eventDate', assign.assign_meid as 'assignMEIDFlag', assign.assign_meidID as 'assignMEID', photo.photographer_name as 'photoName' FROM schedule_assignments as assign join schedule_photographers as photo on assign.assign_photographerIDs = photo.photographer_ID where assign.assign_meid = 'y' && assign.assign_meidID = '' order by  assign.assign_date desc, assign.assign_gallery, assign.assign_location, assign.assign_ID";
#print "Assignments SQL ({$query})<br>";
	$data = executeSQL($connection, $query);
	$max = mysqli_num_rows($data);
#print "Assignments ({$max})<br>";
	$listIDs = '';
	$listComma = '';
	$currentGallery = '';
	$currentTR = '';
	if ($max > 0) {
		while ($row = mysqli_fetch_assoc($data)) {
			if ($row['galleryName'] == $testGallery) { $isVerified = true; }
			if ($currentGallery == '') {
				$currentGallery = $row['galleryName'];
				$currentTR = "<tr height='30'><td>&nbsp;</td><td><input type=text id='{$row['assignID']}' name='{$row['assignID']}' size='9'><input type=hidden id='list_{$row['assignID']}' name='list_{$row['assignID']}' value=\"{$listIDs}\"><td>&nbsp;</td><td>{$row['galleryName']}</td><td>&nbsp;</td><td>{$row['location']}</td><td>&nbsp;</td><td><font size=-1>{$row['photoName']}</font></td><td>&nbsp;</td></tr>";
			}
			if ($currentGallery == $row['galleryName']) {
				$listIDs = "{$listIDs}{$listComma}'{$row['assignID']}'";
				$listComma = ',';
				$currentTR = "<tr height='30'><td>&nbsp;</td><td><input type=text id='{$row['assignID']}' name='{$row['assignID']}' size='9'><input type=hidden id='list_{$row['assignID']}' name='list_{$row['assignID']}' value=\"{$listIDs}\"><td>&nbsp;</td><td>{$row['galleryName']}</td><td>&nbsp;</td><td>{$row['location']}</td><td>&nbsp;</td><td><font size=-1>{$row['photoName']}</font></td><td>&nbsp;</td></tr>";
			} else {
				print $currentTR;
				print "<tr><td></td><td colspan='7' height='1' bgcolor='#666666'></td><td height='1'></td></tr>";
				$currentGallery = $row['galleryName'];
				$listIDs = "'{$row['assignID']}'";
				$currentTR = "<tr height='30'><td>&nbsp;</td><td><input type=text id='{$row['assignID']}' name='{$row['assignID']}' size='9'><input type=hidden id='list_{$row['assignID']}' name='list_{$row['assignID']}' value=\"{$listIDs}\"><td>&nbsp;</td><td>{$row['galleryName']}</td><td>&nbsp;</td><td>{$row['location']}</td><td>&nbsp;</td><td><font size=-1>{$row['photoName']}</font></td><td>&nbsp;</td></tr>";
				$listComma = ',';
			}
		}	
	}	
	print $currentTR;
	print "<tr><td></td><td colspan='7' height='1' bgcolor='#666666'></td><td height='1'></td></tr>";


	mysqli_free_result($data);
	mysqli_close($connection);
?>
<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
<?php
if ($isVerified) {
	print "<tr><td>&nbsp;</td><td colspan=3><input class='btn btn-default' type='button' id='gettybutton' value='Click to send MEIDs to ISI' onClick=\"javascript:linkConfirm();\"></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
} else {
	print "<tr><td>&nbsp;</td><td colspan=3>Verification failed. {$testGallery}</td></tr>";
}

?>
</table></center>
</form>
</div>
</body>