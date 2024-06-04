<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Error Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        p {
            font-size: 16px;
            margin-bottom: 5px;
        }
        a{
            color: black;
        }
        a:hover{
            color: grey;
        }

    </style>
</head>
<body>
    <h1>Link Error Notification</h1>
    <p>The following link has encountered an error:</p>
    <p>URL: <a href="{{ $url }}">{{ $url }}</a></p>
    <p>Status Code: {{ $statusCode }}</p>
    <p>Error Message: {{ $errorMessage }}</p>
    <p>Please investigate and take appropriate action.</p>
</body>
</html>
