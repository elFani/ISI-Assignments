//
//	ISI Schedule related functions and constants
//	Google calendar API
//	ISI Schedule database access
//

//	Eventually remove all Google calendar logic
//	Google calendar API
//	Google calendar and credentials based on league/collection.
//	For Cal/Stanford, there are sub-collections, but use only parent collection ID.

var google_apiKEY = '';
var google_clientID = '';
var google_calendarID = '';
//	ISI Schedule Test calendar ID: hgvrs9579atqsh82ldsk2kni2s@group.calendar.google.com
//	Each google calendar event has an id. Need the id to update/patch the event.
//	Stored in the database.
var google_eventID = '';
//	11 red to 6 orange to 10 green
var google_color = 0;
var google_photographer = '';
var google_gallery = '';

//	Javascript version
var jsVersion = '1.20';

//	Assignments have a database ID
var assignID = '';
var includePhotographerID = '';
var includeElapsedHours = 0;
var includeEditorEmail = '';

//	API related
//var APITARGET_PREFIX = "http://[ID]:[PW]@photographer.isiphotos.com";
var APITARGET_PREFIX = "https://assignments.isiphotos.com";

//	Time select box
	var timeText = new Object();
	timeText['00:00'] = '0:00 AM';
	timeText['01:00'] = '1:00 AM';
	timeText['02:00'] = '2:00 AM';
	timeText['03:00'] = '3:00 AM';
	timeText['04:00'] = '4:00 AM';
	timeText['05:00'] = '5:00 AM';
	timeText['06:00'] = '6:00 AM';
	timeText['07:00'] = '7:00 AM';
	timeText['08:00'] = '8:00 AM';
	timeText['09:00'] = '9:00 AM';
	timeText['10:00'] = '10:00 AM';
	timeText['11:00'] = '11:00 AM';
	timeText['12:00'] = '12:00 PM';
	timeText['13:00'] = '1:00 PM';
	timeText['14:00'] = '2:00 PM';
	timeText['15:00'] = '3:00 PM';
	timeText['16:00'] = '4:00 PM';
	timeText['17:00'] = '5:00 PM';
	timeText['18:00'] = '6:00 PM';
	timeText['19:00'] = '7:00 PM';
	timeText['20:00'] = '8:00 PM';
	timeText['21:00'] = '9:00 PM';
	timeText['22:00'] = '10:00 PM';
	timeText['23:00'] = '11:00 PM';


//	ISI Schedule database access
//	For a league/collection there is associated Google calendar.
//	Using collection ID, get google apikey, clientID, and calendar ID from DB.
function getCalendarData(inputCollectionID) {
if (isConsoleTrace) { console.log("getCalendarData ("+inputCollectionID+")"); }
//	Reset
	google_apiKEY = '';
	google_clientID = '';
	google_calendarID = '';
	
//	Disable google calendar interface
//	return;	
	
	tempData = new Array(inputCollectionID);
//	API request updates apikey, ...
	apiSQL('getCalendarData', tempData);
	return;
}

//	Insert assignment into database.
//	Database table for assignment needs:
//	eventID, collectionID, collectionName,gallery name,event date, photographerID, photographerName)
//	Index 0,       1,        2,              3,          4,         5,                6,            
//	Google string, PhotoShelter string, string, string, string: yyyy-mm-dd, string: hh:mm:00, string: hh:mm:00, string, string
//	May 2020
//	Drop, Drop, collectionName,gallery name,event date, empty, empty)
//	Index 0,       1,        2,              3,          4,         5,                6,            
//	Google string, PhotoShelter string, string, string, string: yyyy-mm-dd, string: hh:mm:00, string: hh:mm:00, string, string
function insertAssignmentDB(inputOrganizationName, inputGalleryName, inputEventDate, inputEventDuration, inputEventStart, inputEventEnd, inputCategory, inputDetails, inputCount, inputLocation, inputContact, inputContactEmail, inputContactPhone, inputRequestor, inputRequestorEmail) {
	tempData = new Array(inputOrganizationName, inputGalleryName, inputEventDate, inputEventDuration, inputEventStart, inputEventEnd, inputCategory, inputDetails, inputCount, inputLocation, inputContact, inputContactEmail, inputContactPhone, inputRequestor, inputRequestorEmail);
	assignID = '';
	apiSQL('insertAssignment', tempData);
	return;
}

