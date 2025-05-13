<?php
session_start();
include '../db/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {
    header("Location: login.php");
    exit();
}

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

// Handle search/filter inputs
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_module = isset($_GET['filter_module']) ? trim($_GET['filter_module']) : '';

// Fetch available modules for category filter
$moduleStmt = $pdo->query("SELECT DISTINCT m.title FROM modules m INNER JOIN lessons l ON l.module_id = m.id INNER JOIN quiz_results qr ON qr.lesson_id = l.id");
$moduleTitles = $moduleStmt->fetchAll(PDO::FETCH_COLUMN);

// Build dynamic SQL
$sql = "
    SELECT 
        u.fullname AS user_name,
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
    INNER JOIN users u ON qr.user_id = u.id
    INNER JOIN lessons l ON qr.lesson_id = l.id
    INNER JOIN modules m ON l.module_id = m.id
    LEFT JOIN quizzes q ON l.id = q.lesson_id
    LEFT JOIN user_quiz_answers uqa ON q.id = uqa.quiz_id AND uqa.user_id = qr.user_id
    WHERE 1";

$params = [];

if (!empty($search)) {
    $sql .= " AND u.fullname LIKE :search";
    $params[':search'] = "%$search%";
}

if (!empty($filter_module)) {
    $sql .= " AND m.title = :filter_module";
    $params[':filter_module'] = $filter_module;
}

$sql .= " ORDER BY u.fullname, m.title, l.title, q.id";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Results Records</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
<div class="container mx-auto p-6">

    <!-- Back Button -->
    <div class="mb-4 text-center">
        <a href="admin_dashboard.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg">
            ⬅ Back to Admin Dashboard
        </a>
    </div>

    <h1 class="text-3xl font-bold mb-6 text-center">User Records</h1>

    <!-- Search and Filter -->
    <form method="GET" class="mb-6 flex flex-wrap gap-4 items-center justify-center">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by user name"
               class="px-4 py-2 border rounded-lg w-64">

        <select name="filter_module" class="px-4 py-2 border rounded-lg">
            <option value="">All Modules</option>
            <?php foreach ($moduleTitles as $module): ?>
                <option value="<?= htmlspecialchars($module) ?>" <?= $filter_module === $module ? 'selected' : '' ?>>
                    <?= htmlspecialchars($module) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Filter</button>
    </form>

    <?php if (empty($results)): ?>
        <p class="text-center text-gray-600">No quiz results found.</p>
    <?php else: ?>
        <?php
        $grouped_results = [];

        foreach ($results as $row) {
            $user_name = $row['user_name'];
            $module_title = $row['module_title'];
            $lesson_title = $row['lesson_title'];

            if (!isset($grouped_results[$user_name])) $grouped_results[$user_name] = [];
            if (!isset($grouped_results[$user_name][$module_title])) $grouped_results[$user_name][$module_title] = [];
            if (!isset($grouped_results[$user_name][$module_title][$lesson_title])) {
                $grouped_results[$user_name][$module_title][$lesson_title] = [
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
                $grouped_results[$user_name][$module_title][$lesson_title]['questions'][] = $row;
            }
        }
        ?>

        <?php foreach ($grouped_results as $user_name => $modules): ?>
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">User: <?= htmlspecialchars($user_name); ?></h2>
                <?php foreach ($modules as $module_title => $lessons): ?>
                    <div class="mb-6">
                        <h3 class="text-xl font-medium text-gray-700 mb-3"><?= htmlspecialchars($module_title); ?></h3>
                        <?php foreach ($lessons as $lesson_title => $data): ?>
                            <div class="mb-6 bg-white p-4 rounded-lg shadow-md">
                                <h4 class="text-lg font-medium text-gray-600 mb-2"><?= htmlspecialchars($lesson_title); ?></h4>
                                <?php if (empty($data['questions'])): ?>
                                    <p class="text-gray-600">No questions available for this lesson.</p>
                                <?php else: ?>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full bg-gray-50 rounded-lg">
                                            <thead>
                                            <tr class="bg-gray-200 text-gray-600 uppercase text-sm">
                                                <th class="py-2 px-4 text-left">Question</th>
                                                <th class="py-2 px-4 text-center">Correct</th>
                                                <th class="py-2 px-4 text-center">User Answer</th>
                                                <th class="py-2 px-4 text-center">Status</th>
                                                <th class="py-2 px-4 text-center">Question Status</th>
                                                <th class="py-2 px-4 text-center">Quiz Result</th>
                                            </tr>
                                            </thead>
                                            <tbody class="text-gray-600 text-sm">
                                            <?php foreach ($data['questions'] as $row): ?>
                                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                                    <td class="py-2 px-4 text-left"><?= htmlspecialchars($row['question']); ?></td>
                                                    <td class="py-2 px-4 text-center"><?= htmlspecialchars($row['option_' . strtolower($row['correct_option'])] ?? 'N/A'); ?></td>
                                                    <td class="py-2 px-4 text-center">
                                                        <?php
                                                        if ($row['selected_option'] && in_array($row['selected_option'], ['A', 'B', 'C', 'D'])) {
                                                            echo htmlspecialchars($row['option_' . strtolower($row['selected_option'])] ?? 'Invalid Option');
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
                                                        <?= $row['question_status'] === 'active' ? '<span class="text-green-600">Active</span>' : '<span class="text-red-600">Inactive</span>'; ?>
                                                    </td>
                                                    <td class="py-2 px-4 text-center">
                                                        Score: <?= $data['quiz_result']['score'] . '/' . $data['quiz_result']['totalItems']; ?><br>
                                                        Status: <?= $data['quiz_result']['isPassed'] ? '<span class="text-green-600">Passed</span>' : '<span class="text-red-600">Failed</span>'; ?><br>
                                                        Taken: <?= date('M j, Y, g:i a', strtotime($data['quiz_result']['taken_at'])); ?>
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
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
