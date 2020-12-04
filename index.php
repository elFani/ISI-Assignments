<?php
#	Test for http://
$isHTTP = false;
$typeHTTPS = gettype($_SERVER['HTTPS']);
$typeSCRIPT = gettype($_SERVER['SCRIPT_URI']);
// if ($typeHTTPS == 'NULL') { $isHTTP = true; }
// if ($typeSCRIPT == 'string') {
//  	$isHTTP = true;
// 	if (strpos($temp,'https://') == '') { $isHTTP = true; }
// 	if (strpos($temp,'http://') == 0) { $isHTTP = true; }
// }
#	Please follow link to ISI Photo Uploader
if ($isHTTP) {
	print "<br><br><center><h2>Hello. Please follow link to ISI Photos Assignment Request.</h2></center><br>";
	print "<br><br><center><h3>Please follow link to ISI Photos Assignment Request.</h3></center><br>";
	print "<center><font size=+1><a href='https://assignments.isiphotos.com'>https://assignments.isiphotos.com</a></font></center><br>";
	print "<center><font size=+1>Link as text: <input type='text' value='https://assignments.isiphotos.com' size=50></font></center><br>";
	exit;
}
#	Load in the settings and includes
include ("includes/isi.schedule.tools.php");
#	--------------------------------------------
#	Overview
#
#	isi.schedule.request.php renamed to index.php
#
#	Create/send assignment request to ISI for photographer(s).
#	For certain leagues, teams are known, and select boxes can be pre-filled.
#	For "Other", user fills in the information.
#
#
#	To Do
#
#	--------------------------------------------
#	Issue:
#	--------------------------------------------
?>
<html>
<head>
<title>ISI Schedule V1.6</title>
<META HTTP-EQUIV="Expires" CONTENT="Tue, 01 Jan 1980 1:00:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<meta http-equiv="Cache-control" content="no-cache">
<link rel="icon" type="image/png" href="https://assignments.isiphotos.com/icons/ISI_logo.png" />

<!-- <link rel="stylesheet" href="includes/css/bootstrap.min.css"> -->
<!-- <link rel="stylesheet" href="includes/css/bootstrap-datepicker3.min.css">	-->
<link rel="stylesheet" href="includes/css/turretcss.min.css">
<link rel="stylesheet" href="includes/css/isi-style.css">

<!--	ISI Schedule related functions	-->
<script type="text/javascript" src="includes/isi.schedule.js"></script>

<script type="text/javascript" src="includes/jquery_211.js"></script>
<script type="text/javascript" src="includes/js/bootstrap.min.js"></script>
<!--	Calendar	-->
<script type="text/javascript" language="javascript" src="includes/CalendarPopup.js"></script>
<script>
//	Check javascript version
var	isStale = false;
if (jsVersion != '1.20') { isStale = true; }

//	Editor ID after changeEditor.
var currentEditorID = '';
//	Issue diagnostic console.log messages.
var isConsoleTrace = false;
//var isConsoleTrace = true;

//	Google calendar and credentials based on league/collection.
//	For Cal/Stanford, there are sub-collections, but use only parent collection ID.
var runawayCounter = 0;
var isGoogleWaiting = true;
//	Google calendar status (enable/disable)
var isCalendarDisabled = true;

//	Constants related to html <file> images/files
var maxImages = 0;
var rowIndex = 0;
var tdIndex = 0;
var statusIndex = 0;
//	Gate api activity
var isWaiting = false;
//	Google calendar
//	Target collection based on league.
var collectionID = '';
var organizationName = '';	// Delete? and use currentLeague?
//	Target gallery based on league, team vs team, and date.
//	Event type contributes to format of gallery name
var activeEventType = '';

//	Multiple photographers and multiple days assignments
var assignPhotographerCount = 1;
var assignDaysCount = 1;

//	Icon associated with the assignment
var assignCurrentIcon = '';

//	Time arrays
	var timeArray	= new Array('00:00','01:00','02:00','03:00','04:00','05:00','06:00','07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00','22:00','23:00');
	var timeMax = timeArray.length;

//	Constants used by image uploader
var	formData = false;
if (window.FormData) {
    formData = new FormData();
} else {
	alert("Requires Javascript FormData object.");
	window.location = "http://www.isiphotos.com";
}
if (window.FileReader) {
	temp = '';
} else {
	alert("Requires Javascript FileReader object.");
	window.location = "http://www.isiphotos.com";
}
//	Timer related
var	uploadTimeout = '';
var timeStart = 0;
var timeEnd = 0;
var timeElapsed = 0;

//	Counter to prevent infinite loop.
var	runawayCounter = 0;

//
// Select date format: value="2010-01-09,January 9 2010"
// value = yyyy-MM-dd,MMM dd yyyy
// Select date format: value=January 9 2010"
// value = MMM dd yyyy

