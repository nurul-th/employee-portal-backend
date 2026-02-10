<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class DocumentController extends Controller
{
    // LIST DOCUMENTS
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

    // VIEW SINGLE DOCUMENT
    public function show(Document $document, Request $request)
    {
        $user = $request->user();

        // Admin can see everything
        if ($user->hasRole('Admin')) {
            return response()->json(
                $document->load(['category', 'department', 'uploader'])
            );
        }

        // Public document → everyone
        if ($document->access_level === 'public') {
            return response()->json(
                $document->load(['category', 'department', 'uploader'])
            );
        }

        // Department document
        if (
            $document->access_level === 'department' &&
            $user->department_id === $document->department_id
        ) {
            return response()->json(
                $document->load(['category', 'department', 'uploader'])
            );
        }

        // Private document → uploader only
        if (
            $document->access_level === 'private' &&
            $document->uploaded_by === $user->id
        ) {
            return response()->json(
                $document->load(['category', 'department', 'uploader'])
            );
        }

        return response()->json(['message' => 'Unauthorized'], 403);
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

        // Store file (public disk recommended)
        $file = $request->file('file');
        $filePath = $file->store('documents', 'public');

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

    // DOWNLOAD DOCUMENT
    public function download(Document $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $document->increment('download_count');

        return response()->download(
        storage_path('app/public/' . $document->file_path),
        $document->file_name
        );
        
        /*return Storage::disk('public')->download(
            $document->file_path,
            $document->file_name
        );*/
    }

    // DELETE DOCUMENT

    public function destroy(Document $document, Request $request)
    {
        $user = $request->user();

        // Authorization rules
        if (
            $user->hasRole('Employee') && $document->uploaded_by !== $user->id)
            {
            return response()->json(['message' => 'Unauthorized'], 403);
            }

        if (
            $user->hasRole('Manager') &&
            $document->access_level === 'public') 
            {
            return response()->json(['message' => 'Unauthorized'], 403);
            }

        // Delete file from storage
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
            }

         // Delete DB record
        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully'
         ]);
    }

}

