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
$INDEX_STATUS	= 0;
$INDEX_DIFFERENCE	= 1;
$INDEX_GALLERY	= 2;
$INDEX_PHOTOGRAPHERID	= 3;
$INDEX_PHOTOGRAPHERNAME	= 4;
$INDEX_PHOTOGRAPHEREMAIL= 5;
$INDEX_DELIVERY	= 6;
$INDEX_EVENTDATE	= 7;
$INDEX_EVENTDATEEND	= 8;
$INDEX_ASSIGNMENTID	= 9;
$INDEX_EDITORID	= 10;
$INDEX_DETAILS	= 11;
#	RSVP may be temporary
$INDEX_RSVP		= 12;
$INDEX_IDLIST	= 13;
$INDEX_EVENTTIME = 14;
$INDEX_REQUESTOR = 15;
$INDEX_MEIDFLAG	= 16;
$INDEX_MEID		= 17;
$INDEX_IMAGESCOUNT = 18;
$INDEX_EDITORID = 19;
$INDEX_LOCATION = 20;
$INDEX_CONTACT = 21;
$INDEX_CONTACTEMAIL = 22;
$INDEX_CONTACTPHONE = 23;

#	--------------------------------------------
#		Overview
#		List assignments associated with this an editor
#		
#		To Do
#		
#	--------------------------------------------
#	Issue:
#	--------------------------------------------

#	Collect editor, filter (all,a,b,c,d,f), sort order (status or date)
#	Editor ID:Name:Email:Organization
$currentEditor = '';
$currentStatusFilter = "'{$STATUS_INITIAL}','{$STATUS_REQUEST}','{$STATUS_ASSIGNED}','{$STATUS_UPLOAD}','{$STATUS_GETTY}'";
$currentSort = 'assign.assign_status';
#	GET logic
$temp = count($_GET);
if ($temp > 0) {
#	Collect/decode GET input
#print "GET<br>";
#print_r($_GET);
	$temp = $_GET['data'];
	$temp = base64_decode($temp);
	$tempArray = explode("&data=", $temp);	
	$max = count($tempArray);
	for ($i = 1; $i < $max; $i++) {
		$temp = $i +1;
		$dataArray[$tempArray[$i]] = $tempArray[$temp];
	}
	$currentEditor = $dataArray['editor'];
}
#	POST logic
$temp = count($_POST);
if ($temp > 0) {
#print "POST<br>";
#print_r($_POST);
	$currentEditor = $_POST['scheduleeditor'];
	$currentStatusFilter = $_POST['schedulefilter'];
	$currentSort = $_POST['schedulesort'];
}
#print "<br>Filter ({$currentStatusFilter})<br>";

#	Function
#$methods = array('ISI Photos' => 'selected', 'Box' => 'selected', 'Box and ISI Photos' => 'selected', 'eMail' => 'selected', 'Other' => 'selected');
function selectMethods($inputID, $inputMethod) {
$methods = array();
if ($inputMethod == '') { $inputMethod = 'i'; }
$methods[$inputMethod] = 'selected';
$temp = "<select class='form-control' name='delivery_{$inputID}' id='delivery_{$inputID}' onChange='//javascript:changeDelivery(this);'>";
$temp .= "<option value='' >Select image delivery method</option>";
$temp .= "<option value='i' {$methods['i']}>ISIPhotos.com</option>";
$temp .= "<option value='b' {$methods['b']}>Box</option>";
$temp .= "<option value='bi' {$methods['bi']}>Box and ISI Photos</option>";
$temp .= "<option value='e' {$methods['e']}>eMail</option>";
$temp .= "<option value='o' {$methods['o']}>Other</option>";
$temp .= "</select>";
	return $temp;
}


#	Colors based on status
#$bgColors = array("request" => "#DC2127","assigned" => "#FCB878","upload" => "#51B749");
#$bgColors = array("request" => "#DC2127","assigned" => "#F7C026","upload" => "#51B749", "a" => "#666666","b" => "#DC2127","c" => "#F7C026","d" => "#51B749");
#$bgColors = array("request" => "#DC2127","assigned" => "#F7C026","upload" => "#51B749", "a" => "#991111","b" => "#EE2233","c" => "#EEEE33","d" => "#51B749","e" => "#2C78B5");
$eventColors = array("request" => "11","assigned" => "5","upload" => "10");
#	Delivery method
$deliveryArray = array("b" => "Box","ps" => "PhotoShelter","bs" => "Box<br>PhotoShelter","e" => "eMail","i" => "ISI Photos","bi" => "Box<br>ISI Photos","o" => "Other");

?>
<html>
<head>
<title>ISI Schedule Editor Assignments Dashboard V1.6</title>
<META HTTP-EQUIV="Expires" CONTENT="Tue, 01 Jan 1980 1:00:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<meta http-equiv="Cache-control" content="no-cache">
<link rel="icon" type="image/png" href="https://assignments.isiphotos.com/icons/ISI_logo.png" />

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
var isConsoleTrace = false;
//var isConsoleTrace = true;
//	Array of TR ids for each organization
var organizationTRs = new Object();
//	Status label object associated with most recent action
var	currentStatusObject = '';
//	Assignment status
<?php
print "var STATUS_INITIAL = '{$STATUS_INITIAL}';\n";
print "var STATUS_REQUEST = '{$STATUS_REQUEST}';\n";
print "var STATUS_ASSIGNED = '{$STATUS_ASSIGNED}';\n";
print "var STATUS_UPLOAD = '{$STATUS_UPLOAD}';\n";
print "var STATUS_WAITING = '{$STATUS_WAITING}';\n";
print "var STATUS_GETTY = '{$STATUS_GETTY}';\n";
print "var STATUS_COMPLETE = '{$STATUS_COMPLETE}';\n";
?>

//	Link to ISI Calendar
function linkCalendar() {
	tempObject = document.getElementById('formcalendar');
	tempObject.submit();
}

//	Link to ISI Schedule Photographer
function linkPhotographer(inputID) {
	tempObject = document.getElementById('schedulephotographer');
	tempObject.value = inputID;
	tempObject = document.getElementById('formphotographer');
	tempObject.submit();
}

