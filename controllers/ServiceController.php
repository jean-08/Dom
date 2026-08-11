<?php
require_once __DIR__ . '/../models/Service.php';

class ServiceController {
    private Service $service;
    public function __construct() { $this->service = new Service(); }

    public function index(): void {
        $services = $this->service->all();
        require __DIR__ . '/../views/service/index.php';
    }

    public function create(): void {
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            header('Location: index.php?action=dashboard'); exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $d = [
                'nom'         => trim($_POST['nom'] ?? ''),
                'description' => trim($_POST['description'] ?? '')
            ];
            $this->service->create($d);
            $_SESSION['success'] = 'Service créé.';
            header('Location: index.php?action=services'); exit;
        }
        require __DIR__ . '/../views/service/index.php';
    }

    public function delete(): void {
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            header('Location: index.php?action=dashboard'); exit;
        }
        $id = (int)($_GET['id'] ?? 0);
        $this->service->delete($id);
        header('Location: index.php?action=services'); exit;
    }
}