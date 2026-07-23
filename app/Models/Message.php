<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_edited' => 'boolean',
        'is_pinned' => 'boolean',
        'is_forwarded' => 'boolean',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyToMessage()
    {
        return $this->belongsTo(Message::class, 'reply_to_message_id')->with('sender:id,name,profile_image');
    }

    public function forwardedFromMessage()
    {
        return $this->belongsTo(Message::class, 'forwarded_from_message_id')->with('sender:id,name,profile_image');
    }

    public function reactions()
    {
        return $this->hasMany(MessageReaction::class);
    }
}
