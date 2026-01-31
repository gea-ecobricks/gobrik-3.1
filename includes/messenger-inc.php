
<!--  Set any page specific graphics to preload-->

<?php require_once ("../meta/$page-$lang.php");?>


<!-- Include DataTables and jQuery Libraries -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>



<STYLE>

        #main {
        height: calc(100vh - 65px) !important;

    }


@media screen and (min-width: 801px) {
  #messenger-form-container {
    max-width: unset;
    padding: 10px;
     width: 92%;
    }
}


/* MESSENGER CSS */
.hidden {
    display: none;
}



.full-convo-message {
    display: flex;
    align-items: center; /* Vertically centers the content */
    justify-content: center; /* Horizontally centers the content */
    color: var(--subdued-text);
    height: 100%;
    text-align: center; /* Ensures text is centered in the element */
    font-size: 1.1em; /* Adjust as needed for better readability */
    padding: 20px; /* Optional: Adds some padding for spacing */
    background: var(--darker); /* Optional: Ensure background matches the message area */
    border-radius: 15px; /* Optional: Rounds the corners if needed */
    flex-flow: column;
}





.messenger-container {
    display: flex;
    height: calc(100vh - 110px);
    border: 1px solid var(--settings-border);
    background: var(--darker);
    border-radius: 15px;
}

.conversation-list-container {
    width: 30%;
    display: flex;
    flex-direction: column;
/*     border-right: 1px solid var(--settings-border); */
/*     background: var(--darker); */
    border-radius: 10px 10px 10px 10px;
}

.start-conversation-container {
    padding: 10px;
    /* background: var(--darker); */
    border-bottom: 1px solid var(--settings-border);
    z-index: 1; /* Ensures it stays above the conversation list when scrolling */
}

#searchBoxContainer {
    margin-top: 10px;
}

#searchResults, #selectedUsers {
    margin-top: 10px;
    max-height: 150px;
    overflow-y: auto;
    border: 1px solid var(--settings-border);
    background: var(--darker);
}

.search-result-item, .selected-user-item {
    padding: 7px;
    cursor: pointer;
    border-bottom: 1px solid var(--settings-border);
    color: var(--text-color);
    background: var(--lighter);
}

/* .selected-user-item {
    background-color: var(--advanced-background);
} */

.conversation-list {
    flex-grow: 1;
    overflow-y: auto;
    padding: 10px 0px 10px 10px;
    scrollbar-width: none; /* For Firefox */
}

.conversation-list::-webkit-scrollbar {
    display: none; /* For Chrome, Safari, and Edge */
}


.timestamp {
    font-size: 0.8em;
    color: var(--subdued-text);
}

.message-thread {
    width: 70%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 10px;
}

#message-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    overflow-y: auto;
    flex-grow: 1;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--settings-border);
    scrollbar-width: none; /* For Firefox */
}

#message-list::-webkit-scrollbar {
    display: none; /* For Chrome, Safari, and Edge */
}



.message-input {
    display: flex;
/*     gap: 10px; */
    margin-top: 10px;
}



#sendButton:hover {
background-color: var(--emblem-pink-over);
}
.message-item {
    margin-bottom: 10px;
    padding: 10px;
    border-radius: 5px;
    background-color: var(--my-chat);
    color: var(--text-color);
    min-width: 10%;
}

.message-item {
    position: relative; /* Ensure the ::after element is positioned relative to the message box */
    padding: 10px;
    border-radius: 15px;
    max-width: 80%;
    word-wrap: break-word;
    margin-bottom: 10px;
}

.message-item.self:not(.flash) { /* Apply background only when not flashing */
    background-color: var(--my-chat);
    color: #fff;
    align-self: flex-end;
    text-align: right;
    margin-right: 2px;
}

.message-item.self::after {
    content: '';
    position: absolute;
    bottom: -17px; /* Position the spike below the message box */
    right: 22px; /* 22px away from the right edge */
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 18px 18px 0 0; /* Creates a right-angled triangle */
    border-color: var(--my-chat) transparent transparent transparent; /* Color the spike */
    transform: rotate(-270deg); /* Rotate to create the angled effect */
}

.message-item:not(.self):not(.flash) { /* Apply background only when not flashing */
    background-color: var(--friends-chat);
    color: var(--text-color);
    align-self: flex-start; /* Aligns to the left */
    text-align: left;
    margin-left: 2px; /* Space to account for the spike */
}