function readytoassign() {
if (isConsoleTrace) { console.log("readytoassign"); }
var passedValidation;
//	Display selected date.
	indicateDate();
//	Validate ...
	passedValidation = validateInput();
	if (passedValidation == false) {
		return false;
	}
//	For other event types, build a gallery name and if it exists then use it, otherwise create it.
	tempObject = document.getElementById('isi_selected_keyword');
	organizationName = currentLeague;
if (isConsoleTrace) { console.log("readytoassign Collection ID("+collectionID+") Name("+organizationName+") Gallery("+tempObject.value+")"); }
	insertAssignment();
return true;
}
function validateInput() {
if (isConsoleTrace) { console.log("validateInput"); }
var test, tempObject;
	tempObject = document.getElementById('isi_selected_keyword');
	if (isConsoleTrace) { console.log("Keyword/Gallery name ("+tempObject.value+") Event("+activeEventType+")"); }

//	Validate requestor
	tempObject = document.getElementById('requestor_data');
	if (tempObject.value == '') {
		alert ('Requestor is not defined.');
		tempObject.focus();
		return false;
	}
//	Validate requestor
	tempObject = document.getElementById('requestoremail_data');
	if (tempObject.value == '') {
		alert ('Requestor email is not defined.');
		tempObject.focus();
		return false;
	}

//	Validate icon
	if (assignCurrentIcon == '') {
		tempObject = document.getElementById('iconsection');
		tempObject.style.display = 'inline';
		alert ('Assignment category required.');
		tempObject.focus();
		return false;
	}


//	Validate collection selected
	tempObject = document.getElementById('isi_selected_league');
	test = tempObject.value;
	tempLeague = test;
	if (test == 'none') {
		tempObject = document.getElementById('isi_league');
		alert ('Collection not selected and a collection is not defined.');
		tempObject.focus();
		return false;
	}
//	Validate date selected
	tempObject = document.getElementById('isi_selected_date');
	test = tempObject.value;
	if (test == '') {
		alert ('Game/Event date selection required.');
		return false;
	}
//	Validate times selected	(end > start)
//	tempObject = document.getElementById('isi_timeend');
//	test = tempObject.value;
//	tempObject = document.getElementById('isi_timestart');
//	if (test <span tempObject.value) {
//		tempObject.focus();
//		alert ('Event end time must be greater than start time.');
//		return false;
//	}

//	Validate location
	tempObject = document.getElementById('location_data');
	test = tempObject.value;
	if (test == '') {
		alert ('Location is not defined.');
		tempObject.focus();
		return false;
	}
	tempObject.value = test.replace("'","");

//	Validate contact
	tempObject = document.getElementById('contact_data');
	test = tempObject.value;
	if (test == '') {
		alert ('Contact is not defined.');
		tempObject.focus();
		return false;
	}

//	Validate contact phone
	tempObject = document.getElementById('contactphone_data');
	test = tempObject.value;
	if (test == '') {
		alert ('Contact phone is not defined.');
		tempObject.focus();
		return false;
	}

//	Validate photographer count. Default is 1 and only numbers are available.
//	if (assignPhotographerCount == '') {
//		tempObject = document.getElementById('isi_count');
//		alert ('Number of photographers is not defined.');
//		tempObject.focus();
//		return false;
//	}

//	Validate event type
	if (activeEventType == '' || activeEventType == 'none') {
		alert ('Event type required.');
		tempObject = document.getElementById('isi_eventtype');
		tempObject.focus();
		return false;
	}

//	Game or Non-game / Other
	game = true;
	if (activeEventType == 'eventgame') {
		game = true;
	} else {
		game = false;
	}

//	Other event. Collect event description.
	if (activeEventType == 'eventother') {
//	Clear selected home team, which feeds into final keyword/gallery name.
		tempObject = document.getElementById('isi_selected_hometeam');
		tempObject.value = '';
//	Clear selected vistor team
		tempObject = document.getElementById('isi_selected_visitorteam');
		tempObject.value = '';
		tempObject = document.getElementById('isi_selected_description');
		test = tempObject.value;
		if (test == '' || test == 'Type event description here') {
			alert ('Event description required.');
			tempObject.focus();
			return false;
		}
		tempObject = document.getElementById('isi_selected_hometeam');
		tempObject.value = test;
	}
	if (test == '-- Other --') {
		tempObject = document.getElementById('isi_selected_hometeam_new');
		test = tempObject.value;
		if (test == '' || test == 'Other') {
			alert ('Team required. Currently('+test+')');
			tempObject.focus();
			return false;
		}
//	Clear/set selected home team
		tempObject = document.getElementById('isi_selected_hometeam');
		tempObject.value = '';
		tempObject.value = test;
	}
//	Non-game event. Collect team and activity.
	if (activeEventType == 'eventnongame') {
		tempObject = document.getElementById('isi_selected_hometeam');
		test = tempObject.value;
		if (test == '') {
			alert ('Team selection required.');
			tempObject = document.getElementById('isi_league_hometeam');
			tempObject.focus();
			return false;
		}
//	Check for selected activity
		tempObject = document.getElementById('isi_selected_visitorteam');
		test = tempObject.value;
		if (test == '') {
			alert ('Team or Non-game activity selection required.');
			tempObject = document.getElementById('isi_league_visitorteam');
			tempObject.focus();
			return false;
		}
		if (test == '-- Other --') {
			tempObject = document.getElementById('isi_selected_visitorteam_new');
			test = tempObject.value;
			if (test == '' || test == 'Other') {
				alert ('Visitor team required. Currently('+test+')');
				tempObject.focus();
				return false;
			}
//	Clear/set selected visitor team
			tempObject = document.getElementById('isi_selected_visitorteam');
			tempObject.value = '';
			tempObject.value = test;
		}
//	Activity updated ??? after initial validation
		tempObject = document.getElementById('isi_selected_visitorteam_new');
		testA = tempObject.value;
		tempObject = document.getElementById('isi_selected_visitorteam');
		test = tempObject.value;
		if (testA != '' && testA != test) {
			tempObject.value = testA;
		}
	}

//	Game teams validation
	if (game == true) {
		tempObject = document.getElementById('isi_selected_hometeam');
		test = tempObject.value;
		if (test == '') {
			alert ('Team selection required.');
			tempObject = document.getElementById('isi_league_hometeam');
			tempObject.focus();
			return false;
		}
		if (test == '-- Other --') {
			tempObject = document.getElementById('isi_selected_hometeam_new');
			test = tempObject.value;
			if (test == '' || test == 'Other') {
				alert ('Team required. Currently('+test+')');
				tempObject.focus();
				return false;
			}
//	Clear/set selected home team
			tempObject = document.getElementById('isi_selected_hometeam');
			tempObject.value = '';
			tempObject.value = test;
		}
		tempObject = document.getElementById('isi_selected_visitorteam');
		test = tempObject.value;
		if (test == '') {
			alert ('Team or Non-game activity selection required.');
			tempObject = document.getElementById('isi_league_visitorteam');
			tempObject.focus();
			return false;
		}
		if (test == '-- Other --') {
			tempObject = document.getElementById('isi_selected_visitorteam_new');
			test = tempObject.value;
			if (test == '' || test == 'Other') {
				alert ('Team required. Currently('+test+')');
				tempObject.focus();
				return false;
			}
//	Clear/set selected visitor team
			tempObject = document.getElementById('isi_selected_visitorteam');
			tempObject.value = '';
			tempObject.value = test;
		}
//	Home updated ??? after initial validation
		tempObject = document.getElementById('isi_selected_hometeam_new');
		testA = tempObject.value;
		tempObject = document.getElementById('isi_selected_hometeam');
		test = tempObject.value;
		if (testA != '' && testA != test) {
			tempObject.value = testA;
		}
//	Visitor updated ??? after initial validation
		tempObject = document.getElementById('isi_selected_visitorteam_new');
		testA = tempObject.value;
		tempObject = document.getElementById('isi_selected_visitorteam');
		test = tempObject.value;
		if (testA != '' && testA != test) {
			tempObject.value = testA;
		}
	}
	tempKeyword = '';
	tempObject = document.getElementById('isi_selected_hometeam');
	tempKeyword = tempKeyword + tempObject.value;
	tempObject = document.getElementById('isi_selected_visitorteam');
	tempvs = ' ';
	if (activeEventType == 'eventgame') { tempvs = ' vs '; }
	test = tempObject.value;
	if (test != '') {
		tempKeyword = tempKeyword + tempvs + tempObject.value;
	}

//	Date is part of the gallery name
	tempObject = document.getElementById('isi_selected_date');
	temp = tempObject.value;
	tempKeyword = tempKeyword + ', ' + temp;

//	Keyword is the gallery name.
	tempObject = document.getElementById('isi_selected_keyword');
	tempObject.value = tempKeyword;
	tempObject = document.getElementById('isi_calendar_date');
	temp = tempObject.value;
if (isConsoleTrace) { console.log("Keyword/Gallery name ("+tempKeyword+")  Event("+activeEventType+")  Collection("+collectionID+") Collection("+organizationName+") GDate("+temp+")"); }
	return true;
}



//	The primary index correlates to PhotoShelter gallery.
//	['teams'] populates the isi_league_ home and visitor select boxes
//	['events'] populates the isi_eventtype, event type select box
var leagues = new Array();

<?php
#	Build javascript arrays of collections for an organization in ISI Schedule
#	Table: schedule names
	$collectionArray = array();
#	Open the database
	$connection = openDB();
	$query = "select organization.organization_name as 'name', organization.organization_email as 'email', organization.organization_teams as 'teams', organization.organization_teamsother as 'teamsother', organization.organization_home as 'home', organization.organization_events as 'events' FROM schedule_organizations as organization order by organization.organization_name";

	$data = executeSQL($connection, $query);
	$max = mysqli_num_rows($data);
#	Build javascript arrays for each collection
	if ($max > 0) {
		while ($row = mysqli_fetch_assoc($data)) {
			print "leagues[\"{$row['name']}\"] = new Array();\n";
			print "leagues[\"{$row['name']}\"][\"teams\"] = new Array({$row['teams']});\n";
			if ($row['teamsother'] != "") {
				print "leagues[\"{$row['name']}\"][\"teamsother\"] = new Array({$row['teamsother']});\n";
			}
			if ($row['home'] != "") {
				print "leagues[\"{$row['name']}\"][\"home\"] = new Array({$row['home']});\n";
			}
			print "leagues[\"{$row['name']}\"][\"events\"] = new Array({$row['events']});\n";
			array_push($collectionArray, "{$row['name']}:{$row['email']}");
		}
	}

?>

