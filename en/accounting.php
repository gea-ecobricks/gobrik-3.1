<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
require_once '../auth/session_start.php';
require '../vendor/autoload.php'; // Path to Composer's autoloader

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.55';
$page = 'accounting';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// LOGIN AND ROLE CHECK:
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$buwana_id = $_SESSION['buwana_id'];
require_once '../gobrikconn_env.php';

$query = "SELECT user_roles FROM tb_ecobrickers WHERE buwana_id = ?";
if ($stmt = $gobrik_conn->prepare($query)) {
    $stmt->bind_param("i", $buwana_id);
    $stmt->execute();
    $stmt->bind_result($user_roles);

    if ($stmt->fetch()) {
        if (stripos($user_roles, 'admin') === false) {
            echo "<script>
                alert('Sorry, only admins can see this page.');
                window.location.href = 'dashboard.php';
            </script>";
            exit();
        }
    } else {
        echo "<script>
            alert('User record not found.');
            window.location.href = 'dashboard.php';
        </script>";
        exit();
    }
    $stmt->close();
} else {
    echo "<script>
        alert('Error checking user role. Please try again later.');
        window.location.href = 'dashboard.php';
    </script>";
    exit();
}


require_once '../buwanaconn_env.php';

// Fetch the user's location data
$user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
$earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
$user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
$user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
$gea_status = getGEA_status($buwana_id);
$user_community_name = getCommunityName($buwana_conn, $buwana_id);
$ecobrick_unique_id = '';
$first_name = getFirstName($buwana_conn, $buwana_id);

// Initialize variables for the email form
$email_addr = $nextEcobricker['email_addr'] ?? '';
$first_name = $nextEcobricker['first_name'] ?? '';
$date_registered = $nextEcobricker['date_registered'] ?? '';
$brk_balance = $nextEcobricker['brk_balance'] ?? 0;
$ecobricks_made = $nextEcobricker['ecobricks_made'] ?? 0;
$city_txt = $nextEcobricker['city_txt'] ?? '';
$region_txt = $nextEcobricker['region_txt'] ?? '';
$country_txt = $nextEcobricker['country_txt'] ?? '';
$subject = "Please activate your 2025 GoBrik account";


?>





<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<?php require_once ("../includes/accounting-inc.php");?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
<div id="top-page-image" class="open-books-top top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">

       <!-- Email confirmation form -->

<div style="text-align:center;">
    <h2>GEA OpenBooks</h2>
    <p>Our backend accounting for adminstrative use.</p>

    <div id="admin-menu" class="dashboard-panel" style="margin-bottom: 30px;">
        <div class="menu-buttons-row">
            <!-- Add Revenue button with onclick and aria property -->
            <button class="page-button" id="add-revenue" onclick="addRevenueTrans()" aria-label="Click to add a revenue transaction">➕ Add Revenue</button>
<a class="page-button" href="https://ecobricks.org/en/open-books.php" target="_blank" aria-label="Go to ecobricks.org">↗️ OpenBooks</a>
            <button class="page-button" id="add-expense" onclick="addExpenseTrans()" aria-label="Click to add an expense transaction">➕ Add Expenses</button>

        </div>
    </div>


      <div class="overflow">
    <table id="all-transactions" class="display" style="width:100%">
        <thead>
            <tr>
                <th data-lang-id="012-id-column">ID</th>
                <th data-lang-id="018-amount-idr-column">Amount</th> <!-- Use IDR amount -->
                <th data-lang-id="013-date-column">Date</th>
                <th data-lang-id="014-sender-column">Sender</th>
                <th data-lang-id="014-sender-column">Type</th> <!-- type_of_transaction -->
                <th data-lang-id="015-category-column">Category</th> <!-- Hide and show only on overflow -->
                <th data-lang-id="016-tran-name-column">Transaction</th>
                <th data-lang-id="017-amount-usd-column">Amount USD</th> <!-- Hide and show only on overflow -->
                <th data-lang-id="019-type-column">Account Note</th> <!-- Renamed column title -->
            </tr>
        </thead>
    </table>
