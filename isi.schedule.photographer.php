<?php
#
#	Base isi.schedule.php
#	Also uses isi.authenticate.php
#	Also uses isi.debug.php
#	/transfer_settings    
#		CalendarPopup.js
#		isi.features_transfer.php
#		isi.initialize.transfer.php
#		jquery_211.js
#
#	Load in the settings and includes
include ("includes/isi.schedule.tools.php");
#	Assignments array indexes.
$INDEX_STATUS	= 0;
$INDEX_DIFFERENCE	= 1;
$INDEX_GALLERY	= 2;
$INDEX_PHOTOGRAPHERID	= 3;
$INDEX_PHOTOGRAPHERNAME	= 4;
$INDEX_DELIVERY	= 5;
$INDEX_ASSIGNMENTID	= 6;
$INDEX_DETAILS	= 7;
$INDEX_RSVP		= 8;
$INDEX_MEIDFLAG	= 9;
$INDEX_MEID		= 10;
#	--------------------------------------------
#		Overview
#		List assignments associated with this an editor
#		
#		To Do
#		
#	--------------------------------------------
#	Issue:
#	--------------------------------------------

#	Collect input photographer ID
#print_r($_POST);
$currentPhotographer = $_POST['schedulephotographer'];
$currentStatusFilter = $_POST['schedulefilter'];
if ($currentStatusFilter == '') { $currentStatusFilter = "'{$STATUS_REQUEST}','{$STATUS_ASSIGNED}','{$STATUS_UPLOAD}','{$STATUS_WAITING}'"; }

#	Delivery method
$deliveryArray = array("b" => "Box","ps" => "PhotoShelter","bs" => "Box<br>PhotoShelter","e" => "eMail");

?>
<html>
<head>
<title>ISI Scheduler Photographer Assignments Dashboard V1.6</title>
<META HTTP-EQUIV="Expires" CONTENT="Tue, 01 Jan 1980 1:00:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">

<!--	ISI Schedule related functions	-->
<style>
<!--
a:link { text-decoration: none!important; }
-->
</style>
<script type="text/javascript" src="includes/isi.schedule.js"></script>
<script type="text/javascript" src="includes/jquery_211.js"></script>

<link rel="stylesheet" href="includes/css/bootstrap.min.css">
<!--<script type="text/javascript" src="includes/js/bootstrap.min.js"></script>	-->

<script>
//	Check javascript version
var	isStale = false;
if (jsVersion != '1.20') { isStale = true; }

//	Issue diagnostic console.log messages.
//var isConsoleTrace = true;
var isConsoleTrace = false;
//	Status label object associated with most recent action
var	currentStatusObject = '';

//	Expand/Contract assignment details
function toggleAssignment(inputLink, inputTH, inputTR) {
	if (isConsoleTrace) { console.log("toggleAssignment TR("+inputTR+")"); }
	linkObject = document.getElementById(inputLink);
	thObject = document.getElementById(inputTH);
	trObject = document.getElementById(inputTR);
	if (trObject.style.display == 'none') {
		thObject.style.display = 'table-row';
		trObject.style.display = 'table-row';
		linkObject.innerHTML = '-';
	} else {
		thObject.style.display = 'none';
		trObject.style.display = 'none';
		linkObject.innerHTML = '+';
	}
return;
}

//	Filter assignments
//	Including reset filter to show all.
function executeFilter(inputStatus) {
	if (isConsoleTrace) { console.log("schedulefilter ("+inputStatus+")"); }
	tempObject = document.getElementById('schedulefilter');
	tempObject.value = "'"+inputStatus+"'";
//	if (inputStatus == 'Reset') { tempObject.value = "'a','b','c','d','f','g'"; }
<?php
	print "if (inputStatus == 'Reset') { tempObject.value = \"'{$STATUS_REQUEST}','{$STATUS_ASSIGNED}','{$STATUS_UPLOAD}','{$STATUS_WAITING}'\"; }";
?>
	executeRefresh();
	return;
}

//	Refresh/reload the page
function executeRefresh() {
//	Reload the page, to remove from assignment list.
	tempObject = document.getElementById('formphotographer');
	tempObject.submit();
	return;
}