//var leagues = ['MLS', 'US Soccer', 'International', 'WPS'];
//leagues["USA Men's Soccer"] = new Array();
//leagues["USA Men's Soccer"]['teams'] = ['USMNT','USMNT U-23','USMNT U-20','USMNT U-18','USMNT U-17'];
//leagues["USA Men's Soccer"]['events'] = ['eventgame','eventnongame','eventother'];
//leagues["USA Women's Soccer"] = new Array();
//leagues["USA Women's Soccer"]['teams'] = ['USWNT','USWNT U-23','USWNT U-20','USWNT U-18','USWNT U-17'];
//leagues["USA Women's Soccer"]['events'] = ['eventgame','eventnongame','eventother'];
//leagues['San Jose Earthquakes Team Photography'] = new Array();
//leagues['San Jose Earthquakes Team Photography']['teams'] = leagues['Major League Soccer']['teams'];
//leagues['San Jose Earthquakes Team Photography']['events'] = ['eventgame','eventother'];
//leagues['San Jose Earthquakes Team Photography']['home'] = ['San Jose Earthquakes'];
//	University support
leagues['Cal Athletics'] = new Array();
leagues['Cal Athletics']['teams'] = ['Athletic Department','Baseball','Basketball M','Basketball W','Beach Volleyball','Crew Ltwt','Crew M','Crew W','Cross Country','Fencing','Field Hockey','Football','Golf M','Golf W','Gymnastics M','Gymnastics W','Lacrosse','Rugby','Sailing','Soccer M','Soccer W','Softball','Squash','Swimming & Diving M','Swimming & Diving W','Synchro','Tennis M','Tennis W','Track & Field','Volleyball W','Waterpolo M','Waterpolo W','Wrestling','-- Other --'];
leagues['Cal Athletics']['teamsother'] = ['-- Other --'];
leagues['Cal Athletics']['events'] = ['eventgame','eventnongame','eventother'];
//leagues['Stanford Athletics'] = new Array();
//leagues['Stanford Athletics']['teams'] = ['Athletic Department','Baseball','Basketball M','Basketball W','Beach Volleyball','Crew Ltw','Crew M','Crew W','Cross Country','Fencing','Field Hockey','Football','Golf M','Golf W','Gymnastics M','Gymnastics W','Lacrosse','Sailing','Soccer M','Soccer W','Softball','Squash','Swimming & Diving M','Swimming & Diving W','Synchro','Tennis M','Tennis W','Track and Field','Volleyball M','Volleyball W','Waterpolo M','Waterpolo W','Wrestling','-- Other --'];
//leagues['Stanford Athletics']['teamsother'] = ['-- Other --','University of Arizona','Arizona State University','University of California-Berkeley','UCLA','University of Colorado','University of Oregon','Oregon State University','University of Southern California','University of Utah','University of Washington','Washington State University'];
//leagues['Stanford Athletics']['events'] = ['typegame','typenongame','typeexists','typeother'];
//leagues['Stanford Athletics']['events'] = ['eventgame','eventnongame','eventother'];
//leagues['General Soccer']['teams'] = ['-- Type in team at right --','Australia','Brazil','Cameroon', 'Canada','China','Colombia','Costa Rica','Cote d\'Ivoire','Ecuador','England','France', 'Germany','Japan','Korea Republic','Mexico','Netherlands', 'New Zealand','Nigeria','Norway','Spain','Sweden','Switzerland','Thailand'];
//	US Soccer, Non-game events list
//	Gallery name of "USWNT Training, date" belongs in [collections] => Training & Travel.
leagues['Nongame'] = new Array();
//leagues['Nongame']['events'] = ['Headshots','Portraits','Press Conference','Training','Travel','-- Other --'];
leagues['Nongame']['events'] = ['Portraits','Team Photo','Portraits and Team Photo','Press Conference','Stylized','Marketing','-- Other --'];
leagues['Nongame']['usevents'] = ['Portraits','Press Conference','Training','Travel','-- Other --'];
leagues['Nongame']['collections'] = new Array();
//leagues['Nongame']['collections']['Headshots'] = 'Portraits & Headshots';
leagues['Nongame']['collections']['Portraits'] = 'Portraits & Headshots';
leagues['Nongame']['collections']['Press Conference'] = 'Training, Travel, and Events';
leagues['Nongame']['collections']['Training'] = 'Training, Travel, and Events';
leagues['Nongame']['collections']['Travel'] = 'Training, Travel, and Events';
leagues['Nongame']['collections']['-- Other --'] = 'Training, Travel, and Events';
//	Event select box
events = new Array();
events['eventgame'] = 'Game or Match';
events['eventnongame'] = 'Non-game Team Event';
//events['eventexists'] = 'Existing game/event from list';
events['eventother'] = 'Other';
//	Months
monthText = new Array();
monthText = ['January','February','March','April','May','June','July','August','September','October','November','December'];

//	Select event time start
//	Update time end select box, time+3.
function changeStart(inputObject) {
	currentIndex = inputObject.selectedIndex;
	currentTime = inputObject.options[currentIndex].value;
	if (isConsoleTrace) { console.log("changeStart: Index("+currentIndex+") Time:("+currentTime+")"); }
//	Reset the Start time select
	if (currentTime == 'none') { return; }
	if (currentTime == 'reset') {
		resetSelect(inputObject, '');
		updateSelect(inputObject, timeMax, timeArray, timeText);
		inputObject.selectedIndex = 12;
		tempObject = document.getElementById('isi_timeend');
		tempObject.selectedIndex = 15;
		return;
	}
//
//	For time increments: size = 6. Adjust, :00, :15, :30, :45, Reset.
	if (inputObject.size == 6) {
		inputObject.size = 1;
		tempObject = document.getElementById('location_data');
		tempObject.focus();
		return;
	}
//	Hours are presented. Update the end time +3
//	Update select for 15 increments.
	tempObject = document.getElementById('isi_timeend');
	currentIndex += 3;
	if (currentIndex > 23) { currentIndex = currentIndex - 24; }
	tempObject.selectedIndex = currentIndex;
//	:00, :15, :30, :45 logic
	resetSelect(inputObject, '');
	tempIndex = currentTime.indexOf(':');
	tempTime = currentTime.substring(0,tempIndex);
//	tempArray = new Array();
//	tempArray.push(temp+':00');
	tempArray = new Array(tempTime+':00', tempTime+':15', tempTime+':30', tempTime+':45', 'reset');
	tempAM = ' AM';
	tempHH = parseInt(tempTime);
	if (tempHH >11) { tempAM = ' PM'; }
	if (tempHH >12) { tempHH = tempHH -12; }
	tempHH = tempHH.toString();
	tempText = new Object();
	tempText[tempTime+':00'] = tempHH+':00'+tempAM;
	tempText[tempTime+':15'] = tempHH+':15'+tempAM;
	tempText[tempTime+':30'] = tempHH+':30'+tempAM;
	tempText[tempTime+':45'] = tempHH+':45'+tempAM;
	tempText['reset'] = 'Reset start time'
	updateSelect(inputObject, 5, tempArray, tempText);
	inputObject.options[0].value = 'none';
	inputObject.options[0].text = 'Adjust start time';
	inputObject.selectedIndex = 1;
	inputObject.size = 6;
	return;
}

//	Select event time end
//	Validate the time window.
function changeEnd(inputObject) {
	currentIndex = inputObject.selectedIndex;
	tempObject = document.getElementById('isi_timestart');
	temp = tempObject.selectedIndex;
	testStart = tempObject.options[temp].value;
	testEnd = inputObject.options[currentIndex].value;
	if (testStart < '23:59' && testStart < testEnd) {
		tempObject = document.getElementById('location_data');
		tempObject.focus();
		return;
	}
	if (testStart > '22:00' && testEnd < '05:00') {
		tempObject = document.getElementById('location_data');
		tempObject.focus();
		return;
	}
	inputObject.selectedIndex = 23;
	alert ('Event end time must be greater than start time. Reset to end of day.');
	return;
}

//	Select count of photographers
function changeCount(inputObject) {
	currentIndex = inputObject.selectedIndex;
	assignPhotographerCount = inputObject.options[currentIndex].value;
	if (isConsoleTrace) { console.log("changeCount: Count("+assignPhotographerCount+")"); }
	tempObject = document.getElementById('isi_duration');
	tempObject.focus();
	return;
}

//	Assignment number of days
function changeDays(inputObject) {
	currentIndex = inputObject.selectedIndex;
	assignDaysCount = inputObject.options[currentIndex].value;
	if (isConsoleTrace) { console.log("changeDays: Count("+assignDaysCount+")"); }
	tempObject = document.getElementById('isi_icon');
	tempObject.focus();
	return;
}

//	Select count of photographers
function changeIcon(inputObject) {
	currentIndex = inputObject.selectedIndex;
	assignCurrentIcon = inputObject.options[currentIndex].value;
	if (isConsoleTrace) { console.log("changeIcon: URL("+assignCurrentIcon+")"); }
	tempObject = document.getElementById('isi_create');
	tempObject.focus();
	return;
}

//	Link to ISI Schedule Editor
function linkEditor() {
	tempObject = document.getElementById('isi_editor_id');
	temp = tempObject.value;
	tempObject = document.getElementById('scheduleeditor');
	tempObject.value = temp;
	tempObject = document.getElementById('formeditor');
	tempObject.submit();
}

