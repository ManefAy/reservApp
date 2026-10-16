<?php
/* ══════════════════════════════════════
   MAIL MODEL
   
   Gère l'envoi d'emails avec PHPMailer.
   Méthode principale : envoyerConfirmation()
   qui envoie l'email de réservation
   avec le QR Code en pièce jointe.
══════════════════════════════════════ */

require_once __DIR__ . '/../libs/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../libs/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../libs/phpmailer/src/Exception.php';
require_once __DIR__ . '/../config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;

class MailModel {

    /**
     * Envoie un email de confirmation de réservation
     * avec le QR Code en pièce jointe
     *
     * @param array  $resa      Données de la réservation
     * @param string $emailDest Email du destinataire
     * @param string $nomDest   Nom du destinataire
     * @param string $qrPath    Chemin vers le fichier QR Code PNG
     * @return bool  true si envoyé, false sinon
     */
    public static function envoyerConfirmation(
        array  $resa,
        string $emailDest,
        string $nomDest,
        string $qrPath
    ): bool {

        // Crée une nouvelle instance PHPMailer
        $mail = new PHPMailer(true); // true = active les exceptions

        try {
            /* ── Configuration SMTP ──────────────
               On utilise le serveur SMTP Gmail
               avec chiffrement TLS (port 587)
            */
            $mail->isSMTP();                        // Utilise SMTP
            $mail->Host       = MAIL_HOST;          // Serveur Gmail
            $mail->SMTPAuth   = true;               // Authentification
            $mail->Username   = MAIL_USERNAME;      // Email Gmail
            $mail->Password   = MAIL_PASSWORD;      // Mot de passe app
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
            $mail->Port       = MAIL_PORT;          // Port 587
            $mail->CharSet    = 'UTF-8';            // Caractères français

            /* ── Expéditeur et destinataire ──────
            */
            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $mail->addAddress($emailDest, $nomDest); // Destinataire

            /* ── Pièce jointe QR Code ────────────
               On attache le fichier QR Code PNG
               généré lors de la réservation
            */
            if (file_exists($qrPath)) {
                $mail->addAttachment(
                    $qrPath,                          // Chemin du fichier
                    'qr_reservation_' . $resa['id'] . '.png' // Nom dans l'email
                );
            }

            /* ── Sujet de l'email ────────────────
            */
            $mail->Subject = '✅ Confirmation de réservation #' . $resa['id'] . ' — ReservApp';

            /* ── Corps de l'email en HTML ─────────
               On génère un beau template HTML
            */
            $mail->isHTML(true);
            $mail->Body    = self::templateHtml($resa, $nomDest);

            /* ── Version texte (fallback) ─────────
               Pour les clients email qui
               n'affichent pas le HTML
            */
            $mail->AltBody = self::templateTexte($resa, $nomDest);

            // Envoie l'email
            $mail->send();
            return true;

        } catch (\Exception $e) {
            // Log l'erreur sans bloquer l'application
            error_log('Erreur envoi email : ' . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Envoie un email avec le lien de réinitialisation du mot de passe
     */
    public static function envoyerReinitialisation(
        string $emailDest,
        string $nomDest,
        string $token
    ): bool {
        $mail = new PHPMailer(true);

        $base = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
              . '://' . $_SERVER['HTTP_HOST']
              . rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $lien = $base . '/index.php?page=reset_password&token=' . $token;

        try {
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $mail->addAddress($emailDest, $nomDest);

            $mail->Subject = '🔐 Réinitialisation de mot de passe — ReservApp';
            $mail->isHTML(true);
            $mail->Body    = self::templateReinitialisation($nomDest, $lien);
            $mail->AltBody = "Bonjour $nomDest,\n\n"
                           . "Cliquez sur ce lien pour réinitialiser votre mot de passe :\n$lien\n\n"
                           . "Ce lien expire dans 1 heure.\n\n"
                           . "Si vous n'avez pas fait cette demande, ignorez cet email.\n\n"
                           . "© " . date('Y') . " ReservApp";

            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log('Erreur envoi email reset : ' . $mail->ErrorInfo);
            return false;
        }
    }

    private static function templateReinitialisation(string $nom, string $lien): string {
        $nomEsc  = htmlspecialchars($nom);
        $lienEsc = htmlspecialchars($lien);
        $annee   = date('Y');
        return "
        <!DOCTYPE html>
        <html lang='fr'>
        <body style='margin:0;padding:0;background:#f0f2f5;font-family:Arial,sans-serif;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background:#f0f2f5;padding:30px 0;'>
                <tr><td align='center'>
                    <table width='600' cellpadding='0' cellspacing='0'
                           style='background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.1);'>
                        <tr>
                            <td style='background:#2c3e50;padding:30px;text-align:center;color:white;'>
                                <h1 style='margin:0;font-size:1.5rem;'>🔐 ReservApp</h1>
                                <p style='margin:8px 0 0;opacity:0.8;'>Réinitialisation de mot de passe</p>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding:35px;'>
                                <p style='color:#333;'>Bonjour <strong>$nomEsc</strong>,</p>
                                <p style='color:#555;'>
                                    Vous avez demandé la réinitialisation de votre mot de passe.
                                    Cliquez sur le bouton ci-dessous :
                                </p>
                                <div style='text-align:center;margin:30px 0;'>
                                    <a href='$lienEsc'
                                       style='background:#2c3e50;color:white;padding:14px 30px;
                                              border-radius:6px;text-decoration:none;
                                              font-weight:bold;font-size:1rem;'>
                                        Réinitialiser mon mot de passe
                                    </a>
                                </div>
                                <p style='color:#888;font-size:0.85rem;'>
                                    Ce lien expire dans <strong>1 heure</strong>.
                                    Si vous n'avez pas fait cette demande, ignorez cet email.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style='background:#f8f9fa;padding:20px;text-align:center;
                                       color:#888;font-size:0.8rem;border-top:1px solid #eee;'>
                                © $annee ReservApp — Email automatique
                            </td>
                        </tr>
                    </table>
                </td></tr>
            </table>
        </body>
        </html>";
    }

    /**
     * Template HTML de l'email de confirmation
     * Design simple et lisible sur mobile
     */
    private static function templateHtml(array $resa, string $nomDest): string {
        $date    = date('d/m/Y', strtotime($resa['date_reservation']));
        $creneau = htmlspecialchars($resa['creneau_libelle']);
        $salle   = htmlspecialchars($resa['salle_nom']);
        $id      = $resa['id'];

        return "
        <!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='margin:0; padding:0; background:#f0f2f5; font-family:Arial,sans-serif;'>

            <!-- Conteneur principal -->
            <table width='100%' cellpadding='0' cellspacing='0'
                   style='background:#f0f2f5; padding:30px 0;'>
                <tr>
                    <td align='center'>
                        <table width='600' cellpadding='0' cellspacing='0'
                               style='background:#ffffff; border-radius:10px;
                                      overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1);'>

                            <!-- En-tête -->
                            <tr>
                                <td style='background:#2c3e50; padding:30px;
                                           text-align:center; color:white;'>
                                    <h1 style='margin:0; font-size:1.5rem;'>
                                        📅 ReservApp
                                    </h1>
                                    <p style='margin:8px 0 0; opacity:0.8;'>
                                        Confirmation de réservation
                                    </p>
                                </td>
                            </tr>

                            <!-- Corps -->
                            <tr>
                                <td style='padding:35px;'>

                                    <!-- Salutation -->
                                    <p style='font-size:1rem; color:#333; margin-bottom:20px;'>
                                        Bonjour <strong>" . htmlspecialchars($nomDest) . "</strong>,
                                    </p>
                                    <p style='color:#555; margin-bottom:25px;'>
                                        Votre réservation a été <strong style='color:#27ae60;'>
                                        confirmée</strong> avec succès. Voici les détails :
                                    </p>

                                    <!-- Carte infos réservation -->
                                    <table width='100%' cellpadding='0' cellspacing='0'
                                           style='background:#f8f9fa; border-radius:8px;
                                                  border-left:4px solid #2c3e50;
                                                  margin-bottom:25px;'>
                                        <tr>
                                            <td style='padding:20px;'>
                                                <table width='100%'>
                                                    <tr>
                                                        <td style='padding:8px 0; color:#888;
                                                                   font-size:0.85rem; width:130px;'>
                                                            🔖 N° Réservation
                                                        </td>
                                                        <td style='padding:8px 0; font-weight:bold;
                                                                   color:#2c3e50;'>
                                                            #$id
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style='padding:8px 0; color:#888;
                                                                   font-size:0.85rem;'>
                                                            🏢 Salle
                                                        </td>
                                                        <td style='padding:8px 0; font-weight:bold;
                                                                   color:#333;'>
                                                            $salle
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style='padding:8px 0; color:#888;
                                                                   font-size:0.85rem;'>
                                                            📅 Date
                                                        </td>
                                                        <td style='padding:8px 0; font-weight:bold;
                                                                   color:#333;'>
                                                            $date
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style='padding:8px 0; color:#888;
                                                                   font-size:0.85rem;'>
                                                            🕐 Créneau
                                                        </td>
                                                        <td style='padding:8px 0; font-weight:bold;
                                                                   color:#333;'>
                                                            $creneau
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style='padding:8px 0; color:#888;
                                                                   font-size:0.85rem;'>
                                                            ✅ Statut
                                                        </td>
                                                        <td style='padding:8px 0;'>
                                                            <span style='background:#d1e7dd;
                                                                         color:#0f5132;
                                                                         padding:3px 10px;
                                                                         border-radius:12px;
                                                                         font-size:0.85rem;
                                                                         font-weight:bold;'>
                                                                ✅ Confirmée
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- Info QR Code -->
                                    <p style='background:#fff3cd; border-left:4px solid #ffc107;
                                              padding:12px 15px; border-radius:5px;
                                              color:#856404; font-size:0.9rem; margin-bottom:25px;'>
                                        📎 <strong>QR Code en pièce jointe</strong> — 
                                        Présentez ce QR Code à l'entrée de la salle.
                                    </p>

                                    <!-- Message final -->
                                    <p style='color:#555; font-size:0.9rem;'>
                                        Vous pouvez annuler votre réservation jusqu'à
                                        <strong>24h avant</strong> la date prévue
                                        depuis votre espace personnel.
                                    </p>

                                </td>
                            </tr>

                            <!-- Pied de page -->
                            <tr>
                                <td style='background:#f8f9fa; padding:20px;
                                           text-align:center; color:#888;
                                           font-size:0.8rem; border-top:1px solid #eee;'>
                                    <p style='margin:0;'>
                                        © " . date('Y') . " ReservApp — 
                                        Cet email a été envoyé automatiquement.
                                    </p>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";
    }

    /**
     * Version texte de l'email (fallback)
     * Pour les clients qui n'affichent pas le HTML
     */
    private static function templateTexte(array $resa, string $nomDest): string {
        return "
Bonjour " . $nomDest . ",

Votre réservation a été confirmée !

=== DÉTAILS ===
N° Réservation : #" . $resa['id'] . "
Salle          : " . $resa['salle_nom'] . "
Date           : " . date('d/m/Y', strtotime($resa['date_reservation'])) . "
Créneau        : " . $resa['creneau_libelle'] . "
Statut         : Confirmée

Le QR Code est en pièce jointe.
Présentez-le à l'entrée de la salle.

© " . date('Y') . " ReservApp
        ";
    }
}