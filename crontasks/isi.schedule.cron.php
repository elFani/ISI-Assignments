<?php
#
# -----------------------------------------------------------------------------
#	Andrew Katsampes
#
#	Overview
#	Access the schedule_assignments table.
#	If assignment in "assigned" status and event complete, then send reminder email to photographer.
#	If 24 hours passed and "request" status, then assume photographer declined and email
#	next photographer. Updates the row with photographer ID and status date.
#		
#	SELECT TIMESTAMPDIFF(HOUR,`assign_statusdate`,NOW()) FROM `schedule_assignments` WHERE `assign_ID` =3	
#	SELECT TIMESTAMPDIFF(MINUTE,`assign_statusdate`,NOW()), `assign_gallery` FROM `schedule_assignments` WHERE (TIMESTAMPDIFF(MINUTE,`assign_statusdate`,NOW()) > 32) order by `assign_statusdate`		
#		
# -----------------------------------------------------------------------------
//	ISI Schedule tools access
include ("../includes/isi.schedule.tools.php");

ini_set("display_errors",2);

#	Reminder to upload images, every 18 hours.
#	Based on remainder of 0, %18?
#$REMINDINTERVAL = '18';
$REMINDINTERVAL = '8';
$TARGET_URL	= 'https://assignments.isiphotos.com';

#	Status
#	Status of assignment: a=initial, b=request, c=assigned, d=upload, e= no status, =potential.	
$STATUS_INITIAL	= "a";
$STATUS_REQUEST = "b";
$STATUS_ASSIGNED = "c";
$STATUS_UPLOAD	= "d";
$STATUS_WAITING = "e";
$STATUS_GETTY = "f";
#$STATUS_COMPLETE = "g";
print "Test include ({$STATUS_COMPLETE})\n";
#	Getty email
$TARGET_URL	= 'https://assignments.isiphotos.com';
#$TARGET_URL	= 'http://photographer.isiphotos.com';
$GETTY_EMAIL = 'Michael.Lawrie@gettyimages.com';
$GETTY_EMAIL = 'john@isiphotos.com';
#$GETTY_EMAIL = 'katsampes@hotmail.com';
$GETTY_REPLY = 'assignments@isiphotos.com';
$GETTY_FROM	 = $GETTY_REPLY;
$NOREPLY = 'noreply@isiphotos.com';

print "ISI Schedule Cron MYSQLI\n";
	$connection = openDBCron();
#	Collect events that have passed a 24hr mark for a photographer request.
#	$query = sprintf("select download_portalID as 'id', download_imageFN as 'fn', download_imageTitle as 'title', download_imagePhotographer as 'photographer', download_date as 'date' from portal_downloads  WHERE download_hostURL = '%s' order by download_date DESC, download_portalID, download_imagePhotographer, download_imageFN", $inputHost);
#	$query = "SELECT TIMESTAMPDIFF(HOUR,`assign_statusdate`,NOW()) as 'difference', `assign_ID` as 'assignID', `assign_gallery`, `assign_editorID` as 'editorID', `assign_photographerIDs` as 'photoIDs', `assign_collectionID` as 'collectionID' FROM `schedule_assignments` where `assign_status`='request' order by `assign_collectionID`, `assign_statusdate`";
#	TIMESTAMPDIFF(HOUR,assign.assign_statusdate) 'difference' - 1, assign.assign_ID - 2, assign.assign_gallery - 3, editor.editor_Email - 4, assign.assign_photographerIDs - 5, assign.assign_organization - 6, assign.assign_details - 7, assign.assign_rsvp - 8, editor.editor_name - 9, editor.editor_ID - 10
	$query = "SELECT TIMESTAMPDIFF(HOUR,assign.assign_statusdate,NOW()) as 'difference', assign.assign_ID as 'assignID', assign.assign_gallery, editor.editor_Email as 'editorEmail', assign.assign_photographerIDs as 'photoIDs', assign.assign_organization as 'organization', assign.assign_details as 'assignDetails', assign.assign_rsvp as 'assignRSVP', assign.assign_location as 'eventLocation', assign.assign_contactName as 'eventContactName', assign.assign_contactEmail as 'eventContactEmail', assign.assign_contactPhone as 'eventContactPhone', editor.editor_name as 'editorName', editor.editor_ID as 'editorID', DATE_FORMAT(assign.assign_date, '%c/%e/%Y') as 'eventDate', DATE_FORMAT(assign.assign_dateEnd, '%c/%e/%Y') as 'eventDateEnd' FROM schedule_assignments as assign join schedule_editors as editor on assign.assign_editorID = editor.editor_ID where assign.assign_status='{$STATUS_REQUEST}' and TIMESTAMPDIFF(HOUR,assign.assign_statusdate,NOW()) >23 order by assign.assign_organization, assign.assign_statusdate";
