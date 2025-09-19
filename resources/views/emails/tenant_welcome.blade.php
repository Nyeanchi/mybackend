<!DOCTYPE html>
<html>

<head>
    <title>Welcome to Domotena</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #f4f4f4;
            padding: 10px;
            text-align: center;
        }

        .content {
            padding: 20px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #008080;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }

        .credentials {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #008080;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Welcome, {{ $tenant->first_name }}!</h1>
        </div>
        <div class="content">
            <p>Thank you for joining Domotena as a tenant.</p>
            <p>Your account has been created successfully with the email: {{ $tenant->email }}.</p>
            <div class="credentials">
                <h3>Your Login Details</h3>
                <p><strong>Email:</strong> {{ $tenant->email }}</p>
                <p><strong>Password:</strong> {{ $password }}</p>
                <p>Please log in and change your password for security.</p>
            </div>
            <p>Explore available properties, submit maintenance requests, and manage your payments!</p>
            <a href="{{ $dashboardUrl }}" class="button">Go to Dashboard</a>
        </div>
    </div>
</body>

</html>
