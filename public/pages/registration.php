<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>KislapAI</title>

    <link rel="stylesheet" href="../static/css/styles/registration.css" type="text/css">
    <link rel="stylesheet" href="../static/css/styles/style.css" type="text/css">
</head>

<body>
    <!-- Navbar header -->
    <header class="header">
        <a href="../../main pages/index.html"><img src="../../imgs/Logo.png" class="logo" alt="DaguPin Logo"></a>
        <nav class="navbar">
            <a href="../../main pages/index.html" class="navtxt">Explore Now</a>
            <a href="../../main pages/bestspots.html">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M21 6h-18v12h4v4l4-4h10v-12z" />
                </svg>
            </a>
            <a href="../../main pages/gallery.html"><svg xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </a>
            <a href="../../main pages/map.html"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </a>
            <a href="../../main pages/about.html"><svg xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                </svg>
            </a>
        </nav>
    </header>

    <form class="form" method="post" enctype="multipart/form-data" action="application/registration">
        <div class="fullname">
            <div class="form-group">
                <label for="lastName">Last Name</label>
                <input id="lastName" name="lastName" type="text" required placeholder="Enter your Last Name">
            </div>

            <div class="form-group">
                <label for="firstName">First Name</label>
                <input id="firstName" name="firstName" type="text" required placeholder="Enter your First Name">
            </div>

            <div class="form-group">
                <label for="middleName">Middle Name</label>
                <input id="middleName" name="middleName" type="text" required placeholder="Enter your Middle Name">
            </div>
        </div>

        <div class="form-group">
            <label for="phoneNumber">Phone Number</label>
            <input id="phoneNumber" type="tel" name="phoneNumber" required
                placeholder="Enter Your Phone Number ex. 09XXXXXXXXX">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" required placeholder="example@example.com">
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <input id="address" type="text" name="address" required
                placeholder="123 Example District, Example City, Example, 1234">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required placeholder="Enter your Password">
        </div>

        <div class="form-group">
            <label for="images">Upload Work Samples (Up to 4)</label>
            <input id="images" type="file" name="images" multiple>
        </div>

        <div class="form-group">
            <label for="resume">Upload Resume</label>
            <input id="resume" type="file" name="resume">
        </div>


        <button type="submit">Apply</button>
    </form>
    <script src="limit-files.js" defer></script>
</body>

</html>