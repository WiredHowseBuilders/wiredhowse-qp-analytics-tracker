<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    FileLoader::incs('app', 'AbsPathInc.php');
    FileLoader::incs('util', 'AccessInc.php');
    Env::load();
    FileLoader::incs('class', 'EmailTracker.php');

    header('Content-Type: application/json');

    try {

        $db = new mysqli(
            Env::get('DB_HOST'),
            Env::get('DB_USER'),
            Env::get('DB_PASS'),
            Env::get('DB_NAME')
        );

        if ($db->connect_error) {
            throw new Exception('Database connection failed: ' . $db->connect_error);
        }

        $tracker = new EmailTracker($db);

        // Get request data
        $input  = json_decode(file_get_contents('php://input'), true) ?: [];
        $action = $input['action'] ?? '';

        switch ($action) {

            case 'list':
                // List emails with filters
                $status = !empty($input['status']) ? $input['status'] : null;
                $search = !empty($input['search']) ? $input['search'] : null;
                $sortBy = $input['sortBy'] ?? 'email_date DESC';
                $page   = (int)($input['page']  ?? 1);
                $limit  = (int)($input['limit'] ?? 50);
                $offset = ($page - 1) * $limit;

                $filters = [];
                if ($status) {
                    $filters['status'] = $status;
                }

                // Handle search
                $emails = [];
                if ($search) {
                    $emails = $tracker->searchEmails($search, 1000);

                    // Apply status filter if needed
                    if ($status) {
                        $emails = array_filter($emails, function ($email) use ($status) {
                            return $email['status'] === $status;
                        });
                    }

                    // Apply sorting
                    usort($emails, function ($a, $b) use ($sortBy) {
                        $parts = explode(' ', $sortBy);
                        $field = $parts[0];
                        $order = $parts[1] ?? 'DESC';

                        $aVal = $a[$field] ?? 0;
                        $bVal = $b[$field] ?? 0;

                        if ($order === 'DESC') {
                            return $bVal <=> $aVal;
                        } else {
                            return $aVal <=> $bVal;
                        }
                    });

                    $total  = count($emails);
                    $emails = array_slice($emails, $offset, $limit);

                } else {
                    // No search - use regular query
                    $emails = $tracker->getEmails($filters, $sortBy, $limit, $offset);

                    // Get total count for pagination
                    $allEmails = $tracker->getEmails($filters, 'id', 100000);
                    $total     = count($allEmails);
                }

                // Calculate stats
                $allForStats = $tracker->getEmails($filters, 'id', 100000);
                $stats = [
                    'total'   => $total,
                    'clicks'  => array_sum(array_column($allForStats, 'clicks')),
                    'revenue' => array_sum(array_column($allForStats, 'revenue'))
                ];

                echo json_encode([
                    'success' => true,
                    'emails'  => $emails,
                    'stats'   => $stats,
                    'total'   => $total,
                    'page'    => $page
                ]);
                break;

            case 'get':
                // Get single email
                $id = (int)($input['id'] ?? 0);

                if (!$id) {
                    echo json_encode(['success' => false, 'message' => 'Invalid email ID']);
                    break;
                }

                $email = $tracker->getEmail($id);

                if (!$email) {
                    echo json_encode(['success' => false, 'message' => 'Email not found']);
                    break;
                }

                echo json_encode([
                    'success' => true,
                    'email'   => $email
                ]);
                break;

            case 'update':
                // Update email
                $id      = (int)($input['id'] ?? 0);
                $updates = $input['updates'] ?? [];

                if (!$id) {
                    echo json_encode(['success' => false, 'message' => 'Invalid email ID']);
                    break;
                }

                if (empty($updates)) {
                    echo json_encode(['success' => false, 'message' => 'No updates provided']);
                    break;
                }

                // Clean up updates - remove empty values that should be null
                $cleanUpdates = [];
                foreach ($updates as $key => $value) {
                    if ($value === '' || $value === null) {
                        $cleanUpdates[$key] = null;
                    } else {
                        $cleanUpdates[$key] = $value;
                    }
                }

                $success = $tracker->updateMetrics($id, $cleanUpdates);

                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'Email updated successfully' : 'Failed to update email'
                ]);
                break;

            case 'stats':
                // Get overall stats
                $emails = $tracker->getEmails([], 'id', 100000);

                $stats = [
                    'total'       => count($emails),
                    'clicks'      => array_sum(array_column($emails, 'clicks')),
                    'revenue'     => array_sum(array_column($emails, 'revenue')),
                    'avg_quality' => 0
                ];

                $qualityScores = array_filter(array_column($emails, 'quality_score'));
                if (!empty($qualityScores)) {
                    $stats['avg_quality'] = array_sum($qualityScores) / count($qualityScores);
                }

                echo json_encode([
                    'success' => true,
                    'stats'   => $stats
                ]);
                break;

            default:
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid action'
                ]);
        }

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

} else {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}