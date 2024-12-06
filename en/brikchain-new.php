<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.46';
$page = 'brikchain';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = isLoggedIn(); // Check if the user is logged in using the helper function


// Check if the user is logged in
if (isLoggedIn()) {
    $buwana_id = $_SESSION['buwana_id'];
        // Include database connection
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch the user's location data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $first_name = getFirstName($buwana_conn, $buwana_id);

    $buwana_conn->close();  // Close the database connection
} else {

}
// Include database connection
require_once '../gobrikconn_env.php';


// Fetch the count of ecobricks and the total weight in kg
$sql = "SELECT COUNT(*) as ecobrick_count, SUM(weight_g) / 1000 as total_weight FROM tb_ecobricks WHERE status != 'not ready'";
$result = $gobrik_conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $ecobrick_count = number_format($row['ecobrick_count'] ?? 0);
    $total_weight = number_format(round($row['total_weight'] ?? 0)); // Format with commas and round to the nearest whole number
} else {
    $ecobrick_count = '0';
    $total_weight = '0';
}

echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';
?>

<!-- Page CSS & JS Initialization -->
<?php require_once("../includes/brikchain-inc.php"); ?>

<!--TOP PAGE BANNER-->

<div class="splash-content-block">
</div>

    <div id="splash-bar" style="margin-bottom:-80vh"></div>

  <!-- PAGE CONTENT -->
    <div id="top-page-image" class="brikchain top-page-image"></div>

    <div id="form-submission-box" class="landing-page-form">
        <div class="form-container">
            <div style="text-align:center;width:100%;margin:auto;margin-top:25px;">
                <h1 data-lang-id="001-brikchain-title">The Brikchain</h1>
                <h4 data-lang-id="002-under-construction" style="color:orange;">🚧 Under construction</h4>
                <div class="splash-sub" data-lang-id="002-splash-subtitle">All the Briks, Blocks & Transactions.</div>

            </div>
            <div class="page-paragraph">

				<p data-lang-id="005-first-page-paragraph">The Brikchain is a manual blockchain that quantifies the ecological value of plastic <a href="sequest.php">sequestered</a> out of the biosphere and out of industry.  Every ecobrick that is authenticated on the GoBrik platform is permanently recorded on the <a href="https://ecobricks.org/en/brikcoins.php">Brikcoin Manual Blockchain</a>.  With each authentication, the corresponding value of sequestered plastic (<a href="https://ecobricks.org/aes">AES plastic</a>) is issued in brikcoins</a>.  Each issuance is made through a block of transactions that are recorded sequentially.  This chain of blocks, transaction and ecobricks is fully searchable here.</p>

				<p data-lang-id="005b-second-page-paragraph">Use the tools below to browse the Brikchain.  You can also view our <a href="open-books.php">Open Books</a> financial accounting.  To learn how the combination of our financial and <a href="regen-reports.php">ecological accounting</a> generates the price per Kg of AES sales see our <a href="https://ecobricks.org/en/offsets.php">offsetting page</a>.</p> </p>
			</div>

        <?php require_once ("side-modules/brikcoin-live-values.php");?>


	<div class="live-data" style="margin-top:30px">
		<?php
			$sql = "SELECT * FROM vw_detail_sums_by_year  WHERE year = 2024;"; $result = $gobrik_conn->query($sql);
			if ($result->num_rows > 0) {

				while($row = $result->fetch_assoc()) {
				echo '<p><span class="blink">◉  </span>  '.$row["final_aes_plastic_cost"].' &#8202;$ USD per 1 Kg of AES Plastic</p>'  ;
				}
					} else {
						echo "0 results";
					}
		?>

		<p style="font-size: 0.85em; margin-top:20px;" data-lang-id="006-current-pricing">This is the current price for the <a href="offsets.php" target="_blank">sale of AES plastic offsets</a>.</p>
	</div>


