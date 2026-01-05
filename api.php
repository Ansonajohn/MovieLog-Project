<?php
session_start();
header('Content-Type: application/json');

// CONNECT TO DATABASE (Port 3307)
$conn = new mysqli("127.0.0.1", "root", "", "moviedb", 3307);
if ($conn->connect_error) { die(json_encode(["message" => "Connection Failed"])); }

$method = $_SERVER['REQUEST_METHOD'];

// --- GET MOVIES ---
if ($method == 'GET') {
    // Feature: Move to Library
    if(isset($_GET['action']) && $_GET['action'] == 'move' && isset($_GET['id'])) {
        $stmt = $conn->prepare("UPDATE movies SET status = 'watched' WHERE id = ?");
        $stmt->bind_param("i", $_GET['id']);
        $stmt->execute();
        echo json_encode(["message" => "Moved to Library!"]);
        exit;
    }

    $status = isset($_GET['status']) ? $_GET['status'] : 'watched';
    $genreClause = (isset($_GET['genre']) && $_GET['genre'] !== 'All') ? "AND genre = '" . $conn->real_escape_string($_GET['genre']) . "'" : "";

    $sql = "SELECT * FROM movies WHERE status = '$status' $genreClause ORDER BY id DESC";
    $result = $conn->query($sql);

    $movies = [];
    while($row = $result->fetch_assoc()) { $movies[] = $row; }
    echo json_encode($movies);
}

// --- ADD MOVIE ---
if ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Check if exists
    $check = $conn->prepare("SELECT id FROM movies WHERE title = ?");
    $check->bind_param("s", $data['title']);
    $check->execute();
    if($check->get_result()->num_rows > 0) { echo json_encode(["message" => "Movie already exists!"]); exit; }

    // Save
    $stmt = $conn->prepare("INSERT INTO movies (title, genre, rating, poster, plot, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisss", $data['title'], $data['genre'], $data['rating'], $data['poster'], $data['plot'], $data['status']);
    
    if ($stmt->execute()) {
        echo json_encode(["message" => "Saved successfully!"]);
    } else {
        echo json_encode(["message" => "Error saving."]);
    }
}

// --- DELETE MOVIE ---
if ($method == 'DELETE') {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode(["message" => "Deleted."]);
}

$conn->close();
?>