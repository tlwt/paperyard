(function($) {

    const canvasContainer = $('#pdf-pages');
    const options = { scale: window.devicePixelRatio };
    const url = $(canvasContainer).attr('data-document')

    function renderPage(page) {
        const viewport = page.getViewport(options.scale);
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const item = document.createElement('div');
        item.classList.add('carousel-item');

        if (page.pageIndex === 0) {
            item.classList.add('active');
        }

        const renderContext = {
            canvasContext: ctx,
            viewport: viewport
        };

        canvas.height = viewport.height;
        canvas.width = viewport.width;
        item.appendChild(canvas);
        canvasContainer.append(item);

        page.render(renderContext);
    }

    function renderPages(pdfDoc) {
        for(let num = 1; num <= pdfDoc.numPages; num++)
            pdfDoc.getPage(num).then(renderPage);
    }

    PDFJS.disableWorker = true;
    PDFJS.getDocument(url).then(renderPages);

    $('.datepicker').datepicker({
        format: 'dd.mm.yyyy',
        weekStart: 1,
        todayBtn: true,
        keyboardNavigation: false
    });

}(jQuery));