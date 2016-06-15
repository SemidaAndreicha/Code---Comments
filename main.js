var user = [], // creates an array
    muc = [],// creates an array
    authenticated    = createCustomEvent('authenticated',authenticatedHandler), //creates an Event named authenticated
    notAuthenticated = createCustomEvent('notAuthenticated',notAuthenticatedHandler), //creates an Event named notAuthenticated
    tokenUpdated     = createCustomEvent('tokenUpdated',tokenUpdatedHandler); //creates an Event named tokenUpdated

function onLoad(name) { //on load dunction
	loginHandler(name, pass); //the username and password

    var dhtmlLogoutButton = document.getElementById('logoutButton'); // when the logout Button is clicked it exits
    dhtmlLogoutButton.addEventListener("click", logoutHandler);

    var dhtmlStatusButton = document.getElementById('statusButton'); // when the status Button is clicked it shows the status
    dhtmlStatusButton.addEventListener("click", statusChangedHandler);

    UCConnect.init({ //the init
        initialPresence: 'online', //the initial
        logEnabled: false //?
    });

	//connection statuses
    UCConnect.events.on('connecting', connectingHandler);
    UCConnect.events.on('connected', connectedHandler);
    UCConnect.events.on('disconnected', disconnectedHandler);
    UCConnect.events.on('parameters', parametersHandler);
    UCConnect.events.on('status', statusHandler);
	
	

    user.activeCall = null; //user has to activete the call is not active from start
}

/**
 * Login related handlers
 */

function loginHandler(name, pass) { //the login
    console.debug('loginHandler', event); //?

    if (typeof UCConnect.getConnection() === 'undefined' || !UCConnect.getConnection().authenticated) { //chcks for identified users or unidentified users?
        var cmd = 'session';  

        var username = "test.user" + name + "@student.ucconnect.io";

        user.email    = "test.user" + name + "@student.ucconnect.io";
        user.username = user.email; //.split('@')[0];

        var password  = md5(pass);
        user.password = password;


        sendRequest('GET',
            cmd + '/' + username + '/' + password,
            function (data) { //asks for username & password
                var response = {};
                if (data.response !== '') { //if the user is found(authentified) then it gives the response?
                    console.debug(data);
                    response = JSON.parse(data.response);
                }

                authenticated.JSONresponse = response;
                document.dispatchEvent(authenticated);
            },
            function (data) { //if the user is not found(notauthentified) then it gives the response?
                var response = {};
                if (data.response !== '') {
                    response = JSON.parse(data.response);
                }
                notAuthenticated.JSONresponse = response;
                document.dispatchEvent(notAuthenticated);
            }
        );
    }
    else { //you are still logged in?
        alert('First logout!'); 
    }
}

function logoutHandler(event) { //the logout Handler
    console.debug('logoutHandler', event);

    if (typeof UCConnect.getConnection() === 'undefined' || !UCConnect.getConnection().authenticated) {
        alert('First login!');
    }
    else { 
        UCConnect.disconnect();
    }
}

function authenticatedHandler(event) { // authenticated Handler
    console.debug('authenticatedHandler', event, event.JSONresponse);

    user.userId = event.JSONresponse.uccUserId;
    user.applicationUserId = event.JSONresponse.UCC_USER.applicationUserId;
    user.applicationTenantId = event.JSONresponse.UCC_USER.applicationTenantId;
    user.applicationSessionId = event.JSONresponse.applicationSessionId;
    user.token  = md5("Sch00l@1nH0lLand");

    var cmd = 'tokens';

    sendRequest('PUT',
        cmd,
        function (data) { //decides the access to the data?
            var response = {};
            if (data.response !== '') {
                response = JSON.parse(data.response);
            }

            tokenUpdated.JSONresponse = response;
            document.dispatchEvent(tokenUpdated);
        },
        function (data) {
            var response = {};
            if (data.response !== '') { //
                response = JSON.parse(data.response); //concatitnates the response data if the response is not null?
            }
            notAuthenticated.JSONresponse = response; //the translation of the response can't be authentified
            document.dispatchEvent(notAuthenticated); 
        },
        JSON.stringify( //makes strings?
            {   "jsonrpc": "2.0",
                "method": "client.storeToken",
                "id": 100,
                "params":{
                    "apiKey": API_KEY,
                    "applicationUserId": user.applicationUserId,
                    "applicationTenantId": user.applicationTenantId,
                    "applicationSessionId": user.applicationSessionId,
                    "token": user.token
                }
            })
    );
}

