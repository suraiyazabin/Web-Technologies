<?php

// =============================================
// STATIC ASSOCIATIVE ARRAY to store all data
// =============================================
$all_records = array();

// Load existing records from file (to keep data between submissions)
$data_file = "records.txt";
if (file_exists($data_file)) {
    $file_content = file_get_contents($data_file);
    $all_records = unserialize($file_content);
    if (!is_array($all_records)) {
        $all_records = array();
    }
}

// Bangladesh divisions list
$divisions = array("Barisal", "Chattogram", "Dhaka", "Khulna", "Mymensingh", "Rajshahi", "Rangpur", "Sylhet");

// Figure out which page to show
// page=1 means show the form
// page=2 means show validation result
// page=3 means show statistics
$page = 1;
if (isset($_POST['page'])) {
    $page = $_POST['page'];
}

// =============================================
// VALIDATION (only runs when form is submitted)
// =============================================
$errors  = array();
$success = false;

if ($page == 2) {

    // --- Check Name ---
    $name = $_POST['name'];
    $name = trim($name);
    if ($name == "") {
        $errors['name'] = "Name is required.";
    }

    // --- Check Gender ---
    $gender = $_POST['gender'];
    if ($gender != "Male" && $gender != "Female") {
        $errors['gender'] = "Please select Male or Female.";
    }

    // --- Check Date of Birth ---
    $dob = $_POST['dob'];
    $dob = trim($dob);
    if ($dob == "") {
        $errors['dob'] = "Date of birth is required.";
    } else {
        // Check format DD/MM/YY
        $parts = explode("/", $dob);
        if (count($parts) != 3) {
            $errors['dob'] = "Date must be in DD/MM/YY format.";
        } else {
            $dd = $parts[0];
            $mm = $parts[1];
            $yy = $parts[2];

            // Make sure all parts are numbers
            if (!is_numeric($dd) || !is_numeric($mm) || !is_numeric($yy)) {
                $errors['dob'] = "Date must contain numbers only, like 05/03/21.";
            } else {
                $dd = (int)$dd;
                $mm = (int)$mm;
                $yy = (int)$yy;
                $yyyy = 2000 + $yy; // turn YY into YYYY (e.g. 21 -> 2021)

                // Check if date is actually valid (e.g. month not 13, day not 32)
                if (!checkdate($mm, $dd, $yyyy)) {
                    $errors['dob'] = "Invalid date. Please check day, month, year.";
                } else {
                    // Calculate age
                    $today      = date("Y-m-d");
                    $birth_date = $yyyy . "-" . $mm . "-" . $dd;

                    $today_obj = new DateTime($today);
                    $birth_obj = new DateTime($birth_date);

                    // Check if birth date is in the future
                    if ($birth_obj > $today_obj) {
                        $errors['dob'] = "Date of birth cannot be in the future.";
                    } else {
                        // Get difference
                        $diff   = $today_obj->diff($birth_obj);
                        $age_y  = $diff->y; // years
                        $age_m  = $diff->m; // months
                        $age_d  = $diff->d; // days
                        $age_text = $age_y . "Y" . $age_m . "M" . $age_d . "D";

                        // Age must be below 5 years
                        if ($age_y >= 5) {
                            $errors['age'] = "Right now we're not providing vaccination for children above 5 years. (Age: " . $age_text . ")";
                        }
                    }
                }
            }
        }
    }

    // --- Check Address ---
    $address = $_POST['address'];
    $address = trim($address);
    if ($address == "") {
        $errors['address'] = "Address is required.";
    }

    // --- Check Division ---
    $division = $_POST['division'];
    if ($division == "" || !in_array($division, $divisions)) {
        $errors['division'] = "Please select a division.";
    }

    // --- Check Country ---
    $country = $_POST['country'];
    if ($country == "") {
        $errors['country'] = "Please select a country.";
    } else if ($country == "Others") {
        $errors['country'] = "For now we're providing vaccination to only Bangladeshi children.";
    }

    // =============================================
    // If NO errors, save the record
    // =============================================
    if (count($errors) == 0) {
        $success = true;

        // Create one record as associative array
        $new_record = array(
            "name"     => $name,
            "gender"   => $gender,
            "dob"      => $dob,
            "age"      => $age_text,
            "address"  => $address,
            "division" => $division,
            "country"  => $country
        );

        // Add to the main array
        $all_records[] = $new_record;

        // Save back to file
        file_put_contents($data_file, serialize($all_records));
    }
}

