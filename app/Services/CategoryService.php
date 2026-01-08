<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    public function getAllCategories()
{
    try {
        return Category::select('id', 'name', 'image')->whereNotNull('id_parent')
            ->get();
    } catch (\Exception $e) {
        Log::error('Error fetching categories: ' . $e->getMessage());
        return collect([]);
    }
}
}
