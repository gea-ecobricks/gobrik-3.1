

<?php require_once ("../meta/edit-project-$lang.php");?>


<STYLE>

.advanced-box-content {
    padding: 2px 15px 15px 15px;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.5s ease-in-out;
	font-size: smaller;
	margin-top: -10px;
}

.dropdown {
  float: right;
  overflow: hidden;
  margin-bottom: -10px;
}

#registration-footer {
  display: none !important;
}

#serial-select ul {
  list-style: none;
  padding: 0;
}

.form-item li:hover {
  background: var(--emblem-blue);
  cursor: pointer;
  padding: 3px;
}

#serial-select {
  background: var(--advanced-background);
  width: 130px;
  margin-top: -10px;
  padding: 10px 10px 10px 20px;
  border-radius: 0px 0px 10px 10px;
  position: absolute;
  z-index: 100;
  margin-left: 15px;
  display: none;
}

.splash-image {display: flex;}
.splash-image img {margin-right: auto; margin-left: 0px;}

@media screen and (max-width: 700px) {
	.splash-content-block {
        background-color: var(--top-header);
        filter: none !important;
        min-height: 20vh !important;
        height: 20vh !important;
	}
  .splash-image {display: none !important;}
}

@media screen and (min-width: 700px) {
	.splash-content-block {
        background-color: var(--top-header);
        filter: none !important;
        min-height: 20vh !important;
	}
}

@media screen and (max-width: 700px) {
.splash-heading {
	font-size: 2.5em !important;
	line-height: 1.1;
	margin: 10px 0px;
	text-align: center;
}
}

@media screen and (min-width: 700px) {
.splash-heading {
	font-size: 3.1em !important;
}
}

#splash-bar {
    background-color: var(--top-header);
    filter: none !important;
    margin-bottom: -200px !important;
}

#main-background {
  background-size: cover;
}

.form-item {
    margin-top: 10px;
    margin-bottom: 10px;
}

.form-caption {
  font-family: "Mulish", sans-serif;
  font-weight: 300;
  color: var(--text-color);
  font-size: 1.0em;
  margin-top: -5px;
}

label {
  font-family: "Mulish", sans-serif;
  font-weight: 500;
  color: var(--text-color);
  font-size: 1.3em;
}

.form-item input {
  background: var(--input-background);
  font-size: 1.3em;
}

.form-item textarea {
  background: var(--input-background);
  font-size: 1.3em;
}

.form-item select {
  background: var(--input-background);
  font-size: 1.2em;
  padding: 5px;
  border-radius: 5px;
  margin-top: 9px;
  margin-bottom: 10px;
}

input[type="text"],
input[type="number"],
textarea,
input[type="date"] {
  font-family: "Mulish", sans-serif;
  font-weight: 300;
  width: 100%;
  padding: 8px;
  margin-bottom: 10px;
  border: 1px solid var(--divider-line);
  border-radius: 5px;
  box-sizing: border-box;
  margin-top: 8px;
}

input[type="submit"] {
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  background-color: #12b712;
  font-size: 1.3em;
  width: 100%;
  margin-top: 30px;
}

input[type="submit"]:hover {
  background-color: green;
}

.spinner-photo-loading {
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-left-color: #ffffff;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0%   { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.form-field-error {
  color: red;
  margin-top: -15px;
  margin-bottom: 20px;
  padding-left: 10px;
  padding-bottom: 15px;
  display: none;
}

.form-container {
  width: 80%;
  background-color: var(--form-background);
  border: 1px solid var(--divider-line);
  border-radius: 15px;
  margin: 0 auto;
  max-width: 1000px;
  z-index: 20;
  font-family: "Mulish", sans-serif;
  position: relative;
}

@media screen and (max-width: 700px) {
  .form-container {
    width: calc(100% - 40px);
    margin: 0;
    padding: 20px;
    position: relative;
    margin-top: 60px;
  }
}

@media screen and (min-width: 701px) {
  .form-container {
    margin-top: auto;
    margin-bottom: auto;
    padding: 30px;
    margin-top: 100px;
  }
}

.module-btn {
  background: var(--emblem-green);
  width: 100%;
  display: flex;
}

