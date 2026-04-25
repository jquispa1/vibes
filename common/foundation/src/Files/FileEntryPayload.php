<?php

namespace Common\Files;

use Common\Files\Traits\GetsEntryTypeFromMime;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use SplFileInfo;
use Symfony\Component\Mime\MimeTypes;

class FileEntryPayload
{
    use GetsEntryTypeFromMime;

    private array $data;
    public mixed $diskName;
    public string|null $clientName;
    public string $filename;
    public ?int $workspaceId;
    public string|null $clientMime;
    public string $type;
    public ?string $relativePath;
    public string $clientExtension;

    public int $size;
    public ?int $parentId;

    public string $diskPrefix;
    public bool $public;
    public string $visibility;
    public int|null $ownerId;

    public function __construct(array $data)
    {
        $this->prepareData($data);
        $this->diskName = Arr::get($data, 'disk', 'uploads');
        $this->public = $this->diskName === 'public';
        $this->prepareEntryPayload();
    }

    protected function prepareData(array $data): void
    {
        $file = Arr::get($data, 'file');
        $this->data = Arr::except($data, 'file');
        if ($file instanceof UploadedFile) {
            $this->data['clientName'] = $file->getClientOriginalName();
            $this->data['clientMime'] =
                $data['clientMime'] ?? $file->getClientMimeType();
            $this->data['size'] = $file->getSize();
            $this->data[
                'clientExtension'
            ] = $file->getClientOriginalExtension();
        } elseif ($file instanceof SplFileInfo) {
            $this->data['clientName'] = $file->getFilename();
            $this->data['clientMime'] = MimeTypes::getDefault()->guessMimeType(
                $file->getPathname(),
            );
            $this->data['size'] = $file->getSize();
            $this->data['clientExtension'] = $file->getExtension();
        }
    }

    protected function prepareEntryPayload(): void
    {
        $this->clientName = $this->data['clientName'];
        $this->clientMime = $this->data['clientMime'];
        $this->clientExtension = $this->getExtension();
        $this->filename = $this->getFilename();
        $this->workspaceId = Arr::has($this->data, 'workspaceId')
            ? (int) $this->data['workspaceId']
            : null;
        $this->relativePath = $this->getRelativePath();
        $this->diskPrefix = $this->getDiskPrefix();
        $this->parentId = (int) Arr::get($this->data, 'parentId') ?: null;
        $this->ownerId = (int) Arr::get($this->data, 'ownerId') ?: Auth::id();
        $this->size =
            $this->data['file_size'] ??
            ($this->data['size'] ?? $this->data['clientSize']);
        $this->visibility = $this->public
            ? 'public'
            : config('common.site.remote_file_visibility');
        $this->type = $this->getTypeFromMime(
            $this->clientMime,
            $this->clientExtension,
        );
    }

    private function getDiskPrefix()
    {
        if ($this->public) {
            return Arr::get($this->data, 'diskPrefix');
        } else {
            return $this->filename;
        }
    }

    private function getFilename(): string
    {
        // Never trust request 'filename' - use only clientName (from actual file) and validated extension
        $uuid = (string) Str::uuid();
        $ext = $this->clientExtension;
        $uniqueSuffix = substr($uuid, 0, 8);

        if ($this->public) {
            // Prefer original filename for readability; add suffix to avoid duplicate file_name in DB
            if (!empty($this->clientName)) {
                $base = pathinfo($this->clientName, PATHINFO_FILENAME);
                $safe = Str::limit(
                    preg_replace('/[^a-zA-Z0-9_\-]/', '', $base) ?: 'file',
                    200,
                );

                return $safe . '-' . $uniqueSuffix . '.' . $ext;
            }

            return $uuid . '.' . $ext;
        }

        // Private disk: readable name with unique suffix (avoids uploads_file_name_unique violation)
        if (!empty($this->clientName)) {
            $base = pathinfo($this->clientName, PATHINFO_FILENAME);
            $safe = Str::limit(
                preg_replace('/[^a-zA-Z0-9_\-]/', '', $base) ?: 'file',
                200,
            );

            return $safe . '-' . $uniqueSuffix . '.' . $ext;
        }

        return $uuid;
    }

    private function getRelativePath(): string|null
    {
        // relative path might sometimes be "null" or "false" as string
        $relativePath = Arr::get($this->data, 'relativePath');
        if (!is_string($relativePath) || !Str::contains($relativePath, '/')) {
            $relativePath = null;
        }
        return $relativePath;
    }

    private function getExtension(): string
    {
        if ($extension = Arr::get($this->data, 'clientExtension')) {
            return $extension;
        }

        $pathinfo = pathinfo($this->clientName);

        if (isset($pathinfo['extension'])) {
            return $pathinfo['extension'];
        }

        return explode('/', $this->clientMime)[1];
    }
}