//	Link to ISI Schedule to request photographer(s)
function linkRequest(inputID, inputEditorID, inputOrganization, inputGallery, inputDetails, inputIDList, inputDate, inputDateEnd, inputTime, inputLocation, inputContactName, inputContactEmail, inputContactPhone) {
//	If "all" then update the EditorID
	tempObject = document.getElementById('assignmentEditorID');
	temp = tempObject.value;
	tempArray = temp.split(":");
	if (tempArray[3] == 'all') {
		tempArray[0] = inputEditorID;
		tempArray[3] = inputOrganization;
		temp = tempArray.join(":");
		tempObject.value = temp;
	}
	tempObject = document.getElementById('assignmentID');
	tempObject.value = inputID;
	tempObject = document.getElementById('assignmentIDList');
	tempObject.value = inputIDList;
	tempObject = document.getElementById('assignmentGallery');
	tempObject.value = inputGallery;
	tempObject = document.getElementById('assignmentDetails');
	tempObject.value = inputDetails;
	tempObject = document.getElementById('assignmentDate');
	tempObject.value = inputDate;
	tempObject = document.getElementById('assignmentDateEnd');
	tempObject.value = inputDateEnd;
	tempObject = document.getElementById('assignmentTime');
	tempObject.value = inputTime;
	tempObject = document.getElementById('assignmentLocation');
	tempObject.value = inputLocation;
	tempObject = document.getElementById('assignmentContactName');
	tempObject.value = inputContactName;
	tempObject = document.getElementById('assignmentContactEmail');
	tempObject.value = inputContactEmail;
	tempObject = document.getElementById('assignmentContactPhone');
	tempObject.value = inputContactPhone;
	tempObject = document.getElementById('formrequest');
	tempObject.submit();
}

//	Build data for passing to Confirmation page
//	accept
//	decline
//	upload	$temp = base64_encode("&data=req&data=upload&data=assignmentid&data={$row['assignID']}&data=photographerid&data={$row['photoID']}");
function buildData(inputRequest, inputID, inputGallery, inputPhotographerID, inputPhotographerName) {
	temp = '';
	temp = "&data=req&data="+inputRequest;
	if (inputID != '') {
		temp = temp + "&data=assignmentid&data="+inputID;
	}
	if (inputGallery != '') {
		temp = temp + "&data=galleryname&data="+inputGallery;
	}
	if (inputPhotographerID != '') {
		temp = temp + "&data=photographerid&data="+inputPhotographerID;
	}
	if (inputPhotographerName != '') {
		temp = temp + "&data=photographername&data="+inputPhotographerName;
	}
	linkConfirmation(temp);
}

//	Link to confirmation page with a request/action
function linkConfirmation(inputData) {
	tempObject = document.getElementById('data');
	tempObject.value = inputData;
	tempObject = document.getElementById('formconfirm');
	tempObject.submit();
}

//	Expand/Contract all assignment details for an organization
function toggleAll(inputID) {
	tempObject = document.getElementById(inputID);
	if (tempObject.style.display == 'none') {
		tempObject.style.display = 'table-row';
	} else {
		tempObject.style.display = 'none';
	}
return;
}
//	As expand/contract for an organization, toggle +/-
function toggleAllDisplay(inputID) {
	tempObject = document.getElementById(inputID);
	temp = tempObject.innerHTML;
	if (temp == '+') {
		tempObject.innerHTML = '-';
	} else {
		tempObject.innerHTML = '+';
	}
return;
}

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
	if (inputStatus == 'Reset') { tempObject.value = "'a','b','c','d','f','g'"; }
	executeRefresh();
	return;
}

