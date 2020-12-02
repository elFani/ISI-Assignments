<?php
#header("Content-Security-Policy: default-src 'none', form-action 'self' https://assignments.isiphotos.com/isi.schedule.confirm.php *.officeapps.live.com ;");
#header("Content-Security-Policy: default-src 'none', form-action 'self' ;");
#
#
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
//	Load in the settings and includes
include ("includes/isi.schedule.tools.php");
#	--------------------------------------------
#		Overview
#		Process/confirm accept/decline/upload complete
#
#	Requests
#	GET (from email links or page links)
#	accept photography assignment
#	decline photography assignment
#	upload of images complete
#	adjust photographer points
#	POST
#	insert photography request into assignment table
#	OR
#	Editor page actions: accept, decline, assign, remind, gettynotify
#
#	--------------------------------------------
#	Issue:
#	--------------------------------------------
?>
<html>
<head>
<title>ISI Schedule Confirmation V1.6</title>
<META HTTP-EQUIV="Expires" CONTENT="Tue, 01 Jan 1980 1:00:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">

<link rel="stylesheet" href="includes/css/bootstrap.min.css">
<!--    Drop the page load.
<link rel="stylesheet" href="includes/css/bootstrap-datepicker3.min.css">
<link rel="stylesheet" href="includes/css/uploader.css">

<script type="text/javascript" src="includes/jquery_211.js"></script>
<script type="text/javascript" src="includes/js/bootstrap.min.js"></script>
-->

<!--	ISI Schedule related functions	-->
<script type="text/javascript" src="includes/isi.schedule.js"></script>

<!--
<script type="text/javascript" language="javascript" src="includes/CalendarPopup.js"></script>
<script src="includes/js/bootstrap-datepicker.min.js"></script>
-->
<script>
//	Issue diagnostic console.log messages.
var isConsoleTrace = false;
//var isConsoleTrace = true;

//	Google calendar and credentials based on league/collection.
//	For Cal/Stanford, there are sub-collections, but use only parent collection ID.
var runawayCounter = 0;

//	Return to Editor
function executeReturn() {
	tempObject = document.getElementById('formeditor');
	tempObject.submit();
	return;
}


</script>
</head>
<?php
#	Return to Editor button
$isReturnButton = false;
$isSendEmail = true;
#	Variables
$isValid = true;
$msgA = '';
$msgB = '';
$msgC = '';
$temp = '';
$dataArray = array();
$query = '';
$max = '';
$connection = '';
$currentRequest = '';
$currentAssignmentID = '';
$currentCollectionID = '';
$currentPhotographerID = '';
$currentAdjustment = '';

#	Assignment information extracted from database
$currentEventID = '';
$currentElapsed = 0;
$currentEditorID = '';
$currentEditorName = '';
$currentEditorEmail = '';
$currentGalleryName = '';
$currentPhotographerName = '';
$currentPhotographerEmail = '';
$photographerIDsList = '';
$photographerSpareIDs = '';
$nextPhotographerID = '';
$IDList = '';
$assignIDList = array();
$assignIDMax = 0;
$currentCollection = '';
$currentDate = '';
$currentDetails = '';
$currentTeam = '';
$tempArray = array();
$errorList = '';

#	Confirmation called by either GET or POST
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
print_r($dataArray);
#	$currentRequest = $_GET['req'];
#	$currentAssignmentID = $_GET['aid'];
#	$currentPhotographerID = $_GET['pid'];
	$currentRequest = $dataArray['req'];
	$currentAssignmentID = $dataArray['assignmentid'];
	$currentEditorID = $dataArray['editorid'];
	$currentGalleryName = $dataArray['galleryname'];
#	$currentCollectionID = $dataArray['collectionid'];
	$currentPhotographerID = $dataArray['photographerid'];
	$currentAdjustment = $dataArray['amount'];
#	First 'n', or Final 'y' upload. Null from uploader
	$currentFinalUpload = $dataArray['finalUpload'];
	if ($currentFinalUpload == "'n'") { $currentFinalUpload = 'n'; }
	if ($currentFinalUpload == "'y'") { $currentFinalUpload = 'y'; }
}
#	POST logic
$temp = count($_POST);
if ($temp > 0) {
#	Collect POST input from Editor page Data=none from editor page
	$temp = $_POST['data'];
	if ($temp != 'none') {
		$isSendEmail = false;
		$isReturnButton = true;
#		$temp = base64_decode($temp);
		$tempArray = explode("&data=", $temp);	
		$max = count($tempArray);
		for ($i = 1; $i < $max; $i++) {
			$temp = $i +1;
			$dataArray[$tempArray[$i]] = $tempArray[$temp];
		}
		$currentRequest = $dataArray['req'];
		$currentAssignmentID = $dataArray['assignmentid'];
		$currentEditorID = $dataArray['editorid'];
		$currentGalleryName = $dataArray['galleryname'];
		$currentPhotographerID = $dataArray['photographerid'];
		$currentPhotographerName = $dataArray['photographername'];
		$currentAdjustment = $dataArray['amount'];
		$currentEditor = $_POST['editoreditor'];
		$currentStatusFilter = $_POST['editorfilter'];
		$currentSort = $_POST['editorsort'];
	} else {
#	Collect POST input
		$currentRequest = $_POST['isi_request'];
		$temp = $_POST['isi_editor'];
		$tempArray = explode(":", $temp);
		$currentEditorID = $tempArray[0];
		$currentEditorEmail = $tempArray[1];
		$currentCollectionID = $tempArray[2];
		$currentCollection = $tempArray[3];
		$currentTeam = $_POST['isi_team'];
		$currentDetails = $_POST['isi_contact'];
		$currentDetails .= '(' . $_POST['isi_contactphone'] . ') ';
		$currentDetails .= $_POST['isi_type'] . ' ';
		$currentDetails .= $_POST['isi_location'] . ' ';
		$currentDetails .= $_POST['isi_date'] . ' ';
		$currentDetails .= $_POST['isi_timestart'] . '-' . $_POST['isi_timeend'] . ' ';
		$currentDetails .= $_POST['isi_details'];
		$currentDate = $_POST['isi_calendar_date'];
	}
}

