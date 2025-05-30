
<?php
$directory = basename(dirname($_SERVER['SCRIPT_NAME']));
$lang = $directory;
$version = '0.35';
$page='brikchain';

$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">';
require_once ("../includes/brikchain-inc.php");
?>



<!--TOP PAGE BANNER-->


<div class="splash-content-block">
	<div class="splash-box">
		<div class="splash-heading" data-lang-id="001-splash-title">The Brikchain</div>
	    <div class="splash-sub" data-lang-id="002-splash-subtitle">All the Briks, Blocks & Transactions.</div>
	</div>
	<div class="splash-image" data-lang-id="003b-splash-image-alt"><img src="../webps/brikchain-450px.webp" style="width: 85%" alt="The brikchain explorer: search all the briks, blocks and transactions">
    </div>
</div>
<div id="splash-bar"></div>
<div id="top-page-image"></div>


  <!-- PAGE CONTENT -->
    <div id="top-page-image" class="offsetting top-page-image"></div>

    <div id="form-submission-box" class="landing-page-form">
        <div class="form-container">
            <div style="text-align:center;width:100%;margin:auto;margin-top:25px;">
                <h1 data-lang-id="001-brikchain-title">The Brikchain</h1>
                <h4 data-lang-id="002-under-construction" style="color:orange;">🚧 Under construction</h4>
                <p data-lang-id="004-lead-page-paragraph">Search and explore the full chain of authenticated ecobricks, blocks and transactions that make up the brikcoin manual blockchain.</p>

            </div>
            <div class="page-paragraph">

				<p data-lang-id="005-first-page-paragraph">The Brikchain is a manual blockchain that quantifies the ecological value of plastic <a href="sequest.php">sequestered</a> out of the biosphere and out of industry.  Every ecobrick that is authenticated on the GoBrik platform is permanently recorded on the <a href="https://ecobricks.org/en/brikcoins.php">Brikcoin Manual Blockchain</a>.  With each authentication, the corresponding value of sequestered plastic (<a href="https://ecobricks.org/aes">AES plastic</a>) is issued in brikcoins</a>.  Each issuance is made through a block of transactions that are recorded sequentially.  This chain of blocks, transaction and ecobricks is fully searchable here.</p>

				<p data-lang-id="005b-second-page-paragraph">Use the tools below to browse the Brikchain.  You can also view our <a href="open-books.php">Open Books</a> financial accounting.  To learn how the combination of our financial and <a href="regen-reports.php">ecological accounting</a> generates the price per Kg of AES sales see our <a href="https://ecobricks.org/en/offsets.php">offsetting page</a>.</p> </p>
			</div>

        <?php require_once ("side-modules/brikcoin-live-values.php");?>


	<div class="live-data" style="margin-top:30px">
		<?php
			$sql = "SELECT * FROM vw_detail_sums_by_year  WHERE year = 2024;"; $result = $conn->query($sql);
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



	<div class="reg-content-block" id="block2">
    <div class="opener-header">
        <div class="opener-header-text">
            <h4 data-lang-id="007-authenticated-header">Authenticated Ecobricks</h4>
            <h6 data-lang-id="008-authenticated-description">The archival chain of authenticated ecobricks.  Updated every six hours with the latest confirmed authentications.</h6>
            <div class="ecobrick-data"><p data-lang-id="009-data-live"><span class="blink">⬤  </span> Data live</p></div>
            <div class="ecobrick-data"><p data-lang-id="010-historical-data">🟠 Historical Data pending transfer</p></div>
        </div>



			<button onclick="preclosed2()" class="block-toggle" id="block-toggle-show2">+</button>

		</div>

		<div id="preclosed2">

			<div class="overflow">

				<table id="ecobricks" class="display" style="width:100%">
					<thead>

						<tr>
							<th style="max-width: 150px !important;" data-lang-id="011-brik-header">Brik</th>
							<th data-lang-id="012-authenticated-header">Authenticated</th>
							<th data-lang-id="013-aes-plastic-header">AES Plastic</th>
							<th data-lang-id="014-maker-header">Maker</th>
							<th data-lang-id="015-value-header">Value</th>
							<th data-lang-id="016-co2e-header">CO2e</th>
							<th data-lang-id="017-serial-header"> 🔎 Serial</th>
                        </tr>
					</thead>
					<!--<tfoot>
						<tr>
							<<th style="max-width: 150px !important;">Brik</th>
							<th>Logged</th>
							<th>AES Plastic</th>
							<th>Value</th>
							<th>CO2e</th>
							<th>Serial</th>
						</tr>
					</tfoot>-->
				</table>
			</div>
		</div>
	</div>


	<div class="reg-content-block" id="block1">
    <div class="opener-header">
        <div class="opener-header-text">
            <h4 data-lang-id="018-blocks-transactions-header">Blocks & Transactions</h4>
            <h6 data-lang-id="019-full-chain-transactions-subheader">The full chain transactions chronicling the generation, exchange, and destruction of brikcoins.</h6>
            <div class="ecobrick-data"><p data-lang-id="020-data-live-current"><span class="blink">⬤  </span> Data live & current</p></div>
        </div>

        <button onclick="preclosed1()" class="block-toggle" id="block-toggle-show1">+</button>
    </div>

    <div id="preclosed1">
        <div class="overflow">
            <table id="brikchain" class="display" style="width:100%">
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
					<!--<tfoot>
						<tr>
						<th>Transaction</th>
						<th>Issued</th>
							<th>Sender</th>
							<th>Type</th>
							<th>Block</th>
							<th>Shard</th>
							<th>Ecobrick</th>
						</tr>
					</tfoot>-->
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

			$result = $conn->query($sql);

			if ($result->num_rows > 0) {

				echo'<table id="brikchain" class="display">
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

				$result = $conn->query($sql);

				if ($result->num_rows > 0) {

					echo'<table id="brikchain" class="display" ><tr><th>From</th><th>To</th><th>Total BRK Generated</th><th>Total BRK Destroyed</th><th>Total Brikcoins</th></tr>';

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


	<?php $gobrik_conn->close();?>




            </div>

        </div>
    </div>
</div>







	<!--FOOTER STARTS HERE-->

	<?php require_once ("../footer-2025.php");?>


<!-- CUSTOM PAGE SCRIPTS-->


<!-- This script is for pages that use the accordion content system-->
<script src="../scripts/accordion-scripts.js" defer></script>

</div>
</body>
</html>