//	Action: Delete the assignment
//	A. Refresh/reload the page
function executeRefresh() {
//	Reload the page, to remove from assignment list.
	tempObject = document.getElementById('formeditor');
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


//	Generate valid Getty MEID request email
function actionGetty() {
	resetStatus();
	statusObject = document.getElementById('statusarea');
	statusObject.innerHTML = 'Active ...';
	currentStatusObject = statusObject;
	gettyMEID(statusObject);
	return;
}


//	Action: Delete the assignment
//	A. Delete from calendar
//	B. Delete from database
function actionDelete(inputID) {
	if (isConsoleTrace) { console.log("actionDelete Assignment("+inputID+")"); }
	resetStatus();
//	B. Delete from database, only
	statusObject = document.getElementById('actionstatuslabel_'+inputID);
	statusObject.innerHTML = 'Before ...';
	currentStatusObject = statusObject;
	deleteAssignmentDB(inputID, statusObject);
//	Reload the page, to remove from assignment list.
//	if (isConsoleTrace) { return; }
	setTimeout(function() { executeRefresh(); }, 700);	
	return;
	tempObject = document.getElementById('formeditor');
	tempObject.submit();
	return;
}

//	Action: Update details and deliver for an assignment
function actionUpdate(inputID, inputStatus) {
	if (isConsoleTrace) { console.log("actionUpdate Assignment("+inputID+") ("+inputStatus+")"); }
	resetStatus();
//	Assignment delivery method
	tempObject = document.getElementById('delivery_'+inputID);
	tempIndex = tempObject.selectedIndex;
	tempDelivery = tempObject.options[tempIndex].value;
//	Assignment details
	tempObject = document.getElementById('details_'+inputID);
	tempDetails = tempObject.value;
	tempDetails = tempDetails.trim();
//	Initiate Getty MEID flag (y/n)
	tempMEIDFlag = 'skip';
	tempObject = document.getElementById('meidflag_'+inputID);
	if (tempObject != null) {
		tempMEIDFlag = 'n';
		if (tempObject.checked == true) {
			tempMEIDFlag = 'y';
		}
	}
//	Getty MEID value
	tempObject = document.getElementById('meid_'+inputID);
	tempMEID = 'skip';
	if (tempObject != null) {
		tempMEID = tempObject.value;
		tempMEID = tempMEID.trim();
		tempObject = document.getElementById('meidlabel_'+inputID);
		if (tempObject != null) { tempObject.innerHTML = tempMEID; }
	}
	statusObject = document.getElementById('actionstatuslabel_'+inputID);
	currentStatusObject = statusObject;
	updateDetails(inputID, tempDelivery, tempDetails, tempMEIDFlag, tempMEID, inputStatus, statusObject);
//	Reload the page, to remove from assignment list.
//	tempObject = document.getElementById('formeditor');
//	tempObject.submit();
	return;
}

//	Action: Assign requestor to assignment and accept
//	actionAssign('{$itemArray[$INDEX_ASSIGNMENTID]}','{$itemArray[$INDEX_GALLERY]}','{$itemArray[$INDEX_REQUESTOR]}', EditorID);
function actionAssign(inputAssignID, inputGallery, inputRequestor, inputEditorID) {
	if (isConsoleTrace) { console.log("actionAssign Assignment("+inputAssignID+") ("+inputEditorID+")"); }
	resetStatus();
	tempObject = document.getElementById('delivery_'+inputAssignID);
	tempIndex = tempObject.selectedIndex;
	tempDelivery = tempObject.options[tempIndex].value;
	tempMEID = 'n';
	tempObject = document.getElementById('meidflag_'+inputAssignID);
	if (tempObject.checked == true) {
		tempMEID = 'y';
	}
	tempObject = document.getElementById('details_'+inputAssignID);
	tempDetails = tempObject.value;
	tempDetails = tempDetails.trim();
	statusObject = document.getElementById('statusarea');
	statusObject.innerHTML = 'Assign/Accept request active ...';
	currentStatusObject = statusObject;
	assignRequestor(inputAssignID, inputGallery, inputEditorID, inputRequestor, tempDelivery, tempMEID, tempDetails, statusObject);
//	Reload the page, to remove from assignment list.
//	tempObject = document.getElementById('formeditor');
//	tempObject.submit();
	return;
}

//	Action: Notify (email) Getty of images sent, with MEID, gallery name, and image count.
//	actionGettyNotify('{$itemArray[$INDEX_ASSIGNMENTID]}','{$itemArray[$INDEX_GALLERY]}','{$itemArray[$INDEX_MEID]}','images_{$itemArray[$INDEX_ASSIGNMENTID]');
function actionGettyNotify(inputAssignID, inputGallery, inputMEID) {
	if (isConsoleTrace) { console.log("actionGettyNotify Assignment("+inputAssignID+") MEID("+inputMEID+")"); }
	resetStatus();
	tempObject = document.getElementById('images_'+inputAssignID);
	tempImageCount = tempObject.value;
//	statusObject = document.getElementById('statusarea');
//	statusObject.innerHTML = 'Notify Getty request active ...';
	statusObject = document.getElementById('gettystatuslabel_'+inputAssignID);
	currentStatusObject = statusObject;
	GettyNotify(inputAssignID, inputGallery, inputMEID, tempImageCount, statusObject);
//	assignRequestor(inputAssignID, inputGallery, inputMEID, tempImageCount, statusObject);
//	Reload the page, to remove from assignment list.
//	tempObject = document.getElementById('formeditor');
//	tempObject.submit();
	return;
}

//	Action: Send email reminder to upload images.
function actionRemind(inputID, inputGallery, inputPhotoID, inputPhotoName, inputPhotoEmail, inputFinal) {
	if (isConsoleTrace) { console.log("actionRemind ("+inputPhotoName+":"+inputPhotoEmail+")"); }
	resetStatus();
//	statusObject = document.getElementById('statusarea');
//	statusObject.innerHTML = "Sending email reminder ...";
//	Status in the next column. Nearer to button/link.
	statusObject = document.getElementById('photographerstatuslabel_'+inputID);
	currentStatusObject = statusObject;
	sendEmailReminder(inputID, inputGallery, inputPhotoID, inputPhotoName, inputPhotoEmail, inputFinal, statusObject);
//	Reload the page, to remove from assignment list.
//	tempObject = document.getElementById('formeditor');
//	tempObject.submit();
	return;
}

//	Action: Editor accepts assignment for photographer
//	buildData('accept', inputID, inputGallery, inputPhotographerID, inputPhotographerName)
//	actionAccept('{$itemArray[$INDEX_ASSIGNMENTID]}','{$itemArray[$INDEX_GALLERY]}','{$itemArray[$INDEX_PHOTOGRAPHERID]}','{$itemArray[$INDEX_PHOTOGRAPHERNAME]}');\">Accept</a>";
function actionAccept(inputID, inputGallery, inputPhotoID, inputPhotoName) {
	if (isConsoleTrace) { console.log("actionAccept ("+inputPhotoID+":"+inputPhotoName+")"); }
	resetStatus();
//	statusObject = document.getElementById('statusarea');
//	statusObject.innerHTML = "Accepting assignment ...";
//	Status in the next column. Nearer to button/link.
	statusObject = document.getElementById('photographerstatuslabel_'+inputID);
	currentStatusObject = statusObject;
	acceptAssignment(inputID, inputGallery, inputPhotoID, inputPhotoName, statusObject);
//	Reload the page, to remove from assignment list.
//	tempObject = document.getElementById('formeditor');
//	tempObject.submit();
	return;
}

//	Action: Editor declines assignment for photographer
//	buildData(inputRequest, inputID, inputGallery, inputPhotographerID, inputPhotographerName)
function actionDecline(inputID, inputGallery, inputPhotoID, inputPhotoName) {
	if (isConsoleTrace) { console.log("actionDecline ("+inputPhotoID+":"+inputPhotoName+")"); }
	resetStatus();
//	statusObject = document.getElementById('statusarea');
//	statusObject.innerHTML = "Declining assignment ...";
//	Status in the next column. Nearer to button/link.
	statusObject = document.getElementById('photographerstatuslabel_'+inputID);
	currentStatusObject = statusObject;
	declineAssignment(inputID, inputGallery, inputPhotoID, inputPhotoName, statusObject);
//	Reload the page, to remove from assignment list.
//	tempObject = document.getElementById('formeditor');
//	tempObject.submit();
	return;
}

//	Action: Flag assignment: First and any upload of images
//	buildData('upload','{$itemArray[$INDEX_ASSIGNMENTID]}','','{$itemArray[$INDEX_PHOTOGRAPHERID]}','')
//	actionUpload('{$itemArray[$INDEX_ASSIGNMENTID]}', 'n' (first))
//	actionUpload('{$itemArray[$INDEX_ASSIGNMENTID]}', 'y' (final))
function actionUpload(inputID, inputFinal) {
	if (isConsoleTrace) { console.log("actionUpload ("+inputID+") Final?("+inputFinal+")"); }
	resetStatus();
//	statusObject = document.getElementById('statusarea');
//	statusObject.innerHTML = "Upload flag ...";
//	Status in the next column. Nearer to button/link.
	statusObject = document.getElementById('photographerstatuslabel_'+inputID);
	currentStatusObject = statusObject;
	flagAssignment(inputID, inputFinal, statusObject);
//	Reload the page, to remove from assignment list.
//	tempObject = document.getElementById('formeditor');
//	tempObject.submit();
	return;
}

//function executeRefresh() {
//	if (isConsoleTrace) { console.log("executeRefresh IDs("+eventIDs.length+")"); }
//	setTimeout(function() { refreshLoop(); }, 500);	
//return;
//}
//
//function refreshLoop() {
//	if (isConsoleTrace) { console.log("refreshLoop ("+eventIDs.length+")"); }
//	if (eventIDs.length == 0) { return; }
//	if (isActive) { setTimeout(function() { refreshLoop(); }, 500); return; }
//	isActive = true;
//	maxIDs = eventIDs.length -1;
//	tempEvent = eventIDs[maxIDs];
//	if (isConsoleTrace) { console.log("refreshLoop Collection("+tempEvent[0]+") ID("+tempEvent[1]+")"); }
//	runawayCounter = 0;
//	currentOrganizationID = tempEvent[0];
//	getCalendarData(currentCollectionID);
//	eventUpdateWait(tempEvent[1], tempEvent[2], tempEvent[3], tempEvent[4]);
//return;
//}

</script>

</head>
<body style="margin-top: 20px;">
<!--	<br><br><font color=#CC0000>Note: AK Diagnosing. Extra messages issued.</font><br><br>	-->
<div class="container">
	<font size=+3>Assignments Dashboard: Editor</font>&nbsp;&nbsp;<script type="text/javascript">if (isConsoleTrace) {document.write("<font size=-1 color=#3300FF>"); }</script>(2020-10-30)</font>
<br><input class="btn btn-default" type="button" id="staleButton" style="display: none;" value="Stale logic. Click to refresh." onClick="javascript:document.location.reload(true);">
<div class="row"> 
<div class="col-xs-12">

<?php
#	Array of results, grouped by collection
	$assignments = array();
#	Organization names: General Soccer, NWSL, USA Women's Soccer, ...
	$organizationArray = array();
#	Anchors: GeneralSoccer, NWSL, USAWomensSoccer, ...
	$anchorsArray = array();
	$currentOrganization = "";
#	Open the database
	$connection = openDB();
	$tempStatus = '';
	$photographerLink = '';

#	Get list of assignments for input editor
#$tempEditor = "{$currentEditor}";
	$tempArray = explode(":", $currentEditor);
	if ($tempArray[0] == 'all') {
		$tempEditor = "editor.editor_name = '{$tempArray[1]}'";
	} else {
		$tempEditor = "editor.editor_ID in (0,{$tempArray[0]})";
	}

	$query = "select assign.assign_organization as 'organization', assign.assign_gallery as 'galleryName', assign.assign_status as 'status', assign.assign_delivery as 'delivery', editor.editor_ID as 'editorID', photo.photographer_ID as 'photoID', photo.photographer_name as 'photoName', photo.photographer_email as 'photoEmail', TIMESTAMPDIFF(HOUR,assign.assign_date,NOW()) as 'difference', DATE_FORMAT(assign.assign_date, '%c/%e/%Y') as 'eventDate', DATE_FORMAT(assign.assign_dateEnd, '%c/%e/%Y') as 'eventDateEnd', TIME_FORMAT(assign.assign_timeStart, '%H:%i') as 'eventTime', assign.assign_ID as 'assignID', assign.assign_details as 'assignDetails', assign.assign_rsvp as 'assignRSVP', assign.assign_meid as 'assignMEIDFlag', assign.assign_meidID as 'assignMEID', assign.assign_images as 'assignImages', assign.assign_requestor as 'requestorName', assign.assign_location as 'eventLocation', assign.assign_contactName as 'eventContactName', assign.assign_contactEmail as 'eventContactEmail', assign.assign_contactPhone as 'eventContactPhone' FROM schedule_assignments as assign join schedule_editors as editor on assign.assign_organization = editor.editor_organization join schedule_photographers as photo on assign.assign_photographerIDs = photo.photographer_ID where {$tempEditor} && assign.assign_status in ({$currentStatusFilter}) order by {$currentSort}, assign.assign_organization, assign.assign_gallery";
#print "Assignments SQL ({$query})<br>";
	$data = executeSQL($connection, $query);
	$max = mysqli_num_rows($data);
#print "Assignments ({$max})<br>";
#	Variables associated with isolating multiple photographers/days assignments
	$tempIndex = 0;
	$tempIndexPrior = 0;
	$tempIndexCurrent = 0;
	$tempPrior = '';
	$tempCurrent = '';
	$tempPriorID = '';
	$tempCurrentID = '';
	$tempPriorIDList = '';
	$tempCurrentIDList = '';
	$tempOrganization = '';
	$currentOrganization = '';
#	Populate array grouped by collection, in status order
	if ($max > 0) {
		while ($row = mysqli_fetch_assoc($data)) {
			if ($currentOrganization != $row['organization']) {
				$currentOrganization = $row['organization'];
				if (in_array($row['organization'], $organizationArray) != true) {
					$tempOrganization = str_replace("'", "", $currentOrganization);
					$tempOrganization = str_replace(" ", "", $tempOrganization);
					array_push($organizationArray, $currentOrganization);
					$anchorsArray[$currentOrganization] = $tempOrganization;
					$assignments[$currentOrganization] = array();
				}
			}
			$tempStatus = $row['status'];
			if ($row['status'] == $STATUS_ASSIGNED && $row['difference'] > 8) { $tempStatus = $STATUS_WAITING; }
			if ($row['status'] == $STATUS_GETTY) {
				if ($row['assignImages'] != '') {
						$test = intval($row['assignImages']);
						if ($test > 0) { $tempStatus = $STATUS_COMPLETE; }
				}
			}
			$temp = $currentOrganization . $row['galleryName'];
#			$tempRow = array($tempStatus, $row['difference'], $row['galleryName'], $row['photoID'], $row['photoName'], $deliveryArray[$row['delivery']], $row['eventDate'], $row['eventDateEnd'], $row['assignID'], $row['editorID'], $row['assignDetails'], $row['assignRSVP'], $row['assignID'], $row['eventTime']);
			$tempFlag = '';
			if ($row['assignMEIDFlag'] == 'y') { $tempFlag = ' checked '; }
			$tempRow = array($tempStatus, $row['difference'], $row['galleryName'], $row['photoID'], $row['photoName'], $row['photoEmail'], $row['delivery'], $row['eventDate'], $row['eventDateEnd'], $row['assignID'], $row['editorID'], $row['assignDetails'], $row['assignRSVP'], $row['assignID'], $row['eventTime'], $row['requestorName'], $tempFlag, $row['assignMEID'], $row['assignImages'], $row['editorID'], $row['eventLocation'], $row['eventContactName'], $row['eventContactEmail'], $row['eventContactPhone']);
			array_push($assignments[$currentOrganization], $tempRow);
#	Multiple photographers assignment or multiple day assignment. Organization . Gallery Name repeats.
			$tempIndex = count($assignments[$currentOrganization]);
			$tempIndex--;
			$tempIndexCurrent = $tempIndex;
#			print "AK004 ({$tempIndexCurrent}) ({$assignments[$currentOrganization][$tempIndexCurrent][$INDEX_GALLERY]})<br>";			
#			print_r($assignments[$currentOrganization]); 
#			print "<br>AK004 end<br>";
			$tempCurrentID = $assignments[$currentOrganization][$tempIndexCurrent][$INDEX_ASSIGNMENTID];
			$tempCurrent = $assignments[$currentOrganization][$tempIndexCurrent][$INDEX_GALLERY];
			$tempCurrentIDList = $assignments[$currentOrganization][$tempIndexCurrent][$INDEX_IDLIST];
#			print "AK005 Current ({$tempIndexCurrent}) ID({$tempCurrentID}) List({$tempCurrentIDList}) Gallery({$tempCurrent})<br>";
			$tempIndex--;
			$tempIndexPrior = $tempIndex;
			if ($assignments[$currentOrganization][$tempIndexCurrent][$INDEX_STATUS] == $assignments[$currentOrganization][$tempIndexPrior][$INDEX_STATUS]) {
			$tempPriorID = $assignments[$currentOrganization][$tempIndexPrior][$INDEX_ASSIGNMENTID];
			$tempPrior = $assignments[$currentOrganization][$tempIndexPrior][$INDEX_GALLERY];
			$tempPriorIDList = $assignments[$currentOrganization][$tempIndexPrior][$INDEX_IDLIST];
#			print "AK005 Prior ({$tempIndexPrior}) ID({$tempPriorID}) List({$tempPriorIDList}) Gallery({$tempPrior})<br><br>";
#	Multiple photographer/day assignment test. Same gallery.
#			if ($tempCurrent == $tempPrior && $assignments[$currentOrganization][$tempIndexCurrent][$INDEX_STATUS] == $assignments[$currentOrganization][$tempIndexPrior][$INDEX_STATUS]) {
			if ($tempCurrent == $tempPrior) {
#				print "&nbsp;&nbsp;&nbsp;AK006 ++++++++++++++<br>";
#print "AK004 Current({$assignments[$currentOrganization][$tempIndexCurrent][$INDEX_STATUS]}:{$assignments[$currentOrganization][$tempIndexCurrent][$INDEX_GALLERY]}) Prior({$assignments[$currentOrganization][$tempIndexPrior][$INDEX_STATUS]}:{$assignments[$currentOrganization][$tempIndexPrior][$INDEX_GALLERY]})<br>";				
				$assignments[$currentOrganization][$tempIndexCurrent][$INDEX_IDLIST] = "{$tempPriorIDList}:{$tempCurrentID}";
				$assignments[$currentOrganization][$tempIndexPrior][$INDEX_IDLIST] = "+++empty+++";
			}
			}
		}
	}
#print_r($assignments);
#print_r($anchorsArray);
mysqli_free_result($data);
mysqli_close($connection);
?>

<a name=top></a><br>
<table border='0'>
<tr><td width=5%>&nbsp;</td><td width=20%>&nbsp;</td><td width=5%>&nbsp;</td><td width=40%>&nbsp;</td><td width=3%>&nbsp;</td><td width=30%>&nbsp;</td><td width=15%>&nbsp;</td></tr>
<tr><td>&nbsp;</td>
<td valign='top'>
<?php
#	List of collection anchor to assignment list
print "<table width='100%' border='0'>";
print "<tr><td><a href='javascript:linkCalendar();'>Assignment Calendar</a></td></tr>";
print "<tr><td>&nbsp;</td></tr>";
print "<tr><td>Links to lists</td></tr>";
#	Sort the arrays
asort($organizationArray);
foreach ($organizationArray as $index) {
	print "<tr><td><a href='#{$anchorsArray[$index]}'>{$index} assignment list</a></td></tr>";
}
print "</table>";
?>
</td>
<td>&nbsp;</td>
<td valign='top'>
	<table border=0 width='100%'><tr><td width='6%'></td><td width='1%'>&nbsp;</td><td>Legend</td></tr>
<?php
	print "<tr><td bgcolor='{$BGCOLORS[$STATUS_INITIAL]}' onclick=javascript:executeFilter('{$STATUS_INITIAL}');>&nbsp</td><td>&nbsp;</td><td>No editor, no photographers</td></tr>";
	print "<tr><td bgcolor='{$BGCOLORS[$STATUS_REQUEST]}' onclick=javascript:executeFilter('{$STATUS_REQUEST}');>&nbsp</td><td>&nbsp;</td><td>Editor recruiting photographer(s)</td></tr>";
	print "<tr><td bgcolor='{$BGCOLORS[$STATUS_ASSIGNED]}' onclick=javascript:executeFilter('{$STATUS_ASSIGNED}');>&nbsp</td><td>&nbsp;</td><td>Photographer assigned.</td></tr>";
	print "<tr><td bgcolor='{$BGCOLORS[$STATUS_WAITING]}' onclick=javascript:executeFilter('{$STATUS_ASSIGNED}');>&nbsp</td><td>&nbsp;</td><td>Waiting for image upload</td></tr>";
	print "<tr><td bgcolor='{$BGCOLORS[$STATUS_UPLOAD]}' onclick=javascript:executeFilter('{$STATUS_UPLOAD}');>&nbsp</td><td>&nbsp;</td><td>Images uploaded. Waiting for Final upload.</td></tr>";
	print "<tr><td bgcolor='{$BGCOLORS[$STATUS_GETTY]}' onclick=javascript:executeFilter('{$STATUS_GETTY}');>&nbsp</td><td>&nbsp;</td><td>Push images to Getty and notify</td></tr>";
	print "<tr><td bgcolor='{$BGCOLORS[$STATUS_COMPLETE]}' onclick=javascript:executeFilter('{$STATUS_COMPLETE}');>&nbsp</td><td>&nbsp;</td><td>Archive (Completed assignments)</td></tr>";
	print "<tr><td><a href=javascript:executeFilter('Reset');>Reset</a></td><td>&nbsp;</td><td>Reset filter, show all assignments</td></tr>";
	print "<tr><td>&nbsp</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
#	print "<tr><td colspan=3><a href='javascript:linkCalendar();'>Assignment Calendar</a></td></tr>";
	print "<tr><td>&nbsp</td><td>&nbsp</td><td><input class='btn btn-default' type='button' value='Refresh/Reload' onClick='javascript:executeRefresh();'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class='btn btn-default' type='button' value='Generate Getty' onClick='javascript:actionGetty();'></td></tr>";
?>
	</table>
</td><td>&nbsp;</td>
<td valign='top' align='center'>
	<table border=0 width='100%'><tr><td align='left'>Status</td></tr><tr><td><span class='text-success' id='statusarea'>&nbsp;</span></td></tr></table>
</td><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
</table>


<?php
#	For each collection, list the assignments
#	To hide a row, the TR needs an id ('GGG27')    
#	tempObject = document.getElementById('GGG27');
#    tempObject.style.display = 'none';

$index = 0;
$currentComma = '';
$currentTRs = '';
print "<table border='0'>";
print "<tr><td width=2%>&nbsp;</td><td width=3%>&nbsp;</td><td width=1%>&nbsp;</td><td>&nbsp;<a name='{$anchorsArray[$organizationArray[$index]]}'></a></td><td width=1%>&nbsp;</td><td>&nbsp;</td><td width=1%>&nbsp;</td><td>&nbsp;</td><td width=1%>&nbsp;</td><td>&nbsp;</td><td width=1%>&nbsp;</td><td width=10%>&nbsp;</td><td width=1%>&nbsp;</td><td width=10%>&nbsp;</td><td width=2%>&nbsp;</td><td>&nbsp;</td><td width=3%>&nbsp;</td></tr>";
#	$tempKey is organization name (USA Women's Soccer). Any use?
foreach ($assignments as $tempKey => $assignmentArray) {
	$currentComma = '';
	$currentTRs = '';
	print "<tr><td align='center'><a id='A_{$anchorsArray[$organizationArray[$index]]}' href=\"javascript:organizationTRs['{$anchorsArray[$organizationArray[$index]]}'].forEach(toggleAll);toggleAllDisplay('A_{$anchorsArray[$organizationArray[$index]]}');\">+</a></td><td>&nbsp;</td><td>&nbsp;</td><td>{$organizationArray[$index]}</a>&nbsp;&nbsp;&nbsp;<a href=#top>Top</a></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
	print "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>Gallery Name</td><td>&nbsp;</td><td>Requestor</td><td>&nbsp;</td><td>Photographer</td><td>&nbsp;</td><td align='center'>Respond<br>Upload</td><td>&nbsp;</td><td align='center'>Action</td><td>&nbsp;</td><td colspan=3>Getty</td><td>&nbsp;</td></tr>";
#	print "<tr id='TRHeader' style='display:none' bgcolor='#DDDDDD'><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td colspan=3>Details</td><td>&nbsp;</td><td>Delivery Method</td><td>&nbsp;</td><td>Action</td><td>&nbsp;</td><td>Spare</td><td>&nbsp;</td><td>Spare</td><td>&nbsp;</td><td>Spare</td><td>&nbsp;</td></tr>";
	foreach ($assignmentArray as $itemArray) {
		if ($itemArray[$INDEX_PHOTOGRAPHERNAME] == '') {
			if ($itemArray[$INDEX_IDLIST] == "+++empty+++") {
				$photographerLink = "&nbsp;";
			} else {
				$tempLength = " (single day)";
				if ($itemArray[$INDEX_EVENTDATE] != $itemArray[$INDEX_EVENTDATEEND]) { $tempLength = " (multiple days)"; }
				$photographerLink = "<a href=\"javascript:linkRequest('{$itemArray[$INDEX_ASSIGNMENTID]}','{$itemArray[$INDEX_EDITORID]}', '{$anchorsArray[$index]}', '{$itemArray[$INDEX_GALLERY]}','{$itemArray[$INDEX_DETAILS]}','{$itemArray[$INDEX_IDLIST]}','{$itemArray[$INDEX_EVENTDATE]}','{$itemArray[$INDEX_EVENTDATEEND]}','{$itemArray[$INDEX_EVENTTIME]}','{$itemArray[$INDEX_LOCATION]}','{$itemArray[$INDEX_CONTACT]}','{$itemArray[$INDEX_CONTACTEMAIL]}','{$itemArray[$INDEX_CONTACTPHONE]}');\">Recruit{$tempLength}</a>";
			}
		} else {
			$photographerLink = "<a href=\"javascript:linkPhotographer('{$itemArray[$INDEX_PHOTOGRAPHERID]}');\">{$itemArray[$INDEX_PHOTOGRAPHERNAME]}</a>";
		}
#	Build links based on status
$linkAssign = '';
$linkAccept = '';
$linkDecline = '';
$linkUpload = '';
$linkRemind = '';
$statusPhotographerAction = "<br><span class='text-success' id='photographerstatuslabel_{$itemArray[$INDEX_ASSIGNMENTID]}'></span>";
$linkDelete = "<br><a href=\"javascript:actionDelete('{$itemArray[$INDEX_ASSIGNMENTID]}');\">Delete</a>";
$linkUpdate = "<a href=\"javascript:actionUpdate('{$itemArray[$INDEX_ASSIGNMENTID]}','{$itemArray[$INDEX_STATUS]}');\">Update</a>";
$statusAssignmentAction = "<br><span class='text-success' id='actionstatuslabel_{$itemArray[$INDEX_ASSIGNMENTID]}'></span>";
$linkGetty = '';
$imagesGetty = '';
$MEIDLabel = '';
$MEIDGetty = '';
$tempSelect = '';
#	Status: Initial
	if ($itemArray[$INDEX_STATUS] == $STATUS_INITIAL && $itemArray[$INDEX_REQUESTOR] != '' && $photographerLink != '&nbsp;') {
#	link to have requestor be accepting photographer
		$linkAssign = "<a href=\"javascript:actionAssign('{$itemArray[$INDEX_ASSIGNMENTID]}','{$itemArray[$INDEX_GALLERY]}','{$itemArray[$INDEX_REQUESTOR]}','{$itemArray[$INDEX_EDITORID]}');\">Assign requestor</a><br>";
		$MEIDGetty = "<input type='checkbox' id='meidflag_{$itemArray[$INDEX_ASSIGNMENTID]}' {$itemArray[$INDEX_MEIDFLAG]}>&nbsp;&nbsp;MEID assignment<br>MEID <input class='form-control' type='text' id='meid_{$itemArray[$INDEX_ASSIGNMENTID]}' name='meid_{$itemArray[$INDEX_ASSIGNMENTID]}' size=9 value='{$itemArray[$INDEX_MEID]}'>";
	}
#	Status: Request. Links to accept/decline the assignment
#	buildData(inputRequest, inputID, inputGallery, inputPhotographerID, inputIDList) {
	$MEIDGetty = "<input type='checkbox' id='meidflag_{$itemArray[$INDEX_ASSIGNMENTID]}' {$itemArray[$INDEX_MEIDFLAG]}>&nbsp;&nbsp;MEID assignment<br>MEID <input class='form-control' type='text' id='meid_{$itemArray[$INDEX_ASSIGNMENTID]}' name='meid_{$itemArray[$INDEX_ASSIGNMENTID]}' size=9 value='{$itemArray[$INDEX_MEID]}'>";
	if ($itemArray[$INDEX_STATUS] == $STATUS_REQUEST && $itemArray[$INDEX_PHOTOGRAPHERID] != 0) {
		$linkAccept = "<a href=\"javascript:actionAccept('{$itemArray[$INDEX_ASSIGNMENTID]}','{$itemArray[$INDEX_GALLERY]}','{$itemArray[$INDEX_PHOTOGRAPHERID]}','{$itemArray[$INDEX_PHOTOGRAPHERNAME]}');\">Accept</a>";
#		$linkDecline = "<a href=\"javascript:buildData('decline','{$itemArray[$INDEX_ASSIGNMENTID]}','{$itemArray[$INDEX_GALLERY]}','{$itemArray[$INDEX_PHOTOGRAPHERID]}','{$itemArray[$INDEX_PHOTOGRAPHERNAME]}');\">Decline</a>";
		$linkDecline = "<br><a href=\"javascript:actionDecline('{$itemArray[$INDEX_ASSIGNMENTID]}','{$itemArray[$INDEX_GALLERY]}','{$itemArray[$INDEX_PHOTOGRAPHERID]}','{$itemArray[$INDEX_PHOTOGRAPHERNAME]}');\">Decline</a>";
		$MEIDGetty = "<input type='checkbox' id='meidflag_{$itemArray[$INDEX_ASSIGNMENTID]}' {$itemArray[$INDEX_MEIDFLAG]}>&nbsp;&nbsp;MEID assignment<br>MEID <input class='form-control' type='text' id='meid_{$itemArray[$INDEX_ASSIGNMENTID]}' name='meid_{$itemArray[$INDEX_ASSIGNMENTID]}' size=9 value='{$itemArray[$INDEX_MEID]}'>";
	}
#	Status: Assigned.
	if ($itemArray[$INDEX_STATUS] == $STATUS_ASSIGNED) {
		if ($itemArray[$INDEX_MEIDFLAG] == ' checked ') {
			$MEIDGetty = "MEID <input class='form-control' type='text' id='meid_{$itemArray[$INDEX_ASSIGNMENTID]}' name='meid_{$itemArray[$INDEX_ASSIGNMENTID]}' size=9 value='{$itemArray[$INDEX_MEID]}'>";
		} else {
			$MEIDGetty = '';
		}
	}
#	Status: Images uploaded.
#	Status: Getty MEID available. Getty links and fields
	if ($itemArray[$INDEX_STATUS] == $STATUS_UPLOAD ) {
#		$linkGetty = "<a href=\"javascript:actionGettyNotify('{$itemArray[$INDEX_ASSIGNMENTID]}','{$itemArray[$INDEX_GALLERY]}','{$itemArray[$INDEX_MEID]}');\">Notify Getty</a>";
#		$imagesGetty = "<input class='form-control' type='text' id='images_{$itemArray[$INDEX_ASSIGNMENTID]}' name='images_{$itemArray[$INDEX_ASSIGNMENTID]}' size=6 value='image_count'>";
#	Images uploaded and no MEID flag then don't present MEID input text.
#	Images uploaded and MEID flag, then present either MEID label or MEID input text.
		$MEIDGetty = '';
		if ($itemArray[$INDEX_MEIDFLAG] == ' checked ') {
			if ($itemArray[$INDEX_MEID] == '') {
				$MEIDGetty = "MEID <input class='form-control' type='text' id='meid_{$itemArray[$INDEX_ASSIGNMENTID]}' name='meid_{$itemArray[$INDEX_ASSIGNMENTID]}' size=9 value='{$itemArray[$INDEX_MEID]}'>";
			} else {
				$MEIDLabel = "MEID: <span id='meidlabel_{$itemArray[$INDEX_ASSIGNMENTID]}'>{$itemArray[$INDEX_MEID]}</span>";
			}
		}
		$linkUpload = "<a href=\"javascript:actionUpload('{$itemArray[$INDEX_ASSIGNMENTID]}', 'y');\">Final Upload</a>";
		$linkRemind = "<br><a href=\"javascript:actionRemind('{$itemArray[$INDEX_ASSIGNMENTID]}','{$itemArray[$INDEX_GALLERY]}','{$itemArray[$INDEX_PHOTOGRAPHERID]}','{$itemArray[$INDEX_PHOTOGRAPHERNAME]}','{$itemArray[$INDEX_PHOTOGRAPHEREMAIL]}','y');\">Send reminder</a>";
#		$linkUpload	= "<span class='text-success' id='gettystatuslabel_{$itemArray[$INDEX_ASSIGNMENTID]}'></span>";
	}
#	Status: Images uploaded and Getty MEID available. Getty links and fields
	if ($itemArray[$INDEX_STATUS] == $STATUS_GETTY) {
		$linkGetty = "<a href=\"javascript:actionGettyNotify('{$itemArray[$INDEX_ASSIGNMENTID]}','{$itemArray[$INDEX_GALLERY]}','{$itemArray[$INDEX_MEID]}');\">Notify Getty</a>";
		$MEIDLabel = "MEID: <span id='meidlabel_{$itemArray[$INDEX_ASSIGNMENTID]}'>{$itemArray[$INDEX_MEID]}</span>";
		$imagesGetty = "<input class='form-control' type='text' id='images_{$itemArray[$INDEX_ASSIGNMENTID]}' name='images_{$itemArray[$INDEX_ASSIGNMENTID]}' size=6 value='image_count'>";
		$linkUpload	= "<span class='text-success' id='gettystatuslabel_{$itemArray[$INDEX_ASSIGNMENTID]}'></span>";
		$MEIDGetty = '';
	}
#	Status: Waiting. Link to flag as uploaded and link to remind
	if ($itemArray[$INDEX_STATUS] == $STATUS_WAITING) {
#		$linkUpload = "<a href=\"javascript:buildData('upload','{$itemArray[$INDEX_ASSIGNMENTID]}','','{$itemArray[$INDEX_PHOTOGRAPHERID]}','');\">Uploaded flag</a>";
		$linkUpload = "<a href=\"javascript:actionUpload('{$itemArray[$INDEX_ASSIGNMENTID]}', 'n');\">First Upload</a>";
		$linkRemind = "<br><a href=\"javascript:actionRemind('{$itemArray[$INDEX_ASSIGNMENTID]}','{$itemArray[$INDEX_GALLERY]}','{$itemArray[$INDEX_PHOTOGRAPHERID]}','{$itemArray[$INDEX_PHOTOGRAPHERNAME]}','{$itemArray[$INDEX_PHOTOGRAPHEREMAIL]}','n');\">Send reminder</a>";
#	Getty MEID text box only when checked.
		if ($itemArray[$INDEX_MEIDFLAG] == ' checked ') {
			$MEIDGetty = "MEID <input class='form-control' type='text' id='meid_{$itemArray[$INDEX_ASSIGNMENTID]}' name='meid_{$itemArray[$INDEX_ASSIGNMENTID]}' size=9 value='{$itemArray[$INDEX_MEID]}'>";
		} else {
			$MEIDGetty = '';
		}
	}
#	Status: Complete/Archive
	if ($itemArray[$INDEX_STATUS] == $STATUS_COMPLETE) {
		$MEIDLabel = "MEID: <span id='meidlabel_{$itemArray[$INDEX_ASSIGNMENTID]}'>{$itemArray[$INDEX_MEID]}</span>";
		$imagesGetty = "Images: <span id='images_{$itemArray[$INDEX_ASSIGNMENTID]}'>{$itemArray[$INDEX_IMAGESCOUNT]}</span>";
		$MEIDGetty = '';
	}

#	Output TRs for an assignment
#	Skip archived/completed assignments
#	$currentStatusFilter
#print "<tr><td>AK900 ({$temp}) ({$STATUS_COMPLETE})</td></tr>";
$temp = "'{$STATUS_COMPLETE}'";
#if ($currentStatusFilter == $temp) { print "<tr><td>ARCHIVE ({$itemArray[$INDEX_ASSIGNMENTID]})</td></tr>"; }
		if ($itemArray[$INDEX_STATUS] != $STATUS_COMPLETE || ($currentStatusFilter == $temp && $itemArray[$INDEX_STATUS] == $STATUS_COMPLETE)) {
			$tempStatus = '';
#	RSVP temporary report/display
			if ($itemArray[$INDEX_RSVP] == 'y') { $tempStatus = "RSVP<br>({$itemArray[$INDEX_ASSIGNMENTID]})"; }
			print "<tr><td align='center'><a id='A_{$itemArray[$INDEX_ASSIGNMENTID]}' href=\"javascript:toggleAssignment('A_{$itemArray[$INDEX_ASSIGNMENTID]}','TH_{$itemArray[$INDEX_ASSIGNMENTID]}','TR_{$itemArray[$INDEX_ASSIGNMENTID]}');\">+</a></td><td bgcolor={$BGCOLORS[$itemArray[$INDEX_STATUS]]}><center><font size=-1>{$itemArray[$INDEX_ASSIGNMENTID]}</font></center></td><td>&nbsp;</td><td valign='top'>{$itemArray[$INDEX_GALLERY]}</td><td>&nbsp;</td><td valign='top' align='center'>$itemArray[$INDEX_REQUESTOR]</td><td>&nbsp;</td><td valign='top'>{$linkAssign}{$photographerLink}</td><td>&nbsp;</td><td align='center' valign='top'>{$linkAccept}{$linkDecline}{$linkUpload}{$linkRemind}{$statusPhotographerAction}</td><td>&nbsp;</td><td align='center' valign='top'>{$linkUpdate}{$linkDelete}{$statusAssignmentAction}</td><td>&nbsp;</td><td align='left' valign='top' colspan=3>{$MEIDGetty}{$MEIDLabel}<br>{$imagesGetty}{$linkGetty}</td><td>&nbsp;</td></tr>";
			print "<tr id='TH_{$itemArray[$INDEX_ASSIGNMENTID]}' style='display:none' bgcolor='#EEEEEE'><td bgcolor='#FFFFFF'>&nbsp;</td><td bgcolor='#FFFFFF'>&nbsp;</td><td bgcolor='#FFFFFF'>&nbsp;</td><td colspan=3>Details</td><td>&nbsp;</td><td colspan=2 align=left>Delivery Method</td><td><font size=-2>Spare</font></td><td>&nbsp;</td><td><font size=-2>Spare</font></td><td>&nbsp;</td><td><font size=-2>Spare</font></td><td>&nbsp;</td><td><font size=-2>Spare</font></td><td bgcolor='#FFFFFF'>&nbsp;</td></tr>";
			$tempSelect = selectMethods($itemArray[$INDEX_ASSIGNMENTID],$itemArray[$INDEX_DELIVERY]);
			print "<tr id='TR_{$itemArray[$INDEX_ASSIGNMENTID]}' style='display:none' bgcolor='#EEEEEE'><td bgcolor='#FFFFFF'>&nbsp;</td><td bgcolor='#FFFFFF'>&nbsp;</td><td bgcolor='#FFFFFF'>&nbsp;</td><td colspan=3><textarea class='form-control' rows='3' cols='60' id='details_{$itemArray[$INDEX_ASSIGNMENTID]}' name='details_{$itemArray[$INDEX_ASSIGNMENTID]}'>{$itemArray[$INDEX_DETAILS]}</textarea></td><td>&nbsp;</td><td colspan=2 align=left valign=top>{$tempSelect}</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>...</td><td bgcolor='#FFFFFF'>&nbsp;</td></tr>";
			print "<tr><td></td><td></td><td height='1'></td><td colspan='13' height='1' bgcolor='#666666'></td><td height='1'></td></tr>";
			$currentTRs = $currentTRs . "{$currentComma}'TH_{$itemArray[$INDEX_ASSIGNMENTID]}','TR_{$itemArray[$INDEX_ASSIGNMENTID]}'";
			$currentComma = ',';
			}
		}
	print "<script>organizationTRs['{$anchorsArray[$organizationArray[$index]]}'] = [{$currentTRs}];</script>";
	$index++;
	print "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td><a name='{$anchorsArray[$organizationArray[$index]]}'></a></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
}
print "</table><br><br><br><br><br>";

?>


</div>
</div>
</div> <!-- end container -->

      <center><font size=-2>&#169;&nbsp;2020-<script>dateyy = new Date();document.write(dateyy.getFullYear());</script> 
       Andrew Katsampes</font></center>

<form id="formphotographer" action="isi.schedule.photographer.php" method="post"><input type="hidden" name="schedulephotographer" id="schedulephotographer" ></form>
<form id="formrequest" action="isi.schedule.php" method="post">
<input type="hidden" name="assignmentID" id="assignmentID" value="">
<input type="hidden" name="assignmentIDList" id="assignmentIDList" value="">
<input type="hidden" name="assignmentGallery" id="assignmentGallery" value="">
<input type="hidden" name="assignmentDetails" id="assignmentDetails" value="">
<input type="hidden" name="assignmentDate" id="assignmentDate" value="">
<input type="hidden" name="assignmentDateEnd" id="assignmentDateEnd" value="">
<input type="hidden" name="assignmentTime" id="assignmentTime" value="">
<input type="hidden" name="assignmentLocation" id="assignmentLocation" value="">
<input type="hidden" name="assignmentContactName" id="assignmentContactName" value="">
<input type="hidden" name="assignmentContactPhone" id="assignmentContactPhone" value="">
<input type="hidden" name="assignmentContactEmail" id="assignmentContactEmail" value="">
<?php
print "<input type='hidden' name='assignmentEditorID' id='assignmentEditorID' value='{$currentEditor}'>";
?>
</form>
<form id="formcalendar" action="isi.schedule.calendar.php" method="post" target="_blank">
<?php
print "<input type='hidden' name='scheduleeditor' id='scheduleeditor' value=\"{$currentEditor}\">";
?>
<input type='hidden' name='public' id='public' value='no'>
<input type='hidden' name='organization' id='organization' value=''>
<input type='hidden' name='calendaradjust' id='calendaradjust' value='0'>
</form>

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

<form id="formconfirm" action="isi.schedule.confirm.php" method="post">
<input type='hidden' name='data' id='data' value='none'>
<?php
#	Post requests to the confirmation page/logic
print "<input type='hidden' name='editoreditor' id='editoreditor' value='{$currentEditor}'>";
print "<input type='hidden' name='editorfilter' id='editorfilter' value=\"{$currentStatusFilter}\">";
print "<input type='hidden' name='editorsort' id='editorsort' value='{$currentSort}'>";
?>
</form>

</body>
<script>
//	Javascript is stale. Display the refresh button
if (isStale == true) {
	tempObject = document.getElementById('staleButton');
	tempObject.style.display = "inline";
}
</script>

</html>