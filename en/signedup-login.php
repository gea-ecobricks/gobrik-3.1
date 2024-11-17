<?php
// Turn on or off error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session before any output
session_start();

// PART 1: Check if the user is already logged in
if (isset($_SESSION['buwana_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Grab language directory from URL
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.59';
$page = 'signedup-login';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$credential_type = '';
$credential_key = '';
$first_name = '';

// PART 2: Initialize buwana_id from URL
$buwana_id = $_GET['id'] ?? null;  // Now initialized early

// Check if buwana_id is valid
if (is_null($buwana_id)) {
    echo '<script>
        alert("Hmm... something went wrong. No buwana ID was passed along. Please try logging in again. If this problem persists, you\'ll need to create a new account.");
        window.location.href = "login.php";
    </script>';
    exit();
}

// PART 3: Look up user information using buwana_id provided in URL

// Buwana database credentials
require_once ("../buwanaconn_env.php");

if ($buwana_id) {
    // Prepare the SQL statement for credentials_tb
    $sql_lookup_credential = "SELECT credential_type, credential_key FROM credentials_tb WHERE buwana_id = ?";
    if ($stmt_lookup_credential = $buwana_conn->prepare($sql_lookup_credential)) {
        $stmt_lookup_credential->bind_param("i", $buwana_id);
        $stmt_lookup_credential->execute();
        $stmt_lookup_credential->bind_result($credential_type, $credential_key);
        $stmt_lookup_credential->fetch();
        $stmt_lookup_credential->close();
    } else {
        error_log("Error preparing statement for credentials_tb: " . $buwana_conn->error);
    }

    // Prepare the SQL statement for users_tb
    $sql_lookup_user = "SELECT first_name FROM users_tb WHERE buwana_id = ?";
    if ($stmt_lookup_user = $buwana_conn->prepare($sql_lookup_user)) {
        $stmt_lookup_user->bind_param("i", $buwana_id);
        $stmt_lookup_user->execute();
        $stmt_lookup_user->bind_result($first_name);
        $stmt_lookup_user->fetch();
        $stmt_lookup_user->close();
    } else {
        error_log("Error preparing statement for users_tb: " . $buwana_conn->error);
    }
}

// Close the database connection
$buwana_conn->close();
?>




<title>Login | GoBrik 3.0</title>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->


<?php require_once ("../includes/signedup-login-inc.php"); ?>


<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
   <div id="top-page-image" class="earthen-service top-page-image" style="height: 230px;
    margin-top: 40px;"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">

        <div style="text-align:center;width:100%;margin:auto;">
            <h2 data-lang-id="100-login-heading-signed-up">Your Buwana Account<br>is Created! 🎉</h2>
            <p>Ok <?php echo htmlspecialchars($first_name); ?>, <span data-lang-id="101-login-subheading-signed-up">now please use your <?php echo htmlspecialchars($credential_type); ?> to login for the first time to start setting up your account:</span></p>
        </div>

        <!-- LOGIN FORM -->
        <form id="signed-up-login" method="post" action="signedup_login_process.php">
            <input type="hidden" name="buwana_id" value="<?php echo htmlspecialchars($buwana_id); ?>">

            <div class="form-item">
                <label for="credential_value"><span data-lang-id="000-your">Your</span> <?php echo htmlspecialchars($credential_type); ?> :</label><br>


                <div class="input-wrapper" style="position: relative;">
                    <input type="text" id="credential_value" name="credential_value" value="<?php echo htmlspecialchars($credential_key); ?>" required>
                    <span class="toggle-select" style="cursor: pointer; position: absolute; right: 10px; top: 50%; transform: translateY(-50%);">🔑</span>
                    <div id="dropdown-menu" style="display: none; position: absolute; right: 10px; top: 100%; z-index: 1000; background: white; border: 1px solid #ccc; width: 150px; text-align: left;">
                        <div class="dropdown-item">E-mail</div>
                        <div class="dropdown-item disabled" style="opacity: 0.5;">SMS</div>
                        <div class="dropdown-item disabled" style="opacity: 0.5;">Peer</div>
                    </div>
                </div>
                <div id="no-buwana-mail" class="form-field-error" style="display:none;margin-top: 0px;margin-bottom:-15px;" data-lang-id="000-no-buwana-account">🤔 We can't find this credential in the database.</div>
            </div>

            <div class="form-item">
                <label for="password" data-lang-id="000-your-password">Your password:</label><br>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required>
                    <span toggle="#password" class="toggle-password" style="cursor: pointer;">🔒</span>
                </div>

                <p class="form-caption" data-lang-id="000-forgot-your-password">Forgot your password? <a href="#" onclick="showPasswordReset('reset')" class="underline-link">Reset it.</a></p>
                <div id="password-error" class="form-field-error" style="display:none;" data-lang-id="000-password-wrong">👉 Password is wrong.</div>
            </div>



            <div style="text-align:center;" data-lang-id="006-login-button-">
        <input type="submit" style="text-align:center;margin-top:15px;width:30%; min-width: 175px;" id="submit-button" value="🔑 Login" class="submit-button enabled">
    </div>
        </form>
    </div>
</div>

</div><!--closes main and starry background-->

<!-- FOOTER STARTS HERE -->
<?php require_once ("../footer-2024.php");?>


<script>


function validateForm() {
    document.getElementById('no-buwana-email').style.display = 'none';
    return true;
}

// Check URL parameters on page load: what's this??
window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('email_not_found')) {
        const email = urlParams.get('email') || '';
        const lang = '<?php echo $lang; ?>'; // Ensure $lang is passed from the backend
        showPasswordReset('reset', lang, email);
        setTimeout(() => {
            const noBuwanaEmail = document.getElementById('no-buwana-email');
            if (noBuwanaEmail) {
                noBuwanaEmail.style.display = 'block';
            }
        }, 100);
    }
}
// Ensure the correct ID is referenced
document.addEventListener("DOMContentLoaded", function() {
    const loginForm = document.getElementById('signed-up-login'); // Ensure you're using the correct form ID
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            var credentialValue = document.getElementById('credential_value').value; // Match the ID in HTML
            var password = document.getElementById('password').value;

            if (credentialValue === '' || password === '') {
                event.preventDefault();
                document.getElementById('password-error').style.display = 'block';
            }
        });
    }
});

