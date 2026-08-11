<?php
require_once __DIR__ . '/../models/Service.php';

class HomeController {
    public function index(): void {
        $serviceModel = new Service();
        $categories   = $serviceModel->all();
        require __DIR__ . '/../views/home.php';
    }
}
