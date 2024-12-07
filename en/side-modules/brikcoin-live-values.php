
<?php

$sql = "SELECT * FROM vw_sum_brk_total ;";
$result = $gobrik_conn->query($sql);
        if ($result->num_rows > 0) {
            echo'<div class="side-module-desktop-mobile" style="display:flex;flex-flow:row">';
        while($row = $result->fetch_assoc()) {
            echo '
            <div id="aes-image" style="width:15%">
                <img src="../svgs/aes-brk-vertical.svg?v=2" style="width:95%; margin-top:20px;"><p style="font-size: 1.0em; margin-top:5px;">
            </div>

            <div id="aes-data" style="width:70%;text-align:left;padding-left:15px;">
                <p>Live Brikcoin value:</p>
            <span class="courier">
                <span class="blink" style="font-size: 1.0em; padding-bottom: 10px">⬤</span>
                <span style="font-size: 1.4em;">'.$row["plastic_value_g_per_brk"].'g AES</span>
                </span>
            </p>';
            echo '<p style="font-size: 1.0em; margin-top:10px;">Current 2024 Price per 1 Kg AES plastic:<br><span class="courier">2.40 USD</span></p>' ; 
            echo '<p style="font-size: 1.0em; margin-top:10px;">Total Circulation:<br><b><span class="courier">'.$row["net_brk_in_circulation"].'&#8202;BRK</b></span></p>' ; 
            echo '<p style="font-size: 0.75em; margin-top:10px;">Brikcoins <a href="/brikcoins.php">(BRK / ß)</a> are commodified in ecological value measured in kilograms of authenticated ecobrick sequestered plastic (<a href="sequestered.php">AES</a>).</p>
            <p><a class="module-btn" href="offsetting.php">ℹ️ AES Offsetting</a></p>
            </div>
            </div>' ; 
        
            } 
            
        } else {
            echo "Failed to connect to Brikchain</div>";
        }
        ?>