//	Reset the status labels (<span>s) associated with actions...
function resetStatus() {
	if (currentStatusObject == '') { return; }
	currentStatusObject.innerHTML = '';
	currentStatusObject = '';
	return;
}

//	Action: Flag assignment: First and any upload of images
//	actionUpload('{$itemArray[$INDEX_ASSIGNMENTID]}', 'n' (first))
//	actionUpload('{$itemArray[$INDEX_ASSIGNMENTID]}', 'y' (final))
function actionUpload(inputID, inputFinal) {
	if (isConsoleTrace) { console.log("actionUpload ("+inputID+") Final?("+inputFinal+")"); }
	resetStatus();
//	Status below the link.
	statusObject = document.getElementById('statuslabel_'+inputID);
	currentStatusObject = statusObject;
	flagAssignment(inputID, inputFinal, statusObject);
	return;
}
//	Action: Photographer accepts assignment
//	buildData('accept', inputID, inputGallery, inputPhotographerID, inputPhotographerName)
//	actionAccept('{$itemArray[$INDEX_ASSIGNMENTID]}','{$itemArray[$INDEX_GALLERY]}','{$itemArray[$INDEX_PHOTOGRAPHERID]}','{$itemArray[$INDEX_PHOTOGRAPHERNAME]}');\">Accept</a>";
function actionAccept(inputID, inputGallery, inputPhotoID, inputPhotoName) {
	if (isConsoleTrace) { console.log("actionAccept ("+inputPhotoID+":"+inputPhotoName+")"); }
	resetStatus();
//	Status below the link.
	statusObject = document.getElementById('statuslabel_'+inputID);
	currentStatusObject = statusObject;
	acceptAssignment(inputID, inputGallery, inputPhotoID, inputPhotoName, statusObject);
	return;
}

//	Action: Photographer declines assignment
//	buildData(inputRequest, inputID, inputGallery, inputPhotographerID, inputPhotographerName)
function actionDecline(inputID, inputGallery, inputPhotoID, inputPhotoName) {
	if (isConsoleTrace) { console.log("actionDecline ("+inputPhotoID+":"+inputPhotoName+")"); }
	resetStatus();
//	Status below the link.
	statusObject = document.getElementById('statuslabel_'+inputID);
	currentStatusObject = statusObject;
	declineAssignment(inputID, inputGallery, inputPhotoID, inputPhotoName, statusObject);
	return;
}


</script>

</head>
<?php
#	Array of results, grouped by collection
	$assignments = array();
	$temp = '';
	$tempBG = '';
	$currentAction = '';
	$intDifference = 0;
	
#	Open the database
	$connection = openDB();
#	Get list of assignments for input photographer
	$query = "select assign.assign_organization as 'organizationName', assign.assign_gallery as 'galleryName', assign.assign_status as 'status', assign.assign_ID as 'assignID', assign.assign_editorID as 'editorID', assign.assign_delivery as 'delivery', photo.photographer_ID as 'photoID', photo.photographer_name as 'photoName', TIMESTAMPDIFF(HOUR,assign.assign_date,NOW()) as 'difference', assign.assign_delivery as 'delivery', assign.assign_details as 'assignDetails', assign.assign_rsvp as 'assignRSVP', assign.assign_meid as 'meidFlag', assign.assign_meidID as 'meidID' FROM schedule_assignments as assign join schedule_photographers as photo on assign.assign_photographerIDs = photo.photographer_ID where photo.photographer_ID = '{$currentPhotographer}' && assign.assign_status in ({$currentStatusFilter}) order by difference desc, assign.assign_status";

#	ONLY NWSL
#	$query = "select assign.assign_organization as 'organizationName', assign.assign_gallery as 'galleryName', assign.assign_status as 'status', assign.assign_ID as 'assignID', assign.assign_editorID as 'editorID', assign.assign_delivery as 'delivery', photo.photographer_ID as 'photoID', photo.photographer_name as 'photoName', TIMESTAMPDIFF(HOUR,assign.assign_date,NOW()) as 'difference', assign.assign_delivery as 'delivery', assign.assign_details as 'assignDetails', assign.assign_rsvp as 'assignRSVP', assign.assign_meid as 'meidFlag', assign.assign_meidID as 'meidID' FROM schedule_assignments as assign join schedule_photographers as photo on assign.assign_photographerIDs = photo.photographer_ID where photo.photographer_ID = '{$currentPhotographer}' && assign.assign_status in ({$currentStatusFilter}) && assign.assign_organization = 'NWSL' order by difference desc, assign.assign_status";
#print "Query ({$query})<br>";
	$data = executeSQL($connection, $query);
	$max = mysqli_num_rows($data);
