<?php

namespace App\Http\Controllers;

use App\Helpers\FileHelper;
use App\Models\Draft;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DraftController extends Controller
{
    public function store(Request $request)
    {

        $data = $request->validate([
            'title' => 'required',
            'body' => 'nullable',
            'privacy' => 'nullable',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,mp4|max:2048'
        ]);

        $user_id = $request->user()->id;
        $data['user_id'] = $user_id;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $this->storeFile($image, 'uploads/drafts', 'public');
            $data['imagePath'] = $path;
        }

        $draft = DB::transaction(function () use ($data) {
            return Draft::create($data);
        });

        if (!$draft) {
            return response()->json(['message' => 'Failed to create draft. Please try again later.'], 500);
        }

        return response()->json(['message' => 'Draft saved successfully!', 'draft' => $draft], 200);
    }

    public function update(Request $request, Draft $draft)
    {
        $data = $request->validate([
            'title' => 'sometimes',
            'body' => 'nullable',
            'privacy' => 'nullable',
            'image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $relativePath = FileHelper::getRelativeFilePath($draft->imagePath);
            $data['imagePath'] = $this->storeFile($image, 'uploads/drafts', 'public', $relativePath);
        }

        $updated = DB::transaction(function () use ($draft, $data) {
            return $draft->update($data);
        });

        if (!$updated) {
            return response()->json(['message' => 'Failed to updated draft. Please try again later.'], 500);
        }

        return response()->json(['message' => 'Draft updated successfully!', 'draft' => $draft], 200);
    }

    public function delete(Draft $draft)
    {
        if (!$draft) {
            return response()->json(['message' => 'Draft not found.'], 404);
        }

        if (!$draft->imagePath) {
            $draft->delete();
            return response()->json(['message' => 'Draft deleted successfully!'], 200);
        }

        $relativePath = FileHelper::getRelativeFilePath($draft->imagePath);
        $deleted = $this->deleteFile($relativePath,  'public');

        if (!$deleted) {
            return response()->json(['message' => 'Failed to delete draft. Please try again later.'], 500);
        }
        $draft->delete();

        return response()->json(['message' => 'Draft deleted successfully!'], 200);
    }
}
