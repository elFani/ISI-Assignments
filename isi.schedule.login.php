<?php
#	Test for http://
$isHTTP = false;
$typeHTTPS = gettype($_SERVER['HTTPS']);
$typeSCRIPT = gettype($_SERVER['SCRIPT_URI']);
if ($typeHTTPS == 'NULL') { $isHTTP = true; }
if ($typeSCRIPT == 'string') {
 	$isHTTP = true;
	if (strpos($temp,'https://') == '') { $isHTTP = true; }
	if (strpos($temp,'http://') == 0) { $isHTTP = true; }
}
#	Please follow link to ISI Photo Uploader
if ($isHTTP) {
	print "<br><br><center><font size=+1>Please follow link to ISI Photos Schedule Login.</font></center><br>";
	print "<center><font size=+1><a href='https://assignments.isiphotos.com/isi.schedule.login.php'>https://assignments.isiphotos.com/isi.schedule.login.php</a></font></center><br>";
	print "<center><font size=+1>Link as text: <input type='text' value='https://assignments.isiphotos.com/isi.schedule.login.php' size=50></font></center><br>";
	exit;
}
#	Load in the settings and includes
include ("includes/isi.schedule.tools.php");
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
?>
<html>
<head>
<title>ISI Schedule Login V1.6</title>
<META HTTP-EQUIV="Expires" CONTENT="Tue, 01 Jan 1980 1:00:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<meta http-equiv="Cache-control" content="no-cache">
<link rel="icon" type="image/png" href="https://assignments.isiphotos.com/icons/ISI_logo.png" />

<link rel="stylesheet" href="includes/css/bootstrap.min.css">
<link rel="stylesheet" href="includes/css/uploader.css">

<!--	ISI Schedule related functions	-->
<script type="text/javascript" src="includes/isi.schedule.js"></script>
<!--	AJAX/JSON	-->
<script type="text/javascript" src="includes/jquery_211.js"></script>
<script type="text/javascript" src="includes/js/bootstrap.min.js"></script>
<!--	Calendar popup	-->
<script type="text/javascript" language="javascript" src="includes/CalendarPopup.js"></script>
<script>
//	Authenticate editor or photographer
var isEditor	= true;
//	Email has dual role, editor and photographer
var isDual		= true;
//	Editor ID after changeEditor.
var currentEditor = '';
var currentEditorID = '';
var currentPhotographerID = '';
var currentStatusFilter = '';
//	Issue diagnostic console.log messages.
var isConsoleTrace = false;
//var isConsoleTrace = true;

//	Constants related to html <file> images/files
var rowIndex = 0;
var statusIndex = 0;
//	API related constants
var apiTarget = '';
var apiURL = '';
var apiParms = '';
var ajaxObject = '';

//	Select editor's organization(s)
function changeStatus(inputObject) {
	tempComma = ''
	tempMax = inputObject.options.length;
	if (inputObject.options[0].selected == true) {
		currentStatusFilter = "'a','b','c','d','f','g'";
		return;
	}
	for (tempIndex = 1; tempIndex<tempMax; tempIndex++) {
		if (inputObject.options[tempIndex].selected == true) {
			currentStatusFilter = currentStatusFilter + tempComma + "'" + inputObject.options[tempIndex].value + "'";
			tempComma = ',';
		}	
	}
	return;
}


