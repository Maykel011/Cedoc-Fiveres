<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>San Juan CDRRMO | About</title>
    <link rel="shortcut icon" href="assets/icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/internAbout.css">
   
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

            <div class = "about">

                <h3>San Juan CDRRMO: Leading Disaster Resilience and Emergency Response</h3>

                <p>The San Juan City Disaster Risk Reduction and Management Office (CDRRMO) has demonstrated a steadfast 
                    commitment to enhancing disaster preparedness and response. In 2022, the city institutionalized the 
                    Disaster Risk Reduction and Management in Health (DRRM-H) program through City Ordinance No. 50, 
                    integrating disaster risk strategies into the health system.</p>

                <p>In December 2022, San Juan City received recognition for its compliance with disaster risk reduction and 
                    management standards, achieving a score of 1.87. The award was accepted by CDRRMO Department Head Saripada 
                    “Tong” L. Pacasum Jr.</p>

                <p>The CDRRMO has also engaged in collaborative training programs, such as the five-day Emergency Rescue Training 
                    (ERT) conducted in partnership with the 27th Naval Affiliated Reserve Group (Rescue Recon 1) in February 2023. </p>

            </div>

            <br><br><br>

            <img src="assets/quote.jpg" class = "quote">

            <br><br><br>

            <h1>INTERN @ SAN JUAN CDRRMO</h1>
            <hr style="width: 20%;">

            <br>

            <div class="benefits">
            <img src="img/img1.jpg" alt="Internship Benefits">
            <div class="text">
                <h3>What San Juan CDRRMO Provides to Interns During the Internship&nbsp;&nbsp;&nbsp;&nbsp;▶</h3>
                <p>San Juan City Disaster Risk Reduction and Management Office (CDRRMO) ensures that interns gain valuable hands-on experience and professional development opportunities. During the internship, interns receive:</p>
                <ul>
                    <li><b>Practical Learning Experience</b> – Interns work on real-world projects related to disaster preparedness, response, and recovery, applying their technical and creative skills in a meaningful way.</li>
                    <li><b>Mentorship & Supervision</b> – Interns are guided by experienced professionals who provide mentorship, feedback, and support throughout the program.</li>
                    <li><b>Flexible Work Schedule</b> – Internship schedules are adjustable based on the intern’s academic requirements and availability.</li>
                    <li><b>Skill Development</b> – Interns gain hands-on training in IT system management, multimedia production, animation, and engineering solutions, depending on their field of study.</li>
                    <li><b>Exposure to Disaster Management Operations</b> – Interns participate in disaster risk reduction initiatives, emergency response planning, and public awareness campaigns.</li>
                    <li><b>Networking Opportunities</b> – Interns collaborate with professionals in various fields, providing valuable connections for future career opportunities.</li>
                    <li><b>Certificate of Completion</b> – Upon successfully finishing the internship program, interns receive a certificate as recognition of their contributions and acquired experience.</li>
                </ul>
            </div>
            </div><br><br><br>


            <center><h2 style="font-family:'Serpentine', sans-serif; font-size: 30px;">Program to apply @ SJCDRRMO</h2></center>
            <hr style="width: 20%;"><br><br>
            <div class="container">
                <?php
                    $cards = [
                        [
                            "title" => "Information Technology", 
                            "description" => "Developing and managing secure digital infrastructures, networks, and databases.", 
                            "full_description" => [
                                "Maintaining and updating the CDRRMO website",
                                "Developing online applications and enhancing security",
                                "Setting up and managing LAN/Wi-Fi networks",
                                "Troubleshooting connectivity issues and configuring network devices.",
                                "Diagnosing and fixing PC and printer issues",
                                "Performing hardware/software maintenance",
                                "Assisting employees with IT-related concerns",
                                "Assembling, upgrading, and optimizing office computers"
                            ],
                            "image" => "img/it.jpg"
                        ],
                        [
                            "title" => "Animation", 
                            "description" => "Creating captivating animations, motion graphics, and visual effects for storytelling.", 
                            "full_description" => [
                                "Assisting in the creation of 2D and 3D animations for various media.",
                                "Designing motion graphics and visual effects for digital content.",
                                "Collaborating with designers and editors to enhance storytelling.",
                                "Editing and refining animations based on feedback.",
                                "Researching animation trends to improve creative output.",
                                "Optimizing animations for different platforms and devices."

                            ],
                            "image" => "img/ani.jpg"
                        ],
                        [
                            "title" => "Computer Engineering", 
                            "description" => "Designing and developing hardware and software solutions for smart systems.", 
                            "full_description" => [
                                "Assisting in hardware and software development for embedded systems.",
                                "Designing and testing circuits and microcontrollers for various applications.",
                                "Troubleshooting and debugging hardware/software integration issues.",
                                "Supporting network and system optimization for efficiency.",
                                "Researching and implementing automation techniques.",
                                "Assisting in the development of IoT and AI-based projects."
                            ],
                            "image" => "img/eng.jpg"
                        ],
                        [
                            "title" => "Multimedia", 
                            "description" => "Combining text, graphics, video, and audio for interactive and engaging content.", 
                            "full_description" => [
                                "Creating and editing visual content, including graphics and videos.",
                                "Assisting in designing user interfaces and interactive media.",
                                "Developing multimedia presentations for various digital platforms.",
                                "Editing audio and video materials for marketing and educational purposes.",
                                "Collaborating with teams to enhance branding through multimedia.",
                                "Researching trends in digital media to improve creative projects."
                            ],
                            "image" => "img/multi.jpg"
                        ]
                    ];

                    foreach ($cards as $index => $card) {
                        echo "<div class='card' onclick='openModal({$index})'>
                                <img src='{$card['image']}' alt='{$card['title']}'>
                                <h3>{$card['title']}</h3>
                                <p>{$card['description']}</p>
                            </div>";

                        
                        echo "<div id='fullDesc{$index}' style='display: none;'>".implode("|", $card['full_description'])."</div>";
                        echo "<div id='imageSrc{$index}' style='display: none;'>{$card['image']}</div>";
                    }
                ?>
            </div>

            <div class="modal" id="modal">
                <div class="modal-content">
                    <span class="close-btn" onclick="closeModal()"></span>
                    <img id="modalImage" src="" alt="">
                    <h2 id="modalTitle"></h2>
                    <ul id="modalDescription"></ul>
                </div>
            </div>



            <br><br><br><br>


    <footer>
            <div class="social-links">
            <a href="https://web.facebook.com/SanJuanCDRRMO" target="_blank"><i class="fab fa-facebook"></i></a>
            <a href="https://x.com/sanjuancityncr?lang=en" target="_blank"><i class="fab fa-twitter"></i></a>
            <a href="https://www.instagram.com/sanjuancityncr/" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="https://www.sanjuancity.gov.ph/SanJuanCity/Makabagong_SJ_Departments_Selected/45" target="_blank"><i class="fas fa-globe"></i></a>
            </div>
            <div class="credits">
           <p> © 2025 SAN JUAN CITY DISASTER RISK REDUCTION AND MANAGEMENT OFFICE.</p>
           <p>Hotline: 137-135<p>
                <p>All Rights Reserved.<p>
            </div>
    </footer>
    <script src = "js/internAbout.js"></script>
</body>
</html>
