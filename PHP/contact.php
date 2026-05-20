<?php
// Initialize variables
$name = $email = $subject = $message = "";
$errors = [];
$success = false;

// Allowed file types and max size (2MB)
$allowed_types = ["image/jpeg", "image/png", "application/pdf"];
$max_size = 2 * 1024 * 1024;

// Sanitize function
function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get values
    $name = $_POST["name"] ?? "";
    $email = $_POST["email"] ?? "";
    $subject = $_POST["subject"] ?? "";
    $message = $_POST["message"] ?? "";

    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $errors[] = "All required fields must be filled.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (strlen($message) < 10) {
        $errors[] = "Message must be at least 10 characters.";
    }

    // File validation
    if (isset($_FILES["attachment"]) && $_FILES["attachment"]["error"] == 0) {
        $file_type = $_FILES["attachment"]["type"];
        $file_size = $_FILES["attachment"]["size"];

        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, PNG, PDF allowed.";
        }

        if ($file_size > $max_size) {
            $errors[] = "File size must be less than 2MB.";
        }
    }

    // If no errors
    if (empty($errors)) {
        // Sanitize
        $name = sanitize($name);
        $email = sanitize($email);
        $subject = sanitize($subject);
        $message = sanitize($message);

        // Simulate email sending
        // (In real case: mail() function)

        $success = true;

        // Clear form values
        $name = $email = $subject = $message = "";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Form</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f4f4;
        }
        .container {
            width: 400px;
            margin: 60px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 0px 10px #ccc;
        }
        input,
select,
textarea,
button {
    width: 100%;
    box-sizing: border-box;
    font-size: 16px;
    margin: 8px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
}

input,
select,
button {
    height: 45px;
    padding: 10px;
}

textarea {
    padding: 10px;
    height: 120px;
    resize: none;
}

button {
    background: #007bff;
    color: white;
    border: none;
    cursor: pointer;
}
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Contact Us</h2>

    <?php
    // Show errors
    if (!empty($errors)) {
        echo "<div class='error'>";
        foreach ($errors as $error) {
            echo "<p>$error</p>";
        }
        echo "</div>";
    }

    // Show success
    if ($success) {
        echo "<div class='success'>";
        echo "<h3>Email Sent Successfully!</h3>";
        echo "<p><strong>Name:</strong> " . sanitize($_POST["name"]) . "</p>";
        echo "<p><strong>Email:</strong> " . sanitize($_POST["email"]) . "</p>";
        echo "<p><strong>Subject:</strong> " . sanitize($_POST["subject"]) . "</p>";
        echo "<p><strong>Message:</strong> " . sanitize($_POST["message"]) . "</p>";
        echo "</div>";
    }
    ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Enter Name" value="<?php echo htmlspecialchars($name); ?>" required>

        <input type="text" name="email" placeholder="Enter Email" value="<?php echo htmlspecialchars($email); ?>" required>

        <select name="subject" required>
            <option value="">Select Subject</option>
            <option value="General" <?php if ($subject=="General") echo "selected"; ?>>General</option>
            <option value="Support" <?php if ($subject=="Support") echo "selected"; ?>>Support</option>
            <option value="Feedback" <?php if ($subject=="Feedback") echo "selected"; ?>>Feedback</option>
        </select>

        <textarea name="message" placeholder="Enter Message (min 10 characters)" required><?php echo htmlspecialchars($message); ?></textarea>

        <input type="file" name="attachment">

        <button type="submit">Send Message</button>
    </form>
</div>

</body>
</html>