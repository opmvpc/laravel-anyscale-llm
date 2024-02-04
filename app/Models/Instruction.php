<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instruction extends Model
{
    use HasFactory;

    protected $fillable = ['personal', 'behavior'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
