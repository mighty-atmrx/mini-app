<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository
{
    public function getCategoryByTitle(string $title)
    {
        return Category::where('title', $title)->first();
    }

    public function getCategoryBySubtitle(string $subtitle)
    {
        return Category::where('subtitle', $subtitle)->first();
    }

    public function getCategoryById(int $categoryId)
    {
        return Category::find($categoryId);
    }

    public function create(array $data)
    {
        return Category::create($data);
    }

    public function delete(int $categoryId)
    {
        $category = $this->getCategoryById($categoryId);
        return $category->delete();
    }
}