//	Delete an assignment from database
//	Using assignID delete assignment based on ID.
function deleteAssignmentDB(inputAssignID, statusObject) {
if (isConsoleTrace) { console.log("deleteAssignmentDB ("+inputAssignID+")"); }
	tempData = new Array(inputAssignID);
//	API request updates apikey, ...
	apiSQL('deleteAssignment', tempData, statusObject);
	return;
}

//	Based on gallery name, get the assignment for google calendar eventID.
//	Using collection ID, get google apikey, clientID, and calendar ID from DB.
function getAssignment(inputGallery) {
if (isConsoleTrace) { console.log("getAssignment ("+inputGallery+")"); }
//	Reset
	assignID = '';
	google_eventID = '';
	includePhotographerID = '';
	google_photographer = '';
	includeElapsedHours = 0;
	tempData = new Array(inputGallery);
//	API request updates apikey, ...
	apiSQL('getAssignment', tempData);
	return;
}

//	Update assignment with status of 'upload.'
//	Using assignID get assignment based on ID.
function updateAssignment(inputAssignID, inputCollectionID, inputEditorEmail, inputPhotographerID, inputPhotographerName, inputGalleryName, inputElapsedHours) {
if (isConsoleTrace) { console.log("updateAssignment ("+inputAssignID+")"); }
	tempData = new Array(inputAssignID, inputCollectionID, inputEditorEmail, inputPhotographerID, inputPhotographerName, inputGalleryName, inputElapsedHours);
//	API request updates apikey, ...
	apiSQL('updateAssignment', tempData);
	return;
}

//	Update details/delivery method in assignment.
//	Using assignID get assignment based on ID.
function updateDetails(inputAssignID, tempDelivery, inputDetails, inputMEIDFlag, inputMEID, inputStatus, statusObject) {
if (isConsoleTrace) { console.log("updateDetails ("+inputAssignID+")"); }
	tempData = new Array(inputAssignID, tempDelivery, inputDetails, inputMEIDFlag, inputMEID, inputStatus);
//	API request updates apikey, ...
	apiSQL('updateDetails', tempData, statusObject);
	return;
}

//	Assign requestor to assignment and accept
//	assignRequestor(inputAssignID, inputGallery, inputEditorID, inputRequestor, tempDelivery, tempMEID, tempDetails, statusObject);
function assignRequestor(inputAssignID, inputGallery, inputEditorID, inputRequestor, inputDelivery, inputMEID, inputDetails, statusObject) {
if (isConsoleTrace) { console.log("assignRequestor ("+inputAssignID+")"); }
	tempData = new Array(inputAssignID, inputGallery, inputEditorID, inputRequestor, inputDelivery, inputMEID, inputDetails, statusObject);
//	API request updates apikey, ...
	apiSQL('assignRequestor', tempData, statusObject);
	return;
}

//	Editor accepts assignment for photographer
//	acceptAssignment(inputID, inputGallery, inputPhotoID, inputPhotoName, statusObject);
function acceptAssignment(inputAssignID, inputGallery, inputPhotoID, inputPhotoName, statusObject) {
if (isConsoleTrace) { console.log("acceptAssignment ("+inputAssignID+")"); }
	tempData = new Array(inputAssignID, inputGallery, inputPhotoID, inputPhotoName, statusObject);
//	API request updates apikey, ...
	apiSQL('acceptAssignment', tempData, statusObject);
	return;
}

//	Editor declines assignment for photographer
//	declineAssignment(inputID, inputGallery, inputPhotoID, inputPhotoName, statusObject);
function declineAssignment(inputAssignID, inputGallery, inputPhotoID, inputPhotoName, statusObject) {
if (isConsoleTrace) { console.log("declineAssignment ("+inputAssignID+")"); }
	tempData = new Array(inputAssignID, inputGallery, inputPhotoID, inputPhotoName, statusObject);
//	API request updates apikey, ...
	apiSQL('declineAssignment', tempData, statusObject);
	return;
}