#	Photographer points
$adjustArray = array('plustwo' => '+ 100','plusone' => '+ 50','neutral' => '+ 0','minusone' => '- 50','minustwo' => '- 100');


#	Return to Editor button
function returnButton() {
	print "<br>";
	print "<input class='btn btn-default' type='button' value='Return to Editor' onClick='javascript:executeReturn();'";
	print "<br>";
	return;
}

?>

<body style="margin-top: 20px;">
<!--	<br><br><font color=#CC0000>Note: AK Diagnosing. Extra messages issued.</font><br><br>	-->
<div class="container">
	<font size=+2>ISI Schedule Confirmation</font>&nbsp;&nbsp;<script type="text/javascript">if (isConsoleTrace) {document.write("<font size=-1 color=#3300FF>"); }</script>(2018-08-19)</font>
<div class="row"> 
<div class="col-xs-12">
<?php
print "AK002 ({$currentRequest})<br>";
#	Process request
#	Accept photography assignment
if ($currentRequest == 'accept') {
	if ($currentAssignmentID == '') {
		print "Confirmation F001: Assignment ID not supplied.<br>";
		$errorList .= "Confirmation F001: Assignment ID not supplied.<br>";
		$isValid = false;
	}
	if ($currentPhotographerID == '') {
		print "Confirmation F002: Photographer ID not supplied.<br>";
		$errorList .= "Confirmation F002: Photographer ID not supplied.<br>";
		$isValid = false;
	}
	if ($isValid) {
		if ($currentGalleryName != '') {
#			$tempArray = multipleAssignments($currentAssignmentID, $currentPhotographerID, $currentGalleryName);
#			$assignIDList = $tempArray[0];
#			$nextPhotographerID = $tempArray[1];
#			$photographerSpareIDs = $tempArray[2];
#			$assignIDMax = count($assignIDList);
#print "AK010 IDs({$assignIDMax}) NextID({$nextPhotographerID}) SpareIDs({$photographerSpareIDs})<br>";
			$tempArray = requestAccept($currentAssignmentID, $currentGalleryName, $currentPhotographerID, '', false);
print_r($tempArray);
		}
	}
	if (count($tempArray['output']) >0) {
		foreach ($tempArray['output'] as $item) {
			print $item;
		}	
	}
#	Return to Editor button
if ($isReturnButton) {
	returnButton();
}

}
#	Decline photography assignment
#	Check logic flow
#		Exhausted? email editor
#		Email next photography
#		Update the database
if ($currentRequest == 'decline') {
	if ($currentAssignmentID == '') {
		print "Confirmation F001: Assignment ID not supplied.<br>";
		$errorList .= "Confirmation F001: Assignment ID not supplied.<br>";
		$isValid = false;
	}
	if ($currentPhotographerID == '') {
		print "Confirmation F002: Photographer ID not supplied.<br>";
		$errorList .= "Confirmation F002: Photographer ID not supplied.<br>";
		$isValid = false;
	}
	if ($isValid) {
		$tempArray = requestDecline($currentAssignmentID, $currentGalleryName, $currentPhotographerID, $currentPhotographerName, false);
	}

	if (count($tempArray['output']) >0) {
		foreach ($tempArray['output'] as $item) {
			print $item;
		}	
	}

#	Return to Editor button
if ($isReturnButton) {
	returnButton();
}

}
#	ISI Uploader request
#	Email link request
#	Upload complete
if ($currentRequest == 'upload') {
	if ($currentAssignmentID == '') {
		print "F201: Assignment ID not supplied.<br>";
		$errorList .= "F201: Assignment ID not supplied.<br>";
		$isValid = false;
	}
	if ($isValid) {
		$tempUpload = 'n';
		if ($currentFinalUpload == 'y') { $tempUpload = 'y'; }
		$tempArray = requestFlagUpload($currentAssignmentID, $tempUpload, true);
	}
	if (count($tempArray['output']) >0) {
		foreach ($tempArray['output'] as $item) {
			print $item;
		}	
	}

#	Return to Editor button
if ($isReturnButton) {
	returnButton();
}

}

