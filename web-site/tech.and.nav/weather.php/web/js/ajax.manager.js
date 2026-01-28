/**
 * Uses ES6 Promises, with async/await.
 *
 * @author Olivier Le Diouris
 */
let forwardAjaxErrors = true;

function initAjax(forwardErrors=false, ping=1000) {

	forwardAjaxErrors = forwardErrors;
	let interval = setInterval(function () {
		fetchData();
	}, ping);
}

async function getPRMSL() {
	let url = '/tech.and.nav/weather.php/weather.php?type=PRMSL';
	const res = await fetch(url);
	const json = await res.json();
	return json
}
async function getAirTemp() {
	let url = '/tech.and.nav/weather.php/weather.php?type=AT';
	const res = await fetch(url);
	const json = await res.json();
	return json
}
async function getRelHum() {
	let url = '/tech.and.nav/weather.php/weather.php?type=RH';
	const res = await fetch(url);
	const json = await res.json();
	return json
}
async function getDewPoint() {
	let url = '/tech.and.nav/weather.php/weather.php?type=DEW-P';
	const res = await fetch(url);
	const json = await res.json();
	return json
}
async function getAbsHum() {
	let url = '/tech.and.nav/weather.php/weather.php?type=AH';
	const res = await fetch(url);
	const json = await res.json();
	return json
}

/**
Return data should look like this:

{
    "instant": {
        "temperature": 9.944921875,
        "humidity": 83.02451293443842,
        "pressure": 984.17908625865,
        "dew-point": 7.204318043621477,
        "abs-hum": 7.771699627056102
    },
    "pressure-map": {
        "2026-01-27T07:39:51": 982.4091311873304,
        "2026-01-27T07:54:52": 982.8030575692871,
        "2026-01-27T08:09:52": 982.9693334992788,
        "2026-01-27T08:24:52": 983.0804248530061,
        "2026-01-27T08:39:52": 983.2748338822435,
        "2026-01-27T08:54:52": 983.315439974433,
        "2026-01-27T09:09:53": 983.585800989231,
        "2026-01-27T09:24:53": 984.0074684906535,
        "2026-01-27T09:39:53": 984.1832592339689
    },
    "temperature-map": {
        "2026-01-27T07:39:51": 10.06796875,
        "2026-01-27T07:54:52": 10.0423828125,
        "2026-01-27T08:09:52": 9.9654296875,
        "2026-01-27T08:24:52": 10.1244140625,
        "2026-01-27T08:39:52": 10.062890625,
        "2026-01-27T08:54:52": 9.980859375,
        "2026-01-27T09:09:53": 9.95,
        "2026-01-27T09:24:53": 10.119140625,
        "2026-01-27T09:39:53": 10.0525390625
    }
}
*/
async function getTheData() {

	// Get all data (PRMSL, AT, RH, DEW-P, AH), to find last/instant values
	// debugger;
	let prmsl = await getPRMSL();
	let airTemp = await getAirTemp();
	let relHum = await getRelHum();
	let dewPoint = await getDewPoint();
	let absHum = await getAbsHum();

	// TODO Limit the data length to 7 days (672 entries at 15-min intervals)

	// Build instant values
	let instant = {};
	if (airTemp.data.length > 0) {
		let lastATEntry = airTemp.data[airTemp.data.length - 1];
		let keys = Object.keys(lastATEntry);
		instant["temperature"] = lastATEntry[keys[0]];
	}
	if (relHum.data.length > 0) {
		let lastHUMEntry = relHum.data[relHum.data.length - 1];
		let keys = Object.keys(lastHUMEntry);
		instant["humidity"] = lastHUMEntry[keys[0]];
	}
	if (prmsl.data.length > 0) {
		let lastPRMSLEntry = prmsl.data[prmsl.data.length - 1];
		let keys = Object.keys(lastPRMSLEntry);
		instant["pressure"] = lastPRMSLEntry[keys[0]];
	}
	if (dewPoint.data.length > 0) {
		let lastDEWEntry = dewPoint.data[dewPoint.data.length - 1];
		let keys = Object.keys(lastDEWEntry);
		instant["dew-point"] = lastDEWEntry[keys[0]];
	}
	if (absHum.data.length > 0) {
		let lastAHEntry = absHum.data[absHum.data.length - 1];
		let keys = Object.keys(lastAHEntry);
		instant["abs-hum"] = lastAHEntry[keys[0]];
	}

	// Rework the maps
	let prmslMap = {};
	for (let i = 0; i < prmsl.data.length; i++) {
		let entry = prmsl.data[i];
		let keys = Object.keys(entry);
		prmslMap[keys[0]] = entry[keys[0]];
	}
	let airTempMap = {};
	for (let i = 0; i < airTemp.data.length; i++) {
		let entry = airTemp.data[i];
		let keys = Object.keys(entry);
		airTempMap[keys[0]] = entry[keys[0]];
	}

	// debugger;
	return {
		"instant": instant,
		"pressure-map": prmslMap,
		"temperature-map": airTempMap
	};
}