//	Called from Editor "dashboard", not ISI Uploader
//	Editor flag assignment as images uploaded
//	flagAssignment(inputID, statusObject);
//	flagAssignment(inputID, inputFinal, statusObject);
function flagAssignment(inputAssignID, inputFinal, statusObject) {
if (isConsoleTrace) { console.log("flagAssignment ("+inputAssignID+")"); }
	tempData = new Array(inputAssignID, inputFinal, statusObject);
//	API request updates apikey, ...
	apiSQL('flagAssignment', tempData, statusObject);
	return;
}

//	API call, get photographer list from database.
//	List is photographers previously used for a collection and new candidates from pool (new).
function STALE_getPhotographers(inputCollectionID) {
if (isConsoleTrace) { console.log("getPhotographers"); }
	tempData = new Array(inputCollectionID);
	apiSQL('getPhotographers', tempData);
}

//	API call, update photographer list for an assignment.
//	inputPhotographerList (IDs) = i:k:l:m:n ...
function updatePhotographers(inputAssignID, inputGallery, inputEditorID, inputPhotographerList, inputEditor, inputEditorEmail, inputDetails, inputDelivery, inputDeadline, inputRSVP, inputMEID, inputIDList, inputEventDate, inputEventDateEnd, inputEventTime, inputEventLocation, inputContactName, inputContactEmail, inputContactPhone, statusObject) {
if (isConsoleTrace) { console.log("updatePhotographers ("+inputAssignID+") ("+inputEditorID+") ("+inputPhotographerList+") ("+inputDelivery+") ("+inputDeadline+")"); }
	tempData = new Array(inputAssignID, inputGallery, inputEditorID, inputPhotographerList, inputEditor, inputEditorEmail, inputDetails, inputDelivery, inputDeadline, inputRSVP, inputMEID, inputIDList, inputEventDate, inputEventDateEnd, inputEventTime, inputEventLocation, inputContactName, inputContactEmail, inputContactPhone);
	temp = apiSQL('updatePhotographers', tempData, statusObject);
if (isConsoleTrace) { console.log("updatePhotographers status: ("+temp+")"); }
	return temp;
}

//	API call, get existing requests from database
//	List is requests for a calendar/collection.
function getRequests(inputEditorID, inputCollectionID) {
if (isConsoleTrace) { console.log("getRequests"); }
	tempData = new Array(inputEditorID, inputCollectionID);
	apiSQL('getRequests', tempData);
}

//	API call, send email reminder to upload images to photographer
//	sendEmailReminder(inputID, inputGallery, inputPhotoID, inputPhotoName, inputPhotoEmail, statusObject);
function sendEmailReminder(inputAssignID, inputGallery, inputPhotoID, inputPhotoName, inputPhotoEmail, inputFinal, statusObject) {
	tempData = new Array(inputAssignID, inputGallery, inputPhotoID, inputPhotoName, inputPhotoEmail, inputFinal);
//	API request updates apikey, ...
	apiSQL('sendEmailReminder', tempData, statusObject);
}

//	API call, send email to editor/photographer
//
function apiEmailSend(inputAssignID, inputEditorID, inputPhotographerArray, inputMessage, inputDetails) {
	tempArray = inputPhotographerArray.split(":");
if (isConsoleTrace) { console.log("apiEmailSend Assign("+inputAssignID+") Editor("+inputEditorID+") Photo("+tempArray[0]+") Msg("+inputMessage+") Details("+inputDetails+")"); }
	tempData = new Array(inputAssignID, inputEditorID, tempArray[0], inputMessage, inputDetails);
	apiSQL('sendEmail', tempData);
}

//	API call, get editor/assigner ID
function apiGetEditorID(inputEditorName) {
	tempData = new Array(inputEditorName);
	apiSQL('getEditorID', tempData);
}

//	Generate valid Getty MEID email
function gettyMEID(statusObject) {
	tempData = new Array();
	apiSQL('gettyMEID', tempData, statusObject);
}

//	Notify Getty of images for an MEID
//	GettyNotify(inputAssignID, inputGallery, tempMEID, tempImageCount, statusObject);
function GettyNotify(inputAssignID, inputGallery, inputMEID, inputCount, statusObject) {
if (isConsoleTrace) { console.log("GettyNotify ("+inputAssignID+") MEID("+inputMEID+")"); }
	tempData = new Array(inputAssignID, inputGallery, inputMEID, inputCount, statusObject);
//	API request updates apikey, ...
	apiSQL('gettyNotify', tempData, statusObject);
	return;
}

