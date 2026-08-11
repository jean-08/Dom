<?php
// services/NotificationService.php

require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../config/database.php'; // Chemin corrigé

/**
 * Service layer to centralize notification creation logic.
 * Toutes les méthodes sont silencieuses : une erreur de notification
 * ne doit JAMAIS interrompre le workflow principal.
 */
class NotificationService {
    private ?Notification $notifModel;
    private ?PDO $db;

    public function __construct() {
        try {
            $this->notifModel = new Notification();
            $this->db = Database::getInstance();
        } catch (Throwable $e) {
            $this->notifModel = null;
            $this->db = null;
        }
    }

    // Generic wrapper
    public function creer(int $id_user, string $type, string $titre, ?string $corps = null, ?string $lien = null): void {
        try {
            if ($this->notifModel) $this->notifModel->creer($id_user, $type, $titre, $corps, $lien);
        } catch (Throwable $e) { /* Silencieux : ne pas bloquer le workflow */ }
    }

    public function nouvelleProposition(int $id_client, int $id_demande, string $demande_titre, string $prestataire_nom): void {
        try {
            if ($this->notifModel) $this->notifModel->nouvelleProposition($id_client, $id_demande, $demande_titre, $prestataire_nom);
        } catch (Throwable $e) { /* Silencieux */ }
    }

    public function propositionRetenue(int $id_prestataire_user, int $id_demande, string $demande_titre): void {
        try {
            if ($this->notifModel) $this->notifModel->propositionRetenue($id_prestataire_user, $id_demande, $demande_titre);
        } catch (Throwable $e) { /* Silencieux */ }
    }

    public function engagementConfirme(int $id_client, int $id_demande, string $demande_titre, string $prestataire_nom): void {
        try {
            if ($this->notifModel) $this->notifModel->engagementConfirme($id_client, $id_demande, $demande_titre, $prestataire_nom);
        } catch (Throwable $e) { /* Silencieux */ }
    }

    /**
     * Notifie les participants d'un nouveau commentaire public.
     */
    public function nouveauCommentaire(int $demandeId, int $commenterId, string $contenu): void {
        try {
            if (!$this->db || !$this->notifModel) return;

            $stmt = $this->db->prepare('SELECT titre FROM demande WHERE id_demande = :id');
            $stmt->execute(['id' => $demandeId]);
            $demande = $stmt->fetch(PDO::FETCH_ASSOC);
            $demandeTitre = $demande['titre'] ?? 'demande';

            $stmt = $this->db->prepare(
                "SELECT DISTINCT id_user FROM (" .
                "SELECT id_user FROM demande WHERE id_demande = :id " .
                "UNION SELECT pp.id_user FROM proposition p JOIN prestataire_profile pp ON pp.id_profile = p.id_profile WHERE p.id_demande = :id " .
                "UNION SELECT id_user FROM commentaire_demande WHERE id_demande = :id" .
                ") users WHERE id_user != :commenter"
            );
            $stmt->execute(['id' => $demandeId, 'commenter' => $commenterId]);
            $destinataires = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $commentateurNom = trim(($_SESSION['user']['prenom'] ?? '') . ' ' . ($_SESSION['user']['nom'] ?? ''));
            if ($commentateurNom === '') $commentateurNom = "Quelqu'un";
            $extrait = mb_strimwidth($contenu, 0, 100, '...');

            foreach ($destinataires as $idDest) {
                $this->notifModel->creer(
                    (int)$idDest,
                    'NOUVEAU_COMMENTAIRE',
                    "Nouveau commentaire sur « $demandeTitre »",
                    "$commentateurNom a écrit : « $extrait »",
                    "index.php?action=demande_show&id=$demandeId#commentaires"
                );
            }
        } catch (Throwable $e) { /* Silencieux */ }
    }
}
?>