function tokenUpdatedHandler(event) { 
    console.debug('tokenUpdatedHandler', event); //token

    var data =  { 
        apiLocation: API_LOCATION,
        apiKey: API_KEY,
        user: user.username,
        tenantId: user.applicationTenantId,
        userId: user.applicationUserId,
        token: user.token,
        sessionId: user.applicationSessionId
    };

    UCConnect.connect(data); //concatinates the connection with the data?
}

function notAuthenticatedHandler(event) { // if authentification fails then it displayes the error name
    console.debug('notAuthenticatedHandler', event.JSONresponse);

    var code    = 0;
    var message = 'General error!';

    if (typeof event.JSONresponse.error !== 'undefined' &&
        typeof event.JSONresponse.error.code !== 'undefined' &&
        typeof event.JSONresponse.error.message !== 'undefined') {
        code    = event.JSONresponse.error.code;
        message = event.JSONresponse.error.message;
    }
    alert('[' + code + '] ' + message);
}

/**
 * End of Login related handlers
 */

/**
 * UCConnect connection related handlers
 *
 */
function connectingHandler(event) { //debugger
    console.debug('connectingHandler', event);
}

function connectedHandler(event) {
    console.debug('connectedHandler', event);

    if (event.proto === 'xmpp') {
        UCConnect.fetchPrivateXml('xmpp', 'muc:rooms', handleMucRooms);
    }
}

function handleMucRooms(data, iq) { //I don't understand this fn?? HELP!!
    console.debug('handleMucRooms', data, iq);

    var mucRooms;
    var dhtmlClasses = document.getElementById('classes');

    // Check if the returned data is a valid JSON object
    try {
        mucRooms = JSON.parse(data);
    } catch(e){
        return;
    }

    muc = [];

    for (var i in mucRooms) {
        var room = mucRooms[i]
        muc.push({
            roomId: room.roomId,
            roomHost: room.roomHost,
            roomName: room.roomName,
            username: room.username,
            from: room.from
        });

        UCConnect.groupJoin('xmpp', room.roomHost, room.roomId, UCConnect.getConnection().jid);

        var opt = document.createElement('option');
        opt.value = room.roomId + '/' + room.roomHost;
        opt.innerHTML = room.roomName;
        opt.setAttribute("data-incall", false);
        opt.setAttribute("data-username", room.username);
        dhtmlClasses.appendChild(opt);
    }

    if (dhtmlClasses.children.length>0) {
        if (dhtmlClasses.children[0].getAttribute('data-incall') === 'true') {
            document.getElementById('joinClassButton').style.display = 'block';
        }
        else {
            document.getElementById('joinClassButton').style.display = 'none';
        }

    }
}

function disconnectedHandler(event) {
    console.debug('disconnectedHandler', event);
}

function parametersHandler(event) {
    console.debug('parametersHandler', event);
}

/**
 * End of UCConnect connection related handlers
 *
 */

/**
 * UCConnect roster, status and class invite related handlers
 *
 */

function statusChangedHandler(event) { 
    console.debug('statusChangedHandler', event);

    if (typeof UCConnect.getConnection() === 'undefined' || !UCConnect.getConnection().authenticated) {
        alert('First login!');
    }
    else {
        var status = document.getElementById('status').value;
        UCConnect.status(status, '');
    }
}

function statusHandler(event) { //I need help here!
    console.debug('statusHandler', event);

    var status = event.data.status;


    for (var jid in status) {
        console.debug('Status: ', jid, status[jid].status);
        setStatus(jid, status[jid].status);
    }
}

function setStatus(jid, status) { //I need a bit of explanation here!
    var options = document.getElementById('roster').options;
    for (var i=0; i<options.length; i++) {
        if (options[i].value === jid) {
            options[i].text = jid.split('/')[0] + ' ['+status+']';
        }
    }
}


