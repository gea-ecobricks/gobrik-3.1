

<?php require_once ("../meta/admin-review-$lang.php");?>


<STYLE>


 /* Ensure the parent container can resize and show content that expands */
#validate-introduction {
    position: relative;
    overflow: visible; /* Allows content to grow beyond its bounds */
    transition: height 0.3s ease; /* Smooth transition for height change */
}

.photo-container {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: visible; /* Make sure the container grows with the rotated image */
    margin: 0 auto;
    text-align: center;
    background: var(--lighter);
    width: 100%;  /* Adjust as needed */
    height: auto; /* Adjust as needed */
}

.rotatable-photo {
    max-width: 100%; /* Ensure image does not exceed the container width */
    height: auto;
    transition: transform 0.3s ease; /* Smooth transition for the rotation */
    display: block;
}

/* Rotate Controls */
.rotate-controls {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Rotate and Confirm Buttons */
.rotate-button, .confirm-rotate-button {
    font-size: 1.1em;
    color: var(--text-color);
    background-color: grey;
    border-radius: 50%;
    width: 40px;    /* Define fixed width to ensure circle shape */
    height: 40px;   /* Define fixed height to ensure circle shape */
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0 5px;
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.2s, background-color 0.2s;
}

.rotate-button:hover{
    opacity: 1;
    background-color: grey;
}

.confirm-rotate-button:hover {
    opacity: 1;
    background-color: green;
    color: white;
}









        .advanced-box-content {
    padding: 2px 15px 15px 15px;
    max-height: 0;  /* Initially set to 0 */
    overflow: hidden;  /* Hide any overflowing content */
    transition: max-height 0.5s ease-in-out;  /* Transition effect */
	font-size:smaller;
	margin-top:-10px;
}


.dropdown {
  float: right;
  overflow: hidden;
  margin-bottom: -10px;
}

#splash-bar {
  background-color: var(--top-header);
  filter: none !important;
  margin-bottom: -200px !important;
}
.photo-upload-container {
    width: 100%;
    padding: 10px;
    background-color: var(--lighter);
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px; /* Adds space between elements */
    margin-bottom: 30px;
}

.custom-file-upload {
    display: inline-block;
    padding: 10px 20px;
    font-size: 1.3rem;
    color: var(--h1);
    background-color: grey;
    border: 2px solid transparent;
    border-radius: 6px;
    cursor: pointer;
    text-align: center;
    transition: background-color 0.3s, border-color 0.3s;
}

.custom-file-upload:hover {
    background-color: var(--accordion-background); /* Lighten the grey background on hover */
    border-color: var(--text-color); /* Changes border color on hover */
}

.custom-file-upload input[type="file"] {
    display: none; /* Hide the actual file input */
}

.file-name {
    margin-top: 8px;
    font-size: 1rem;
    color: var(--text-color);
}

.form-caption {
    font-size: 1rem;
    color: var(--text-color);
/*     text-align: center; */
}



#upload-progress-button {
color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  background-color: #12b712;
  background-size: 0% 100%;
  transition: background-size 0.5s ease;
  font-size: 1.3em;
  width: 100%;
  margin-top: 30px;
  }


  /* Style for the text form */
#add-vision-form {
    width: 100%;
/*     margin-top: 20px; */
}

/* Style for the textarea */
#vision_message {
    width: 100%;
    font-size: 1.3em;
    border-radius: 15px;
    padding: 10px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    resize: vertical; /* Allows vertical resizing only */
    max-width: 100%;
    min-height: 100px; /* Ensures a comfortable size for typing */
    line-height: 1.5em; /* Increases space between lines for readability */
}

/* Style for the character counter */
#character-counter {
    text-align: right;
    font-size: 0.9em;
    color: grey;
    margin-top: 5px;
}

/* Button group style */
.button-group {
    display: flex;
    gap: 10px;
    width: 100%;
    margin-top: 10px;
}

.confirm-button {
    flex-grow: 1;
    text-align: center;
    padding: 10px;
    cursor: pointer;
}

#skip-button {
    background: grey;
}


</style>


<script>

// ROTATE Photo

