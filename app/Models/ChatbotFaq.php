<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotFaq extends Model
{
    
    protected $fillable = [
        'keyword',
        'response_auth',
        'response_guest',
    ];
}