
    // Toggle Dropdown
    const profileBtn = document.getElementById('profileBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');

    profileBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    dropdownMenu.classList.toggle('show');
    profileBtn.classList.toggle('active');
});

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
    if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
    dropdownMenu.classList.remove('show');
    profileBtn.classList.remove('active');
}
});

    // Logout function
    function logout() {
    if (confirm('Are you sure you want to logout?')) {
    window.location.href = 'logout.php';
}
}

    // Demo: Simulate different user types
    function simulateUser(type) {
    // This is just for demo purposes
    // In production, this would be handled by your PHP session
    alert(`Demo: This would show the menu for ${type}.\n\nIn production, reload the page with different session variables to see different menus.`);
}

    // Add active state to current page
    const currentPath = window.location.pathname;
    document.querySelectorAll('.navbar a:not(.logo-link)').forEach(link => {
    if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href'))) {
    link.classList.add('active');
}
});
