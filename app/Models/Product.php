<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;


    protected $fillable = [
        'name',
        'id_category',
        'description',
        'id_brand',
        'image_primary',
        'status',
    ];


    public function category()
    {
        return $this->belongsTo(Category::class, 'id_category');
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'id_brand');
    }
    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'id_product');
    }
    public function searchProducts($keyword)
    {
        return Product::where('name', 'like', '%' . $keyword . '%')
                      ->orWhere('description', 'like', '%' . $keyword . '%')
                      ->get();
    }
    public function colors()
    {
        return $this->hasManyThrough(
            Color::class,
            ProductVariant::class,
            'id_product', // Foreign key on product_variants
            'id',         // Local key on colors
            'id',         // Local key on products
            'id_color'    // Foreign key on product_variants
        )->distinct(); // để tránh trùng lặp màu
    }
    public function firstVariant()
    {
        return $this->hasOne(ProductVariant::class, 'id_product')->orderBy('price');
    }
    public function sizes()
    {
        return $this->hasManyThrough(
            Size::class,
            ProductVariant::class,
            'id_product',
            'id',
            'id',
            'id_size'
        )->distinct();
    }
    public function albums()
{
    return $this->hasMany(ProductAlbum::class, 'id_product');
}
  public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
