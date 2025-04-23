<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cedoc_fiveres";

// Initialize variables
$success = isset($_GET['success']) ? $_GET['success'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;

// Only process the form if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        header("Location: internApplication.php?error=Database+connection+failed");
        exit();
    }

    // File upload directory - relative to server root
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/Cedoc-Fiveres/InternDocuments/";

    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            header("Location: internApplication.php?error=Failed+to+create+upload+directory");
            exit();
        }
    }

    // Process form data
    $full_name = $_POST['full_name'] ?? '';
    $program_course = $_POST['program_course'] ?? '';
    $school_university = $_POST['school_university'] ?? '';
    $other_school = $_POST['other_school'] ?? '';
    $address = $_POST['address'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    $email = $_POST['email'] ?? '';
    $ojt_hours = $_POST['ojt_hours'] ?? '';
    $roles = $_POST['roles'] ?? '';
    $q1 = $_POST['q1'] ?? '';
    $q2 = $_POST['q2'] ?? '';
    $q3 = $_POST['q3'] ?? '';
    $q4 = $_POST['q4'] ?? '';
    $q5 = $_POST['q5'] ?? '';

    // Handle file uploads
    $resumePath = '';
    $moaPath = '';
    $recomPath = '';

    // Function to handle file uploads
    // Update the uploadFile function
function uploadFile($file, $uploadDir, $allowedTypes = ['pdf', 'doc', 'docx']) {
    if (!isset($file) || $file['error'] != UPLOAD_ERR_OK) {
        return null;
    }

    // Get file extension
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Check if file type is allowed
    if (!in_array($fileExt, $allowedTypes)) {
        return null;
    }

    // Check file size (10MB max)
    if ($file['size'] > 10 * 1024 * 1024) {
        return null;
    }

    // Generate unique filename
    $fileName = uniqid('', true) . '.' . $fileExt;
    $relativePath = "InternDocuments/" . $fileName;
    $absolutePath = $uploadDir . $fileName;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $absolutePath)) {
        return $relativePath;
    }

    return null;
}

    // Upload resume (required)
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == UPLOAD_ERR_OK) {
        $resumePath = uploadFile($_FILES['resume'], $uploadDir);
        if (!$resumePath) {
            header("Location: internApplication.php?error=Invalid+resume+file");
            exit();
        }
    } else {
        header("Location: internApplication.php?error=Resume+is+required");
        exit();
    }

    // Upload MOA (optional)
    if (isset($_FILES['moa']) && $_FILES['moa']['error'] == UPLOAD_ERR_OK) {
        $moaPath = uploadFile($_FILES['moa'], $uploadDir);
    }

    // Upload recommendation letter (optional)
    if (isset($_FILES['recom']) && $_FILES['recom']['error'] == UPLOAD_ERR_OK) {
        $recomPath = uploadFile($_FILES['recom'], $uploadDir);
    }

    // If school is "Others", use the other_school value
    if ($school_university === 'Others' && !empty($other_school)) {
        $school_university = $other_school;
    }

    // Insert data into database
    try {
        $stmt = $conn->prepare("INSERT INTO internship_applications (
            full_name, program_course, school_university, address, contact_number, 
            email, ojt_hours, roles, resume_path, moa_path, recom_path,
            q1, q2, q3, q4, q5, application_date
        ) VALUES (
            :full_name, :program_course, :school_university, :address, :contact_number, 
            :email, :ojt_hours, :roles, :resume_path, :moa_path, :recom_path,
            :q1, :q2, :q3, :q4, :q5, NOW()
        )");

        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':program_course', $program_course);
        $stmt->bindParam(':school_university', $school_university);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':contact_number', $contact_number);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':ojt_hours', $ojt_hours);
        $stmt->bindParam(':roles', $roles);
        $stmt->bindParam(':resume_path', $resumePath);
        $stmt->bindParam(':moa_path', $moaPath);
        $stmt->bindParam(':recom_path', $recomPath);
        $stmt->bindParam(':q1', $q1);
        $stmt->bindParam(':q2', $q2);
        $stmt->bindParam(':q3', $q3);
        $stmt->bindParam(':q4', $q4);
        $stmt->bindParam(':q5', $q5);

        $stmt->execute();

        header("Location: internApplication.php?success=1");
        exit();
    } catch(PDOException $e) {
        // Clean up uploaded files if database insert fails
        if ($resumePath && file_exists($_SERVER['DOCUMENT_ROOT'] . $resumePath)) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $resumePath);
        }
        if ($moaPath && file_exists($_SERVER['DOCUMENT_ROOT'] . $moaPath)) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $moaPath);
        }
        if ($recomPath && file_exists($_SERVER['DOCUMENT_ROOT'] . $recomPath)) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $recomPath);
        }
        
        header("Location: internApplication.php?error=Database+error");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>San Juan CDRRMO | Internship Application</title>
    <link rel="shortcut icon" href="assets/icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1, h2, h3 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        /* Navbar Styles */
        nav {
            background-color:  #1b2560;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        nav img {
            height: 50px;
        }

        nav ul {
            display: flex;
            list-style: none;
        }

        nav ul li {
            margin-left: 20px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        nav ul li a:hover {
            color: #3498db;
        }

        /* Form Styles */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
            gap: 20px;
        }

        .form-group {
            flex: 1;
            min-width: 250px;
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="tel"],
        input[type="email"],
        input[type="number"],
        input[type="file"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .submit-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s;
            display: block;
            margin: 30px auto 0;
        }

        .submit-btn:hover {
            background-color: #2980b9;
        }

        /* Datetime Styles */
        .datetime {
            text-align: right;
            padding: 10px 20px;
            background-color: #f8f9fa;
            font-size: 14px;
            color: #6c757d;
        }

        /* Alert Messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            
            .form-group {
                width: 100%;
            }
            
            nav ul {
                flex-direction: column;
                align-items: flex-end;
            }
            
            nav ul li {
                margin: 5px 0;
            }
        }
        .background-slider {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    transition: opacity 1.5s ease-in-out, transform 1s ease-in-out;
    z-index: -1; /* send it to the back */
}

