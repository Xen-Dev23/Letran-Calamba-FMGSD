<?php
session_start();
include '../db/db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if answers were submitted
if (isset($_POST['quiz_ids']) && isset($_POST['answers'])) {
    $totalScore = 0;

    foreach ($_POST['quiz_ids'] as $quiz_id) {
        $user_answer = $_POST['answers'][$quiz_id];

        // Get the correct answer from the database
        $query = $conn->prepare("SELECT correct_option FROM quizzes WHERE id = ?");
        $query->bind_param("i", $quiz_id);
        $query->execute();
        $result = $query->get_result();
        $quiz = $result->fetch_assoc();

        $is_correct = ($user_answer === $quiz['correct_option']) ? 1 : 0;
        if ($is_correct) $totalScore++;

        // Save user's answer to results table
        $insert = $conn->prepare("INSERT INTO results (user_id, quiz_id, user_answer, is_correct) VALUES (?, ?, ?, ?)");
        $insert->bind_param("iisi", $user_id, $quiz_id, $user_answer, $is_correct);
        $insert->execute();
    }

    // Save final score to scores table
    $insertScore = $conn->prepare("INSERT INTO scores (user_id, score) VALUES (?, ?)");
    $insertScore->bind_param("ii", $user_id, $totalScore);
    $insertScore->execute();

    // Redirect back to the user dashboard
    header("Location: user_dashboard.php");
    exit();
} else {
    echo "⚠️ No answers submitted!";
}
?>
