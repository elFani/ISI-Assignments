<?php
#	--------------------------------------------
#	Overview
#	Select assignments for an organization, order by date
#	and present a 6 week calendar with the events as icons.
#
#	Requests
#
#	POST
#	From editors, with posted parameter "public=n"
#	For anybody, via javascript submit with parameter "public=y"
#
#	--------------------------------------------
#	Issue:
#	--------------------------------------------
#
#	Read the database and build all the arrays.
#	Build the calendar/table

#	Load in the settings and includes - colors.
include ("includes/isi.schedule.tools.php");

#	Enable/Disable PHP print statements.
$isLogging = false;
#$isLogging = true;

#	HTML constants, colors, sizes
#	Width of Status TD
$STATUS_TD	= 20;
$DAY_WIDTH	= 100;
$IMG_WIDTH	= 50;
$IMG_HEIGHT	= 50;
$OFFSET		= 25;
$SUMMARY_IMG_WIDTH	= 20;

#	Colors FFF4D2 FFECC1
$BGCOLORS['text']	= "#888888";
$BGCOLORS['border']	= "#EEEEEE";
$BGCOLORS['table']	= "#FFFFFF";
$BGCOLORS['today']	= "#FF40FF";

#	Navigation TDs
#$NAVIGATION[0]	= "<td width=50><center><a href=\"javascript:navigate(-3);\"><img src='icons/Arrow2Up.gif' alt='Shift back 3 weeks' width=20></a></center></td>";
$NAVIGATION[0]	= "<td width=50 rowspan=2 valign='top' align='center'><table><tr height=20><td></td></tr><tr><td><a href=\"javascript:navigate(-3);\"><img src='icons/Arrow2Up.gif' title='Shift back 3 weeks' width=20></a></td></tr><tr height=40><td></td></tr><tr><td><a href=\"javascript:navigate(-1);\"><img src='icons/ArrowUp.gif' title='Shift back 1 week' width=20></a></center></td></tr></table></td>";
#$NAVIGATION[1]	= "<tr><td><a href=\"javascript:navigate(-1);\"><img src='icons/ArrowUp.gif' alt='Shift back 1 week' width=20></a></center></td></tr>";
$NAVIGATION[1]	= "";
$NAVIGATION[2]	= "<td width=50><center><a href=\"javascript:navigate(0);\">Reset</a></center></td>";
$NAVIGATION[3]	= "<td width=50>&nbsp;</td>";
$NAVIGATION[4]	= "<td width=50 rowspan=2 valign='bottom' align='center'><table><tr><td><a href=\"javascript:navigate(1);\"><img src='icons/ArrowDown.gif' title='Shift forward 1 week' width=20></a></td></tr><tr height=40><td></td></tr><tr><td><a href=\"javascript:navigate(3);\"><img src='icons/Arrow2Down.gif' title='Shift forward 3 weeks' width=20></a></td></tr><tr height=20><td></td></tr></table></td>";
$NAVIGATION[5]	= "";
#$NAVIGATION[4]	= "<td width=50><center><a href=\"javascript:navigate(1);\"><img src='icons/ArrowDown.gif' alt='Shift forward 1 week' width=20></a></center></td>";
#$NAVIGATION[5]	= "<td width=50><center><a href=\"javascript:navigate(3);\"><img src='icons/Arrow2Down.gif' alt='Shift forward 3 weeks' width=20></a></center></td>";
$NAVIGATION[0]	= "";
$NAVIGATION[1]	= "";
$NAVIGATION[2]	= "";
$NAVIGATION[3]	= "";
$NAVIGATION[4]	= "";
$NAVIGATION[5]	= "";


#	Collect passed parameters
#	$POST from Editor page with scheduleeditor and calendaradjust
#	$POST from public calendar page with organization=Stanford%20Athletics,public=yes
$currentEditor = $_POST['scheduleeditor'];
$tempArray = explode(":", $currentEditor);
$currentEditorName = $tempArray[1];
$currentOrganization = $tempArray[3];
$calendarAdjust = (int)$_POST['calendaradjust'];
#print "AK000 Adjust({$calendarAdjust})<br>";
$isPublicFlag = $_POST['public'];
#print "AK001 Public({$isPublicFlag}) Organization({$currentOrganization})<br>";
if ($isPublicFlag == 'yes') {
	$currentOrganization  = $_POST['organization'];
}
#print "AK002 Organization({$currentOrganization})<br>";
?>

<html>
<head>
<title>ISI Schedule Calendar V1.6</title>
<META HTTP-EQUIV="Expires" CONTENT="Tue, 01 Jan 1980 1:00:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<link rel="stylesheet" href="includes/css/bootstrap.min.css">
<style type="text/css">

.detail, .summary {
  position: absolute;
}

.detail {
  top: 40px;
  left: 40px;
  height: 400px;
  width: 250px;
  border: 2px;
  border-style: solid;
  border-color:#EEEEEE;
  background:#CCCCCC;
  z-index: -1;
  visibility: hidden;
}

.summary {
  top: 80px;
  left: 80px;
  height: 200px;
  width: 350px;
  border: 2px;
  border-style: solid;
  border-color:#666666;
  background:#EEEEEE;
  z-index: -1;
  visibility: hidden;
}


.datecell {
  width: 100px;
  height: 70px;
  overflow-y: auto;
  overflow-x: hidden;
}

</style>

<script>
//	Diagnostic javascript console messages
var isConsoleTrace = false;
//var isConsoleTrace = true;

