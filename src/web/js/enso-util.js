var ENSO_MODAL_ID = "enso_modal";

function ensoDoSmallModal(modalContent, modalFooter){
	if($("#" + ENSO_MODAL_ID).length != 0) 
		$("#" + ENSO_MODAL_ID).remove();
	
	$("body").append(
		"<div id='" + ENSO_MODAL_ID + "' class='modal'></div>"	  
	);
	
	var footerDiv = "<div class='modal-footer' style='border-top:1px solid rgba(0, 0, 0, 0.1)'>" +
						modalFooter +
					"</div>";
	
	ensoSetModalContent(modalContent, footerDiv);
}

function ensoDoMediumModal(modalContent, modalFooter){
	if($("#" + ENSO_MODAL_ID).length != 0) 
		$("#" + ENSO_MODAL_ID).remove();
	
	$("body").append(
			"<div id='" + ENSO_MODAL_ID + "' class='modal modal-fixed-footer'></div>"	  
	);
	
	var footerDiv = "<div class='modal-footer'>" +
						modalFooter +
					"</div>";
	ensoSetModalContent(modalContent, footerDiv);
}

function ensoDoBigModal(modalContent, modalFooter){
	if($("#" + ENSO_MODAL_ID).length != 0) 
		$("#" + ENSO_MODAL_ID).remove();
		
	var width = $(document).width() - 20;
	
	$("body").append(
		"<div id='" + ENSO_MODAL_ID + "' class='modal modal-fixed-footer' style='bottom: 10px; right: 10px; left: 10px; margin: 0px; width:" + width + "px;'></div>"	  
	);
	
	var footerDiv = "<div class='modal-footer'>" +
						modalFooter +
					"</div>";
	ensoSetModalContent(modalContent, footerDiv);
}

function ensoSetModalContent(modalContent, footerDiv){
	
	$("#" + ENSO_MODAL_ID).append(
		"<div class='modal-content'>" +
			modalContent +
		"</div>" +
		footerDiv		
	);
	
	$("#" + ENSO_MODAL_ID).openModal({
		dismissible: false, // Modal can be dismissed by clicking outside of the modal
		opacity: .5, // Opacity of modal background
		in_duration: 300, // Transition in duration
		out_duration: 200, // Transition out duration
		ready: function() { 
			$("#" + ENSO_MODAL_ID).css("z-index", "1100");
			$(".lean-overlay").css("z-index", "1099");
		}, // Callback for Modal open
		complete: function() { 
			$("#" + ENSO_MODAL_ID).css("z-index", "1200");
		}, // Callback for Modal open
	});
}

function ensoHideModal(){
	$("#" + ENSO_MODAL_ID).closeModal();
}

function ensoRemoveModal(){
	$("#" + ENSO_MODAL_ID).remove();
}