print "SQL (24hrs passed) ({$query})\n";
	$data = executeSQLCron($connection, $query);
	if ($data === false) { print "error: c001: Image download SQL failed ()."; exit; }
#	$max = mysql_num_rows($data);
	$max = mysqli_affected_rows($connection);
print "Count({$max}). Passed 24 hours after photographer contacted.\n";
	$currentPhotoArray = array();
	$currentAssignmentArray = array();
	if ($max > 0) {
#		while ($row = mysql_fetch_array($data)) {
		while ($row = mysqli_fetch_assoc($data)) {
#print_r($row);
print "Each assignment/photographer ({$row['difference']}+{$row['photoIDs']}+{$row['organization']}+{$row['assign_gallery']})\n";
			array_push($currentAssignmentArray, $row['difference']."+".$row['assignID']."+".$row['editorEmail']."+".$row['photoIDs']."+".$row['organization']."+".$row['assign_gallery']."+".$row['assignDetails']."+".$row['editorName']."+".$row['editorID']."+".$row['eventDate']."+".$row['eventDateEnd']."+".$row['assignRSVP']."+".$row['eventLocation']."+".$row['eventContactName']."+".$row['eventContactEmail']."+".$row['eventContactPhone']);
		}
	}
print "Array of photographers contacted 24+ hrs.\n";
print_r($currentAssignmentArray);
print "\nEnd array\n";

	$tempLength = 0;
	$tempArray = array();
	$assignmentMax = $max;
	$currentPhotoArray = array();
	$currentAssignmentID = '';
	$currentGallery = '';
	$currentOrganization = '';
	$currentDetails = '';
	$currentEditor = '';
	$currentEditorEmail = '';
	$currentPhotographerIDs = '';
	$nextPhotographerID = '';
#	Build array of photographer names, emails.
#	photo.photographer_ID - 1, photo.photographer_name - 2, photo.photographer_email - 3 
	$temp = "select photo.photographer_ID as photoID, photo.photographer_name as photographerName, photo.photographer_email as photographerEmail from schedule_photographers as photo order by photo.photographer_ID";
print "SQL photographers ID/name/email ({$temp})\n";
	$data = executeSQLCron($connection, $temp);
#	$max = mysql_num_rows($data);
	$max = mysqli_affected_rows($connection);
#	while ($row = mysql_fetch_array($data)) {
	while ($row = mysqli_fetch_assoc($data)) {
		$currentPhotoArray[$row["photoID"]] = array();
		$currentPhotoArray[$row["photoID"]]["photographerName"] = $row["photographerName"];
		$currentPhotoArray[$row["photoID"]]["photographerEmail"] = $row["photographerEmail"];
	}

