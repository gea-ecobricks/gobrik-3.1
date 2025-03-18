<?php

// Function to validate email format
function is_valid_email($email) {
    $email_regex = "/^(?!.*\.\.)[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";

    if (!preg_match($email_regex, $email)) {
        return ["valid" => false, "reason" => "Invalid email format"];
    }

    $parts = explode("@", $email);
    if (count($parts) !== 2) {
        return ["valid" => false, "reason" => "Invalid email structure"];
    }

    $local_part = $parts[0];
    $domain = strtolower($parts[1]);

    // Prevent emails with suspicious local parts (like just dots or special chars)
    if (preg_match("/^[._%+-]+$/", $local_part)) {
        return ["valid" => false, "reason" => "Invalid local part"];
    }

    // Detect common domain typos and suggest correction
    $common_typos = [
        "gmail.com"   => ["gmal.com", "gmial.com", "gmaill.com", "gmail.cm", "gmail.con", "gmail.om"],
        "hotmail.com" => ["hotmil.com", "hotmial.com", "hotmail.cm", "hotmail.con", "hotmail.om"],
        "outlook.com" => ["otlook.com", "outlok.com", "outloo.com", "outlook.cm", "outlook.con"],
        "yahoo.com"   => ["yaho.com", "yaoo.com", "yhoo.com", "yahoo.cm", "yahoo.con"],
    ];

    foreach ($common_typos as $correct => $typos) {
        if (in_array($domain, $typos)) {
            return ["valid" => false, "reason" => "Typo in domain ($domain), did you mean $correct?"];
        }
    }

    // Block disposable email providers
    $disposable_domains = [
        "10minutemail.com", "temp-mail.org", "guerrillamail.com", "mailinator.com",
        "trashmail.com", "yopmail.com", "dispostable.com", "getnada.com", "test.com"
    ];

    if (in_array($domain, $disposable_domains)) {
        return ["valid" => false, "reason" => "Disposable email detected"];
    }

    return ["valid" => true, "reason" => ""];
}

// Function to check if domain exists using DNS MX lookup
function domain_exists($email) {
    $domain = substr(strrchr($email, "@"), 1);

    // Ensure domain contains only valid characters
    if (!preg_match("/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $domain)) {
        return false;
    }

    return checkdnsrr($domain, "MX");
}

?>
