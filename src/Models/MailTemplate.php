<?php

namespace crudPackage\Models;

use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    protected $fillable = [
        'key',
        'subject',
        'heading',
        'content',
        'button',
        'note',
    ];
}
