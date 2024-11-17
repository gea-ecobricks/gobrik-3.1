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
    "001-log-title": "Log an Ecobrick",
    "002-log-subheading": "Share your ecobrick with the world!<br>Use this form to log your ecobrick into the brikchain system.",
    "005-ecobricker-maker": "Who made this ecobrick?",
    "005b-ecobricker-maker-caption": "Provide the name of the ecobricker. Avoid special characters.",
    "000-field-required-error": "This field is required.",
    "000-maker-field-too-long-error": "The name is too long. Max 255 characters.",
    "005b-maker-error": "The entry contains invalid characters. Avoid quotes, slashes, and greater-than signs please.",
    "006-volume-ml": "Volume of the Ecobrick (in milliliters):",
    "006-select-volume": "Select volume...",
    "006-volume-ml-caption": "Please provide the volume of the ecobrick in milliliters.",
    "007-weight-g": "Weight of the ecobrick (in grams):",
    "007-weight-g-caption": "Round up to the nearest gram.",
    "007-brand_name": "What brand of bottle is used for this ecobrick?",
    "000-field-too-long-error": "This entry should be under 100 characters. All we need is the bottle brand name i.e. \"Max Water\".",
    "000-field-invalid-error": "The entry contains invalid characters. Avoid quotes, slashes, and greater-than signs please.",
    "008-bottom-color": "Bottom color of the Ecobrick:",
    "008-bottom-color-caption": "Please select the bottom color of the ecobrick.",
    "009-sequestration-type": "What kind of ecobrick is this?",
    "009-sequestration-type-caption": "Please select the type of ecobrick. Learn more about <a href=\"#\" onclick=\"showModalInfo('ocean')\" class=\"underline-link\">ocean ecobricks</a>, <a href=\"#\" onclick=\"showModalInfo('cigbrick')\" class=\"underline-link\">cigbricks</a> and <a href=\"#\" onclick=\"showModalInfo('regular')\" class=\"underline-link\">regular ecobricks</a>.",
    "010-plastic-from": "Where is the plastic from?",
    "010-plastic-from-caption": "From where was your ecobrick's plastic sourced?",
    "011-location-full": "Where is this ecobrick based?",
    "011-location-full-caption": "Start typing the name of your town or city, and we'll fill in the rest using the open source, non-corporate openstreetmaps API. Avoid using your exact address for privacy-- just your town, city or country is fine.",

    "009-select-bottom-color": "Select bottom color...",
    "010-no-color-set": "No deliberate color set",
    "011-clear": "Clear",
    "012-white": "White",
    "013-black": "Black",
    "014-yellow": "Yellow",
    "015-orange": "Orange",
    "016-red": "Red",
    "017-pink": "Pink",
    "018-purple": "Purple",
    "019-violet": "Violet",
    "020-dark-blue": "Dark blue",
    "021-sky-blue": "Sky blue",
    "022-brown": "Brown",
    "023-grey": "Grey",
    "024-silver": "Silver",
    "025-gold": "Gold",
    "026-cigbrick-beige": "Cigbrick beige",
    "000-field-required-error": "This field is required.",

    "011-select-ecobrick-type": "Select ecobrick type...",
    "012-regular-ecobrick": "Regular ecobrick",
    "013-cigbrick": "Cigbrick",
    "014-ocean-ecobrick": "Ocean ecobrick",
    "015-select-plastic-source": "Select plastic source...",
    "016-home": "Home",
    "017-business": "Business",
    "018-community": "Neighbourhood",
    "019-factory": "Factory",
    "020-beach": "Beach",
    "021-ocean": "Ocean",
    "022-river": "River",
    "023-forest": "Forest",
    "024-field": "Field",

    "016-submit-button": '<input type="submit" class="submit-button enabled" value="Next: Density Check" aria-label="Submit Form">',

  '030-save-as-default': 'Save this as my default ecobrick settings.',
  '031-location-tags': '⚙️ Location',
  '032-community-tag': 'Community:',
  '032-watershed-tag': 'Watershed:',
  '033-location-tag': 'Location:',
  '035-your-defaults-loaded': 'Your ecobrick defaults have been loaded. 🫡',
  '111-localization-explanation': 'When you log an ecobrick it is tagged with your own Buwana account localization.  You can edit these defaults here:',



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

    };