#	Spin through assignments with status of 'request' that have exceeded 24 hr accept/decline from photographer
	for ($index = 0; $index < $assignmentMax; $index++) {
		$tempArray = explode("+", $currentAssignmentArray[$index]);
		$currentAssignmentID = $tempArray[1];
		$currentOrganization = $tempArray[4];
		$currentGallery = $tempArray[5];
		$currentDetails = $tempArray[6];
		$currentEditor = $tempArray[7];
		$currentEditorID = $tempArray[8];
		$currentEditorEmail = $tempArray[2];
		$currentPhotographerIDs = $tempArray[3];
		$currentEventDate = $tempArray[9];
		$currentEventDateEnd = $tempArray[10];
		$currentRSVP = $tempArray[11];
		$currentLocation = $tempArray[12];
		$currentContactName = $tempArray[13];
		$currentContactEmail = $tempArray[14];
		$currentContactPhone = $tempArray[15];
print "For each assignment IDs({$currentPhotographerIDs}) ({$currentOrganization}) ({$tempArray[4]}) RSVP({$currentRSVP}) \n";
#		if ($currentOrganization != $tempArray[4]) {
		if ($currentOrganization == $tempArray[4]) {
print "AK106 ({$currentEditorEmail}) ({$currentAssignmentID})\n";
#	Photographer IDs list exhausted/empty.
#	Editor ID:Name:Email:Organization
			if ($currentPhotographerIDs == '') {
print "Photographer list for assignment exhausted. ({$currentGallery})\n";
				$sentStatus = false;
				$MailTo = $currentEditorEmail;
				$MailFrom = $NOREPLY;
				$MailReply= $NOREPLY;
				$MailSubject = "Photographer candidate list exhausted ({$currentGallery})";
				$TO = $MailTo;
				$HEADERS  = "MIME-Version: 1.0 \r\n";
				$HEADERS .= "Content-type: text/html; charset=iso-8859-1 \r\n";
				$HEADERS .= "From: {$MailFrom}";
				$HEADERS .= "\r\nReply-To: {$MailReply}";
				$SUBJECT = $MailSubject;
				$MESSAGE = "Photographer candidate list exhausted. Need to select more potential photographers.<br><br>";
				$temp = base64_encode("&data=editor&data=".$currentEditorID.":".$currentEditor.":".$currentEditorEmail.":".$currentOrganization);
				$MESSAGE .= "<a href='{$TARGET_URL}/isi.schedule.editor.php?data={$temp}'>Access all assignments</a><br>";
				$temp = base64_encode("&data=req&data=delete&data=assignmentid&data=".$currentAssignmentID);
				$MESSAGE .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Delete current assignment</a><br><br>";
#				$MESSAGE .= "Text links - access<br>";
#				$MESSAGE .= "Click link below to access assignments. (Not implemented)<br>";
#				$MESSAGE .= "http://photographer.isiphotos.com/isi.schedule.php<br><br>";
				if ($currentEventDate != $currentEventDateEnd) {
					$MESSAGE .= "Assignment duration: {$currentEventDate} through {$currentEventDateEnd}<br>";
				}
				$MESSAGE .= "Assignment details:<br>";
				$MESSAGE .= "{$currentDetails}<br>";
#
#		Close the pipe, sending the email
#
				$sentStatus = mail($TO,$SUBJECT,$MESSAGE,$HEADERS);
			}
#	Locate current photographer in collection list, and send accept/decline email to next photographer.
			$tempArray = explode(":", $currentPhotographerIDs);
#	When RSVP required, stick with current photographer as opposed to moving down the photographer list
			if ($currentRSVP == 'y') {
				$nextPhotographerID = $tempArray[0];
print "Photographer RSVP ID({$nextPhotographerID})\n";
			} else {
				$nextPhotographerID = $tempArray[1];
				$tempLength = strlen($tempArray[0]);
				$tempArray[0] = '';
				$currentPhotographerIDs = implode(":",$tempArray);
				$currentPhotographerIDs = substr($currentPhotographerIDs,$tempLength);
			}
print "AK007 ID  ({$nextPhotographerID}) ({$currentGallery})\n";
print "AK008 IDs({$currentPhotographerIDs}) Length({$tempLength})\n";
			$tempEditor = $currentPhotoArray[$nextPhotographerID]['photographerEmail'];
print "AK009 ({$nextPhotographerID}) ({$tempEditor}) ({$currentEditorEmail})\n";
			if ($nextPhotographerID != '' && $tempEditor != '') {
print "AK109 ({$nextPhotographerID}) ({$tempEditor}) ({$currentRSVP}) sending accept/decline\n";
			$sentStatus = false;
			$MailTo = $tempEditor;
			$MailFrom = $NOREPLY;
			$MailReply= $NOREPLY;
			$MailSubject = "Accept/Decline assignment ({$currentGallery}).";
			$TO = $MailTo;
			$HEADERS  = "MIME-Version: 1.0 \r\n";
			$HEADERS .= "Content-type: text/html; charset=iso-8859-1 \r\n";
			$HEADERS .= "From: {$MailFrom}";
			$HEADERS .= "\r\nReply-To: {$MailReply}";
			$SUBJECT = $MailSubject;
#			$temp = base64_encode("req=delete&assignid=".$tempArray[2]);
#			$MESSAGE .= "http://photographer.isiphotos.com/isi.schedule.confirm.php?data={$temp}\n";
			$MESSAGE = "{$currentPhotoArray[$nextPhotographerID]['photographerName']},<br><br>";
			$MESSAGE .= "Within 24 hours, use the links below to accept/decline a photo assignment. Thanks.<br>";
			if ($currentRSVP == 'y') {
				$MESSAGE .= "A response, either accept or deny, is requested.<br>";
			}

#	ID List???
#	&data=idlist&data={$IDList}");

			$MESSAGE .= "Assignment editor: {$currentEditor}<br><br>";
			$temp = base64_encode("&data=req&data=accept&data=assignmentid&data={$currentAssignmentID}&data=photographerid&data={$nextPhotographerID}&data=galleryname&data={$currentGallery}");
			$MESSAGE .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Accept assignment</a><br>";
			$temp = base64_encode("&data=req&data=decline&data=assignmentid&data={$currentAssignmentID}&data=photographerid&data={$nextPhotographerID}&data=galleryname&data={$currentGallery}");
			$MESSAGE .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Decline assignment</a><br><br>";
			if ($currentEventDate != $currentEventDateEnd) {
				$MESSAGE .= "Assignment duration:<br>";
				$MESSAGE .= "{$currentEventDate} through {$currentEventDateEnd}<br>";
			}
			$MESSAGE .= "Assignment details:<br>";
			$MESSAGE .= "{$currentDetails}<br><br>";
#	Add Location
			$MESSAGE .= "Location: ";
			$MESSAGE .= "{$inputEventLocation}<br>";
#	Add Contact
			$MESSAGE .= "Contact: ";
			$MESSAGE .= "{$inputEventContactName}<br>";
			$MESSAGE .= "Contact email: ";
			$MESSAGE .= "{$inputEventContactEmail}<br>";
			$MESSAGE .= "Contact phone: ";
			$MESSAGE .= "{$inputEventContactPhone}<br>";
			$MESSAGE .= "<br>Use the links below after first image upload and after final image upload.<br>";
#			$temp = base64_encode("&data=req&data=upload&data=assignmentid&data={$currentAssignmentID}");
#			$MESSAGE .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Flag images uploaded</a><br><br>";
			$temp = base64_encode("&data=req&data=upload&data=assignmentid&data={$currentAssignmentID}&data=finalUpload&data=n");
			$MESSAGE .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Update: First upload complete</a><br>";
			$temp = base64_encode("&data=req&data=upload&data=assignmentid&data={$currentAssignmentID}&data=finalUpload&data=y");
			$MESSAGE .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Update: Final upload complete</a><br><br>";
#			$MESSAGE .= "<a href='http://photographer.isiphotos.com/isi.schedule.confirm.php?req=accept&aid={$currentAssignmentID}&pid={$nextPhotographerID}'>Accept assignment</a><br><br>";
#			$MESSAGE .= "<a href='http://photographer.isiphotos.com/isi.schedule.confirm.php?req=decline&aid={$currentAssignmentID}&pid={$nextPhotographerID}'>Decline assignment</a><br><br>";
print "Review Location ++++ ({$MESSAGE})\n";
#
#		Close the pipe, sending the email
#
			$sentStatus = mail($TO,$SUBJECT,$MESSAGE,$HEADERS);
			if ($sentStatus) {
				print "Accept/Decline sent to ({$tempEditor}) for ({$currentGallery})\n";
			} else {
				print "Accept/Decline NOT ({$tempEditor}) for ({$currentGallery})\n";
			}
			}

#	Update assignment with updated list of photographer IDs, and update the date/time
#mysqli_affected_rows() expects parameter 1 to be mysqli, resource given in /nfs/c09/h01/mnt/139088/data/cron/isi.schedule.cron.php on line 175
#error: c001: Assignment update (142) failed ().
#	$temp = sprintf("select photo.photographer_ID as photoID, photo.photographer_name as photographerName from schedule_collectionphotographers as collection join schedule_photographers as photo on collection.league_photographerID = photo.photographer_ID WHERE collection.league_collectionID = '%s' order by collection.league_photographer_points", $currentOrganization);
		$tempStatus = $STATUS_REQUEST;
		if ($currentPhotographerIDs == '') { $tempStatus = $STATUS_INITIAL; }
		$temp = sprintf("update schedule_assignments set assign_photographerIDs = '%s', assign_status = '%s', assign_statusdate = NOW() where assign_ID = '%s'", $currentPhotographerIDs, $tempStatus, $currentAssignmentID);
print "SQL update photographer list in assignment ({$temp})\n";
		$data = executeSQLCron($connection, $temp);
#		$max = mysql_affected_rows($data);
		$max = mysqli_affected_rows($connection);
		if ($max != 1) { print "error: c001: Assignment update ({$currentAssignmentID}) failed ()."; exit; }
			
print "\n\n\n\n";
		}
	}

