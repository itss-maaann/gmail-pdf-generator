<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdfGeneration extends Model
{
    protected $fillable = ['id', 'status', 'from_email', 'to_email', 'file_path', 'error'];
}
