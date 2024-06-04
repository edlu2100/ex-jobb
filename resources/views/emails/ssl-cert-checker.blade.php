<!DOCTYPE html>
<html>
<head>
    <title>SSL Certificate Notification</title>
</head>
<body>
    <h1>{{ $valid ? 'SSL Certificate Notification' : 'SSL Certificate Warning' }}</h1>
    <p>Website URL: {{ $url }}</p>
    <p>Website ID: {{ $websiteId }}</p>
    <p>Valid: {{ $valid ? 'Yes' : 'No' }}</p>
    <p>Expiration Date: {{ $validTo }}</p>
    <p>Message: {{ $mes }}</p>
</body>
</html>
