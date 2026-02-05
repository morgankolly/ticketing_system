<?php


class CategoryModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function insertCategory(string $categoryName): bool {
        $stmt = $this->pdo->prepare("INSERT INTO Category (category_name, created_at, updated_at) VALUES (:category_name, NOW(), NOW())");
        $stmt->bindParam(':category_name', $categoryName, PDO::PARAM_STR);
        return $stmt->execute();
          
    }
    public function deleteCategory(string $categoryName): bool {
        $stmt = $this->pdo->prepare("DELETE FROM Category WHERE category_name = :category_name");
        $stmt->bindParam(':category_name', $categoryName, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function updateCategory(string $categoryName, array $data): bool {
        $stmt = $this->pdo->prepare("UPDATE Category SET category_name = :new_category_name, updated_at = NOW() WHERE category_name = :category_name");
        $stmt->bindParam(':category_name', $categoryName, PDO::PARAM_STR);
        $stmt->bindParam(':new_category_name', $data['new_category_name'], PDO::PARAM_STR);
        return $stmt->execute();
    }
    public function getAllCategories(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM Category");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
public function fetchAllCategories(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM Category");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

}

public function fetchCategory(string $categoryName): array {
        $stmt = $this->pdo->prepare("SELECT * FROM Category WHERE category_name = :category_name");
        $stmt->bindParam(':category_name', $categoryName, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}