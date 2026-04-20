<?php
namespace App\Http\Controllers;

use App\Models\FetchedEmailAttachment;
use App\Models\SendEmailAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class AttachmentController extends Controller
{
    private function getAttachment($emailBox, $id)
    {
        return match ($emailBox) {
            'inbox' => FetchedEmailAttachment::findOrFail($id),
            'outbox' => SendEmailAttachment::findOrFail($id),
            default => abort(404, 'Invalid mailbox')
        };
    }

    private function getFile($attachment)
    {
        $path = "{$attachment->storage_path}/{$attachment->name}";
        $disk = Storage::disk($attachment->storage_disk);

        if (!$disk->exists($path)) {
            \Log::error("Attachment not found", ['id' => $attachment->id, 'path' => $path]);
            abort(404);
        }
        return [$disk, $path];
    }

    public function show($emailBox, $id)
    {
        $attachment    = $this->getAttachment($emailBox, $id);
        [$disk, $path] = $this->getFile($attachment);

        return response()->file(
            $disk->path($path),
            [
                'Content-Type'        => $attachment->mime_type ?? 'application/octet-stream',
                'Content-Disposition' => $attachment->inline ? 'inline' : 'attachment'
            ]
        );
    }

    public function download($emailBox, $id)
    {
        $attachment    = $this->getAttachment($emailBox, $id);
        [$disk, $path] = $this->getFile($attachment);

        return response()->download($disk->path($path), $attachment->name);
    }
}
