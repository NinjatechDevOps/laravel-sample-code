<html>
<head>
    <script src="{{url('/js/pdfobject.min.js')}}"></script>
</head>
<body style="margin:0 !important">
    <div id="datasheet_preview"></div>
    <script>
        PDFObject.embed("{!! $product->datasheet_iframe_url !!}", "#datasheet_preview");
    </script>
</body>
</html>
