document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("profileForm");
    const editBtn = document.getElementById("editBtn");
    const cancelBtn = document.getElementById("cancelBtn");
    const saveBtn = document.getElementById("saveBtn");
    const inputs = form.querySelectorAll("input:not([type='file']):not([id*='Password']), textarea");
    const photoBtn = document.getElementById("photoBtn");
    const photoInput = document.getElementById("photoUpload");
    const photoContainer = document.getElementById("profilePhoto");
    const placeholder = photoContainer.querySelector(".photo-placeholder");
    let originalValues = {};
    let originalSrc = photoContainer.querySelector("img")?.src || "";

    function applyCircle(img) {
        img.style.width = "100%";
        img.style.height = "100%";
        img.style.borderRadius = "50%";
        img.style.objectFit = "cover";
    }

    if (photoContainer.querySelector("img")) applyCircle(photoContainer.querySelector("img"));

    // Edit
    editBtn.addEventListener("click", () => {
        inputs.forEach(input => {
            originalValues[input.id] = input.value;
            if (input.id !== "email") input.disabled = false;
        });
        editBtn.classList.add("hidden");
        cancelBtn.classList.remove("hidden");
        saveBtn.classList.remove("hidden");
        photoBtn.classList.remove("hidden");
    });

    // Cancel
    cancelBtn.addEventListener("click", () => {
        inputs.forEach(input => {
            input.value = originalValues[input.id];
            input.disabled = true;
        });
        editBtn.classList.remove("hidden");
        cancelBtn.classList.add("hidden");
        saveBtn.classList.add("hidden");
        photoBtn.classList.add("hidden");

        // reset photo
        if (photoContainer.querySelector("img")) {
            photoContainer.querySelector("img").src = originalSrc;
            applyCircle(photoContainer.querySelector("img"));
        } else if (placeholder) {
            placeholder.style.display = originalSrc ? "none" : "block";
        }
    });

    // Photo preview
    photoBtn.addEventListener("click", () => photoInput.click());
    photoInput.addEventListener("change", () => {
        const file = photoInput.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = () => {
            photoContainer.innerHTML = `<img src="${reader.result}" alt="Profile Photo">`;
            applyCircle(photoContainer.querySelector("img"));
        };
        reader.readAsDataURL(file);
    });

    // Submit
    form.addEventListener("submit", (e) => {
        e.preventDefault(); // prevent navigation
        
        // Show loading state
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        
        const formData = new FormData(form);

        fetch(form.action, {
            method: "POST",
            body: formData
        })
            .then(res => res.json())
            .then(result => {
                console.log(result); // server response
                
                if (result.success) {
                    console.log('Update result:', result); // Debug log
                    
                    // Success - disable inputs and hide edit buttons
                    inputs.forEach(input => input.disabled = true);
                    editBtn.classList.remove("hidden");
                    cancelBtn.classList.add("hidden");
                    saveBtn.classList.add("hidden");
                    photoBtn.classList.add("hidden");

                    // Update photo with server path if photo was uploaded
                    if (result.photoUploaded && result.photoUrl) {
                        console.log('Updating photo to:', result.photoUrl);
                        
                        // Update profile photo
                        photoContainer.innerHTML = `<img src="${result.photoUrl}?t=${Date.now()}" alt="Profile Photo">`;
                        applyCircle(photoContainer.querySelector("img"));
                        originalSrc = result.photoUrl;
                        
                        // Update navbar photo if it exists
                        updateNavbarPhoto(result.photoUrl);
                        
                        // Show success message with photo update note
                        showMessage('Profile and photo updated successfully!', 'success');
                    } else {
                        // Update originalSrc to current photo if no new photo
                        const img = photoContainer.querySelector("img");
                        if (img) originalSrc = img.src;
                        
                        // Show success message
                        showMessage('Profile updated successfully!', 'success');
                    }
                } else {
                    // Error - show error message
                    showMessage(result.error || 'Failed to update profile', 'error');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                showMessage('Network error. Please try again.', 'error');
            })
            .finally(() => {
                // Reset button state
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
            });
    });
    
    // Helper function to update navbar photo
    function updateNavbarPhoto(photoUrl) {
        // Update navbar profile button photo
        const navbarProfileBtn = document.querySelector('.profile-btn-img');
        if (navbarProfileBtn) {
            navbarProfileBtn.src = photoUrl + '?t=' + Date.now();
        }
        
        // Update dropdown avatar photo
        const dropdownAvatar = document.querySelector('.avatar-img');
        if (dropdownAvatar) {
            dropdownAvatar.src = photoUrl + '?t=' + Date.now();
        }
        
        console.log('Updated navbar photos with:', photoUrl);
    }
    
    // Helper function to show messages
    function showMessage(message, type) {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create new alert
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
        `;
        
        // Insert at top of container
        const container = document.querySelector('.container');
        container.insertBefore(alert, container.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
});

document.addEventListener("DOMContentLoaded", () => {
    const firstNameInput = document.getElementById("firstName");
    const lastNameInput = document.getElementById("lastName");
    const displayName = document.querySelector(".profile-name");

    // Update display name live
    function updateDisplayName() {
        displayName.textContent = firstNameInput.value + " " + lastNameInput.value;
    }

    firstNameInput.addEventListener("input", updateDisplayName);
    lastNameInput.addEventListener("input", updateDisplayName);
});