<?php
#	Load in the settings and includes
include ("includes/isi.schedule.tools.php");
#	--------------------------------------------
#	Overview
#	Given an assignment ID and gallery, present list of photographers.
#	Editor selects photographers to be canvassed for accept/decline.
#
#	--------------------------------------------
#	Issue:
#
#	--------------------------------------------
#print_r($_POST);
$currentID = $_POST['assignmentID'];
$currentIDList = "'" . $_POST['assignmentIDList'] . "'";
$currentEditor = $_POST['assignmentEditorID'];
$currentGallery = $_POST['assignmentGallery'];
$currentDetails = $_POST['assignmentDetails'];
$currentEventDate = $_POST['assignmentDate'];
$currentEventDateEnd = $_POST['assignmentDateEnd'];
$currentEventTime = $_POST['assignmentTime'];
$currentEventLocation = $_POST['assignmentLocation'];
$currentEventContactName = $_POST['assignmentContactName'];
$currentEventContactEmail = $_POST['assignmentContactEmail'];
$currentEventContactPhone = $_POST['assignmentContactPhone'];
$temp = explode(":", $currentEditor);
$currentEditorID = $temp[0];
$currentEditorName = $temp[1];
$currentEditorEmail = $temp[2];
$currentOrganization = $temp[3];
#$temp = explode(',', $currentIDList);
#$currentIDList = '';
#print_r($temp);
#$tempMax = count($temp);
#print "AK004 ({$tempMax})<br>";
#$comma = '';
#for ($i = 0; $i < $tempMax; $i++) {
#	$currentIDList .= $comma . "'". $temp[$i] . "'";
#	$comma = ',';
#}
#print "AK001 ({$currentID}) ({$currentEditor}) ({$currentOrganization}) ({$currentGallery}) ({$currentIDList})<br>";
#print "AK002 ({$currentID}) ({$currentDetails}) ({$currentEventDate}) ({$currentEventDateEnd})<br>";
$photographerArray = getPhotographers($currentOrganization);


?>
<html>
<head>
<title>ISI Schedule V1.6</title>
<META HTTP-EQUIV="Expires" CONTENT="Tue, 01 Jan 1980 1:00:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">

<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous"> -->
<link rel="stylesheet" href="includes/css/bootstrap.min.css">
<link rel="stylesheet" href="includes/css/bootstrap-datepicker3.min.css">
<link rel="stylesheet" href="includes/css/uploader.css">

<!--	ISI Schedule related functions	-->
<script type="text/javascript" src="includes/isi.schedule.js"></script>

<script type="text/javascript" src="includes/jquery_211.js"></script>
<!-- Latest compiled and minified BOOTSTRAP JavaScript - must be declared after jquery -->
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script> -->
<script type="text/javascript" src="includes/js/bootstrap.min.js"></script>
<!-- UPLOADER MAIN JS -->
<!-- <script src="includes/uploader.js"></script> -->
<script type="text/javascript" language="javascript" src="includes/CalendarPopup.js"></script>
<script>
//	Include JQuery support
//	Upload/APi related variables.
// Global variables
//	Issue diagnostic console.log messages.
var isConsoleTrace = false;
//var isConsoleTrace = true;

//	Editor ID from login.
var currentEditorID 	= '';
//	Information to be collected about the assignment
var assignDeliveryMode 	= '';
var assignDeadline 		= '';
var assignRSVP 			= 'n';
var assignMEID 			= 'n';
//	Count of assignments. Usually 1, but when multiple, need enough photographers.
<?php
$temp = explode(',', $currentIDList);
$temp = count($temp);
print "var assignEventCount = {$temp};\n";
?>

//	Google calendar and credentials based on league/collection.
//	For Cal/Stanford, there are sub-collections, but use only parent collection ID.
var isGoogleWaiting = true;
//	Google calendar status (enable/disable)
var isCalendarDisabled = true;

