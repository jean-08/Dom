<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Notifications in-app.
 * Les notifications sont créées par les contrôleurs/modèles métier (jamais par l'utilisateur directement).
 */
class Notification {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    /** Toutes les notifications d'un utilisateur (non lues en premier). */
    public function byUser(int $id_user, int $limit = 50): array {
        $s = $this->db->prepare("
            SELECT * FROM notification
            WHERE id_user = ?
            ORDER BY lu ASC, created_at DESC
            LIMIT ?
        ");
        $s->execute([$id_user, $limit]);
        return $s->fetchAll();
    }

    /** Nombre de notifications non lues. */
    public function countUnread(int $id_user): int {
        $s = $this->db->prepare("SELECT COUNT(*) AS nb FROM notification WHERE id_user = ? AND lu = false");
        $s->execute([$id_user]);
        return (int)$s->fetch()['nb'];
    }

    /** Marquer une notification comme lue. */
    public function marquerLue(int $id_notification, int $id_user): bool {
        $s = $this->db->prepare("UPDATE notification SET lu = true WHERE id_notification = ? AND id_user = ?");
        return $s->execute([$id_notification, $id_user]);
    }

    /** Marquer toutes les notifications d'un utilisateur comme lues. */
    public function marquerToutesLues(int $id_user): bool {
        $s = $this->db->prepare("UPDATE notification SET lu = true WHERE id_user = ? AND lu = false");
        return $s->execute([$id_user]);
    }

    /**
     * Créer une notification pour un utilisateur.
     * @param string $type  Constante type (ex: 'NOUVELLE_PROPOSITION', 'MESSAGE_RECU', …)
     */
    public function creer(int $id_user, string $type, string $titre, ?string $corps = null, ?string $lien = null): bool {
        // Ne pas notifier ses propres actions (règle métier P2)
        $s = $this->db->prepare("
            INSERT INTO notification (id_user, type, titre, corps, lien_ressource)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $s->execute([$id_user, $type, $titre, $corps, $lien]);
    }

    // -----------------------------------------------------------------------
    // Méthodes de création sémantiques (façade)
    // -----------------------------------------------------------------------

    public function nouvelleProposition(int $id_client, int $id_demande, string $demande_titre, string $prestataire_nom): void {
        $this->creer($id_client, 'NOUVELLE_PROPOSITION',
            "Nouvelle proposition reçue",
            "$prestataire_nom a envoyé une proposition pour « $demande_titre »",
            "index.php?action=demande_show&id=$id_demande#propositions"
        );
    }

    public function propositionRetenue(int $id_prestataire_user, int $id_demande, string $demande_titre): void {
        $this->creer($id_prestataire_user, 'PROPOSITION_RETENUE',
            "Votre proposition a été retenue ! 🎉",
            "Le client vous a sélectionné pour « $demande_titre ». Confirmez votre engagement.",
            "index.php?action=demande_show&id=$id_demande"
        );
    }

    public function propositionNonRetenue(int $id_prestataire_user, int $id_demande, string $demande_titre): void {
        $this->creer($id_prestataire_user, 'PROPOSITION_NON_RETENUE',
            "Proposition non retenue",
            "Le client a choisi un autre prestataire pour « $demande_titre ».",
            "index.php?action=demandes_eligibles"
        );
    }

    public function engagementConfirme(int $id_client, int $id_demande, string $demande_titre, string $prestataire_nom): void {
        $this->creer($id_client, 'ENGAGEMENT_CONFIRME',
            "Prestataire engagé",
            "$prestataire_nom a confirmé son engagement sur « $demande_titre ».",
            "index.php?action=demande_show&id=$id_demande"
        );
    }

    public function diagnosticPublie(int $id_client, int $id_demande, string $demande_titre): void {
        $this->creer($id_client, 'DIAGNOSTIC_PUBLIE',
            "Diagnostic disponible",
            "Un diagnostic a été publié pour votre demande « $demande_titre ».",
            "index.php?action=demande_show&id=$id_demande#diagnostic"
        );
    }

    public function solutionProposee(int $id_client, int $id_demande, string $demande_titre): void {
        $this->creer($id_client, 'SOLUTION_PROPOSEE',
            "Solution à valider",
            "Une solution a été proposée pour « $demande_titre ». Veuillez la valider ou la refuser.",
            "index.php?action=demande_show&id=$id_demande#solution"
        );
    }

    public function solutionValidee(int $id_prestataire_user, int $id_demande, string $demande_titre): void {
        $this->creer($id_prestataire_user, 'SOLUTION_VALIDEE',
            "Solution validée ✅",
            "Le client a validé votre solution pour « $demande_titre ». Vous pouvez démarrer l'intervention.",
            "index.php?action=demande_show&id=$id_demande"
        );
    }

    public function solutionRefusee(int $id_prestataire_user, int $id_demande, string $demande_titre): void {
        $this->creer($id_prestataire_user, 'SOLUTION_REFUSEE',
            "Solution refusée",
            "Le client a refusé votre solution pour « $demande_titre ». Proposez une nouvelle solution.",
            "index.php?action=demande_show&id=$id_demande"
        );
    }

    public function interventionTerminee(int $id_client, int $id_demande, string $demande_titre): void {
        $this->creer($id_client, 'INTERVENTION_TERMINEE',
            "Intervention terminée",
            "L'intervention pour « $demande_titre » est terminée. N'oubliez pas de laisser un avis !",
            "index.php?action=demande_show&id=$id_demande#avis"
        );
    }

    public function candidatureValidee(int $id_user): void {
        $this->creer($id_user, 'CANDIDATURE_VALIDEE',
            "Candidature validée 🎉",
            "Votre profil prestataire a été validé. Vous pouvez maintenant proposer vos services !",
            "index.php?action=dashboard"
        );
    }

    public function candidatureRejetee(int $id_user, string $motif): void {
        $this->creer($id_user, 'CANDIDATURE_REJETEE',
            "Candidature rejetée",
            "Votre candidature n'a pas été retenue. Motif : $motif. Vous pouvez resoumettre un dossier amélioré.",
            "index.php?action=prestataire_candidater"
        );
    }

    public function compteSuspendu(int $id_user, string $motif): void {
        $this->creer($id_user, 'COMPTE_SUSPENDU',
            "Votre compte a été suspendu",
            "Motif : $motif. Contactez le support pour plus d'informations.",
            "index.php?action=compte_suspendu"
        );
    }

    public function compteReactive(int $id_user): void {
        $this->creer($id_user, 'COMPTE_REACTIVE',
            "Votre compte a été réactivé",
            "Votre accès à DomAssist a été rétabli.",
            "index.php?action=dashboard"
        );
    }

    // Admin
    public function nouvelleCandidatureAdmin(int $id_admin, string $prenom, string $nom): void {
        $this->creer($id_admin, 'NOUVELLE_CANDIDATURE',
            "Nouvelle candidature prestataire",
            "$prenom $nom a soumis une candidature prestataire.",
            "index.php?action=admin_prestataires_en_attente"
        );
    }

    public function nouveauSignalementAdmin(int $id_admin, string $motif): void {
        $this->creer($id_admin, 'NOUVEAU_SIGNALEMENT',
            "Nouveau signalement",
            "Un signalement de type « $motif » vient d'être créé.",
            "index.php?action=admin_signalements"
        );
    }
}
