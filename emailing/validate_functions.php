<?php

// Function to validate email format
function is_valid_email($email) {
    $email_regex = "/^(?!.*\.\.)[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";

    if (!preg_match($email_regex, $email)) {
        return ["valid" => false, "reason" => "Invalid format"];
    }

    $common_typos = [
        "gmail.com" => ["gmal.com", "gmial.com", "gmaill.com", "gmail.cm", "gmail.con", "gmail.om"],
        "hotmail.com" => ["hotmil.com", "hotmial.com", "hotmail.cm", "hotmail.con", "hotmail.om"],
        "outlook.com" => ["otlook.com", "outlok.com", "outloo.com", "outlook.cm", "outlook.con"]
    ];

    $parts = explode("@", $email);
    $domain = strtolower($parts[1]);

    foreach ($common_typos as $correct => $typos) {
        if (in_array($domain, $typos)) {
            return ["valid" => false, "reason" => "Typo in domain ($domain)"];
        }
    }

    return ["valid" => true, "reason" => ""];
}

// Function to check if domain exists using DNS MX lookup
function domain_exists($email) {
    $domain = substr(strrchr($email, "@"), 1);
    return checkdnsrr($domain, "MX");
}

?>