//	Constants related to html <file> images/files
var rowIndex = 0;
var tdIndex = 0;
var statusIndex = 0;
//	API access
//	Gate api activity
var isWaiting = false;
//	API related constants
var apiTarget 	= '';
var apiURL 		= '';
var apiParms 	= '';
var ajaxObject 	= '';
//	Google calendar
//	Target collection based on league.
var collectionID = '';
var collectionName = '';
var collectionMode = '';
//	Gallery ID. If created then change from initial. If existing, then it is a gallery ID.
//	Check to see if gallery ID exists within target collection (collectionID). If it does,
//	then galleryID has a value. If it does not, then it needs to be created and galleryID
//	will get valued.
var galleryID = 'initial';
//	Target gallery based on league, team vs team, and date.
//	If more than one matching gallery, then array is populated and tested.
var galleryIDs = new Array();
//	For NWSL, the gallery name will be prefixed.
//	ED home team vs away team, month, day, year
var galleryPrefix = '';
//	Event type contributes to format of gallery name
var activeEventType = '';

//	Array of potential photographers
//	Photographer index in database, in select box.
var photographerValue = new Array();
//	Photographer name in database, in select box.
var photographerText = new Array();
var photographerEmail = new Array();
<?php
#	Build/populate the photographerText array
	$temp = '';
	foreach ($photographerArray as $itemArray) {
		foreach ($itemArray as $item) {
			$temp = trim($item['name']);
			print "photographerText[{$item['id']}] = '{$temp}';";
			$temp = trim($item['email']);
			print "photographerEmail[{$item['id']}] = '{$temp}';";
		}
	}

?>
var assignPhotographerIDs = '';
var assignPhotographerName = '';
//	By click, the order of photographers is changed.
//	Any click occupies position, which is incremented on each click.
var currentPhotographerPosition = 0;

//	Timer related
var	uploadTimeout = '';
var timeStart = 0;
var timeEnd = 0;
var timeElapsed = 0;

//	Counter to prevent infinite loop.
var	runawayCounter = 0;

//
//	Photographer, league, date related variables.	
//
// Select date format: value="2010-01-09,January 9 2010"
// value = yyyy-MM-dd,MMM dd yyyy
// Select date format: value=January 9 2010"
// value = MMM dd yyyy

//	Hesitate before returning to prior page.
//	Rushing skips database update.
function priorPage() {
	runawayCounter++;
//console.log("priorPage Counter("+runawayCounter+")");
	if (runawayCounter >20) { return; }
	statusObject = document.getElementById('eventstatuslabel');
	test = statusObject.innerHTML;
//	console.log("priorPage ("+test+")");
	if (test == "Failed") { return; }
	if (test == "Done") { window.history.back();  return; }
	setTimeout(function() { priorPage(); }, 500);
	return;
}


function readytoassign() {
if (isConsoleTrace) { console.log("readytoassign"); }
var passedValidation;
//	Validate ...	
	passedValidation = validateInput();
	if (passedValidation == false) {
		return false;
	}
//	Update assignment with photographers
//	updatePhotographers();
	tempObject = document.getElementById('isi_photographer');
//	tempEmail = tempObject.options[1].value;
//	tempName = photographerText[tempEmail];
//	tempEmail = photographerEmail[tempEmail];
	tempObject = document.getElementById('isi_details');
	tempDetails = tempObject.value;
	tempDetails = tempDetails.trim();
	if (tempDetails == '') { tempDetails = '-'; }
	statusObject = document.getElementById('eventstatuslabel');
	statusObject.innerHTML = 'Contact request active ...';
<?php
#	Update the assignment with editor, photographers, deadline, ...
	print "updatePhotographers('{$currentID}', '{$currentGallery}', '{$currentEditorID}', assignPhotographerIDs, '{$currentEditorName}', '{$currentEditorEmail}', tempDetails, assignDeliveryMode, assignDeadline, assignRSVP, assignMEID, {$currentIDList}, '{$currentEventDate}', '{$currentEventDateEnd}', '{$currentEventTime}', '{$currentEventLocation}', '{$currentEventContactName}', '{$currentEventContactEmail}', '{$currentEventContactPhone}', statusObject);\n";
?>
//	Done. Go back to list.
//	If good update go back
//	If update fails, then stay with error message.
//	Hesitate to give the SQL time to complete. 750 milliseconds.
	setTimeout(function() { priorPage(); }, 1000);
return true;	
}
function validateInput() {
if (isConsoleTrace) { console.log("validateInput"); }
var test, tempObject;

//	Validate image delivery
	if (assignDeliveryMode == '') {
		tempObject = document.getElementById('isi_delivery');
		alert ('Method of image delivery is not defined.');
		tempObject.focus();
		return false;
	}

//	Validate deadline
	if (assignDeadline == '') {
		tempObject = document.getElementById('isi_deadline');
		alert ('Image upload deadline/target time is not defined.');
		tempObject.focus();
		return false;
	}

//	Validate photographer list
	if (assignPhotographerIDs == '') {
		tempObject = document.getElementById('isi_photographer');
		alert ('Photographer list is empty.');
		tempObject.focus();
		return false;
	}
//	Compare event count with photographers selected.
	temp = assignPhotographerIDs.split(',');
	temp = temp.length;
	if (temp < assignEventCount) {
		alert("Warning. Multi-photographer assignment. Photographer candidate list is less than number required.");
		tempObject = document.getElementById('isi_photographer');
		tempObject.focus();
		return false;
	}
	
		
//	Indicate event confirmation complete, near the button
	tempObject = document.getElementById('isi_schedule');
//	tempObject.value = "Retry event confirmation";
	tempObject.style.display = "none";
	
//if (isConsoleTrace) { console.log("Keyword/Gallery name ("+tempKeyword+")  Event("+activeEventType+")  Collection("+collectionID+") Collection("+collectionName+") GDate("+temp+")"); }
//return false;
	return true;
}

