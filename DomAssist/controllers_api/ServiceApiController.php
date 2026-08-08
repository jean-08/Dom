<?php
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/ApiAuth.php';
require_once __DIR__ . '/ApiRequest.php';

/**
 * Catalogue des services (catégories d'assistance).
 * Liste / détail : publics (formulaire de demande, annuaire).
 * Création / modification / suppression : admin uniquement.
 */
class ServiceApiController
{
    private Service $service;

    public function __construct()
    {
        $this->service = new Service();
    }

    /** GET ?action=service_list */
    public function list(): void
    {
        $rows = $this->service->all();
        ApiResponse::success([
            'services' => array_map([$this, 'format'], $rows),
        ]);
    }

    /** GET ?action=service_show&id=N */
    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            ApiResponse::error('Paramètre id requis.', 422);
        }

        $row = $this->service->find($id);
        if (!$row) {
            ApiResponse::error('Service introuvable.', 404);
        }

        ApiResponse::success(['service' => $this->format($row)]);
    }

    /** POST ?action=service_create  { nom, description? } — admin */
    public function create(): void
    {
        ApiAuth::requireAdmin();

        $d = ApiRequest::body();
        $nom = trim($d['nom'] ?? '');
        $description = trim($d['description'] ?? '');

        if ($nom === '') {
            ApiResponse::error('Le nom du service est requis.', 422);
        }

        $this->service->create([
            'nom'         => $nom,
            'description' => $description !== '' ? $description : null,
        ]);

        ApiResponse::success([
            'message'    => 'Service créé.',
            'id_service' => $this->service->lastId(),
        ], 201);
    }

    /** POST ?action=service_update  { id_service, nom, description? } — admin */
    public function update(): void
    {
        ApiAuth::requireAdmin();

        $d = ApiRequest::body();
        $id = (int) ($d['id_service'] ?? 0);
        $nom = trim($d['nom'] ?? '');
        $description = trim($d['description'] ?? '');

        if ($id <= 0 || $nom === '') {
            ApiResponse::error('id_service et nom sont requis.', 422);
        }

        if (!$this->service->find($id)) {
            ApiResponse::error('Service introuvable.', 404);
        }

        $this->service->update($id, [
            'nom'         => $nom,
            'description' => $description !== '' ? $description : null,
        ]);

        ApiResponse::success(['message' => 'Service mis à jour.']);
    }

    /** POST ?action=service_delete  { id_service } — admin */
    public function delete(): void
    {
        ApiAuth::requireAdmin();

        $id = (int) (ApiRequest::body()['id_service'] ?? 0);
        if ($id <= 0) {
            ApiResponse::error('id_service requis.', 422);
        }

        if (!$this->service->find($id)) {
            ApiResponse::error('Service introuvable.', 404);
        }

        // ON DELETE CASCADE côté avoir_une_competence / éventuels liens
        $this->service->delete($id);
        ApiResponse::success(['message' => 'Service supprimé.']);
    }

    private function format(array $s): array
    {
        return [
            'id_service'  => (int) ($s['id_service'] ?? 0),
            'nom'         => $s['nom'] ?? null,
            'description' => $s['description'] ?? null,
            // niveau présent uniquement via byPrestataire
            'niveau'      => $s['niveau'] ?? null,
        ];
    }
}