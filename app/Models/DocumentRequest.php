<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_code', 'user_id', 'document_type_id', 
        'purpose', 'status', 'remarks', 'requestor_name', 
        'requestor_phone', 'requestor_address',
    ];

    protected function requestorDisplayName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->user 
                ? ($this->user->resident ? "{$this->user->resident->fname} {$this->user->resident->lname}" : $this->user->email)
                : $this->requestor_name . ' (Walk-in)',
        );
    }

    // Link to the Resident
    public function resident()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Link to the Document Type info
    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function attachments()
    {
        return $this->hasMany(RequestAttachment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}