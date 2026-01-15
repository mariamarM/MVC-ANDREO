<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MVC Register</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <form method="post" action="../views/auth/register.php">
        <h2>Register</h2>
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        
        <label for="music_genre">Favorite Music Genre:</label>
        <input type="text" id="music_genre" name="music_genre">
        
        <button type="submit">Register</button>
</body>
</html>