</div>


    </div>
</div>


</div> <!--Closes main-->


<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2025.php"); ?>



    <script>

    /*show trans*/

    function openTransactionModal(transactionId) {
    // Select modal elements
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');

    // Show the modal
    modal.style.display = 'flex';
    modalBox.style.flexFlow = 'column';

    // Lock scrolling for the body and blur the background
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open'); // Locks scrolling

    // Set up the modal-content-box styles
    const modalContentBox = document.getElementById('modal-content-box');
    modalContentBox.style.maxHeight = '80vh'; // Ensure it doesn’t exceed 80% of the viewport height
    modalContentBox.style.overflowY = 'auto'; // Make the modal scrollable if content overflows

    // Clear previous modal content and set up the structure
    modalContentBox.innerHTML = `<h4>Transaction Details - ID: ${transactionId}</h4>
                                  <div id="transaction-table-container"><p>Loading transaction details...</p></div>`;

    // Show the modal
    modal.classList.remove('modal-hidden');

    // AJAX request to fetch transaction details
    $.ajax({
        url: '../api/fetch_cash_trans.php', // Backend PHP file
        type: 'GET',
        data: { cash_tran_id: transactionId }, // Pass the transaction ID
        success: function (response) {
            // Parse the JSON response from the server
            const data = JSON.parse(response);

            if (data.error) {
                // If there's an error, display it
                document.getElementById('transaction-table-container').innerHTML = `<p>${data.error}</p>`;
                return;
            }

            // Construct the HTML for the transaction details
            let transactionDetailsHTML = `
                <div id="main-details">
                    ${data.paymt_record_url && data.paymt_record_url !== 'N/A' ? `
                        <div id="photo">
                            <img src="${data.paymt_record_url}" width="90%" />
                        </div>
                    ` : ''}
                    <div class="serial"><b>Amount:</b> <br>
                    <h2><var>${data.native_ccy_amt} ${data.currency_code}</var></h2></div>
                    <div class="general-field"><b>Transaction Name:</b> ${data.tran_name_desc}</div>
                    <div class="main"><b>Sender:</b> <var>${data.sender_for_display}</var></div>
                    <div class="main"><b>Sent:</b> <var>${data.datetime_sent_ts}</var></div>
                    <div class="main"><b>Type:</b> <var>${data.type_of_transaction}</var></div>
                    ${data.expense_accounting_type ? `
                        <div class="main"><b>Category:</b> ${data.expense_accounting_type}</div>
                    ` : ''}
                    ${data.revenue_accounting_type ? `
                        <div class="main"><b>Category:</b> ${data.revenue_accounting_type}</div>
                    ` : ''}
                    <div class="ecobrick-data">
                        <p><b>>> Raw Cash Transaction Record</b></p>
                        <p><b>Record ID:</b> ${data.knack_record_id}</p>
                        <p><b>Cash Transaction ID:</b> ${data.cash_tran_id}</p>
                        <p><b>Sender (for display):</b> ${data.sender_for_display}</p>
                        <p><b>Date Time Sent:</b> ${data.datetime_sent_ts}</p>
                        <p><b>Transaction Name:</b> ${data.tran_name_desc}</p>
                        <p><b>Amount USD:</b> ${data.usd_amount}</p>
                        ${data.native_ccy_amt_display !== "0.00" ? `
                            <p><b>Native Currency Amount:</b> ${data.native_ccy_amt_display}</p>
                        ` : ''}
                        <p><b>Exchange Rate:</b> ${data.exchange_ratio}</p>
                        ${data.connected_brk_trans ? `
                            <p><b>Connected BRK Transaction:</b>
                                <a href="details-brk-trans.php?tran_id=${data.connected_brk_trans}" target="_blank">
                                    ${data.connected_brk_trans}
                                </a>
                            </p>
                        ` : ''}
                    </div>
                </div>
            `;

            // Populate the modal with the transaction details
            document.getElementById('transaction-table-container').innerHTML = transactionDetailsHTML;
        },
        error: function (xhr, status, error) {
            // Display error message if AJAX fails
            document.getElementById('transaction-table-container').innerHTML = '<p>Error loading transaction details. Please try again later.</p>';
        }
    });
}



