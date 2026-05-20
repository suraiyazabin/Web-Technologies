<?php
// Initialize variables
$errors = [];
$success = false;

$fullName = $email = $username = $age = $gender = $course = "";
$terms = "";

// Sanitize function
function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if (isset($_POST['register'])) {

    // Get values
    $fullName = $_POST['fullname'] ?? "";
    $email = $_POST['email'] ?? "";
    $username = $_POST['username'] ?? "";
    $password = $_POST['password'] ?? "";
    $confirmPassword = $_POST['confirm_password'] ?? "";
    $age = $_POST['age'] ?? "";
    $gender = $_POST['gender'] ?? "";
    $course = $_POST['course'] ?? "";
    $terms = $_POST['terms'] ?? "";

    // 🔹 VALIDATIONS

    // 1. Empty fields
    if (empty($fullName) || empty($email) || empty($username) || empty($password) || empty($confirmPassword) || empty($age) || empty($gender) || empty($course)) {
        $errors[] = "All fields are required.";
    }

    // 2. Full Name validation
    if (!preg_match("/^[a-zA-Z ]*$/", $fullName)) {
        $errors[] = "Full Name can contain only letters and spaces.";
    }

    // 3. Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // 4. Username length
    if (strlen($username) < 5) {
        $errors[] = "Username must be at least 5 characters.";
    }

    // 5. Password length
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    // 6. Password match
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    // 7. Age validation
    if ($age < 18) {
        $errors[] = "Age must be 18 or above.";
    }

    // 8. Gender check
    if (empty($gender)) {
        $errors[] = "Please select a gender.";
    }

    // 9. Course check
    if (empty($course)) {
        $errors[] = "Please select a course.";
    }

    // 10. Terms checkbox
    if (!isset($_POST['terms'])) {
        $errors[] = "You must accept Terms & Conditions.";
    }

    // If no errors
    if (empty($errors)) {
        // Sanitize data
        $fullName = sanitize($fullName);
        $email = sanitize($email);
        $username = sanitize($username);
        $age = sanitize($age);
        $gender = sanitize($gender);
        $course = sanitize($course);

        $success = true;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Registration</title>
    <style>
        body { font-family: Arial; background: #f2f2f2; }
        .container {
            width: 420px;
            margin: 40px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
        }
        input,select,button {
            width: 100%;
            height: 42px;
            padding: 10px;
            margin: 6px 0;
            box-sizing: border-box;
            font-size: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
}

        button {
            background: green;
            color: white;
            border: none;
            cursor: pointer;
        }
        .error { color: red; }
        .success { color: green; 
    }
    input[type="radio"],
    input[type="checkbox"] {
    width: auto;
    height: auto;
}
    </style>
</head>
<body>

<div class="container">
    <h2>Student Registration</h2>

    <!-- Show Errors -->
    <?php
    if (!empty($errors)) {
        echo "<div class='error'>";
        foreach ($errors as $e) {
            echo "<p>$e</p>";
        }
        echo "</div>";
    }

    // Show Success
    if ($success) {
        echo "<div class='success'>";
        echo "<h3>Registration Successful!</h3>";
        echo "<p><strong>Name:</strong> $fullName</p>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>Username:</strong> $username</p>";
        echo "<p><strong>Age:</strong> $age</p>";
        echo "<p><strong>Gender:</strong> $gender</p>";
        echo "<p><strong>Course:</strong> $course</p>";
        echo "</div>";
    }
    ?>

    <!-- FORM -->
    <form method="POST">

        <label>Full Name:</label>
        <input type="text" name="fullname" value="<?php echo htmlspecialchars($fullName); ?>">

        <label>Email:</label>
        <input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>">

        <label>Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">

        <label>Password:</label>
        <input type="password" name="password">

        <label>Confirm Password:</label>
        <input type="password" name="confirm_password">

        <label>Age:</label>
        <input type="number" name="age" value="<?php echo htmlspecialchars($age); ?>">

        <label>Gender:</label><br>
        <input type="radio" name="gender" value="Male" <?php if($gender=="Male") echo "checked"; ?>> Male
        <input type="radio" name="gender" value="Female" <?php if($gender=="Female") echo "checked"; ?>> Female
        <br><br>

        <label>Course:</label>
        <select name="course">
            <option value="">Select Course</option>
            <option value="CSE" <?php if($course=="CSE") echo "selected"; ?>>CSE</option>
            <option value="EEE" <?php if($course=="EEE") echo "selected"; ?>>EEE</option>
            <option value="BBA" <?php if($course=="BBA") echo "selected"; ?>>BBA</option>
        </select>

        <br>

        <input type="checkbox" name="terms" <?php if(isset($_POST['terms'])) echo "checked"; ?>>
        I accept Terms & Conditions

        <br><br>
        <button type="submit" name="register">Register</button>

    </form>
</div>

</body>
</html>