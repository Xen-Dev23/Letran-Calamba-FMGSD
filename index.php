<?php
// You can add PHP code here if needed
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home EHS | Letran Calamba</title>
    <link rel="stylesheet" href="index.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <link rel="icon" type="image/x-icon" href="https://www.letran-calamba.edu.ph/static/img-content/logoLetran.webp">
</head>
<body>
    <header>
        <div class="letran">
            <div class="logo">
                <img src="https://www.letran-calamba.edu.ph/static/img-content/logoLetran.webp" alt="College Logo" style="height: 60px;">
            </div>

            <div class="nartel">
                <span class="pogi">Colegio de San Juan de Letran Calamba</span>
                <span class="pogi2">Bucal, Calamba City, Laguna, Philippines ● 4027</span>
            </div>
        </div>

        <div class="header-right">
            <div class="search-bar">
                <input type="text" placeholder="search">
                <span class="search-icon">🔍</span>
            </div>
            <div class="social-icons">
                <a href="https://www.instagram.com/letrancalambaph/">
                    <img src="https://www.svgrepo.com/show/452229/instagram-1.svg" alt="Instagram">
                </a>
                <a href="https://www.youtube.com/@letrancalambaofficial">
                    <img src="https://www.svgrepo.com/show/475700/youtube-color.svg" alt="YouTube">
                </a>
                <a href="https://x.com/letrancalambaph">
                    <img src="https://img.icons8.com/?size=50&id=phOKFKYpe00C&format=png" alt="X">
                </a>
                <a href="https://www.tiktok.com/@letrancalambaph">
                    <img src="https://www.svgrepo.com/show/303156/tiktok-icon-white-1-logo.svg" alt="TikTok">
                </a>
                <a href="https://www.facebook.com/LetranCalambaOfficial">
                    <img src="https://www.svgrepo.com/show/303113/facebook-icon-logo.svg" alt="Facebook">
                </a>
                <a href="https://mail.google.com/">
                    <img src="https://www.svgrepo.com/show/349378/gmail.svg" alt="Email">
                </a>
            </div>
            <div class="login-button">
                <a href="login.php">LOGIN</a>
            </div>
        </div>
    </header>

    <div class="border">
        <nav class="navbar">
            <ul class="nav-links">
                <li>
                    <a href="#" class="nav-link">ABOUT</a>
                    <ul class="dropdown">
                        <li><a href="#">About Letran Calamba</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-link">ACADEMICS</a>
                    <ul class="dropdown">
                        <li><a href="#">Programs</a></li>
                        <li><a href="#">Departments</a></li>
                        <li><a href="#">Faculty</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-link">RESEARCH</a>
                    <ul class="dropdown">
                        <li><a href="#">Publications</a></li>
                        <li><a href="#">Projects</a></li>
                        <li><a href="#">Centers</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-link">ESG</a>
                    <ul class="dropdown">
                        <li><a href="#">Sustainability</a></li>
                        <li><a href="#">Governance</a></li>
                        <li><a href="#">Initiatives</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-link">COMMUNITY</a>
                    <ul class="dropdown">
                        <li><a href="#">Events</a></li>
                        <li><a href="#">Outreach</a></li>
                        <li><a href="#">Alumni</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-link">LINKAGES</a>
                    <ul class="dropdown">
                        <li><a href="#">Partnerships</a></li>
                        <li><a href="#">Collaborations</a></li>
                        <li><a href="#">Networks</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-link">ADMISSION</a>
                    <ul class="dropdown">
                        <li><a href="#">Apply Now</a></li>
                        <li><a href="#">Requirements</a></li>
                        <li><a href="#">Fees</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const navLinks = document.querySelectorAll('.nav-link');

            navLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault(); // Prevent default link behavior
                    const dropdown = this.nextElementSibling; // Get the dropdown ul
                    const isActive = dropdown.classList.contains('active');

                    // Close all other dropdowns
                    document.querySelectorAll('.dropdown').forEach(d => {
                        if (d !== dropdown) d.classList.remove('active');
                    });

                    // Toggle the clicked dropdown
                    if (isActive) {
                        dropdown.classList.remove('active');
                    } else {
                        dropdown.classList.add('active');
                    }
                });
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.nav-links')) {
                    document.querySelectorAll('.dropdown').forEach(d => {
                        d.classList.remove('active');
                    });
                }
            });
        });
    </script>
</body>
</html>