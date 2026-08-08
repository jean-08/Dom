<?php

/**
 * Helper utilitaire pour la gestion sécurisée des uploads et de la génération d'avatars libres.
 */

class UploadHelper {

    /**
     * Upload sécurisé d'une image (JPG, PNG, WEBP).
     * @return string|false Chemin relatif du fichier uploadé ou false si erreur.
     */
    public static function uploadImage(array $file, string $subFolder = 'avatars', string $prefix = 'img'): string|false {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $fileTmp  = $file['tmp_name'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validation extensions autorisées
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($fileExt, $allowedExts)) {
            return false;
        }

        // Limite taille : 5 Mo
        if ($fileSize > 5 * 1024 * 1024) {
            return false;
        }

        $uploadDir = __DIR__ . '/../public/uploads/' . trim($subFolder, '/') . '/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $uniqueName = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
        $targetFile = $uploadDir . $uniqueName;

        if (move_uploaded_file($fileTmp, $targetFile)) {
            return 'public/uploads/' . trim($subFolder, '/') . '/' . $uniqueName;
        }

        return false;
    }

    /**
     * Retourne l'URL de la photo de profil ou génère un avatar SVG libre de droits avec initiales.
     */
    public static function getAvatarUrl(?string $photoUrl, string $prenom = 'User', string $nom = ''): string {
        if (!empty($photoUrl)) {
            return $photoUrl;
        }

        $initials = strtoupper(mb_substr($prenom, 0, 1) . mb_substr($nom, 0, 1));
        if (empty($initials)) {
            $initials = 'DA';
        }

        // Palette de couleurs curated libres
        $bgColors = ['4f46e5', '0d9488', '2563eb', 'd97706', '7c3aed', '059669', 'dc2626'];
        $seed = hexdec(substr(md5($prenom . $nom), 0, 4));
        $bgColor = $bgColors[$seed % count($bgColors)];

        // SVG généré à la volée Data URI
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128">'
             . '<rect width="128" height="128" rx="64" fill="#' . $bgColor . '"/>'
             . '<text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#ffffff" font-family="sans-serif" font-size="48" font-weight="bold">' . htmlspecialchars($initials) . '</text>'
             . '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Upload sécurisé de plusieurs images (ex: $_FILES['photos']).
     * @return array Liste des chemins relatifs des fichiers uploadés avec succès.
     */
    public static function uploadMultipleImages(array $filesKey, string $subFolder = 'demandes', string $prefix = 'demande', int $maxCount = 5): array {
        $uploadedPaths = [];
        if (!isset($filesKey['name'])) {
            return $uploadedPaths;
        }

        // Si un seul fichier transmis
        if (!is_array($filesKey['name'])) {
            $path = self::uploadImage($filesKey, $subFolder, $prefix);
            if ($path) {
                $uploadedPaths[] = $path;
            }
            return $uploadedPaths;
        }

        $count = min(count($filesKey['name']), $maxCount);
        for ($i = 0; $i < $count; $i++) {
            if (!isset($filesKey['error'][$i]) || $filesKey['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $singleFile = [
                'name'     => $filesKey['name'][$i],
                'type'     => $filesKey['type'][$i] ?? '',
                'tmp_name' => $filesKey['tmp_name'][$i],
                'error'    => $filesKey['error'][$i],
                'size'     => $filesKey['size'][$i] ?? 0,
            ];

            $path = self::uploadImage($singleFile, $subFolder, $prefix);
            if ($path) {
                $uploadedPaths[] = $path;
            }
        }

        return $uploadedPaths;
    }
    /**
     * Upload sécurisé d'un fichier générique (ex. PDF pour le CV).
     *
     * @param array  $file        Tableau $_FILES d’un fichier unique.
     * @param string $subFolder   Sous‑dossier dans public/uploads/ (ex: 'cvs').
     * @param string $prefix      Préfixe du nom de fichier.
     * @param array  $allowedExts Extensions autorisées (ex: ['pdf']).
     *
     * @return string|false Chemin relatif du fichier uploadé ou false.
     */
    public static function uploadFile(
        array $file,
        string $subFolder = 'files',
        string $prefix = 'file',
        array $allowedExts = ['pdf']
    ): string|false {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $fileTmp  = $file['tmp_name'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedExts)) {
            return false;
        }

        if ($fileSize > 10 * 1024 * 1024) {
            return false;
        }

        $uploadDir = __DIR__ . '/../public/uploads/' . trim($subFolder, '/') . '/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $uniqueName = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
        $targetFile = $uploadDir . $uniqueName;

        if (move_uploaded_file($fileTmp, $targetFile) || rename($fileTmp, $targetFile) || copy($fileTmp, $targetFile)) {
            return 'public/uploads/' . trim($subFolder, '/') . '/' . $uniqueName;
        }

        return false;
    }

}