//	Delivery method selection.
function changeDelivery(inputObject) {
	assignDeliveryMode = '';
	currentIndex = inputObject.selectedIndex;
	assignDeliveryMode = inputObject.options[currentIndex].value;
	if (isConsoleTrace) { console.log("changeDelivery: Delivery("+assignDeliveryMode+")"); }
	tempObject = document.getElementById('isi_deadline');
	tempObject.focus();
	return;
}

//	Deadline selection.
function changeDeadline(inputObject) {
	assignDeadline = '';
	currentIndex = inputObject.selectedIndex;
	assignDeadline = inputObject.options[currentIndex].value;
	if (isConsoleTrace) { console.log("changeDeadline: Deadline("+assignDeadline+")"); }
	tempObject = document.getElementById('isi_photographer');
	tempObject.focus();
	return;
}

//	Photographer RSVP.
function changeRSVP(inputObject) {
	if (isConsoleTrace) { console.log("changeRSVP: Checked("+inputObject.checked+")"); }
	if (inputObject.checked == true) {
		assignRSVP = 'y';
	} else {
		assignRSVP = 'n';
	}
	tempObject = document.getElementById('isi_meid');
	tempObject.focus();
	return;
}

//	Getty MEID event/request.
function changeMEID(inputObject) {
	if (isConsoleTrace) { console.log("changeMEID: Checked("+inputObject.checked+")"); }
	if (inputObject.checked == true) {
		assignMEID = 'y';
	} else {
		assignMEID = 'n';
	}
	tempObject = document.getElementById('isi_schedule');
	tempObject.focus();
	return;
}

