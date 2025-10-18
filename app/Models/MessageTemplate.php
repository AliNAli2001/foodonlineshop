<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    protected $table = 'messages_template';

    protected $fillable = [
        'template_name',
        'message_type',
        'content',
        'language',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const TYPES = [
        'whatsapp' => 'WhatsApp',
        'email' => 'Email',
    ];

    const LANGUAGES = [
        'ar' => 'Arabic',
        'en' => 'English',
    ];
}