/* ALL TRANSACTIONS */
$(document).ready(function () {
    $('#all-transactions').DataTable({
        ajax: '../api/fetch_all_transactions.php', // URL of the new PHP file
        columns: [
            {
                data: 'ID',
                orderable: false,
                render: function (data, type, row) {
                    const escapedData = String(data).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                    return `<a href="#" onclick="openTransactionModal('${escapedData}')">${data}</a>`;
                },
                className: 'dt-center' // Center-align the ID column
            },
            {
                data: 'AmountIDR',
                render: function (data) {
                    return `${data} IDR`; // Add "IDR" after the Amount IDR
                }
            },
            { data: 'Date' }, // Sortable column
            { data: 'Sender' },
            { data: 'Type' }, // type_of_transaction
            { data: 'Category' }, // Hidden by default for overflow
            { data: 'Transaction' },
            {
                data: 'AmountUSD',
                render: function (data) {
                    return `$${data}`; // Add dollar sign to the Amount USD
                }
            },
            {
                data: 'AccountNote',
                render: function (data) {
                    return data || 'N/A'; // Show 'N/A' if no account note exists
                }
            }
        ],
        columnDefs: [
            {
                targets: [5, 7], // Hide "Category" and "Amount USD" by default
                visible: false,
                responsivePriority: 4 // Lower priority for responsive view
            },
            {
                targets: [4, 8], // Ensure "Type" and "Account Note" are visible on most devices
                responsivePriority: 1
            }
        ],
        responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.childRowImmediate,
                type: ''
            },
            breakpoints: [
                { name: 'desktop', width: Infinity },
                { name: 'tablet', width: 1024 },
                { name: 'mobile', width: 700 }
            ]
        },
        order: [[2, 'desc']] // Sort by the "Date" column (index 2), descending
    });
});




</script>


<script>

// ADD Revenue
function addRevenueTrans() {
    // Select modal elements
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');

    // Show the modal
    modal.style.display = 'flex';
    modalBox.style.flexFlow = 'column';

    // Lock scrolling for the body and blur the background
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open'); // Locks scrolling

    // Set up the modal-content-box styles
    const modalContentBox = document.getElementById('modal-content-box');
    modalContentBox.style.maxHeight = '80vh'; // Ensure it doesn’t exceed 80% of the viewport height
    modalContentBox.style.overflowY = 'auto'; // Make the modal scrollable if content overflows

    // List of revenue_accounting_type values
    const revenueTypes = [
        'Trainer EarthWands Remittance',
        'Trainer Course 20% Remittance',
        'Other',
        'Open donation',
        'O2E Contribution',
        'GEA Trainer Monthly Contribution',
        'GEA product purchase',
        'GEA Course Registration',
        'GEA Catalyst Program Fee',
        'Founder contribution',
        'Encouragement',
        'Brikcoin purchase',
        'Arc.io CDN',
        'AES Plastic Offset Purchase'
    ];

    // Generate options for the dropdown
    const revenueTypeOptions = revenueTypes
        .map(type => `<option value="${type}">${type}</option>`)
        .join('');

    // Create the form HTML
    const formHTML = `
        <h2 style="text-align:center">Add Revenue Transaction</h2>
        <form id="add-revenue-form" onsubmit="submitRevenueTrans(event)">
            <div class="form-item" style="margin-top: 25px;">
                <label for="amount-idr">Amount (IDR):</label>
                <input type="number" id="amount-idr" name="amount_idr" required />
            </div>
            <div class="form-item">
                <label for="sender">From:</label>
                <input type="text" id="sender" name="sender" required />
            </div>
            <div class="form-item">
                <label for="transaction-date">Transaction Date:</label>
                <input type="date" id="transaction-date" name="transaction_date" value="${new Date().toISOString().split('T')[0]}" required />
            </div>
            <div class="form-item">
                <label for="description">Transaction Description:</label>
                <textarea id="description" name="description" rows="4" required style="width:90%"></textarea>
            </div>
            <div class="form-item">
                <label for="revenue-type">Revenue Accounting Type:</label>
                <select id="revenue-type" name="revenue_type" required>
                    <option value="">Select a revenue type</option>
                    ${revenueTypeOptions}
                </select>
            </div>
            <div data-lang-id="016-submit-button" style="margin:auto;text-align: center;margin-top:30px;">
                <button type="submit" class="submit-button enabled" aria-label="Submit Form">➕ Add Revenue Transaction</button>
            </div>
        </form>
    `;

    // Populate the modal content
    modalContentBox.innerHTML = formHTML;

    // Show the modal
    modal.classList.remove('modal-hidden');
}