// Credentials menu
document.addEventListener("DOMContentLoaded", function () {
    const toggleSelectIcon = document.querySelector('.toggle-select');
    const dropdownMenu = document.getElementById('dropdown-menu');
    const credentialKeyInput = document.getElementById('credential_value'); // Correct ID

    if (toggleSelectIcon && dropdownMenu && credentialKeyInput) {
        const dropdownItems = dropdownMenu.querySelectorAll('.dropdown-item');

        // Toggle dropdown menu visibility on click
        toggleSelectIcon.addEventListener('click', function () {
            dropdownMenu.style.display = dropdownMenu.style.display === 'none' ? 'block' : 'none';
        });

        // Close dropdown if clicked outside
        document.addEventListener('click', function (e) {
            if (!toggleSelectIcon.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.style.display = 'none';
            }
        });

        // Handle dropdown item selection
        dropdownItems.forEach(function (item) {
            item.addEventListener('click', function () {
                if (!item.classList.contains('disabled')) {
                    credentialKeyInput.value = item.textContent.trim();
                    dropdownMenu.style.display = 'none';
                }
            });
        });
    }
});



// Function to validate password
function validatePassword(isValid) {
    const passwordErrorDiv = document.getElementById('password-error');
    if (!isValid) {
        passwordErrorDiv.style.display = 'flex';
    } else {
        passwordErrorDiv.style.display = 'none';
    }
}


// function closeModal() {
//     const modal = document.getElementById('form-modal-message');
//     modal.style.display = 'none';
//     document.getElementById('page-content').classList.remove('blurred');
//     document.getElementById('footer-full').classList.remove('blurred');
//     document.body.classList.remove('modal-open');
// }

function validateForm() {
    const email = document.querySelector('input[name="email"]').value;
    if (!email) {
        alert('Please enter a valid email address.');
        return false;
    }
    return true;
}

document.addEventListener("DOMContentLoaded", function() {
    const errorType = "<?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : ''; ?>";
    if (errorType) {
        alert(errorType);
    }
});


// Form submission validation
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('login').addEventListener('submit', function(event) {
        var credentialValue = document.getElementById('credential_key').value;
        var password = document.getElementById('password').value;

        if (credentialValue === '' || password === '') {
            event.preventDefault();
            document.getElementById('password-error').style.display = 'block';
        }
    });
});
</script>

</body>
</html>
