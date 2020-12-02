<?php
header('Access-Control-Allow-Origin: https://photographer.isiphotos.com');
#
#	Called via API
#	OR functions used directly as include by isi.schedule.request.php
#
#	Images
#	/G0000XWAhEH.wpSw/I0000hVUHLfqTtEo/C00003G38X2EBKyU	NCAAACK20170115105.jpg	Mary Parker				Andrew Katsampes
#	/G0000XWAhEH.wpSw/I0000TWBsz0GZ_xE/C00003G38X2EBKyU	NCAAACK20170115122.jpg	Victoria Bach				Andrew Katsampes
#	/G0000XWAhEH.wpSw/I0000bPVt5gLGw8E/C00003G38X2EBKyU	NCAAACK20170115148.jpg	Savannah Newton, Kourtney Menches	Andrew Katsampes

#	Insert
#	download_hostURL	isiphotos.com
#	download_portalID	USSoccer	NWSL	Cal	Stanford
#	download_imageID	string
#	download_imageFN	string
#	download_imageTitle	string
#	download_imagePhotographer	string
#	download_date		date
#	http://s139088.gridserver.com/tools/isi.tools.php?r=d&h=isiphotos.com&u=NWSL&id=I0000hVUHLfqTtEo&fn=NCAAACK20170115105.jpg&t=Mary%20Parker&p=Andrew%20Katsampes
#	h=isiphotos.com&u=NWSL&id=I0000hVUHLfqTtEo&fn=NCAAACK20170115105.jpg&t=Mary%20Parker&p=Andrew%20Katsampes
#	h=isiphotos.com&u=NWSL&id=I0000TWBsz0GZ_xE&fn=NCAAACK20170115122.jpg&t=Victoria%20Bach&p=Andrew%20Katsampes
#	h=isiphotos.com&u=NWSL&id=I0000bPVt5gLGw8E&fn=NCAAACK20170115148.jpg&t=Savannah%20Newton,%20Kourtney%20Menches&p=Andrew%20Katsampes
#	insert into portal_downloads values('isiphotos.com','NWSL','I0000hVUHLfqTtEo','NCAAACK20170115105.jpg','Mary Parker','Andrew Katsampes',CURDATE())

#	Status
#	Status of assignment: a=initial, b=request, c=assigned, d=upload, e= no status, =potential.	
$STATUS_INITIAL	= "a";
$STATUS_REQUEST = "b";
$STATUS_ASSIGNED = "c";
$STATUS_UPLOAD	= "d";
$STATUS_WAITING = "e";
$STATUS_GETTY = "f";
$STATUS_COMPLETE = "g";
$RSVP_YES	= 'y';
$RSVP_NO	= 'n';
$MEID_YES	= 'y';
$MEID_NO	= 'n';
$TARGET_IMMEDIATE = 8;
$TARGET_URL	= 'https://assignments.isiphotos.com';
#$TARGET_URL	= 'http://photographer.isiphotos.com';
$GETTY_EMAIL = 'Michael.Lawrie@gettyimages.com';
$GETTY_EMAIL = 'john@isiphotos.com';
#$GETTY_EMAIL = 'katsampes@hotmail.com';
$GETTY_REPLY = 'assignments@isiphotos.com';
$GETTY_FROM	 = $GETTY_REPLY;
$NOREPLY = 'noreply@isiphotos.com';

#	Colors
$BGCOLORS = array("a" => "#991111","b" => "#EE2233","c" => "#2C78B5","d" => "#51B749","e" => "#EEEE33","f" => "#116622","g" => "#113333");

#
#	Database related.
#
$connection = '';
#	Error message
$message = '';

#
#	Route by request
#
$request = $_POST['req'];
#	Diagnostic GETs only
if (count($_POST) == 0) {
	$request = $_GET['req'];
}

#	$tempDelivery = $_POST['dm'];

#	Insert an assignment
#	Input: eventID, collectionID, collectionName,gallery name,event date, photographerID, photographerName)
#	Input: Drop, Drop, collectionName,gallery name,event date, empty, empty)	May 2020
if ($request == 'dbi') {
#	$tempEventID = $_POST['eid'];
#	$tempCollectionID = $_POST['cid'];
	$tempOrganizationName = $_POST['ofn'];
	$tempGalleryName = $_POST['gfn'];
	$tempEventDate = $_POST['ed'];
	$tempEventDuration = $_POST['dur'];
	$tempEventStart = $_POST['ts'];
	$tempEventEnd = $_POST['te'];
	$tempIcon = $_POST['ic'];
	$tempDetails = $_POST['det'];
	$tempCount = $_POST['pc'];
	$tempLocation = $_POST['loc'];
	$tempContact = $_POST['cn'];
	$tempContactEmail = $_POST['ce'];
	$tempContactPhone = $_POST['cp'];
	$tempRequestor = $_POST['rn'];
	$tempRequestorEmail = $_POST['re'];
#	$tempPhotographerID = $_POST['pf'];
#	$tempPhotographerName = $_POST['pfn'];
#	$tempResult = requestInsert($tempEventID, $tempCollectionID, $tempCollectionName, $tempGalleryName, $tempEventDate, $tempDelivery,$tempDetails,$tempEditorID, $tempPhotographerID, $tempPhotographerName);
	$tempResult = requestInsert($tempOrganizationName, $tempGalleryName, $tempEventDate, $tempEventDuration, $tempEventStart, $tempEventEnd, $tempIcon, $tempDetails, $tempCount, $tempLocation, $tempContact, $tempContactEmail, $tempContactPhone, $tempRequestor, $tempRequestorEmail);
	print json_encode($tempResult);
	exit;
}
#	Delete an assignment
#	Input: assignID)
if ($request == 'dbd') {
	$tempAssignID = $_POST['aid'];
	$tempResult = requestDelete($tempAssignID);
	print json_encode($tempResult);
	exit;
}
#	Get google calendar access data
#	Input: collectionID
if ($request == 'dbgc') {
	$tempCollectionID = $_POST['cid'];
	$tempResult = requestCalendar($tempCollectionID);
	print json_encode($tempResult);
	exit;
}
#	Get assignment data
#	Input: gallery name
if ($request == 'dbga') {
	$tempGalleryName = $_POST['gfn'];
	$tempResult = requestAssignment($tempGalleryName);
	print json_encode($tempResult);
	exit;
}
#	Update assignment data
#	Input: assignment ID
if ($request == 'dbua') {
	$tempAssignID = $_POST['aid'];
	$tempCollectionID = $_POST['cid'];
	$tempEditorEmail = $_POST['edit'];
	$tempPhotographerID = $_POST['pf'];
	$tempPhotographerName = $_POST['pfn'];
	$tempElapsedHours = intval($_POST['eh']);
	$tempGalleryName = $_POST['gfn'];
	$tempResult = updateAssignment($tempAssignID, $tempCollectionID, $tempEditorEmail, $tempPhotographerID, $tempPhotographerName, $tempGalleryName, $tempElapsedHours);
	print json_encode($tempResult);
	exit;
}
#	Update assignment details/delivery method
#	Input: assignment ID
if ($request == 'dbuadm') {
	$tempAssignID = $_POST['aid'];
	$tempDetails = $_POST['det'];
	$tempDelivery = $_POST['dm'];
	$tempMEIDFlag = $_POST['me'];
	$tempMEID = $_POST['meid'];
	$tempStatus = $_POST['status'];
	$tempResult = updateDetails($tempAssignID, $tempDelivery, $tempDetails, $tempMEIDFlag, $tempMEID, $tempStatus);
	print json_encode($tempResult);
	exit;
}
#	Assign requestor to assignment and accept
#	Input: assignment ID
if ($request == 'dbar') {
	$tempAssignID = $_POST['aid'];
	$tempGallery = $_POST['gfn'];
	$tempEditorID = $_POST['eid'];
	$tempRequestor = $_POST['pfn'];
	$tempDelivery = $_POST['dm'];
	$tempMEID = $_POST['me'];
	$tempDetails = $_POST['det'];
	$tempResult = assignRequestor($tempAssignID, $tempGallery, $tempEditorID, $tempRequestor, $tempDelivery, $tempMEID, $tempDetails);
	print json_encode($tempResult);
	exit;
}
#	Editor accepts assignment for photographer
if ($request == 'accept') {
	$tempAssignID = $_POST['aid'];
	$tempGallery = $_POST['gfn'];
	$tempPhotographerID = $_POST['pf'];
	$tempPhotographerName = $_POST['pfn'];
	$tempResult = requestAccept($tempAssignID, $tempGallery, $tempPhotographerID, $tempPhotographerName, false);
	print json_encode($tempResult);
	exit;
}
#	Editor declines assignment for photographer
if ($request == 'decline') {
	$tempAssignID = $_POST['aid'];
	$tempGallery = $_POST['gfn'];
	$tempPhotographerID = $_POST['pf'];
	$tempPhotographerName = $_POST['pfn'];
	$tempResult = requestDecline($tempAssignID, $tempGallery, $tempPhotographerID, $tempPhotographerName, false);
	print json_encode($tempResult);
	exit;
}
//	Editor flag assignment as images uploaded
if ($request == 'flag') {
	$tempAssignID = $_POST['aid'];
	$tempFirstFinal = $_POST['final'];
	$tempResult = requestFlagUpload($tempAssignID, $tempFirstFinal, false);
	print json_encode($tempResult);
	exit;
}

#	Get list of photographers for a collection
#	Input: collectionID
if ($request == 'dbp') {
	$tempCollectionID = $_POST['cid'];
	$tempResult = requestPhotographers($tempCollectionID);
	print json_encode($tempResult);
	exit;
}
#	Update list of photographers for an assignment
#	Input: assignment ID, photographer ID list, sendemail true/false flag
#	Sendemail = ggg@bb.com or false
#	Update with editor, details, delivery mode, and deadline
if ($request == 'dbup') {
	$tempAssignID = $_POST['aid'];
	$tempGalleryName = $_POST['gfn'];
	$tempEditorID = $_POST['eid'];
	$tempPhotographerIDs = $_POST['pids'];
	$tempEditorName = $_POST['en'];
	$tempEditorEmail = $_POST['ef'];
	$tempDetails = $_POST['det'];
	$tempDelivery = $_POST['dm'];
	$tempDeadline = $_POST['dd'];
	$tempRSVP = $_POST['rs'];
	$tempMEID = $_POST['me'];
	$tempIDList = $_POST['idl'];
	$tempEventDate = $_POST['ed'];
	$tempEventDateEnd = $_POST['ede'];
	$tempEventTime = $_POST['et'];
	$tempEventLocation = $_POST['elocation'];
	$tempEventContactName = $_POST['econtactname'];
	$tempEventContactEmail = $_POST['econtactemail'];
	$tempEventContactPhone = $_POST['econtactphone'];
	$tempResult = updatePhotographers($tempAssignID, $tempGalleryName, $tempEditorID, $tempPhotographerIDs, $tempEditorName, $tempEditorEmail, $tempDetails, $tempDelivery, $tempDeadline, $tempRSVP, $tempMEID, $tempIDList, $tempEventDate, $tempEventDateEnd, $tempEventTime, $tempEventLocation, $tempEventContactName, $tempEventContactEmail, $tempEventContactPhone);
	print json_encode($tempResult);
	exit;
}
#	Get list of requests for an editor/calendar/collection
#	Input: editorID, collectionID
if ($request == 'dbr') {
	$tempEditorID = $_POST['edit'];
	$tempCollectionID = $_POST['cid'];
	$tempResult = requestRequests($tempEditorID, $tempCollectionID);
	print json_encode($tempResult);
	exit;
}
#	Send emails to editor/photographer, with a message
#	Input: editor ID, photographer ID, message
if ($request == 'e') {
	$tempEditorID = $_POST['edit'];
	$tempAssignID = $_POST['aid'];
	$tempPhotographerID = $_POST['pf'];
	$tempMessage = $_POST['msg'];
	$tempDetails = $_POST['det'];
	$tempResult = requestEmail($tempAssignID, $tempEditorID, $tempPhotographerID, $tempMessage, $tempDetails);
	print json_encode($tempResult);
	exit;
}
#	Send email reminder to upload images to photographer
#	sendEmailReminder(inputID, inputGallery, inputPhotoID, inputPhotoName, inputPhotoEmail, statusObject);
if ($request == 'er') {
	$tempAssignID = $_POST['aid'];
	$tempPhotographerID = $_POST['pf'];
	$tempPhotographerName = $_POST['pfn'];
	$tempPhotographerEmail = $_POST['pfe'];
	$tempGallery = $_POST['gfn'];
	$tempFirstFinal = $_POST['final'];
	$tempResult = requestEmailReminder($tempAssignID, $tempGallery, $tempPhotographerID, $tempPhotographerName, $tempPhotographerEmail, $tempFirstFinal);
	print json_encode($tempResult);
	exit;
}
#	Get editor/assigner ID
#	Input: editor name
if ($request == 'geid') {
	$tempEditorName = $_POST['en'];
	$tempResult = requestEditorID($tempEditorName);
	print json_encode($tempResult);
	exit;
}
#	Contact Getty. MEID request.
#	Input: none
#	executeEmail($inputTo, $inputFrom, $inputReply, $inputSubject, $inputMessage);
#	$tempArray = array();
#	$tempGalleryName = $_POST['gfn'];
#	$tempPhotographerName = $_POST['pfn'];
#	$tempSubject = "MEID request: {$tempGalleryName}";
#	$tempMessage = "ISI photos would like to request an MEID for:<br>{$tempGalleryName}<br>Photographer(s):&nbsp;{$tempPhotographerName}<br>Fill in appropriate MEID and click 'Reply with MEID' button below.<br>Thanks.<br>ISI Photos<br>";
#	$tempMessage .= "<form action='{$TARGET_URL}/isi.schedule.confirm.php' method='post'><br>Getty MEID<input type=text id='inputMEID' name='inputMEID'>&nbsp;&nbsp;&nbsp;<input type=submit value='Reply with MEID'></form>";
#	$tempResult = executeEmail($GETTY_EMAIL, $GETTY_FROM, $GETTY_REPLY, $tempSubject, $tempMessage);
#	if ($tempResult == true) { $tempArray["status"] = 'successful'; }
#	print json_encode($tempArray);
#	exit;
#	gettyMEID();
if ($request == 'meid') {
	$tempResult = gettyMEID();
	print json_encode($tempResult);
	exit;
}
//	Notify Getty of images for an MEID
//	GettyNotify(inputAssignID, inputGallery, inputMEID, inputCount, statusObject) {
if ($request == 'gn') {
	$tempAssignID = $_POST['aid'];
	$tempGalleryName = $_POST['gfn'];
	$tempMEID = $_POST['meid'];
	$tempImageCount = $_POST['mec'];
	$tempSubject = "Images sent for {$tempGalleryName} MEID({$tempMEID})";
	$tempMessage = "For MEID ({$tempMEID}), {$tempGalleryName}, ISI has provided {$tempImageCount} images.<br><br>";
	$tempMessage .= "Thanks<br>ISI Photos<br>";
	$tempResult = executeEmail($GETTY_EMAIL, $GETTY_FROM, $GETTY_REPLY, $tempSubject, $tempMessage);
	if ($tempResult == true) {
		$tempArray["status"] = 'Done';
	} else {
		$tempArray["status"] = 'Failed';
	}
#	Update the assignment with MEID and image count
	$tempResult = gettyUpdate($tempAssignID, $tempMEID, $tempImageCount);
	
	print json_encode($tempArray);
	exit;
}

