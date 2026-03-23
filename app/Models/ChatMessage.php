<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'resident_id', 'sender_id', 'message', 'is_read_by_admin', 'is_read_by_resident'
    ];

    // The resident this chat belongs to
    public function resident()
    {
        return $this->belongsTo(User::class, 'resident_id');
    }

    // The specific user (Admin or Resident) who sent this message
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id')->withDefault([
            'name' => 'Barangay Bot',
            'id' => null, 
        ]);
    }
}