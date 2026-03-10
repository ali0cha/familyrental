<?php
/**
 * mailer.php — Helper d'envoi d'email via Gmail SMTP (PHPMailer)
 *
 * Installation PHPMailer :
 *   composer require phpmailer/phpmailer
 *
 * Puis remplacez les trois require ci-dessous par :
 *   require 'vendor/autoload.php';
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// ─── Configuration SMTP ────────────────────────────────────────────────────────
// Pour Gmail : activez un "mot de passe d'application" dans les paramètres de sécurité
// de votre compte Google (nécessite la validation en deux étapes).
define('MAIL_HOST',       'smtp.gmail.com');
define('MAIL_PORT',       587);
define('MAIL_USERNAME',   'your.address@gmail.com'); // ← Adresse Gmail expéditrice
define('MAIL_PASSWORD',   'xxxx xxxx xxxx xxxx');    // ← Mot de passe d'application Gmail
define('MAIL_FROM_NAME',  APP_NAME . ' (no reply)');

// ──────────────────────────────────────────────────────────────────────────────

/**
 * Récupère dynamiquement les adresses email de tous les admins actifs en base.
 */
function getAdminEmails(): array
{
    global $pdo;
    $stmt = $pdo->query("SELECT email FROM users WHERE role = 'admin' AND is_active = 1 AND email IS NOT NULL AND email != ''");
    return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
}

/**
 * Fonction générique d'envoi d'email.
 *
 * @param string|array $to       Destinataire(s) : "email" ou [["email","nom"], ...]
 * @param string       $subject  Sujet du mail
 * @param string       $body     Corps HTML du mail
 * @return bool
 */