#print "Rows ({$max})<br>";
	if ($max > 0) {
		while ($row = mysqli_fetch_assoc($data)) {
#print_r($row);
			$tempStatus = $row['status'];
			if ($row['status'] == $STATUS_ASSIGNED && $row['difference'] > 8) { $tempStatus = $STATUS_WAITING; }
			$tempRow = array($tempStatus, $row['difference'], $row['galleryName'], $row['photoID'], $row['photoName'], $row['delivery'], $row['assignID'], $row['assignDetails'], $row['assignRSVP'], $row['meidFlag'], $row['meidID']);
			array_push($assignments, $tempRow);
		}
	}
#print_r($assignments);
mysqli_free_result($data);
mysqli_close($connection);

?>
<body style="margin-top: 20px;">
<!--	<br><br><font color=#CC0000>Note: AK Diagnosing. Extra messages issued.</font><br><br>	-->
<div class="container">
	<font size=+3>Assignments Dashboard: Photographer</font>&nbsp;&nbsp;<script type="text/javascript">if (isConsoleTrace) {document.write("<font size=-1 color=#3300FF>"); }</script>(2020-10-30)</font>
<br><input class="btn btn-default" type="button" id="staleButton" style="display: none;" value="Stale logic. Click to refresh." onClick="javascript:document.location.reload(true);">

<div class="row"> 
<br>
<!--	Legend	-->
<table width=95% border='0'>
<tr><td width=25%>&nbsp;</td><td width=45%>&nbsp;</td><td width=35%>&nbsp;</td></tr>
<tr><td>&nbsp;</td>
<td valign='top'>
	<table border=0 width='100%'><tr><td width='6%'></td><td width='1%'>&nbsp;</td><td>Legend</td></tr>
<?php
#	print "<tr><td bgcolor='{$BGCOLORS[$STATUS_INITIAL]}' onclick=javascript:executeFilter('{$STATUS_INITIAL}');>&nbsp</td><td>&nbsp;</td><td>No editor, no photographers</td></tr>";
#	print "<tr><td bgcolor='{$BGCOLORS[$STATUS_INITIAL]}'>&nbsp</td><td>&nbsp;</td><td>No editor, no photographers</td></tr>";
	print "<tr><td bgcolor='{$BGCOLORS[$STATUS_REQUEST]}' onclick=javascript:executeFilter('{$STATUS_REQUEST}');>&nbsp</td><td>&nbsp;</td><td>Editor recruiting photographer(s)</td></tr>";
	print "<tr><td bgcolor='{$BGCOLORS[$STATUS_ASSIGNED]}' onclick=javascript:executeFilter('{$STATUS_ASSIGNED}');>&nbsp</td><td>&nbsp;</td><td>Photographer assigned.</td></tr>";
	print "<tr><td bgcolor='{$BGCOLORS[$STATUS_WAITING]}' onclick=javascript:executeFilter('{$STATUS_ASSIGNED}');>&nbsp</td><td>&nbsp;</td><td>Waiting for image upload</td></tr>";
	print "<tr><td bgcolor='{$BGCOLORS[$STATUS_UPLOAD]}' onclick=javascript:executeFilter('{$STATUS_UPLOAD}');>&nbsp</td><td>&nbsp;</td><td>Images uploaded. Waiting for Final upload.</td></tr>";
