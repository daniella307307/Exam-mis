<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View PDF</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.12.313/pdf.min.js"></script>
    <style>
        #pdf-container {
            width: 100%;
            height: 90vh;
            overflow: auto;
            background: #f4f4f4;
        }
        canvas {
            display: block;
            margin: auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }
    </style>
</head>
<body>

<h2>PDF Viewer</h2>
<div id="pdf-container"></div>

<script>
    //var url = 'Arduino1234.pdf'; // Your PDF file path
    var url='https://bluelackesadigital.com/Auth/programs/documents/Introduction_to_Electricity_and_Electronics.pdf';

    pdfjsLib.getDocument(url).promise.then(function(pdf) {
        var container = document.getElementById("pdf-container");

        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
            pdf.getPage(pageNum).then(function(page) {
                var scale = 1.5;
                var viewport = page.getViewport({ scale });

                var canvas = document.createElement("canvas");
                var context = canvas.getContext("2d");

                canvas.height = viewport.height;
                canvas.width = viewport.width;

                container.appendChild(canvas);

                page.render({
                    canvasContext: context,
                    viewport: viewport
                });
            });
        }
    });
</script>

</body>
</html>
