<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kislap Login</title>

    <link rel="stylesheet" href="public/css/style.css" type="text/css">
    <link rel="stylesheet" href="public/css/userProfile.css" type="text/css">
</head>

<body>
<!-- Navbar header -->
<header class="header">
    <a href="../../main pages/index.html"><img src="../../imgs/Logo.png" class="logo" alt="DaguPin Logo"></a>
    <nav class="navbar">
        <a href="../../main pages/index.html" class="navtxt">Explore Now</a>
        <a href="../../main pages/bestspots.html">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M21 6h-18v12h4v4l4-4h10v-12z"/>
            </svg>
        </a>
        <a href="../../main pages/gallery.html">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
        </a>
        <a href="../../main pages/map.html">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
            </svg>
        </a>
        <a href="../../main pages/about.html">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z"/>
            </svg>
        </a>
    </nav>
</header>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
<div class="container">
    <div class="profile-header">
        <div class="profile-photo-container">
            <div class="profile-photo" id="profilePhoto">👤</div>
            <button class="photo-upload-btn" onclick="document.getElementById('photoUpload').click()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </button>
            <input type="file" id="photoUpload" accept="image/*">
        </div>
        <div class="profile-name" id="displayName">John Doe</div>
        <div class="profile-email" id="displayEmail">john.doe@example.com</div>
    </div>

    <div class="profile-body">
        <h2 class="section-title">Personal Information</h2>

        <form id="profileForm">
            <div class="form-grid">
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" name="firstName" value="John" disabled>
                </div>

                <div class="form-group">
                    <label for="middleName">Middle Name</label>
                    <input type="text" id="middleName" name="middleName" value="Michael" disabled>
                </div>

                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" name="lastName" value="Doe" disabled>
                </div>

                <div class="form-group">
                    <label for="phoneNumber">Phone Number</label>
                    <input type="tel" id="phoneNumber" name="phoneNumber" value="+1234567890" disabled>
                </div>

                <div class="form-group full-width">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="john.doe@example.com" disabled>
                    <span class="info-text">Email cannot be changed</span>
                </div>

                <div class="form-group full-width">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" disabled>123 Main Street, Apt 4B
New York, NY 10001</textarea>
                </div>
            </div>

            <div class="button-group">
                <button type="button" class="btn-edit" id="editBtn" onclick="enableEdit()">Edit Profile</button>
                <button type="button" class="btn-cancel hidden" id="cancelBtn" onclick="cancelEdit()">Cancel</button>
                <button type="submit" class="btn-save hidden" id="saveBtn">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>

        // Toggle buttons
        document.getElementById('editBtn').classList.add('hidden');
        document.getElementById('cancelBtn').classList.remove('hidden');
        document.getElementById('saveBtn').classList.remove('hidden');
    }

    function cancelEdit() {
        // Restore original values
        const form = document.getElementById('profileForm');
        const inputs = form.querySelectorAll('input:not([type="file"]), textarea');
        inputs.forEach(input => {
            input.value = originalValues[input.id];
            input.disabled = true;
        });

        // Toggle buttons
        document.getElementById('editBtn').classList.remove('hidden');
        document.getElementById('cancelBtn').classList.add('hidden');
        document.getElementById('saveBtn').classList.add('hidden');
    }

    // Handle form submission
    document.getElementById('profileForm').addEventListener('submit', function (e) {
        e.preventDefault();

        // Collect form data
        const formData = new FormData(this);

        // Disable inputs
        const inputs = this.querySelectorAll('input:not([type="file"]), textarea');
        inputs.forEach(input => input.disabled = true);

        // Toggle buttons
        document.getElementById('editBtn').classList.remove('hidden');
        document.getElementById('cancelBtn').classList.add('hidden');
        document.getElementById('saveBtn').classList.add('hidden');
    });
</script>
</body>
</html>

</body>
</html>