#	Every 12 hours? send email reminder about upload of images.
#SELECT TIMESTAMPDIFF(HOUR, `assign_date`, NOW()) as 'difference', (TIMESTAMPDIFF(HOUR, `assign_date`, NOW())%18) as 'remindTime', NOW(), `assign_date` , `assign_photographerID` as 'photoID' FROM `schedule_assignments` WHERE NOW() > `assign_date`
#SELECT TIMESTAMPDIFF(HOUR, `assign_date`, NOW()) as 'difference', (TIMESTAMPDIFF(HOUR, `assign_date`, NOW())%18) as 'remindTime', NOW(), `assign_date` , `assign_photographerID` as 'photoID' FROM `schedule_assignments` WHERE NOW() > `assign_date` and (TIMESTAMPDIFF(HOUR, `assign_date`, NOW())%18) = 10
#SELECT TIMESTAMPDIFF(HOUR, assign.assign_date, NOW()) as 'difference', (TIMESTAMPDIFF(HOUR, assign.assign_date, NOW())%18) as 'remindTime', NOW(), assign.assign_date , assign.assign_photographerID as 'photoID', photo.photographer_email as 'photoEmail' FROM schedule_assignments as assign join schedule_photographers as photo on assign.assign_photographerID = photo.photographer_ID WHERE NOW() > assign.assign_date and (TIMESTAMPDIFF(HOUR, assign.assign_date, NOW())%18) > 0
#	Change from assign.assign_status != 'upload' to = 'assigned'
	$query = "SELECT TIMESTAMPDIFF(HOUR, assign.assign_date, NOW()) as 'difference', (TIMESTAMPDIFF(HOUR, assign.assign_date, NOW()) % {$REMINDINTERVAL}) as 'remindTime', NOW(), assign.assign_date, assign.assign_deadline as 'deadline', assign.assign_ID as 'assignID', assign.assign_gallery, photo.photographer_email as 'photoEmail', photo.photographer_ID as 'photoID' FROM schedule_assignments as assign join schedule_photographers as photo on assign.assign_photographerIDs = photo.photographer_ID WHERE NOW() > assign.assign_date and assign.assign_status = '{$STATUS_ASSIGNED}' ";
