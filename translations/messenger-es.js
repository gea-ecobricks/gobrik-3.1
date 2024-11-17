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


const es_Page_Translations = {



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
