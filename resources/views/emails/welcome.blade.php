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

        .role-instructions {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #008080;
        }

        .login-details {
            margin-top: 20px;
            padding: 15px;
            background-color: #e6f3f3;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Welcome, {{ $user->first_name }}!</h1>
        </div>
        <div class="content">
            <p>Thank you for joining Domotena as a {{ $user->role }}.</p>
            <p>Your account has been created successfully with the email: {{ $user->email }}.</p>
            @if ($user->role === 'tenant')
                <div class="login-details">
                    <h3>Your Login Details</h3>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Password:</strong> {{ $password }}</p>
                    <!-- Assuming $password is passed to the mailable -->
                    <p><strong>Dashboard:</strong> <a href="https://domotena.vercel.app/login?role=tenant">Log in to your
                            tenant dashboard</a></p>
                </div>
            @endif
            <div class="role-instructions">
                @if ($user->role === 'landlord')
                    <h3>Getting Started as a Landlord</h3>
                    <p>You can now list and manage your properties, track payments, and respond to maintenance requests.
                    </p>
                    <ul>
                        <li>Add your properties in the Landlord Dashboard.</li>
                        <li>Monitor tenant applications and lease agreements.</li>
                        <li>Stay updated with payment and maintenance notifications.</li>
                    </ul>
                @elseif ($user->role === 'tenant')
                    <h3>Getting Started as a Tenant</h3>
                    <p>Explore available properties, submit maintenance requests, and manage your payments.</p>
                    <ul>
                        <li>Browse properties in your desired region.</li>
                        <li>Submit applications to rent properties.</li>
                        <li>Track your payment history and maintenance requests.</li>
                    </ul>
                @endif
            </div>
            <p>Explore our platform to get started!</p>
            <a href="{{ config('app.url') }}" class="button">Go to Dashboard</a>
        </div>
    </div>
</body>

</html>



{{-- <!DOCTYPE html>
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

        .role-instructions {
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
            <h1>Welcome, {{ $user->first_name }}!</h1>
        </div>
        <div class="content">
            <p>Thank you for registering with Domotena as a {{ $user->role }}.</p>
            <p>Your account has been created successfully with the email: {{ $user->email }}.</p>
            <div class="role-instructions">
                @if ($user->role === 'landlord')
                    <h3>Getting Started as a Landlord</h3>
                    <p>You can now list and manage your properties, track payments, and respond to maintenance requests.
                    </p>
                    <ul>
                        <li>Add your properties in the Landlord Dashboard.</li>
                        <li>Monitor tenant applications and lease agreements.</li>
                        <li>Stay updated with payment and maintenance notifications.</li>
                    </ul>
                @elseif ($user->role === 'tenant')
                    <h3>Getting Started as a Tenant</h3>
                    <p>Explore available properties, submit maintenance requests, and manage your payments.</p>
                    <ul>
                        <li>Browse properties in your desired region.</li>
                        <li>Submit applications to rent properties.</li>
                        <li>Track your payment history and maintenance requests.</li>
                    </ul>
                @endif
            </div>
            <p>Explore our platform to get started!</p>
            <a href="https://domotena.vercel.app/login?role=landlord" class="button">Go to Dashboard</a>
        </div>
    </div>
</body>

</html> --}}