//	As league selection occurs, populate the teams select boxes
//	Leagues are PhotoShelter collections.
function changeLeague(inputObject) {
var i;
var index;
var tempMax, tempObject, tempText, tempValue, currentIndex, currentData;
	galleryAsKeyword = false;
	currentIndex = inputObject.selectedIndex;
	tempArray = inputObject.options[currentIndex].value;
	tempArray = tempArray.split(":");
	currentLeague = tempArray[0];
	currentLeagueEmail = tempArray[1];
	tempObject = document.getElementById('isi_selected_league');
	tempObject.value = currentLeague;
	tempObject = document.getElementById('isi_selected_league_email');
	tempObject.value = currentLeagueEmail;
//	Reset event select boxes and text fields.
	activeEventType = 'none';
	resetEvent();
//	Confirm event information button display
	tempObject = document.getElementById('isi_create');
	tempObject.style.display = 'inline';
//	Reset event confirmation status
	tempObject = document.getElementById('eventstatuslabel');
	tempObject.textContent = '';
	if (currentLeague == 'none') { return; }
//	Update event type select
	tempMax = leagues[currentLeague]['events'].length;
	tempObject = document.getElementById('isi_eventtype');
	resetSelect(tempObject, '');
	updateSelect(tempObject, tempMax, leagues[currentLeague]['events'], events);
//	Gallery titles are keywords from prior import.
//	Adding more images to existing gallery, you can use existing
//	gallery title as the keyword.
	if (currentLeague == 'galleryaskeyword') {
		galleryAsKeyword = true;
		tempObject = document.getElementById('isi_league_hometeam');
		resetSelect(tempObject, '');
		tempOption = new Option('Keyword/team defined by target gallery you select','');
		tempObject.options[1] = tempOption;
		tempObject = document.getElementById('isi_league_visitorteam');
		resetSelect(tempObject, '');
		tempOption = new Option('Keyword/team defined by target gallery you select','');
		tempObject.options[1] = tempOption;
		return;
	}
//	Update Select team, home team
	tempMax = leagues[currentLeague]['teams'].length;
	tempObject = document.getElementById('isi_league_hometeam');
	resetSelect(tempObject, '');
//	updateSelect(tempObject, tempMax, leagues[currentLeague]['teams'], '');
	tempTeams = leagues[currentLeague]['teams'];
	if (currentLeague == "San Jose Earthquakes Team Photography") {
		tempTeams = leagues[currentLeague]['home'];
		tempMax = leagues[currentLeague]['home'].length;
		assignCurrentIcon = leagues[currentLeague]['home'][1];
	}
//	updateSelect(tempObject, tempMax, tempTeams, '');
	updateSelect(tempObject, tempMax, tempTeams, 'icons');
//	Update Select team, visitor team
	if (currentLeague == "USA Men's Soccer" || currentLeague == "USA Women's Soccer") {
			tempMax   = leagues['International Soccer']['teams'].length;
			tempTeams = leagues['International Soccer']['teams'];
		} else {
			tempMax   = leagues[currentLeague]['teams'].length;
			tempTeams = leagues[currentLeague]['teams'];
		}
	if (isConsoleTrace) { console.log("changeLeague: League("+currentLeague+")"); }
	tempObject = document.getElementById('isi_league_visitorteam');
	resetSelect(tempObject, '');
	updateSelect(tempObject, tempMax, tempTeams, '');

//	Reset text boxes
	tempObject = document.getElementById('isi_selected_description');
	tempObject.value = '';
	tempObject = document.getElementById('isi_selected_hometeam_new');
	tempObject.value = '';
	tempObject = document.getElementById('isi_selected_visitorteam_new');
	tempObject.value = '';
	return;
}

//	Home team selected
function changeHome(inputObject){
var i;
var index;
var test, tempMax, tempObject, tempText, tempValue, currentIndex, currentData;
	currentIndex = inputObject.selectedIndex;
	currentData  = inputObject.options[currentIndex].value;
	if (isConsoleTrace) { console.log("changeHome Data("+currentData+")"); }
//	Current data is? team name:team jpg/png
//	If true reset currentData and capture the jpg/png
	test = currentData.indexOf(":");
	if (test >0) {
		test++;
		assignCurrentIcon = currentData.substr(test);
		test--;
		currentData = currentData.substr(0,test);
	}
	tempObject = document.getElementById('isi_selected_hometeam');
	tempObject.value = currentData;
//	Home team text field status
	tempObject = document.getElementById('isi_selected_hometeam_new');
	tempObject.value = '';
	tempObject.style.display = "none";
//	Home team other text field status
	tempObject = document.getElementById('homeotherlabel');
	tempObject.textContent = '';
	if (currentData == '-- Type event description below --') {
		tempObject = document.getElementById('isi_selected_description');
		tempObject.value = 'Type event description here';
		tempObject = document.getElementById('isi_selected_hometeam_new');
		tempObject.value = '';
		tempObject = document.getElementById('isi_selected_visitorteam_new');
		tempObject.value = '';
		return;
	}
	if (currentData == '-- Other --') {
		tempObject = document.getElementById('isi_selected_hometeam_new');
		tempObject.style.display = "inline";
		tempObject.value = 'Other';
		tempObject.focus();
		tempObject.select();
//	Visitor team other text field status
		tempObject = document.getElementById('homeotherlabel');
		tempObject.textContent = 'Enter new home team';
		tempObject.style.display = "inline";
//	Reset description
		tempObject = document.getElementById('isi_selected_description');
		tempObject.value = '';
		return;
	}
	tempObject = document.getElementById('isi_selected_description');
	tempObject.value = '';
	tempObject = document.getElementById('isi_selected_hometeam_new');
	tempObject.value = '';
//	For Cal Athletics and Stanford Athletics there are sub-collections, by team game.
//	Nongame activities for Stanford and Cal
//	Get PhotoShelter collection ID. Used for creating/accessing gallery.
//	Indicate gallery/collection permissions
	if ((activeEventType == 'eventgame' || activeEventType == 'eventnongame') && currentLeague == 'Cal Athletics') {
		tempObject = document.getElementById('isi_selected_hometeam');
		tempObject.value = 'Cal ' + currentData;
	}
	if ((activeEventType == 'eventgame' || activeEventType == 'eventnongame') && currentLeague == 'Stanford Athletics') {
		tempObject = document.getElementById('isi_selected_hometeam');
		tempObject.value = 'Stanford ' + currentData;
	}
//	For Cal/Stanford, once a team is selected, then get collection children (existing galleries for that team)
//	and update/populate the select box.
//	if (activeEventType == 'eventexists' && (currentLeague == 'Cal Athletics' || currentLeague == 'Stanford Athletics')) {
//		if (isConsoleTrace) { console.log("changeHome: ID("+collectionID+") Name("+currentData+")"); }
//		runawayCounter = 0;
//		setTimeout(function() { updateList(); }, 1000);
//	}
	return;
}

//	Update the list of existing games/events via setTimeout().
//	Keep as example of how to iterate, wait, until data received.
function updateList() {
//	Update runaway counter and test if done.
	runawayCounter++;
	if (isConsoleTrace) { console.log("updateList ID("+collectionID+") Runaway("+runawayCounter+")"); }
	if (runawayCounter > 50) { return; }
	if (collectionID == '' || collectionID == 'initial') { setTimeout(function() { updateList(); }, 700); return;}
	tempObject = document.getElementById('eventawaylabel');
	tempObject.textContent = 'Existing game/event list';
	tempObject.style.display = "inline";
	tempObject = document.getElementById('isi_league_visitorteam');
	tempObject.style.display = "none";
//	getCollectionChildren(collectionID, tempObject);
	tempObject.style.display = "inline";
	tempObject.focus();
	return;
}

//	Visitor selected
function changeVisitor(inputObject, inputCollections){
var i;
var index;
var tempMax, tempObject, tempText, tempValue, currentIndex, currentData;
	currentIndex = inputObject.selectedIndex;
	currentData  = inputObject.options[currentIndex].value;
	if (isConsoleTrace) { console.log("changeVisitor: Data("+currentData+") Event("+activeEventType+")"); }
	tempObject = document.getElementById('isi_selected_visitorteam');
	tempObject.value = currentData;
//	Reset Visitor team text field status
	tempObject = document.getElementById('isi_selected_visitorteam_new');
	tempObject.value = '';
	tempObject.style.display = "none";
//	Reset Visitor team other text field status
	tempObject = document.getElementById('visitorotherlabel');
	tempObject.textContent = '';
	if (currentData == '-- Other --') {
		tempObject = document.getElementById('isi_selected_visitorteam_new');
		tempObject.style.display = "inline";
		tempObject.value = 'Other';
		tempObject.focus();
		tempObject.select();
//	Visitor team other text field status
		tempObject = document.getElementById('visitorotherlabel');
		tempObject.textContent = 'Enter new visiting team or event description';
		tempObject.style.display = "inline";
//	Reset description
		tempObject = document.getElementById('isi_selected_description');
		tempObject.value = '';
		return;
	}
	tempObject = document.getElementById('isi_selected_visitorteam_new');
	tempObject.value = '';
//	For US Soccer there are sub-collections.
//	Get PhotoShelter collection ID. Used for creating/accessing gallery.
//	Indicate gallery/collection permissions
//	For 'Portraits & Headshots' there is no USMNT 'Portraits & Headshots' nor
//	USWNT 'Portraits & Headshots'. Therefore, when retrieving the collection ID
//	for 'Portraits & Headshots' it will not be unique or possibly correct. USWNT and
//	USMNT will share 'Portraits & Headshots', until the respective collections
//	are renamed with a USMNT and USWNT prefix. Request made June 2016.
//	Not possible to work around collections with identical names.
	if (isConsoleTrace) { console.log("changeVisitor: EventType("+activeEventType+") League("+currentLeague+") Collection("+collectionID+") Data("+currentData+")"); }
	return;
}

