<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    // LIST DOCUMENTS
   
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Document::with(['category','department','uploader']);

        // RBAC VISIBILITY FILTER

        if (!$user->hasRole('Admin')) {

            $query->where(function ($q) use ($user) {

                // Public
                $q->where('access_level', 'public')

                // Department
                ->orWhere(function ($sub) use ($user) {
                    $sub->where('access_level', 'department')
                        ->where('department_id', $user->department_id);
                })

                // Private (own only)
                ->orWhere(function ($sub) use ($user) {
                    $sub->where('access_level', 'private')
                        ->where('uploaded_by', $user->id);
                });

            });
        }

        // SEARCH (title + description) - case-insensitive + support aliases
        $search = $request->input('search')
            ?? $request->input('q')
            ?? $request->input('keyword');

        if (!empty($search)) {
            $search = trim(mb_strtolower($search));

        $query->where(function ($q) use ($search) {
            $q->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
              ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
        });
    }

        // FILTERS (AND LOGIC) - support aliases
        $categoryId = $request->input('category')
            ?? $request->input('category_id')
            ?? $request->input('document_category_id');

        if (!empty($categoryId)) {
        $query->where('category_id', $categoryId);
        }

        $departmentId = $request->input('department')
            ?? $request->input('department_id')
         ?? $request->input('document_department_id');

        if (!empty($departmentId)) {
            $query->where('department_id', $departmentId);
        }

        // SORT

        if ($request->filled('sort')) {

            switch ($request->sort) {

                case 'name':
                    $query->orderBy('title', 'asc');
                break;

                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;

                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;

                case 'most_downloaded':
                    $query->orderBy('download_count', 'desc');
                    break;

                case 'size':
                    $query->orderBy('file_size', 'desc');
                    break;

                default:
                    $query->latest();
            }

        } else {
            $query->latest();
        }

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min($perPage, 100)); // clamp 1..100
        $documents = $query->paginate($perPage);

        return response()->json([
            'count' => $documents->total(),
            'current_page' => $documents->currentPage(),
            'last_page' => $documents->lastPage(),
            'per_page' => $documents->perPage(),
            'data' => $documents->items(),
        ]);

    }

    // SHOW SINGLE DOCUMENT
    
    public function show($id, Request $request)
    {
        $user = $request->user();
        $document = Document::findOrFail($id);

        if ($user->hasRole('Admin')) {
            return response()->json($document->load(['category','department','uploader']));
        }

        if ($document->access_level === 'public') {
            return response()->json($document->load(['category','department','uploader']));
        }

        if (
            $document->access_level === 'department' &&
            $document->department_id === $user->department_id
        ) {
            return response()->json($document->load(['category','department','uploader']));
        }

        if (
            $document->access_level === 'private' &&
            $document->uploaded_by === $user->id
        ) {
            return response()->json($document->load(['category','department','uploader']));
        }

        return response()->json(['message'=>'Unauthorized'],403);
    }

    // STORE DOCUMENT
    public function store(Request $request)
    {
        $user = $request->user();

        // ❌ Employee not allowed to upload
        if ($user->hasRole('Employee')) {
            return response()->json([
            'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_category_id' => 'required|exists:document_categories,id',
            'file' => 'required|file|mimes:pdf,docx,xlsx,jpg,png|max:10240',
            'access_level' => 'required|in:public,department,private',
        ]);

        if ($user->hasRole('Admin')) {

            $departmentId = $request->access_level === 'department'
                ? $request->department_id
                : null;

        } else { 
            // Manager only
            $departmentId = $user->department_id;
        }

        $file = $request->file('file');
        $filePath = $file->store('documents', 'public');

        $document = Document::create([
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->document_category_id,
            'department_id' => $departmentId,
            'access_level' => $request->access_level,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_type' => $file->getClientOriginalExtension(),
            'uploaded_by' => $user->id,
            'download_count' => 0,
        ]);

        return response()->json([
            'message'=>'Document uploaded successfully',
            'document'=>$document
        ],201);
    }

    // UPDATE DOCUMENT METADATA
    public function update($id, Request $request)
    {
        $user = $request->user();
        $document = Document::findOrFail($id);

        // 👑 Admin → edit anything
        if ($user->hasRole('Admin')) {
            // allowed
        }

        // 🧑‍💼 Manager → own uploaded only
        elseif ($user->hasRole('Manager')) {

            if ($document->uploaded_by !== $user->id) {
                return response()->json(['message'=>'Unauthorized'],403);
            }
        }

        // 👨‍💻 Employee → not allowed
        else {
            return response()->json(['message'=>'Unauthorized'],403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'access_level' => 'sometimes|in:public,department,private',
            'department_id' => 'nullable|exists:departments,id'
        ]);

        $document->update($request->only([
            'title',
            'description',
            'access_level',
            'department_id'
        ]));

        return response()->json([
            'message' => 'Document updated successfully',
            'document' => $document
        ]);
    }

    // DOWNLOAD
    public function download($id, Request $request)
    {
        $user = $request->user();
        $document = Document::findOrFail($id);

        $canDownload = false;

        // Admin → full access
        if ($user->hasRole('Admin')) {
            $canDownload = true;
        }

        // Public → everyone
        elseif ($document->access_level === 'public') {
            $canDownload = true;
        }

        // Department → same department
        elseif (
            $document->access_level === 'department' &&
            $document->department_id === $user->department_id
        ) {
            $canDownload = true;
        }

        // Private → uploader only
        elseif (
            $document->access_level === 'private' &&
            $document->uploaded_by === $user->id
        ) {
            $canDownload = true;
        }

        if (!$canDownload) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            return response()->json(['message'=>'File not found'],404);
        }

        $document->increment('download_count');

        return Storage::disk('public')->download(
            $document->file_path,
            $document->file_name
        );
    }

    // DELETE DOCUMENT
    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $document = Document::findOrFail($id);

        // 👑 Admin → full access
        if ($user->hasRole('Admin')) {
            // allowed
        }

        // 🧑‍💼 Manager → own uploaded only
        elseif ($user->hasRole('Manager')) {

            if ($document->uploaded_by !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }
        }

        // 👨‍💻 Employee → not allowed at all
        else {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully'
        ]);
    }
}