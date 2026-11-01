<?php
/**
 * deploy_upload_prod.php : envoi de fichiers vers le serveur FTP de PRODUCTION
 * défini dans les variables FTP_PROD_* du fichier .env.
 *
 * CLI uniquement : le script refuse de s'exécuter depuis le navigateur.
 * Une confirmation (taper OUI) est demandée avant chaque envoi.
 *
 * Usage :
 *   php deploy_upload_prod.php <fichier_ou_dossier_local> <dossier_distant>
 *
 * Le fichier ou dossier local est placé DANS le dossier distant indiqué,
 * sous son propre nom. Chemins distants relatifs au dossier de connexion FTP.
 * Exemples :
 *   php deploy_upload_prod.php resources/views/club/persons/index.blade.php /resources/views/club/persons
 *   php deploy_upload_prod.php public/css /public_html
 *
 * Exclus automatiquement : .env, .git, vendor, node_modules et tout nom commençant par un point.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Accès refusé : script utilisable uniquement en ligne de commande.');
}

error_reporting(E_ALL);
set_time_limit(0);

if (!extension_loaded('curl')) {
    fwrite(STDERR, "[ERREUR] L'extension PHP 'curl' est désactivée. Activez extension=curl dans php.ini.\n");
    exit(1);
}

const TARGET_ENV = 'prod';

$root = realpath(__DIR__);

// ---------- Utilitaires ----------

function fail(string $msg): void
{
    fwrite(STDERR, "[ERREUR] $msg\n");
    exit(1);
}

function readEnv(string $path): array
{
    if (!is_file($path)) {
        fail(".env introuvable : $path");
    }

    $vars = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }

        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }

        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));

        if (strlen($val) >= 2 && ($val[0] === '"' || $val[0] === "'") && substr($val, -1) === $val[0]) {
            $val = substr($val, 1, -1);
        }
        $vars[$key] = $val;
    }

    return $vars;
}

function isExcluded(string $name): bool
{
    return $name[0] === '.' || in_array($name, ['vendor', 'node_modules'], true);
}

/** Construit l'URL FTP en encodant chaque segment du chemin distant. */
function ftpUrl(string $host, int $port, string $remotePath): string
{
    $segments = array_map('rawurlencode', array_filter(explode('/', $remotePath), 'strlen'));
    return 'ftp://' . $host . ':' . $port . '/' . implode('/', $segments);
}

/** Envoie un fichier unique. cURL crée automatiquement les dossiers distants manquants. */
function ftpPutFile(array $c, string $localFile, string $remotePath): void
{
    $fh = fopen($localFile, 'rb');
    if ($fh === false) {
        throw new RuntimeException("Lecture impossible : $localFile");
    }

    $ch = curl_init(ftpUrl($c['host'], $c['port'], $remotePath));
    curl_setopt_array($ch, [
        CURLOPT_UPLOAD                  => true,
        CURLOPT_INFILE                  => $fh,
        CURLOPT_INFILESIZE              => filesize($localFile),
        CURLOPT_USERPWD                 => $c['user'] . ':' . $c['pass'],
        CURLOPT_FTP_CREATE_MISSING_DIRS => CURLFTP_CREATE_DIR_RETRY,
        CURLOPT_FTP_USE_EPSV            => true,
        CURLOPT_CONNECTTIMEOUT          => 15,
        CURLOPT_TIMEOUT                 => 300,
    ]);

    if ($c['tls']) {
        // FTPS explicite (AUTH TLS) : échoue plutôt que de repasser en clair
        curl_setopt($ch, CURLOPT_USE_SSL, CURLUSESSL_ALL);
        if ($c['insecure']) {
            // Certificat non valide pour l'IP : vérification désactivée (FTP_PROD_TLS_INSECURE=true)
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
    }

    $ok = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    fclose($fh);

    if ($ok === false) {
        throw new RuntimeException("Échec de l'envoi de $remotePath : $err");
    }
}

function uploadPath(array $c, string $local, string $remote, int &$count): void
{
    if (is_dir($local)) {
        foreach (scandir($local) as $item) {
            if ($item === '.' || $item === '..' || isExcluded($item)) {
                continue;
            }
            uploadPath($c, $local . DIRECTORY_SEPARATOR . $item, $remote . '/' . $item, $count);
        }
        return;
    }

    ftpPutFile($c, $local, $remote);
    $count++;
    echo "[OK] $remote\n";
}

// ---------- Programme principal ----------

if ($argc !== 3) {
    fail("Usage : php deploy_upload_prod.php <fichier_ou_dossier_local> <dossier_distant>");
}

[, $localArg, $remoteDir] = $argv;

// Chemin local : relatif au projet ou absolu, mais toujours à l'intérieur du projet
$local = realpath($root . DIRECTORY_SEPARATOR . $localArg) ?: realpath($localArg);
if ($local === false || strpos($local, $root) !== 0) {
    fail("Chemin local introuvable ou hors du projet : $localArg");
}

$remoteDir = '/' . trim(str_replace('\\', '/', $remoteDir), '/');
$remote    = rtrim($remoteDir, '/') . '/' . basename($local);

$cfg = readEnv($root . DIRECTORY_SEPARATOR . '.env');
$p   = 'FTP_' . strtoupper(TARGET_ENV) . '_';

$conn = [
    'host' => $cfg[$p . 'HOST'] ?? '',
    'port' => (int) ($cfg[$p . 'PORT'] ?? 21),
    'user' => $cfg[$p . 'USERNAME'] ?? '',
    'pass' => $cfg[$p . 'PASSWORD'] ?? '',
    'tls'  => strtolower($cfg[$p . 'TLS'] ?? 'false') === 'true',
    'insecure' => strtolower($cfg[$p . 'TLS_INSECURE'] ?? 'false') === 'true',
];

if ($conn['host'] === '' || $conn['user'] === '') {
    fail("Variables {$p}HOST / {$p}USERNAME absentes du .env");
}

echo "Cible       : PRODUCTION ({$conn['host']}:{$conn['port']}, TLS=" . ($conn['tls'] ? 'oui' : 'non') . ")\n";
echo "Local       : $local\n";
echo "Distant     : $remote\n";
if ($conn['insecure']) {
    echo "ATTENTION   : vérification du certificat TLS désactivée (FTP_PROD_TLS_INSECURE=true)\n";
}
echo "\n";

echo "Vous allez déployer en PRODUCTION. Tapez OUI pour continuer : ";
$answer = trim((string) fgets(STDIN));
if ($answer !== 'OUI') {
    fail("Annulé.");
}

try {
    $count = 0;
    uploadPath($conn, $local, $remote, $count);
    echo "\nTerminé : $count fichier(s) envoyé(s).\n";
} catch (Throwable $e) {
    fail($e->getMessage());
}
