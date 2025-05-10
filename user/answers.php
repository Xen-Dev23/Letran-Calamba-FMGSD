<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'letran_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Fetch the most recent quiz results for each lesson, with module, lesson, and question details
$stmt = $pdo->prepare("
    SELECT 
        m.id AS module_id,
        m.title AS module_title,
        l.id AS lesson_id,
        l.title AS lesson_title,
        q.id AS quiz_id,
        q.question,
        q.option_a,
        q.option_b,
        q.option_c,
        q.option_d,
        q.correct_option,
        q.status AS question_status,
        uqa.selected_option,
        qr.score,
        qr.totalItems,
        qr.isPassed,
        qr.taken_at
    FROM quiz_results qr
    INNER JOIN (
        SELECT lesson_id, MAX(taken_at) AS latest_taken_at
        FROM quiz_results
        WHERE user_id = :user_id
        GROUP BY lesson_id
    ) latest ON qr.lesson_id = latest.lesson_id AND qr.taken_at = latest.latest_taken_at
    JOIN lessons l ON qr.lesson_id = l.id
    JOIN modules m ON l.module_id = m.id
    LEFT JOIN quizzes q ON l.id = q.lesson_id
    LEFT JOIN user_quiz_answers uqa ON q.id = uqa.quiz_id AND uqa.user_id = :user_id
    WHERE qr.user_id = :user_id
    ORDER BY m.title, l.title, q.id
");
$stmt->execute(['user_id' => $user_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results Records</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6 text-center">Your Quiz Answers</h1>

        <?php if (empty($results)): ?>
            <p class="text-center text-gray-600">No quiz results found.</p>
        <?php else: ?>
            <?php 
            $grouped_results = [];
            
            // Group results by module and lesson
            foreach ($results as $row) {
                $module_title = $row['module_title'];
                $lesson_title = $row['lesson_title'];
                if (!isset($grouped_results[$module_title])) {
                    $grouped_results[$module_title] = [];
                }
                if (!isset($grouped_results[$module_title][$lesson_title])) {
                    $grouped_results[$module_title][$lesson_title] = [
                        'questions' => [],
                        'quiz_result' => [
                            'score' => $row['score'],
                            'totalItems' => $row['totalItems'],
                            'isPassed' => $row['isPassed'],
                            'taken_at' => $row['taken_at']
                        ]
                    ];
                }
                if (!empty($row['question'])) {
                    $grouped_results[$module_title][$lesson_title]['questions'][] = $row;
                }
            }
            ?>

            <?php foreach ($grouped_results as $module_title => $lessons): ?>
                <div class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4"><?php echo htmlspecialchars($module_title); ?></h2>
                    <?php foreach ($lessons as $lesson_title => $data): ?>
                        <div class="mb-6 bg-white p-4 rounded-lg shadow-md">
                            <h3 class="text-xl font-medium text-gray-700 mb-3"><?php echo htmlspecialchars($lesson_title); ?></h3>
                            <?php if (empty($data['questions'])): ?>
                                <p class="text-gray-600">No questions available for this lesson.</p>
                                <div class="mt-2">
                                    <p class="text-gray-600">
                                        Quiz Result: Score: <?php echo $data['quiz_result']['score'] . '/' . $data['quiz_result']['totalItems']; ?>,
                                        Status: <?php echo $data['quiz_result']['isPassed'] ? '<span class="text-green-600">Passed</span>' : '<span class="text-red-600">Failed</span>'; ?>,
                                        Taken: <?php echo date('M j, Y, g:i a', strtotime($data['quiz_result']['taken_at'])); ?>
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full bg-gray-50 rounded-lg">
                                        <thead>
                                            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                                                <th class="py-2 px-4 text-left">Question</th>
                                                <th class="py-2 px-4 text-center">Correct Answer</th>
                                                <th class="py-2 px-4 text-center">Your Answer</th>
                                                <th class="py-2 px-4 text-center">Status</th>
                                                <th class="py-2 px-4 text-center">Question Status</th>
                                                <th class="py-2 px-4 text-center">Quiz Result</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 text-sm">
                                            <?php foreach ($data['questions'] as $row): ?>
                                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                                    <td class="py-2 px-4 text-left"><?php echo htmlspecialchars($row['question']); ?></td>
                                                    <td class="py-2 px-4 text-center">
                                                        <?php 
                                                        $correct_option = 'option_' . strtolower($row['correct_option']);
                                                        echo htmlspecialchars($row[$correct_option]);
                                                        ?>
                                                    </td>
                                                    <td class="py-2 px-4 text-center">
                                                        <?php 
                                                        // Robust check for selected_option
                                                        if ($row['selected_option'] && in_array($row['selected_option'], ['A', 'B', 'C', 'D'])) {
                                                            $option_key = 'option_' . strtolower($row['selected_option']);
                                                            echo htmlspecialchars($row[$option_key] ?? 'Invalid Option');
                                                        } else {
                                                            echo 'Not Answered';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="py-2 px-4 text-center">
                                                        <?php 
                                                        if ($row['selected_option'] && in_array($row['selected_option'], ['A', 'B', 'C', 'D'])) {
                                                            echo $row['selected_option'] === $row['correct_option'] 
                                                                ? '<span class="text-green-600">Correct</span>' 
                                                                : '<span class="text-red-600">Incorrect</span>';
                                                        } else {
                                                            echo '<span class="text-gray-600">Not Answered</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="py-2 px-4 text-center">
                                                        <?php echo $row['question_status'] === 'active' ? '<span class="text-green-600">Active</span>' : '<span class="text-red-600">Inactive</span>'; ?>
                                                    </td>
                                                    <td class="py-2 px-4 text-center">
                                                        Score: <?php echo $data['quiz_result']['score'] . '/' . $data['quiz_result']['totalItems']; ?><br>
                                                        Status: <?php echo $data['quiz_result']['isPassed'] ? '<span class="text-green-600">Passed</span>' : '<span class="text-red-600">Failed</span>'; ?><br>
                                                        Taken: <?php echo date('M j, Y, g:i a', strtotime($data['quiz_result']['taken_at'])); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>