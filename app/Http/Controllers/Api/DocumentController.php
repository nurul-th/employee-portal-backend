<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;

class DocumentController extends Controller
{
    //index function - list document with access control
    public function index(Request $request)
    {
        $user = $request->user();

        // Admin → all documents
        if ($user->hasRole('admin')) {
            $documents = Document::with(['category', 'department', 'uploader'])
                ->latest()
                ->get();

            return response()->json($documents);
        }

        // Manager / Employee
        $documents = Document::where(function ($query) use ($user) {

            // Public documents
            $query->where('access_level', 'public')

            // Department documents
            ->orWhere(function ($q) use ($user) {
                $q->where('access_level', 'department')
                  ->where('department_id', $user->department_id);
            })

            // Private documents uploaded by user
            ->orWhere(function ($q) use ($user) {
                $q->where('access_level', 'private')
                  ->where('uploaded_by', $user->id);
            });

        })
        ->with(['category', 'department', 'uploader'])
        ->latest()
        ->get();

        return response()->json($documents);
    }

    //store function - upload new document
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:document_categories, id'],
            'department_id' => ['nullable', 'exists:departments, id'],
            'access_level' => ['required', 'in:public, department, private'],
            'file' => [
                'required',
                'file',
                'mimes:pdf,docx,xlsx,jpg,png,webp',
                'max:10240', //10MB
            ],
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        $document = Document::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'category_id' => $validated['category_id'],
            'department_id' => $validated['department_id'] ?? null,
            'uploaded_by' => $request->user()->id,
            'access_level' => $validated['access_level'],
            'download_count' => 0,
        ]);

        return response()->json([
            'message' => 'Document uploaded successfully',
            'data' => $document,
        ], 201);
    }
}