//	Window coordinates
tempH = window.innerHeight;
tempW = window.innerWidth;
if (isConsoleTrace) { console.log ("Window ("+tempH+":"+tempW+")"); }
var windowBottom = tempH;
var INDEX_STATUS	= 0;
var INDEX_GALLERY	= 1;
var INDEX_ICON		= 2;
var INDEX_TIME		= 3;
var IMAGEURL = "icons/";
//var IMAGEURL = "http://[ID]:{[PW]}@photographer.isiphotos.com/icons/";
var SUMMARY_IMG_WIDTH = 20;
var SUMMARY_ROW_HEIGHT = 33;
var SUMMARY_TR_HEIGHT = 25;
var DETAIL_LOCATION	= 4;
var DETAIL_CONTACT	= 5;
var DETAIL_EMAIL	= 6;
var DETAIL_PHONE	= 7;
var DETAIL_PHOTO	= 8;
var DETAIL_DETAILS	= 9;
var DETAIL_IMG_WIDTH = 20;
var DETAIL_ROW_HEIGHT = 30;
var DETAIL_TR_HEIGHT = 20;

//	Build array of colors
//	Set isPublic flag. isPublic then "no colors," no status presented.
var bgColors = Object();
bgColors['today']	= "#FF40FF";
bgColors['text']	= "#888888";
bgColors['border']	= "#DDDDDD";
//bgColors['borderreset']	= "#FFFFFF";
bgColors['borderreset']	= "#DDDDDD";
bgColors['table']	= "#FFFFFF";
bgColors['summaryText']	= "#333333";
bgColors['detailText']	= "#222222";
bgColors['publicBorder'] = "#666666";
<?php
foreach ($BGCOLORS as $key => $item) {
	print "bgColors['{$key}'] = '{$item}';\n";
}
if ($isPublicFlag == 'yes') {
	print "var isPublic = true;\n";
} else {
	print "var isPublic = false;\n";
}
?>


//	Build array of status with associated dates
<?php
$statusArray = array();
$statusArray[$STATUS_INITIAL]	= array();
$statusArray[$STATUS_REQUEST]	= array();
$statusArray[$STATUS_ASSIGNED]	= array();
$statusArray[$STATUS_UPLOAD]	= array();
$statusArray[$STATUS_WAITING]	= array();
$statusArray[$STATUS_GETTY]		= array();
$statusArray[$STATUS_COMPLETE]	= array();
#	Convert PHP arrays to javascript realtime events array.
print "var statusArray = new Object();";
print "statusArray['{$STATUS_INITIAL}'] = new Array();";
print "statusArray['{$STATUS_REQUEST}'] = new Array();";
print "statusArray['{$STATUS_ASSIGNED}'] = new Array();";
print "statusArray['{$STATUS_UPLOAD}'] = new Array();";
print "statusArray['{$STATUS_WAITING}'] = new Array();";
print "statusArray['{$STATUS_GETTY}'] = new Array();";
print "statusArray['{$STATUS_COMPLETE}'] = new Array();";
?>
//	As TDs are filtered and highlighted based on status, list them in activeStatus
var activeStatus = new Array();

// Based on status, highlight days with matching assignment status
function filterStatus(inputStatus) {
if (isConsoleTrace) { console.log("filterStatus ("+inputStatus+")"); }
if (isPublic == true) { return; }
//	Reset any TDs before filtering next status
resetStatus();
var tempObject = '';
var	tempMax = statusArray[inputStatus].length;
var tempIndex = 0;
//	Using status and spin through the list of dates and update the border color
for (tempIndex = 0; tempIndex < tempMax; tempIndex++) {
		temp = statusArray[inputStatus][tempIndex];
		tempObject = document.getElementById(temp);
		if (tempObject != null) {
			tempObject.style.borderWidth = 3;
			tempObject.style.borderStyle = "solid";
			tempObject.style.borderColor = bgColors[inputStatus];
			activeStatus.push(temp);
		}
	}
}

//	Reset any TDs highlighted by filterStatus
function resetStatus() {
var	tempMax = activeStatus.length;
if (tempMax == 0) { return; }
var tempIndex = 0;
for (tempIndex = 0; tempIndex < tempMax; tempIndex++) {
		temp = activeStatus[tempIndex];
		tempObject = document.getElementById(temp);
		if (tempObject != null) {
			tempObject.style.borderWidth = 1;
			tempObject.style.borderStyle = "solid";
			tempObject.style.borderColor = bgColors['borderreset'];
		}
	}
activeStatus = Array();
return;
}

