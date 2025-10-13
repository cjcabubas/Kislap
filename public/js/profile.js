document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("profileForm");
    const editBtn = document.getElementById("editBtn");
    const cancelBtn = document.getElementById("cancelBtn");
    const saveBtn = document.getElementById("saveBtn");
    const inputs = form.querySelectorAll("input:not([type='file']), textarea");
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
        const formData = new FormData(form);

        fetch(form.action, {
            method: "POST",
            body: formData
        })
            .then(res => res.text())
            .then(result => {
                console.log(result); // server response
                inputs.forEach(input => input.disabled = true);
                editBtn.classList.remove("hidden");
                cancelBtn.classList.add("hidden");
                saveBtn.classList.add("hidden");
                photoBtn.classList.add("hidden");

                // Update originalSrc to current photo
                const img = photoContainer.querySelector("img");
                if (img) originalSrc = img.src;
            })
            .catch(err => console.error(err));
    });
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