<?php 
require_once __DIR__ . '/../models/Model.php';

class OtpModel extends Model {
    /** generer et sauvgarder code OTP */
    public function creer(int $userId) : string {
        /** generer le code à 6 chiffres */
        $code = str_pad(random_int(0,999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));  // 5 minutes

        /** supprimer les anciens codes OTP pour cet utilisateur */
        $stmt = $this->db->prepare("DELETE FROM otp_codes WHERE utilisateurs_id = ?");
        $stmt->execute([$userId]);

        /** sauvegarder le code OTP */
        $stmt = $this->db->prepare("INSERT INTO otp_codes (utilisateurs_id, code, expires_at,used) VALUES (?,?,?,0)");
        $stmt->execute([$userId, $code, $expiresAt]);
        return $code;
    }

    /** verifier le code OTP */
    public function verifier(int $userId, string $code): bool {
    $stmt = $this->db->prepare("SELECT * FROM otp_codes
        WHERE utilisateurs_id = ?
            AND code = ?
            AND used = 0
            AND expires_at > NOW()
    ");
    $stmt->execute([$userId, trim($code)]);
    $otp = $stmt->fetch();

    if (!$otp) return false;

    $stmt = $this->db->prepare("UPDATE otp_codes SET used = 1 WHERE id = ?");
    $stmt->execute([$otp['id']]);

    return true;
}

}