.module-btn:hover {
  background: var(--emblem-green-over);
}

.confirm-button {
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  background: var(--emblem-green);
  font-size: 1.3em;
  margin: auto;
  justify-content: center;
  text-align: center;
  text-decoration: none;
  margin-top: 10px;
  display: flex;
}

.confirm-button:hover {
  background: var(--emblem-green-over);
}

.form-item {
    border-radius: 5px;
    padding-left: 10px;
    padding-right: 10px;
    padding-top: 10px;
    background-color: #00000015;
}

.form-item label,
.form-item input,
.form-item .form-caption {
    padding: 10px;
}

.form-item .form-caption {
    font-size: 1.0em;
}

.input-container {
    position: relative;
    display: inline-block;
    width: 100%;
}

#location_address {
    width: 100%;
    padding-left: 30px;
}

.spinner {
    display: none;
    position: absolute;
    top: 30%;
    left: 10px;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    border: 4px solid rgba(0,0,0,0.1);
    border-top: 4px solid var(--emblem-pink);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Admin tag search */
.trainer-tag-container {
    display: flex;
    flex-wrap: wrap;
    margin-top: 5px;
}

.trainer-tag-box {
    display: flex;
    align-items: center;
    background: #ccc;
    border-radius: 12px;
    padding: 4px 10px;
    margin: 3px;
    font-size: 0.9em;
}

.trainer-tag-box .remove-trainer {
    margin-right: 6px;
    cursor: pointer;
    color: #fff;
    font-weight: bold;
}

.autocomplete-results {
    background: var(--form-background);
    border: 1px solid var(--divider-line);
    border-radius: 5px;
    margin-top: -8px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 50;
    position: relative;
}

.autocomplete-item {
    padding: 8px 12px;
    cursor: pointer;
    font-family: "Mulish", sans-serif;
}

.autocomplete-item:hover {
    background: var(--emblem-blue);
    color: white;
}

/* Save success notice */
.save-success-notice {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    border-radius: 8px;
    padding: 12px 20px;
    margin-bottom: 20px;
    font-family: "Mulish", sans-serif;
    font-size: 1.1em;
}

.save-success-notice a {
    color: #155724;
    font-weight: 600;
    text-decoration: underline;
}

/* Photo preview section */
.photos-section-header {
    font-family: "Arvo", serif;
    color: var(--h1);
    font-size: 1.6em;
    margin: 30px 0 10px 0;
    padding-top: 20px;
    border-top: 1px solid var(--divider-line);
}

.photos-section-intro {
    font-family: "Mulish", sans-serif;
    font-weight: 300;
    color: var(--text-color);
    margin-bottom: 20px;
}

.photos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

@media screen and (max-width: 900px) {
    .photos-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media screen and (max-width: 500px) {
    .photos-grid {
        grid-template-columns: 1fr;
    }
}

.photo-slot {
    display: flex;
    flex-direction: column;
}

.photo-preview-box {
    margin: 8px 0 10px 10px;
}

.photo-preview-box img {
    width: 130px;
    height: 130px;
    object-fit: cover;
    border-radius: 8px;
    display: block;
    border: 2px solid var(--divider-line);
}

.photo-no-image {
    width: 130px;
    height: 130px;
    border-radius: 8px;
    background: var(--input-background);
    border: 2px dashed var(--divider-line);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8em;
    color: var(--text-color);
    opacity: 0.55;
    text-align: center;
    padding: 8px;
    box-sizing: border-box;
}

.photo-slot input[type="file"] {
    font-size: 1.0em;
    color: var(--text-color);
    border-radius: 5px;
    background-color: #ffffff35;
    margin-top: 4px;
    cursor: pointer;
    padding: 6px 10px;
    width: 100%;
    box-sizing: border-box;
}

/* Delete project button */
.delete-project-btn {
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    background: #c0392b;
    font-size: 1.1em;
    width: 100%;
    margin-top: 12px;
    display: block;
    text-align: center;
    font-family: "Mulish", sans-serif;
}

.delete-project-btn:hover {
    background: #96281b;
}

</style>

<link rel="stylesheet" href="../styles/dashboard-v2-styles.css?v13">

<?php require_once ("../header-2026b.php");?>