function sendMail($to, string $subject, string $body): bool
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);

        if (is_array($to)) {
            foreach ($to as $recipient) {
                if (is_array($recipient)) {
                    $mail->addAddress($recipient[0], $recipient[1] ?? '');
                } else {
                    $mail->addAddress($recipient);
                }
            }
        } else {
            $mail->addAddress($to);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = emailTemplate($subject, $body);
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur envoi email : " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Enveloppe HTML commune pour tous les emails.
 */
function emailTemplate(string $title, string $content): string
{
    $year    = date('Y');
    $appName = APP_NAME;
    return <<<HTML
    <!DOCTYPE html>
    <html lang="fr">
    <head>
      <meta charset="UTF-8">
      <style>
        body { font-family: Georgia, 'Times New Roman', serif; background: #f5f3ea; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 12px;
                   overflow: hidden; box-shadow: 0 4px 16px rgba(33,46,54,.12); }
        .header  { background: #212e36; color: #fff; padding: 24px 32px; border-bottom: 4px solid #b07d5a; }
        .header h1 { margin: 0; font-size: 20px; color: #b07d5a; }
        .body    { padding: 28px 32px; color: #212e36; line-height: 1.7; }
        .footer  { background: #212e36; text-align: center; padding: 14px;
                   font-size: 12px; color: #94a88e; }
        .btn     { display: inline-block; margin-top: 16px; padding: 11px 24px;
                   background: #b07d5a; color: #fff; border-radius: 8px;
                   text-decoration: none; font-weight: bold; letter-spacing: .3px; }
      </style>
    </head>
    <body>
      <div class="wrapper">
        <div class="header"><h1>$title</h1></div>
        <div class="body">$content</div>
        <div class="footer">© $year $appName – Usage exclusivement familial</div>
      </div>
    </body>
    </html>
    HTML;
}


// ═══════════════════════════════════════════════════════════════════════════════
//  Fonctions métier
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * 1. Nouvelle inscription → email aux admins
 */
function mailNouvelUtilisateur(string $username, string $userEmail): void
{
    $subject = "🏖️ " . APP_NAME . " - Nouvelle inscription en attente";
    $url = APP_URL . '/admin.php';
    $body = "
        <p>Bonjour,</p>
        <p>Un nouveau membre vient de s'inscrire et attend votre validation :</p>
        <ul>
          <li><strong>Pseudo :</strong> " . htmlspecialchars($username) . "</li>
          <li><strong>Email :</strong> " . htmlspecialchars($userEmail) . "</li>
        </ul>
        <p>Connectez-vous au panneau d'administration pour approuver ou ignorer ce compte.</p>
        <a class='btn' href='$url'>Accéder à l'administration</a>
    ";
    sendMail(getAdminEmails(), $subject, $body);
}

/**
 * 2. Compte validé → email à l'utilisateur
 */
function mailCompteValide(string $username, string $userEmail): void
{
    $subject = "🏖️ " . APP_NAME . " - Votre compte a été activé !";
    $url = APP_URL . '/login.php';
    $body = "
        <p>Bonjour <strong>" . htmlspecialchars($username) . "</strong>,</p>
        <p>Bonne nouvelle ! L'administrateur a validé votre compte. Vous pouvez désormais
           vous connecter et consulter le planning des réservations.</p>
        <a class='btn' href='$url'>Se connecter</a>
    ";
    sendMail($userEmail, $subject, $body);
}

/**
 * 3. Nouvelle demande de réservation → email aux admins
 */
function mailNouvelleReservation(string $username, string $startDate, string $endDate): void
{
    $subject = "🏖️ " . APP_NAME . " - Nouvelle demande de séjour";
    $start = date('d/m/Y', strtotime($startDate));
    $end   = date('d/m/Y', strtotime($endDate));
    $url = APP_URL . '/admin.php';
    $body = "
        <p>Bonjour,</p>
        <p><strong>" . htmlspecialchars($username) . "</strong> vient de soumettre une demande de séjour :</p>
        <ul>
          <li><strong>Arrivée :</strong> $start</li>
          <li><strong>Départ :</strong> $end</li>
        </ul>
        <p>Rendez-vous dans l'administration pour approuver ou refuser cette demande.</p>
        <a class='btn' href='$url'>Gérer les réservations</a>
    ";
    sendMail(getAdminEmails(), $subject, $body);
}

/**
 * 4a. Réservation approuvée → email à l'utilisateur
 */
function mailReservationApprouvee(string $username, string $userEmail, string $startDate, string $endDate): void
{
    $subject = "🏖️ " . APP_NAME . " - Votre séjour est confirmé ✅";
    $start = date('d/m/Y', strtotime($startDate));
    $end   = date('d/m/Y', strtotime($endDate));
    $url = APP_URL . '/planning.php';
    $body = "
        <p>Bonjour <strong>" . htmlspecialchars($username) . "</strong>,</p>
        <p>Votre demande de séjour a été <strong style='color:#198754'>approuvée</strong> !</p>
        <ul>
          <li><strong>Arrivée :</strong> $start</li>
          <li><strong>Départ :</strong> $end</li>
        </ul>
        <p>Profitez bien de la villa ! 🌊</p>
        <a class='btn' href='$url'>Voir le planning</a>
    ";
    sendMail($userEmail, $subject, $body);
}

/**
 * 4b. Réservation refusée → email à l'utilisateur
 */
function mailReservationRefusee(string $username, string $userEmail, string $startDate, string $endDate): void
{
    $subject = "🏖️ " . APP_NAME . " - Votre demande de séjour a été refusée";
    $start = date('d/m/Y', strtotime($startDate));
    $end   = date('d/m/Y', strtotime($endDate));
    $url = APP_URL . '/planning.php';
    $body = "
        <p>Bonjour <strong>" . htmlspecialchars($username) . "</strong>,</p>
        <p>Malheureusement, votre demande de séjour du <strong>$start</strong>
           au <strong>$end</strong> a été <strong style='color:#dc3545'>refusée</strong>.</p>
        <p>N'hésitez pas à choisir d'autres dates sur le planning.</p>
        <a class='btn' href='$url'>Consulter le planning</a>
    ";
    sendMail($userEmail, $subject, $body);
}

/**
 * 5. Réinitialisation de mot de passe → email à l'utilisateur
 */
function mailReinitialisationMotDePasse(string $username, string $userEmail, string $token): void
{
    $resetLink = APP_URL . '/reset_password.php?token=' . urlencode($token);
    $subject   = "🏖️ " . APP_NAME . " - Réinitialisation de votre mot de passe";
    $body = "
    <p>Bonjour <strong>" . htmlspecialchars($username) . "</strong>,</p>
    <p>Vous avez demandé la réinitialisation de votre mot de passe.
    Cliquez sur le bouton ci-dessous pour en choisir un nouveau :</p>
    <a class='btn' href='$resetLink'>Réinitialiser mon mot de passe</a>
    <p style='margin-top:20px; color:#888; font-size:13px;'>
    Ce lien est valable <strong>1 heure</strong>.<br>
    Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet email.
    </p>
    ";
    sendMail($userEmail, $subject, $body);
}

/**
 * 6. Réservation modifiée → email aux admins
 */
function mailReservationModifiee(string $username, string $startDate, string $endDate): void
{
    $subject = "🏖️ " . APP_NAME . " - Demande de modification de séjour";
    $start = date('d/m/Y', strtotime($startDate));
    $end   = date('d/m/Y', strtotime($endDate));
    $url = APP_URL . '/admin.php';
    $body = "
        <p>Bonjour,</p>
        <p><strong>" . htmlspecialchars($username) . "</strong> a modifié sa demande de séjour.
           Les nouvelles dates sont :</p>
        <ul>
          <li><strong>Arrivée :</strong> $start</li>
          <li><strong>Départ :</strong> $end</li>
        </ul>
        <p>La réservation est désormais <strong>en attente</strong> et nécessite votre validation.</p>
        <a class='btn' href='$url'>Gérer les réservations</a>
    ";
    sendMail(getAdminEmails(), $subject, $body);
}

/**
 * 7. Réservation annulée par l'utilisateur → email aux admins
 */
function mailReservationAnnuleeAdmin(string $username, string $startDate, string $endDate): void
{
    $subject = "🏖️ " . APP_NAME . " - Séjour annulé par un membre";
    $start = date('d/m/Y', strtotime($startDate));
    $end   = date('d/m/Y', strtotime($endDate));
    $url = APP_URL . '/admin.php';
    $body = "
        <p>Bonjour,</p>
        <p><strong>" . htmlspecialchars($username) . "</strong> a annulé son séjour du
           <strong>$start</strong> au <strong>$end</strong>.</p>
        <p>Ces dates sont à nouveau disponibles à la réservation.</p>
        <a class='btn' href='$url'>Voir le planning</a>
    ";
    sendMail(getAdminEmails(), $subject, $body);
}
