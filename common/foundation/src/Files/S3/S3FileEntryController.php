<?php

namespace Common\Files\S3;

use Common\Core\BaseController;
use Common\Files\Actions\CreateFileEntry;
use Common\Files\Actions\GetUserSpaceUsage;
use Common\Files\Actions\ValidateFileUpload;
use Common\Files\Events\FileUploaded;
use Common\Files\FileEntry;
use Common\Files\FileEntryPayload;
use Illuminate\Support\Facades\Auth;

class S3FileEntryController extends BaseController
{
    public function store()
    {
        $validatedData = $this->validate(request(), [
            'clientExtension' => 'required|string',
            'clientMime' => 'nullable|string|max:255',
            'clientName' => 'required|string',
            'disk' => 'string',
            'diskPrefix' => 'string',
            'filename' => 'required|string',
            'parentId' => 'nullable|exists:file_entries,id',
            'relativePath' => 'nullable|string',
            'workspaceId' => 'nullable|int',
            'size' => 'required|int',
        ]);

        $spaceUsage = Auth::check() ? app(GetUserSpaceUsage::class) : null;
        $uploadErrors = (new ValidateFileUpload(
            allowedExtensions: settings('uploads.allowed_extensions'),
            blockedExtensions: settings('uploads.blocked_extensions'),
            maxSize: settings('uploads.max_size'),
            usedSpace: $spaceUsage?->getSpaceUsed(),
            availableSpace: $spaceUsage?->getAvailableSpace(),
        ))->execute([
            'extension' => $validatedData['clientExtension'],
            'size' => $validatedData['size'],
        ]);

        if ($uploadErrors !== null && $uploadErrors->isNotEmpty()) {
            return $this->error($uploadErrors->first(), [], 422);
        }

        $payload = new FileEntryPayload($validatedData);

        $this->authorize('store', [FileEntry::class, $payload->parentId]);

        $fileEntry = app(CreateFileEntry::class)->execute($payload);

        event(new FileUploaded($fileEntry));

        return $this->success(['fileEntry' => $fileEntry]);
    }
}
