<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'settingable_id',
        'settingable_type',
        'key',
        'value',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    public function settingable()
    {
        return $this->morphTo();
    }

    public static function scopeByKey($query, string $key)
    {
        $query->where('key', $key);
    }

    public static function scopeByKeyValue($query, string $key, string $value)
    {
        $query->where('key', $key)->where('value', $value);
    }
}
