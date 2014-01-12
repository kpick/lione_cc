function doPopup(id) {

}
function doConfirm(id,msg) {
	$('#'+id).dialog({
		autoOpen:false,
		width: 400,
		modal: true,
		resizable: false,
		buttons: {
			"OK": function() {
				alert( 'foo');
			},
			"Cancel": function() {
				alert('bar');
			}
		}
	});
}