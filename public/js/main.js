/* --- ON LOAD --- */
// EU cookie law
window.addEventListener("load", function(){
	window.cookieconsent.initialise({
		"palette": {
			"popup": {
				"background": "#000"
			},
			"button": {
				"background": "#f1d600"
			}
		}
	})});



/* --- FUNCTIONS --- */
// copy value of input to clipboard from input's id
function copyInputToClipboard(inputId) {
	$('#'+inputId).focus();
	$('#'+inputId).select();
	document.execCommand('copy');
}

function disableElement(selectorStr) {
	$(selectorStr).attr('disabled', 'disabled');
}