.message-item:not(.self)::after {
    content: '';
    position: absolute;
    bottom: -17px; /* Position the spike below the message box */
    left: 22px; /* 22px away from the left edge */
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 18px 0 0 18px; /* Creates a left-angled triangle */
    border-color: var(--friends-chat) transparent transparent transparent; /* Color the spike */
    transform: rotate(270deg); /* Rotate to create the angled effect */
    margin-top: -2px;
}

/* FLASH ANIMATION FOR NEW MESSAGES */

@keyframes flash {
    0% {
        background-color: pink;
        color: white;

    }
/*     50% { */
/*         background-color: var(--emblem-pink-over); */
/*     } */
    100% {
        background-color: var(--my-chat);
        color: var(--text-color);
    }
}

.message-item.flash {
    animation: flash 0.8s ease-out;
    align-self: flex-end;
      text-align: right;
      margin-right: 2px;
}




.message-item .sender {
    font-size: smaller;
    color: var(--text-color); /* Color for sender names */
}

.message-item .the-message-text {
    font-size: medium;
    color: var(--h1); /* Color for sender names */
}



.message-item .timestamp {
    font-size: 0.8em;
    color: var(--subdued-text);
}


#searchResults {
    position: relative;
    background: var(--darker);
    border: 1px solid var(--settings-border);
    max-height: 200px;
    overflow-y: auto;
    z-index: 2; /* Ensures it appears above other elements */
    margin: -3px 12px 0px 12px;
  padding: 4px;
}

.search-result-item {
    padding: 8px;
    border-bottom: 1px solid var(--settings-border);
    cursor: pointer;
    color: var(--text-color);
}

.search-result-item:hover {
    background-color: var(--advanced-background);
}




.conversation-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: var(--text-color);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 10px 10px auto 0px;
    min-width: 40px; /* Prevent the icon from shrinking */

}


.conversation-icon .initial {
    color: var(--same);
    font-size: 1.2em;
    font-weight: bold;
}

.conversation-details {
    flex-grow: 1;
    overflow: hidden; /* Ensures no overflow */
}

.conversation-details strong {
    color: var(--h1); /* Color for the other participants' names */
    font-size: 1em;
}


.delete-conversation {
    position: absolute;
    top: 5px;
    right: 5px;
    font-size: 1.2em;
    color: var(--subdued-text);
    cursor: pointer;
    background: transparent;
    border: none;
    padding: 10px;
    line-height: 1;
}

.convo-preview-text {
    color: var(--text-color);
    font-size: 0.9em;
    overflow: hidden;
    text-overflow: ellipsis; /* Adds "..." at the end if the text is too long */
    white-space: nowrap; /* Ensures the text stays on a single line */
}

.timestamp {
    font-size: 0.8em;
    color: var(--subdued-text);
    margin-top: 2px;
}

.message-input {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}





.spinner-right {

position: absolute;
  top: 13px;
  right: 11px;
  transform: translateY(-50%);
  width: 15px;
  height: 15px;
  border: 4px solid rgba(0,0,0,0.1);
    border-top-width: 4px;
    border-top-style: solid;
    border-top-color: rgba(0, 0, 0, 0.1);
  border-top: 4px solid var(--emblem-blue);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  display: none;

  }






.message-thread {
    width: 70%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 10px;
    transition: width 0.4s ease; /* Animates the width change over 0.4 seconds */
}

.message-thread.expanded {
    width: calc(100% - 60px); /* When the conversation list is collapsed, the message thread takes more space */
}


.delete-conversation {
    position: absolute;
    top: 5px;
    right: 5px;
    font-size: 0.9em;
    color: var(--subdued-text);
    cursor: pointer;
    background: transparent;
    border: none;
    padding: 2px;
    line-height: 1;
}

.delete-conversation:hover {
    color: var(--emblem-red); /* Change the color on hover to indicate delete action */
}



.conversation-details {
    flex-grow: 1;
    overflow: hidden;
    transition: opacity 0.4s ease; /* Smoothly fades out the details when hidden */
}




.message-thumbnail {
    max-width: 100%;
    height: auto;
    margin-bottom: 10px;
    border-radius: 5px;
}




#splash-bar {
  background-color: var(--top-header);
  filter: none !important;
  margin-bottom: -200px !important;
}



//CUSTOMIZED FIELD, UPLOAD AND SEND BUTTONS

//upload button
/* Wrapper for the message input, similar to bug-report input */
.message-input-wrapper, {
    position: relative;
    width: 100%;
    margin-bottom: 10px;
    padding-bottom: 16px;
}


/* Style for the upload photo button */
.upload-photo-button {
    position: absolute;
    /* bottom: 18px;
  left: 10px; */
  width: 45px;
  height: 45px;
    background: grey;
    color: white;
    border: none;
    border-radius: 50%;
    font-size: 1.2em;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.3s ease;
}

