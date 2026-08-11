<?php

class ApiRequest {
    /** Lit le corps JSON de la requête et le retourne en tableau associatif. */
    public static function body(): array {
        $raw = file_get_contents('php://input');
        if ($raw === '' || $raw === false) {
            return $_POST; // fallback si jamais un client envoie du form-urlencoded
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}