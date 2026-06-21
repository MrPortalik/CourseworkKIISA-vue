<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\PlatformReport;
use App\Models\User;
use App\Support\ImageUploadRules;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class PlatformReportController extends Controller
{
    private const MAX_ATTACHMENTS_BYTES = 10 * 1024 * 1024;

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:article_complaint,user_complaint,feedback,site_complaint',
            'message' => 'required|string|min:10|max:5000',
            'article_id' => 'nullable|exists:articles,id',
            'reported_user_id' => 'nullable|exists:users,id',
            'attachments' => 'nullable|array|max:10',
            ...ImageUploadRules::rule('attachments.*', 10240),
        ]);

        $files = $request->file('attachments', []);
        if (! is_array($files)) {
            $files = $files ? [$files] : [];
        }

        $this->assertAttachmentsTotalSize($files);

        if ($validated['type'] === PlatformReport::TYPE_ARTICLE_COMPLAINT) {
            $article = Article::findOrFail($validated['article_id']);
            if (! $article->is_published) {
                abort(422, 'Нельзя пожаловаться на неопубликованную статью.');
            }
            $validated['reported_user_id'] = null;
        } elseif ($validated['type'] === PlatformReport::TYPE_USER_COMPLAINT) {
            $reportedUser = User::findOrFail($validated['reported_user_id']);
            if ($reportedUser->id === $request->user()->id) {
                abort(422, 'Нельзя пожаловаться на себя.');
            }
            $validated['article_id'] = null;
        } else {
            $validated['article_id'] = null;
            $validated['reported_user_id'] = null;
        }

        $report = PlatformReport::create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'article_id' => $validated['article_id'],
            'reported_user_id' => $validated['reported_user_id'],
            'message' => $validated['message'],
            'status' => PlatformReport::STATUS_PENDING,
        ]);

        if ($files !== []) {
            $report->update([
                'attachments' => $this->storeAttachments($report->id, $files),
            ]);
        }

        return back()->with('status', 'Сообщение отправлено. Спасибо за обратную связь!');
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    private function assertAttachmentsTotalSize(array $files): void
    {
        $total = 0;
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $total += $file->getSize();
            }
        }

        if ($total > self::MAX_ATTACHMENTS_BYTES) {
            throw ValidationException::withMessages([
                'attachments' => ['Общий размер вложений не должен превышать 10 МБ.'],
            ]);
        }
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, array{path: string, name: string, size: int, url: string}>
     */
    private function storeAttachments(int $reportId, array $files): array
    {
        $stored = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store("report_attachments/{$reportId}", 'public');
            $stored[] = [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'url' => '/storage/'.ltrim($path, '/'),
            ];
        }

        return $stored;
    }
}
