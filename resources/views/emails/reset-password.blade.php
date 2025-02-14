<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>Hello!</h1>
    <p>You are receiving this email because we received a password reset request for your account.</p>
    <div class="code">{{ $token }}</div>
    <p>This code will expire in 15 minutes.</p>
    <p>If you did not request a password reset, no further action is required.</p>
</body>
</html>