//	Day Summary set/reset
//	Display a summary of the day, change border size and color
function daySummary(inputObject, inputPosition, inputDate, inputKey, inputStatus) {
//if (isConsoleTrace) { console.log("daySummary ("+inputObject.id+")"); }
//temp = bgColors[inputStatus];
//if (isConsoleTrace) { console.log("daySummary ("+inputPosition+") ("+inputDate+") ("+inputKey+") ("+temp+")"); }
//	Event details - hide
var tempObject = document.getElementById("detailObject");
tempObject.style.zIndex = -1;
tempObject.style.visibility = 'hidden';

//	Get position of day TD
var temp = inputObject.getBoundingClientRect();
var currentTop	= temp.top;
var currentRight= temp.right;
var currentLeft	= temp.left;
//if (isConsoleTrace) { console.log("daySummary Top("+currentTop+") Left("+currentLeft+") Right("+currentRight+")"); }

//	Build day summary
//	Increment height for each row.
var index = 0;
var max = statusArray[inputKey].length;
var currentHeight = 0;
temp = "<center><table name='daySummary'>";
//temp = temp + "<tr height='"+SUMMARY_TR_HEIGHT+"'><td valign='top' align='center' colspan='3'><font color='"+bgColors['summaryText']+"'>"+inputDate+"</font></td></tr>";
tempHeight = SUMMARY_TR_HEIGHT +10;
temp = temp + "<tr height='"+tempHeight+"'><td valign='center' align='center' colspan='3'><font color='"+bgColors['summaryText']+"'>"+inputDate+"</font></td></tr>";
currentHeight += SUMMARY_ROW_HEIGHT;
for (index =0; index < max; index++) {
	tempArray = statusArray[inputKey][index].split("+");
//	temp = temp + "<tr><td valign='top'><img src='ttthover_images/logosport_volleyball.jpg' width=20 style='border:2px solid #EEEE33'></td><td valign='top'><font color='#EEEEEE' size='-2'>10:00 AM</font></td><td valign='top'><font color='#EEEEEE' size='-1'>Stanford Volleyball W v University of Utah</font></td></tr>";
	tempStatus = tempArray[INDEX_STATUS];
//	Public calendar has no status colors	
	tempColor = " style='border:2px solid "+bgColors[tempStatus]+"' ";
	if (isPublic == true) { tempColor = ''; }
	temp = temp + "<tr height='"+SUMMARY_TR_HEIGHT+"'><td valign='top' align='center'><img src='"+IMAGEURL+tempArray[INDEX_ICON]+"' width="+SUMMARY_IMG_WIDTH+" "+tempColor+"></td><td valign='center' align='center' width=45><font color='"+bgColors['summaryText']+"' size='-2'>"+tempArray[INDEX_TIME]+"</font></td><td valign='center'><font color='"+bgColors['summaryText']+"' size='-1'>"+tempArray[INDEX_GALLERY]+"</font></td></tr>";
	currentHeight += SUMMARY_ROW_HEIGHT;
}
temp = temp + "</table></center>";

//	Summary HTML/Table
tempObject = document.getElementById("summaryObject");
tempObject.innerHTML = temp;
//	Position summary to the left of current TD
if (inputPosition == 'positionleft') {
	tempObject.style.left = currentLeft -400;
}
//	Position summary to the right of current TD
if (inputPosition == 'positionright') {
	tempObject.style.left = currentRight +50;
} 
//	Avoid bottom overrun of summary
	tempBottom = windowBottom +window.scrollY;
	temp = currentTop +window.scrollY;
	temp = temp +currentHeight;
	if (temp >tempBottom) {
		temp = tempBottom -currentHeight -10;
	} else {
		temp = temp -currentHeight;
	}
tempObject.style.top = temp;

//	Day summary - visible with text
tempObject.style.height = currentHeight;
tempObject.style.visibility = 'visible';
tempObject.style.zIndex = 200;

//	Border color is based on lowest assignment status
//	Public calendar has no status colors	
tempObject = inputObject;
if (inputObject.id == 'datetext') {
	tempObject = inputObject.parentNode;
	tempObject = tempObject.parentNode;
	tempObject = tempObject.parentNode;
	tempObject = tempObject.parentNode;
	tempObject = tempObject.parentNode;
}

//	Public calendar has no status colors	
if (isPublic == false) {
	tempObject.style.borderWidth = 6;
	tempObject.style.borderStyle = "solid";
	tempObject.style.borderColor = bgColors[inputStatus];
}

}

//	Reset from summary of day display
//	Reset border color to initial
function dayReset(inputObject) {
//if (isConsoleTrace) { console.log("dayReset ("+inputObject.id+")"); }
tempObject = inputObject;
if (inputObject.id == 'datetext') {
	tempObject = inputObject.parentNode;
	tempObject = tempObject.parentNode;
	tempObject = tempObject.parentNode;
	tempObject = tempObject.parentNode;
	tempObject = tempObject.parentNode;
}

tempObject.style.borderWidth = 1;
tempObject.style.borderStyle = "solid";
tempObject.style.borderColor = bgColors['borderreset'];

//	Day Summary - hide
tempObject = document.getElementById("summaryObject");
tempObject.style.zIndex = -1;
tempObject.style.visibility = 'hidden';
tempObject.innerHTML = '... reset ...';
}

