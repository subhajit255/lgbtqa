<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication OTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            font-size: 20px;
            color: #333;
            font-weight: bold;
        }

        .content {
            margin-top: 20px;
            line-height: 1.6;
            color: #666;
        }

        .otp-code {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #e9ecef;
            color: #007bff;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            Two-Factor Authentication OTP
        </div>
        <div class="content">
            You are receiving this email because a login attempt was made for your account that requires two-factor authentication.
            <br><br>
            Your OTP is:
            <br>
            <div class="otp-code">{{ $otp }}</div>
            <br><br>
            If you did not request this, please secure your account.
            <br><br>
            Regards,<br>
            {{ config('app.name') }}
        </div>
    </div>
</body>

</html>