//	Event type selection (isi_eventtype)
//	game, nongame, other.
//	For each reset/populate select boxes, text fields, and stored values.
//	Stored values:
//<input type="hidden" id="isi_selected_league" name="isi_selected_league" value="none">
//<input type="hidden" id="isi_selected_hometeam" name="isi_selected_hometeam" value="">
//<input type="hidden" id="isi_selected_visitorteam" name="isi_selected_visitorteam" value="">
//<input type="hidden" id="isi_selected_keyword" name="isi_selected_keyword" value="">
//<input type="hidden" id="isi_selected_gallery" name="isi_selected_gallery" value="">
//<input type="hidden" id="isi_selected_gallery_title" name="isi_selected_gallery_title" value="">

function changeEvent(inputObject) {
//	Make the event labels and buttons visible
	eventVisible("inline");
	tempObject = '';
//	Confirm event information button display
	tempObject = document.getElementById('isi_create');
	tempObject.style.display = 'inline';
//	Reset event confirmation status
	tempObject = document.getElementById('eventstatuslabel');
	tempObject.textContent = '';
//	Reset Home team select box
	tempObject = document.getElementById('isi_league_hometeam');
	tempObject.selectedIndex = 0;
//	Reset Visitor team select box
	tempObject = document.getElementById('isi_league_visitorteam');
	tempObject.selectedIndex = 0;
//	Reset stored values that build keyword/gallery name
	tempObject = document.getElementById('isi_selected_hometeam');
	tempObject.value = '';
	tempObject = document.getElementById('isi_selected_visitorteam');
	tempObject.value = '';
//	Reset select boxes based on league
	tempObject = document.getElementById('isi_selected_league_email');
	currentLeagueEmail = tempObject.value;
	tempObject = document.getElementById('isi_selected_league');
	currentLeague = tempObject.value;
	if (currentLeague == 'none') {
//	Reset event type select box
		inputObject.selectedIndex = 0;
//	No collection, focus on Collection select box.
		tempObject = document.getElementById('isi_league');
		alert ('Collection not selected and a collection is not defined.');
		tempObject.focus();
		return;
	}

//	Indicate current/active event type
	currentIndex = inputObject.selectedIndex;
	activeEventType = inputObject.options[currentIndex].value;
	if (isConsoleTrace) { console.log("changeEvent: Type("+activeEventType+") Index("+currentIndex+")"); }

//	Event type: Game / Match
//	Display/Hide appropriate select boxes and text fields.
	if (activeEventType == 'eventgame') {
		tempObject = document.getElementById('eventtypelabel');
		tempObject.textContent = 'Standard Game / Match';
		tempObject.style.display = "block";
		tempObject = document.getElementById('eventtypeaction');
		tempObject.textContent = 'Select home and visiting teams. The teams available for selection depend on your choice in Collection above.';
		tempObject.style.display = "block";
//	Set Home/Avay Team labels
		tempObject = document.getElementById('eventhomelabel');
		tempObject.textContent = 'Home Team';
		tempObject.style.display = "inline";
		tempObject = document.getElementById('eventawaylabel');
		tempObject.textContent = 'Away Team';
		tempObject.style.display = "inline";
		if (currentLeague == "USA Men's Soccer" || currentLeague == "USA Women's Soccer") {
			tempObject = document.getElementById('eventhomelabel');
			tempObject.textContent = 'USA Team';
			tempObject.style.display = "inline";
			tempObject = document.getElementById('eventawaylabel');
			tempObject.textContent = 'Team';
			tempObject.style.display = "inline";
		}
		if (currentLeague == 'Cal Athletics' || currentLeague == 'Stanford Athletics') {
			tempObject = document.getElementById('eventhomelabel');
			tempObject.textContent = 'Team';
			tempObject.style.display = "inline";
			tempObject = document.getElementById('eventawaylabel');
			tempObject.textContent = 'Team';
			tempObject.style.display = "inline";
		}
//	Home team select box
		tempObject = document.getElementById('isi_league_hometeam');
		tempObject.style.display = "inline";
		tempObject.focus();
//	Update select list of home teams
		tempMax = leagues[currentLeague]['teams'].length;
		resetSelect(tempObject, '');
//		updateSelect(tempObject, tempMax, leagues[currentLeague]['teams'], '');
		if (currentLeague == "San Jose Earthquakes Team Photography") {
			tempMax = leagues[currentLeague]['home'].length;
			updateSelect(tempObject, tempMax, leagues[currentLeague]['home'], 'icons');
			tempObject.selectedIndex = 1;
			tempObject = document.getElementById('isi_selected_hometeam');
			tempObject.value = leagues[currentLeague]['home'][0];
		} else {
//			updateSelect(tempObject, tempMax, leagues[currentLeague]['teams'], '');
			updateSelect(tempObject, tempMax, leagues[currentLeague]['teams'], 'icons');
		}
//	Visitor team select box
		tempObject = document.getElementById('isi_league_visitorteam');
//		tempObject.style.visibility = "hidden";
		tempObject.style.display = "inline";
//	Update select list of visitor teams
		tempMax   = leagues[currentLeague]['teams'].length;
		tempTeams = leagues[currentLeague]['teams'];
		if (currentLeague == "USA Men's Soccer" || currentLeague == "USA Women's Soccer") {
			tempMax   = leagues['International Soccer']['teams'].length;
			tempTeams = leagues['International Soccer']['teams'];
		}
		if (currentLeague == 'Cal Athletics' || currentLeague == 'Stanford Athletics') {
			tempMax   = leagues[currentLeague]['teamsother'].length;
			tempTeams = leagues[currentLeague]['teamsother'];
		}
		resetSelect(tempObject, '');
		updateSelect(tempObject, tempMax, tempTeams, '');
//	Home team text field status
		tempObject = document.getElementById('isi_selected_hometeam_new');
		tempObject.style.display = "none";
//	Home team other text field status
		tempObject = document.getElementById('homeotherlabel');
		tempObject.textContent = '';
//	Visitor team text field status
		tempObject = document.getElementById('isi_selected_visitorteam_new');
		tempObject.style.display = "none";
//	Visitor team other text field status
		tempObject = document.getElementById('visitorotherlabel');
		tempObject.textContent = '';
//	Event description text field status
		tempObject = document.getElementById('isi_selected_description');
		tempObject.style.display = "none";
		return;
	}

//	Event type: Non-game team event
//	Display/Hide appropriate select boxes and text fields.
	if (activeEventType == 'eventnongame') {
		tempObject = document.getElementById('eventtypelabel');
		tempObject.textContent = 'Non-Game Team Activity';
		tempObject = document.getElementById('eventtypeaction');
		tempObject.textContent = 'Select team and type of non-game activity below. This type of upload is for Galleries that are team events. Examples: training, travel, or press conferences. The teams available for selection depend on your choice in Collection above.';
		tempObject.style.display = "block";
		tempObject = document.getElementById('eventhomelabel');
		tempObject.textContent = 'Team';
		tempObject.style.display = "inline";
		tempObject = document.getElementById('eventawaylabel');
		tempObject.textContent = 'Non-Game Activity';
		tempObject.style.display = "inline";
//	Home team select box
		tempObject = document.getElementById('isi_league_hometeam');
		tempObject.style.display = "inline";
		tempObject.focus();
//	Visitor team select box
		tempObject = document.getElementById('isi_league_visitorteam');
		tempObject.style.display = "inline";
//	If current league other than US or college then blanks ....
		if (currentLeague == "USA Men's Soccer" || currentLeague == "USA Women's Soccer") {
			tempMax = leagues['Nongame']['usevents'].length;
			resetSelect(tempObject, '');
			updateSelect(tempObject, tempMax, leagues['Nongame']['usevents'], '');
		}
		if (currentLeague == 'Cal Athletics' || currentLeague == 'Stanford Athletics') {
			tempMax = leagues['Nongame']['events'].length;
			resetSelect(tempObject, '');
			updateSelect(tempObject, tempMax, leagues['Nongame']['events'], '');
		}
//	Home team text field status
		tempObject = document.getElementById('isi_selected_hometeam_new');
		tempObject.style.display = "none";
//	Home team other text field status
		tempObject = document.getElementById('homeotherlabel');
		tempObject.textContent = '';
//	Visitor team text field status
		tempObject = document.getElementById('isi_selected_visitorteam_new');
		tempObject.style.display = "none";
//	Visitor team other text field status
		tempObject = document.getElementById('visitorotherlabel');
		tempObject.textContent = '';
//	Event description text field status
		tempObject = document.getElementById('isi_selected_description');
		tempObject.style.display = "none";
		return;
	}
//	Event type: Other
//	Display/Hide appropriate select boxes and text fields.
	if (activeEventType == 'eventother') {
		tempObject = document.getElementById('eventtypelabel');
		tempObject.textContent = 'Other Events/Galleries';
		tempObject.style.display = "block";
		tempObject = document.getElementById('eventtypeaction');
		tempObject.textContent = 'These Galleries are for special events or events that are not directly related to a team. Simply enter the event description below.';
		tempObject.style.display = "block";
		tempObject = document.getElementById('eventhomelabel');
		tempObject.textContent = 'Event description';
		tempObject = document.getElementById('eventawaylabel');
		tempObject.textContent = ' ';
//	Home team select box
		tempObject = document.getElementById('isi_league_hometeam');
		resetSelect(tempObject, '');
		tempObject.style.display = "none";
//	Visitor team select box
		tempObject = document.getElementById('isi_league_visitorteam');
		resetSelect(tempObject, '');
		tempObject.style.display = "none";
//	Home team text field status
		tempObject = document.getElementById('isi_selected_hometeam_new');
		tempObject.value = '';
		tempObject.style.display = "none";
//	Home team other text field status
		tempObject = document.getElementById('homeotherlabel');
		tempObject.textContent = '';
//	Visitor team text field status
		tempObject = document.getElementById('isi_selected_visitorteam_new');
		tempObject.value = '';
		tempObject.style.display = "none";
//	Visitor team other text field status
		tempObject = document.getElementById('visitorotherlabel');
		tempObject.textContent = '';
//	Event description text field status
		tempObject = document.getElementById('isi_selected_description');
		tempObject.style.display = "inline";
		tempObject.style.visibility = "visible";
		tempObject.value = "Type event description here";
		tempObject.focus();
		tempObject.select();
		return;
	}
	if (activeEventType == 'none') {
		resetEvent();
	}
return;
}

