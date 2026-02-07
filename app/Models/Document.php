<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Department;
use App\Models\DocumentCategory;
use App\Models\User;

class Document extends Model
{
    protected $fillable = [
        'title',
        'description',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'category_id',
        'department_id',
        'uploaded_by',
        'access_level',
        'download_count',
    ];

    public function category()
    {
        return $this->belongsTo(DocumentCategory::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
