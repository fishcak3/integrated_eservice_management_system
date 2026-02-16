<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestAttachment extends Model
{
    protected $fillable = ['document_request_id', 'file_path', 'file_name', 'file_type'];

    public function request()
    {
        return $this->belongsTo(DocumentRequest::class);
    }
}