.upload-photo-button:hover {
    background: var(--emblem-blue);
}

.image-file-name {
    position: absolute;
    bottom: -7px;
    left: 80px; /* Position to the left of the upload button */
    font-size: 0.8em;
    color: var(--subdued-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px; /* Adjust as needed */
}

#messageInput {
    padding: 12px;
    padding-left: 20px;
    background: var(--main-background);
    color: var(--text-color);
    font-size: 1.25em;
    border-radius: 25px;
    width: -moz-available;
    margin-left: 55px;
    resize: none; /* Prevents manual resizing */
    overflow: hidden; /* Hides the scrollbar */
    max-height: calc(1.5em * 5 + 30px); /* Adjusts to a max of 5 rows plus padding */
    line-height: 1.5em;
    border: none; /* Removes all borders */
    outline: none; /* Removes the border when selected */
    font-family: 'Mulish', sans-serif;
    width: 100%;
}

/* Placeholder text style */
#messageInput::placeholder {
    color: var(--subdued-text);
}



#sendButton {
    width: 50px; /* Adjust width to fit the triangle */
    background-color: var(--emblem-pink);
    border: none;
    border-radius: 20px; /* Flat left edge, rounded right edge */
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.3s ease;
    position: absolute;
    bottom: 24px;
  right: 18px;
  width: 38px;
  height: 38px;
}

#sendButton::before {
    content: '';
    display: block;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 8px 0 8px 16px; /* Creates a right-facing triangle */
    border-color: transparent transparent transparent var(--main-background); /* White triangle color */
    margin-left: 3px; /* Adjust position if needed */
}

#sendButton:hover {
    background-color: var(--emblem-pink-over);
}

.upload-spinner {
    position: absolute;
    border-radius: 50%; /* Ensures a circular shape */
    bottom: 28px;
    right: 20px;
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-top: 4px solid var(--emblem-pink);
    animation: spin 1s linear infinite;
    display: none; /* Hidden by default */
    width: 25px;
    height: 25px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.error-indicator {
    position: absolute;
    border-radius: 20px;
    bottom: 34px;
    right: 25px;
    width: 25px;
    height: 25px;
    background-color: var(--emblem-red); /* Use a CSS variable for color */
    color: #fff;
    font-size: 1.5em;
    display: flex;
    align-items: center;
    justify-content: center;
    display: none; /* Hidden by default */
}





/* START CONVO BUTTONS */
.start-convo-button {
    display: flex; /* Align SVG and text side by side */
    align-items: center; /* Vertically center the content */
    background: var(--emblem-green);
    color: white;
    border: 1px solid var(--emblem-green);
    padding: 10px;
    cursor: pointer;
    font-size: 1.15em;
    transition: background 0.3s ease, border 0.3s ease;
    height: 42px; /* Ensures both buttons have the same height */
    text-align: left;
    width: calc(100% - 60px); /* Leaves room for the toggle button */
    border-radius: 15px 5px 5px 15px;
}


.start-convo-button:hover {
    background: var(--emblem-green-over);
    border-color: var(--emblem-green);
}


.start-convo-button img.button-icon {
    height: 30px; /* Height of the SVG icon */
    margin-right: 8px; /* Space between the icon and text */
    width: auto; /* Keeps aspect ratio of the icon */
}

.toggle-drawer-button {
    display: flex; /* Align SVG and text side by side */
    align-items: center; /* Vertically center the content */
    color: white;
    padding: 10px 0px 10px 15px;
    cursor: pointer;
    font-size: 1.2em;
    transition: background 0.3s ease, border 0.3s ease;
    height: 42px; /* Ensures both buttons have the same height */
    width: 50px;
    text-align: center;
    background: var(--emblem-blue);
    border-radius: 5px 15px 15px 5px;
    margin-left: 5px;
    border: 1px solid var(--emblem-blue);
}

.start-convo-button.hidden {
    display: none;
}

.toggle-drawer-button:hover {
    background: var(--emblem-blue-over);
    border-color: var(--emblem-blue-over);
}

/* Create Conversation Button */
.create-button {

    color: white;
    padding: 10px;
    border-radius: 0px 0px 10px 10px;
    font-size: 1.1em;
    margin: auto;
    justify-content: center;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    transition: background 0.3s ease, border 0.3s ease;
    width: 100%;
    border: none;
}

.create-button.disabled {
    background: grey;
    color: white;
    cursor: not-allowed;
}
.create-button:not(.disabled) {
    background: var(--emblem-green);
    cursor: pointer;
}

.create-button:not(.disabled):hover {
    background: var(--emblem-green-over);

}




/* #userSearchInput { */
/*     padding: 5px; */
/*     padding-right: 20px; */
/*     background: var(--main-background); */
/*     color: var(--text-color); */
/*     font-size: 1em; */
/*     border-radius: 25px; */
/*     width: -moz-available; */
/*     resize: none;  *//* Prevents manual resizing */
/*     overflow: hidden;  *//* Hides the scrollbar */
/*     max-height: calc(1.5em * 5 + 30px);  *//* Adjusts to a max of 5 rows plus padding */
/*     line-height: 1.5em; */
/*     border: none;  *//* Removes all borders */
/*     outline: none;  *//* Removes the border when selected */
/*     font-family: 'Mulish', sans-serif; */
/* } */

#searchBoxContainer {
    position: relative;
}

