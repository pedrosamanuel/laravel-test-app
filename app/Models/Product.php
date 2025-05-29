<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object"
 * )
 */
class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'description',
        'price',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
