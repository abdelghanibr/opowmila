# Astuces

## Upload en prod

Depuis **PowerShell** (pas Git Bash), à la racine du projet :

```powershell
php deploy_upload_prod.php --yes resources/views/club/persons/index.blade.php /resources/views/club/persons
```

- 2e argument = dossier distant **qui contient** le fichier (commence par `/`).
- Git Bash transforme `/resources/...` en `/C:/laragon/bin/git/resources/...` : mauvais dossier.
- `.env` : `FTP_PROD_HOST=orion-shared.dzsecurity.net` (le nom d'hôte, pas l'IP 197.140.7.31,
  sinon erreur SSL « no alternative certificate subject name matches »).
- Page inchangée après upload : `php artisan view:clear` sur le serveur.

Détails complets : [upload.md](upload.md).