//	Present event details
function eventDetail(inputObject, inputKey, inputGallery, inputPosition, inputAdjust) {
if (isConsoleTrace) { console.log("eventDetail  Adjust("+inputAdjust+") ("+inputKey+") ("+inputGallery+")"); }
//	Day Summary - hide
tempObject = document.getElementById("summaryObject");
tempObject.style.zIndex = -1;
tempObject.innerHTML = '... reset ...';

//	If event details visible, toggle with click to hidden
tempObject = document.getElementById('detailObject');
if (tempObject.style.visibility == 'visible') {
	tempObject.style.zIndex = -1;
	tempObject.style.visibility = 'hidden';
	return;
}

//	Expand details into an array.
tempArray = statusArray[inputKey].split("+");
//console.dir(tempArray);

//	Build the event details
tempObject = document.getElementById('detailObject');
//temp = "<tr><td style='background-color:#EEEE33' bgcolor=\"#EEEE33\" width='10'>&nbsp;</td><td colspan='3' align='center'><font color='#333333' size='-1'>"+inputGallery+"</font></td></tr>";
//	Public calendar has no status colors	
if (isPublic == true) { 
	tempObject.style.borderColor = bgColors['publicBorder'];
} else {
	tempObject.style.borderColor = bgColors[tempArray[INDEX_STATUS]];
}

var currentHeight = 0;
temp = "<br><center><table name='eventDetails'>";
currentHeight += DETAIL_ROW_HEIGHT;
//	Public calendar has no status colors	
tempColor = " style='border:2px solid "+bgColors[tempArray[INDEX_STATUS]]+"' ";
if (isPublic == true) { tempColor = ''; }
temp = temp + "<tr height='"+DETAIL_TR_HEIGHT+"'><td width=3></td><td align='center'><img src='"+IMAGEURL+tempArray[INDEX_ICON]+"' width="+DETAIL_IMG_WIDTH+" "+tempColor+"></td><td align='left'><font color='"+bgColors['detailText']+"'>Event Details</font></td><td width=3></td></tr>";
currentHeight += DETAIL_ROW_HEIGHT;
temp = temp + "<tr height='"+DETAIL_TR_HEIGHT+"'><td width=3></td><td  colspan='2' align='center'><font color='"+bgColors['detailText']+"' size='-1'>"+inputGallery+"</font></td><td width=3></td></tr>";
currentHeight += DETAIL_ROW_HEIGHT;
temp = temp + "<tr height='"+DETAIL_TR_HEIGHT+"'><td width=3></td><td><font color='"+bgColors['detailText']+"' size='-2'>Start time:</font></td><td><font color='"+bgColors['detailText']+"' size='-2'>"+tempArray[INDEX_TIME]+"</font></td><td width=3></td></tr>";
currentHeight += DETAIL_ROW_HEIGHT;
temp = temp + "<tr height='"+DETAIL_TR_HEIGHT+"'><td width=3></td><td><font color='"+bgColors['detailText']+"' size='-2'>Location:</font></td><td><font color='"+bgColors['detailText']+"' size='-2'>"+tempArray[DETAIL_LOCATION]+"</font></td><td width=3></td></tr>";
currentHeight += DETAIL_ROW_HEIGHT;
temp = temp + "<tr height='"+DETAIL_TR_HEIGHT+"'><td width=3></td><td><font color='"+bgColors['detailText']+"' size='-2'>Contact name:</font></td><td><font color='"+bgColors['detailText']+"' size='-2'>"+tempArray[DETAIL_CONTACT]+"</font></td><td width=3></td></tr>";
currentHeight += DETAIL_ROW_HEIGHT;
temp = temp + "<tr height='"+DETAIL_TR_HEIGHT+"'><td width=3></td><td><font color='"+bgColors['detailText']+"' size='-2'>Contact email:</font></td><td><font color='"+bgColors['detailText']+"' size='-2'>"+tempArray[DETAIL_EMAIL]+"</font></td><td width=3></td></tr>";
currentHeight += DETAIL_ROW_HEIGHT;
temp = temp + "<tr height='"+DETAIL_TR_HEIGHT+"'><td width=3></td><td><font color='"+bgColors['detailText']+"' size='-2'>Contact phone:</font></td><td><font color='"+bgColors['detailText']+"' size='-2'>"+tempArray[DETAIL_PHONE]+"</font></td><td width=3></td></tr>";
currentHeight += DETAIL_ROW_HEIGHT;
temp = temp + "<tr height='"+DETAIL_TR_HEIGHT+"'><td width=3></td><td><font color='"+bgColors['detailText']+"' size='-2'>Photographer:</font></td><td><font color='"+bgColors['detailText']+"' size='-2'>"+tempArray[DETAIL_PHOTO]+"</font></td><td width=3></td></tr>";
currentHeight += DETAIL_ROW_HEIGHT;
temp = temp + "<tr height='"+DETAIL_TR_HEIGHT+"'><td width=3></td><td><font color='"+bgColors['detailText']+"' size='-2' valign='top'>Details:</font></td><td><font color='"+bgColors['detailText']+"' size='-2'>"+tempArray[DETAIL_DETAILS]+"</font></td><td width=3></td></tr>";
currentHeight += DETAIL_ROW_HEIGHT;
temp = temp + "</table></center>";
tempObject.innerHTML = temp;

//	Increment height for each row.
tempObject.style.height = currentHeight;

//	Position the event details
//	Calculate top and left, based on bounding top/left, scroll, height, and window bottom limit
var temp = inputObject.getBoundingClientRect();
var currentTop	= temp.top;
var currentRight= temp.right;
var currentLeft	= temp.left;
//	Position summary to the left of current TD
if (inputPosition == 'positionleft') {
	tempObject.style.left = currentLeft -275 -inputAdjust;
}
//	Position summary to the right of current TD
if (inputPosition == 'positionright') {
	tempObject.style.left = currentRight +50 +inputAdjust;
}
//	Avoid bottom overrun of event details
	tempBottom = windowBottom +window.scrollY;
	temp = currentTop +window.scrollY;
	temp = temp +currentHeight;
	if (temp >tempBottom) {
		temp = tempBottom -currentHeight -10;
	} else {
		temp = temp -currentHeight;
	}
tempObject.style.top = temp;


//	Present the event details
tempObject.style.visibility = 'visible';
tempObject.style.zIndex = 200;

}