print "SQL upload reminders (No time) ({$query})\n";
	$data = executeSQLCron($connection, $query);
	$max = mysqli_affected_rows($connection);
print "Count({$max}) Assignments reminders DIAGNOSTICS.\n";
print "Each assignment 'deadline':'difference':'remindTime':'photoEmail':'assign_gallery'\n";
	if ($max > 0) {
		while ($row = mysqli_fetch_assoc($data)) {
print "Each assignment ({$row['deadline']}:{$row['difference']}:{$row['remindTime']}:{$row['photoEmail']}:{$row['assign_gallery']})\n";
print_r($row);
		}
	}


#	$query = "SELECT TIMESTAMPDIFF(HOUR, assign.assign_date, NOW()) as 'difference', (TIMESTAMPDIFF(HOUR, assign.assign_date, NOW()) % {$REMINDINTERVAL}) as 'remindTime', NOW(), assign.assign_date, assign.assign_deadline as 'deadline', assign.assign_ID as 'assignID', assign.assign_gallery, photo.photographer_email as 'photoEmail', photo.photographer_ID as 'photoID' FROM schedule_assignments as assign join schedule_photographers as photo on assign.assign_photographerIDs = photo.photographer_ID WHERE NOW() > assign.assign_date and (TIMESTAMPDIFF(HOUR, assign.assign_date, NOW())%{$REMINDINTERVAL}) = 3 and assign.assign_status = '{$STATUS_ASSIGNED}' ";
	$query = "SELECT TIMESTAMPDIFF(HOUR, assign.assign_date, NOW()) as 'difference', (TIMESTAMPDIFF(HOUR, assign.assign_date, NOW()) % {$REMINDINTERVAL}) as 'remindTime', NOW(), assign.assign_date, assign.assign_deadline as 'deadline', assign.assign_ID as 'assignID', assign.assign_gallery, photo.photographer_email as 'photoEmail', photo.photographer_ID as 'photoID' FROM schedule_assignments as assign join schedule_photographers as photo on assign.assign_photographerIDs = photo.photographer_ID WHERE NOW() > assign.assign_date and  assign.assign_status = '{$STATUS_ASSIGNED}' ";