#	Adjust a photographer's points
if ($currentRequest == 'adjust') {
	if ($currentCollectionID == '') { print "F301: Collection ID not supplied.<br>"; $errorList .= "F301: Collection ID not supplied.<br>"; $isValid = false; }
	if ($currentPhotographerID == '') { print "F302: Photographer ID not supplied.<br>"; $errorList .= "F302: Photographer ID not supplied.<br>"; $isValid = false; }
	if ($isValid) {
		$points = $adjustArray[$currentAdjustment];
		$temp = sprintf("update schedule_organizationphotographers set league_photographer_points = league_photographer_points %s, league_photographer_assignment = league_photographer_assignment +1 where league_organization = '%s' and league_photographerID = '%s'", $points, $currentCollectionID, $currentPhotographerID);
#print "AK000 ({$temp})<br>";
		$max = executeSQL($connection, $temp);
		if ($max == true) {
			print "&nbsp;&nbsp;&nbsp;Collection/Photographer updated in database.<br>";
		} else {
			print "F303: Collection update ({$currentCollectionID}) failed ().<br>";
			$errorList .= "F303: Collection update ({$currentCollectionID}) failed ().<br>";
		}
	}
}

#	Insert assignment
#	STALE???
if ($currentRequest == 'insertrequest') {
	$temp = sprintf("insert into schedule_assignments values(' ','%s','%s','%s','%s','%s','%s','%s','%s','%s','{$STATUS_INITIAL}',NOW())", 'calendarID', $currentCollectionID, $currentCollection, $currentTeam, $currentDate, 'delivery', $currentDetails, $currentEditorID, 'photographers');
print "AK000 insertrequest({$temp})<br>";
	$max = executeSQL($connection, $temp);
	if ($max == true) {
		print "&nbsp;&nbsp;&nbsp;Assignment updated in database.<br>";
	} else {
		print "F304: Collection update ({$currentCollectionID}) failed ().<br>";
		$errorList .= "F304: Collection update ({$currentCollectionID}) failed ().<br>";
	}
}

#	Delete assignment request
if ($currentRequest == 'delete') {
	$temp = sprintf("delete from schedule_assignments where assign_ID = '%s' ", $currentAssignmentID);
#print "AK000 deleterequest({$temp})<br>";
	$max = executeSQL($connection, $temp);
	if ($max == true) {
		print "&nbsp;&nbsp;&nbsp;Assignment ({$currentAssignmentID}) deleted from database.<br>";
	} else {
		print "F305: Assignment delete ({$currentAssignmentID}) failed ().<br>";
		$errorList .= "F305: Assignment delete ({$currentAssignmentID}) failed ().<br>";
	}
}

#	Update Getty MEIDs from Getty
if ($currentRequest == 'gettyMEIDs') {
$test = '';
$temp = '';
$tempList = '';
$tempStatus = '';
	$connection = openDB();
	foreach ($_POST as $key => $item) {
#	Integers are assignment IDs, point to list_[ID]
		$test = gettype($key);
		if ($test == 'integer' && $item != '') {
			$temp = "list_{$key}";
			$tempList = $_POST[$temp];
#	Update assignments with MEID, regardless of status
			$query = "update schedule_assignments set assign_statusdate = NOW(), assign_statusaudit = 'gettyMEIDs:g:0', assign_meidID = '{$item}' where assign_ID in ({$tempList})";
			$max = executeSQL($connection, $query);
			if ($max >0) {
				print "Update successful {$tempList} with {$item}.<br>";
			}
#	Update assignments with MEID where STATUS_UPLOAD, set to STATUS_GETTY	
			$query = "update schedule_assignments set assign_statusdate = NOW(), assign_statusaudit = 'gettyMEIDs:g:0', assign_meidID = '{$item}', assign_status = '$STATUS_GETTY' where assign_ID in ({$tempList}) && assign_status = '$STATUS_UPLOAD'";
			$max = executeSQL($connection, $query);
		}
	
	}
	mysqli_close($connection);

}



#print "AK900 ({$currentRequest})<br>";
#	Close the database
mysqli_close($connection);

?>	

</div>
</div>
</div>
<form id="formeditor" action="isi.schedule.editor.php" method="post">
<?php
$temp = time();
#	"Reload" editor list of assignments
print "<input type='hidden' name='scheduleeditor' id='scheduleeditor' value='{$currentEditor}'>";
print "<input type='hidden' name='schedulefilter' id='schedulefilter' value=\"{$currentStatusFilter}\">";
print "<input type='hidden' name='schedulesort' id='schedulesort' value='{$currentSort}'>";
print "<input type='hidden' name='uniqueTime' id='uniqueTime' value='{$temp}'>";
?>
</form>

      <center><font size=-2>&#169;&nbsp;2020-<script>dateyy = new Date();document.write(dateyy.getFullYear());</script> 
       Andrew Katsampes</font></center>

</body>

</html>