//	Select editor's organization(s)
//	Update the status filter
function changeEditor(inputObject) {
//	Update the status filter
	currentStatusFilter = '';
	tempObject = document.getElementById('isi_status');
	changeStatus(tempObject);
//	Editor's organization
	currentIndex = inputObject.selectedIndex;
	temp = inputObject.options[currentIndex].value;
	if (isConsoleTrace) { console.log("changeEditor: value("+temp+")"); }
	tempArray = temp.split(":");
	currentEditorID = tempArray[0] + ":" +tempArray[1];
	if (isConsoleTrace) { console.log("changeEditor: currentEditorID("+currentEditorID+")"); }
//	currentEditor = tempArray[1];
//	if (isConsoleTrace) { console.log("changeEditor: currentEditor("+currentEditor+")"); }
//	collectionID = tempArray[3];
//	if (isConsoleTrace) { console.log("changeEditor: collectionID("+collectionID+")"); }
	collectionName = tempArray[2];
	if (isConsoleTrace) { console.log("changeEditor: collectionName("+collectionName+")"); }
	
//	Link to page listing assignments for this editor
	linkEditor();
	return;
}

//	Link to ISI Schedule Editor
function linkEditor(inputStatus) {
	tempObject = document.getElementById('isi_editor_id');
	temp = tempObject.value;
	tempObject = document.getElementById('scheduleeditor');
	tempObject.value = currentEditorID + ":" + temp + ":" + collectionName;
	tempObject = document.getElementById('schedulefilter');
	tempObject.value = currentStatusFilter;
//	Sort by status or date
	tempObject = document.getElementById('isi_sort');
	temp = tempObject.selectedIndex;
	temp = tempObject.options[temp].value;
	tempObject = document.getElementById('schedulesort');
	tempObject.value = temp;
	tempObject = document.getElementById('formeditor');
	tempObject.submit();
}

//	Link to ISI Schedule Photograper, and list assignments.
function linkPhotographer(inputID) {
	tempObject = document.getElementById('schedulephotographer');
	tempObject.value = inputID;
	tempObject = document.getElementById('formphotographer');
	tempObject.submit();
}

//	Authenticate
//	Input editor/photographer id and pw
//	Input: true = editor, false = photographer
function apiAuthISI() {
var temp = '';
var inputID = '';
var inputPW = '';
var tempObject;

//	Hide the editor section			
	tempObject = document.getElementById('editorsection');
	tempObject.style.display = 'none';
	tempObject = document.getElementById('resetstatuslabel01');
	tempObject.style.display = 'none';

var tempRole = 'Editor';
if (isEditor == false) { tempRole = 'Photographer'; }

//	Validate editor/photograher ID
	tempObject = document.getElementById("isi_editor_id");
	if (tempObject == null) {
		alert (tempRole + ' email required.');
		tempObject.focus();
		return true;
	}
	temp = tempObject.value;
	if (temp == '') {
		alert (tempRole + ' email required.');
		tempObject.focus();
		return true;
	}
	inputID = temp;

//	Validate photographer PIN
	tempObject = document.getElementById("isi_editor_pin");
	temp = tempObject.value;
	if (temp == '') {
		alert (tempRole + ' PIN required.');
		tempObject = document.getElementById("isi_editor_pin");
		tempObject.focus();
		return true;
	}
	inputPW = temp;

//	Prepare URL, parameters to call API.
apiTarget = 'https://assignments.isiphotos.com/includes/isi.schedule.tools.php';
if (isEditor) {
	apiParms = new Object();
	apiParms["req"] = 'auth';
	apiParms["ee"] = inputID;
	apiParms["ep"] = inputPW;
} else {
	apiParms = new Object();
	apiParms["req"] = 'authp';
	apiParms["pe"] = inputID;
	apiParms["pp"] = inputPW;
}
//	Build URL
//temp = apiParms.join("&");
//apiURL = apiTarget + '?' + temp;
//temp = encodeURI(temp);
if (isConsoleTrace) { console.log("apiAuthISI URL("+apiTarget+")"); }
//	Call API
//ajaxObject = $.ajax(apiURL);
//    data: {req: tempRequest, ee: inputID, ep:inputPW, pe: inputID, pp:inputPW},
//    data: {apiParms},
ajaxObject = $.ajax(apiTarget, {
    type: 'POST',
    data: apiParms,
    error: function (jqXhr, textStatus, errorMessage) {
            console.log('Error' + errorMessage);
    }
});

//	Done/Success event
ajaxObject.success(function(dataObject) {
	tempValue = new Array();
	tempText = new Array();
	tempJSON = JSON.parse(dataObject);
	tempMax = tempJSON.count;
	tempArray = tempJSON.list;
if (isConsoleTrace) { console.dir(tempJSON); }
	if (tempMax > 0 ) {
		if (isEditor) {
			i=0;
			index = 0;
//	Collect organizations for this editor
//	All entry
			tempValue[index] = "all:" + tempJSON.list[i].editorName + ":all";
			tempText["all:" + tempJSON.list[i].editorName + ":all"] = "ALL Organizations";
			index++;
			for (i=0; i<tempMax; i++) {
				tempValue[index] = tempJSON.list[i].editorID + ":" + tempJSON.list[i].editorName + ":" + tempJSON.list[i].organization;
				tempText[tempJSON.list[i].editorID + ":" + tempJSON.list[i].editorName + ":" + tempJSON.list[i].organization] = tempJSON.list[i].organization;
				index++;
			}
if (isConsoleTrace) { console.log("Values ("+tempMax+")"); }
if (isConsoleTrace) { console.dir(tempValue); }
//	List of organizations.
			tempMax++;
			tempObject = document.getElementById("isi_editor");
			updateSelect(tempObject, tempMax, tempValue, tempText);
//	Display successful status	
			indicateISIAuth('successful');
//	Check photographer (dual role?)
			isEditor = false;
			apiAuthISI();
//	Display the editor section		
			tempObject = document.getElementById('editorsection');
			tempObject.style.display = 'inline';
		} else {
//	Collect photographer ID
			currentPhotographerID = tempJSON.list[0].photographerID;
//	Display successful status	
			indicateISIAuth('successful');
//	Display photographer assignments button
			tempObject = document.getElementById('isi_photographer');
			tempObject.style.display= "inline";
		}
	} else {
//	Display failed status
		if (isEditor) {
			isDual = false;
//	Editor failed, check for photographer
			isEditor = false;
			apiAuthISI();
		} else {
//	If photographer fails then issue failed feedback message.
			if (isDual == false) {
				indicateISIAuth('failed');
			}
		}
	}
});

}

