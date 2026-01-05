<?php
session_start();
$conn = new mysqli("127.0.0.1", "root", "", "moviedb", 3307);
if ($conn->connect_error) { die("Connection Failed"); }

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $user = $_POST['username'];
    $pass = $_POST['password'];

    if ($action == 'register') {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $user);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $message = "❌ Username taken!";
        } else {
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $user, $hashed_pass);
            if ($stmt->execute()) { $message = "✅ Created! Please log in."; } 
        }
    }

    if ($action == 'login') {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($pass, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                header("Location: index.html");
                exit;
            } else { $message = "❌ Wrong password!"; }
        } else { $message = "❌ User not found!"; }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - MovieLog</title>
    <style>
        body { background: #121212; color: white; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: 'Segoe UI', sans-serif; margin: 0; }
        .box { background: #1f1f1f; padding: 40px; border-radius: 8px; text-align: center; width: 320px; border: 1px solid #333; }
        h2 { margin-top: 0; color: #e50914; }
        input { width: 90%; padding: 12px; margin: 10px 0; border: 1px solid #333; border-radius: 4px; background: #2b2b2b; color: white; }
        button { width: 100%; padding: 12px; background: #e50914; color: white; border: none; font-weight: bold; cursor: pointer; border-radius: 4px; margin-top: 10px; }
        .toggle-link { display: block; margin-top: 15px; color: #aaa; cursor: pointer; font-size: 0.9em; text-decoration: underline; }
        .message { color: #ffeb3b; margin-bottom: 10px; font-size: 0.9em; min-height: 20px; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <div class="box">
        <h2>🎬 MovieLog</h2>
        <div class="message"><?php echo $message; ?></div>

        <form id="login-form" method="POST">
            <input type="hidden" name="action" value="login">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign In</button>
            <span class="toggle-link" onclick="toggleForms()">New here? Create Account</span>
        </form>

        <form id="register-form" method="POST" class="hidden">
            <input type="hidden" name="action" value="register">
            <input type="text" name="username" placeholder="Pick a Username" required>
            <input type="password" name="password" placeholder="Create Password" required>
            <button type="submit" style="background: #2196F3;">Create Account</button>
            <span class="toggle-link" onclick="toggleForms()">Already have an account? Sign In</span>
        </form>
    </div>
    <script>
        function toggleForms() {
            document.getElementById('login-form').classList.toggle('hidden');
            document.getElementById('register-form').classList.toggle('hidden');
        }
    </script>
</body>
</html>