print "SQL upload reminders (after {$REMINDINTERVAL} hours) ({$query})\n";
	$data = executeSQLCron($connection, $query);
	if ($data === false) { print "error: c001: Image download SQL failed ()."; exit; }
#	$max = mysql_num_rows($data);
	$max = mysqli_affected_rows($connection);
print "Count({$max}) Assignments reminders.\n";
	if ($max > 0) {
#		while ($row = mysql_fetch_array($data)) {
		while ($row = mysqli_fetch_assoc($data)) {
print "Each assignment ({$row['deadline']}:{$row['difference']}:{$row['remindTime']}:{$row['photoEmail']}:{$row['assign_gallery']})\n";
			$tempEditor = $row['photoEmail'];
			$sentStatus = false;
			$MailTo = $tempEditor;
			$MailFrom = $NOREPLY;
			$MailReply= $NOREPLY;
			$MailSubject = "Upload reminder. Event: {$row['assign_gallery']}";
$testDifference = (int)$row['difference'];
$testDeadline = (int)$row['deadline'];
print "AK300 Difference({$testDifference}) Deadline({$testDeadline})<br>";
$testDeadline += 24;
print "AK301 Deadline({$testDeadline})<br>";
			
#if ($row['difference'] >= $row['deadline']) {
if ($testDifference >= $testDeadline) {
print "AK305 Deadline passed ++++++++ Difference({$row['difference']}) Deadline({$row['deadline']})\n";

			$TO = $MailTo;
			$HEADERS  = "MIME-Version: 1.0 \r\n";
			$HEADERS .= "Content-type: text/html; charset=iso-8859-1 \r\n";
			$HEADERS .= "From: {$MailFrom}";
			$HEADERS .= "\r\nReply-To: {$MailReply}";
			$SUBJECT = $MailSubject;
			$MESSAGE = "Images from {$row['assign_gallery']} need to be uploaded.<br><br>";
			$MESSAGE .= "Use the links below after first image upload and after final image upload.<br>";
#			$temp = base64_encode("&data=req&data=upload&data=assignmentid&data={$row['assignID']}&data=photographerid&data={$row['photoID']}");
#			$MESSAGE .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Flag as images uploaded</a><br><br>";
			$temp = base64_encode("&data=req&data=upload&data=assignmentid&data={$row['assignID']}&data=finalUpload&data=n");
print "Email confirm FIRST link. ({$TARGET_URL}/isi.schedule.confirm.php?data={$temp})\n";
			$MESSAGE .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Update: First upload complete</a><br>";
			$temp = base64_encode("&data=req&data=upload&data=assignmentid&data={$row['assignID']}&data=finalUpload&data=y");
print "Email confirm FINAL link. ({$TARGET_URL}/isi.schedule.confirm.php?data={$temp})\n";
			$MESSAGE .= "<a href='{$TARGET_URL}/isi.schedule.confirm.php?data={$temp}'>Update: Final upload complete</a><br><br>";
#			$MESSAGE .= "<a href='http://photographer.isiphotos.com/isi.upload.php'>Link to ISI Uploader</a><br><br>";
#			$MESSAGE .= "Text links - delete<br>";
#			$MESSAGE .= "Click link below to indicate images uploaded.<br>";
#			$MESSAGE .= "http://photographer.isiphotos.com/isi.upload.php<br>";
#			$MESSAGE .= "Click link below to access ISI Uploader.<br>";
#			$MESSAGE .= "http://photographer.isiphotos.com/isi.upload.php<br>";
#
#		Close the pipe, sending the email
#
			$sentStatus = mail($TO,$SUBJECT,$MESSAGE,$HEADERS);
			if ($sentStatus) {
				print "Reminder Sent to ({$tempEditor}) for ({$row['assign_gallery']}).\n";
			} else {
				print "Reminder FAILED to ({$tempEditor}) for ({$row['assign_gallery']}).\n";
			}
# Diff + 24
}
		}
	}