//	Pool of available photographers.
//	Click a photographer to add to assignment list of photographers (assignPhotographerIDs).
function changePhotographerPool(inputObject) {
	currentIndex = inputObject.selectedIndex;
//	Validate click position, selectedIndex.
	if (currentIndex == 0) { return; }
	if (isConsoleTrace) { console.log("changePool: ID("+inputObject.options[currentIndex].value+")"); }
	photographerValue.push(inputObject.options[currentIndex].value);
	assignPhotographerIDs = photographerValue.join(':');
	if (isConsoleTrace) { console.log("changePool: IDs("+assignPhotographerIDs+")"); }
	tempMax = photographerValue.length;
	tempObject = document.getElementById('isi_photographer');
//	Update the select element with revised list in new order.
	resetSelect(tempObject, '');
	updateSelect(tempObject, tempMax, photographerValue, photographerText);
	tempObject.size = (photographerValue.length) +1;
	return;
}
//	Arrange the order of assignment list of photographers (assignPhotographerIDs).
function changePhotographerOrder(inputObject) {
	currentIndex = inputObject.selectedIndex;
//	Validate click position, selectedIndex.
	if (currentIndex == 0) { return; }
	currentIndex--;
//	Reorder array of photographers. 
	tempArray = new Array();
	tempArray[0] = photographerValue[currentIndex];
	photographerValue[currentIndex] = '';
	tempMax = photographerValue.length;
	tempIndex = 1;
	for (i=0; i<tempMax; i++) {
		if (photographerValue[i] != '') {
			tempArray[tempIndex] = photographerValue[i];
			tempIndex++;
		}
	}
	photographerValue = tempArray;

//	Selected by click photographer into next position in the photographer IDs array (photographerValue).
	assignPhotographerIDs = photographerValue.join(':');
	if (isConsoleTrace) { console.log("changeOrder: IDs("+assignPhotographerIDs+")"); }
	tempMax = photographerValue.length;
	tempObject = document.getElementById('isi_photographer');
//	Update the select element with revised list in new order.
	resetSelect(tempObject, '');
	updateSelect(tempObject, tempMax, photographerValue, photographerText);
	
	return;
}
//	Arrange the order of assignment list of photographers (assignPhotographerIDs).
function delete_OLD_changePhotographerOrder(inputObject) {
	currentIndex = inputObject.selectedIndex;
//	Validate click position, selectedIndex.
	if (currentIndex == 0) { return; }
	if (currentPhotographerPosition >= photographerValue.length) { return; }
	if (currentPhotographerPosition >= currentIndex) { return; }
//	
	testTempID = currentIndex;
	testTempID--;
	testTempIndex = 0;
	for (i=currentIndex -1; i > currentPhotographerPosition; i--) {
		testTempIndex = i -1;
		photographerValue[i] =  photographerValue[testTempIndex];
	}
//	Selected by click photographer into next position in the photographer IDs array (photographerValue).
	photographerValue[currentPhotographerPosition] = inputObject.options[currentIndex].value;
	currentPhotographerPosition++;
	assignPhotographerIDs = photographerValue.join(':');
	tempIndex = assignPhotographerIDs.indexOf(':end');
	if (tempIndex != -1) { assignPhotographerIDs = assignPhotographerIDs.substring(0,tempIndex); }
	if (isConsoleTrace) { console.log("changeOrder: IDs("+assignPhotographerIDs+")"); }
	tempMax = photographerValue.length;
	tempObject = document.getElementById('isi_photographer');
//	Update the select element with revised list in new order.
	resetSelect(tempObject, '');
	updateSelect(tempObject, tempMax, photographerValue, photographerText);
	
	return;
}


//	As a photographer selection occurs,
//	Collect photographer ID and name.
function delete_changePhotographer(inputObject) {
	assignPhotographerID = '';
	assignPhotographerName = '';
	currentIndex = inputObject.selectedIndex;
	assignPhotographerID = inputObject.options[currentIndex].value;
	assignPhotographerName = inputObject.options[currentIndex].text;
	if (isConsoleTrace) { console.log("changePhotographer: ID("+assignPhotographerID+") Name("+assignPhotographerName+")"); }
	tempObject = document.getElementById('isi_schedule');
	tempObject.focus();
	return;
}


//	Event labels and buttons
//	Display or not
//	inputState = "none" or
//	inputState = "inline"
function eventVisible(inputState) {
tempObject = document.getElementById('eventlabel01');
tempObject.style.display = inputState;
tempObject = document.getElementById('isi_league');
tempObject.style.display = inputState;
photographerVisible("inline");
//tempObject = document.getElementById('eventlabel02');
//tempObject.style.display = inputState;
//tempObject = document.getElementById('isi_date');
//tempObject.style.display = inputState;
//tempObject = document.getElementById('dateLabel');
//tempObject.style.display = inputState;
tempObject = document.getElementById('eventlabel03');
tempObject.style.display = inputState;
tempObject = document.getElementById('isi_eventtype');
tempObject.style.display = inputState;
tempObject = document.getElementById('isi_schedule');
tempObject.style.display = inputState;
return;
}

