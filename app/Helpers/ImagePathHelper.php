<?php

namespace App\Helpers;

class ImagePathHelper
{
    /**
     * Standardize image path to /storage/products/filename format
     * Handles various input formats and converts them all to the standard format
     */
    public static function standardize(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = trim($path);

        // If already in correct format, return as is
        if (strpos($path, '/storage/products/') === 0) {
            return $path;
        }

        // Remove duplicate /storage segments
        $path = preg_replace('#/storage/+storage/#', '/storage/', $path);
        $path = preg_replace('#/storage/+#', '/storage/', $path);

        // If path starts with /storage/ but not /storage/products/, fix it
        if (strpos($path, '/storage/') === 0) {
            // Extract filename or subfolder
            $relativePath = substr($path, strlen('/storage/'));
            // If it doesn't start with 'products/', add it
            if (strpos($relativePath, 'products/') !== 0) {
                $path = '/storage/products/' . $relativePath;
            }
            return $path;
        }

        // If path is relative (e.g., 'products/file.jpg'), add /storage/ prefix
        if (strpos($path, 'products/') === 0) {
            return '/storage/' . $path;
        }

        // If path is just filename, assume it's in products folder
        if (strpos($path, '/') === false) {
            return '/storage/products/' . $path;
        }

        // For storage/products/ format (without leading slash)
        if (strpos($path, 'storage/products/') === 0) {
            return '/' . $path;
        }

        // If we have something like 'storage/something', convert it
        if (strpos($path, 'storage/') === 0) {
            $relativePath = substr($path, strlen('storage/'));
            if (strpos($relativePath, 'products/') !== 0) {
                return '/storage/products/' . $relativePath;
            }
            return '/storage/' . $relativePath;
        }

        // Default: assume it's a products folder item
        return '/storage/products/' . $path;
    }

    /**
     * Get the relative path for storing in database
     * Converts /storage/products/filename to products/filename
     */
    public static function getRelativePath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = self::standardize($path);

        // Remove /storage/ prefix
        if (strpos($path, '/storage/') === 0) {
            return substr($path, strlen('/storage/'));
        }

        return $path;
    }

    /**
     * Format path for display in HTML img src attributes
     * Should return /storage/products/filename format
     */
    public static function formatForDisplay(?string $path): ?string
    {
        return self::standardize($path);
    }
}
