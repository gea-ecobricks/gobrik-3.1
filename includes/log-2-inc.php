

<?php require_once ("../meta/log-2-$lang.php");?>


<STYLE>

#page-content {
    filter: blur(5px); /* Apply blur by default */
    transition: filter 0.3s ease; /* Smooth transition for the blur */
}

body {
overflow: hidden;
}




    #main {
        height: fit-content !important;
  padding-bottom: 100px;
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
  background-color: #099d09; /* Green background */
  position: relative; /* Needed for progress bar overlay */
  font-size: 1.3em;
  width: 100%;
  margin-top: 30px;
  height: 45px;
  display: flex;
  align-items: center;
  justify-content: center;
}

#upload-progress-button .progress-fill {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  width: 0%; /* Start at 0% */
  background-color: #4ab54a; /* fill for progress */
  transition: width 0.5s ease;
  z-index: 1; /* Ensure it's behind the button text */
  border-radius: 4px;
}

#upload-progress-button span {
  z-index: 2; /* Ensure button text is on top */
}

.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid rgba(0, 0, 0, 0.1);
    border-left-color: #000;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-right: 10px; /* Space between spinner and text */
    display: none; /* Initially hidden */
    z-index: 10;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

#upload-progress-button:hover {
  background-color: #077e07;
}




</style>

<?php require_once ("../header-2024.php");?>