function showCustomAlert(message) {
	let dialog = document.getElementById('custom-alert');

	if (dialog) {
		let content = document.getElementById('custom-alert-content');
		if (content) {
			content.innerHTML = message;
			dialog.show();
		} else {
			console.log(message);
		}
	} else {
		console.log(message);
	}
}

function closeCustomAlert() {
	let dialog = document.getElementById('custom-alert');
	if (dialog) {
		dialog.close();
	}
}

function fetchData(errCallback) {
	// Display popup, fetching data
	showCustomAlert('Fetching data, please wait...');
	let before = (new Date()).getTime();
	let getData = getTheData();
	getData.then((value) => {
		let after = (new Date()).getTime();
		console.log(`Done in ${after - before} ms.`);
		// Close popup
		closeCustomAlert();
		let json = value; // No parse required
		onMessage(json);
	}, (error, errmess) => {
		let message;
		if (errmess) {
			let mess = JSON.parse(errmess);
			if (mess.message) {
				message = mess.message;
			}
		}
		// Close popup
		closeCustomAlert();
		if (errCallback) {
			errCallback(error, message);
		} else {
			console.debug("Failed to get data..." + (error ? JSON.stringify(error, null, 2) : ' - ') + ', ' + (message ? message : ' - '));
		}
	});
}

const EVENT_FULL     = 'full';
const EVENT_AT       = 'at';   // Air Temperature
const EVENT_PRMSL    = 'prmsl';
const EVENT_HUM      = 'hum';  // Relative
const EVENT_DEW      = 'dew';
const EVENT_AH       = 'ah';   // Absolute humidity

function onMessage(json) {
	try {
		let errMess = "";

		try {
			events.publish(EVENT_FULL, json);
		} catch (err) {
			errMess += ((errMess.length > 0 ? ", " : "Cannot read ") + "full data");
			console.debug(`onMessage: ${errMess}`);
		}

		// Publishes
		if (json["instant"]["temperature"]) {
			try {
				let airTemp = json["instant"]["temperature"];
				events.publish(EVENT_AT, airTemp);
			} catch (err) {
				errMess += ((errMess.length > 0 ? ", " : "Cannot read ") + "air temperature");
			}
		} else {
			console.debug("No Air Temp.");
		}
		// Battery_Voltage, Relative_Humidity, Barometric_Pressure
		if (json["instant"]["pressure"]) {
			try {
				let baro = json["instant"]["pressure"];
				if (baro != 0) {
					events.publish(EVENT_PRMSL, baro);
				}
			} catch (err) {
				errMess += ((errMess.length > 0 ? ", " : "Cannot read ") + "PRMSL");
			}
		} else {
			console.debug("No Baro.");
		}
		if (json["instant"]["humidity"]) {
			try {
				let hum = json["instant"]["humidity"];
				if (hum > 0) {
					events.publish(EVENT_HUM, hum);
				}
			} catch (err) {
				errMess += ((errMess.length > 0 ? ", " : "Cannot read ") + "Relative_Humidity");
			}
		} else {
			console.debug("No HUM");
		}
		if (json["instant"]["dew-point"]) {
			try {
				let dew = json["instant"]["dew-point"];
				events.publish(EVENT_DEW, dew);
			} catch (err) {
				errMess += ((errMess.length > 0 ? ", " : "Cannot read ") + "dew");
			}
		} else {
			console.debug("No dewpoint");
		}
		if (json["instant"]["abs-hum"]) {
			try {
				let ah = json["instant"]["abs-hum"];
				events.publish(EVENT_AH, ah);
			} catch (err) {
				errMess += ((errMess.length > 0 ? ", " : "Cannot read ") + "ah");
			}
		} else {
			console.debug("No Absolute Humidity");
		}

		if (errMess && forwardAjaxErrors) {
			displayErr(errMess);
		}
	} catch (err) {
		displayErr(err);
	}
}