
<?php


// GitHub personal access token stored securely in environment
define('GITHUB_TOKEN', getenv('GITHUB_TOKEN') ?: ''); // token is set

// Owner and repository name
define('REPO_OWNER', 'gea-ecobricks');
define('REPO_NAME', 'gobrik-3.0');

// Function to make API requests to GitHub
function githubApiRequest($endpoint, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/repos/' . REPO_OWNER . '/' . REPO_NAME . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: token ' . GITHUB_TOKEN,
        'User-Agent: GoBrik-3.0-Middleware',
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['response' => json_decode($response, true), 'http_code' => $httpCode];
}

// Function to push changes to a file in the repository
function githubPushChange($filePath, $content, $commitMessage) {
    // Fetch the current file to get the SHA
    $currentFile = githubApiRequest('/contents/' . $filePath);

    // Check for errors in fetching the file
    if ($currentFile['http_code'] !== 200) {
        return ['error' => 'Failed to fetch current file. HTTP Code: ' . $currentFile['http_code']];
    }

    $data = [
        'message' => $commitMessage,
        'content' => base64_encode($content), // Encode the new content in base64
        'sha' => $currentFile['response']['sha'] ?? null // Use the current file's SHA to ensure correct update
    ];

    // Send the update request to GitHub
    $updateResponse = githubApiRequest('/contents/' . $filePath, 'PUT', $data);

    if ($updateResponse['http_code'] === 200 || $updateResponse['http_code'] === 201) {
        return ['success' => 'File updated successfully'];
    } else {
        return ['error' => 'Failed to push changes. HTTP Code: ' . $updateResponse['http_code']];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (empty(GITHUB_TOKEN)) {
        die(json_encode(['error' => 'GitHub token is missing or invalid.']));
    }

    $action = $_POST['action'];

    if ($action === 'update') {
        $filePath = urlencode($_POST['path'] ?? '');
        $content = $_POST['content'] ?? '';
        $commitMessage = $_POST['message'] ?? 'Updated file via GDE';

        if (empty($filePath) || empty($content)) {
            die(json_encode(['error' => 'File path and content are required.']));
        }

        $updateResult = githubPushChange($filePath, $content, $commitMessage);
        header('Content-Type: application/json');
        echo json_encode($updateResult);
    } else {
        echo json_encode(['error' => 'Invalid action specified.']);
    }


} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['path'])) {
    // Default GET operation to fetch file or repo structure
    $path = $_GET['path'];
    $contents = githubApiRequest('/contents/' . $path);

    if (!empty($contents['response'])) {
        header('Content-Type: application/json');
        echo json_encode($contents['response']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'File or directory not found.']);
    }
} else {
    // Fetch the root structure of the repo if no path specified
    $structure = githubApiRequest('/contents/');

    if (!empty($structure['response'])) {
        header('Content-Type: application/json');
        echo json_encode($structure['response']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Repository structure not found.']);
    }
}
?>
