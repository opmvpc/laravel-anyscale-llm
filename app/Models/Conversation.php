<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'token_count'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function history(): array
    {
        return $this->messages->map(function ($message) {
            return [
                'role' => $message->role,
                'content' => $message->body,
            ];
        })->toArray();
    }
}
