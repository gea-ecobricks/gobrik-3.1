    /*-----------------------------------
TEXT TRANSLATION SNIPPETS FOR GOBRIK.com
-----------------------------------*/

// Ampersand (&): Should be escaped as &amp; because it starts HTML character references.
// Less-than (<): Should be escaped as &lt; because it starts an HTML tag.
// Greater-than (>): Should be escaped as &gt; because it ends an HTML tag.
// Double quote ("): Should be escaped as &quot; when inside attribute values.
// Single quote/apostrophe ('): Should be escaped as &#39; or &apos; when inside attribute values.
// Backslash (\): Should be escaped as \\ in JavaScript strings to prevent ending the string prematurely.
// Forward slash (/): Should be escaped as \/ in </script> tags to prevent prematurely closing a script.


const en_Page_Translations = {

 "001-form-title": "Record Serial & Take Photo",
    "002-form-description-1": "Your ecobrick has been logged with a weight of ",
    "003-form-description-2": "a volume of ",
    "004-form-description-3": "and a density of ",
    "005-form-description-4": " Your ecobrick has been allocated the serial number:",
    "006-enscribe-label": "How would you like to inscribe the serial number on your ecobrick?",
    "007-enscribe-option-1": "Select one...",
    "008-enscribe-option-2": "⭐ Permanent marker",
    "009-enscribe-option-3": "👎 Water soluable marker ",
    "010-enscribe-option-4": "⭐ Enamel paint",
    "011-enscribe-option-5": "⭐ Nail polish",
    "012-enscribe-option-6": "⭐ Plastic insert",
    "013-enscribe-option-7": "Other",
    "014-photo-options-label": "What kind of photo would you like to log of your ecobrick?",
    "015-photo-options-option-1": "Select one...",
    "016-photo-options-option-2": "A basic ecobrick photo",
    "017-photo-options-option-3": "A selfie photo",
    "018-photo-options-option-4": "A basic photo and a selfie photo",
    "019-feature-photo": "Upload a basic ecobrick photo:",
    "020-feature-photo-step-1": "Take a vertical portrait photo",
    "021-feature-photo-step-2": "Be sure your photo shows the serial & weight clearly",
    "022-feature-photo-step-3": "Be sure your photo shows your ecobricks bottom color",
    "023-feature-photo-step-4": "Be sure your photo shows your ecobricks top",
    "024-feature-photo-step-5": "Be sure your data is permanently enscribed!",
    "025-feature-photo-step-6": "Do not use an external label to mark the ecobrick",
//    "025-basic-photo-label": '📷 Take Basic Photo<input type="file" id="ecobrick_photo_main" name="ecobrick_photo_main" onchange="displayFileName()">',
    "026-basic-feature-desc": "Take or select a photo of your serialized ecobrick.",
    "027-label-selfie": "Upload an ecobrick selfie:",
    "028-selfie-photo-step-1": "Be sure your photo is a horizontal landscape",
    "029-selfie-photo-step-2": "Be sure your photo shows the serial & weight clearly",
    "030-selfie-photo-step-3": "Be sure your photo shows your ecobricks bottom color",
    "031-selfie-photo-step-4": "Be sure your photo shows your ecobricks top",
    "032-selfie-photo-step-5": "Be sure your data is permanently enscribed!",
    "033-selfie-photo-step-6": "Do not use an external label to mark the ecobrick",
    "034-selfie-photo-step-7": "And smile!",
    "035-selfie-upload": '📷 Take Selfie Photo<input type="file" id="selfie_photo_main" name="selfie_photo_main">',
    "035b-no-file-chosen": "No file chosen",
    "036-another-photo-optional": "Upload your ecobrick selfie.",
//    "037-submit-upload-button": '<input type="submit" value="⬆️ Upload Photos" id="upload-progress-button" aria-label="Submit photos for upload">',

//Modals for density check

    "underDensityTitle": 'Under Density',
    "underDensityMessage": "Your ecobrick's density of ${density} is under the GEA standard of 0.33g/ml. Please check that you have entered the weight and volume correctly. If not, then please repack your ecobrick with more plastic to achieve minimum density. GEA guidelines are developed to ensure the building integrity, fire safety, and reusability of an ecobrick.",
    "lowDensityTitle": 'Low Density',
    "lowDensityMessage": "Careful! Your ecobrick's density of ${density}ml is on the low side. It passes the minimum standard of 0.33g/ml however, its density makes it less solid, fire safe and reusable than it could be. Keep going and log this ecobrick, but see if you can pack more plastic next time.",
    "greatJobTitle": 'Good job!',
    "greatJobMessage": "Your ecobrick's density of ${density} is ideal. It passes the minimum standard of 0.33g/ml making it solid, fire safe, and reusable.",
    "highDensityTitle": 'High Density',
    "highDensityMessage": "Careful, your ecobrick's density of ${density} is very high. Your ${volume} bottle packed with ${weight} of plastic is under the maximum density of 0.73g/ml however, its high density makes it nearly too solid and too heavy for certain ecobrick applications.  Keep going, but keep this in mind for next time.",
    "overMaxDensityTitle": 'Over Max Density',
    "overMaxDensityMessage": "Your ecobrick's density of ${density} is over the GEA standard of 0.73g/ml. Please check that you have entered the weight and volume correctly. If so, then please repack your ecobrick with less plastic. GEA guidelines are developed to ensure the safety and usability of ecobricks for all short and long-term applications.",
    "geaStandardsLinkText": 'GEA Standards',
    "nextRegisterSerial": 'Next: Register Serial',
    "goBack": 'Go Back',

    "035-selfie-upload-box": '<div class="photo-upload-container" data-lang-id="035-selfie-upload-box"><label for="selfie_photo_main" class="custom-file-upload">📷 Add Selfie Photo <input type="file" id="selfie_photo_main" name="selfie_photo_main" onchange="displayFileName(\'selfie_photo_main\', \'file-name-selfie\')"></label><span id="file-name-selfie" class="file-name">No file chosen</span><p class="form-caption">Upload your ecobrick selfie.</p></div>',
"013b-see-examples": '👁️ See an example of <a href="#" onclick="showModalInfo(\'inserts\')" class="underline-link">plastic inserts</a>, <a href="#" onclick="showModalInfo(\'enamel\')" class="underline-link">enamel paint</a>, <a href="#" onclick="showModalInfo(\'marker\')" class="underline-link">permanent marker</a> and <a href="#" onclick="showModalInfo(\'nailvarnish\')" class="underline-link">nail varnish</a>',





    };