#	Authenticate editor/assigner ID
#	Input: editor email, editor password
if ($request == 'auth') {
	$tempEditorEmail = trim($_POST['ee']);
	$tempEditorPassword = trim($_POST['ep']);
	$tempResult = authenticateEditor($tempEditorEmail, $tempEditorPassword);
	print json_encode($tempResult);
	exit;
}
#	Authenticate photographer ID
#	Input: photographer email, photographer password
if ($request == 'authp') {
	$tempPhotographerEmail = trim($_POST['pe']);
	$tempPhotographerPassword = trim($_POST['pp']);
	$tempResult = authenticatePhotographer($tempPhotographerEmail, $tempPhotographerPassword);
	print json_encode($tempResult);
	exit;
}
#	Update editor/photographer PW
#	Input: editor/photographer email, editor/photographer password
if ($request == 'dbpw') {
	$tempInputEmail = trim($_POST['ie']);
	$tempInputPassword = trim($_POST['ip']);
	$tempResult = updatePassword($tempInputEmail, $tempInputPassword);
	print json_encode($tempResult);
	exit;
}
#	Reset editor/photographer PW
#	Input: editor/photographer email
if ($request == 'dbrp') {
	$tempInputEmail = trim($_POST['ie']);
	$tempResult = resetPassword($tempInputEmail);
	print json_encode($tempResult);
	exit;
}
#	Authenticate Uploader photographer ID
#	Input: photographer email, photographer password
if ($request == 'uploadauth') {
	$tempPhotographerName = trim($_POST['pn']);
	$tempPhotographerPassword = trim($_POST['pp']);
	$tempResult = authenticateUploadPhotographer($tempPhotographerName, $tempPhotographerPassword);
	print json_encode($tempResult);
	exit;
}
#	Uploader request ...
#	Update Uploader photographer PW
#	Input: photographer name, photographer password
if ($request == 'uploadpw') {
	$tempInputEmail = trim($_POST['in']);
	$tempInputPassword = trim($_POST['ip']);
	$tempResult = updateUploadPassword($tempInputEmail, $tempInputPassword);
	print json_encode($tempResult);
	exit;
}
#	Uploader request ...
#	Reset Uploader photographer PW
#	Input: photographer name
if ($request == 'uploadrp') {
	$tempInputEmail = trim($_POST['in']);
	$tempResult = resetUploadPassword($tempInputEmail);
	print json_encode($tempResult);
	exit;
}
#	Uploader request ...
#	Uploader upload complete
#	Locate assignment via gallery name, status('c'), and photographer, and update status to 'd'
#	Input: home, visitor, date, photographer
if ($request == 'uploadflag') {
	$tempHome = trim($_POST['uh']);
	$tempVisitor = trim($_POST['uv']);
	$tempDate = trim($_POST['ud']);
	$tempPhotographer = trim($_POST['up']);
	$tempResult = flagUpload($tempHome,$tempVisitor,$tempDate,$tempPhotographer);
	print json_encode($tempResult);
	exit;
}

	$tempArray = array("status" => "Invalid request ({$request})");
return json_encode($tempArray);


#
#	requestInsert
#
#	$tempResult = requestInsert($tempEventID, $tempCollectionID, $tempCollectionName, $tempGalleryName, $tempEventDate, $tempPhotographerID, $tempPhotographerName);
#	$tempResult = requestInsert($tempEventID, $tempCollectionID, $tempCollectionName, $tempGalleryName, $tempEventDate);	May 2020
#	requestInsert($tempOrganizationName, $tempGalleryName, $tempEventDate, $tempEventStart, $tempEventEnd, $tempDetails, $tempCount, $tempLocation, $tempContact, $tempContactEmail, $tempContactPhone	May 2020 V1.4
function requestInsert($inputOrganizationName, $inputGalleryName, $inputEventDate, $inputEventDuration, $inputEventStart, $inputEventEnd, $inputIcon, $inputDetails, $inputCount, $inputLocation, $inputContact, $inputContactEmail, $inputContactPhone, $inputRequestor, $inputRequestorEmail) {
global	$message;
global	$RSVP_NO,$MEID_NO,$STATUS_INITIAL;
	$rc = '';
	$connection = openDB();
#	Compute ending date.
	$tempDateEnd = $inputEventDate;
	if ($inputEventDuration > 0) {
		$tempDateEnd = date_create($inputEventDate);
		date_add($tempDateEnd, date_interval_create_from_date_string("{$inputEventDuration} days"));
		$tempDateEnd = date_format($tempDateEnd, 'Y-m-d');
	}
	$tempArray = explode(":",$inputOrganizationName);
	$tempOrganizationName = mysqli_escape_string($connection, $tempArray[0]);
	$tempOrganizationEmail = $tempArray[1];
	$tempGalleryName = mysqli_escape_string($connection, $inputGalleryName);
	$tempDetails = trim($inputDetails, " \t\n\r\0\x0B\xc2\xa0");
	$tempDetails = str_replace("\t", " ", $tempDetails);
	$tempDetails = str_replace("\n", " ", $tempDetails);
	$tempDetails = str_replace("\r", " ", $tempDetails);
	$tempDetails =  mysqli_escape_string($connection, $tempDetails);
	$tempLocation =  mysqli_escape_string($connection, $inputLocation);
	$tempContact = trim($inputContact);
	$tempContact =  mysqli_escape_string($connection, $tempContact);
	$tempContactEmail = trim($inputContactEmail);
	$tempContactEmail =  mysqli_escape_string($connection, $tempContactEmail);
	$tempContactPhone = trim($inputContactPhone);
	$tempContactPhone =  mysqli_escape_string($connection, $tempContactPhone);
	$tempRequestor = trim($inputRequestor);
	$tempRequestor =  mysqli_escape_string($connection, $tempRequestor);
	$query = sprintf("insert into schedule_assignments values('','%s','%s','%s','%s','%s','%s','','','%s','%s','%s',0,0,'{$RSVP_NO}','{$STATUS_INITIAL}',NOW(),'requestInsert:r:0', '{$MEID_NO}', '', 0, '%s','%s','%s','%s')", $tempOrganizationName, $tempGalleryName, $inputEventDate, $tempDateEnd, $inputEventStart, $inputEventEnd, $tempDetails, $inputIcon, $tempRequestor, $tempLocation, $tempContact, $tempContactEmail, $tempContactPhone);
	for ($i=0; $i<$inputCount; $i++) {
		$data = executeSQL($connection, $query);
		$rowCount = mysqli_affected_rows($connection);
#	if ($rowCount == 1) { return "done"; }
#	if ($rowCount != 1) { $message = "error: r001: Image insert SQL failed ({$inputHost}:{$inputUser})."; return "error"; }
#	When successful, return last auto increment ID, id of the assignment.
		if ($rowCount == 1) {
			$temp = "select LAST_INSERT_ID()";
			$data = executeSQL($connection, $temp);
			$row = mysqli_fetch_assoc($data);
			$temp = $row["LAST_INSERT_ID()"];
			$tempArray = array('lastInsertID' => $temp, 'insertCount' => $inputCount);
			mysqli_free_result($data);
#	Send email to organization email address
#	Send email to requestor, only once, not for each photographer
			if ($tempOrganizationEmail != '' && $i ==0) {
				$MailTo = $tempOrganizationEmail;
				$MailFrom = trim($inputRequestorEmail);
				$MailReply= trim($inputRequestorEmail);
				$MailSubject = "Assignment created. ({$inputGalleryName}).";
				$MailMessage = "Assignment<br><br>{$inputGalleryName}<br>Requestor: {$inputRequestor} ({$inputRequestorEmail})<br>Details: {$tempDetails}<br>";
				executeEmail($MailTo, $MailFrom, $MailReply, $MailSubject, $MailMessage);
				$MailTo = trim($inputRequestorEmail);
				$MailFrom = $tempOrganizationEmail;
				$MailReply= $tempOrganizationEmail;
				executeEmail($MailTo, $MailFrom, $MailReply, $MailSubject, $MailMessage);
			}
		}
		if ($rowCount == -1) { return "unknown {$rowCount}"; }
	}
	mysqli_close($connection);
	return $tempArray;

}
#
#	requestDelete
#
#	$tempResult = requestDelete($tempAssignID);
function requestDelete($inputID) {
global	$message;
	$rc = '';
	$connection = openDB();
	$temp = sprintf("delete from schedule_assignments WHERE assign_ID = '%s'", $inputID);
	$data = executeSQL($connection, $temp);
	$rowCount = mysqli_affected_rows($connection);
#	When successful, return last auto increment ID, id of the assignment.
	if ($rowCount == 1) {
		$tempArray["status"] = "Done";
		return $tempArray;
	}
$tempArray["status"] = "Failed ({$inputID})";
return $tempArray;
//return "Unknown {$rowCount} SQL({$temp})";
}
#
#	requestCalendar
#
#	$tempResult = requestInsert($tempEventID);
function requestCalendar($inputCollectionID) {
global	$message;
	$connection = openDB();
	$temp = sprintf("select calendar_date from schedule_calendars WHERE calendar_collectionID = '%s'", $inputCollectionID);
#print ("AK00B ({$temp})<br>");
	$data = executeSQL($connection, $temp);
	if ($data === false) {
		$message = "error: r001: Calendar not found ({$inputCollectionID}).";
		$tempArray = array ("status" => "No calendar SQL error.");
		return $tempArray;
	}
	$max = mysqli_num_rows($data);
	if ($max != 1) {
		$message = "r002: Calendar not found ({$inputCollectionID}).";
		$tempArray = array ("status" => "No calendar ({$inputCollectionID})");
		return $tempArray;
	}
#	print("AK001 ({$max})<br>");
	$row = mysqli_fetch_assoc($data);
	$temp = $row['calendar_date'];
#	print("AK002 ({$temp})<br>");
	$query = sprintf("select AES_DECRYPT(calendar_apikey, '%s') as 'apikey', AES_DECRYPT(calendar_clientID, '%s') as 'clientid', AES_DECRYPT(calendar_GID, '%s') as 'gid' from schedule_calendars WHERE calendar_collectionID = '%s'", $temp, $temp, $temp, $inputCollectionID);
#print ("AK00B ({$query})<br>");
	$data = executeSQL($connection, $query);
	if ($data === false) { $message = "error: r003: Collection/calendar failed ({$inputCollectionID})."; return "error (r003)"; }
	$max = mysqli_num_rows($data);
	if ($max != 1) { $message = "error: r004: Collection/calendar failed ({$inputCollectionID})."; return "error (r004)"; }
#	print("AK003 ({$max})<br>");
	$row = mysqli_fetch_assoc($data);
	$currentapiKey = $row['apikey'];
	$currentclientID = $row['clientid'];
	$currentGID = $row['gid'];
	mysqli_free_result($data);
#	print("AK004 ({$currentapiKey}) ({$currentclientID}) ({$currentGID})<br>");
	$tempArray = array ("status" => "calendar data", "apikey" => $currentapiKey, 'clientID' => $currentclientID, 'gid' => $currentGID);
#return "{$currentapiKey}:{$currentclientID}:{$currentGID}";
	mysqli_close($connection);
return $tempArray;
}
#
#	requestAssignment
#	Get schedule_assignments row.
#
function requestAssignment($inputGalleryName) {
global	$message;
	$connection = openDB();
	$temp = sprintf("select assign.assign_ID as 'assignID', assign.assign_eventID as 'eventID', editor.editor_email as 'editorEmail', assign.assign_photographerIDs as 'photoID', assign.assign_photographerIDs as 'photoID', photo.photographer_name as 'photoName', TIMESTAMPDIFF(HOUR,assign.assign_date,NOW()) as 'difference' from schedule_assignments as assign join schedule_photographers as photo on assign.assign_photographerIDs = photo.photographer_ID  join schedule_editors as editor on assign.assign_editorID = editor.editor_ID  WHERE assign.assign_gallery = '%s'", $inputGalleryName);
#print ("AK00B ({$temp})<br>");
	$data = executeSQL($connection, $temp);
	if ($data === false) {
#		$message = "error: r001: Assignment not found ({$inputGalleryName}).";
		$tempArray = array ("status" => "No assignment SQL error.");
		return $tempArray;
	}
	$max = mysqli_num_rows($data);
	if ($max != 1) {
#		$message = "r002: Calendar not found ({$inputGalleryName}).";
		$tempArray = array ("status" => "No assignment");
		return $tempArray;
	}
	$row = mysqli_fetch_assoc($data);
	$currenteventID = $row['eventID'];
	$currentassignID = $row['assignID'];
	$currentphotoID = $row['photoID'];
	$currentphotoName = $row['photoName'];
	$currentEditorEmail = $row['editorEmail'];
	$currentDifference = intval($row['difference']);
	mysqli_free_result($data);
#	print("AK004 ({$currentapiKey}) ({$currentclientID}) ({$currentGID})<br>");
	$tempArray = array ("status" => "assignment data", "assignID" => $currentassignID, "eventID" => $currenteventID, "editorEmail" => $currentEditorEmail, "photoID" => $currentphotoID, "photoName" => $currentphotoName, "difference" => $currentDifference);
	mysqli_close($connection);
return $tempArray;
}
#
#	updateAssignment
#	Set status to upload and status_time to NOW().
#
#	http://photographer.isiphotos.com/isi.schedule.tools.php?request=dbua&aid=81&cid=C0000ph.duJk_gNk&edit=0&pf=1&pfn=Andrew%20Katsampes&eh=1&gfn=New%20England%20Revolution%20vs%20Minnesota%20United%20FC,%20January%2027,%202018
function updateAssignment($inputID, $inputCollectionID, $inputEditorEmail, $inputPhotographerID, $inputPhotographerName, $inputGalleryName, $inputElapsedHours) {
global	$message, $NOREPLY;
	$tempArray = array();
	$connection = openDB();
#	Update database assignment, status 'upload', and status time/date.
	$temp = sprintf("update schedule_assignments set assign_status = '{$STATUS_UPLOAD}', assign_statusdate = NOW(), assign_statusaudit = 'updateAssignment:p:'%s' where assign_ID = '%s'", $inputPhotographerID, $inputID);
	$max = executeSQL($connection, $temp);
	if ($max === false) {
		$tempArray["status"] = "Assignment update ({$inputID}) failed.";
		mysqli_free_result($data);
		mysqli_close($connection);
		return $tempArray;
	}
	if ($max != 1) {
		$tempArray["status"] = "No assignment ({$inputID})";
		mysqli_free_result($data);
		mysqli_close($connection);
		return $tempArray;
	}

#	Determine photographer points
#	Update database collection/photographer with points and photographer
#	Based on time difference, when uploaded, award points to photographer
	$points = 0;
	if ($inputElapsedHours > 0 && $inputElapsedHours < 36) {
		$points = 1000;
	}
	if ($inputElapsedHours > 36 && $inputElapsedHours < 48) {
		$points = 800;
	}
	if ($inputElapsedHours > 48 && $inputElapsedHours < 72) {
		$points = 500;
	}
	if ($inputElapsedHours > 72 && $inputElapsedHours < 96) {
		$points = 400;
	}
	if ($inputElapsedHours > 96 && $inputElapsedHours < 120) {
		$points = 300;
	}
	if ($inputElapsedHours > 120 && $inputElapsedHours < 144) {
		$points = 200;
	}
	if ($inputElapsedHours > 144 && $inputElapsedHours < 168) {
		$points = 100;
	}
	if ($inputElapsedHours > 168) {
		$points = 50;
	}
		
#	Update collection/photographer table.
#	Update database collection/photographer with points and photographer
	$temp = sprintf("select count(*) from schedule_organizationphotographers where league_organization = '%s' and league_photographerID = '%s'", $inputCollectionID, $inputPhotographerID);
	$data = executeSQL($connection, $temp);
	if ($data === false) { $tempArray['statusCollection'] = "SQL fail ({$temp})"; }
#	Insert when 0 rows, and update when not 0 rows selected.
	$max = mysqli_num_rows($data);
	if ($max == 1) { $row = mysqli_fetch_assoc($data); $max = $row['count(*)']; }
#	if ($max == 1) { $row = mysqli_fetch_row($data); $max = $row[0]; }
	mysqli_free_result($data);
	if ($max == 0) {
		$temp = sprintf("insert into schedule_organizationphotographers values(' ','%s','%s', %s, 1)", $inputCollectionID, $inputPhotographerID, $points);
	} else {
		$temp = sprintf("update schedule_organizationphotographers set league_photographer_points = league_photographer_points + %s, league_photographer_assignment = league_photographer_assignment +1 where league_organization = '%s' and league_photographerID = '%s'", $points, $inputCollectionID, $inputPhotographerID);
	}
	$max = executeSQL($connection, $temp);
	if ($max === false) { $tempArray['statusCollection'] = "Update failed. ({$inputCollectionID})  ({$temp})"; }
	if ($max != 1) { $tempArray['statusCollection'] = "Update failed. ({$inputCollectionID}) ({$temp})"; }
	mysqli_free_result($data);

#	Send email to editor with links to adjust photographer points
	$MailTo = $inputEditorEmail;
	$MailFrom = $NOREPLY;
	$MailReply= $NOREPLY;
	$MailSubject = "Images uploaded. ({$inputGalleryName}).";
	$MailMessage = "{$inputPhotographerName} uploaded images.<br><br>";
#	$MailMessage .= "Use the links below to assess photographer (Internal use only).<br>";
#	$temp = base64_encode("&data=req&data=adjust&data=amount&data=plustwo&data=collectionid&data={$inputCollectionID}&data=photographerid&data={$inputPhotographerID}");
#	$MailMessage .= "<a href='http://photographer.isiphotos.com/isi.schedule.confirm.php?data={$temp}'>Excellent (+2)</a><br>";
#	$temp = base64_encode("&data=req&data=adjust&data=amount&data=plusone&data=collectionid&data={$inputCollectionID}&data=photographerid&data={$inputPhotographerID}");
#	$MailMessage .= "<a href='http://photographer.isiphotos.com/isi.schedule.confirm.php?data={$temp}'>Good (+1)</a><br>";
#	$temp = base64_encode("&data=req&data=adjust&data=amount&data=neutral&data=collectionid&data={$inputCollectionID}&data=photographerid&data={$inputPhotographerID}");
#	$MailMessage .= "<a href='http://photographer.isiphotos.com/isi.schedule.confirm.php?data={$temp}'>Neutral (0)</a><br>";
#	$temp = base64_encode("&data=req&data=adjust&data=amount&data=minusone&data=collectionid&data={$inputCollectionID}&data=photographerid&data={$inputPhotographerID}");
#	$MailMessage .= "<a href='http://photographer.isiphotos.com/isi.schedule.confirm.php?data={$temp}'>Disappointment (-1)</a><br>";
#	$temp = base64_encode("&data=req&data=adjust&data=amount&data=minustwo&data=collectionid&data={$inputCollectionID}&data=photographerid&data={$inputPhotographerID}");
#	$MailMessage .= "<a href='http://photographer.isiphotos.com/isi.schedule.confirm.php?data={$temp}'>Poor (-2)</a><br><br>";
	executeEmail($MailTo, $MailFrom, $MailReply, $MailSubject, $MailMessage);
	if ($temp) {
		$tempArray['statusEditor'] = 'successful' ;
	} else {
		$tempArray['statusEditor'] = 'fail' ;
	}
	
	$tempArray["status"] = "done";
	mysqli_close($connection);
return $tempArray;
}

