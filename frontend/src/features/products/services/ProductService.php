<?php
require_once __DIR__ . '/../../../services/MockData.php';

class ProductService
{
    public static function getAll(): array {
        return getProducts();
    }

    public static function getFeatured(): array {
        return array_values(array_filter(getProducts(), fn($p) => $p['is_featured'] ?? false));
    }

    public static function getById(int $id): ?array {
        foreach (getProducts() as $p) {
            if ($p['id'] === $id) return $p;
        }
        return null;
    }

    public static function getRelated(int $categoryId, int $excludeId, int $limit = 4): array {
        $related = array_filter(getProducts(), function ($p) use ($categoryId, $excludeId) {
            return $p['category_id'] === $categoryId && $p['id'] !== $excludeId;
        });
        return array_slice(array_values($related), 0, $limit);
    }

    public static function getByCategory(string $slug): array {
        $cat = null;
        foreach (getCategories() as $c) {
            if ($c['slug'] === $slug) { $cat = $c; break; }
        }
        if (!$cat) return getProducts();
        return array_values(array_filter(getProducts(), fn($p) => $p['category_id'] === $cat['id']));
    }

    public static function search(string $q): array {
        if ($q === '') return getProducts();
        $q = mb_strtolower($q, 'UTF-8');
        return array_values(array_filter(getProducts(), function ($p) use ($q) {
            return mb_strpos(mb_strtolower($p['name'], 'UTF-8'), $q) !== false;
        }));
    }
}