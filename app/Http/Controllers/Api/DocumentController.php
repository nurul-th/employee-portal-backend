<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;

class DocumentController extends Controller
{
    //LIST DOCUMENTS
    public function index(Request $request)
    {
        $user = $request->user();

        // Admin → see all
        if ($user->hasRole('Admin')) {
            return Document::with(['category', 'department', 'uploader'])
                ->latest()
                ->get();
        }

        // Manager & Employee
        return Document::where(function ($query) use ($user) {

            // Public
            $query->where('access_level', 'public')

            // Department (Manager)
            ->orWhere(function ($q) use ($user) {
                $q->where('access_level', 'department')
                  ->where('department_id', $user->department_id);
            })

            // Private (own only)
            ->orWhere(function ($q) use ($user) {
                $q->where('access_level', 'private')
                  ->where('uploaded_by', $user->id);
            });

        })
        ->with(['category', 'department', 'uploader'])
        ->latest()
        ->get();
    }

    // UPLOAD DOCUMENT
    public function store(Request $request)
    {
        $user = $request->user();

        // Base validation
        $request->validate([
            'title' => 'required|string|max:255',
            'document_category_id' => 'required|exists:document_categories,id',
            'file' => 'required|file|max:10240',
        ]);

        // Role-based rules
        if ($user->hasRole('Admin')) {

            $request->validate([
                'access_level' => 'required|in:public,department,private',
                'department_id' => 'required_if:access_level,department|exists:departments,id',
            ]);

            $accessLevel = $request->access_level;
            $departmentId = $request->department_id;

        } elseif ($user->hasRole('Manager')) {

            $accessLevel = 'department';
            $departmentId = $user->department_id;

        } else {
            // Employee
            $accessLevel = 'private';
            $departmentId = null;
        }

        // Store file
        $file = $request->file('file');
        $filePath = $file->store('documents');

        // Save document
        $document = Document::create([
            'title' => $request->title,
            'category_id' => $request->document_category_id,
            'department_id' => $departmentId,
            'access_level' => $accessLevel,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_type' => $file->getClientMimeType(),
            'uploaded_by' => $user->id,
            'download_count' => 0,
        ]);

        return response()->json([
            'message' => 'Document uploaded successfully',
            'document' => $document,
        ], 201);
    }
}