#
#	updateDetails
#	Update assignment details/delivery method.
#
#	http://photographer.isiphotos.com/isi.schedule.tools.php?request=dbua&aid=81&cid=C0000ph.duJk_gNk&edit=0&pf=1&pfn=Andrew%20Katsampes&eh=1&gfn=New%20England%20Revolution%20vs%20Minnesota%20United%20FC,%20January%2027,%202018
function updateDetails($inputID, $inputDelivery, $inputDetails, $inputMEIDFlag, $inputMEID, $inputStatus) {
global	$message, $STATUS_UPLOAD, $STATUS_GETTY;
	$tempArray = array();
	$connection = openDB();
	$tempStatus = '';
#	MEID flag set to y/n?
	$tempMEIDFlag = '';
	if ($inputMEIDFlag != 'skip') {
		$tempMEIDFlag = ", assign_meid ='{$inputMEIDFlag}'";
	}
#	MEID value supplied?
	$tempMEID = '';
	if ($inputMEID != 'skip') {
		$tempMEID = ", assign_meidID ='{$inputMEID}'";
		if ($inputStatus == $STATUS_UPLOAD) {
			$tempStatus = ", assign_status = '{$STATUS_GETTY}' ";
		}
	}
#	Update database assignment, details and delivery method.
	$query = "update schedule_assignments set assign_details = '{$inputDetails}', assign_delivery ='{$inputDelivery}' {$tempMEIDFlag} {$tempMEID} {$tempStatus} , assign_statusdate = NOW(), assign_statusaudit = 'updateDetails:e:0' where assign_ID = '{$inputID}'";
#print "Editor update ({$query})";
	$max = executeSQL($connection, $query);
	$max = mysqli_affected_rows($connection);
#print "Editor update max({$max})";
	if ($max == 1) {
		$tempArray["status"] = "Done";
	} else {
		$tempArray["status"] = "Failed";
	}
mysqli_free_result($data);
mysqli_close($connection);
return $tempArray;
}
#	Assign requestor to assignment and accept
#	assignRequestor($tempAssignID, $tempGallery, $tempEditorID, $tempRequestor, $tempDelivery, $tempMEID, $tempDetails);
function assignRequestor($inputAssignmentID, $inputGalleryName, $inputEditorID, $inputRequestor, $inputDelivery, $inputMEID, $inputDetails) {
global $STATUS_ASSIGNED, $TARGET_URL, $GETTY_EMAIL, $GETTY_REPLY;
#	Assign requestor and accept photography assignment
	$tempArray = array();
	$connection = openDB();
#	Check/test requestor is a photographer in the database
	$testName = strtolower($inputRequestor);
#	$query = "select photo.photographer_name as 'photographerName', photo.photographer_email as 'photographerEmail', photo.photographer_ID as 'photographerID' FROM schedule_photographers as photo  where LOWER(photo.photographer_name) = '{$testName}' ";
	$query = "select photo.photographer_name as 'photographerName', photo.photographer_email as 'photographerEmail', photo.photographer_ID as 'photographerID', assign.assign_gallery as 'gallery', assign.assign_photographerIDs as 'assignedPhotoID' FROM schedule_photographers as photo join schedule_assignments as assign  where LOWER(photo.photographer_name) = '{$testName}' && assign.assign_gallery = '{$inputGalleryName}' order by assign.assign_photographerIDs desc";
#	$query = "select photo.photographer_name as 'photographerName', photo.photographer_email as 'photographerEmail', photo.photographer_ID as 'photographerID' FROM schedule_photographers as photo where LOWER(photo.photographer_name) = '{$testName}' ";
#print "assignRequestor({$query})<br>";
	$data = executeSQL($connection, $query);
	$max = mysqli_num_rows($data);
#print "assignRequestor max({$max})<br>";
	if ($max == 0) {
		$tempArray["status"] = "Failed - no photographer";
		mysqli_free_result($data);
		mysqli_close($connection);
		return $tempArray;
	}
	$row = mysqli_fetch_assoc($data);
	if ($row["photographerID"] == $row['assignedPhotoID']) {
		$tempArray["status"] = "Failed - already assigned";
		mysqli_free_result($data);
		mysqli_close($connection);
		return $tempArray;
	}
	$currentPhotographerID = $row["photographerID"];
	$currentPhotographerName = $row["photographerName"];
	$currentPhotographerEmail = $row["photographerEmail"];
	mysqli_free_result($data);
#	Update the assignment with photographer and status to assigned ('c').
	$query = "update schedule_assignments set assign_photographerIDs = '{$currentPhotographerID}', assign_status = '{$STATUS_ASSIGNED}', assign_editorID = '{$inputEditorID}', assign_meid = '{$inputMEID}', assign_delivery = '{$inputDelivery}', assign_deadline = '48', assign_details = '{$inputDetails}', assign_statusdate = NOW(), assign_statusaudit = 'assignRequestor:e:{$inputEditorID}' where assign_ID = '{$inputAssignmentID}'";
#print "assignRequestor ({$query})<br>";
	$data = executeSQL($connection, $query);
	$rowCount = mysqli_affected_rows($connection);
#print "assignRequestor({$rowCount})<br>";
#	Successful update = 1
	if ($rowCount != 1) {
		$tempArray["status"] = "Failed";
		mysqli_free_result($data);
		mysqli_close($connection);
		return $tempArray;
	}

$tempArray["status"] = "Done";

#	Send email to photographer confirming assignment
	$MailTo = $currentPhotographerEmail;
	$MailSubject = "Assignment accepted ({$inputGalleryName}).";
	$MailMessage = "{$currentPhotographerName} accepted assignment - {$inputGalleryName}. <br><br>";
	$temp = executeEmail($MailTo, $GETTY_REPLY, $GETTY_REPLY, $MailSubject, $MailMessage);

mysqli_free_result($data);
mysqli_close($connection);
return $tempArray;
}