//	Navigate
//	Backwards, 3 weeks or 1 week
//	Forwards, 3 weeks or 1 week
function navigate(inputAmount) {
if (isConsoleTrace) { console.log("navigate  Input("+inputAmount+")"); }
tempAmount = inputAmount *(-7);
<?php
print "tempAmount = tempAmount +{$calendarAdjust};\n";
?>
//if (isConsoleTrace) { console.log("navigate  Amount("+tempAmount+")"); }
tempObject = document.getElementById('calendaradjust');
tempObject.value = tempAmount;
if (isPublic == true) {
	temp = 'yes';
} else {
	temp = 'no';
}
tempObject = document.getElementById('public');
tempObject.value = temp;
tempObject = document.getElementById('formcalendar');
tempObject.submit();
}




</script>
</head>
<body style="margin-top: 20px;">
<div class="container">
<?php
	$temp = '';
	if ($currentOrganization != 'all') { $temp = "&nbsp;&nbsp;&nbsp;&nbsp;<font size=+2>{$currentOrganization}</font>"; }
	$tempScript = '';
	if ($isPublicFlag == 'no') { $tempScript = "<script type='text/javascript'>if (isConsoleTrace) {document.write('<font size=-1 color=#3300FF>'); }</script>(2020-07-03)</font>"; }
	print "<font size=+3>ISI Schedule Calendar</font>{$temp}&nbsp;&nbsp;{$tempScript}";
?>
<div class="row"> 
</div></div>
<br>
<?php
#	Open the database
$connection = openDB();

#	Dates. Calendar start, end, day of week index ...
#	$query = "select DATE_FORMAT(NOW(), '%M %e') as 'today', DATE_FORMAT(NOW(), '%w') as 'todayIndex', DATE(DATE_SUB(NOW(),INTERVAL 14 DAY)) as 'startCalendar', DATE_FORMAT(DATE(DATE_SUB(NOW(),INTERVAL 14 DAY)), '%w') as 'startIndex', DATE(DATE_ADD(NOW(),INTERVAL 28 DAY)) as 'endCalendar'   ";
#	$query = "select DATE_FORMAT('2020-06-01', '%M %e') as 'today',DATE_FORMAT('2020-06-01', '%Y-%m-%d') as 'dbtoday', DATE_FORMAT('2020-06-01', '%w') as 'todayIndex', DATE(DATE_SUB('2020-06-01',INTERVAL 21 DAY)) as 'startCalendar', DATE_FORMAT(DATE(DATE_SUB('2020-06-01',INTERVAL 21 DAY)), '%w') as 'startIndex', DATE(DATE_ADD('2020-06-01',INTERVAL 35 DAY)) as 'endCalendar'   ";
$dateLow = -(21 +$calendarAdjust);
$dateHigh = 56 +$dateLow;
#print "AK001 ({$dateLow}) ({$dateHigh})<br>";
#	$query = "select DATE_FORMAT('2020-06-01', '%M %e') as 'today',DATE_FORMAT('2020-06-01', '%Y-%m-%d') as 'dbtoday', DATE_FORMAT('2020-06-01', '%w') as 'todayIndex', DATE(DATE_ADD('2020-06-01',INTERVAL {$dateLow} DAY)) as 'startCalendar', DATE(DATE_ADD('2020-06-01',INTERVAL {$dateHigh} DAY)) as 'endCalendar'   ";
$query = "select DATE_FORMAT(NOW(), '%M %e') as 'today',DATE_FORMAT(NOW(), '%Y-%m-%d') as 'dbtoday', DATE_FORMAT(NOW(), '%w') as 'todayIndex', DATE(DATE_ADD(NOW(),INTERVAL {$dateLow} DAY)) as 'startCalendar', DATE(DATE_ADD(NOW(),INTERVAL {$dateHigh} DAY)) as 'endCalendar'   ";
if ($isLogging) { print "Date SQL {$query}<br>"; }
$data = executeSQL($connection, $query);
$max = mysqli_num_rows($data);
if ($isLogging) { print "Date Rows Max({$max})<br>"; }
if ($max <1) { print "Database failed. Exiting."; exit; }
$row = mysqli_fetch_assoc($data);
$today = $row['today'];
$dbtoday = $row['dbtoday'];
$startCalendar = $row['startCalendar'];
$endCalendar = $row['endCalendar'];
$dayindex = $row['todayIndex'];

#	Array of DB Dates, filled later with assignments for each date
#	Table with key as DB Date and table of DB Dates.
$dates = array();
$datesIndex = array();
$index = 0;
$tableStart = 0;
$tableEnd = $index;
$temp = $endCalendar . ' +1 day';
$period = new DatePeriod(new DateTime($startCalendar), new DateInterval('P1D'), new DateTime($temp));
    foreach ($period as $date) {
#        $dates[] = $date->format("Y-m-d");
#        $dates[] = $date->format("M j");
		$key = date_format($date, 'Y-m-d');
		$datesIndex[$index] = $key;
		$list = array();
		$list['month'] = date_format($date, 'F');
		$list['date'] = date_format($date, 'j');
       	$list['dayindex'] = $dayindex;
       	$list['assignments'] = array();
       	if ($dayindex == 0 && $tableStart == '') { $tableStart = $index +7; }
       	if ($dayindex == 6 && $tableStart != '') { $tableEnd = $index; }
		$dates[$key] = $list;
        $dayindex++;
        if ($dayindex >6) { $dayindex = 0; }
        $index++;
    }
//	Present only 6 weeks
if ($index > 56) { $tableEnd -= 7; }

#print "AK200 ({$datesIndex[$tableStart]}) ({$datesIndex[$tableEnd]}) Index({$index})<br>";
#   print_r($dates);