//	API SQL to/from database
function apiSQL(inputRequest, inputData, statusObject) {
	if (isConsoleTrace) { console.log("apiSQL request("+inputRequest+")"); }
//	Reset

//	Prepare URL, parameters to call API.
//	Insert assignment into the database.
// apiTarget = 'http://www.akactionphoto.com/isi.debug.php';
//	tempData = new Array(inputOrganizationName, inputGalleryName, inputEventDate, inputEventStart, inputEventEnd, inputDetails, inputCount, inputLocation, inputContact, inputContactEmail, inputContactPhone);
	apiTarget = APITARGET_PREFIX + '/includes/isi.schedule.tools.php';
	if (inputRequest == 'insertAssignment') {
//	Insert assignment
	var tempNameO = inputData[0];
	tempNameO = tempNameO.replace('&', '%26');
	var tempNameG = inputData[1];
	tempNameG = tempNameG.replace('&', '%26');
	apiParms = new Object();
	apiParms['req']	= 'dbi' ;
	apiParms['ofn']	= tempNameO;
	apiParms['gfn']	= tempNameG;
	apiParms['ed']	= inputData[2];
	apiParms['dur']	= inputData[3];
	apiParms['ts']	= inputData[4];
	apiParms['te']	= inputData[5];
	apiParms['ic']	= inputData[6];
	apiParms['det']	= inputData[7];
	apiParms['pc']	= inputData[8];
	apiParms['loc']	= inputData[9];
	apiParms['cn']	= inputData[10];
	apiParms['ce']	= inputData[11];
	apiParms['cp']	= inputData[12];
	apiParms['rn']	= inputData[13];
	apiParms['re']	= inputData[14];
	}
//	Delete assignment
	if (inputRequest == 'deleteAssignment') {
	apiParms = new Object();
	apiParms['req']	= 'dbd';
	apiParms['aid']	= inputData[0];
	}
//	Get Google calendar access data
	if (inputRequest == 'getCalendarData') {
	apiParms = new Object();
	apiParms['req']	= 'dbgc';
	apiParms['cid']	= inputData[0];
	}
//	Get assignment data
	if (inputRequest == 'getAssignment') {
	var tempNameG = inputData[0];
	tempNameG = tempNameG.replace('&', '%26');
	apiParms = new Object();
	apiParms['req']	= 'dbga';
	apiParms['gfn']	= tempNameG;
	}
//	Update assignment 
//	updateAssignment(inputAssignID, inputCollectionID, inputEditorEmail, inputPhotographerID, inputPhotographerName, inputGalleryName, inputElapsedHours) {
	if (inputRequest == 'updateAssignment') {
	var tempNameG = inputData[5];
	tempNameG = tempNameG.replace('&', '%26');
	apiParms = new Object();
	apiParms['req']	= 'dbua';
	apiParms['aid'] = inputData[0];
	apiParms['cid'] = inputData[1];
	apiParms['edit'] = inputData[2];
	apiParms['pf']	= inputData[3];
	apiParms['pfn']	= inputData[4];
	apiParms['eh']	= inputData[6];
	apiParms['gfn']	= tempNameG;
	}
//	Update assignment details and delivery method
//	updateDetails(inputID, tempDelivery, tempDetails, tempMEID, statusObject);
	if (inputRequest == 'updateDetails') {
	apiParms = new Object();
	apiParms['req']	= 'dbuadm';
	apiParms['aid'] = inputData[0];
	apiParms['dm']	= inputData[1];
	apiParms['det']	= inputData[2];
	apiParms['me']  = inputData[3];
	apiParms['meid']= inputData[4];
	apiParms['status']= inputData[5];
	}
//	Assign requestor to assignment and accept
//	assignRequestor(inputAssignID, inputGallery, inputEditorID, inputRequestor, tempDelivery, tempMEID, tempDetails, statusObject);
	if (inputRequest == 'assignRequestor') {
	var tempNameG = inputData[1];
	tempNameG = tempNameG.replace('&', '%26');
	apiParms = new Object();
	apiParms['req']	= 'dbar';
	apiParms['aid'] = inputData[0];
	apiParms['gfn']	= tempNameG;
	apiParms['eid']	= inputData[2];
	apiParms['pfn']	= inputData[3];
	apiParms['dm']	= inputData[4];
	apiParms['me']	= inputData[5];
	apiParms['det']	= inputData[6];
	}	
//	Editor accepts assignment for photographer
//	acceptAssignment(inputID, inputGallery, inputPhotoID, inputPhotoName, statusObject);
//	tempData = new Array(inputAssignID, inputGallery, inputPhotoID, inputPhotoName, statusObject);
	if (inputRequest == 'acceptAssignment') {
	apiParms = new Object();
	apiParms['req']	= 'accept';
	apiParms['aid'] = inputData[0];
	apiParms['gfn']	= inputData[1];
	apiParms['pf']	= inputData[2];
	apiParms['pfn']	= inputData[3];
	}
//	Editor declines assignment for photographer
//	declineAssignment(inputID, inputGallery, inputPhotoID, inputPhotoName, statusObject);
//	tempData = new Array(inputAssignID, inputGallery, inputPhotoID, inputPhotoName, statusObject);
	if (inputRequest == 'declineAssignment') {
	apiParms = new Object();
	apiParms['req']	= 'decline';
	apiParms['aid'] = inputData[0];
	apiParms['gfn']	= inputData[1];
	apiParms['pf']	= inputData[2];
	apiParms['pfn']	= inputData[3];
	}
//	Editor flag assignment as images uploaded
//	flagAssignment(inputID, inputFinal, statusObject);
//	tempData = new Array(inputAssignID, inputFinal, statusObject);
	if (inputRequest == 'flagAssignment') {
	apiParms = new Object();
	apiParms['req']	= 'flag';
	apiParms['aid'] = inputData[0];
	apiParms['final'] = inputData[1];
	}
	
//	Get list of appropriate photographers
	if (inputRequest == 'getPhotographers') {
	apiParms = new Object();
	apiParms['req']	= 'dbp';
	apiParms['cid']	= inputData[0];
	}
//	Get list of appropriate photographers
//	Update assignment with editor, photographer list, details, delivery mode, and deadline.
	if (inputRequest == 'updatePhotographers') {
	var tempNameG = inputData[1];
	tempNameG = tempNameG.replace('&', '%26');
	apiParms = new Object();
	apiParms['req']	= 'dbup';
	apiParms['aid']	= inputData[0];
	apiParms['gfn']	= tempNameG;
	apiParms['eid']	= inputData[2];
	apiParms['pids'] = inputData[3];
	apiParms['en']	= inputData[4];
	apiParms['ef']	= inputData[5];
	apiParms['det']	= inputData[6];
	apiParms['dm']	= inputData[7];
	apiParms['dd']	= inputData[8];
	apiParms['me']	= inputData[10];
	apiParms['idl']	= inputData[11];
	apiParms['ed']	= inputData[12];
	apiParms['ede']	= inputData[13];
	apiParms['et']	= inputData[14];
	apiParms['rs']	= inputData[9];
	apiParms['elocation']		= inputData[15];
	apiParms['econtactname']	= inputData[16];
	apiParms['econtactemail']	= inputData[17];
	apiParms['econtactphone']	= inputData[18];
	}
//	Get list of requests for editor/calendar/collection
	if (inputRequest == 'getRequests') {
	apiParms = new Object();
	apiParms['req']	= 'dbr';
	apiParms['edit']= inputData[0];
	apiParms['cid']	= inputData[1];
	}
//	Send email reminder to upload images to photographer\
//	sendEmailRemind(inputID, inputGallery, inputPhotoID, inputPhotoName, inputPhotoEmail, inputFinal (First 'n', Final 'y'), statusObject);
	if (inputRequest == 'sendEmailReminder') {
//	var tempNameG = inputData[1];
//	tempNameG = tempNameG.replace('&', '%26');
	apiParms = new Object();
	apiParms['req']	= 'er';
	apiParms['aid']	= inputData[0];
	apiParms['gfn']	= inputData[1];
	apiParms['pf']	= inputData[2];
	apiParms['pfn']	= inputData[3];
	apiParms['pfe']	= inputData[4];
	apiParms['final'] = inputData[5];
	}
//	Send an email
	if (inputRequest == 'sendEmail') {
	apiParms = new Object();
	apiParms['req']	= 'e';
	apiParms['aid']	= inputData[0];
	apiParms['edit']= inputData[1];
	apiParms['pf']	= inputData[2];
	apiParms['msg']	= inputData[3];
	apiParms['det']	= inputData[4];
	}
//	Get editor ID
	if (inputRequest == 'getEditorID') {
	apiParms = new Object();
	apiParms['req']	= 'geid';
	apiParms['en']	= inputData[0];
	}
//	Contact Getty with MEID request
	if (inputRequest == 'gettyMEID') {
	apiParms = new Object();
	apiParms['req']	= 'meid';
	}
	
//	Notify Getty of images for an MEID
//	GettyNotify(inputAssignID, inputGallery, inputMEID, inputCount, statusObject) {
	if (inputRequest == 'gettyNotify') {
//	var tempNameG = inputData[1];
//	tempNameG = tempNameG.replace('&', '%26');
	apiParms = new Object();
	apiParms['req']	= 'gn';
	apiParms['aid']	= inputData[0];
	apiParms['gfn']	= inputData[1];
	apiParms['meid']= inputData[2];
	apiParms['mec'] = inputData[3];
	}
	
//	Build URL
//temp = apiParms.join("&");
//temp = encodeURI(temp);
//apiURL = apiTarget + '?' + temp;
	if (isConsoleTrace) { console.log("apiSQL URL("+apiTarget+")"); }
//	Call API with dataType: 'json'
//ajaxObject = $.ajax({
//	url : apiURL,
//	dataType: 'json'
//	});
ajaxObject = $.ajax(apiTarget, {
    type: 'POST',
	dataType: 'json',
    data: apiParms,
    error: function (jqXhr, textStatus, errorMessage) {
            console.log('Error: ' + errorMessage);
    }
});

//	Done/Success event
ajaxObject.success(function(dataObject) {
//	temp = dataObject;
//	Insert google calendar event complete
//	Insert schedule assignment
	if (inputRequest == 'insertAssignment') {
		assignID = dataObject.lastInsertID;
		temptype = typeof(assignID);
		tempColor = "#5cb85c";
		tempMessage = "Assignment created ("+assignID+"). Change teams/date for another assignment.";
		if (temptype == 'undefined') {
			tempColor = '#FF0000';
			tempMessage = "Failed to create assignment. Resolution? Shift-reload the page and retry.";
		}
//	Indicate event confirmation complete, near the button
		tempObject = document.getElementById('isi_create');
		tempObject.style.display = "inline";
		tempObject = document.getElementById('eventstatuslabel');
//		tempObject.textContent = "Assignment created. Change teams/date for another assignment.";
		tempObject.textContent = tempMessage;
		tempObject.style.color = tempColor;
//	There is no table on isi.schedule.request page.
//		tempTableObject = document.getElementById('statusTable');
//		indicateAssignment(tempTableObject, 'successful');
//		Send emails to editor and potential photographer
//		There is no editor and there is no photographer.
//		apiEmailSend(assignID, inputData[7], inputData[8], tempNameG, inputData[6]);
		return;
	}
//	Delete schedule assignment
	if (inputRequest == 'deleteAssignment') {		 
		if (dataObject.status == "Done") {
			console.log("Assignment("+inputData[0]+") deleted.");
			tempColor = '#5cb85c';
		} else {
			console.log("Assignment("+inputData[0]+") delete failed.");
			tempColor = '#FF0000';
		}
		statusObject.innerHTML = "<font color='"+tempColor+"'>"+dataObject.status+"</font>";
		return;
	}
//	Google calendar access data retrieved
	if (inputRequest == 'getCalendarData') {
		if (dataObject.status == "calendar data") {
			google_apiKEY = dataObject.apikey;
			google_clientID = dataObject.clientID;
			google_calendarID = dataObject.gid;
		} else {
			console.log("No calendar for "+inputData[0]);
		}
		return;
	}
//	Assignment data retrieved
	if (inputRequest == 'getAssignment') {
		if (dataObject.status == "assignment data") {
			assignID = dataObject.assignID;
			google_eventID = dataObject.eventID;
			includePhotographerID = dataObject.photoID;
			google_photographer = dataObject.photoName;
			includeElapsedHours = dataObject.difference;
			includeEditorEmail = dataObject.editorEmail;
		} else {
			console.log("No assignment for "+inputData[0]);
		}
		return;
	}
//	Assignment data retrieved
	if (inputRequest == 'updateAssignment') {
		if (dataObject.status == "done") {
			console.log("Assignment updated "+inputData[0]);
		} else {
			console.log("No assignment for "+inputData[0]);
		}
		return;
	}
//	Assignment details/deliver updated
	if (inputRequest == 'updateDetails') {
		if (dataObject.status == "Done") {
			console.log("Assignment details updated "+inputData[0]);
			tempColor = '#5cb85c';
		} else {
			console.log("Assignment ("+inputData[0]+") details update failed.");
			tempColor = '#FF0000';
		}
		statusObject.innerHTML = "<font color='"+tempColor+"'>"+dataObject.status+"</font>";
		return;
	}
//	Assign requestor to assignment and accept
	if (inputRequest == 'assignRequestor') {
		if (dataObject.status == "Done") {
			console.log("Requestor assigned/accepted to assignment "+inputData[0]);
			tempColor = '#5cb85c';
		} else {
			console.log("Requestor assigned/accepted ("+inputData[0]+") failed.");
			tempColor = '#FF0000';
		}
		statusObject.innerHTML = "<font color='"+tempColor+"'>"+inputData[3] +" assigned/accepted: "+dataObject.status+"</font>";
		return;
	}
//	Editor accepts assignment for photographer
	if (inputRequest == 'acceptAssignment') {
		if (dataObject.status == "Done") {
			console.log(inputData[3]+" accepted assignment "+inputData[1]);
			tempColor = '#5cb85c';
		} else {
			console.log("Accepting assignment ("+inputData[0]+") ("+inputData[1]+") failed.");
			tempColor = '#FF0000';
		}
//		statusObject.innerHTML = "<font color='"+tempColor+"'>"+inputData[3]+" accept assignment "+inputData[1]+": "+dataObject.status+"</font>";
		statusObject.innerHTML = "<font color='"+tempColor+"'>"+dataObject.status+"</font>";
		return;
	}
//	Editor declines assignment for photographer
	if (inputRequest == 'declineAssignment') {
		if (dataObject.status == "Done") {
			console.log(inputData[3]+" declined assignment "+inputData[1]);
			tempColor = '#5cb85c';
		} else {
			console.log("Decline assignment ("+inputData[0]+") ("+inputData[1]+") failed.");
			tempColor = '#FF0000';
		}
//		statusObject.innerHTML = "<font color='"+tempColor+"'>"+inputData[3]+" accept assignment "+inputData[1]+": "+dataObject.status+"</font>";
		statusObject.innerHTML = "<font color='"+tempColor+"'>"+dataObject.status+"</font>";
		return;
	}
//	Editor flag assignment as images uploaded
//	flagAssignment(inputID, inputFinal, statusObject);
	if (inputRequest == 'flagAssignment') {
		if (dataObject.status == "Done") {
			console.log("Upload images flagged for assignment "+inputData[0]);
			tempColor = '#5cb85c';
		} else {
			console.log("Upload images flag for("+inputData[0]+") failed.");
			tempColor = '#FF0000';
		}
//		statusObject.innerHTML = "<font color='"+tempColor+"'>"+inputData[3]+" accept assignment "+inputData[1]+": "+dataObject.status+"</font>";
		statusObject.innerHTML = "<font color='"+tempColor+"'>"+dataObject.status+"</font>";
		return;
	}
	
	
//	Photographer list updated.
	if (inputRequest == 'updatePhotographers') {
		if (dataObject.status == "done") {
			console.log("Assignment updated: ("+inputData[0]+")");
			statusObject.innerHTML = "Done";
			return "done";
		} else {
			console.log("Assignment update failed: "+inputData[0]);
			statusObject.innerHTML = "Failed";
			return "failed";
		}
		return;
	}
//	List of photographers retrieved, update select box.
	if (inputRequest == 'getPhotographers') {
//	Update select box list of candidate photographers
//	List from database, update photographers array.
		tempObject = document.getElementById('isi_photographer_pool');
		resetSelect(tempObject);
		photographerValue = new Array();
		photographerText = new Array();
		tempValue = new Array();
		tempText = new Array();
//		tempMax = dataObject.list.length;
		tempMax = dataObject.count;
		tempArray = dataObject.list;
		tempIndex = '';
		endflag = true;
		i=0;
		index = 0;
		for (i=0; i<tempMax; i++) {
			tempIndex = dataObject.list[i].name.indexOf("(New)");
			if (endflag == true && tempIndex != -1) {
//				assignPhotographerIDs = photographerValue.join(':');
				endflag = false;
//				photographerValue[index] = 'end';
//				photographerText['end'] = '--------- End of list ---------';
//				photographerValue.push('end');
//				photographerText['end'] = '--------- End of list ---------';
				tempValue[index] = 'end';
				tempText['end'] = '--------- End of list ---------';
				index++;
			}
//		Photographers who have photographed that league are in the list (photographerValue).
//			photographerValue[index] = dataObject.list[i].id;
			photographerText[dataObject.list[i].id] = dataObject.list[i].name;
			tempValue[index] = dataObject.list[i].id;
			tempText[dataObject.list[i].id] = dataObject.list[i].name;
			index++;
		}
//	List of photographers for schedule_assign table, assign_photographerIDs .
		tempMax++;
//		updateSelect(tempObject, tempMax, photographerValue, photographerText);
		updateSelect(tempObject, tempMax, tempValue, tempText);
		return;
	}
//	List of photographers retrieved, update select box.
	if (inputRequest == 'getRequests') {
//	Update select box list with existing requests for editor/calendar/collection
//	List from database, update requests array.
		requestValue = new Array();
		requestText = new Array();
		tempMax = dataObject.count;
		if (tempMax == 0) { return; }
		tempArray = dataObject.list;
		tempIndex = '';
		i=0;
		index = 0;
		for (i=0; i<tempMax; i++) {
			requestValue[index] = dataObject.list[i].assignID;
			requestText[dataObject.list[i].assignID] = dataObject.list[i].details;
			index++;
		}
//	List of requests from schedule_assign, for editor/calendar/collection, value is assignID .
		tempMax++;
		tempObject = document.getElementById('isi_league');
		updateSelect(tempObject, tempMax, requestValue, requestText);
		tempObject.size = tempMax +1;
		return;
	}
	if (inputRequest == 'sendEmailReminder') {
		if (dataObject.status == "Done") {
			console.log("Email sent to "+inputData[3]+" ("+inputData[4]+")");
			tempColor = '#5cb85c';
		} else {
			console.log("Email failed: "+inputData[3]+" ("+inputData[4]+")");
			tempColor = '#FF0000';
		}
//		statusObject.innerHTML = "<font color='"+tempColor+"'>Email to "+inputData[3]+" ("+inputData[4]+"): "+dataObject.status+"</font>";
//		Or status in next column
		statusObject.innerHTML = "<font color='"+tempColor+"'>"+dataObject.status+"</font>";
		return;
	}
	if (inputRequest == 'sendEmail') {
		if (dataObject.statusEditor != 'successful' && dataObject.statusPhotographer != 'successful') {
			alert("Emails to editor and photographer failed to send.");
		}
	}
	if (inputRequest == 'getEditorID') {
		currentEditorID = dataObject.editorID;
	}
	if (inputRequest == 'gettyMEID') {
		if (dataObject.status == "Done") {
			tempColor = '#5cb85c';
		} else {
			tempColor = '#FF0000';
		}
		statusObject.innerHTML = "<font color='"+tempColor+"'>"+dataObject.status+"</font>";
		return;
	}
	if (inputRequest == 'gettyNotify') {
		if (dataObject.status == "Done") {
			console.log("Getty notified for "+inputData[1]+" MEID("+inputData[2]+")");
			tempColor = '#5cb85c';
		} else {
			console.log("Getty notify failed "+inputData[1]+" MEID("+inputData[2]+")");
			tempColor = '#FF0000';
		}
//		statusObject.innerHTML = "<font color='"+tempColor+"'>Getty notify for "+inputData[1]+" MEID("+inputData[2]+"): "+dataObject.status+"</font>";
//		Or status in next column
		statusObject.innerHTML = "<font color='"+tempColor+"'>"+dataObject.status+"</font>";
		return;
	}


});

}