//	Select photographer labels and buttons
//	Display or not
//	inputState = "none" or
//	inputState = "inline"
function photographerVisible(inputState) {
	tempObject = document.getElementById('photographersection');
	tempObject.style.display = inputState;
return;
}

//	Reset the select box
//	Used by isi.schedule.js
function resetSelect(inputSelect, inputFilter) {
var i;
var index;
var tempMax, tempObject, tempText, tempValue, currentIndex, currentData;
	inputMax = inputSelect.options.length;
	if (inputMax == 1) { return; }
	inputMax--;
	for (i=inputMax; i>0; i--) {
//		if (isConsoleTrace) { console.log("resetSelect: value:text("+i+":"+inputSelect.options[i].value+":"+inputSelect.options[i].text+")"); }
		if (inputFilter != '' && (inputFilter == inputSelect.options[i].value)) {
			continue;
		}
		inputSelect.options[i] = null;
	}
	return;
}
//	Update the select box
//	Used by isi.schedule.js
//	inputSelect = select object
//	inputMax	= maximum number of options
//	inputData	= source for options
//	inputText	= Text in a select box.
function updateSelect(inputSelect, inputMax, inputData, inputText) {
var i;
var index;
var tempMax, tempObject, tempText, tempValue, currentIndex, currentData, noText;
console.log ("updateSelect ID("+inputSelect.id+") length("+inputSelect.options.length+")");
	index = 1;
	index = inputSelect.options.length;
	noText = true;
//	Normally value = text in the select. If passed, inputText, defines other text for a value.
//	Used for event type select.
	if (typeof(inputText) == 'object') { noText = false; }
	for (i=0; i<inputMax; i++) {
//		tempText = inputData[i];
//		tempValue= tempText;
		tempValue = inputData[i];
//	Logic for matching value/text or non-matching value/text
		if (noText) {
			tempText= tempValue;
		} else {
			tempText = inputText[tempValue];
		}
		tempOption = new Option(tempText,tempValue);
		inputSelect.options[index] = tempOption;
		index++;
	}
	return;
}


//	Debug
//	Diagnostic email via api
//	Input photographer, collection ID, gallery ID, gallery Name
//	isWaiting updated, as a gate.
function apiDiagnostic(inputRequest, inputName, inputStatus, inputMode) {
	if (inputRequest == 'gallery' && DEBUGSTATUS != true) { return; }
	if (inputRequest == 'timing' && PERFORMANCESTATUS != true) { return; }
if (isConsoleTrace) { console.log("apiDiagnostic request("+inputRequest+")"); }
//	Reset
var tempObject;
//	Photographer
	tempObject = document.getElementById("isi_editor_id");
	photographer = tempObject.value;

//	Prepare URL, parameters to call API.
// apiTarget = 'http://www.akactionphoto.com/isi.debug.php';
apiTarget = 'https://assignments.isiphotos.com/isi.debug.php';
//	Gallery request
if (inputRequest == 'gallery') {
	var tempName = inputName;
	tempName = tempName.replace('&', '%26');
	apiParms = [
	'req=' + inputRequest,
	'pfn=' + photographer,
	'cid=' + collectionID,
	'gid=' + galleryID,
	'gfn=' + tempName,
	'gstat=' + inputStatus,
	'gmode=' + inputMode
];
}
//	Build URL
temp = apiParms.join("&");
apiURL = apiTarget + '?' + temp;
//	alert("AK002 ("+apiURL+")");
//	Call API
ajaxObject = $.ajax(apiURL);
//	Done event
ajaxObject.done(function(dataObject) {
	temp = dataObject;
	if (temp == 'successful') {
		tempTableObject = document.getElementById('statusTable');
		indicateDiagnostic(tempTableObject, 'successful');
	} else {
		tempTableObject = document.getElementById('statusTable');
		indicateDiagnostic(tempTableObject, 'failed');
	}
});


}