#	print "<tr><td bgcolor='{$BGCOLORS[$STATUS_GETTY]}' onclick=javascript:executeFilter('{$STATUS_GETTY}');>&nbsp</td><td>&nbsp;</td><td>Push images to Getty and notify</td></tr>";
#	print "<tr><td bgcolor='{$BGCOLORS[$STATUS_GETTY]}'>&nbsp</td><td>&nbsp;</td><td>Push images to Getty and notify</td></tr>";
#	print "<tr><td bgcolor='{$BGCOLORS[$STATUS_COMPLETE]}' onclick=javascript:executeFilter('{$STATUS_COMPLETE}');>&nbsp</td><td>&nbsp;</td><td>Archive (Completed assignments)</td></tr>";
#	print "<tr><td bgcolor='{$BGCOLORS[$STATUS_COMPLETE]}'>&nbsp</td><td>&nbsp;</td><td>Archive (Completed assignments)</td></tr>";
	print "<tr><td><a href=javascript:executeFilter('Reset');>Reset</a></td><td>&nbsp;</td><td>Reset filter, show all assignments</td></tr>";
	print "<tr><td>&nbsp</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
	print "<tr><td>&nbsp</td><td>&nbsp</td><td><input class='btn btn-default' type='button' value='Refresh/Reload' onClick='javascript:executeRefresh();'>&nbsp;</td></tr>";
	print "<tr><td>&nbsp</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
?>
	</table>