// ADD Revenue
function addRevenueTrans() {
    // Select modal elements
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');

    // Show the modal
    modal.style.display = 'flex';
    modalBox.style.flexFlow = 'column';

    // Lock scrolling for the body and blur the background
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open'); // Locks scrolling

    // Set up the modal-content-box styles
    const modalContentBox = document.getElementById('modal-content-box');
    modalContentBox.style.maxHeight = '80vh'; // Ensure it doesn’t exceed 80% of the viewport height
    modalContentBox.style.overflowY = 'auto'; // Make the modal scrollable if content overflows

    // List of revenue_accounting_type values
    const revenueTypes = [
        'Trainer EarthWands Remittance',
        'Trainer Course 20% Remittance',
        'Other',
        'Open donation',
        'O2E Contribution',
        'GEA Trainer Monthly Contribution',
        'GEA product purchase',
        'GEA Course Registration',
        'GEA Catalyst Program Fee',
        'Founder contribution',
        'Encouragement',
        'Brikcoin purchase',
        'Arc.io CDN',
        'AES Plastic Offset Purchase'
    ];

    // List of receiving_gea_acct options
    const receivingAccounts = [
        'CAN Bank Account',
        'ID Bank Account',
        'PayPal Account',
        'Yayasan Bank Account'
    ];

    // Generate options for the dropdowns
    const revenueTypeOptions = revenueTypes
        .map(type => `<option value="${type}">${type}</option>`)
        .join('');
    const receivingAcctOptions = receivingAccounts
        .map(account => `<option value="${account}">${account}</option>`)
        .join('');

    // Create the form HTML
    const formHTML = `
        <h2 style="text-align:center">Add Revenue Transaction</h2>
        <form id="add-revenue-form" onsubmit="submitRevenueTrans(event)">
            <div class="form-item" style="margin-top: 25px;">
                <label for="amount-idr">Amount (IDR):</label>
                <input type="number" id="amount-idr" name="amount_idr" required />
            </div>
            <div class="form-item">
                <label for="sender">From:</label>
                <input type="text" id="sender" name="sender" required />
            </div>
            <div class="form-item">
                <label for="transaction-date">Transaction Date:</label>
                <input type="date" id="transaction-date" name="transaction_date" value="${new Date().toISOString().split('T')[0]}" required />
            </div>
            <div class="form-item">
                <label for="description">Transaction Description:</label>
                <textarea id="description" name="description" rows="4" required style="width:90%"></textarea>
            </div>
            <div class="form-item">
                <label for="revenue-type">Revenue Accounting Type:</label>
                <select id="revenue-type" name="revenue_type" required>
                    <option value="">Select a revenue type</option>
                    ${revenueTypeOptions}
                </select>
            </div>
            <div class="form-item">
                <label for="receiving-gea-acct">Receiving GEA Account:</label>
                <select id="receiving-gea-acct" name="receiving_gea_acct" required>
                    <option value="">Select an account</option>
                    ${receivingAcctOptions}
                </select>
            </div>
            <!-- Uncomment the following section to include the "Was this transaction sent by a GoBrik user?" field -->
            <!--
            <div class="form-item">
                <label for="sender-ecobricker">Was this revenue sent by a GoBrik user?</label>
                <select id="sender-ecobricker" name="sender_ecobricker">
                    <option value="">Select an option</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </div>
            -->
            <!-- Uncomment the following section to include the file upload for transaction record -->
            <!--
            <div class="form-item">
                <label for="transaction-image">Image of Transaction Record:</label>
                <input type="file" id="transaction-image" name="transaction_image" accept="image/*" />
            </div>
            -->
            <div data-lang-id="016-submit-button" style="margin:auto;text-align: center;margin-top:30px;">
                <button type="submit" class="submit-button enabled" aria-label="Submit Form">➕ Add Revenue Transaction</button>
            </div>
        </form>
    `;

    // Populate the modal content
    modalContentBox.innerHTML = formHTML;

    // Show the modal
    modal.classList.remove('modal-hidden');
}