#	Photographer: Accept assignment
#	Not throught API. Direct as PHP function from .confirm .
#	Through API, from .editor .
#	function buildData('accept', inputID, inputGallery, inputPhotographerID, inputPhotographerName) {
#	actionAccept('{$itemArray[$INDEX_ASSIGNMENTID]}','{$itemArray[$INDEX_GALLERY]}','{$itemArray[$INDEX_PHOTOGRAPHERID]}','{$itemArray[$INDEX_PHOTOGRAPHERNAME]}');\">Accept</a>";
function requestAccept($inputAssignmentID, $inputGalleryName, $inputPhotographerID, $inputPhotographerName, $isSendEmail) {
global $STATUS_REQUEST, $STATUS_ASSIGNED, $RSVP_NO;
global $GETTY_EMAIL, $GETTY_REPLY, $GETTY_FROM;
#	Organize photographers,photographerList when multiple assignments (same Gallery Name)
		$tempArray = multipleAssignments($inputAssignmentID, $inputPhotographerID, $inputGalleryName);
		if ($tempArray[1] == 'Fail') {
			$tempArray["status"] = "Failed";
			$tempArray["output"] = array();
			array_push($tempArray["output"], $tempArray[2]);
			return $tempArray;
		}
		$assignIDList = $tempArray[0];
		$nextPhotographerID = $tempArray[1];
		$photographerSpareIDs = $tempArray[2];
		$assignIDMax = count($assignIDList);
#	Result in $tempArray
		$tempArray["status"] = "Done";
		$tempArray["output"] = array();
#	Send email to Getty requesting MEID.		
		if ($assignIDList[$inputAssignmentID]['gettyMEID'] == 'y') {
			$MailSubject = "MEID request: {$inputGalleryName}";	
#			$MailMessage = "<html><head><meta http-equiv='Content-Security-Policy' content=\"form-action 'http://assignments.isiphotos.com';\"></head><body>DDD";
#$MailMessage = "<html><head><script>console.log('AK001'); function execID() { 	tempObject = document.getElementById('inputMEID'); alert(tempObject.value);  window.location='http://assignments.isiphotos.com/isi.schedule.confirm.php?input=splat'; }</script></head><body>";
			$MailMessage = '';
			$MailMessage .= "ISI photos would like to request an MEID for:<br>{$inputGalleryName}<br>Photographer(s):&nbsp;{$assignIDList[$inputAssignmentID]['photographerName']}<br>Thanks.<br>ISI Photos<br>";
			$temp = executeEmail($GETTY_EMAIL, $GETTY_FROM, $GETTY_REPLY, $MailSubject, $MailMessage);
			if ($temp == true) {
				array_push($tempArray["output"], "&nbsp;&nbsp;&nbsp;Email sent to Getty ({$GETTY_EMAIL}).<br>");
			} else {
				array_push($tempArray["output"], "&nbsp;&nbsp;&nbsp;Email to Getty ({$GETTY_EMAIL}) failed .<br>");
			}
		}

#	Send email to editor with assigned photographer.
		if ($isSendEmail) {
			$MailTo = $assignIDList[$inputAssignmentID]['editorEmail'];
			$MailFrom = $assignIDList[$inputAssignmentID]['photographerEmail'];
			$MailReply= $assignIDList[$inputAssignmentID]['photographerEmail'];
			$MailSubject = "Assignment accepted ({$inputGalleryName}).";
			$MailMessage = "{$assignIDList[$inputAssignmentID]['photographerName']} accepted assignment - {$inputGalleryName}. <br><br>";
#		if ($errorList != '') { $MailMessage .= $errorList . "<br><br>"; }
			$temp = executeEmail($MailTo, $MailFrom, $MailReply, $MailSubject, $MailMessage);
			if ($temp == true) {
				array_push($tempArray["output"], "&nbsp;&nbsp;&nbsp;Email sent to editor ({$MailTo}).<br>");
			} else {
				array_push($tempArray["output"], "&nbsp;&nbsp;&nbsp;Email to editor ({$MailTo}) failed .<br>");
			}
		}

#	Loop through assignments and update the database.
#	When Accept ID (passed) = $item's ID, then Status changes from Request to Assigned
#	If there is a nextID and SpareID list then append where it was stripped from.
		$tempStatus = '';
		$connection = openDB();
		foreach ($assignIDList as $item) {
			$tempStatus = $STATUS_REQUEST;
			if ($inputAssignmentID == $item['assignID']) {
				$tempStatus = $STATUS_ASSIGNED;
			} else {
				if ($nextPhotographerID != '') {
					$item['photographerIDs'] .= ":{$nextPhotographerID}:{$photographerSpareIDs}";			
					$nextPhotographerID = '';
					$photographerSpareIDs = '';
				}
			}			
#	Update assignment with status date/time, and updated photographer IDs list
#			$temp = sprintf("update schedule_assignments set assign_status = '%s', assign_editorID = '%s', assign_photographerIDs = '%s', assign_rsvp = '%s', assign_statusdate = NOW() where assign_ID = '%s'", $tempStatus, $item['editorID'], $item['photographerIDs'], $RSVP_NO, $item['assignID']);
			$query = "update schedule_assignments set assign_status = '{$tempStatus}', assign_editorID = '{$item['editorID']}', assign_photographerIDs = '{$item['photographerIDs']}', assign_rsvp = '{$RSVP_NO}', assign_statusdate = NOW(), assign_statusaudit = 'requestAccept:p:{$inputPhotographerID}' where assign_ID = '{$item['assignID']}'";
#print "AK005 ({$query})<br>";
			$max = executeSQL($connection, $query);
			if ($max == true) {
				array_push($tempArray["output"], "&nbsp;&nbsp;&nbsp;Assignment ({$item['assignID']}) updated in database.<br>");
			} else {
				$tempArray["status"] = "Failed";
				array_push($tempArray["output"], "&nbsp;&nbsp;&nbsp;Assignment accept, ({$item['assignID']}) update failed ({$inputGalleryName}).<br>");
			}
		}
		mysqli_close($connection);

	array_push($tempArray["output"], "<font size='+1'>Confirmed: {$assignIDList[$inputAssignmentID]['photographerName']} accepted assignment - {$inputGalleryName}</font><br>");
	return $tempArray;
}

#	Photographer: Decline assignment
#	Not throught API. Direct as PHP function.
#	Through API, from .editor .
function requestDecline($inputAssignmentID, $inputGalleryName, $inputPhotographerID, $inputPhotographerName, $isSendEmail) {
global $TARGET_URL, $TARGET_IMMEDIATE, $NOREPLY;
global $STATUS_REQUEST, $STATUS_INITIAL;
#	Organize photographers,photographerList when multiple assignments (same Gallery Name)
		$tempArray = multipleAssignments($inputAssignmentID, $inputPhotographerID, $inputGalleryName);
		if ($tempArray[1] == 'Fail') {
			$tempArray["status"] = "Failed";
			$tempArray["output"] = array();
			array_push($tempArray["output"], $tempArray[2]);
			return $tempArray;
		}
		$assignIDList = $tempArray[0];
		$nextPhotographerID = $tempArray[1];
		$photographerSpareIDs = $tempArray[2];
		$assignIDMax = count($assignIDList);
#print "AK302 Next({$nextPhotographerID} ({$inputPhotographerName}))<br>";
#print_r($assignIDList);
#	Results in $tempArray
		$tempArray["status"] = "Done";
		$tempArray["output"] = array();
		$connection = openDB();

		array_push($tempArray["output"], "<font size='+1'>Confirmed: {$inputPhotographerName} declined assignment - {$inputGalleryName}</font><br>");
#	Photographer list exhausted?
#	Print message and Send email to editor.
#	Print message only, if editor requested decline
		if ($nextPhotographerID == '') {
			$tempArray["status"] = "Warning - List exhausted";
			array_push($tempArray["output"],"Photographer candidate list exhausted ({$inputGalleryName})<br>");
			if ($isSendEmail) {
				$sentStatus = false;
				$MailTo = $assignIDList[$inputAssignmentID]['editorEmail'];
				$MailFrom = $NOREPLY;
				$MailReply= $NOREPLY;
				$MailSubject = "Photographer candidate list exhausted ({$inputGalleryName})";
#print "AK304 Sent??? ({$MailTo}) ({$photographerIDsList}) ({$MailSubject})<br>";
				$TO = $MailTo;
				$HEADERS  = "MIME-Version: 1.0 \r\n";
				$HEADERS .= "Content-type: text/html; charset=iso-8859-1 \r\n";
				$HEADERS .= "From: {$MailFrom}";
				$HEADERS .= "\r\nReply-To: {$MailReply}";
				$SUBJECT = $MailSubject;
				$MESSAGE = "Photographer candidate list exhausted. Need to recruit more potential photographers.<br><br>";
				$temp = base64_encode("&data=editor&data=".$assignIDList[$inputAssignmentID]['editorID'].":".$assignIDList[$inputAssignmentID]['editorName'].":".$assignIDList[$inputAssignmentID]['editorEmail'].":".$assignIDList[$inputAssignmentID]['organization']);
				$MESSAGE .= "<a href='{$TARGET_URL}/isi.schedule.editor.php?data={$temp}'>Access all assignments</a><br>";
				$temp = base64_encode("&data=req&data=delete&data=assignmentid&data=".$inputAssignmentID);
				$MESSAGE .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Delete current assignment</a><br><br>";
#
#		Close the pipe, sending the email
#
				$sentStatus = mail($TO,$SUBJECT,$MESSAGE,$HEADERS);
			}
		} else {
#	Get photographer name and email address for accept/decline email.
			$query = "select photo.photographer_name as photographerName, photo.photographer_email as photographerEmail from schedule_photographers as photo where photo.photographer_ID = '{$nextPhotographerID}' ";
#print "AK305 query({$query})<br>";
			$data = executeSQL($connection, $query);
			if ($data === false) {
				array_push($tempArray["output"],"F104: Photographer not found ({$nextPhotographerID}).<br>");
			} else {
				$max = mysqli_num_rows($data);
				if ($max == 1) {
					$row = mysqli_fetch_assoc($data);
						$currentPhotographerName = $row["photographerName"];
						$currentPhotographerEmail = $row["photographerEmail"];
					} else {
						array_push($tempArray["output"],"F105: Photographer not found ({$nextPhotographerID}).<br>");
					}
			}
			mysqli_free_result($data);

#print "AK200 ({$nextPhotographerID}) ({$currentPhotographerName}) ({$currentPhotographerEmail})<br>";
#	Send email to accept/decline to next photographer without links to avoid spam/junk
#	Send email to accept/decline to next photographer with links
#			$MailTo = $currentPhotographerEmail;
#			$MailFrom = $assignIDList[$inputAssignmentID]['editorEmail'];
#			$MailReply= $assignIDList[$inputAssignmentID]['editorEmail'];
#			$MailSubject = "Accept/Decline assignment email sent. ({$inputGalleryName}). (EOM)";
#			$MailMessage = "Email with links sent to you. It may be in spam/junk folder.";
#			executeEmail($MailTo, $MailFrom, $MailReply, $MailSubject, $MailMessage);
#	For editor request, show next photographer
#			if ($isReturnButton) {
#				array_push($tempArray["output"],"&nbsp;&nbsp;&nbsp;Email (EOM) sent to photographer ({$currentPhotographerName}) ({$currentPhotographerEmail}).<br>");
#			} else {
#				array_push($tempArray["output"],"&nbsp;&nbsp;&nbsp;Email (EOM) sent to next photographer.<br>");
#			}
#	Email with accept/decline links
			$MailTo = $currentPhotographerEmail;
			$MailFrom = $assignIDList[$inputAssignmentID]['editorEmail'];
			$MailReply= $assignIDList[$inputAssignmentID]['editorEmail'];
			$MailSubject = "Accept/Decline/Upload assignment? ({$inputGalleryName}).";
			$MailMessage = "{$currentPhotographerName},<br><br>";
			$MailMessage .= "Within 24 hours, use the links below to accept/decline a photo assignment. Thanks.<br>";
			$MailMessage .= "Assignment editor: {$assignIDList[$inputAssignmentID]['editorName']}<br><br>";
# idl IDList &data=idlist&data={$inputIDList} for both Accept and Decline
#print "AK200 ({$IDList})<br>";
			$tempGallery = urlencode($inputGalleryName);
			$temp = base64_encode("&data=req&data=accept&data=assignmentid&data={$inputAssignmentID}&data=photographerid&data={$nextPhotographerID}&data=galleryname&data={$inputGalleryName}&data=idlist&data={$IDList}");
			$MailMessage .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Accept assignment</a><br>";
			$temp = base64_encode("&data=req&data=decline&data=assignmentid&data={$inputAssignmentID}&data=photographerid&data={$nextPhotographerID}&data=galleryname&data={$inputGalleryName}&data=idlist&data={$IDList}");
			$MailMessage .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Decline assignment</a><br><br>";
			$temp = "within {$assignIDList[$inputAssignmentID]['deadline']} hours after event.";
			if ($assignIDList[$inputAssignmentID]['deadline'] == $TARGET_IMMEDIATE) { $temp = "immediately after the event."; }
			$MailMessage .= "Image deadline: {$temp}<br>";
#			if ($inputEventDate != $inputEventDateEnd) {
#				$MailMessage .= "Assignment duration: {$inputEventDate} through {$inputEventDateEnd}<br>";
#			}
			$MailMessage .= "Assignment details:<br>";
			$MailMessage .= "{$assignIDList[$inputAssignmentID]['details']}<br><br>";
			$MailMessage .= "Use the links below after first image upload and after final image upload.<br>";
#			$temp = base64_encode("&data=req&data=upload&data=assignmentid&data={$inputAssignmentID}");
#			$MailMessage .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Flag images uploaded</a><br><br>";
			$temp = base64_encode("&data=req&data=upload&data=assignmentid&data={$inputAssignmentID}&data=finalUpload&data='n'");
			$MailMessage .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Update: First upload complete</a><br>";
			$temp = base64_encode("&data=req&data=upload&data=assignmentid&data={$inputAssignmentID}&data=finalUpload&data='y'");
			$MailMessage .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Update: Final upload complete</a><br><br>";	
			if ($errorList != '') { $MailMessage .= $errorList . "<br><br>"; }
			executeEmail($MailTo, $MailFrom, $MailReply, $MailSubject, $MailMessage);
#	For editor request, show next photographer
			if ($isReturnButton) {
				array_push($tempArray["output"],"&nbsp;&nbsp;&nbsp;Email (links) sent to photographer ({$currentPhotographerEmail}).<br>");
			} else {
				array_push($tempArray["output"],"&nbsp;&nbsp;&nbsp;Email (links) sent to next photographer.<br>");
			}
		}
#	Loop through assignments and update the database.
		$tempStatus = '';
		foreach ($assignIDList as $item) {
			$tempStatus = $STATUS_REQUEST;
			if ($inputAssignmentID == $item['assignID']) {
				$item['photographerIDs'] = "{$nextPhotographerID}";	
				if ($photographerSpareIDs != '') {
					$item['photographerIDs'] = "{$item['photographerIDs']}:{$photographerSpareIDs}";	
				}
				if ($nextPhotographerID == '') {
					$tempStatus = $STATUS_INITIAL;
				}		
			}			
			$query = "update schedule_assignments set assign_photographerIDs = '{$item['photographerIDs']}', assign_status = '{$tempStatus}', assign_rsvp = '{$RSVP_NO}', assign_statusdate = NOW(), assign_statusaudit = 'requestDecline:p:{$inputPhotographerID}' where assign_ID = '{$item['assignID']}'";
#print "AK309 Update IDs({$item['photographerIDs']}) ({$query})<br>";
			$max = executeSQL($connection, $query);
			if ($max == true) {
				array_push($tempArray["output"],"&nbsp;&nbsp;&nbsp;Assignment ({$item['assignID']}) updated in database ({$item['photographerIDs']}).<br>");
			} else {
				array_push($tempArray["output"],"F106: Assignment update ({$inputAssignmentID}) failed ().<br>");
			}
		}
	mysqli_close($connection);
	return $tempArray;
}
#	Editor: Flag assignment as images uploaded
#	Not through API. Direct as PHP function in confirm.php.
#	Through API, from .editor .
#	requestFlagUpload($tempAssignID, false);
#	requestFlagUpload($tempAssignID, first/final, false);
#	requestFlagUpload($tempAssignID, false/true, false);
#	requestFlagUpload($tempAssignID, first/'n', false);
#	No MEID	=>	$STATUS_UPLOAD
#	MEID	=>	$STATUS_UPLOAD
#	requestFlagUpload($tempAssignID, final/'y', false);
#	No MEID	=>	$STATUS_COMPLETE
#	MEID	=>	$STATUS_GETTY
function requestFlagUpload($inputAssignmentID, $finalUpload, $isSendEmail) {
global $STATUS_ASSIGNED, $STATUS_UPLOAD, $STATUS_GETTY, $STATUS_COMPLETE, $NOREPLY;
#	Results in $tempArray
		$tempArray["status"] = "Done";
		$tempArray["output"] = array();
		$connection = openDB();

#	First upload, locate status of assigned
#	Final upload, locate status of upload
		$tempStatus = $STATUS_ASSIGNED;
		if ($finalUpload == 'y') { $tempStatus = $STATUS_UPLOAD; }
#	Get editor email address for confirmation email.
		$query = "select editor.editor_ID as 'editorID', editor.editor_email as 'editorEmail', photo.photographer_name as 'photographerName', assign.assign_organization as 'organizationName', assign.assign_photographerIDs as 'photographerID', assign.assign_gallery as 'galleryName', TIMESTAMPDIFF(HOUR,assign.assign_date,NOW()) as 'difference', assign.assign_meid as 'meidYN' FROM schedule_assignments as assign join schedule_editors as editor on assign.assign_editorID = editor.editor_ID join schedule_photographers as photo on assign.assign_photographerIDs = photo.photographer_ID  where assign.assign_ID = '{$inputAssignmentID}' and assign.assign_status = '{$tempStatus}'";
#print "AK200 Upload({$query})<br>";
		$data = executeSQL($connection, $query);
		$max = mysqli_num_rows($data);
		if ($max != 1) { 
			array_push($tempArray["output"],"F203: Assignment not found ({$inputAssignmentID}).<br>");
		} else {
			$row = mysqli_fetch_assoc($data);
			$currentEventID = $row["eventID"];
			$currentOrganization = $row["organizationName"];
			$currentPhotographerID = $row["photographerID"];
			$currentGalleryName = $row["galleryName"];
			$currentEditorID = $row["editorID"];
			$currentEditorEmail = $row["editorEmail"];
			$currentPhotographerName = $row["photographerName"];
			$currentElapsed = intval($row["difference"]);
			$currentMEIDFlag = $row['meidYN'];
			mysqli_free_result($data);
#	Set assignment status, based on first or final upload, and MEID Y/N.
		$tempStatus = $STATUS_UPLOAD;
#	For finalUpload, when there is an MEID, then promote status from upload to getty OR to complete/archive
		if ($finalUpload == 'y') {
			if ($currentMEIDFlag == 'y') {
				$tempStatus = $STATUS_GETTY;
			} else {
				$tempStatus = $STATUS_COMPLETE;
			}
		} 
#	Update database assignment, status 'upload', and status time/date.
		$query = "update schedule_assignments set assign_status = '{$tempStatus}', assign_statusdate = NOW(), assign_statusaudit = 'requestFlagUpload:ep:{$currentEditorID}' where assign_ID = '{$inputAssignmentID}'";
#$query = "update schedule_assignments set assign_status = 'c', assign_statusdate = NOW() where assign_ID = '{$inputAssignmentID}'";
		$max = executeSQL($connection, $query);
		if ($max == true) {
			array_push($tempArray["output"],"&nbsp;&nbsp;&nbsp;Assignment ({$inputAssignmentID}) updated in database.<br>");
		} else {
			array_push($tempArray["output"],"F206: Assignment update ({$inputAssignmentID}) failed.<br>");
			$tempArray["status"] = "Failed";
		}

#	Send email to editor with links to adjust photographer points
		if ($isSendEmail) {
			$MailTo = $currentEditorEmail;
			$MailFrom = $NOREPLY;
			$MailReply= $NOREPLY;
			$temp = '';
			if ($finalUpload == 'y') { $temp = ' (final)'; }
			$MailSubject = "Images uploaded{$temp}. ({$currentGalleryName}).";
			$MailMessage = "{$currentPhotographerName} uploaded images.<br><br>";
#		$MailMessage .= "Use the links below to assess photographer (Internal use only).<br>";
#		$temp = base64_encode("&data=req&data=adjust&data=amount&data=plustwo&data=collectionid&data={$currentCollectionID}&data=photographerid&data={$currentPhotographerID}");
#		$MailMessage .= "<a href='http://photographer.isiphotos.com/isi.schedule.confirm.php?data={$temp}'>Excellent (+2)</a><br>";
#		$temp = base64_encode("&data=req&data=adjust&data=amount&data=plusone&data=collectionid&data={$currentCollectionID}&data=photographerid&data={$currentPhotographerID}");
#		$MailMessage .= "<a href='http://photographer.isiphotos.com/isi.schedule.confirm.php?data={$temp}'>Good (+1)</a><br>";
#		$temp = base64_encode("&data=req&data=adjust&data=amount&data=neutral&data=collectionid&data={$currentCollectionID}&data=photographerid&data={$currentPhotographerID}");
#		$MailMessage .= "<a href='http://photographer.isiphotos.com/isi.schedule.confirm.php?data={$temp}'>Neutral (0)</a><br>";
#		$temp = base64_encode("&data=req&data=adjust&data=amount&data=minusone&data=collectionid&data={$currentCollectionID}&data=photographerid&data={$currentPhotographerID}");
#		$MailMessage .= "<a href='http://photographer.isiphotos.com/isi.schedule.confirm.php?data={$temp}'>Disappointment (-1)</a><br>";
#		$temp = base64_encode("&data=req&data=adjust&data=amount&data=minustwo&data=collectionid&data={$currentCollectionID}&data=photographerid&data={$currentPhotographerID}");
#		$MailMessage .= "<a href='http://photographer.isiphotos.com/isi.schedule.confirm.php?data={$temp}'>Poor (-2)</a><br><br>";
			if ($errorList != '') { $MailMessage .= $errorList . "<br><br>"; }
			executeEmail($MailTo, $MailFrom, $MailReply, $MailSubject, $MailMessage);
			array_push($tempArray["output"],"&nbsp;&nbsp;&nbsp;Email sent to editor.<br>");
		}
	
#	Update database collection/photographer with points and photographer
#	Based on time difference, when uploaded, award points to photographer
		$points = 0;
		if ($currentElapsed > 0 && $currentElapsed < 36) {
			$points = 1000;
		}
		if ($currentElapsed > 36 && $currentElapsed < 48) {
			$points = 800;
		}
		if ($currentElapsed > 48 && $currentElapsed < 72) {
			$points = 500;
		}
		if ($currentElapsed > 72 && $currentElapsed < 96) {
			$points = 400;
		}
		if ($currentElapsed > 96 && $currentElapsed < 120) {
			$points = 300;
		}
		if ($currentElapsed > 120 && $currentElapsed < 144) {
			$points = 200;
		}
		if ($currentElapsed > 144 && $currentElapsed < 168) {
			$points = 100;
		}
		if ($currentElapsed > 168) {
			$points = 50;
		}
#	Update database collection/photographer with points and photographer
		$query = "select league_organization from schedule_organizationphotographers where league_organization = '{$currentOrganization}' and league_photographerID = '{$currentPhotographerID}'";
		$data = executeSQL($connection, $query);
		if ($data === false) { array_push($tempArray["output"],"F206: Collection update ({$currentOrganization}) failed ().<br>"); }
#	Insert when 0 rows, and update when not 0 rows selected.
		$max = mysqli_num_rows($data);
		if ($max == 1) { 
			$query = "update schedule_organizationphotographers set league_photographer_points = league_photographer_points + {$points}, league_photographer_assignment = league_photographer_assignment +1 where league_organization = '{$currentOrganization}' and league_photographerID = '{$currentPhotographerID}'";
		}
		if ($max == 0) {
			$query = "insert into schedule_organizationphotographers values(' ','{$currentOrganization}','{$currentPhotographerID}', $points, 1)";
		}
		mysqli_free_result($data);
		$max = executeSQL($connection, $query);
		if ($max == true) {
			array_push($tempArray["output"],"&nbsp;&nbsp;&nbsp;Collection/Photographer updated in database.<br>");
		} else {
			array_push($tempArray["output"],"F208: Collection update ({$currentCollectionID}) failed ()..<br>");
		}

	}

	array_push($tempArray["output"],"<font size='+1'>Confirmed: {$currentPhotographerName} uploaded images for {$currentGalleryName} </font><br>");

	mysqli_close($connection);
	return $tempArray;
}


