<?php
session_start();
require '../includes/db.php';
require '../includes/functions.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $lesson_id = $_POST['lesson_id'];
  $user_id = $_SESSION['user_id'];
  $answers = $_POST['answers'];

  $stmt = $pdo->prepare("SELECT id, correct_option FROM quizzes WHERE lesson_id = :lesson_id");
  $stmt->execute([':lesson_id' => $lesson_id]);
  $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $score = 0;
  $total = count($questions);

  // Calculate score
  foreach ($questions as $question) {
    $question_id = $question['id'];
    if (isset($answers[$question_id]) && strtoupper($answers[$question_id]) === strtoupper($question['correct_option'])) {
      $score++;
    }
  }

  $passingScore = $total * 0.5;

  // Save result
  $stmt = $pdo->prepare("INSERT INTO quiz_results (user_id, lesson_id, score, isPassed, totalItems, isWatched) VALUES (:user_id, :lesson_id, :score, :is_Passed, :total_no_of_items, :is_Watched)");

  $isPassed = ($score >= $passingScore) ? 1 : 0;

  $sucess = $stmt->execute([':user_id' => $user_id, ':lesson_id' => $lesson_id, ':score' => $score, ':is_Passed' => $isPassed,':total_no_of_items' => $total, ':is_Watched' => 1]);
  $result_id = $pdo->lastInsertId();

  if ($sucess) {
    $_SESSION['quiz-result'] = 1;
    header("Location: quiz_result.php?result_id={$result_id}");
    exit();
  } else {
    dd("INTERNAL SERVER ERROR");
  }
}