function submitRevenueTrans(event) {
    event.preventDefault(); // Prevent default form submission

    // Get form data
    const formData = new FormData();
    formData.append('amount_idr', document.getElementById('amount-idr').value);
    formData.append('sender', document.getElementById('sender').value);
    //formData.append('sender_ecobricker', document.getElementById('sender-ecobricker').value);
    formData.append('transaction_date', document.getElementById('transaction-date').value);
    formData.append('description', document.getElementById('description').value);
    formData.append('revenue_type', document.getElementById('revenue-type').value);
    formData.append('receiving_gea_acct', document.getElementById('receiving-gea-acct').value);
    //formData.append('transaction_image', document.getElementById('transaction-image').files[0]);

    // Send the data to the backend
    $.ajax({
        url: '../api/add_revenue_trans.php',
        type: 'POST',
        data: formData,
        contentType: false, // Necessary for file uploads
        processData: false, // Prevent jQuery from processing the data
        success: function (response) {
            const data = JSON.parse(response);
            if (data.success) {
                alert('Revenue transaction added successfully!');
                closeInfoModal(); // Close the modal
                location.reload(); // Reload the page to refresh data
            } else {
                alert(`Error: ${data.error}`);
            }
        },
        error: function (xhr, status, error) {
            alert('Failed to add revenue transaction. Please try again later.');
            console.error('AJAX Error:', status, error);
        }
    });
}