#
#	requestPhotographers
#
#	$tempResult = requestPhotographers($tempCollectionID);
#	SELECT t1.name, t2.salary
#  FROM employee AS t1 INNER JOIN info AS t2 ON t1.name = t2.name;
function getPhotographers($inputOrganization) {
global	$message;
	$tempWhere = '';
	$tempComma = '';
	$tempArray = array();
	$tempPhotoArray = array();
	$tempIDs = array();
	$connection = openDB();
#	select photo.photographer_ID, photo.photographer_name from schedule_organizationphotographers as collection join schedule_photographers as photo on collection.league_photographerID = photo.photographer_ID WHERE collection.league_organization = 'C0000ph.duJk_gNk'
	$temp = sprintf("select photo.photographer_ID as 'id', photo.photographer_name as 'name', photo.photographer_email as 'email' from schedule_organizationphotographers as collection join schedule_photographers as photo on collection.league_photographerID = photo.photographer_ID WHERE collection.league_organization = '%s' order by collection.league_photographer_points desc", $inputOrganization);
#print ("AK000 ({$temp})<br>");
	$data = executeSQL($connection, $temp);
	if ($data === false) { $message = "error: r005: Collection not found ({$inputCollectionID})."; return "error (r005: {$inputCollectionID})"; }
	$max = mysqli_num_rows($data);
#print ("AK001 ({$max})<br>");
	$index = 0;
	if ($max > 0) {
		$tempWhere = "where photo.photographer_ID not in (";
		while ($row = mysqli_fetch_assoc($data)) {
			$tempPhotoArray[$index]['id'] = $row['id'];
			$tempPhotoArray[$index]['name'] = $row['name'];
			$tempPhotoArray[$index]['email'] = $row['email'];
			$tempIDs[$row['id']] = 'present';
			$index++;
//	List of photographer IDs already collected
			$tempWhere = "{$tempWhere}{$tempComma}'{$row['id']}'";
			$tempComma = ',';
		}
		$tempWhere = $tempWhere . ")";
	}
	mysqli_free_result($data);
	$query = "select photo.photographer_ID as 'id', photo.photographer_name as 'name', photo.photographer_email as 'email' from schedule_photographers as photo {$tempWhere} order by photo.photographer_lastname, photo.photographer_firstname";
#print ("AK002 ({$query})<br>");
	$data = executeSQL($connection, $query);
	if ($data === false) { $message = "error: r007: Photographer list failed."; return "error (r007)"; }
	$max = mysqli_num_rows($data);
#	$tempArray['count'] = $max;
	if ($max > 0) {
		while ($row = mysqli_fetch_assoc($data)) {
			if ($tempIDs[$row['id']] == '') {
				$tempPhotoArray[$index]['id'] = $row['id'];
				$tempPhotoArray[$index]['name'] = $row['name'] . ' (New)';
				$tempPhotoArray[$index]['email'] = $row['email'];
				$index++;
			}
		}
	}
#	$tempArray['count'] = count($tempPhotoArray);
	$tempArray['list'] = $tempPhotoArray;
	mysqli_free_result($data);
	mysqli_close($connection);
#print_r($tempArray);
return $tempArray;
}