//	Reset editor/photographer PIN/Password
//	Send email with new password
function resetPassword() {
	tempObject = document.getElementById('isi_editor_id');
	tempID = tempObject.value;
	if (tempID == '') {
		alert ('Email required.');
		tempObject.focus();
		return true;
	}
	if (isConsoleTrace) { console.log("resetPassword ID("+tempID+")"); }

//	if ($request == 'dbrp') {
//	$tempInputEmail = $_GET['ie'];
//	Prepare URL, parameters to call API.
	apiTarget = 'https://assignments.isiphotos.com/includes/isi.schedule.tools.php';
	apiParms = new Object();
	apiParms["req"] = 'dbrp';
	apiParms["ie"] = tempID;
//temp = apiParms.join("&");
//apiURL = apiTarget + '?' + temp;
//temp = encodeURI(temp);
if (isConsoleTrace) { console.log("resetPassword URL("+apiTarget+")"); }
//	Call API
//ajaxObject = $.ajax(apiURL);
ajaxObject = $.ajax(apiTarget, {
    type: 'POST',
    data: apiParms,
    error: function (jqXhr, textStatus, errorMessage) {
            console.log('Error' + errorMessage);
    }
});

//	Done/Success event
ajaxObject.success(function(dataObject) {
	tempJSON = JSON.parse(dataObject);
	if (isConsoleTrace) { console.dir(tempJSON); }
	indicateReset(tempJSON['status']);
	tempObject = document.getElementById("isi_editor_pin");
	tempObject.focus();
});
	return;
}