#	Get the assignments within the table date range.
#	Determine maximum number of assignments in a day
#	Determine next most number of assignments in a day
#	Use to build/define height of TD for a date, which affects scroll bar.
$maxCount = 0;
$priorCount = 0;
$currentCount = 0;
$currentDate = '';
$priorDate = '';
#	Get the assignments for the current Organization
#	If all, then all organizations for editor
$tempJoin = '';
$tempWhere = '';
$tempOrganization = mysqli_real_escape_string($connection, $currentOrganization);
if ($currentOrganization != '' && $currentOrganization != 'all') { $tempWhere = " and assign.assign_organization = \"{$tempOrganization}\" "; }
if ($currentOrganization == 'all') {
	$tempJoin = " join schedule_editors as editor on assign.assign_organization = editor.editor_organization ";
	$tempWhere = " and editor.editor_name = '{$currentEditorName}' ";
}
#$query = "select assign.assign_ID as 'ID', assign.assign_date as 'date', DATE_FORMAT(assign.assign_timeStart, '%l:%i %p') as 'time', assign.assign_gallery as 'gallery', assign.assign_icon as 'icon', assign.assign_status as 'status', assign.assign_location as 'location', assign.assign_contactName as 'contact', assign.assign_contactEmail as 'email', assign.assign_contactPhone as 'phone', photographer.photographer_name as 'photographer', assign.assign_details as 'details' from schedule_assignments as assign {$tempJoin} join schedule_photographers as photographer on assign.assign_photographerIDs = photographer.photographer_ID where assign.assign_date between DATE(DATE_ADD('2020-06-01',INTERVAL {$dateLow} DAY)) and DATE(DATE_ADD('2020-06-01',INTERVAL {$dateHigh} DAY)) {$tempWhere} order by assign.assign_date asc, assign.assign_timeStart asc, assign.assign_status";
$query = "select assign.assign_ID as 'ID', assign.assign_date as 'date', DATE_FORMAT(assign.assign_timeStart, '%l:%i %p') as 'time', assign.assign_gallery as 'gallery', assign.assign_icon as 'icon', assign.assign_status as 'status', assign.assign_location as 'location', assign.assign_contactName as 'contact', assign.assign_contactEmail as 'email', assign.assign_contactPhone as 'phone', photographer.photographer_name as 'photographer', assign.assign_details as 'details', assign.assign_meidID as 'meidID', assign.assign_images as 'imageCount' from schedule_assignments as assign {$tempJoin} join schedule_photographers as photographer on assign.assign_photographerIDs = photographer.photographer_ID where assign.assign_date between DATE(DATE_ADD(NOW(),INTERVAL {$dateLow} DAY)) and DATE(DATE_ADD(NOW(),INTERVAL {$dateHigh} DAY)) {$tempWhere} order by assign.assign_date asc, assign.assign_timeStart asc, assign.assign_status";
if ($isLogging) { print "Assignments SQL: {$query}<br>"; }
$data = executeSQL($connection, $query);
$max = mysqli_num_rows($data);
if ($isLogging) { print "Assignments Count: ({$max})<br>"; }
$temp = '';
$tempIndex = 0;
if ($max > 0) {
	while ($row = mysqli_fetch_assoc($data)) {
		$currentDate = $row['date'];
		if ($currentDate != $priorDate) {
			$priorDate = $currentDate;
			if ($currentCount > $priorCount) {
				$priorCount = $currentCount;
			}
			if ($currentCount > $maxCount) {
				$priorCount = $maxCount;
				$maxCount = $currentCount;
			}
			$currentCount = 0;
		}
		$currentCount++;
//	MaxCount day will scroll. PriorCount days used for cell height.

		$assignment = array();
		$assignment['status'] = $row['status'];
		$temp = $row['gallery'];
		$tempIndex = strrpos($temp, ',');
		$temp = substr($temp, 0, $tempIndex);
		$tempIndex = strrpos($temp, ',');
		$temp = substr($temp, 0, $tempIndex);
		$assignment['gallery'] = $temp;		
		$assignment['icon'] = $row['icon'];
		$assignment['time'] = $row['time'];
		$assignment['location'] = $row['location'];
		$assignment['contact'] = $row['contact'];
		$assignment['email'] = $row['email'];
		$assignment['phone'] = $row['phone'];
		$assignment['photographer'] = $row['photographer'];
		$assignment['details'] = $row['details'];
		$assignment['ID'] = "ID:{$row['ID']}";
#		array_push($dates[$row['date']][assignments], $assignment);
#		Build array of status and dates
		$test = in_array($row['date'], $statusArray[$row['status']]);
		if ($test == false) {
			$tempStatus = $row['status'];
			if ($dbtoday > $row['date'] && $row['status'] == $STATUS_ASSIGNED) {
				$tempStatus = $STATUS_WAITING;
			}
			$test = intval($row['imageCount']);
			if ($row['status'] == $STATUS_GETTY && $test >0) {
				$tempStatus = $STATUS_COMPLETE;
			}
			$assignment['status'] = $tempStatus;
			array_push($statusArray[$tempStatus], $row['date']);
		}
#	Insert assignment with details into array with updated status
		array_push($dates[$row['date']][assignments], $assignment);
	}
}

#	Capture counts when dates done, a type of date change.
if ($currentCount > $priorCount) {
	$priorCount = $currentCount;
}
if ($currentCount > $maxCount) {
	$priorCount = $maxCount;
	$maxCount = $currentCount;
}
//if ($currentCount >0 && $maxCount == 0) { $maxCount = $currentCount; }

