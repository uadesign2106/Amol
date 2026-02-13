<?php
require_once __DIR__ . '/../helpers/common.php';

function getCategories(bool $activeOnly = true): array
{
    $sql = 'SELECT * FROM categories';
    if ($activeOnly) {
        $sql .= ' WHERE status = 1';
    }
    return db()->query($sql . ' ORDER BY category_name')->fetchAll();
}

function getIndustries(bool $activeOnly = true): array
{
    $sql = 'SELECT * FROM industries';
    if ($activeOnly) {
        $sql .= ' WHERE status = 1';
    }
    return db()->query($sql . ' ORDER BY industry_name')->fetchAll();
}

function getProducts(array $filters = []): array
{
    $params = [];
    $where = ['p.status = 1'];

    if (!empty($filters['category_id'])) {
        $where[] = 'p.category_id = :category_id';
        $params[':category_id'] = $filters['category_id'];
    }
    if (!empty($filters['industry_id'])) {
        $where[] = 'pi.industry_id = :industry_id';
        $params[':industry_id'] = $filters['industry_id'];
    }

    $sql = 'SELECT DISTINCT p.*, c.category_name,
        GROUP_CONCAT(i.industry_name ORDER BY i.industry_name SEPARATOR ",") as industries
        FROM products p
        JOIN categories c ON c.id = p.category_id
        LEFT JOIN product_industries pi ON pi.product_id = p.id
        LEFT JOIN industries i ON i.id = pi.industry_id
        WHERE ' . implode(' AND ', $where) . '
        GROUP BY p.id ORDER BY p.created_at DESC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function getProductById(int $id): ?array
{
    $stmt = db()->prepare('SELECT p.*, c.category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch();
    if (!$product) {
        return null;
    }

    $inds = db()->prepare('SELECT i.id, i.industry_name FROM industries i JOIN product_industries pi ON pi.industry_id = i.id WHERE pi.product_id = :id');
    $inds->execute([':id' => $id]);
    $product['industry_ids'] = array_column($inds->fetchAll(), 'id');
    return $product;
}
