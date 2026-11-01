# Upload en production (FTP)

Envoi de fichiers vers le serveur de production avec `deploy_upload_prod.php`.

## Commande

À lancer **depuis PowerShell**, à la racine du projet (`G:\opowmila43`) :

```powershell
php deploy_upload_prod.php --yes <fichier_ou_dossier_local> <dossier_distant>
```

Le fichier (ou dossier) local est placé **dans** le dossier distant, sous son propre nom.
Le dossier distant est relatif au dossier de connexion FTP et commence par `/`.

- Sans `--yes`, le script demande de taper `OUI` avant l'envoi.
- Exclus automatiquement : `.env`, `.git`, `vendor`, `node_modules` et tout nom commençant par un point.

## Exemples

```powershell
# Une vue
php deploy_upload_prod.php --yes resources/views/club/persons/index.blade.php /resources/views/club/persons

# Un contrôleur
php deploy_upload_prod.php --yes app/Http/Controllers/PlayerCardController.php /app/Http/Controllers

# Un dossier entier
php deploy_upload_prod.php --yes public/css /public_html
```

Résultat attendu :

```
Cible       : PRODUCTION (orion-shared.dzsecurity.net:21, TLS=oui)
...
[OK] /resources/views/club/persons/index.blade.php
Terminé : 1 fichier(s) envoyé(s).
```

## Configuration (.env)

```
FTP_PROD_HOST=orion-shared.dzsecurity.net
FTP_PROD_PORT=21
FTP_PROD_USERNAME=...
FTP_PROD_PASSWORD=...
FTP_PROD_TLS=true
```

## Pièges connus

### Ne pas utiliser Git Bash

Git Bash (MSYS) réécrit les arguments qui commencent par `/` en chemins Windows :
`/resources/views/club/persons` devient `/C:/laragon/bin/git/resources/views/club/persons`,
et le fichier part dans un mauvais dossier sur le serveur. Toujours lancer depuis **PowerShell**
(ou cmd).

### Erreur « SSL: no alternative certificate subject name matches target ipv4 address »

Le certificat TLS du serveur est émis pour `orion-shared.dzsecurity.net`, pas pour l'IP
`197.140.7.31`. Il faut donc mettre le **nom d'hôte** dans `FTP_PROD_HOST`, pas l'IP.

Ne pas utiliser `FTP_PROD_TLS_INSECURE=true` (désactive la vérification du certificat) :
le nom d'hôte suffit et garde la connexion vérifiée.

Pour vérifier le nom présent sur le certificat :

```bash
echo | openssl s_client -starttls ftp -connect 197.140.7.31:21 2>/dev/null | openssl x509 -noout -subject -ext subjectAltName
```

### La page n'a pas changé après l'upload

Vider le cache des vues sur le serveur : `php artisan view:clear`.

## À ne pas envoyer en prod

- `deploy_upload*.php`, `scripts/` : outils locaux.
- `public/zz_*.php` : fichiers de test, accessibles publiquement s'ils sont dans `public/`.
