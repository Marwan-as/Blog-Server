<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Draft;
use App\Helpers\FileHelper;
use Illuminate\Http\Request;
use App\Services\DraftService;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Draft\StoreDraftRequest;
use App\Http\Requests\Draft\UpdateDraftRequest;

class DraftController extends Controller
{
    private $draftService;

    public function __construct(DraftService $draftService)
    {
        $this->draftService = $draftService;
    }

    public function store(StoreDraftRequest $request)
    {
        $draft = $this->draftService->createDraft($request);

        if (!$draft) {
            return response()->json(['message' => 'Failed to create draft. Please try again later.'], 500);
        }

        return response()->json(['message' => 'Draft saved successfully!', 'draft' => $draft], 200);
    }

    public function update(UpdateDraftRequest $request, Draft $draft)
    {
        $updated = $this->draftService->updateDraft($request, $draft);

        if (!$updated) {
            return response()->json(['message' => 'Failed to updated draft. Please try again later.'], 500);
        }

        return response()->json(['message' => 'Draft updated successfully!', 'draft' => $draft], 200);
    }

    public function destroy(Draft $draft)
    {
        if (!$draft) {
            return response()->json(['message' => 'Draft not found.'], 404);
        }


        $deleted = $this->draftService->deleteDraft($draft);

        if (!$deleted) {
            return response()->json(['message' => 'Failed to delete draft. Please try again later.'], 500);
        }

        return response()->json(['message' => 'Draft deleted successfully!'], 200);
    }
}