// SECTION 1: Function to send rotation request to the PHP function
function rotateEcobrickPhoto(photoUrl, thumbUrl, rotationDegrees, photoId, totalRotationDegrees) {
    // Create an AJAX request to send the rotation degrees to the server
    var xhr = new XMLHttpRequest();
    var url = "rotate_photo.php"; // PHP file that handles the photo rotation
    var params = "photo_url=" + encodeURIComponent(photoUrl) +
                 "&thumb_url=" + encodeURIComponent(thumbUrl) +
                 "&rotation=" + rotationDegrees;

    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    // Handle the server's response
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                console.log("Server response: " + xhr.responseText);

                // Check if the response contains a success message
                if (xhr.responseText.trim().includes("rotated successfully")) {
                    // Alert the user of the successful rotation
                    alert("Your photo has been rotated " + totalRotationDegrees + " degrees clockwise and saved to the server.");
                    console.log("Image rotation successful for: " + photoUrl);

                    // SECTION 2: Preserve the current rotation after confirmation
                    // Do not reset the image to 0 degrees after confirmation.
                    // The image will stay at its current rotation.

                } else {
                    // Handle error response from the server
                    alert("Something went wrong saving your rotation. Error: " + xhr.responseText);
                }
            } else {
                // Handle the error if the request was unsuccessful
                alert("An error occurred. Status: " + xhr.status);
            }
        }
    };


    // Send the rotation degrees to the server
    xhr.send(params);
}

// SECTION 3: Function to adjust the height of the container after the image rotates
function adjustContainerHeight(photo, container) {
    var currentRotation = parseInt(photo.getAttribute('data-rotation')) || 0;

    // Adjust height when the image is rotated by 90 or 270 degrees
    if (currentRotation % 180 !== 0) {
        var newHeight = photo.width;
        container.style.height = newHeight + 'px';
    } else {
        // Set container height to auto when image is not rotated (0 or 180 degrees)
        container.style.height = 'auto';
    }
}

// SECTION 4: Function to handle the rotate button clicks
document.querySelectorAll('.rotate-button').forEach(function(button) {
    button.addEventListener('click', function() {
        var photoContainer = this.closest('.photo-container');
        var photo = photoContainer.querySelector('.rotatable-photo');
        var confirmButton = photoContainer.querySelector('.confirm-rotate-button');

        // Get the current rotation from the data attribute
        var currentRotation = parseInt(photo.getAttribute('data-rotation')) || 0;
        var direction = this.getAttribute('data-direction');

        // Rotate the image based on the direction
        if (direction === 'left') {
            currentRotation = (currentRotation - 90) % 360;
        } else if (direction === 'right') {
            currentRotation = (currentRotation + 90) % 360;
        }

        // Apply the rotation and update the data-rotation attribute
        photo.style.transform = 'rotate(' + currentRotation + 'deg)';
        photo.setAttribute('data-rotation', currentRotation);

        // Show the confirm button
        confirmButton.style.display = 'block';

        // Adjust the container height based on the new image rotation
        adjustContainerHeight(photo, photoContainer);
    });
});

// SECTION 5: Handle the confirmation button click to send the rotation to the server
document.querySelectorAll('.confirm-rotate-button').forEach(function(button) {
    button.addEventListener('click', function() {
        var photoContainer = this.closest('.photo-container');
        var photo = photoContainer.querySelector('.rotatable-photo');
        var currentRotation = parseInt(photo.getAttribute('data-rotation')) || 0;
        var photoUrl = this.previousElementSibling.getAttribute('data-photo-url'); // Get the original photo URL from the rotate button
        var thumbUrl = this.getAttribute('data-thumb-url'); // Get the thumbnail URL from the confirm button

        // Calculate total clockwise rotation (normalize it to 0-360)
        var totalRotationDegrees = (currentRotation + 360) % 360;

        // Trigger the PHP function to rotate the actual photo
        var photoId = photo.getAttribute('id'); // Assuming the photo ID corresponds to the ecobrick ID or serial_no
        rotateEcobrickPhoto(photoUrl, thumbUrl, currentRotation, photoId, totalRotationDegrees);
    });
});



</script>

<?php require_once ("../header-2024.php");?>

