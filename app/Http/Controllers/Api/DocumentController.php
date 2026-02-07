<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;

class DocumentController extends Controller
{
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
}