// =============================================
// STATISTICS (for page 3)
// =============================================
$total_count  = 0;
$male_count   = 0;
$female_count = 0;

if ($page == 3) {
    // Array length
    $total_count = count($all_records);

    // Searching condition: loop and count by gender
    for ($i = 0; $i < count($all_records); $i++) {
        if ($all_records[$i]['gender'] == "Male") {
            $male_count++;
        } else if ($all_records[$i]['gender'] == "Female") {
            $female_count++;
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Child Vaccination Form</title>
    <style>
        /* Basic reset */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            background-color: #e8f5e9;
            padding: 30px 15px;
        }

        h1 {
            text-align: center;
            color: #2e7d32;
            margin-bottom: 5px;
        }

        p.subtitle {
            text-align: center;
            color: #555;
            margin-bottom: 25px;
            font-size: 14px;
        }

        /* Steps bar */
        .steps {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
            gap: 10px;
        }
        .step-box {
            background: #ccc;
            color: #fff;
            border-radius: 5px;
            padding: 7px 18px;
            font-size: 13px;
            font-weight: bold;
        }
        .step-box.active {
            background: #2e7d32;
        }
        .step-arrow { color: #999; font-size: 18px; }

        /* Main form box */
        .box {
            background: #fff;
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 8px;
            border: 1px solid #c8e6c9;
        }

        .box h2 {
            color: #2e7d32;
            margin-bottom: 20px;
            font-size: 20px;
            border-bottom: 2px solid #c8e6c9;
            padding-bottom: 10px;
        }

        /* Each form row */
        .field {
            margin-bottom: 18px;
        }

        .field label {
            display: block;
            font-weight: bold;
            color: #333;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .field input[type="text"],
        .field textarea,
        .field select {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #aaa;
            border-radius: 5px;
            font-size: 14px;
        }

        .field textarea {
            height: 80px;
            resize: vertical;
        }

        /* Radio buttons */
        .radio-row {
            display: flex;
            gap: 20px;
        }
        .radio-row label {
            font-weight: normal;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        /* Warning box (for country = Others) */
        .warning-box {
            background: #fff8e1;
            border-left: 4px solid #f9a825;
            padding: 10px 12px;
            margin-top: 8px;
            font-size: 13px;
            color: #5d4037;
            border-radius: 4px;
            display: none;
        }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #2e7d32;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-submit:hover { background-color: #1b5e20; }

        /* Secondary button */
        .btn-secondary {
            width: 100%;
            padding: 11px;
            background-color: #fff;
            color: #2e7d32;
            border: 2px solid #2e7d32;
            border-radius: 5px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-secondary:hover { background-color: #e8f5e9; }

        /* Success banner */
        .success-banner {
            background: #e8f5e9;
            border: 2px solid #43a047;
            border-radius: 6px;
            padding: 18px 20px;
            text-align: center;
            margin-bottom: 22px;
        }
        .success-banner .tick { font-size: 40px; }
        .success-banner h3 { color: #2e7d32; font-size: 20px; margin-top: 8px; }

        /* Error banner */
        .error-banner {
            background: #ffebee;
            border: 2px solid #e53935;
            border-radius: 6px;
            padding: 18px 20px;
            text-align: center;
            margin-bottom: 22px;
        }
        .error-banner .cross { font-size: 40px; }
        .error-banner h3 { color: #c62828; font-size: 20px; margin-top: 8px; }

        /* Error list on page 2 */
        .error-list { list-style: none; margin-top: 10px; }
        .error-list li {
            background: #ffebee;
            border-left: 4px solid #e53935;
            padding: 10px 14px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-size: 14px;
            color: #b71c1c;
        }
        .error-list li strong { text-transform: capitalize; }

        /* Summary table on page 2 */
        .summary-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        .summary-table tr td { padding: 8px 10px; border-bottom: 1px solid #e0e0e0; }
        .summary-table tr td:first-child { font-weight: bold; color: #555; width: 35%; }

        /* Stats boxes on page 3 */
        .stat-row {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        .stat-box {
            flex: 1;
            background: #2e7d32;
            color: white;
            border-radius: 7px;
            padding: 20px;
            text-align: center;
        }
        .stat-box .big-num {
            font-size: 42px;
            font-weight: bold;
            line-height: 1;
        }
        .stat-box .stat-label {
            font-size: 13px;
            margin-top: 6px;
            opacity: 0.85;
        }
        .stat-box.male-box   { background: #1565c0; }
        .stat-box.female-box { background: #ad1457; }

        /* Records table on page 3 */
        .records-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .records-table th {
            background: #2e7d32;
            color: white;
            padding: 9px 10px;
            text-align: left;
        }
        .records-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        .records-table tr:nth-child(even) td { background: #f1f8e9; }

        .no-record { text-align: center; color: #888; padding: 30px; font-size: 14px; }

        hr.line { border: none; border-top: 1px solid #e0e0e0; margin: 20px 0; }

        .hint { font-size: 12px; color: #777; margin-top: 4px; }
    </style>
</head>
<body>

<h1>🏥 Child Vaccination Portal</h1>
<p class="subtitle">Government of Bangladesh — Immunisation Programme</p>

<!-- Step indicator -->
<div class="steps">
    <div class="step-box <?php if($page == 1) echo 'active'; ?>">Step 1: Form</div>
    <div class="step-arrow">→</div>
    <div class="step-box <?php if($page == 2) echo 'active'; ?>">Step 2: Result</div>
    <div class="step-arrow">→</div>
    <div class="step-box <?php if($page == 3) echo 'active'; ?>">Step 3: Statistics</div>
</div>


<!-- ============================================================
     PAGE 1 — Registration Form
============================================================ -->
<?php if ($page == 1): ?>

<div class="box">
    <h2>📋 Child Registration Form</h2>

    <form method="POST" action="">
        <!-- Hidden field to tell PHP which page to go to next -->
        <input type="hidden" name="page" value="2">

        <!-- NAME -->
        <div class="field">
            <label>Full Name *</label>
            <input type="text" name="name" placeholder="Enter child's full name">
        </div>

        <!-- GENDER -->
        <div class="field">
            <label>Gender *</label>
            <div class="radio-row">
                <label>
                    <input type="radio" name="gender" value="Male"> Male
                </label>
                <label>
                    <input type="radio" name="gender" value="Female"> Female
                </label>
            </div>
        </div>

        <!-- DATE OF BIRTH -->
        <div class="field">
            <label>Date of Birth * (DD/MM/YY)</label>
            <input type="text" name="dob" placeholder="e.g. 05/03/21">
            <p class="hint">Child must be under 5 years old.</p>
        </div>

        <!-- ADDRESS -->
        <div class="field">
            <label>Address *</label>
            <textarea name="address" placeholder="House no, road, area, city..."></textarea>
        </div>

        <!-- DIVISION -->
        <div class="field">
            <label>Division *</label>
            <select name="division">
                <option value="">-- Select Division --</option>
                <?php
                // Loop through divisions array and create options
                foreach ($divisions as $div) {
                    echo '<option value="' . $div . '">' . $div . '</option>';
                }
                ?>
            </select>
        </div>

        <!-- COUNTRY -->
        <div class="field">
            <label>Country *</label>
            <select name="country" id="countrySelect" onchange="checkCountry()">
                <option value="">-- Select Country --</option>
                <option value="Bangladesh">Bangladesh</option>
                <option value="Others">Others</option>
            </select>
            <!-- This warning shows if "Others" is picked (JS only for preview, PHP validates) -->
            <div class="warning-box" id="countryWarning">
                ⚠️ For now we're providing vaccination to only Bangladeshi children.
            </div>
        </div>

        <hr class="line">
        <button type="submit" class="btn-submit">Submit →</button>
    </form>
</div>

<script>
// Show a warning when "Others" is selected in country dropdown
function checkCountry() {
    var selected = document.getElementById("countrySelect").value;
    var warning  = document.getElementById("countryWarning");
    if (selected == "Others") {
        warning.style.display = "block";
    } else {
        warning.style.display = "none";
    }
}
</script>


<!-- ============================================================
     PAGE 2 — Validation Result
============================================================ -->
<?php elseif ($page == 2): ?>

<div class="box">
    <h2>📄 Registration Result</h2>

    <?php if ($success): ?>

        <!-- SUCCESS -->
        <div class="success-banner">
            <div class="tick">✅</div>
            <h3>You're eligible for vaccination!</h3>
            <p style="color:#555; margin-top:6px; font-size:14px;">Your registration has been saved successfully.</p>
        </div>

        <p style="font-weight:bold; margin-bottom:8px; color:#333;">Your Submitted Details:</p>
        <table class="summary-table">
            <tr><td>Name</td><td><?php echo $name; ?></td></tr>
            <tr><td>Gender</td><td><?php echo $gender; ?></td></tr>
            <tr><td>Date of Birth</td><td><?php echo $dob; ?></td></tr>
            <tr><td>Age</td><td><?php echo $age_text; ?></td></tr>
            <tr><td>Address</td><td><?php echo $address; ?></td></tr>
            <tr><td>Division</td><td><?php echo $division; ?></td></tr>
            <tr><td>Country</td><td><?php echo $country; ?></td></tr>
        </table>

    <?php else: ?>

        <!-- ERRORS -->
        <div class="error-banner">
            <div class="cross">❌</div>
            <h3>Registration Failed</h3>
            <p style="color:#555; margin-top:6px; font-size:14px;">Please fix the errors below and try again.</p>
        </div>

        <p style="font-weight:bold; color:#c62828; margin-bottom:10px;">Errors Found:</p>
        <ul class="error-list">
            <?php
            // Loop through all errors and display them
            foreach ($errors as $field => $message) {
                echo '<li><strong>' . $field . ':</strong> ' . $message . '</li>';
            }
            ?>
        </ul>

    <?php endif; ?>

    <hr class="line">

    <!-- Button to go to page 3 (statistics) -->
    <form method="POST" action="">
        <input type="hidden" name="page" value="3">
        <button type="submit" class="btn-submit">📊 View Statistics</button>
    </form>

    <!-- Button to go back to form -->
    <form method="GET" action="">
        <button type="submit" class="btn-secondary">← Submit Another Entry</button>
    </form>
</div>


<!-- ============================================================
     PAGE 3 — Statistics
============================================================ -->
<?php elseif ($page == 3): ?>

<div class="box">
    <h2>📊 Submission Statistics</h2>

    <!-- Stat boxes -->
    <div class="stat-row">
        <div class="stat-box">
            <div class="big-num"><?php echo $total_count; ?></div>
            <div class="stat-label">Total Submitted</div>
        </div>
        <div class="stat-box male-box">
            <div class="big-num"><?php echo $male_count; ?></div>
            <div class="stat-label">Male</div>
        </div>
        <div class="stat-box female-box">
            <div class="big-num"><?php echo $female_count; ?></div>
            <div class="stat-label">Female</div>
        </div>
    </div>

    <!-- Records table -->
    <p style="font-weight:bold; color:#333; margin-bottom:10px;">
        All Records (Total: <?php echo $total_count; ?>)
    </p>

    <?php if ($total_count > 0): ?>

        <table class="records-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Age</th>
                    <th>Division</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Loop through records array and display each one
                for ($i = 0; $i < count($all_records); $i++) {
                    echo '<tr>';
                    echo '<td>' . ($i + 1) . '</td>';
                    echo '<td>' . $all_records[$i]['name'] . '</td>';
                    echo '<td>' . $all_records[$i]['gender'] . '</td>';
                    echo '<td>' . $all_records[$i]['age'] . '</td>';
                    echo '<td>' . $all_records[$i]['division'] . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>

    <?php else: ?>
        <div class="no-record">No records found yet.</div>
    <?php endif; ?>

    <hr class="line">

    <form method="GET" action="">
        <button type="submit" class="btn-submit">← Register Another Child</button>
    </form>
</div>

<?php endif; ?>

</body>
</html>