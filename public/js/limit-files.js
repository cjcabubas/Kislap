
const fileInput = document.getElementById('works');
fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 4) {
        alert("You can only upload up to 4 files.");
        fileInput.value = ""; // clears the selection
    }
});