.clear-search-button {
    position: absolute;
    left: 10px;
    top: 23px;
    transform: translateY(-50%);
    background: var(--lighter);
    border: none;
    border-radius: 50%;
    width: 23px;
    height: 23px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.3s, color 0.3s;
}


.clear-search-button::before,
.clear-search-button::after {
    content: '';
    position: absolute;
    width: 12px; /* Adjust to make the "X" thinner */
    height: 1.5px; /* Thinner line for a lighter look */
    background-color: var(--main-background); /* Darker color for contrast */
}

.clear-search-button::before {
    transform: rotate(45deg);
}

.clear-search-button::after {
    transform: rotate(-45deg);
}

.clear-search-button:hover {
    background: var(--subdued-text); /* White background on hover */
}

.clear-search-button:hover::before,
.clear-search-button:hover::after {
    background-color: #000; /* Darker "X" on hover */
}

#userSearchInput {
    padding: 4px 4px 4px 40px; /* Adjust for the space taken by the clear button */
    background: var(--main-background);
    color: var(--text-color);
    font-size: 1em;
    border-radius: 25px;
    width: 100%; /* Ensure it spans the container */
    resize: none; /* Prevents manual resizing */
    overflow: hidden; /* Hides the scrollbar */
    max-height: calc(1.5em * 5 + 30px); /* Adjusts to a max of 5 rows plus padding */
    line-height: 1.5em;
    border: none; /* Removes all borders */
    outline: none; /* Removes the border when selected */
    font-family: 'Mulish', sans-serif;
}

//convo item


.conversation-item.collapsed .delete-conversation,
.conversation-item.collapsed .conversation-details,
.start-convo-button.hidden {
    display: none; /* Hide elements when the drawer is collapsed */
}


.conversation-item {
    position: relative; /* Allows the delete button to be positioned relative to this container */
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 3px solid var(--settings-border);
    cursor: pointer;
    color: var(--text-color);
    border-radius: 15px 15px 15px 15px;
    background: linear-gradient(to left, var(--darker), rgba(0, 0, 0, 0));
}

.conversation-item.active {
/*      linear-gradient(to right, var(--darker), rgba(0, 0, 0, 0)); */
background: linear-gradient(to right, rgba(19, 129, 3, 0.18), rgba(0, 0, 0, 0));
border-right: none;
  border-bottom: none;
  margin-bottom: 4px;
}

.conversation-item:hover {
    background-color: var(--lighter);
}

.conversation-item strong {
    color: var(--h1); /* Color for the other participants' names */
}

.message-item.self .sender {
    display: none;
}

/*convo icon colors*/

.conversation-item:nth-of-type(10n+1) .conversation-icon { background-color: var(--color-1); }
.conversation-item:nth-of-type(10n+2) .conversation-icon { background-color: var(--color-2); }
.conversation-item:nth-of-type(10n+3) .conversation-icon { background-color: var(--color-3); }
.conversation-item:nth-of-type(10n+4) .conversation-icon { background-color: var(--color-4); }
.conversation-item:nth-of-type(10n+5) .conversation-icon { background-color: var(--color-5); }
.conversation-item:nth-of-type(10n+6) .conversation-icon { background-color: var(--color-6); }
.conversation-item:nth-of-type(10n+7) .conversation-icon { background-color: var(--color-7); }
.conversation-item:nth-of-type(10n+8) .conversation-icon { background-color: var(--color-8); }
.conversation-item:nth-of-type(10n+9) .conversation-icon { background-color: var(--color-9); }
.conversation-item:nth-of-type(10n+10) .conversation-icon { background-color: var(--color-0); }





    </style>





<?php require_once ("../header-2026.php");?>



