<?php
session_start();

// SIMPLE FILE STORAGE (NO SERIALIZE)
// format: name|gender
$file = "data.txt";

if (!file_exists($file)) {
    file_put_contents($file, "");
}

// READ DATA INTO ARRAY
$data = file($file, FILE_IGNORE_NEW_LINES);

// AGE FUNCTION

function getAge($dob) {
    $birth = DateTime::createFromFormat("d/m/y", $dob);
    if (!$birth) return false;

    $today = new DateTime();
    return $today->diff($birth);
}

// FORM SUBMIT
 
if (isset($_POST['submit'])) {

    $name = $_POST['name'];
    $gender = $_POST['gender'] ?? "";
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $division = $_POST['division'];
    $country = $_POST['country'];

    $errors = [];

    // EMPTY CHECK
    if ($name == "") $errors[] = "Name required";
    if ($gender == "") $errors[] = "Gender required";
    if ($dob == "") $errors[] = "DOB required";
    if ($address == "") $errors[] = "Address required";
    if ($division == "") $errors[] = "Division required";
    if ($country == "") $errors[] = "Country required";

    // AGE CHECK
    $age = getAge($dob);

    if (!$age) {
        $errors[] = "DOB must be DD/MM/YY";
    } else {
        $ageText = $age->y . "Y" . $age->m . "M" . $age->d . "D";

        if ($age->y > 5) {
            $errors[] = "We only provide vaccination for children under 5 ($ageText)";
        }
    }

    // COUNTRY CHECK
    if ($country == "Others") {
        $errors[] = "Only Bangladeshi children allowed";
    }

    // STORE RESULT IN SESSION
    $_SESSION['result'] = [
        "errors" => $errors,
        "ageText" => $ageText ?? ""
    ];

    // SAVE DATA IF VALID (NO SERIALIZE)
    if (empty($errors)) {
        $line = $name . "|" . $gender . "\n";
        file_put_contents($file, $line, FILE_APPEND);
    }

    header("Location: ?page=2");
    exit();
}
?>

<?php if (!isset($_GET['page']) || $_GET['page'] == 1) { ?>

<h2>Vaccination Form</h2>

<form method="POST">

Name:<br>
<input type="text" name="name"><br><br>

Gender:<br>
<input type="radio" name="gender" value="Male"> Male
<input type="radio" name="gender" value="Female"> Female
<br><br>

DOB (DD/MM/YY):<br>
<input type="text" name="dob"><br><br>

Address:<br>
<textarea name="address"></textarea><br><br>

Division:<br>
<select name="division">
    <option value="">Select</option>
    <option>Dhaka</option>
    <option>Chittagong</option>
    <option>Rajshahi</option>
    <option>Khulna</option>
    <option>Barishal</option>
    <option>Sylhet</option>
    <option>Rangpur</option>
    <option>Mymensingh</option>
</select>
<br><br>

Country:<br>
<select name="country">
    <option value="">Select</option>
    <option>Bangladesh</option>
    <option>Others</option>
</select>

<br><br>

<button type="submit" name="submit">Submit</button>

</form>

<br>
<a href="?page=3">Go to Statistics</a>

<?php } ?>

<?php if (isset($_GET['page']) && $_GET['page'] == 2) { ?>

<h2>Result Page</h2>

<?php
$result = $_SESSION['result'];

if (empty($result['errors'])) {
    echo "<h3 style='color:green'>You're eligible for vaccination</h3>";
    echo "Age: " . $result['ageText'];
} else {
    echo "<h3 style='color:red'>Errors:</h3>";
    foreach ($result['errors'] as $e) {
        echo $e . "<br>";
    }
}
?>

<br><a href="?page=1">Back</a> |
<a href="?page=3">Stats</a>

<?php } ?>

<?php if (isset($_GET['page']) && $_GET['page'] == 3) { ?>

<h2>Statistics</h2>

<?php
$male = 0;
$female = 0;

// SEARCHING USING LOOP
for ($i = 0; $i < count($data); $i++) {

    $parts = explode("|", $data[$i]);

    if (isset($parts[1]) && $parts[1] == "Male") {
        $male++;
    }

    if (isset($parts[1]) && $parts[1] == "Female") {
        $female++;
    }
}
?>

Total: <?= count($data) ?><br>
Male: <?= $male ?><br>
Female: <?= $female ?><br>

<br>
<a href="?page=1">Back to Form</a>

<?php } ?>