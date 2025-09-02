<?php
// Dhru Fusion Proxy \u2014 forwards requests to your real Dhru provider endpoint
// Configure provider URL in .env as DHRU_PROVIDER_URL (e.g., https://provider.com/api/index.php)

declare(strict_types=1);

// Always return JSON
header('Content-Type: application/json; charset=utf-8');
header('X-Dhru-Proxy: 1'); // diagnostic header to confirm proxy is hit

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ERROR' => [['MESSAGE' => 'Method not allowed']]]);
    exit;
}

// Load .env so this standalone script can access DHRU_PROVIDER_URL
$rootDir = dirname(__DIR__, 2); // project root (e.g., /var/www)
$autoload = $rootDir . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require_once $autoload;
    if (class_exists(\Dotenv\Dotenv::class)) {
        // Load env without overriding existing environment variables
        \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
    }
}

// Read provider URL from env (supports $_ENV/$_SERVER/getenv)
$providerUrl = $_ENV['DHRU_PROVIDER_URL'] ?? $_SERVER['DHRU_PROVIDER_URL'] ?? getenv('DHRU_PROVIDER_URL');

if (!$providerUrl) {
    http_response_code(500);
    echo json_encode(['ERROR' => [['MESSAGE' => 'Missing DHRU_PROVIDER_URL in environment']]]);
    exit;
}

// Protect against misconfiguration (must not point to this proxy)
if (preg_match('~/dhru/index\.php$~i', (string)$providerUrl)) {
    http_response_code(500);
    echo json_encode(['ERROR' => [['MESSAGE' => 'Misconfigured DHRU_PROVIDER_URL (points to proxy, not provider)']]]);
    exit;
}


// Collect incoming fields as-is (Dhru expects urlencoded form fields)
$payload = [
    'username'     => $_POST['username']     ?? '',
    'apiaccesskey' => $_POST['apiaccesskey'] ?? '',
    'action'       => $_POST['action']       ?? '',
    'parameters'   => $_POST['parameters']   ?? '', // base64-encoded JSON per Dhru standard
];

// Basic validation
if ($payload['username'] === '' || $payload['apiaccesskey'] === '' || $payload['action'] === '') {
    http_response_code(400);
    echo json_encode(['ERROR' => [['MESSAGE' => 'username, apiaccesskey, and action are required']]]);
    exit;
}

// Forward via cURL
$ch = curl_init($providerUrl);
if ($ch === false) {
    http_response_code(500);
    echo json_encode(['ERROR' => [['MESSAGE' => 'Failed to initialize cURL']]]);
    exit;
}

$headers = [
    'Accept: application/json',
    'Content-Type: application/x-www-form-urlencoded',
];

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_POSTFIELDS => http_build_query($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true, // capture headers to split
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_ENCODING => '', // auto-decode gzip/deflate if returned by upstream
]);

$response = curl_exec($ch);
if ($response === false) {
    $err = curl_error($ch);
    curl_close($ch);
    http_response_code(502);
    echo json_encode(['ERROR' => [['MESSAGE' => 'Upstream request failed: ' . $err]]]);
    exit;
}

$httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: 200;
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE) ?: 0;
$body       = substr($response, $headerSize);

curl_close($ch);

// Pass-through upstream status code and convert XML -> JSON if needed
http_response_code($httpCode);

$rawHeaders = substr($response, 0, $headerSize) ?: '';
$ctype = null;
foreach (explode("\r\n", strtolower($rawHeaders)) as $line) {
    if (str_starts_with($line, 'content-type:')) {
        $ctype = trim(substr($line, strlen('content-type:')));
        break;
    }
}

// Try to convert XML to JSON unconditionally; if parsing fails, return original body
// Allow forcing JSON via ?format=json for debugging/clients (kept for compatibility)
$forceJson = isset($_GET['format']) && $_GET['format'] === 'json';

// Clean illegal control chars and BOM that may break XML parser (avoid UTF-8 regex flag)
$bodyClean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$body);
$bodyClean = preg_replace('/^\xEF\xBB\xBF/', '', (string)$bodyClean);

// Replace HTML named entities not defined in XML with UTF-8 equivalents
$allowed = ['amp','lt','gt','quot','apos'];
$entityMap = [
    'nbsp' => ' ',
    'rarr' => '→',
    'larr' => '←',
    'uarr' => '↑',
    'darr' => '↓',
    'hellip' => '…',
    'rsquo' => '’',
    'lsquo' => '‘',
    'rdquo' => '”',
    'ldquo' => '“',
    'ndash' => '–',
    'mdash' => '—',
    'copy' => '©',
    'reg' => '®',
    'trade' => '™',
    'deg' => '°',
    'bull' => '•',
];
$bodyClean = preg_replace_callback('/&([a-zA-Z][a-zA-Z0-9]+);/', function($m) use ($allowed, $entityMap) {
    $name = strtolower($m[1]);
    if (in_array($name, $allowed, true)) {
        return '&' . $name . ';';
    }
    return $entityMap[$name] ?? ' ';
}, (string)$bodyClean);

libxml_use_internal_errors(true);
$xml = simplexml_load_string((string)$bodyClean, 'SimpleXMLElement', LIBXML_NOCDATA);
if ($xml === false) {
    $errs = libxml_get_errors();
    if (!empty($errs)) {
        $first = $errs[0];
        $msg = 'line ' . ($first->line ?? 0) . ': ' . trim($first->message ?? 'err');
        header('X-Dhru-XmlErr: ' . substr(str_replace(["\r","\n"], ' ', $msg), 0, 180));
    }
    libxml_clear_errors();

    // Try DOMDocument with tolerant flags
    $dom = new DOMDocument('1.0', 'UTF-8');
    $ok = $dom->loadXML((string)$bodyClean, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET | LIBXML_NOCDATA | LIBXML_PARSEHUGE);
    if ($ok) {
        $xml = simplexml_import_dom($dom);
    }
}

if ($xml !== false && $xml !== null) {
    $arr = json_decode(json_encode($xml), true);
    header('X-Dhru-Converted: 1');
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

// Fallback: return original body if XML parsing failed
header('X-Dhru-Converted: 0');
// Include a small sniff of the body to help debugging
$sniff = substr((string)$body, 0, 80);
header('X-Dhru-BodyStart: ' . base64_encode($sniff));
echo $body;