<!--BLOCKS-->


	<div class="reg-content-block" id="block1">
    <div class="opener-header">
        <div class="opener-header-text">
            <h4 data-lang-id="018-blocks-transactions-header">Blocks & Transactions</h4>
            <h6 data-lang-id="019-full-chain-transactions-subheader">The full chain transactions chronicling the generation, exchange, and destruction of brikcoins.</h6>
            <div class="ecobrick-data"><p data-lang-id="020-data-live-current"><span class="blink">⬤  </span> Data live & current</p></div>
        </div>

        <button onclick="preclosed1()" class="block-toggle" id="block-toggle-show1">+</button>
    </div>

    <div id="preclosed1-not">
        <div class="overflow">
            <table id="brikchain-transactions" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th data-lang-id="021-transaction-header">🔎 Transaction</th>
                        <th data-lang-id="022-issued-header">Issued</th>
                        <th data-lang-id="023-sender-header">Sender</th>
                        <th data-lang-id="024-recipient-header">Recipient</th>
                        <th data-lang-id="025-type-header">Type</th>
                        <th data-lang-id="026-block-header">Block</th>
                        <th data-lang-id="027-shard-header">Shard</th>
                        <th data-lang-id="028-ecobrick-header">Ecobrick</th>
                    </tr>
                </thead>

				</table>
			</div>
		</div>
	</div>

	<div class="reg-content-block" id="block3">
		<div class="opener-header">
			<div class="opener-header-text">
				<h4 data-lang-id="029-aes-plastic-valuations-header">AES Plastic Valuations</h4>
				<h6 data-lang-id="030-aes-value-description">Each year the value of 1 Kg of AES plastic is determined by the ecobricks authenticated in that year.  The net weight of the authenticated plastic is divided by the GEA's expenses maintaining the blockchain (see the GEA's yearly <a href="open-books.php" data-lang-id="031-open-books-link">Open Books</a> financial accounting)</h6>
				<div class="ecobrick-data"><p data-lang-id="032-brikcoin-data-live"><span class="blink">⬤  </span> Brikcoin Data live</p></div>
				<div class="ecobrick-data"><p data-lang-id="033-openbooks-accounting-live"><span class="blink">⬤  </span> OpenBooks Accounting live</p></div>
				<div class="ecobrick-data"><p data-lang-id="034-ecobrick-weight-estimated">🟠 Ecobrick weight data estimated (pending archival transfer)</p></div>
			</div>
			<button onclick="preclosed3()" class="block-toggle" id="block-toggle-show3">+</button>
		</div>


		<div id="preclosed3">

			<div class="overflow">

			<?php

			$sql = "SELECT * FROM vw_detail_sums_by_year Order by `year` DESC;";

			$result = $gobrik_conn->query($sql);

			if ($result->num_rows > 0) {

				echo'<table id="brikchain-overview" class="display">
				<tr>
					<th data-lang-id="035-year-header">Year</th>
					<th data-lang-id="036-brk-generated-header">BRK Generated</th>
					<!-- <th>Authenticated</th> -->
					<th data-lang-id="037-calculated-aes-plastic-header">Calculated AES plastic</th>
					<!-- <th>Tallied AES Plastic</th> -->
					<th data-lang-id="038-gea-year-expenses-header">GEA Year Expenses</th>
					<th data-lang-id="039-1kg-aes-value-header">1kg AES Value</th>
				</tr>
			';

			// output data of each row
			while($row = $result->fetch_assoc()) {

				echo "<tr>
				<td>".$row["year"]."</td>
				<td>".$row["total_brk"]."&#8202;ß</td>
				<!--<td>".$row["brick_count"]." ecobricks</td>-->
				<td>".$row["calculated_weight"]."&#8202;Kg</td>
				<!--<td>".$row["weight"]."&#8202;Kg</td>-->
				<td>".$row["tot_usd_exp_amt"]."&#8202;$ USD</td>
				<td>".$row["final_aes_plastic_cost"]."&#8202;$ USD</td>
				</tr>";
				}
				echo "</table>";
			} else {
				echo "0 results";
			}
			?>
			</div>
		</div>
	</div>

	<div class="reg-content-block" id="block4">
    <div class="opener-header">
        <div class="opener-header-text">
            <h4 data-lang-id="040-total-brikcoin-pool-header">Total Brikcoin Pool</h4>
            <h6 data-lang-id="041-running-yearly-totals-description">Running and yearly totals of brikcoins generated, destroyed and in circulation. </h6>
            <div class="ecobrick-data"><p data-lang-id="042-data-live-current"><span class="blink">⬤  </span> Data live & current</p></div>
        </div>
        <button onclick="preclosed4()" class="block-toggle" id="block-toggle-show4">+</button>
    </div>

		<div id="preclosed4">

			<div class="overflow">


				<?php

				$sql = "SELECT * FROM vw_sum_brk_total ;";

				$result = $gobrik_conn->query($sql);

				if ($result->num_rows > 0) {

					echo'<table id="brikchain-total" class="display" ><tr><th>From</th><th>To</th><th>Total BRK Generated</th><th>Total BRK Destroyed</th><th>Total Brikcoins</th></tr>';

				// data-lang-id="042b-brik-total-table"  output data of each row
				//until($row = $result->fetch_assoc()) {
					$row = $result->fetch_assoc();
					echo "<tr><td>".$row["from_date"]."</td><td>".$row["to_date"]."</td><td>".$row["total_brk"]."&#8202;ß</td><td>".$row["aes_purchased"]."&#8202;kg</td><td>".$row["net_brk_in_circulation"]."&#8202;ß</td></tr>";
				//	}
					echo "</table>";
				} else {
					echo "Failed to connect to database";
				}

			?>
			</div>
		</div>
	</div>


            </div>

        </div>
    </div>