</td>
<td>&nbsp;</td></tr>
</table>
<!--	Assignments		-->
<table border='0'>
<tr><td width=2%>&nbsp;</td><td width=3%>&nbsp;</td><td width=1%>&nbsp;</td><td>&nbsp;</td><td width=2%>&nbsp;</td><td>&nbsp;</td><td width=2%>&nbsp;</td><td>&nbsp;</td><td width=2%>&nbsp;</td><td>&nbsp;</td><td width=2%>&nbsp;</td><td>&nbsp;</td><td width=3%>&nbsp;</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;Action links</td><td>&nbsp;</td><td>&nbsp;Gallery Name</td><td>&nbsp;</td><td>Delivery Method</td><td>&nbsp;</td><td>Spare</td><td>&nbsp;</td><td>Spare</td><td>&nbsp;</td></tr>
<tr><td></td><td></td><td height='1'></td><td colspan='9' height='1' bgcolor='#666666'></td><td></td><td></td></tr>
<?php
#	Spin through the assignments
$linkActions = '';
	foreach ($assignments as $itemArray) {
		$linkActions = 'sss';
#   Status: Initial (SNO)
		if ($itemArray[$INDEX_STATUS] == $STATUS_INITIAL) { $linkActions = 'SNO'; }
#   Status: Request/Recruit
		if ($itemArray[$INDEX_STATUS] == $STATUS_REQUEST) {
			$linkActions = "<a href=\"javascript:actionAccept('{$itemArray[$INDEX_ASSIGNMENTID]}','{$itemArray[$INDEX_GALLERY]}','{$itemArray[$INDEX_PHOTOGRAPHERID]}','{$itemArray[$INDEX_PHOTOGRAPHERNAME]}');\">Accept</a>";
			$linkActions .= "<br><a href=\"javascript:actionDecline('{$itemArray[$INDEX_ASSIGNMENTID]}','{$itemArray[$INDEX_GALLERY]}','{$itemArray[$INDEX_PHOTOGRAPHERID]}','{$itemArray[$INDEX_PHOTOGRAPHERNAME]}');\">Decline</a>";
			$linkActions .= "<br><span class='text-success' id='statuslabel_{$itemArray[$INDEX_ASSIGNMENTID]}'>&nbsp;</span>";
		}
#   Status: Assigned
		if ($itemArray[$INDEX_STATUS] == $STATUS_ASSIGNED && $intDifference > 0) { $linkActions = 'SNO'; }
		if ($itemArray[$INDEX_STATUS] == $STATUS_ASSIGNED && !($intDifference > 0)) { $linkActions = ''; }
#   Status: Waiting
		if ($itemArray[$INDEX_STATUS] == $STATUS_WAITING) {
			$linkActions = "<a href=\"javascript:actionUpload('{$itemArray[$INDEX_ASSIGNMENTID]}', 'n', 'statuslabel_{$itemArray[$INDEX_ASSIGNMENTID]}');\">First Upload</a>";
			$linkActions .= "<br><span class='text-success' id='statuslabel_{$itemArray[$INDEX_ASSIGNMENTID]}'>&nbsp;</span>";
	}
#   Status: Upload
		if ($itemArray[$INDEX_STATUS] == $STATUS_UPLOAD) {
			$linkActions = "<a href=\"javascript:actionUpload('{$itemArray[$INDEX_ASSIGNMENTID]}', 'y');\">Final Upload</a>";
			$linkActions .= "<br><span class='text-success' id='statuslabel_{$itemArray[$INDEX_ASSIGNMENTID]}'>&nbsp;</span>";
		}
#   Status: Getty (SNO)
		if ($itemArray[$INDEX_STATUS] == $STATUS_GETTY) { $linkActions = 'SNO'; }
#   Status: Archive/Complete (SNO)
		if ($itemArray[$INDEX_STATUS] == $STATUS_COMPLETE) { $linkActions = 'SNO'; }
#	Print out assignment
		print "<tr><td align='center'><a id='A_{$itemArray[$INDEX_ASSIGNMENTID]}' href=\"javascript:toggleAssignment('A_{$itemArray[$INDEX_ASSIGNMENTID]}','TH_{$itemArray[$INDEX_ASSIGNMENTID]}','TR_{$itemArray[$INDEX_ASSIGNMENTID]}');\">+</a></td><td bgcolor={$BGCOLORS[$itemArray[$INDEX_STATUS]]}><center><font size=-1>{$itemArray[$INDEX_ASSIGNMENTID]}</font></center></td><td>&nbsp;</td><td align='center' valign='top'>{$linkActions}</td><td>&nbsp;</td><td valign='top'>&nbsp;{$itemArray[$INDEX_GALLERY]}</td><td>&nbsp;</td><td valign='top'>{$deliveryArray[$itemArray[$INDEX_DELIVERY]]}</a></td><td>&nbsp;</td><td valign='top'>&nbsp;</td><td>&nbsp;</td><td valign='top'>{$itemArray[$INDEX_DIFFERENCE]}</td><td>&nbsp;</td></tr>";
		print "<tr><td></td><td></td><td height='1'></td><td colspan='9' height='1' bgcolor='#666666'></td><td></td><td></td></tr>";
#	Expanded assignment: Details
		print "<tr id='TH_{$itemArray[$INDEX_ASSIGNMENTID]}' style='display:none' bgcolor='#EEEEEE'><td bgcolor='#FFFFFF'>&nbsp;</td><td bgcolor='#FFFFFF'>&nbsp;</td><td bgcolor='#FFFFFF'>&nbsp;</td><td colspan=3>Details</td><td>&nbsp;</td><td colspan=2 align=left><font size=-2>Spare</font></td><td><font size=-2>Spare</font></td><td>&nbsp;</td><td><font size=-2>Spare</font></td><td bgcolor='#FFFFFF'>&nbsp;</td></tr>";
		print "<tr id='TR_{$itemArray[$INDEX_ASSIGNMENTID]}' style='display:none' bgcolor='#EEEEEE'><td bgcolor='#FFFFFF'>&nbsp;</td><td bgcolor='#FFFFFF'>&nbsp;</td><td bgcolor='#FFFFFF'>&nbsp;</td><td colspan=3><textarea class='form-control' rows='3' cols='60' id='details_{$itemArray[$INDEX_ASSIGNMENTID]}' name='details_{$itemArray[$INDEX_ASSIGNMENTID]}'>{$itemArray[$INDEX_DETAILS]}</textarea></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>...</td><td bgcolor='#FFFFFF'>&nbsp;</td></tr>";
		print "<tr><td></td><td></td><td height='2'></td><td colspan='9' height='1' bgcolor='#666666'></td><td height='1'></td></tr>";
	}
			
?>
</table>

<form id="formphotographer" action="isi.schedule.photographer.php" method="post">
<?php
$temp = time();
#	"Reload" editor list of assignments
print "<input type='hidden' name='schedulephotographer' id='schedulephotographer' value='{$currentPhotographer}'>";
print "<input type='hidden' name='schedulefilter' id='schedulefilter' value=\"{$currentStatusFilter}\">";
print "<input type='hidden' name='schedulesort' id='schedulesort' value='{$currentSort}'>";
print "<input type='hidden' name='uniqueTime' id='uniqueTime' value='{$temp}'>";
?>
</form>

</div>
</div>
<br><br>

      <center><font size=-2>&#169;&nbsp;2020-<script>dateyy = new Date();document.write(dateyy.getFullYear());</script> 
       Andrew Katsampes</font></center>

</body>
<script>
//	Javascript is stale. Display the refresh button
if (isStale == true) {
	tempObject = document.getElementById('staleButton');
	tempObject.style.display = "inline";
}
</script>
</html>