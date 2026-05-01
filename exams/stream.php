<?php
include('../db.php');
session_start();


// Optional: handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['full_name']);
    $stream = $_POST['stream'];
    $grade = $_POST['grade'];

    $errors = [];

    // Validate full name (at least 2 words)
    if (str_word_count($name) < 2) {
        $errors[] = "Please enter your full legal name (at least first and last name).";
    }

    if (empty($stream)) {
        $errors[] = "Please select a stream.";
    }

    if (empty($grade)) {
        $errors[] = "Please select a grade.";
    }

    if (empty($errors)) {
        // Redirect or save to DB
        echo "<script>alert('Registration successful!');</script>";
    }

     $stmt = $conn->prepare("UPDATE players SET nickname=?, stream=?, grade=? WHERE player_id=?");
    $stmt->bind_param("sssi", $name, $stream, $grade, $_SESSION['player_id']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
        header("Location: waiting.php");
        exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Entry</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

<div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center">Student Information</h2>

    <!-- Show errors -->
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        
        <!-- Full Name -->
        <label class="block mb-2 font-semibold">Full Legal Name</label>
        <input 
            type="text" 
            name="full_name" 
            placeholder="e.g. John Michael Doe"
            class="w-full p-2 border rounded mb-4"
            required
        >

        <!-- Stream Dropdown -->
        <label class="block mb-2 font-semibold">Stream</label>
        <select name="stream" class="w-full p-2 border rounded mb-4" required>
            <option value="">Select Stream</option>
            <option value="">A</option>
            <option value="Science">B</option>
            <option value="Arts">C</option>
        </select>

        <!-- Grade Dropdown -->
        <label class="block mb-2 font-semibold">Grade</label>
        <select name="grade" class="w-full p-2 border rounded mb-6" required>
            <option value="">Select Grade</option>
            <option value="Nursery 1">Nursery 1</option>
            <option value="Nursery 2">Nursery 2</option>
            <option value="Nursery 3">Nursery 3</option>
            <option value="Grade 1">Grade 1</option>
            <option value="Grade 2">Grade 2</option>
            <option value="Grade 3">Grade 3</option>
            <option value="Grade 4">Grade 4</option>
            <option value="Grade 5">Grade 5</option>
            <option value="Grade 6">Grade 6</option>
            <option value="Grade 7">Grade 7</option>
            <option value="Grade 8">Grade 8</option>
            <option value="Grade 9">Grade 9</option>
            <option value="Grade 10">Grade 10</option>
            <option value="Grade 11">Grade 11</option>
            <option value="Grade 12">Grade 12</option>
        </select>

        <!-- Submit -->
        <button 
            type="submit" 
            class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 rounded"
        >
            Continue
        </button>
    </form>
</div>

</body>
</html>
<?php
if (isset($conn)) $conn->close();
?>