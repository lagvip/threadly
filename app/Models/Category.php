<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'id_parent',
    ];
    public function children()
    {
        return $this->hasMany(Category::class, 'id_parent', 'id');
    }

    // Quan hệ một-một (ngược lại): Một category con có một category cha
    public function parent()
    {
        return $this->belongsTo(Category::class, 'id_parent', 'id');
    }
    public function products()
    {
        return $this->hasMany(Product::class, 'id_category');
    }
}
