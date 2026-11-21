document.getElementById("image-upload-form").addEventListener("submit", function(event) {
    event.preventDefault();

    const volunteerName = document.getElementById("volunteer_name").value;
    const fileInput = document.getElementById("image-input");
    const caption = document.getElementById("caption").value;

    if (fileInput.files.length > 0) {
        const formData = new FormData();
        formData.append("volunteer_name", volunteerName);
        formData.append("image", fileInput.files[0]);
        formData.append("caption", caption);

        fetch("upload_image.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            fileInput.value = "";
            document.getElementById("caption").value = "";
            fetchUploadedImages();
        })
        .catch(error => console.error("Error:", error));
    }
});
