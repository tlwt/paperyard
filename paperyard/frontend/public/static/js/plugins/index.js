(function() {
    var dropzone = new Dropzone("#file-upload-zone",{
        url: '/upload',
        acceptFiles: "application/pdf",
        previewTemplate : '<div style="display:none"></div>',
        clickable: '#file-upload-zone, #file-upload-zone *'
    });
    
    dropzone.on("addedfile", function(file) { 
    	var element = $("#file-upload-zone").find(".fa");
    	element.removeClass('fa-upload');
    	element.addClass('fa-spinner fa-spin');
    });
    
    dropzone.on("complete", function(file) { 
    	element = $("#file-upload-zone").find(".fa")
    	element.removeClass('fa-spinner fa-spin')
    	element.addClass('fa-upload');
    	if (file.status  == 'success'){
    		$.notify({message: 'File upload success.'},{type: 'success'});
    	} else {
    		$.notify({message: 'File upload faild.'},{type: 'danger'});
    	}
    });

}());
