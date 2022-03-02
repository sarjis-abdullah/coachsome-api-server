<!-- HTML for static distribution bundle build -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Coachsome API</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('swagger/swagger-ui.css') }}" />
    <link rel="icon" href="{{ asset('swagger/favicon.ico') }}"  />
    <style>
        html
        {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *,
        *:before,
        *:after
        {
            box-sizing: inherit;
        }
        body
        {
            margin:0;
            background: #fafafa;
        }
        .swagger-ui img {
            content: url("{{ asset('assets/images/logos/logo-light.png') }}");
            width: 134px; /* width of your logo */
            height: 19px;
            /*height: 40px; !* height of your logo *!*/
        }
    </style>
</head>

<body>
<div id="swagger-ui"></div>

<script src="{{ asset('swagger/swagger-ui-bundle.js') }}" charset="UTF-8"> </script>
<script src="{{ asset('swagger/swagger-ui-standalone-preset.js') }}" charset="UTF-8"> </script>
<script>
    window.onload = function() {
        // Begin Swagger UI call region
        const ui = SwaggerUIBundle({
            url: "{{ route('openapi') }}",
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],
            layout: "StandaloneLayout"
        });
        // End Swagger UI call region
        window.ui = ui;
    };
</script>
</body>
</html>
