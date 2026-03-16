<?php
require_once '../earthenAuth_helper.php';
require_once '../auth/session_start.php';
require_once '../gobrikconn_env.php';

header('Content-Type: application/json; charset=UTF-8');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'not_logged_in']);
    exit();
}

$buwana_id = $_SESSION['buwana_id'];

$training_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$ecobricker_id = isset($_GET['ecobricker_id']) ? intval($_GET['ecobricker_id']) : 0;

if ($training_id <= 0 || $ecobricker_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'invalid_params']);
    exit();
}

/* ---------------------------------------------------------
   Validate ecobricker belongs to logged-in user
--------------------------------------------------------- */
$stmt_user = $gobrik_conn->prepare("
    SELECT buwana_id
    FROM tb_ecobrickers
    WHERE ecobricker_id = ?
    LIMIT 1
");

if (!$stmt_user) {
    echo json_encode(['success' => false, 'error' => 'db_user_prepare']);
    exit();
}

$stmt_user->bind_param('i', $ecobricker_id);
$stmt_user->execute();
$stmt_user->bind_result($owner_buwana_id);
$user_found = $stmt_user->fetch();
$stmt_user->close();

if (!$user_found) {
    echo json_encode(['success' => false, 'error' => 'user_not_found']);
    exit();
}

if ((int)$owner_buwana_id !== (int)$buwana_id) {
    echo json_encode(['success' => false, 'error' => 'unauthorized']);
    exit();
}

/* ---------------------------------------------------------
   Begin transaction
--------------------------------------------------------- */
$gobrik_conn->begin_transaction();

try {
    $deleted_anything = false;

    /* -----------------------------------------------------
       1. Remove new-style registration if it exists
    ----------------------------------------------------- */
    $registration_id = null;
    $pledge_id = null;

    $stmt_reg = $gobrik_conn->prepare("
        SELECT registration_id, pledge_id
        FROM training_registrations_tb
        WHERE training_id = ? AND buwana_id = ?
        LIMIT 1
    ");

    if (!$stmt_reg) {
        throw new Exception('db_reg_prepare');
    }

    $stmt_reg->bind_param('ii', $training_id, $buwana_id);
    $stmt_reg->execute();
    $stmt_reg->bind_result($registration_id, $pledge_id);
    $has_registration = $stmt_reg->fetch();
    $stmt_reg->close();

    if ($has_registration) {
        // Delete registration first
        $stmt_del_reg = $gobrik_conn->prepare("
            DELETE FROM training_registrations_tb
            WHERE registration_id = ?
        ");
        if (!$stmt_del_reg) {
            throw new Exception('db_reg_delete_prepare');
        }

        $stmt_del_reg->bind_param('i', $registration_id);
        if (!$stmt_del_reg->execute()) {
            throw new Exception('db_reg_delete_execute');
        }
        $stmt_del_reg->close();
        $deleted_anything = true;

        // Delete linked pledge if present
        if (!empty($pledge_id)) {
            $stmt_del_pledge = $gobrik_conn->prepare("
                DELETE FROM training_pledges_tb
                WHERE pledge_id = ? AND training_id = ? AND buwana_id = ?
            ");
            if (!$stmt_del_pledge) {
                throw new Exception('db_pledge_delete_prepare');
            }

            $stmt_del_pledge->bind_param('iii', $pledge_id, $training_id, $buwana_id);
            if (!$stmt_del_pledge->execute()) {
                throw new Exception('db_pledge_delete_execute');
            }
            $stmt_del_pledge->close();
        } else {
            // Safety cleanup in case a pledge exists but linkage was not stored
            $stmt_del_pledge_fallback = $gobrik_conn->prepare("
                DELETE FROM training_pledges_tb
                WHERE training_id = ? AND buwana_id = ?
            ");
            if (!$stmt_del_pledge_fallback) {
                throw new Exception('db_pledge_fallback_prepare');
            }

            $stmt_del_pledge_fallback->bind_param('ii', $training_id, $buwana_id);
            if (!$stmt_del_pledge_fallback->execute()) {
                throw new Exception('db_pledge_fallback_execute');
            }
            $stmt_del_pledge_fallback->close();
        }
    } else {
        // Even if no registration exists, clean up any orphan pledge rows
        $stmt_del_pledge_orphan = $gobrik_conn->prepare("
            DELETE FROM training_pledges_tb
            WHERE training_id = ? AND buwana_id = ?
        ");
        if (!$stmt_del_pledge_orphan) {
            throw new Exception('db_pledge_orphan_prepare');
        }

        $stmt_del_pledge_orphan->bind_param('ii', $training_id, $buwana_id);
        if (!$stmt_del_pledge_orphan->execute()) {
            throw new Exception('db_pledge_orphan_execute');
        }
        if ($stmt_del_pledge_orphan->affected_rows > 0) {
            $deleted_anything = true;
        }
        $stmt_del_pledge_orphan->close();
    }

    /* -----------------------------------------------------
       2. Remove legacy trainee registration if it exists
    ----------------------------------------------------- */
    $stmt_del_legacy = $gobrik_conn->prepare("
        DELETE FROM tb_training_trainees
        WHERE training_id = ? AND ecobricker_id = ?
    ");

    if (!$stmt_del_legacy) {
        throw new Exception('db_legacy_prepare');
    }

    $stmt_del_legacy->bind_param('ii', $training_id, $ecobricker_id);
    if (!$stmt_del_legacy->execute()) {
        throw new Exception('db_legacy_execute');
    }

    if ($stmt_del_legacy->affected_rows > 0) {
        $deleted_anything = true;
    }
    $stmt_del_legacy->close();

    /* -----------------------------------------------------
       3. Optional legacy/simple-paid payment cleanup
       For now, we do not delete payments because they may
       be accounting records. This endpoint is for unregistering,
       not rewriting financial history.
    ----------------------------------------------------- */

    $gobrik_conn->commit();
    $gobrik_conn->close();

    if ($deleted_anything) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'not_found']);
    }
    exit();

} catch (Throwable $e) {
    $gobrik_conn->rollback();
    $gobrik_conn->close();
    error_log('unregister_training.php error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'transaction_failed']);
    exit();
}
?>