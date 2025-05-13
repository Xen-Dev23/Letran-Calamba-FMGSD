<?php
session_start();
require '../includes/db.php';
require '../includes/functions.php';
require '../vendor/autoload.php'; // Fixed path to vendor/autoload.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $lesson_id = $_POST['lesson_id'];
  $user_id = $_SESSION['user_id'];
  $answers = $_POST['answers'];

  // Fetch lesson and module details for email
  $stmt = $pdo->prepare("
    SELECT l.title AS lesson_title, m.title AS module_title 
    FROM lessons l 
    JOIN modules m ON l.module_id = m.id 
    WHERE l.id = :lesson_id
  ");
  $stmt->execute([':lesson_id' => $lesson_id]);
  $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

  // Fetch user details for email
  $stmt = $pdo->prepare("SELECT fullname, email FROM users WHERE id = :user_id");
  $stmt->execute([':user_id' => $user_id]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  $stmt = $pdo->prepare("SELECT id, correct_option FROM quizzes WHERE lesson_id = :lesson_id");
  $stmt->execute([':lesson_id' => $lesson_id]);
  $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $score = 0;
  $total = count($questions);

  // Calculate score and save answers
  foreach ($questions as $question) {
    $question_id = $question['id'];
    if (isset($answers[$question_id]) && strtoupper($answers[$question_id]) === strtoupper($question['correct_option'])) {
      $score++;
    }

    // Save the user's answer to user_quiz_answers
    if (isset($answers[$question_id]) && in_array(strtoupper($answers[$question_id]), ['A', 'B', 'C', 'D'])) {
      $stmt_answer = $pdo->prepare("
        INSERT INTO user_quiz_answers (user_id, quiz_id, selected_option)
        VALUES (:user_id, :quiz_id, :selected_option)
        ON DUPLICATE KEY UPDATE selected_option = :selected_option
      ");
      $stmt_answer->execute([
        ':user_id' => $user_id,
        ':quiz_id' => $question_id,
        ':selected_option' => strtoupper($answers[$question_id])
      ]);
    }
  }

  $passingScore = $total * 0.5;

  // Save result
  $stmt = $pdo->prepare("INSERT INTO quiz_results (user_id, lesson_id, score, isPassed, totalItems, isWatched) VALUES (:user_id, :lesson_id, :score, :is_Passed, :total_no_of_items, :is_Watched)");

  $isPassed = ($score >= $passingScore) ? 1 : 0;

  $sucess = $stmt->execute([':user_id' => $user_id, ':lesson_id' => $lesson_id, ':score' => $score, ':is_Passed' => $isPassed, ':total_no_of_items' => $total, ':is_Watched' => 1]);
  $result_id = $pdo->lastInsertId();

  if ($sucess) {
    // Send email with quiz results
    if ($lesson && $user) {
      $to = $user['email'];
      $subject = "Your Quiz Results for {$lesson['lesson_title']}";
      $pass_status = $isPassed ? 'Passed' : 'Failed';
      $message = "Dear {$user['fullname']},\n\n";
      $message .= "You have completed the quiz for the following lesson:\n";
      $message .= "Module: {$lesson['module_title']}\n";
      $message .= "Lesson: {$lesson['lesson_title']}\n\n";
      $message .= "Your Results:\n";
      $message .= "Score: {$score} out of {$total}\n";
      $message .= "Percentage: " . number_format(($score / $total) * 100, 2) . "%\n";
      $message .= "Status: {$pass_status}\n\n";
      $message .= "Thank you for participating!\n";
      $message .= "Best regards,\nLetran System Team";

      // Use PHPMailer instead of mail()
      $mail = new PHPMailer(true);
      try {
          $mail->isSMTP();
          $mail->Host = 'smtp.gmail.com';
          $mail->SMTPAuth = true;
          $mail->Username = 'opulenciaandrei23@gmail.com'; // Replace with your Gmail address
          $mail->Password = 'pkou mbww kqgc hgrh'; // Replace with your Gmail App Password
          $mail->SMTPSecure = 'tls';
          $mail->Port = 587;

          $mail->setFrom('no-reply@letransystem.com', 'Letran System');
          $mail->addAddress($to);
          $mail->Subject = $subject;
          $mail->Body = $message;

          $email_sent = $mail->send();
      } catch (Exception $e) {
          $email_sent = false;
          $_SESSION['error'] = "Quiz submitted, but email could not be sent. Error: {$mail->ErrorInfo}";
      }

      if ($email_sent) {
        $_SESSION['success'] = "Quiz submitted successfully! Results have been sent to your email.";
      } else {
        $_SESSION['error'] = "Quiz submitted, but there was an issue sending the email.";
      }
    } else {
      $_SESSION['error'] = "Quiz submitted, but email could not be sent due to missing lesson or user data.";
    }

    $_SESSION['quiz-result'] = 1;
    header("Location: quiz_result.php?result_id={$result_id}");
    exit();
  } else {
    dd("INTERNAL SERVER ERROR");
  }
}
?>