//	MaxCount day will scroll. PriorCount days used for cell height.
if ($priorCount == 0) { $priorCount = $maxCount; }
if (($priorCount % 2) > 0) {
	$temp = intdiv($priorCount,2) +1;
} else {
	$temp = intdiv($priorCount, 2);
}
$dataCellHeight = 30 +($temp *50);

#	Populate the javascript status array with dates
print '<script>';
#print "console.log('statusArray ...');";
foreach ($statusArray as $key => $dataArray) {
	foreach ($dataArray as $item) {
		print "statusArray['$key'].push('{$item}');";
	}
}
#print 'console.dir(statusArray);';
print '</script>';

foreach ($dates as $key => $dataArray) {
	$assignmentArray = $dataArray['assignments'];
	$temp = count($assignmentArray);
	$tempKey = '';
	if ($temp > 0) {
		print '<script>';
		print "statusArray['$key'] = new Array();";
		print '</script>';
#	print "Dates key {$key} Array({$temp})<br>";
		$index = 0;
		foreach ($assignmentArray as $tempArray) {
			$temp = implode("+", $tempArray);
#			print "$key ({$tempArray['ID']}) => ({$temp})<br>";
			print '<script>';
			print "statusArray['$key'][$index] = '$temp';";
#			print "console.dir(statusArray['$key']);";
			$temp = "{$tempArray['status']}+gallery+{$tempArray['icon']}+{$tempArray['time']}+{$tempArray['location']}+{$tempArray['contact']}+{$tempArray['email']}+{$tempArray['phone']}+{$tempArray['photographer']}+{$tempArray['details']}";
			print "statusArray['{$tempArray['ID']}'] = '$temp';";
#			print "console.dir(statusArray['{$tempArray['ID']}']);";
			print '</script>';
			$index++;
		}
	}

}

#	Close the database
mysqli_close($connection);

#print "<font size=+1 color='#FF0000'>AK Tinkering 1123AM</font><br><br>";

$tdIndex = 0;
$rowIndex = 0;
$temp = '';
$currentKey = '';
$currentMonth = date_format($startCalendar, 'M');
$tempMonth = $currentMonth;
#	Assignments calendar
print "<center><table name='wholeCalendar' border='1' bgcolor='{$BGCOLORS['table']}' width='95%' bordercolor='{$BGCOLORS['border']}'>";
#	Print today's date and the status legend with active filter links
#	Remainder of line "</td><td width='100' valign='center' align='right'><font color='#888888'>May 17<font></td><td width='100' valign='center' align='right'><font color='#888888'>18<font></td><td width='100' valign='center' align='right'><font color='#888888'>19<font></td><td width='100' valign='center' align='right'><font color='#888888'>20<font></td><td width='100' valign='center' align='right'><font color='#888888'>21<font></td><td width='100' valign='center' align='right'><font color='#888888'>22<font></td><td width='100' valign='center' align='right'><font color='#888888'>23<font></td><td width='30' valign='center' align='center' rowspan='2' bgcolor='#AAAAAA'><img src='icons/Arrow2Up.gif' height='150' width='15'></td></tr>";
print "<tr><td width='{$DAY_WIDTH}' rowspan='6' valign='top' align='center'><font color='{$BGCOLORS['text']}'>{$today}</font><br><br>";
#print "<tr><td rowspan='6' valign='top' align='center'>{$today}<br><br>";
if ($isPublicFlag == 'no') {
	print "<font color='{$BGCOLORS['text']}'>Status</font><table><tr height=4><td></td></tr><tr><td width={$STATUS_TD} height={$STATUS_TD} bgcolor='{$BGCOLORS[$STATUS_INITIAL]}' onclick=\"javascript:filterStatus('{$STATUS_INITIAL}');\"></td><tr>";
	print "<tr height=3><td></td></tr>";
	print "<tr><td width={$STATUS_TD} height={$STATUS_TD} bgcolor='{$BGCOLORS[$STATUS_REQUEST]}' onclick=\"javascript:filterStatus('{$STATUS_REQUEST}');\"></td><tr>";
	print "<tr height=3><td></td></tr>";
	print "<tr><td width={$STATUS_TD} height={$STATUS_TD} bgcolor='{$BGCOLORS[$STATUS_ASSIGNED]}' onclick=\"javascript:filterStatus('{$STATUS_ASSIGNED}');\"></td><tr>";
	print "<tr height=3><td></td></tr>";
	print "<tr><td width={$STATUS_TD} height={$STATUS_TD} bgcolor='{$BGCOLORS[$STATUS_WAITING]}' onclick=\"javascript:filterStatus('{$STATUS_WAITING}');\"></td><tr>";
	print "<tr height=3><td></td></tr>";
	print "<tr><td width={$STATUS_TD} height={$STATUS_TD} bgcolor='{$BGCOLORS[$STATUS_UPLOAD]}' onclick=\"javascript:filterStatus('{$STATUS_UPLOAD}');\"></td><tr>";
	print "<tr height=3><td></td></tr>";
	print "<tr><td width={$STATUS_TD} height={$STATUS_TD} bgcolor='{$BGCOLORS[$STATUS_GETTY]}' onclick=\"javascript:filterStatus('{$STATUS_GETTY}');\"></td><tr>";
	print "<tr height=3><td></td></tr>";
	print "<tr><td width={$STATUS_TD} height={$STATUS_TD} bgcolor='{$BGCOLORS[$STATUS_COMPLETE]}' onclick=\"javascript:filterStatus('{$STATUS_COMPLETE}');\"></td><tr>";
	print "</table>";
}
#	Navigation
print "<br><br><font color='{$BGCOLORS['text']}'>Shift</font><br><font color='{$BGCOLORS['text']}'>Calendar</font><br><br>";
print "<a href=\"javascript:navigate(-3);\"><img src='icons/Arrow2Up.gif' title='Shift back 3 weeks' width=20></a><br><br>";
print "<a href=\"javascript:navigate(-1);\"><img src='icons/ArrowUp.gif' title='Shift back 1 week' width=20></a><br><br>";
print "<br><br>";
print "<a href=\"javascript:navigate(1);\"><img src='icons/ArrowDown.gif' title='Shift forward 1 week' width=20></a><br><br>";
print "<a href=\"javascript:navigate(3);\"><img src='icons/Arrow2Down.gif' title='Shift forward 3 weeks' width=20></a><br><br>";