//	Google calendar related functions.
//	A. Insert event into google calendar.
//	B. Insert event into database.
function insertAssignmentCalendar() {
	if (isConsoleTrace) { console.log("insertAssignmentCalendar +++++++"); }
return;
//	Check Google calendar status.
	if (isCalendarDisabled) {
//	B. Insert event into database - only
		if (isConsoleTrace) { console.log("insertAssignmentCalendar Calendar logic skipped."); }
//	Assignment in database has: eventID, collectionID, collectionName, gallery name, event date, start time, end time.
		tempObject = document.getElementById('isi_photographer');
		assignPhotographerName = tempObject.options[1].text;
		tempObject = document.getElementById('isi_details');
		tempDetails = tempObject.value;
		if (tempDetails == '') { tempDetails = '-'; }
		tempObject = document.getElementById('isi_selected_keyword');
		insertAssignmentDB(google_eventID, collectionID, collectionName,tempObject.value,'GGGtempDate','GGGassignDeliveryMode',tempDetails,currentEditorID,assignPhotographerIDs,assignPhotographerName);
		return;
	 }
}
//	401 Code
//function googleTest(apiObject) {
//if (isConsoleTrace) { console.log("apiObject follows Auth("+isAuthorized+") ("+apiObject['access_token']+") ("+apiObject['code']+") ("+apiObject['client_id']+")"); }
//if (isConsoleTrace) { console.dir(apiObject); }
//	temp = typeof(apiObject['access_token']);
//	if (temp == 'undefined' || apiObject['code'] == '401') { setTimeout(function() { googleApiKey(); }, 1000); return;}
//if (isConsoleTrace) { console.log("apiObject temp("+temp+")"); }
//	if (temp != 'undefined' && apiObject['access_token'] != '') {
//		isAuthorized = true;
//		googleReady(true);
//	}
//}


//	Generate a table row with text that diagnostic/debug complete
function indicateDiagnostic(tableObject, inputStatus) {
	var rowObject = tableObject.insertRow(rowIndex);
	var cell1 = rowObject.insertCell(0);
	var cell2 = rowObject.insertCell(1);
	var cell3 = rowObject.insertCell(2);
	var cell4 = rowObject.insertCell(3);
	cell1.innerHTML = "&nbsp;";
	cell2.innerHTML = "&nbsp;";
	if (inputStatus == 'failed') {
		cell2.bgColor = '#FF0000';
	}
	if (inputStatus == 'successful') {
		cell2.bgColor = '#5cb85c';
	}
	cell3.id = "status"+statusIndex;
	cell3.innerHTML = "&nbsp;&nbsp;&nbsp;Diagnostics execute "+inputStatus+".";
	cell4.innerHTML = "&nbsp;";
	rowIndex++;
}

</script>
</head>
<body style="margin-top: 20px;">
<!--	<br><br><font color=#CC0000>Note: AK Diagnosing. Extra messages issued.</font><br><br>	-->
<!--	<br><br><font color=#CC0000>Note: AK Tinkering.</font><br><br>	-->
<div class="container">
<!--	<h1>Photo Uploader</h1>	-->
	<font size=+3>ISI Schedule Photographers</font>&nbsp;&nbsp;<script type="text/javascript">if (isConsoleTrace) {document.write("<font size=-1 color=#3300FF>"); }</script>(2020-08-26)</font>
<div class="row"> 
<div class="col-xs-12">
	

<form name="isi_transfer" id="isi_transfer" action="none.php" method="post" enctype="multipart/form-data">
<input type="hidden" id="isi_selected_keyword" name="isi_selected_keyword" value="">

<?php
print "<label id='eventlabel01'>Assignment: {$currentGallery}</label>";
if ($currentEventDate != $currentEventDateEnd) {
	print "<br><label id='eventlabel02'><font color='#FF0000'>Note: </font>Multiple day assignment {$currentEventDate} through {$currentEventDateEnd}</label>";
}
?>

<section id="photographersection">
	<div class="form-group" style="width:350px">
		<label>Additional assignment details</label>
<?php
	print "<textarea class='form-control' rows='2' cols='60' id='isi_details' name='isi_details'>{$currentDetails}</textarea>";
?>
</div>
	<div class="form-group" style="width:220px">
		<label>Image delivery</label>
	<select class="form-control" name="isi_delivery" id="isi_delivery" onChange='javascript:changeDelivery(this);'>
	<option value='' >Select image delivery method</option>
	<option value='i' >ISIPhotos.com</option>
	<option value='b' >Box</option>
