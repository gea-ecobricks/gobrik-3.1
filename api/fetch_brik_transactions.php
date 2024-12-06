<?php
// Include the GoBrik server connection credentials
require_once '../gobrikconn_env.php';

try {
    // Get DataTables parameters
    $start = intval($_POST['start'] ?? 0); // Starting row index
    $length = intval($_POST['length'] ?? 10); // Number of rows to fetch
    $orderColumnIndex = intval($_POST['order'][0]['column'] ?? 0); // Column index for ordering
    $orderDir = $_POST['order'][0]['dir'] ?? 'asc'; // Sort direction ('asc' or 'desc')
    $searchValue = $_POST['search']['value'] ?? ''; // Search value, if any

    // Map column indexes to database fields
    $columns = [
        'tran_id',
        'send_ts',
        'sender',
        'receiver_or_receivers',
        'block_tran_type',
        'block_amt',
        'individual_amt',
        'ecobrick_serial_no'
    ];
    $orderColumn = $columns[$orderColumnIndex] ?? 'tran_id'; // Default to `tran_id` if index is invalid

    // Base query
    $sql = "SELECT
                chain_ledger_id,
                tran_id,
                tran_name,
                individual_amt,
                status,
                send_ts,
                sender_ecobricker,
                block_tran_type,
                block_amt,
                sender,
                receiver_or_receivers,
                receiver_1,
                receiver_2,
                receiver_3,
                receiver_central_reserve,
                sender_central_reserve,
                ecobrick_serial_no,
                tran_sender_note,
                product,
                send_dt,
                accomp_payment,
                authenticator_version,
                expense_type,
                gea_accounting_category,
                shipping_cost_brk,
                product_cost_brk,
                total_cost_incl_shipping,
                shipping_with_currency,
                aes_officially_purchased,
                country_of_buyer,
                currency_for_shipping,
                credit_other_ecobricker_yn,
                catalyst_name
            FROM tb_brk_transaction";

    // Apply search filter
    if (!empty($searchValue)) {
        $sql .= " WHERE tran_id LIKE ? OR sender LIKE ? OR receiver_or_receivers LIKE ?";
    }

    // Add ORDER BY clause for sorting
    $sql .= " ORDER BY $orderColumn $orderDir";

    // Add LIMIT for pagination
    $sql .= " LIMIT ?, ?";

    // Prepare the statement
    $stmt = $gobrik_conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare the statement: " . $gobrik_conn->error);
    }

    // Bind parameters
    if (!empty($searchValue)) {
        $searchParam = "%$searchValue%";
        $stmt->bind_param("sssii", $searchParam, $searchParam, $searchParam, $start, $length);
    } else {
        $stmt->bind_param("ii", $start, $length);
    }

    // Execute the query
    $stmt->execute();

    // Fetch data
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // Get total record count
    $totalQuery = "SELECT COUNT(*) as total FROM tb_brk_transaction";
    $totalResult = $gobrik_conn->query($totalQuery);
    $totalRecords = $totalResult->fetch_assoc()['total'];

    // Prepare the response
    $response = [
        "draw" => intval($_POST['draw'] ?? 1), // DataTables draw counter
        "recordsTotal" => $totalRecords, // Total number of records
        "recordsFiltered" => $totalRecords, // Total records after filtering (same if no filter applied)
        "data" => $data // Data to display
    ];

    // Return the data as JSON
    echo json_encode($response);

} catch (Exception $e) {
    // Return the error as JSON
    echo json_encode(['error' => $e->getMessage()]);
}
?>