.background-slider img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    animation: fade 10s infinite alternate; /* simple fade effect */
}

@keyframes fade {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

    </style>
</head>

<body onload="updateDateTime()">
    <div class="datetime" id="datetime"></div>
    <nav>
        <a href="internHome.php">
            <img src="assets/logo.png" alt="logo">
        </a>
        <ul>
            <li><a href="internHome.php">Home</a></li>
            <li><a href="internAbout.php">About</a></li>
            <li><a href="internApplication.php">Apply Now!</a></li>
        </ul>
    </nav>
    <div class="background-slider">
    <img id="slider" src="assets/heroimg/hero1.jpg" alt="Slideshow">
    
</div>

    <div class="container">
        <?php if ($success): ?>
            <div class="alert alert-success">
                Your application has been submitted successfully! We will review your application and contact you soon.
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                There was an error submitting your application. Please try again or contact support.
            </div>
        <?php endif; ?>
        
        <h2 id="program-year-heading">San Juan CDRRMO Internship Program</h2>
        
        <form id="applicationForm" action="InternApplicationBE.php" method="post" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Full Name<span style="color: red;">*</span></label>
                    <input type="text" id="full_name" name="full_name" placeholder="ex. Juan Dela Cruz" required>
                </div>
                <div class="form-group">
                    <label for="program_course">Program/Course<span style="color: red;">*</span></label>
                    <input type="text" id="program_course" name="program_course" placeholder="ex. Bachelor of Science in Information Technology" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="school_university">School/University<span style="color: red;">*</span></label>
                    <select id="school_university" name="school_university" onchange="handleSchoolChange()" required>
                        <option value="" disabled selected>Select School/University</option>
                        <option value="JRU">Jose Rizal University (JRU)</option>
                        <option value="FEU">Far Eastern University (FEU)</option>
                        <option value="PUP">Polytechnic University of the Philippines (PUP)</option>
                        <option value="STI">Systems Technology Institute (STI)</option>
                        <option value="Others">Others:</option>
                    </select>
                    <input type="text" id="other_school" name="other_school" placeholder="Please specify" style="display: none; margin-top: 10px;" />
                </div>
                <div class="form-group">
                    <label for="address">Address<span style="color: red;">*</span></label>
                    <input type="text" id="address" name="address" placeholder="ex. City Government of San Juan. Pinaglabanan St, Corner Dr.P.A.Narciso, San Juan, 1500 Metro Manila" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="contact_number">Contact Number<span style="color: red;">*</span></label>
                    <input type="tel" id="contact_number" name="contact_number" pattern="[0-9]{11}" placeholder="09123456789" maxlength="11" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address<span style="color: red;">*</span></label>
                    <input type="email" id="email" name="email" pattern="^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com|outlook\.com)$" placeholder="ex. juandelacruz123@gmail.com" required>
                </div>
                <div class="form-group">
                    <label for="ojt_hours">OJT Hours<span style="color: red;">*</span></label>
                    <input type="number" id="ojt_hours" name="ojt_hours" min="10" max="2000" placeholder="486" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="roles">Role/Position<span style="color: red;">*</span></label>
                    <input type="text" id="roles" name="roles" placeholder="ex. Multimedia" required>
                </div>
                <div class="form-group">
                    <label for="resume">Resume (PDF or DOCX, 10MB max)<span style="color: red;">*</span></label>
                    <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
                    <small class="file-hint">Accepted formats: PDF, DOC, DOCX</small>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="moa">MOA (PDF or DOCX, 10MB max)</label>
                    <input type="file" id="moa" name="moa" accept=".pdf,.doc,.docx">
                    <small class="file-hint">If school and company doesn't have existing MOA</small>
                </div>
                <div class="form-group">
                    <label for="recom">Recommendation Letter (PDF or DOCX, 10MB max)</label>
                    <input type="file" id="recom" name="recom" accept=".pdf,.doc,.docx">
                </div>
            </div>
            
            <h2>Questions</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="q1">Tell me about yourself?<span style="color: red;">*</span></label>
                    <textarea id="q1" name="q1" rows="4" placeholder="Your answer" required></textarea>
                </div>
                <div class="form-group">
                    <label for="q2">What are your strengths and weaknesses?<span style="color: red;">*</span></label>
                    <textarea id="q2" name="q2" rows="4" placeholder="Your answer" required></textarea>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="q3">What do you hope to gain from this internship?<span style="color: red;">*</span></label>
                    <textarea id="q3" name="q3" rows="5" placeholder="Your answer" required></textarea>
                </div>
                <div class="form-group">
                    <label for="q4">How do you handle deadlines and pressure?<span style="color: red;">*</span></label>
                    <textarea id="q4" name="q4" rows="5" placeholder="Your answer" required></textarea>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="q5">What makes you stand out from the rest?<span style="color: red;">*</span></label>
                    <textarea id="q5" name="q5" rows="5" placeholder="Your answer" required></textarea>
                </div>
            </div>

            <button type="submit" class="submit-btn">Submit Application</button>
        </form>
    </div>

    <script>
        // Set current year in heading
        const currentYear = new Date().getFullYear();
        document.getElementById("program-year-heading").innerHTML = `${currentYear} San Juan CDRRMO Internship Program`;
        
        // Handle school dropdown change
        function handleSchoolChange() {
            const schoolSelect = document.getElementById('school_university');
            const otherSchoolInput = document.getElementById('other_school');
            
            if (schoolSelect.value === 'Others') {
                otherSchoolInput.style.display = 'block';
                otherSchoolInput.required = true;
            } else {
                otherSchoolInput.style.display = 'none';
                otherSchoolInput.required = false;
                otherSchoolInput.value = '';
            }
        }
        
        // Update date and time
        function updateDateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            document.getElementById('datetime').textContent = now.toLocaleDateString('en-US', options);
            
            // Update every second
            setTimeout(updateDateTime, 1000);
        }
        
        // File size validation
        document.getElementById('applicationForm').addEventListener('submit', function(e) {
            const resume = document.getElementById('resume');
            const moa = document.getElementById('moa');
            const recom = document.getElementById('recom');
            const maxSize = 10 * 1024 * 1024; // 10MB
            
            if (resume.files.length > 0 && resume.files[0].size > maxSize) {
                alert('Resume file size exceeds 10MB limit');
                e.preventDefault();
                return;
            }
            
            if (moa.files.length > 0 && moa.files[0].size > maxSize) {
                alert('MOA file size exceeds 10MB limit');
                e.preventDefault();
                return;
            }
            
            if (recom.files.length > 0 && recom.files[0].size > maxSize) {
                alert('Recommendation letter file size exceeds 10MB limit');
                e.preventDefault();
                return;
            }
        });
    </script>
</body>
</html>