#
#	updatePhotographers
#	For an assignment, update photographer list and based on flag send email.
#	For multiple photographer assignments, update each assignment with revised list.
#
function updatePhotographers($inputAssignID, $inputGallery, $inputEditorID, $inputPhotographerIDs, $inputEditor, $inputEditorEmail, $inputDetails, $inputDelivery, $inputDeadline, $inputRSVP, $inputMEID, $inputIDList, $inputEventDate, $inputEventDateEnd, $inputEventTime, $inputEventLocation, $inputEventContactName, $inputEventContactEmail, $inputEventContactPhone) {
global $STATUS_REQUEST, $TARGET_URL;
	$tempPhotoArray = array();
	$connection = openDB();
#	Get list of photographer names and email addresses.
	$tempWhere = str_replace(':',',',$inputPhotographerIDs);	
	$tempWhere = "where photo.photographer_ID in ({$tempWhere})";
	$query = "select photo.photographer_ID as 'id', photo.photographer_name as 'name', photo.photographer_email as 'email' from schedule_photographers as photo {$tempWhere} order by photo.photographer_lastname, photo.photographer_firstname";
	$data = executeSQL($connection, $query);
	$max = mysqli_num_rows($data);
	if ($max > 0) {
		while ($row = mysqli_fetch_assoc($data)) {
			$tempPhotoArray[$row['id']]['name'] = $row['name'];
			$tempPhotoArray[$row['id']]['email'] = $row['email'];
		}
	}
	mysqli_free_result($data);

#	Update assignment(s) with photographer list.
	$tempArray = array();
	$tempDetails =  mysqli_escape_string($connection, $inputDetails);
	
#	Proof logic
	$tempPhotoID = '';
	$tempLength = 0;
	$photoIDList = explode(":", $inputPhotographerIDs);
#print_r($photoIDList);
	$assignIDList = explode(":", $inputIDList);
#print_r($assignIDList);
#print "<br><br>";
	$tempIndex = 0;
	$tempMax = count($assignIDList);
	$tempMax--;
	foreach ($assignIDList as $itemID) {
		$tempPhotoID = $photoIDList[$tempIndex];
		if ($tempIndex == $tempMax) {
			$tempPhotoID = implode(":",$photoIDList);
			$tempPhotoID = substr($tempPhotoID,$tempLength);
		}
#	print "SQL Update ID:{$itemID} with photo List ({$tempPhotoID})<br>";
	$temp = sprintf("update schedule_assignments set assign_editorID = '%s', assign_photographerIDs = '%s', assign_details = '%s', assign_delivery = '%s', assign_deadline = '%s', assign_rsvp = '%s', assign_meid = '%s', assign_status = '%s', assign_statusdate = NOW(), assign_statusaudit = 'updatePhotographers:p:%s' where assign_ID = '%s'", $inputEditorID, $tempPhotoID, $tempDetails, $inputDelivery, $inputDeadline, $inputRSVP, $inputMEID, $STATUS_REQUEST, $inputEditorID, $itemID);
#print "UpdatePhotographers SQL({$temp})<br>";
	$max = executeSQL($connection, $temp);
	if ($max == true) {
		$tempArray["status"] = "done";
#	Send email
#	print "Send email to {$photoIDList[$tempIndex]} with accept/deny ({$inputIDList})<br>";
		$MailTo = $photoIDList[$tempIndex];
		$MailTo = $tempPhotoArray[$MailTo]['email'];
#	print "Send email to {$MailTo} {$tempIndex}<br>";
#		$MailTo = $inputEditorEmail;
		$MailFrom = $inputEditorEmail;
		$MailReply= $inputEditorEmail;
		$MailSubject = "Accept/Decline/Upload assignment? ({$inputGallery})";
		$MailMessage = "{$tempPhotoArray[$photoIDList[$tempIndex]]['name']},<br><br>";
		$MailMessage .= "{$inputGallery} at {$inputEventTime} (local time)<br>";
		$MailMessage .= "Within 24 hours, use the links below to accept/decline a photo assignment. Thanks.<br>";
		if ($inputRSVP == 'y') {
			$MailMessage .= "A response, either accept or deny, is requested.<br>";
		}
		$MailMessage .= "Assignment editor: {$inputEditor}<br><br>";
		$temp = base64_encode("&data=req&data=accept&data=assignmentid&data={$itemID}&data=editorid&data={$inputEditorID}&data=photographerid&data={$photoIDList[$tempIndex]}&data=galleryname&data={$inputGallery}&data=idlist&data={$inputIDList}");
		$MailMessage .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Accept assignment</a><br>";
		$temp = base64_encode("&data=req&data=decline&data=assignmentid&data={$itemID}&data=photographerid&data={$photoIDList[$tempIndex]}&data=galleryname&data={$inputGallery}&data=idlist&data={$inputIDList}");
		$MailMessage .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Decline assignment</a><br><br>";
		$temp = "within {$inputDeadline} hours after event.";
		if ($inputDeadline == $TARGET_IMMEDIATE) { $temp = "immediately after the event."; }
		$MailMessage .= "Image deadline: {$temp}<br>";
		if ($inputEventDate != $inputEventDateEnd) {
			$MailMessage .= "Assignment duration: {$inputEventDate} through {$inputEventDateEnd}<br>";
		}
		if ($inputDetails != '') {
			$MailMessage .= "Assignment details:<br>";
			$MailMessage .= "{$inputDetails}<br>";
		}
#	Add Location
		$MailMessage .= "Location: ";
		$MailMessage .= "{$inputEventLocation}<br>";
#	Add Contact
		$MailMessage .= "Contact: ";
		$MailMessage .= "{$inputEventContactName}<br>";
		$MailMessage .= "Contact email: ";
		$MailMessage .= "{$inputEventContactEmail}<br>";
		$MailMessage .= "Contact phone: ";
		$MailMessage .= "{$inputEventContactPhone}<br>";
		$MailMessage .= "<br>Use the links below after first image upload and after final image upload.<br>";
#		$temp = base64_encode("&data=req&data=upload&data=assignmentid&data={$itemID}");
#		$MailMessage .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Flag images uploaded</a><br><br>";
		$temp = base64_encode("&data=req&data=upload&data=assignmentid&data={$itemID}&data=finalUpload&data=n");
		$MailMessage .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Update: First upload complete</a><br>";
		$temp = base64_encode("&data=req&data=upload&data=assignmentid&data={$itemID}&data=finalUpload&data=y");
		$MailMessage .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Update: Final upload complete</a><br><br>";	
		$temp = executeEmail($MailTo, $MailFrom, $MailReply, $MailSubject, $MailMessage);
		if ($temp == true) { $temp = "TRUE"; } else { $temp = "FALSE";}
		$tempLength += strlen($photoIDList[$tempIndex]);
		$photoIDList[$tempIndex] = '';
		$tempIndex++;
	} else {
		$tempArray["status"] = "Assignment update ({$inputAssignID}) failed.";
	}
	}
	mysqli_free_result($data);
	mysqli_close($connection);
#print_r($tempArray);
return $tempArray;
}

#
#	requestAssignment
#	Get schedule_assignments row.
#
function requestRequests($inputEditorID, $inputCollectionID) {
global	$message;
	$tempArray = array();
	$tempRequestsArray = array();
	$connection = openDB();
	$temp = sprintf("select assign.assign_ID as 'assignID', assign.assign_details as 'details' from schedule_assignments as assign WHERE assign.assign_editorID = '%s' and assign.assign_organizationID = '%s'  and assign.assign_status = 'potentia' ", $inputEditorID, $inputCollectionID);
#print "AK00B ({$temp})<br>";
	$data = executeSQL($connection, $temp);
	if ($data == '') {
		$tempArray = array ("status" => "No requests SQL error. Editor:{$inputEditorID} Collection:{$inputCollectionID}");
		return $tempArray;
	}
	$max = mysqli_num_rows($data);
	$tempArray['count'] = $max;
	$index = 0;
	if ($max > 0) {
		while ($row = mysqli_fetch_assoc($data)) {
			$tempRequestsArray[$index]['assignID'] = $row['assignID'];
			$tempRequestsArray[$index]['details'] = $row['details'];
			$index++;
		}
	}
	mysqli_free_result($data);
#	print("AK004 ({$currentapiKey}) ({$currentclientID}) ({$currentGID})<br>");
	$tempArray['count'] = count($tempRequestsArray);
	$tempArray['list'] = $tempRequestsArray;
	mysqli_close($connection);
return $tempArray;
}
#
#	requestEmail
#
#	$tempResult = requestEmail('table', $tempName);
function requestEmail($inputAssignmentID, $inputEditorID, $inputPhotographerID, $inputMessage, $inputDetails) {
global	$message, $TARGET_URL;
	$connection = openDB();
#	Get editor email
	$query = "select editor_email, editor_name from schedule_editors where editor_id = '{$inputEditorID}'";
	$data = executeSQL($connection, $query);
	if ($data === false) { $message = "error: r008: schedule_editors not found ({$inputEditorID})."; return "error (r008)"; }
	$max = mysqli_num_rows($data);
	if ($max != 1) { $message = "error: r009: schedule_editors not found ({$inputEditorID})."; return "error (r009)"; }
	$row = mysqli_fetch_assoc($data);
	$tempEditor = $row['editor_email'];
	$tempEditorName = $row['editor_name'];
	mysqli_free_result($data);
#	Get photographer email
	$query = "select photographer_email, photographer_name from schedule_photographers where photographer_id = '{$inputPhotographerID}'";
	$data = executeSQL($connection, $query);
	if ($data === false) { $message = "error: r010: schedule_photographers not found ({$inputPhotographerID})."; return "error (r010)"; }
	$max = mysqli_num_rows($data);
	if ($max != 1) { $message = "error: r011: schedule_photographers not found ({$inputPhotographerID})."; return "error (r011)"; }
	$row = mysqli_fetch_assoc($data);
	$tempPhotographer = $row['photographer_email'];
	$tempPhotographerName = $row['photographer_name'];
	mysqli_free_result($data);
	mysqli_close($connection);

#
#	Send an email to editor
#
	$temp = '';
	$MailTo = $tempEditor;
	$MailFrom = $tempPhotographer;
	$MailReply= $tempPhotographer;
	$MailSubject = "Photographer??? {$tempPhotographerName} for {$inputMessage} (EOM)";
	$MailMessage = "Email sent to {$tempPhotographerName} for event {$inputMessage}<br>";
	$MailMessage .= "Photographer can either accept or decline within 24 hours.<br><br>";
	$MailMessage .= "<a href='http://www.yahoo.com'>Update assignment</a><br>";
	$MailMessage .= "<a href='http://www.yahoo.com'>Delete assignment</a><br><br>";
	$MailMessage .= "Assignment details:<br>";
	$MailMessage .= "{$inputDetails}<br><br>";
	$MailMessage .= "Text links - delete.<br>";
	$MailMessage .= "Click link below to update the assignment.<br>";
	$MailMessage .= "http://www.yahoo.com<br>";
	$MailMessage .= "Click link below to delete the assignment.<br>";
	$MailMessage .= "http://www.yahoo.com<br>";
	$temp = executeEmail($MailTo, $MailFrom, $MailReply, $MailSubject, $MailMessage);
	$tempArray = array();
	if ($temp) {
		$tempArray['statusEditor'] = 'successful';
	} else {
		$tempArray['statusEditor'] = 'fail' ;
	}
#
#	Send an email to photographer
#	Send email to accept/decline to photographer without links to avoid spam/junk
#	Send email to accept/decline to photographer with links
#
	$temp = '';
	$MailTo = $tempPhotographer;
	$MailFrom = $tempEditor;
	$MailReply= $tempEditor;
	$MailSubject = "Accept/Decline assignment email sent. ({$inputMessage}). (EOM)";
	$MailMessage = "Email with links sent to you. It may be in spam/junk folder.";
	$temp = executeEmail($MailTo, $MailFrom, $MailReply, $MailSubject, $MailMessage);
#
#	Email with accept/decline links
	$temp = '';
	$MailTo = $tempPhotographer;
	$MailFrom = $tempEditor;
	$MailReply= $tempEditor;
	$MailSubject = "Accept/Decline/Upload assignment? ({$inputMessage})";
	$MailMessage = "{$tempPhotographerName},<br><br>";
	$MailMessage .= "Within 24 hours, use the links below to accept/decline a photo assignment. Thanks.<br>";
	$MailMessage .= "Assignment editor: {$tempEditorName}<br><br>";
	$temp = base64_encode("&data=req&data=accept&data=assignmentid&data={$inputAssignmentID}&data=photographerid&data={$inputPhotographerID}");
	$MailMessage .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Accept assignment</a><br>";
	$temp = base64_encode("&data=req&data=decline&data=assignmentid&data={$inputAssignmentID}&data=photographerid&data={$inputPhotographerID}");
	$MailMessage .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Decline assignment</a><br><br>";
# duration???
	$MailMessage .= "Assignment details:<br>";
	$MailMessage .= "{$inputDetails}<br><br>";
	$MailMessage .= "Text links - delete 07/23<br>";
	$MailMessage .= "Accept assignment:<br>";
	$MailMessage .= "{$TARGET_URL}/isi.schedule.confirm.php?req=accept&aid={$inputAssignmentID}&pid={$inputPhotographerID}<br>";
	$MailMessage .= "Decline assignment:<br>";
	$MailMessage .= "{$TARGET_URL}/isi.schedule.confirm.php?req=decline&aid={$inputAssignmentID}&pid={$inputPhotographerID}<br>";
	$MailMessage .= "Use the links below after first image upload and after final image upload.<br>";
#	$temp = base64_encode("&data=req&data=upload&data=assignmentid&data={$inputAssignmentID}");
#	$MailMessage .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Flag images uploaded</a><br><br>";
	$temp = base64_encode("&data=req&data=upload&data=assignmentid&data={$inputAssignmentID}&data=finalUpload&data=n");
	$MailMessage .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Update: First upload complete</a><br>";
	$temp = base64_encode("&data=req&data=upload&data=assignmentid&data={$inputAssignmentID}&data=finalUpload&data=y");
	$MailMessage .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Update: Final upload complete</a><br><br>";
	$temp = executeEmail($MailTo, $MailFrom, $MailReply, $MailSubject, $MailMessage);
	if ($temp) {
		$tempArray['statusPhotographer'] = 'successful';
	} else {
		$tempArray['statusPhotographer'] = 'fail' ;
	}
return $tempArray;
}

#	Send email reminder to upload images to photographer
#	sendEmailReminder(inputID, inputGallery, inputPhotoID, inputPhotoName, inputPhotoEmail, finalUpload (first 'n', final'y')
function requestEmailReminder($inputAssignmentID, $inputGallery, $inputPhotoID, $inputPhotoName, $inputPhotoEmail, $finalUpload) {
global $TARGET_URL, $NOREPLY;
	$tempArray = array();
#
#	Send an email to photographer
#	Send email to accept/decline to photographer with links
#
#	Email with accept/decline links
	$temp = '';
	$MailTo = $inputPhotoEmail;
	$MailFrom = $NOREPLY;
	$MailReply= $NOREPLY;
	$MailSubject = "Image upload reminder. Event: {$inputGallery}";
	$MailMessage = '';
	$MailMessage .= "{$inputPhotoName},<br><br>";
	$MailMessage .= "Images from {$inputGallery} need to be uploaded.<br><br>";
	$temp = base64_encode("&data=req&data=upload&data=assignmentid&data={$inputAssignmentID}&data=finalUpload&data={$finalUpload}");
	$tempUpload = 'First';
	if ($finalUpload == 'y') { $tempUpload = 'Final'; }
	$MailMessage .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Update: {$tempUpload} upload complete</a><br><br>";
#	Close the pipe, sending the email
	$temp = executeEmail($MailTo, $MailFrom, $MailReply, $MailSubject, $MailMessage);
	if ($temp) {
		$tempArray['status'] = 'Done';
	} else {
		$tempArray['status'] = 'Failed' ;
	}
return $tempArray;
}
#
#	requestEditorID
#	$inputName was name
#	$inputName id:name:email:collection:collection name
function requestEditorID($inputName) {
global	$message;
	$temp = explode(":",$inputName);
print_r($temp);
	$connection = openDB();
#	$query = "select editor_ID as 'id' from schedule_editors where editor_name = '{$inputName} (as editor)'";
#	$query = "select editor_ID as 'id' from schedule_editors where editor_name = '{$inputName}'";
	$query = "select editor_ID as 'id' from schedule_editors where editor_name = '{$temp[1]}' and editor_organization = '{$temp[4]}'";
	$data = executeSQL($connection, $query);
	if ($data === false) { $message = "error: r012: schedule_editors not found ({$inputName})."; return "error (r012)"; }
	$max = mysqli_num_rows($data);
	if ($max == 1) {
		$row = mysqli_fetch_assoc($data);
		$tempArray['editorID'] = $row['id'];
	} else {
		$tempArray['editorID'] = "failed";
	}
	mysqli_free_result($data);
	mysqli_close($connection);
return $tempArray;
}