//	Update editor/photographer PIN/Password
function executePassword() {
	tempObject = document.getElementById('isi_editor_id');
	tempID = tempObject.value;
	if (tempID == '') {
		alert ('Email required.');
		tempObject.focus();
		return true;
	}
	tempObject = document.getElementById('isi_editor_pin');
	tempPW = tempObject.value;
	if (tempPW == '') {
		alert ('PIN required.');
		tempObject.focus();
		return true;
	}
	if (isConsoleTrace) { console.log("executePassword ID("+tempID+") PW("+tempPW+")"); }
//	Reset the authentication text
	tempObject = document.getElementById('authenticatestatuslabel01');
	tempObject.textContent = '';
	
//	if ($request == 'dbpw') {
//	$tempInputEmail = $_GET['ie'];
//	$tempInputPassword = $_GET['ip'];
//	Prepare URL, parameters to call API.
	apiTarget = 'https://assignments.isiphotos.com/includes/isi.schedule.tools.php';
	apiParms = new Object();
	apiParms["req"] = 'dbpw';
	apiParms["ie"] = tempID;
	apiParms["ip"] = tempPW;
//temp = apiParms.join("&");
//apiURL = apiTarget + '?' + temp;
//temp = encodeURI(temp);
if (isConsoleTrace) { console.log("executePassword URL("+apiTarget+")"); }
//	Call API
//ajaxObject = $.ajax(apiURL);
//    data: {req: 'dbpw', ie: tempID, ip: tempPW},
ajaxObject = $.ajax(apiTarget, {
    type: 'POST',
    data: apiParms,
    error: function (jqXhr, textStatus, errorMessage) {
            console.log('Error' + errorMessage);
    }
});
//	Done/Success event
ajaxObject.success(function(dataObject) {
	tempJSON = JSON.parse(dataObject);
	if (isConsoleTrace) { console.dir(tempJSON); }
	indicatePassword(tempJSON['status']);
	tempObject = document.getElementById("isi_authenticate");
	tempObject.focus();
});
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
if (isConsoleTrace) { console.log ("updateSelect ID("+inputSelect.id+") length("+inputSelect.options.length+")"); }
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

//	Indicate that authentication completed
//	When successful, present update pin button
function indicateISIAuth(inputStatus) {
	tempObject = document.getElementById('authenticatestatuslabel01');
	if (inputStatus == 'failed') {
		tempObject.style.color = "#FF0000";
//	Reset isEditor flag.
		isDual = true;
		isEditor = true;
	}
	if (inputStatus == 'successful') {
		tempObject.style.color = "#5cb85c";
//	Present update pin button after successful authentication
		tempObject = document.getElementById('isi_update');
		tempObject.style.display = "inline";
//	Hide reset pin button after successful authentication
		tempObject = document.getElementById('isi_reset');
		tempObject.style.display = "none";
	}
	tempObject = document.getElementById('authenticatestatuslabel01');
	tempObject.textContent = "Authentication "+inputStatus+".";
}

//	Indicate that password update/change completed
function indicatePassword(inputStatus) {
	tempObject = document.getElementById('updatestatuslabel01');
	tempObject.style.color = "#5cb85c";
	if (inputStatus == 'failed') {
		tempObject.style.color = "#FF0000";
	}
	tempObject.textContent = "Password update: "+inputStatus+".";
}

//	Indicate that password reset completed
function indicateReset(inputStatus) {
	tempObject = document.getElementById('resetstatuslabel01');
	tempObject.style.color = "#5cb85c";
	if (inputStatus == 'failed') {
		tempObject.style.color = "#FF0000";
	}
	tempObject.textContent = "Password reset: "+inputStatus+".";
}

</script>
</head>
<body style="margin-top: 20px;">
<!--	<br><br><font color=#CC0000>Note: AK Diagnosing. Extra messages issued.</font><br><br>	-->
<!--	<br><br><font color=#CC0000>Note: AK Tinkering.</font><br><br>	-->
<div class="container">
<!--	<h1>Photo Uploader</h1>	-->
	<font size=+3>ISI Photos Schedule Login</font>&nbsp;&nbsp;<script type="text/javascript">if (isConsoleTrace) {document.write("<font size=-1 color=#3300FF>"); }</script>(2020-08-17)</font>
<div class="row"> 
<div class="col-xs-12">

<div class="form-group" style="width:450px">
	<label>Editor/Photographer EMail</label>
	<input class="form-control" type='text' value='' name='isi_editor_id' id='isi_editor_id' size='30'>
	</div>

<div class="form-group" style="width:450px">
	<label>Editor/Photographer Password</label>
	<input class="form-control" type='text' value='' name='isi_editor_pin' id='isi_editor_pin' size='30' onBlur='javascript:tempObject = document.getElementById("isi_authenticate");javascript:tempObject.focus();'>
	</div>
<div class="form-group" style="width:450px">
<input class='btn btn-default' type='button' id='isi_authenticate' value='Authenticate' onClick='javascript:apiAuthISI(true);'>
&nbsp;&nbsp;<span id='authenticatestatuslabel01' class='text-success'></span>
<input class='btn btn-default' type='button' id='isi_update' value='Change password' onClick='javascript:executePassword();'>
&nbsp;&nbsp;<span id='updatestatuslabel01' class='text-success'></span>
<input class='btn btn-default' type='button' id='isi_reset' value='Reset password' onClick='javascript:resetPassword();'>
&nbsp;&nbsp;<span id='resetstatuslabel01' class='text-success'></span>
</div>

<section id="editorsection" style="display:none;">
	<div class="form-group" style="width:250px" >
		<label>Filter by Status</label>
	<select class="form-control" name="isi_status" id="isi_status" multiple style="height:120px">
	<option value='a,b,c,d,f,g' selected>All</option>
	<option value='a'>Initial</option>
	<option value='b'>Request</option>
	<option value='c'>Assigned</option>
	<option value='d'>Upload</option>
	<option value='f'>Getty</option>
	</select>
</div>

	<div class="form-group" style="width:250px" >
		<label>Sort by ...</label>
	<select class="form-control" name="isi_sort" id="isi_sort" size=3>
	<option value='assign.assign_status' selected>Status</option>
	<option value='assign.assign_date'>Date (ascending)</option>
	<option value='assign.assign_date desc'>Date (descending)</option>
	</select>
</div>

<div class="form-group" style="width:450px">
<label id="eventlabel03">Organizations</label>
<select class='form-control' name='isi_editor' id='isi_editor' onChange='javascript:changeEditor(this);'>
	<option value='none'>Select Organization for editor</option>
</select>
</div>

</div>

<div class="form-group" style="width:450px">
<input class='btn btn-default' type='button' id='isi_photographer' value='Photographer assignments' onClick='javascript:linkPhotographer(currentPhotographerID);'>
</div>

</form>



<table border="0" id="statusTable" width="70%">
</table>

<script>
//	Hide the photographer assignments list button.
tempObject = document.getElementById('isi_photographer');
tempObject.style.display = "none";
//	Hide the Change PIN button until after authenticated
tempObject = document.getElementById('isi_update');
tempObject.style.display = "none";

</script>

      <center><font size=-2>&#169;&nbsp;2020-<script>dateyy = new Date();document.write(dateyy.getFullYear());</script> 
       Andrew Katsampes</font></center>

<form id="formeditor" action="isi.schedule.editor.php" method="post">
<input type="hidden" name="scheduleeditor" id="scheduleeditor" value="">
<input type="hidden" name="schedulefilter" id="schedulefilter" value="">
<input type="hidden" name="schedulesort" id="schedulesort" value="">
</form>
<form id="formphotographer" action="isi.schedule.photographer.php" method="post">
<input type="hidden" name="schedulephotographer" id="schedulephotographer" value="" >
</form>
</body>
</html>

