<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use SoftDeletes; // 2. Sử dụng trait

    protected $fillable = ['name', 'image'];
    
    // 3. Khai báo kiểu dữ liệu cho ngày xóa (tùy chọn)
    protected $dates = ['deleted_at'];
}