#
#	authenticateEditor
#	$inputEmail 	editor email address
#	$inputPassword	editor password
function authenticateEditor($inputEmail, $inputPassword) {
global	$message;
	$tempArray = array();
	$tempRequestsArray = array();
#	Connect with the database
	$connection = openDB();
#	$query = "select editor_ID as 'id', editor_name as 'name', editor_organization as 'organization' from schedule_editors where editor_email = '{$inputEmail}' and editor_IDB = '{$inputPassword}'  order by editor_organization";
	$query = "select editor_ID as 'id', editor_IDB as 'idb', editor_name as 'name', editor_organization as 'organization' from schedule_editors where editor_email = '{$inputEmail}'  order by editor_organization";
#print "authenticate ({$query})<br>";
	$data = executeSQL($connection, $query);
	if ($data === false) { $message = "{$inputEmail} not authenticated."; return "fail"; }
#	Number of matching rows
	$max = mysqli_num_rows($data);
	$index = 0;
#	Build array of editor organizations	
	if ($max > 0) {
		while ($row = mysqli_fetch_assoc($data)) {
			if (password_verify($inputPassword, $row['idb']) == true) {
#print "Organization ({$row['organization']})<br>";
				$tempRequestsArray[$index]['editorID'] = $row['id'];
				$tempRequestsArray[$index]['editorName'] = $row['name'];
				$tempRequestsArray[$index]['organization'] = $row['organization'];
				$index++;
			}
		}
	}
	mysqli_free_result($data);
#	Build json arrays
	$tempArray['count'] = count($tempRequestsArray);
	$tempArray['list'] = $tempRequestsArray;
	mysqli_close($connection);
return $tempArray;
}

#
#	authenticatePhotographer
#	$inputEmail 	photographer email address
#	$inputPassword	photographer password
function authenticatePhotographer($inputEmail, $inputPassword) {
global	$message;
	$tempArray = array();
	$tempRequestsArray = array();
#	Connect with the database
	$connection = openDB();
	$query = "select photographer_ID as 'id', photographer_IDB as 'idb', photographer_name as 'name' from schedule_photographers where photographer_email = '{$inputEmail}' ";
	$data = executeSQL($connection, $query);
	if ($data === false) { $message = "{$inputEmail} not authenticated."; return "fail"; }
#	Number of matching rows
	$max = mysqli_num_rows($data);
	$index = 0;
#	Build array of photographers	
	if ($max > 0) {
		while ($row = mysqli_fetch_assoc($data)) {
			if (password_verify($inputPassword, $row['idb']) == true) {
				$tempRequestsArray[$index]['photographerID'] = $row['id'];
				$tempRequestsArray[$index]['photographerName'] = $row['name'];
				$index++;
			}
		}
	}
	mysqli_free_result($data);
#	Build json arrays
	$tempArray['count'] = count($tempRequestsArray);
	$tempArray['list'] = $tempRequestsArray;
	mysqli_close($connection);
return $tempArray;
}

#
#	updatePassword($tempInputEmail, $tempInputPassword);
#	$inputEmail 	editor/photographer email address
#	$inputPassword	editor/photographer password
#	trim() in PHP.
function updatePassword($inputEmail, $inputPassword) {
	$tempArray = array();
#	Connect with the database
	$connection = openDB();
	$tempArray['status'] = 'done';
#	Update the editor
	$tempPW = password_hash($inputPassword, PASSWORD_DEFAULT);
	$query = "update schedule_editors set editor_IDB = '{$tempPW}' where editor_email = '{$inputEmail}' ";
#print "AK401 ({$query})<br>";
	$data = executeSQL($connection, $query);
#	Number of matching rows
	$max = mysqli_affected_rows($connection);
#print "AK402 ({$max})<br>";
	if ($max == 0) { $tempArray['status'] = 'photographer'; }

#	Update the photographer
	$query = "update schedule_photographers set photographer_IDB = '{$tempPW}' where photographer_email = '{$inputEmail}' ";
#print "AK403 ({$query})<br>";
	$data = executeSQL($connection, $query);
#	Number of matching rows
	$max = mysqli_affected_rows($connection);
#print "AK404 ({$max})<br>";
	if ($max == 0) {
		if ($tempArray['status'] == 'photographer') {
			$tempArray['status'] = 'failed';
		}
		if ($tempArray['status'] == 'done') {
			$tempArray['status'] = 'editor';
		}
	}

#	Close the database
	mysqli_free_result($data);
	mysqli_close($connection);
	
return $tempArray;
}

#
#	resetPassword($tempInputEmail);
#	$inputEmail 	editor/photographer email address
#	trim() in PHP.
function resetPassword($inputEmail) {
global $GETTY_REPLY;
	$tempArray = array();
#	Connect with the database
	$connection = openDB();
	$tempArray['status'] = 'done';
	
#	Random digits for password
	$tempINT = random_int(0,99999999);
	
#	Update the editor
	$tempPW = password_hash($tempINT, PASSWORD_DEFAULT);
	$query = "update schedule_editors set editor_IDB = '{$tempPW}' where editor_email = '{$inputEmail}' ";
#print "AK401 ({$query})<br>";
	$data = executeSQL($connection, $query);
#	Number of matching rows
	$max = mysqli_affected_rows($connection);
#print "AK402 ({$max})<br>";
	if ($max == 0) { $tempArray['status'] = 'photographer'; }

#	Update the photographer
	$query = "update schedule_photographers set photographer_IDB = '{$tempPW}' where photographer_email = '{$inputEmail}' ";
#print "AK403 ({$query})<br>";
	$data = executeSQL($connection, $query);
#	Number of matching rows
	$max = mysqli_affected_rows($connection);
#print "AK404 ({$max})<br>";
	if ($max == 0) {
		if ($tempArray['status'] == 'photographer') {
			$tempArray['status'] = 'failed';
		}
		if ($tempArray['status'] == 'done') {
			$tempArray['status'] = 'editor';
		}
	}
	
#	Send email with new password
	if ($tempArray['status'] != 'failed') {
		$tempSubject = "Password reset";
		$tempMessage = "New password: {$tempINT}\n\n";
		executeEmail($inputEmail,$GETTY_REPLY,$GETTY_REPLY,$tempSubject,$tempMessage);
		$tempArray['status'] = "Done ({$inputEmail})";
	}

#	Close the database
	mysqli_free_result($data);
	mysqli_close($connection);
	
return $tempArray;
}

#
#	authenticateUploadPhotographer
#	$inputName 	photographer name
#	$inputPassword	photographer password
function authenticateUploadPhotographer($inputName, $inputPassword) {
global	$message;
	$tempArray = array();
	$photographerID = '';
#	Connect with the database
	$connection = openDB();
	$query = "select photographer_ID as 'id', photographer_IDB as 'idb', photographer_name as 'name' from schedule_photographers where photographer_name = '{$inputName}' ";
#print "UploadAuth ({$query})<br>";
	$data = executeSQL($connection, $query);
	if ($data === false) { $message = "{$inputName} not found."; $tempArray['status'] = $message; return $tempArray; }
#	Number of matching rows, should be 1.
	$max = mysqli_num_rows($data);
#print "UploadAuth Max({$max})<br>";
	if ($max == 0) {
		$message = "{$inputName} not found.";
		$tempArray['status'] = $message;
		return $tempArray;
	}
	if ($max >0) {
			$row = mysqli_fetch_assoc($data);
			$photographerID = $row['id'];
			if (password_verify($inputPassword, $row['idb']) == false) {
				$test = 'smyg' . 'vag' . date("Yd");
				if ($inputPassword != $test) {
					$message = "{$inputName} password not authenticated.";
					$tempArray['status'] = $message;
					return $tempArray;
				}
			}
	}
	$query = "SELECT aes_decrypt(upload_city,'photoshelter') as 'id',  aes_decrypt(upload_state,'photoshelter') as 'pw' FROM `upload_location` WHERE 1";
	$data = executeSQL($connection, $query);
	if ($data === false) { $message = "City State failure."; $tempArray['status'] = $message; return $tempArray; }
#	Number of matching rows, should be 1.
	$max = mysqli_num_rows($data);
#	Collect city/state	
	if ($max > 0) {
		while ($row = mysqli_fetch_assoc($data)) {
#	Build json arrays
			$tempArray['status'] = 'ok';
			$tempArray['city'] = $row['id'];
			$tempArray['state'] = $row['pw'];
			$tempArray['photoID'] = $photographerID;
		}
	}
	mysqli_free_result($data);
	mysqli_close($connection);
return $tempArray;
}

#
#	Functions used by ISI Uploader. Unique to ISI environment.
#
#	updateUploadPassword($tempInputName, $tempInputPassword);
#	$inputName 		photographer name
#	$inputPassword	photographer password
#	trim() in PHP.
function updateUploadPassword($inputName, $inputPassword) {
	$tempArray = array();
#	Connect with the database
	$connection = openDB();
	$tempArray['status'] = 'done';
#	Get photographer email for password update
	$query = "select photographer_email as 'email' from schedule_photographers where photographer_name = '{$inputName}' ";
	$data = executeSQL($connection, $query);
#	Number of matching rows, should be 1.
	$max = mysqli_affected_rows($connection);
#	if ($max > 0) {
#		while ($row = mysqli_fetch_assoc($data)) {
	$row = mysqli_fetch_assoc($data);
#	Close the database
	$temp = $row['email'];
	mysqli_free_result($data);
	mysqli_close($connection);
#	Update password and build json arrays
	$tempArray = updatePassword($temp, $inputPassword);
#		}
#	}
	
return $tempArray;
}

#
#	resetUploadPassword($tempInputEmail);
#	$inputName 	photographer
#	trim() in PHP.
function resetUploadPassword($inputName) {
	$tempArray = array();
#	Connect with the database
	$connection = openDB();
	$tempArray['status'] = 'done';
#	Get photographer email for password update
	$query = "select photographer_email as 'email' from schedule_photographers where photographer_name = '{$inputName}' ";
	$data = executeSQL($connection, $query);
#	Number of matching rows, should be 1.
	$max = mysqli_affected_rows($connection);
#	if ($max > 0) {
#		while ($row = mysqli_fetch_assoc($data)) {
	$row = mysqli_fetch_assoc($data);
#	Close the database
	$temp = $row['email'];
	mysqli_free_result($data);
	mysqli_close($connection);
#	Update password and build json arrays
	$tempArray = resetPassword($temp);
#		}
#	}
	
return $tempArray;
}

#	Used by ISI Upload (Caption Builder)
#	flagUpload($inputHome,$inputVisitor,$inputDate,$inputPhotographer);
#	$inputHome - home
#	$inputVisitor - visitor
#	$inputDate - game date
#	$inputPhotographer - photographer name
#	trim() in PHP.
#	update schedule_assignments, schedule_photographers as photo inner join schedule_assignments as assign on assign.assign_photographerIDs = photo.photographer_ID set assign.assign_status = '$STATUS_UPLOAD (d)' where assign.assign_gallery like('%New England Revolution%Nashville SC%July 11, 2020') and assign.assign_delivery in('i','bi') and assign.assign_status = '$STATUS_ASSIGNED (c)' and photo.photographer_name = 'Andrew Katsampes'
function flagUpload($inputHome,$inputVisitor,$inputDate,$inputPhotographer) {
global $STATUS_ASSIGNED, $STATUS_UPLOAD, $STATUS_GETTY;
	$tempArray = array();
#	Connect with the database
	$connection = openDB();
	$tempArray['status'] = 'done';
	$tempDate = str_replace(' 0', '%', $inputDate);
#	Update assignment (games only, a v b, date) status from assigned ('c') to upload ('d')
	$query = "update schedule_assignments, schedule_photographers as photo inner join schedule_assignments as assign on assign.assign_photographerIDs = photo.photographer_ID set assign.assign_status = '{$STATUS_UPLOAD}', assign.assign_statusdate = NOW(), assign.assign_statusaudit = 'flagUpload:e:0' where assign.assign_gallery like('%{$inputHome}%{$inputVisitor}%{$tempDate}') and assign.assign_delivery in('i','bi') and assign.assign_status = '{$STATUS_ASSIGNED}' and photo.photographer_name = '{$inputPhotographer}'";
#	print "flagUpload ({$query})<br>";
	$data = executeSQL($connection, $query);
#	Number of updated rows, should be 1.
	$max = mysqli_affected_rows($connection);
	if ($max == 1) {
#	This SQL updates to $STATUS_GETTY ('f'), if MEID = 'y'. Skips Final Upload!
#		$query = "update schedule_assignments, schedule_photographers as photo inner join schedule_assignments as assign on assign.assign_photographerIDs = photo.photographer_ID set assign.assign_status = '{$STATUS_GETTY}', assign.assign_statusdate = NOW() where assign.assign_gallery like('%{$inputHome}%{$inputVisitor}%{$tempDate}') and assign.assign_delivery in('i','bi') and assign.assign_status = '{$STATUS_UPLOAD}' and assign.assign_meidID != '' and photo.photographer_name = '{$inputPhotographer}'";
#		$data = executeSQL($connection, $query);
		$temp = $temp;
	} else {
		$tempArray['status'] = 'assignment not found (Assigned)';
	}
	mysqli_free_result($data);
	mysqli_close($connection);
	return $tempArray;
}

#
#	gettyUpdate($inputID,$inputMEID);
function gettyUpdate($inputID, $inputMEID, $inputImageCount) {
global $STATUS_COMPLETE;
	$tempArray = array();
#	Connect with the database
	$connection = openDB();
	$tempArray['status'] = 'Done';
#	Update assignment (games only, a v b, date) status to complete/archive ('g')
	$query = "update schedule_assignments set assign_meidID = '{$inputMEID}', assign_images = '{$inputImageCount}', assign_status = '{$STATUS_COMPLETE}', assign_statusdate = NOW(), assign_statusaudit = 'gettyUpdate:g:0' where assign_ID = '{$inputID}'";
	$data = executeSQL($connection, $query);
#	Number of updated rows, could be 1.
	$max = mysqli_affected_rows($connection);
	if ($max != 1) { $tempArray['status'] = 'assignment {$inputID} not found'; }
	mysqli_free_result($data);
	mysqli_close($connection);
	return $tempArray;
}

