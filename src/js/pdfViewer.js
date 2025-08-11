import * as pdfjsLib from 'pdfjs-dist/webpack.mjs';

const pdfViewer = {

  init() {

    const url = document.getElementById('tempUrl');
    console.log(url);
    const container = document.getElementById('pdf-container');
    if (!container || !url) {
      return;
    }

    pdfjsLib.getDocument(url.value).promise.then(pdf => {
      for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
        pdf.getPage(pageNum).then(page => {
          const viewport = page.getViewport({ scale: 2.5 });
          const canvas = document.createElement('canvas');
          const ctx = canvas.getContext('2d');
          canvas.width = viewport.width;
          canvas.height = viewport.height;

          const renderContext = {
            canvasContext: ctx,
            viewport: viewport
          };

          page.render(renderContext);
          container.appendChild(canvas);
        });
      }
    });
  }

}

export default pdfViewer