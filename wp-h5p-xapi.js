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
                if( xapi_settings.alerts == true ){
                    alert("Unable to save result data.\n\nMessage: " + message + "\n" + "Code: " + code);
                } else {
                    console.log("Unable to save result data.\n\nMessage: " + message + "\n" + "Code: " + code);
                }
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

		if (res.ok) {
			$.event.trigger({
				type: "h5pXapiStatementSaved",
				message: res.message
			});
		}
	}

	/**
	 * xAPI statement event listener.
	 */
	function onXapi(event) {
		if (!xapi_settings.ajax_url)
			return;

		showSpinner();

		var data = {
			action: 'xapi_event'
		};
		/*console.log("on xapi, statement:");
		console.log(JSON.stringify(event.data.statement));*/

		if (typeof event.data.statement.context == 'undefined') {
			//console.log("here, context");
			event.data.statement.context = {};
		}
		if (typeof event.data.statement.context.contextActivities == 'undefined') {
			//console.log("here, contextActivities");
			event.data.statement.context.contextActivities = {};
		}
		if (typeof event.data.statement.context.contextActivities.grouping == 'undefined') {
			//console.log("here, grouping");
			event.data.statement.context.contextActivities.grouping = [];
		}

		if (xapi_settings.context_activity)
			event.data.statement.context.contextActivities.grouping.push(xapi_settings.context_activity);

		data.statement = JSON.stringify(event.data.statement);
		//data.statement = event.data.statement;

		$.ajax({
			type: "POST",
			url: xapi_settings.ajax_url,
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
		//console.log("h5p xapi ready");

		if (typeof H5P !== 'undefined' && H5P.externalDispatcher)
			H5P.externalDispatcher.on('xAPI', onXapi);

		$("body").append("<div id='wp-h5p-xapi-spinner'>Saving...</div>");
		$("#wp-h5p-xapi-spinner").hide();
	});
});