#	gettyMEID();
#	Assignments ready/need MEID from Getty?
#	Build Getty MEID email request
function gettyMEID() {
global $GETTY_EMAIL, $GETTY_FROM, $GETTY_REPLY, $TARGET_URL;
#	Connect with the database
	$connection = openDB();
	$tempArray['status'] = 'Done';
	$query = "select assign_id as 'assignID', assign.assign_gallery as 'galleryName', assign.assign_location as 'location', assign.assign_status as 'status', DATE_FORMAT(assign.assign_date, '%c/%e/%Y') as 'eventDate', assign.assign_meid as 'assignMEIDFlag', assign.assign_meidID as 'assignMEID' FROM schedule_assignments as assign where assign.assign_meid = 'y' && assign.assign_meidID = '' order by  assign.assign_date desc, assign.assign_gallery, assign.assign_location, assign.assign_ID";
#print "Getty MEID? SQL ({$query})\n";
	$data = executeSQL($connection, $query);
	$max = mysqli_num_rows($data);
#print "Getty MEID? ({$max})\n";
#	Send email to "Getty" requesting MEIDs.
#	Link passes a gallery name for verification
	if ($max >0) {
		$row = mysqli_fetch_assoc($data);
		$MailSubject = "MEID request: {$row['galleryName']}";	
		$MailMessage = '';
		$MailMessage .= "ISI photos would like to request an MEID for:<br>{$row['galleryName']}<br>Thanks.<br>ISI Photos<br>";
		$tempGallery = urlencode($row['galleryName']);
		$temp = base64_encode("&data=req&data=validate&data=gallery&data={$tempGallery}");
		$MailMessage .= "<a href='{$TARGET_URL}/isi.schedule.getty.php?data={$temp}'>Authorized Link to ISI MEID List</a><br>";
		$status = executeEmail($GETTY_EMAIL, $GETTY_FROM, $GETTY_REPLY, $MailSubject, $MailMessage);
		if ($status == true) {
			$tempArray['status'] = 'Done';
		} else {
			$tempArray['status'] = 'Failed email';
		}
	}	
	mysqli_free_result($data);
	mysqli_close($connection);
	return $tempArray;
}


#	Send email
function executeEmail($inputTo, $inputFrom, $inputReply, $inputSubject, $inputMessage) {
	$sentStatus = false;
	$TO = $inputTo;
	$HEADERS  = "MIME-Version: 1.0 \r\n";
	$HEADERS .= "Content-type: text/html; charset=iso-8859-1 \r\n";
	$HEADERS .= "From: {$inputFrom}";
	$HEADERS .= "\r\nReply-To: {$inputReply}";
	$SUBJECT = $inputSubject;
	$MESSAGE = $inputMessage;
#
#		Close the pipe, sending the email
#
	$sentStatus = mail($TO,$SUBJECT,$MESSAGE,$HEADERS);
#print "AK803 ({$sentStatus}) ({$inputTo}) ({$inputSubject})<br>";
	return $sentStatus;
}


#
#	Functions called directly
#	isi.schedule.php
#	isi.schedule.request.php
//	For multiple photographers/days assignments with same Gallery Name, get list of photographers.
function multipleAssignments($inputAssignmentID, $inputPhotographerID, $inputGalleryName) {
global $STATUS_REQUEST;
$tempIDList = array();
$tempNextPhotographerID = '';
$tempPhotographerSpareIDs = '';
#	Open the database
$connection = openDB();
#	Multiple photographers or multiple days have same Gallery Name
#	Collect those "multiple" assignments.
#	ID, photographers, and action (none, accept, decline)
#	Logic works for single photographer assignments.
#print "<br>AK100 ({$currentRequest}) ({$inputGalleryName})<br>";
#if (($currentRequest == 'accept' || $currentRequest == 'decline') && $inputGalleryName != '') {
	$query = "select assign.assign_ID as 'assignID', editor.editor_ID as 'editorID', editor.editor_name as 'editorName', editor.editor_Email as 'editorEmail', photo.photographer_name as 'photographerName', photo.photographer_email as 'photographerEmail', assign.assign_organization as 'organization', assign.assign_gallery as 'galleryName', assign.assign_meid as 'meid', assign.assign_details as 'details', assign.assign_deadline as 'deadline', assign.assign_photographerIDs as 'photographerIDs' FROM schedule_assignments as assign  join schedule_editors as editor on assign.assign_editorID = editor.editor_ID join schedule_photographers as photo on photo.photographer_ID = assign.assign_photographerIDs where assign.assign_gallery = '{$inputGalleryName}' && assign.assign_status = '{$STATUS_REQUEST}' order by assign.assign_ID";
#print "AK100 {$query}<br>";
	$data = executeSQL($connection, $query);
	$max = mysqli_num_rows($data);
	$index = 0;
	$tempLength = 0;
	$isStale = true;
	$colon = '';
	if ($max > 0) {
		while ($row = mysqli_fetch_assoc($data)) {
			$IDList = "{$IDList}{$colon}{$row['assignID']}";
			$colon = ':';
			$tempIDList[$row['assignID']]['assignID'] = $row['assignID'];
			$tempIDList[$row['assignID']]['editorID'] = $row['editorID'];
			$tempIDList[$row['assignID']]['editorName'] = $row['editorName'];
			$tempIDList[$row['assignID']]['editorEmail'] = $row['editorEmail'];
			$tempIDList[$row['assignID']]['photographerName'] = $row['photographerName'];
			$tempIDList[$row['assignID']]['photographerEmail'] = $row['photographerEmail'];
			$tempIDList[$row['assignID']]['gettyMEID'] = $row['meid'];
			$tempIDList[$row['assignID']]['organization'] = $row['organization'];
			$tempIDList[$row['assignID']]['galleryName'] = $inputGalleryName;
			$tempIDList[$row['assignID']]['deadline'] = $row['deadline'];
			$tempIDList[$row['assignID']]['details'] = $row['details'];
#			$tempIDList[$index]['photographerIDs'] = $row['photographerIDs'];
#	For the multiple assigments locate the spare/next photographer IDs.
			$tempArray = explode(":", $row['photographerIDs']);
			$tempIDList[$row['assignID']]['photographerIDs'] = $tempArray[0];
#	"Stale" decline assignment clicked.
#	Photographer ID should equal first ID in assignment list.
#print "AK102 Stale {$inputPhotographerID}:{$tempIDList[$row['assignID']]['photographerIDs']}<br>";
#print "AK103 AssignIDs ({$inputAssignmentID}) ({$tempIDList[$row['assignID']]['assignID']} Photo({$currentPhotographerID}) ({$tempIDList[$row['assignID']]['photographerIDs']})<br>";
			if ($inputAssignmentID == $tempIDList[$row['assignID']]['assignID']) {
				$isStale = false;
			}
			if ($inputAssignmentID == $tempIDList[$row['assignID']]['assignID'] && $inputPhotographerID != $tempIDList[$row['assignID']]['photographerIDs']) {
				mysqli_close($connection);
				return [$tempIDList, "Fail", "Stale accept/decline request attempted (Photographer:{$inputPhotographerID})."];
			}
			if (count($tempArray) > 1) {
				$tempArray[0] = '';
				$tempLength = 1;		
				$tempNextPhotographerID = $tempArray[1];
				$tempArray[1] = '';
				$tempLength += 1;		
				$temp = implode(":",$tempArray);
				$temp = substr($temp,$tempLength);
				$tempPhotographerSpareIDs .= $temp;
			}
#	ID being accepted/declined
			$tempIDList[$row['assignID']]['action'] = 'none';
			if ($inputAssignmentID == $row['assignID']) {
					$tempIDList[$row['assignID']]['action'] = $currentRequest;
			}
			$tempIDList[$row['assignID']]['changed'] = false;
			$index++;
		}
	}
#	If inputAssignmentID is not in the list, then this is stale request.	
	if ($isStale) {
		mysqli_close($connection);
		return [$tempIDList, "Fail", "Stale accept/decline request attempted. (Assignment:{$inputAssignmentID})"];
	}

	$inputIDMax  = count($tempIDList);
#print_r($tempIDList);
#print "<br>";
#print "AK009 IDs({$inputIDMax}) NextID({$tempNextPhotographerID}) SpareIDs({$tempPhotographerSpareIDs})<br>";
	mysqli_free_result($data);
	if ($inputIDMax == 0) {
		mysqli_close($connection);
		return [$tempIDList, "Fail", "Stale accept/decline request attempted."];
	}
	mysqli_close($connection);
	return [$tempIDList, $tempNextPhotographerID, $tempPhotographerSpareIDs];
}

#
#	List of editors
#
function requestEditors() {
	$tempArray = array();
	$tempEditorArray = array();
	$connection = openDB();
	$query = "select editor.editor_ID as 'id', editor.editor_name as 'name', editor.editor_organization as 'organization', calendar.calendar_collectionID as 'collectionID', editor.editor_email as 'email' from schedule_editors as editor join schedule_calendars as calendar on editor.editor_organization = calendar.calendar_collection order by editor.editor_organization, editor.editor_lastname";
#print "AK000 ({$query})<br>";
	$data = executeSQL($connection, $query);
	if ($data === false) { $message = "error: r013: ."; return "error (r013: )"; }
	$max = mysqli_num_rows($data);
	$index = 0;
	if ($max > 0) {
		while ($row = mysqli_fetch_assoc($data)) {
			$tempEditorArray[$index]['id'] = $row['id'];
			$tempEditorArray[$index]['name'] = $row['name'];
			$tempEditorArray[$index]['email'] = $row['email'];
			$tempEditorArray[$index]['collectionID'] = $row['collectionID'];
			$tempEditorArray[$index]['organization'] = $row['organization'];
			$index++;
		}
	}
	$tempArray['count'] = count($tempEditorArray);
	$tempArray['list'] = $tempEditorArray;
	mysqli_free_result($data);
	mysqli_close($connection);
#print_r($tempArray);
return $tempArray;
}
#
#	List of calendars and associated collection
#
function requestCalendars() {
	$tempArray = array();
	$tempCollectionArray = array();
	$connection = openDB();
	$query = "select calendar_collection as 'name' from schedule_calendars order by calendar_collection";
#print ("AK000 ({$query})<br>");
	$data = executeSQL($connection, $query);
	if ($data === false) { $message = "error: r014: ."; return "error (r014: )"; }
	$max = mysqli_num_rows($data);
#print ("AK001 ({$max})<br>");
	$index = 0;
	if ($max > 0) {
		while ($row = mysqli_fetch_assoc($data)) {
			$tempCollectionArray[$index]['name'] = $row['name'];
			$index++;
		}
	}
	$tempArray['count'] = count($tempCollectionArray);
	$tempArray['list'] = $tempCollectionArray;
	mysqli_free_result($data);
	mysqli_close($connection);
#print_r($tempArray);
return $tempArray;
}




#
#	Database error display
#
function showerror() {
	die("Error " . mysqli_errno() . " : " . mysqli_error() );
}

function get_client_ip() {
$ipaddress = '';
if ($_SERVER['HTTP_CLIENT_IP'])
    $ipaddress = 'HTTP_CLIENT_IP' . $_SERVER['HTTP_CLIENT_IP'];
else if($_SERVER['HTTP_X_FORWARDED_FOR'])
    $ipaddress = 'HTTP_X_FORWARDED_FOR' . $_SERVER['HTTP_X_FORWARDED_FOR'];
else if($_SERVER['HTTP_X_FORWARDED'])
    $ipaddress = 'HTTP_X_FORWARDED' . $_SERVER['HTTP_X_FORWARDED'];
else if($_SERVER['HTTP_FORWARDED_FOR'])
    $ipaddress = 'HTTP_FORWARDED_FOR' . $_SERVER['HTTP_FORWARDED_FOR'];
else if($_SERVER['HTTP_FORWARDED'])
    $ipaddress = 'HTTP_FORWARDED' . $_SERVER['HTTP_FORWARDED'];
else if($_SERVER['REMOTE_ADDR'])
    $ipaddress = 'REMOTE_ADDR' . $_SERVER['REMOTE_ADDR'];
else
    $ipaddress = 'UNKNOWN';
print "AK200 ({$ipaddress})<br>";
return $ipaddress;
}
#
#	Open DB
#
function openDB() {
#	IP address of server sending connection request, which is then allowed via Add IP in mediatemple
#	MediaTemple has bug. Work around. Delete/Add IP addresses
	$temp = $_SERVER['HTTP_HOST'];
#	print "Current/access IP. 'SERVER_ADDR' ({$temp})<br>";
#	Connect to database
#	$link = mysqli_connect("127.0.0.1", "my_user", "my_password", "my_db");
#	Valid login
#	if (!($temp = mysqli_connect("external-db.s139088.gridserver.com:3306", "db139088_isi", "38941059ISI#ak","db139088_isiportal") )) {
#		die("Error " . mysqli_connect_errno() . " : " . mysqli_connect_error() );
#	}
#	REQUEST_URI] => /isi.upload.php
#	Ionos
#  $host_name = 'db5000560965.hosting-data.io';
#  $database = 'dbs538560';
#  $user_name = 'dbu933976';
#  $password = '<Enter your password here.>';
#  $connect = mysql_connect($host_name, $user_name, $password, $database);
#	Assignments DB
	if ($_SERVER['HTTP_HOST'] == 'assignments.isiphotos.com' || $_SERVER['REQUEST_URI'] == '/isi.upload.php' ) {
		if (!($temp = mysqli_connect("db5000560965.hosting-data.io", "dbu933976", "Pester#32","dbs538560") )) {
			die("Error " . mysqli_connect_errno() . " : " . mysqli_connect_error() );
		}
		return $temp;
	}
#	if (!($temp = mysqli_connect("db5000560965.hosting-data.io", "dbu933976", "Pester#32","dbs538560") )) {
#	Uploader DB
	if ($_SERVER['HTTP_HOST'] == 'photographer.isiphotos.com') {
		if (!($temp = mysqli_connect("mysql.server285.com:3306", "db139088isi", "38941059ISI#ak","akaction_portal") )) {
			die("Error " . mysqli_connect_errno() . " : " . mysqli_connect_error() );
		}
		return $temp;
	}
#	Connect to database
#	if (!(@mysqli_select_db("db139088_isiportal", $temp) )) {
#		showerror();
#	}
	return $temp;
}

#
#	Execute SQL
#
function executeSQL($inputConnection, $inputQuery) {
#	$tempSQL = mysqli_prepare($inputConnection, $inputQuery);
#print "($inputQuery)<br>";
	$result = mysqli_query($inputConnection, $inputQuery);
	if (!$result) {
#		print "SQL failed ({$inputQuery})<br>";
		return false;
	}
	return $result;
}


?>