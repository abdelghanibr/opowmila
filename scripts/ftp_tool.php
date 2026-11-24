<?php
// Usage :
//   php ftp_tool.php <projet> list <dossier_distant>
//   php ftp_tool.php <projet> size <fichier_distant>
//   php ftp_tool.php <projet> put  <fichier_local_relatif> <fichier_distant>
// Lit FTP_HOST / FTP_PORT / FTP_USERNAME / FTP_PASSWORD / FTP_FTPS / FTP_IP dans <projet>/.env
[$_, $project, $mode, $a, $b] = array_pad($argv, 5, null);

$env = [];
foreach (file("$project/.env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
    if ($l === '' || $l[0] === '#' || ($p = strpos($l, '=')) === false) continue;
    $v = trim(substr($l, $p + 1));
    if (strlen($v) >= 2 && ($v[0] === '"' || $v[0] === "'") && substr($v, -1) === $v[0]) $v = substr($v, 1, -1);
    $env[trim(substr($l, 0, $p))] = $v;
}

function url(string $host, int $port, string $path): string
{
    $seg = array_map('rawurlencode', array_filter(explode('/', $path), 'strlen'));
    $joined = implode('/', $seg);
    return "ftp://$host:$port/" . $joined . ($joined !== '' && str_ends_with($path, '/') ? '/' : '');
}

function run(array $env, string $host, string $path, array $opts)
{
    $ch = curl_init(url($host, (int) ($env['FTP_PORT'] ?? 21), $path));
    curl_setopt_array($ch, $opts + [
        CURLOPT_USERPWD        => $env['FTP_USERNAME'] . ':' . $env['FTP_PASSWORD'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 20,
        CURLOPT_TIMEOUT        => 300,
        CURLOPT_FTP_USE_EPSV   => true,
    ]);
    if (strtolower($env['FTP_FTPS'] ?? 'false') === 'true') {
        // FTPS avec vérification du certificat (consigne G:\CLAUDE.md : ne jamais la désactiver)
        curl_setopt($ch, CURLOPT_USE_SSL, CURLUSESSL_ALL);
    }
    $out = curl_exec($ch);
    $err = curl_error($ch);
    $no  = curl_errno($ch);
    curl_close($ch);
    return [$out, $err, $no];
}

// IP -> nom d'hôte du certificat (voir G:\CLAUDE.md, « Serveurs connus »)
const CERT_HOSTS = [
    '197.140.7.31'  => 'orion-shared.dzsecurity.net',
    '157.90.66.113' => 'oort-shared.dzsecurity.net',
];

function attempt(array $env, string $path, array $opts)
{
    $ip = $env['FTP_IP'] ?? '';
    $hosts = array_unique(array_filter([
        CERT_HOSTS[$ip] ?? '',      // nom du certificat d'abord, quand l'IP est connue
        $env['FTP_HOST'] ?? '',
        strtolower($env['FTP_FTPS'] ?? 'false') === 'true' ? '' : $ip, // IP seule seulement sans TLS
    ]));
    foreach ($hosts as $host) {
        [$out, $err, $no] = run($env, $host, $path, $opts);
        if ($no === 0) return $out;
        fwrite(STDERR, "[$host] $err\n");
        if ($no !== CURLE_COULDNT_RESOLVE_HOST && $no !== CURLE_COULDNT_CONNECT && $no !== CURLE_OPERATION_TIMEDOUT) break;
    }
    exit(1);
}

if ($mode === 'list') {
    echo attempt($env, rtrim($a, '/') . '/', [CURLOPT_DIRLISTONLY => true]);
} elseif ($mode === 'size') {
    attempt($env, $a, [CURLOPT_NOBODY => true, CURLOPT_FILETIME => true]);
    echo "exists: $a\n";
} elseif ($mode === 'same') {
    // Compare le fichier distant avec le fichier local (normalise les fins de ligne)
    $remote = attempt($env, $a, []);
    $local  = file_get_contents("$project/$a");
    $norm   = fn ($s) => str_replace("\r\n", "\n", $s);
    echo $norm($remote) === $norm($local) ? "IDENTIQUE $a\n" : "DIFFERENT $a\n";
} elseif ($mode === 'put') {
    $local = realpath("$project/$a") or exit("local introuvable\n");
    $fh = fopen($local, 'rb');
    attempt($env, $b, [CURLOPT_UPLOAD => true, CURLOPT_INFILE => $fh, CURLOPT_INFILESIZE => filesize($local)]);
    echo "[OK] $b\n";
}
