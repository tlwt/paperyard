(function() {

    $("#file-upload-zone").dropzone({
        url: '/upload',
        acceptFiles: "application/pdf",
        previewTemplate : '<div style="display:none"></div>',
        clickable: true
    });

}());
