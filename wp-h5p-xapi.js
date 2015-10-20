jQuery(function($) {

	var spinnerCount = 0;

	/**
	 * Show the spinner and increase counter.
	 */
	function showSpinner() {
		spinnerCount++;
		$("#wp-h5p-xapi-spinner").show();
	}

	/**
	 * Decrease the spinner count. Hide if no more spinners.
	 */
	function hideSpinner() {
		spinnerCount--;

		if (!spinnerCount)
			$("#wp-h5p-xapi-spinner").fadeOut(500);
	}

	/**
	 * Show error.
	 */
	function showError(message, code) {
		console.error("Unable to save xAPI statement");

		alert("Unable to save result data.\n\nMessage: " + message + "\n" + "Code: " + code);
	}

	/**
	 * Post error.
	 */
	function onXapiPostError(xhr, message, error) {
		hideSpinner();

		console.log("xapi post error");
		console.log(xhr.responseText);

		showError(message, xhr.status);
	}

	/**
	 * Post success.
	 */
	function onXapiPostSuccess(res, textStatus, xhr) {
		hideSpinner();

		if (!res.hasOwnProperty("ok")) {
			console.log("xapi post error");
			console.log(xhr.responseText);
			showError("Got bad response back...", 500);
		}

		if (!res.ok) {
			console.log("xapi post error");
			console.log(xhr.responseText);
			showError(res.message, res.code);
		}
	}

	/**
	 * xAPI statement event listener.
	 */
	function onXapi(event) {
		if (!WP_H5P_XAPI_STATEMENT_URL)
			return;

		showSpinner();

		var data = {};
		/*console.log("on xapi, statement:");
		console.log(JSON.stringify(event.data.statement));*/
		
		if (!typeof event.data.statement.context == 'object'){
			event.data.statement.context = {};
		}
		if (!typeof event.data.statement.context.contextActivities == 'object'){
			event.data.statement.context.contextActivities = {};
		}
		if (!typeof event.data.statement.context.contextActivities.grouping == 'array'){
			event.data.statement.context.grouping = [];
		}
		
		event.data.statement.context.grouping.push(WP_H5P_XAPI_CONTEXTACTIVITY);
		
		data.statement = JSON.stringify(event.data.statement);
		//data.statement = event.data.statement;

		$.ajax({
			type: "POST",
			url: WP_H5P_XAPI_STATEMENT_URL,
			data: data,
			dataType: "json",
			success: onXapiPostSuccess,
			error: onXapiPostError
		});
	}

	/**
	 * Main.
	 * Create save spinner and register event listener.
	 */
	$(document).ready(function() {
		console.log("ready");
		H5P.externalDispatcher.on('xAPI', onXapi);

		$("body").append("<div id='wp-h5p-xapi-spinner'>Saving...</div>");
		$("#wp-h5p-xapi-spinner").hide();
	});
});
