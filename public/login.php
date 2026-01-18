<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Music Virtual Closet</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <main>
        <div class="login-container">
            <h2>Login to Music Virtual Closet</h2>
            <?php if (isset($_GET['registered'])): ?>
    <p style="color: green;">Account created successfully. Please log in.</p>
<?php endif; ?>
            <form action="/public/login.php" method="POST">
                <div class="form-group
">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group
">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-submit">Log In</button>
            </form>
        </div>
    </main>
</body>
</html>