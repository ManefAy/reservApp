<?php
/* ══════════════════════════════════════
   CONFIGURATION EMAIL
   
   On utilise Gmail SMTP pour envoyer
   les emails. Tu peux utiliser n'importe
   quel autre serveur SMTP.
   
   ⚠️ IMPORTANT pour Gmail :
   1. Active la validation en 2 étapes
      sur ton compte Google
   2. Va sur : myaccount.google.com
      → Sécurité → Mots de passe des applications
   3. Génère un mot de passe pour "Mail"
   4. Utilise ce mot de passe ici (pas
      ton vrai mot de passe Gmail !)
══════════════════════════════════════ */

define('MAIL_HOST', 'smtp.gmail.com');  // Serveur SMTP Gmail
define('MAIL_PORT',     587);               // Port SMTP (587 = TLS)
define('MAIL_USERNAME', 'YOUR_EMAIL'); // ← Ton email Gmail
define('MAIL_PASSWORD', 'YOUR_PASSWORD'); // ← Mot de passe app
define('MAIL_FROM',     'YOUR_EXPEDITOR_EMAIL'); // Email expéditeur
define('MAIL_FROM_NAME', 'YOUR_EXPIDITOR_NAME');           // Nom expéditeur