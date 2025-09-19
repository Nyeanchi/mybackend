<!DOCTYPE html>
<html>

<head>
    <title>Welcome to Domotena!</title>
</head>

<body>
    <h1>Welcome, {{ $user->first_name }} {{ $user->last_name }}!</h1>
    <p>Your tenant account has been created successfully.</p>
    <ul>
        <li>Email: {{ $user->email }}</li>
        <li>Temporary Password: {{ $password }}</li>
        <li>Property: {{ $user->property->name ?? 'Assigned' }}</li> <!-- Customize as needed -->
    </ul>
    <p>Please log in and change your password immediately.</p>
    <a href="{{ url('/login') }}">Log In Now</a>
</body>

</html>
