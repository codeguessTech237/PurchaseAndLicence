<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseCode extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'purchase_codes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'is_active',
        'activated_at',
        'activated_by',
    ];

    /**
     * Casts for attributes.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
    ];

    /**
     * Scope to find only active codes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Mark code as activated.
     */
    public function activate($user = null)
    {
        $this->update([
            'is_active' => false,
            'activated_at' => now(),
            'activated_by' => $user,
        ]);
    }
}