#	Assignments ready/need MEID from Getty?
#	assign.assign_date desc, assign.assign_gallery, assign.assign_location, assign.assign_ID
	$query = "select assign_id as 'assignID', assign.assign_gallery as 'galleryName', assign.assign_location as 'location', assign.assign_status as 'status', DATE_FORMAT(assign.assign_date, '%c/%e/%Y') as 'eventDate', assign.assign_meid as 'assignMEIDFlag', assign.assign_meidID as 'assignMEID' FROM schedule_assignments as assign where assign.assign_meid = 'y' && assign.assign_meidID = '' order by  assign.assign_date desc, assign.assign_gallery, assign.assign_location, assign.assign_ID";
#print "Getty MEID? SQL ({$query})\n";
	$data = executeSQLCron($connection, $query);
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
			print "&nbsp;&nbsp;&nbsp;Email sent to Getty ({$GETTY_EMAIL}).\n";
		} else {
			print "&nbsp;&nbsp;&nbsp;Email to Getty ({$GETTY_EMAIL}) failed.\n";
		}
		print "Getty MEID URL ({$TARGET_URL}/isi.schedule.getty.php?data={$temp})\n";
	}	


print "Done\n";
#	Close the database
	mysqli_close($connection);

exit;

#
#	Database error display
#
function showerrorCron() {
#	die("Error " . mysql_errno() . " : " . mysql_error() );
	die("Error " . mysqli_errno() . " : " . mysqli_error() );
}

#
#	Open DB
#
function openDBCron() {
#	Connect to database
#	if (!($temp = @mysql_connect("external-db.s139088.gridserver.com:3306", "db139088_isi", "38941059ISI#ak") )) {
#	if (!($temp = @mysqli_connect("external-db.s139088.gridserver.com:3306", "db139088_isi", "38941059ISI#ak","db139088_isiportal") )) {
#	if (!($temp = mysqli_connect("mysql.server285.com:3306", "db139088isi", "38941059ISI#ak","akaction_portal") )) {
	if (!($temp = mysqli_connect("db5000560965.hosting-data.io", "dbu933976", "Pester#32","dbs538560") )) {
		die("Error " . mysqli_connect_errno() . " : " . mysqli_connect_error() );
#		showerrorCron();
	}
	return $temp;
}

#
#	Execute SQL
#
function executeSQLCron($inputConnection, $inputQuery) {
	$result = mysqli_query($inputConnection, $inputQuery);
#	$result = mysql_query($inputQuery);
	if (!$result) {
		print "SQL failed ({$inputQuery})\n";
		return false;
	}
	return $result;
}


?>