<!--	<option value='ps' >PhotoShelter</option>	-->
<!--	<option value='bs' >Box and PhotoShelter</option>	-->
	<option value='bi' >Box and ISI Photos</option>
	<option value='e' >eMail</option>
	<option value='o' >Other</option>
	</select>
</div>
	<div class="form-group" style="width:220px">
		<label>Image upload deadline</label>
	<select class="form-control" name="isi_deadline" id="isi_deadline" onChange='javascript:changeDeadline(this);' size=5>
<!--	<option value='' >Select target time</option>	-->
	<option value=8>Immediate</option>
	<option value=12>Within 12 hours</option>
	<option value=24>Within 24 hours</option>
	<option value=36>Within 36 hours</option>
	<option value=72>Within 72 hours</option>
	</select>
</div>


<table border=0 width=750><tr><td>
		<label>Photographer candidate list</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="Reset list" onclick="javascript:tempObject=document.getElementById('isi_photographer');resetSelect(tempObject,'');tempObject.size=1;photographerValue=new Array();">
</td><td width=4%>&nbsp;</td><td>
		<label>Photographer pool</label>
</td></tr><tr><td valign="top">
<div class="form-group" style="width:410px">
	<select class="form-control" name="isi_photographer" id="isi_photographer" onChange='javascript:changePhotographerOrder(this);'>
	<option value='' >Optional: Click photographer to reorder.</option>
	</select>
</div>
</td><td width=4% valign="top"><br><img src="icons/bluearrowleft.png" width="30"></td><td valign="top">
<div class="form-group" style="width:300px">
	<select class="form-control" name="isi_photographer_pool" id="isi_photographer_pool" size="12" onChange='javascript:changePhotographerPool(this);'>
	<option value='' >Click to select assignment photographers</option>
<?php
	$endflag = true;
	$i = 0;
	$index = 0;
	$test = 0;
	foreach ($photographerArray as $itemArray) {
		foreach ($itemArray as $item) {
			$test = strpos($item['name'], '(New)');
			if ($endflag == true && $test !== false) {
				$endflag = false;		
				print "<option value='end'>--------- End of recent list ---------</option>";
			}
			print "<option value='{$item['id']}'>{$item['name']}</option>";
		}
	}



#		endflag = true;
#		i=0;
#		index = 0;
#		for (i=0; i<tempMax; i++) {
#			tempIndex = dataObject.list[i].name.indexOf("(New)");
#			if (endflag == true && tempIndex != -1) {
#				endflag = false;
#				tempValue[index] = 'end';
#				tempText['end'] = '--------- End of list ---------';
#				index++;
#			}
#//		Photographers who have photographed that league are in the list (photographerValue).
#//			photographerValue[index] = dataObject.list[i].id;
#			photographerText[dataObject.list[i].id] = dataObject.list[i].name;
#			tempValue[index] = dataObject.list[i].id;
#			tempText[dataObject.list[i].id] = dataObject.list[i].name;
#			index++;
#		}




?>
	</select>
</div>
</td></tr></table>
</section>
<br>
<input type="checkbox" id="isi_rsvp" onclick="javascript:changeRSVP(this);">&nbsp;&nbsp;&nbsp;Photographer must respond (accept/decline)
<br>
<input type="checkbox" id="isi_meid" onclick="javascript:changeMEID(this);">&nbsp;&nbsp;&nbsp;Initiate MEID request
<br><br>

<input class="btn btn-default" type="button" id="isi_schedule" value="Click to contact photographer(s)" onClick="javascript:readytoassign();">
&nbsp;&nbsp;<span class="text-success" id="eventstatuslabel"></span>
<br><br>
<input class="btn btn-default" type="button" value="Cancel" onClick="javascript:window.history.back();">


</form>



<table border="0" id="statusTable" width="70%">
</table>
</div>
</div>
</div> <!-- end container -->
<br>

      <center><font size=-2>&#169;&nbsp;2020-<script>dateyy = new Date();document.write(dateyy.getFullYear());</script> 
       Andrew Katsampes</font></center>

<form id="formeditor" action="isi.schedule.editor.php" method="post"><input type="hidden" name="scheduleeditor" id="scheduleeditor" ></form>
</body>
</html>