</div>







	<!--FOOTER STARTS HERE-->

	<?php require_once ("../footer-2024.php");?>


<!-- BRK TRANS DATATABLE -->
<script>
    $(document).ready(function () {
        $('#brikchain-transactions').DataTable({
            serverSide: true, // Enable server-side processing
            processing: true, // Show a processing indicator
            ajax: {
                url: '../api/fetch_brik_transactions.php', // Server endpoint to fetch data
                type: 'POST' // HTTP method
            },
            columns: [
                {
                    data: 'tran_id',
                    title: '🔎 Transaction',
                    render: function(data, type, row) {
                        // Make tran_id clickable
                        return `<a href="#" onclick="openTransactionModal(${data})">${data}</a>`;
                    }
                },
                { data: 'send_ts', title: 'Issued' },
                { data: 'sender', title: 'Sender' },
                { data: 'receiver_or_receivers', title: 'Recipient' },
                { data: 'block_tran_type', title: 'Type' },
                {
                    data: 'block_amt',
                    title: 'Block',
                    render: function(data, type, row) {
                        return `${parseFloat(data).toFixed(2)}&#8202;ß`;
                    }
                },
                {
                    data: 'individual_amt',
                    title: 'Shard',
                    render: function(data, type, row) {
                        return `${parseFloat(data).toFixed(2)}&#8202;ß`;
                    }
                },
                {
                    data: 'ecobrick_serial_no',
                    title: 'Brik',
                    render: function(data, type, row) {
                        return `<a href="brik.php" target="_blank">${data}</a>`;
                    }
                }
            ],
            order: [[0, 'desc']], // Sort by the first column (`tran_id`) in descending order
            pageLength: 12, // Number of rows per page
            lengthMenu: [12, 25, 50, 100] // Options for rows per page
        });
    });

    function openTransactionModal(tran_id) {
        // Blur the background and prepare the modal
        document.getElementById('page-content')?.classList.add('blurred');
        document.getElementById('footer-full')?.classList.add('blurred');
        document.body.classList.add('modal-open');

        // Clear previous modal content
        const modalMessage = document.querySelector('.modal-message');
        modalMessage.innerHTML = '<p>Loading transaction details...</p>';

        // Fetch transaction details
        fetch(`../api/fetch_brik_transactions.php?tran_id=${tran_id}`)
            .then(response => response.json())
            .then(data => {
                let tableHTML = '<table id="transaction-details-table" class="display" style="width:100%">';
                tableHTML += '<thead><tr><th>Field</th><th>Value</th></tr></thead><tbody>';

                for (const [field, value] of Object.entries(data)) {
                    tableHTML += `<tr><td>${field}</td><td>${value}</td></tr>`;
                }

                tableHTML += '</tbody></table>';

                modalMessage.innerHTML = tableHTML;

                $('#transaction-details-table').DataTable({
                    paging: false,
                    searching: false,
                    info: false
                });
            })
            .catch(error => {
                modalMessage.innerHTML = `<p>Error loading transaction details: ${error.message}</p>`;
            });

        document.getElementById('form-modal-message').classList.remove('modal-hidden');
    }
</script>










<!-- This script is for pages that use the accordion content system-->
<script src="../scripts/accordion-scripts.js" defer></script>

</div>
</body>
</html>