// ADD Expense
function addExpenseTrans() {
    // Select modal elements
    const modal = document.getElementById('form-modal-message');
    const modalBox = document.getElementById('modal-content-box');

    // Show the modal
    modal.style.display = 'flex';
    modalBox.style.flexFlow = 'column';

    // Lock scrolling for the body and blur the background
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open'); // Locks scrolling

    // Set up the modal-content-box styles
    const modalContentBox = document.getElementById('modal-content-box');
    modalContentBox.style.maxHeight = '80vh'; // Ensure it doesn’t exceed 80% of the viewport height
    modalContentBox.style.overflowY = 'auto'; // Make the modal scrollable if content overflows

    // List of expense_accounting_type values
    const expenseTypes = [
        'Work Space',
        'Visas',
        'Transportation',
        'Translation Services',
        'Trainers Fees',
        'Team Communications',
        'Team Communication Platform',
        'Site Servers & Domains',
        'Services',
        'Rent & Co-working',
        'Reimbursement',
        'Internet Access',
        'GEA team meeting expense',
        'EarthWand Expense',
        'Domain name fees',
        'Database & App',
        'Center Circle Salary (unpaid)',
        'Center Circle Salary',
        'App services',
        'Accomodations'
    ];

    // List of expense_vendor options
    const expenseVendors = [
        'Zoom Video Conferencing',
        'Vultr.com',
        'Visas for Trainings',
        'VectorStock',
        'Travel Service Provider',
        'Tony Rakka Cafe',
        'Misc. Vendor',
        'Make.com',
        'Localize',
        'Knack.com',
        'Integromat',
        'IndiHome Internet Provider',
        'I. Bagus Swastika',
        'Gumi Bamboo',
        'Eledo.online',
        'DropBox',
        'Cloudflare',
        '1&1 IONOS Inc.'
    ];

    // Generate options for the dropdowns
    const expenseTypeOptions = expenseTypes
        .map(type => `<option value="${type}">${type}</option>`)
        .join('');
    const expenseVendorOptions = expenseVendors
        .map(vendor => `<option value="${vendor}">${vendor}</option>`)
        .join('');

    // Create the form HTML
    const formHTML = `
        <h2 style="text-align:center">Add Expense Transaction</h2>
        <form id="add-expense-form" onsubmit="submitExpenseTrans(event)">
            <div class="form-item" style="margin-top: 25px;">
                <label for="amount-idr">Amount (IDR):</label>
                <input type="number" id="amount-idr" name="amount_idr" required />
            </div>
            <div class="form-item">
                <label for="receiver">To (only use this field for a GoBrik account user):</label>
                <input type="text" id="receiver" name="receiver" />
            </div>
            <div class="form-item">
                <label for="transaction-date">Transaction Date:</label>
                <input type="date" id="transaction-date" name="transaction_date" value="${new Date().toISOString().split('T')[0]}" required />
            </div>
            <div class="form-item">
                <label for="description">Transaction Description:</label>
                <textarea id="description" name="description" rows="4" required style="width:90%"></textarea>
            </div>
            <div class="form-item">
                <label for="expense-type">Expense Accounting Type:</label>
                <select id="expense-type" name="expense_type" required>
                    <option value="">Select an expense type</option>
                    ${expenseTypeOptions}
                </select>
            </div>
            <div class="form-item">
                <label for="expense-vendor">Expense Vendor:</label>
                <select id="expense-vendor" name="expense_vendor">
                    <option value="">Select a vendor (optional)</option>
                    ${expenseVendorOptions}
                </select>
            </div>
            <div data-lang-id="016-submit-button" style="margin:auto;text-align: center;margin-top:30px;">
                <button type="submit" class="submit-button enabled" aria-label="Submit Form">➕ Add Expense Transaction</button>
            </div>
        </form>
    `;

    // Populate the modal content
    modalContentBox.innerHTML = formHTML;

    // Show the modal
    modal.classList.remove('modal-hidden');
}


function submitExpenseTrans(event) {
    event.preventDefault(); // Prevent default form submission

    // Get form data and handle optional fields
    const formData = {
        amount_idr: document.getElementById('amount-idr').value,
        receiver: document.getElementById('receiver').value || '',
        transaction_date_dt: document.getElementById('transaction-date').value, // Corrected key
        description: document.getElementById('description').value,
        expense_type: document.getElementById('expense-type').value,
        expense_vendor: document.getElementById('expense-vendor').value || '' // Allow empty value
    };

    // Log formData for debugging
    console.log('Submitting formData:', formData);

    // Send the data to the backend
    $.ajax({
        url: '../api/add_expense_trans.php',
        type: 'POST',
        data: formData,
        success: function (response) {
            const data = JSON.parse(response);
            if (data.success) {
                alert('Expense transaction added successfully!');
                closeInfoModal(); // Close the modal
                location.reload(); // Reload the page to refresh data
            } else {
                alert(`Error: ${data.error}`);
            }
        },
        error: function (xhr, status, error) {
            alert('Failed to add expense transaction. Please try again later.');
            console.error('AJAX Error:', status, error);
        }
    });
}


</script>



</body>
</html>