print "</td>";
for ($index = $tableStart; $index < $tableEnd; $index++) {
	for ($tdIndex = 0; $tdIndex < 7; $tdIndex++) {
		$currentKey = $datesIndex[$index];
		$temp = '';
		if ($currentKey == $dbtoday) { $temp = " style='border: 2px solid {$BGCOLORS['today']}' "; }
		if ($currentMonth != $dates[$currentKey]['month']) {
			$currentMonth = $dates[$currentKey]['month'];
			$tempMonth = "{$currentMonth} ";
		}
		$assignmentCount = count($dates[$currentKey]['assignments']);

#		print "<td id='{$currentKey}' width='{$DAY_WIDTH}' valign='top' align='center' ><font color='{$BGCOLORS['text']}'>{$tempMonth}&nbsp;{$dates[$currentKey]['date']}{$temp}{$tempCount}</font></td>";
		print "<td id='{$currentKey}' width='{$DAY_WIDTH}' valign='top' align='center' {$temp} ><div class='datecell' style='height: {$dataCellHeight}px;'>";
#	Date
#	Status is lowest of all assignments for that day
		$tempStatus = $STATUS_COMPLETE;
		foreach ($dates[$currentKey]['assignments'] as $dataArray) {
			if ($dataArray['status'] < $tempStatus) { $tempStatus = $dataArray['status']; }
		}
#	Position of Day Summary and Event Details, depends on day of the week (left or right on the page)
		$tempPosition = 'positionright';
		if ($tdIndex >3) { $tempPosition = 'positionleft'; }
#	Mouseover/out depend on assignments ($tempCount >0)
		$tempMouse = '';
		if ($assignmentCount >0) { $tempMouse = " onmouseover=\"javascript:daySummary(this,'{$tempPosition}','{$dates[$currentKey]['month']} {$dates[$currentKey]['date']}', '{$currentKey}', '{$tempStatus}');\" onmouseout='javascript:dayReset(this);'"; }
		print "<table name='calendarDate' width='80%'><tr><td colspan=2 id='datetext' align='center' {$tempMouse}><font color='{$BGCOLORS['text']}'>{$tempMonth}{$dates[$currentKey]['date']}</font></td></tr>";
#	Icons, if any
		$isEven = false;
		$temp = '';
		$tempOffset = $OFFSET;
		if ($assignmentCount == 1) { $tempOffset += 25; }
		if ($assignmentCount >0) {
			foreach ($dates[$currentKey]['assignments'] as $dataArray) {
				if ($isEven) { $tempOffset = $OFFSET +25; }
				$temp = $temp . "<td colspan='1' align='center'><img src='icons/{$dataArray['icon']}'  onclick=\"javascript:eventDetail(this, '{$dataArray['ID']}','{$dataArray['gallery']}','{$tempPosition}',{$tempOffset});\" width='{$IMG_WIDTH}' height='{$IMG_HEIGHT}' ></td>";
				if ($isEven) {
					print "<tr>{$temp}</tr>";
					$temp = '';
					$isEven = false;
				}
				if ($temp != '') { $isEven = true; }
			}
			if ($isEven) {
				$temp = str_replace("colspan='1'", "colspan='2'", $temp);
#				print "<tr>{$temp}<td></td></tr>";		
				print "<tr>{$temp}</tr>";		
			}
		}
		print "</table></div></td>";
		$tempMonth = '';
		$index++;
	}
	$index--;
#	print "<td width=50>&nbsp;</td></tr>";
#	Navigation cells/links
#	print "<td width=50>{$NAVIGATION[$rowIndex]}</td></tr>";
	print "{$NAVIGATION[$rowIndex]}</tr>";
	$rowIndex++;
}
print "</table></center>";




?>
<br><br>

      <center><font size=-2>&#169;&nbsp;2020-<script>dateyy = new Date();document.write(dateyy.getFullYear());</script> 
       Andrew Katsampes</font></center>

<div class="detail" id="detailObject">Event Details</div>

<div class="summary" id="summaryObject">Day Summary</div>

<form id="formcalendar" action="isi.schedule.calendar.php" method="post">
<?php
print "<input type='hidden' name='scheduleeditor' id='scheduleeditor' value=\"{$currentEditor}\">";
print "<input type='hidden' name='organization' id='organization' value=\"{$currentOrganization}\">";
?>
<input type='hidden' name='public' id='public' value='no'>
<input type='hidden' name='calendaradjust' id='calendaradjust' value='0'>
</form>


</body>

<script>
//console.dir(statusArray);
</script>

</html>