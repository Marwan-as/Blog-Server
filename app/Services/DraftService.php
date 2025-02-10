<?php

namespace App\Services;

use Exception;
use App\Models\Draft;
use App\Helpers\FileHelper;
use InvalidArgumentException;
use App\Traits\FileServiceTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Draft\StoreDraftRequest;
use App\Http\Requests\Draft\UpdateDraftRequest;

class DraftService
{
    use FileServiceTrait;

    public function createDraft(StoreDraftRequest $request): Draft
    {
        $data = $request->validated();

        $user_id = $request->user()->id;
        $data['user_id'] = $user_id;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $this->storeFile($image, 'uploads/media', 'public');
            $data['imagePath'] = $path;
        }

        $draft = DB::transaction(fn() => Draft::create($data));

        if (!$draft) {
            throw new Exception('Failed to create draft.', 500);
        }

        return $draft;
    }

    public function updateDraft(UpdateDraftRequest $request, Draft $draft): Draft
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $relativePath = FileHelper::getRelativeFilePath($draft->imagePath);
            $data['imagePath'] = $this->storeFile($image, 'uploads/media', 'public', $relativePath);
        }

        $updated = DB::transaction(fn() => $draft->update($data));

        if (!$updated) {
            throw new Exception('Failed to update draft.', 500);
        }

        return $updated;
    }

    public function deleteDraft(Draft $draft): ?bool
    {
        if (!$draft->imagePath) {
            return $draft->delete();
        }

        $relativePath = FileHelper::getRelativeFilePath($draft->imagePath);
        $deleted = $this->deleteFile($relativePath,  'public');

        if (!$deleted) {
            throw new Exception('Failed to delete draft image.', 500);
        }

        return $draft->delete();
    }
}