//	Reset event type, select boxes, and text fields.
function resetEvent() {
	tempObject = document.getElementById('eventtypelabel');
	tempObject.textContent = ' ';
	tempObject = document.getElementById('eventtypeaction');
	tempObject.textContent = ' ';
	tempObject = document.getElementById('eventhomelabel');
	tempObject.textContent = ' ';
	tempObject = document.getElementById('eventawaylabel');
	tempObject.textContent = ' ';
//	Home team select box
	tempObject = document.getElementById('isi_league_hometeam');
	resetSelect(tempObject, '');
	tempObject.style.display = "none";
//	Visitor team select box
	tempObject = document.getElementById('isi_league_visitorteam');
	resetSelect(tempObject, '');
	tempObject.style.display = "none";
//	Home team text field status
	tempObject = document.getElementById('isi_selected_hometeam_new');
	tempObject.value = '';
	tempObject.style.display = "none";
//	Home team other text field status
	tempObject = document.getElementById('homeotherlabel');
	tempObject.textContent = '';
//	Visitor team text field status
	tempObject = document.getElementById('isi_selected_visitorteam_new');
	tempObject.value = '';
	tempObject.style.display = "none";
//	Visitor team other text field status
	tempObject = document.getElementById('visitorotherlabel');
	tempObject.textContent = '';
//	Event description text field status
	tempObject = document.getElementById('isi_selected_description');
	tempObject.value = '';
	tempObject.style.display = "none";
//	Reset stored values that build keyword/gallery name
	tempObject = document.getElementById('isi_selected_hometeam');
	tempObject.value = '';
	tempObject = document.getElementById('isi_selected_visitorteam');
	tempObject.value = '';
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
dateVisible("inline");
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
tempObject = document.getElementById('isi_create');
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
//	Date, start time, and end time labels and buttons
//	Display or not
//	inputState = "none" or
//	inputState = "inline"
function dateVisible(inputState) {
	tempObject = document.getElementById('datesection');
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
//	inputText	= icons means inputData is name:jpg, select value should be name:jpg and extra increment.
//	inputText	= '', but inputData is name:jpg, then select value is name and skip
//	inputText	= '', and inputData is not name:jpg, then no skip
function updateSelect(inputSelect, inputMax, inputData, inputText) {
var i;
var index;
var tempMax, tempObject, tempText, tempValue, currentIndex, currentData, noText;
//console.log ("updateSelect ID("+inputSelect.id+") length("+inputSelect.options.length+")");
//console.log ("updateSelect Text("+inputData[1]+")");
	index = 1;
	index = inputSelect.options.length;
	increment = 1;
//	inputData is team name and team jpg/png when inputText is 'icons'
	isIcon = false;
	if (inputText == 'icons') { isIcon = true; }
//	inputData is name:jpg, but not icons processing. Test
	isSkip = false;
	test = inputData[1].indexOf('.');
	if (test >0 && isIcon == false) { isSkip = true; }
	noText = true;
//	Normally value = text in the select. If passed, inputText, defines other text for a value.
//	Used for event type select.
	if (typeof(inputText) == 'object') { noText = false; }
//	If 'icons', then the inputData is list of teams and their logo jpg/png files.
//	Every other item is a team name. Value is team name : team jpg/png
	for (i=0; i<inputMax; i++) {
//		tempText = inputData[i];
//		tempValue= tempText;
		if (isIcon) {
			tempText = inputData[i];
			tempValue = inputData[i];
			i++;
			tempValue = tempValue + ":" + inputData[i];
		} else {
			tempValue = inputData[i];
			if (isSkip) { i++; }
		}
//	Logic for matching value/text or non-matching value/text
		if (noText) {
			if (isIcon == false) {
				tempText= tempValue;
			}
		} else {
			tempText = inputText[tempValue];
		}
		tempOption = new Option(tempText,tempValue);
		inputSelect.options[index] = tempOption;
		index++;
	}
	return;
}


//
//	Upload/API related functions.
//
//	Insert assignment into database.
function insertAssignment() {
	if (isConsoleTrace) { console.log("insertAssignment: "); }
//	Assignment in database has: eventID, collectionID, organizationName, gallery name, event date, start time, end time.
//	Assignment in database has: organizationName, gallery name, event date, start time, end time.
	tempObject = document.getElementById('isi_calendar_date');
	tempDate = tempObject.value;
	tempObject = document.getElementById('isi_duration');
	tempDuration = tempObject.value;
	tempObject = document.getElementById('isi_timestart');
	tempTimeStart = tempObject.value;
	tempObject = document.getElementById('isi_timeend');
	tempTimeEnd = tempObject.value;
	tempObject = document.getElementById('isi_details');
	tempDetails = tempObject.value;
	tempDetails = tempDetails.replace("'","-");
	tempObject = document.getElementById('location_data');
	tempLocation = tempObject.value;
	tempObject = document.getElementById('requestor_data');
	tempRequestor = tempObject.value;
	tempObject = document.getElementById('requestoremail_data');
	tempRequestorEmail = tempObject.value;
	tempObject = document.getElementById('contact_data');
	tempContact = tempObject.value;
	tempObject = document.getElementById('contactemail_data');
	tempContactEmail = tempObject.value;
	tempObject = document.getElementById('contactphone_data');
	tempContactPhone = tempObject.value;
	if (tempDetails == '') { tempDetails = '-'; }
	tempObject = document.getElementById('isi_selected_keyword');
	tempOrganization = organizationName+":"+currentLeagueEmail;
//	insertAssignmentDB(organizationName,tempObject.value,tempDate,assignDeliveryMode,tempDetails,assignPhotographerCount);
//inputOrganizationName, inputGalleryName, inputEventDate, inputEventStart, inputEventEnd, inputDetails, inputCount, inputLocation, inputContact, inputContactEmail, inputContactPhone
	insertAssignmentDB(tempOrganization,tempObject.value,tempDate,tempDuration,tempTimeStart,tempTimeEnd,assignCurrentIcon,tempDetails,assignPhotographerCount,tempLocation,tempContact,tempContactEmail,tempContactPhone,tempRequestor,tempRequestorEmail);
	return;
	$tempOrganizationName = $_GET['cfn'];
	$tempGalleryName = $_GET['gfn'];
	$tempEventDate = $_GET['ed'];
	$tempDelivery = $_GET['dm'];
	$tempDetails = $_GET['det'];
//	$tempPhotographerID = $_GET['pf'];
//	$tempPhotographerName = $_GET['pfn'];
//	$tempResult = requestInsert($tempEventID, $tempCollectionID, $tempOrganizationName93, $tempGalleryName, $tempEventDate, $tempDelivery,$tempDetails,$tempEditorID, $tempPhotographerID, $tempPhotographerName);
	$tempResult = requestInsert($tempOrganizationName, $tempGalleryName, $tempEventDate, $tempDelivery,$tempDetails);
	return;
}


//	Display selected date
function indicateDate() {
	tempObject = document.getElementById('isi_selected_date');
	temp = tempObject.value;
if (isConsoleTrace) { console.log("indicateDate date("+temp+")"); }
	tempObject = document.getElementById('dateLabel');
	tempObject.textContent = temp;
}


</script>
</head>
<body style="margin-top: 20px;">
<!--	<br><br><font color=#CC0000>Note: AK Diagnosing. Extra messages issued.</font><br><br>	-->
<!--	<br><br><font color=#CC0000>Note: Only Stanford, MLS, NWSL, NWSL Stock Images, and International are working correctly.  San Jose - maybe. (Icons available for these.)</font><br><br>	-->
<div class="container">
	<h1>ISI Photos Assignment Request <small>(2020-11-05)</small></h1>
	<p>
		<?php
			echo $_SERVER['HTTP_HOST'];
		?>
	</p>
	<input class="btn btn-default" type="button" id="staleButton" style="display: none;" value="Stale logic. Click to refresh." onClick="javascript:document.location.reload(true);">
		<form name="isi_transfer" id="isi_transfer" action="none.php" method="post" enctype="multipart/form-data">
			<input type="hidden" id="isi_selected_league" name="isi_selected_league" value="none">
			<input type="hidden" id="isi_selected_league_email" name="isi_selected_league_email" value="none">
			<input type="hidden" id="isi_selected_hometeam" name="isi_selected_hometeam" value="">
			<input type="hidden" id="isi_selected_visitorteam" name="isi_selected_visitorteam" value="">
			<input type="hidden" id="isi_selected_keyword" name="isi_selected_keyword" value="">
			<input type="hidden" id="isi_selected_gallery" name="isi_selected_gallery" value="">
			<input type="hidden" id="isi_selected_gallery_title" name="isi_selected_gallery_title" value="">
			<?php
			#	Defaults to today's date (year-month-day, month day, year)
			#	yyyy-MM-dd,MMM d, yyyy
			#	$today = date("Y-m-d") . "," . date("F j, Y");
			#	May 8, 2017
				$today = date("F j, Y");
			#	print "<input type=\"hidden\" id=\"isi_selected_date\" name=\"isi_selected_date\" value=\"{$today}\" onChange=\"indicateDate();\">";
			#	onchange for hidden works with type=text, style=display:none AND onchange() after changing the value. Update Calendar.popUp.js.
				print "<input type=\"text\" id=\"isi_selected_date\" name=\"isi_selected_date\" value=\"{$today}\" style=\"display: none;\" onChange=\"indicateDate();\">";
				$today = date("Y-m-d");
				print "<input type=\"text\" id=\"isi_calendar_date\" name=\"isi_calendar_date\" value=\"{$today}\" style=\"display: none;\">";
			?>

			<?php
			#	Initialization.
			#	Select box for organizations
				// print "<div class=\"form-group\" style=\"width:450px\">
				// 		<label id='eventlabel01'>Organization list from database</label>
				// 		<select class='form-control' name='isi_league' id='isi_league' onChange='javascript:changeLeague(this);'>";
				// print "<option value='none'>Select client/teams</option>";
				// foreach ($collectionArray as $item) {
				// 	$temp = explode(":", $item);
				// 	if ($temp[0] == 'Other') { continue; }
				// 	print "<option value=\"{$item}\">{$temp[0]}</option>";
				// }
				// print "<option value=\"Other\">-- Other --</option>";
				// print "</select></div>";

			?>
			<p class="field">
				<label>Clients & Teams</label>
				<label class="select" for="organization">
					<select class="select" name="organization" id="organization">
						<option value='none'>select organization</option>
						<?php
							foreach ($collectionArray as $item) {
							$temp = explode(":", $item);
							if ($temp[0] == 'Other') { continue; }
							print "<option value=\"{$item}\">{$temp[0]}</option>";
						}
						?>
						<option value="Other">-- Other --</option>
					</select>
				</label>
			</p>
			<p class="field">
				<label id="contactlabel" for="requestor_data">Requestor</label>
				<input type="text" id="requestor_data" name="requestor_data" value="">
			</p>
			<p class="field">
				<label id="contactphonelabel" for="requestoremail_data">Requestor email</label>
				<input type="email" id="requestoremail_data" name="requestoremail_data" value="">
			</p>
			<p class="field">
				<label>Event Type</label>
				<label class="select" id="eventlabel03" for="isi_eventtype">
					<select class="select" name="isi_eventtype" id="isi_eventtype" onChange='javascript:changeEvent(this);'>
						<option value="none">select event type</option>
						<option value="eventgame">new type</option>
					</select>
				</label>
			</p>

			<div class="secondaryEventInfo">
			<label id="eventtypelabel"></label>
			<p id="eventtypeaction"></p>

			<div class="form-group" style="width:450px">
				<label id="eventhomelabel"></label>
				<select id="isi_league_hometeam" name="isi_league_hometeam" onChange="javascript:changeHome(this);">
					<option value="" selected>Select Team</option>
				</select>
			</div>
			<div class="form-group" style="width:450px">
				<input class="form-control" type="text" id="isi_selected_description" name="isi_selected_description" onClick="this.select();" >
			</div>
			<div class="form-group" style="width:450px">
				<label id="homeotherlabel"></label>
				<input class="form-control" type="text" id="isi_selected_hometeam_new" name="isi_selected_hometeam_new" value="" onClick="this.select();" >
			</div>

			<div class="form-group" style="width:450px">
			<label id="eventawaylabel"></label>
			<select class="form-control" id="isi_league_visitorteam" name="isi_league_visitorteam" onChange="javascript:changeVisitor(this,leagues['Nongame']['collections']);">
				<option value="" selected>Selection needed</option>
			</select>
			</div>
			<div class="form-group" style="width:450px">
			<label id="visitorotherlabel"></label>
			<input class="form-control" type="text" id="isi_selected_visitorteam_new" name="isi_selected_visitorteam_new" value="" onClick="this.select();" >
			</div>
</div> <!-- end secondaryEventInfo -->


<section id="datesection" style="display:none;">
<div class="form-group">
<table>
<tr><td>&nbsp;</td><td colspan=3><label>Event time window (local time)</label></td></tr>
<tr><td>
<label id="eventlabel02">Date:</label>
<?php
#	Defaults to today's date (month day, year)
	$today = date("F j, Y");
	print "<span id=\"dateLabel\">{$today}</span>";

?>
</td><td><label>Start</label></td><td width=2%>&nbsp;</td><td><label>End</label></td></tr>

<tr><td>
	<button class="btn btn-default date" type="button" data-provide="datepicker" name="isi_date" id="isi_date" data-date-format="MM d, yyyy">Change Date</button>
</td><td>
	<select class="form-control" name="isi_timestart" id="isi_timestart" onchange=javascript:changeStart(this);>
	<option value='00:00' >0:00 AM</option>
	<option value='01:00' >1:00 AM</option>
	<option value='02:00' >2:00 AM</option>
	<option value='03:00' >3:00 AM</option>
	<option value='04:00' >4:00 AM</option>
	<option value='05:00' >5:00 AM</option>
	<option value='06:00' >6:00 AM</option>
	<option value='07:00' >7:00 AM</option>
	<option value='08:00' >8:00 AM</option>
	<option value='09:00' >9:00 AM</option>
	<option value='10:00' >10:00 AM</option>
	<option value='11:00' selected >11:00 AM</option>
	<option value='12:00' >12:00 PM</option>
	<option value='13:00' >1:00 PM</option>
	<option value='14:00' >2:00 PM</option>
	<option value='15:00' >3:00 PM</option>
	<option value='16:00' >4:00 PM</option>
	<option value='17:00' >5:00 PM</option>
	<option value='18:00' >6:00 PM</option>
	<option value='19:00' >7:00 PM</option>
	<option value='20:00' >8:00 PM</option>
	<option value='21:00' >9:00 PM</option>
	<option value='22:00' >10:00 PM</option>
	<option value='23:00' >11:00 PM</option>
	</select>
	</td><td>&nbsp;</td><td>
	<select class="form-control" name="isi_timeend" id="isi_timeend" onchange=javascript:changeEnd(this);>
	<option value='00:00' >00:00 AM</option>
	<option value='01:00' >1:00 AM</option>
	<option value='02:00' >2:00 AM</option>
	<option value='03:00' >3:00 AM</option>
	<option value='04:00' >4:00 AM</option>
	<option value='05:00' >5:00 AM</option>
	<option value='06:00' >6:00 AM</option>
	<option value='07:00' >7:00 AM</option>
	<option value='08:00' >8:00 AM</option>
	<option value='09:00' >9:00 AM</option>
	<option value='10:00' >10:00 AM</option>
	<option value='11:00' >11:00 AM</option>
	<option value='12:00' >12:00 PM</option>
	<option value='13:00' >1:00 PM</option>
	<option value='14:00' >2:00 PM</option>
	<option value='15:00' selected >3:00 PM</option>
	<option value='16:00' >4:00 PM</option>
	<option value='17:00' >5:00 PM</option>
	<option value='18:00' >6:00 PM</option>
	<option value='19:00' >7:00 PM</option>
	<option value='20:00' >8:00 PM</option>
	<option value='21:00' >9:00 PM</option>
	<option value='22:00' >10:00 PM</option>
	<option value='23:00' >11:00 PM</option>
	</select>
</td></tr></table>
</div>
</section>

<section id="photographersection" style="display:none;">
<div class="form-group" style="width:450px">
	<label id="locationlabel">Location / Venue</label>
	<input class="form-control" type="text" id="location_data" name="location_data" value="">
</div>
<div class="form-group" style="width:450px">
	<label id="contactlabel">On site contact</label>
	<input class="form-control" type="text" id="contact_data" name="contact_data" value="">
</div>
<div class="form-group" style="width:450px">
	<label id="contactphonelabel">On site contact phone number</label>
	<input class="form-control" type="text" id="contactphone_data" name="contactphone_data" value="">
</div>
<div class="form-group" style="width:450px">
	<label id="contactphonelabel">On site contact email</label>
	<input class="form-control" type="text" id="contactemail_data" name="contactemail_data" value="">
</div>
<!--
	<div class="form-group" style="width:350px;">
		<label>Additional assignment details</label>
		<input class="form-control" type="textarea" rows="4" cols="60" id="isi_details" name="isi_details" value=""  >
	</div>
-->
	<div class="form-group" style="width:450px;">
		<label>Additional assignment details</label>
		<textarea class="form-control" rows="2" cols="60" id="isi_details" name="isi_details">&nbsp;</textarea>
	</div>

<table border=0>
<tr><td>
	<div class="form-group" style="width:150px">
		<label>Photographers</label>
	<select class="form-control" name="isi_count" id="isi_count" onChange='javascript:changeCount(this);'>
	<option value=1 selected>1</option>
	<option value=2>2</option>
	<option value=3>3</option>
	<option value=4>4</option>
	<option value=5>5</option>
	<option value=6>6</option>
	</select>
</div>
</td><td width=10%>&nbsp;</td><td>
	<div class="form-group" style="width:250px">
		<label>Number of days for assignment</label>
	<select class="form-control" name="isi_duration" id="isi_duration" onChange='javascript:changeDays(this);'>
	<option value=0 selected>Only today</option>
	<option value=1>2</option>
	<option value=2>3</option>
	<option value=3>4</option>
	</select>
</div>
</td></tr>
</table>
</section>
<section id="iconsection" style="display:none;">
	<div class="form-group" style="width:250px">
		<label>Assignment activity</label>
	<select class="form-control" name="isi_icon" id="isi_icon" onChange='javascript:changeIcon(this);'>
	<option value="">Select activity (in progress)</option>
	<option value='activity01.jpg'>"Need icons, then text for the icon"</option>
	<option value='activity02.jpg'>"List sports at Stanford"</option>
	<option value='activity03.jpg'>"US Soccer, Training, Travel, Portraits, Press Conference."</option>
	</select>
</div>
</section>


<br><br>

<input class="btn btn-default" type="button" id="isi_create" value="Click to request assignment" onClick="javascript:readytoassign();">
&nbsp;&nbsp;<span class="text-success" id="eventstatuslabel"></span>


</form>



<table border="0" id="statusTable" width="70%">
</table>
</div><!-- end container -->
<footer class="text-align-center background-light-200 padding-m position-fixed position-bottom width-100">
		<small>&#169;&nbsp;2020-<script>dateyy = new Date();document.write(dateyy.getFullYear());</script>
       Andrew Katsampes</small>
</footer>



<script src="includes/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript">
//d, dd: Numeric date, no leading zero and leading zero, respectively. Eg, 5, 05.
//D, DD: Abbreviated and full weekday names, respectively. Eg, Mon, Monday.
//m, mm: Numeric month, no leading zero and leading zero, respectively. Eg, 7, 07.
//M, MM: Abbreviated and full month names, respectively. Eg, Jan, January
//yy, yyyy: 2- and 4-digit years, respectively. Eg, 12, 2012.
//	$today = date("Y-m-d") . "," . date("F j, Y");
//	Original onClick event
//	tempObject=document.getElementById('isi_selected_date');
//	alert(tempObject.value);
//	javascript:cal.select(tempObject,'isi_date','yyyy-MM-dd,MMM d, yyyy');
$('#isi_date').on('changeDate', function(eventDate){
	var selectedDate = '';
//	Generate date yyyy-mm-dd for Google Calendar
	var inputDate = eventDate.date;
	var tempDay = inputDate.getDate();
	if (tempDay < 10) { tempDay = '0' + tempDay; }
	var tempMonth = inputDate.getMonth();
	tempMonth++;
	if (tempMonth < 10) { tempMonth = '0' + tempMonth; }
	var tempYear = inputDate.getFullYear();
	selectedDate = tempYear+'-'+tempMonth+'-'+tempDay;
	tempObject = document.getElementById('isi_calendar_date');
	tempObject.value = selectedDate;
//	Generate date month dd, yyyy for PhotoShelter gallery name
	inputDate = eventDate.date;
	tempDay = inputDate.getDate();
	tempMonth = inputDate.getMonth();
	tempMonth = monthText[tempMonth];
	tempYear = inputDate.getFullYear();
//	selectedDate = tempYear+'-'+tempMonth+'-'+tempDay+',';
//	Format used to be yyyy-mm-dd,month dd,yyyy
//	Not sure if necessary.
	selectedDate = tempMonth+' '+tempDay+', '+tempYear;
	tempObject = document.getElementById('isi_selected_date');
	tempObject.value = selectedDate;
	indicateDate();
	 $(this).datepicker('hide');
});
</script>
<script>
//	Hide the select team boxes, home team, visitor team, and description text fields.
//	Home team select box
tempObject = document.getElementById('isi_league_hometeam');
tempObject.style.display = "none";
//	Visitor team select box
tempObject = document.getElementById('isi_league_visitorteam');
tempObject.style.display = "none";
//	Home team text field status
tempObject = document.getElementById('isi_selected_hometeam_new');
tempObject.style.display = "none";
//	Visitor team text field status
tempObject = document.getElementById('isi_selected_visitorteam_new');
tempObject.style.display = "none";
//	Event description text field status
tempObject = document.getElementById('isi_selected_description');
tempObject.style.display = "none";
//	Event labels and buttons
//eventVisible("none");
//	Date, start time, and event time labels and buttons
dateVisible("none");
//	Select photographer labels and buttons
photographerVisible("none");
//	Javascript is stale. Display the refresh button
if (isStale == true) {
	tempObject = document.getElementById('staleButton');
	tempObject.style.display = "inline";
}


</script>
<form id="formeditor" action="isi.schedule.editor.php" method="post">
<input type="hidden" name="scheduleeditor" id="scheduleeditor" >
</form>
</body>
</html>