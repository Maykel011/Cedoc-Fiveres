
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>San Juan CDRRMO | Internship Application</title>
    <link rel="shortcut icon" href="assets/icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/InternApplication.css">
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
    <div class="container">
    <h2 id="program-year-heading">San Juan CDRRMO Internship Program</h2>
        <script>
            const currentYear = new Date().getFullYear();
            document.getElementById("program-year-heading").innerHTML = `${currentYear} San Juan CDRRMO Internship Program`;
        </script>
        <form action="InternApplicationBE.php" method="post" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Full Name<span style="color: red;">*</span></label>
                    <input type="text" id="full_name" name="full_name" placeholder="ex. Juan Dela Cruz" required>
                </div>
                <div class="form-group">
                    <label for="program_course">Program/Course<span style="color: red;">*</span></label>
                    <input type="text" id="program_course" name="program_course" placeholder="ex. Bachelor of Science in Information Technology" required>
                </div>
            <div class="form-group">
                <label for="school_university">School/University<span style="color: red;">*</span></label>
                <select id="school_university" name="school_university" onchange="handleSchoolChange()" style="width: 95%; height: 33px;" required>
                    <option value="" disabled selected>Select School/University</option>
                    <option value="JRU">Jose Rizal University (JRU)</option>
                    <option value="FEU">Far Eastern University (FEU)</option>
                    <option value="PUP">Polytechnic University of the Philippines (PUP)</option>
                    <option value="STI">Systems Technology Institute (STI)</option>
                    <option value="Others">Others:</option>
                </select>
                <input type="text" id="other_school" name="other_school" placeholder="Please specify" style="display: none; margin-top: 10px;" />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex-basis: 100%;">
                    <label for="address">Address<span style="color: red;">*</span></label>
                    <input type="text" id="address" name="address" style="width: 96.7%;" placeholder="ex. City Government of San Juan. Pinaglabanan St, Corner Dr.P.A.Narciso, San Juan, 1500 Metro Manila" required>
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
                    <input type="text" id="roles" name="roles" min="10" max="99"  placeholder="ex. Multimedia" required>
                </div>
                <div class="form-group">
                    <label for="resume">Resume 10MB max<span style="color: red;">*</span></label>
                    <input type="file" id="resume" name="resume" accept=".doc,.docx,.pdf" required style="border: 1px solid #ccc; border-radius: 4px; padding: 5px; width: 94.5%;">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="moa">MOA 10MB max (If school and company doesn't have exsisting MOA)</label>
                    <input type="file" id="moa" name="moa" accept=".doc,.docx,.pdf" style="border: 1px solid #ccc; border-radius: 4px; padding: 5px; width: 94.5%;">
                </div>
                <div class="form-group">
                    <label for="recom">Recommendation Letter 10MB max</label>
                    <input type="file" id="recom" name="recom" accept=".doc,.docx,.pdf" style="border: 1px solid #ccc; border-radius: 4px; padding: 5px; width: 94.5%;">
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
        <textarea id="q5" name="q5" rows="5" placeholder="Your answer" required style="width:45%"></textarea>
    </div>
</div>


            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>
    <script src = "js/internApplication.js"></script>
</body>
</html>
