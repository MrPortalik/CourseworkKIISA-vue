<?php

namespace App\Notifications;

use App\Models\PlatformReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReportResponseNotification extends Notification
{
    use Queueable;

    public function __construct(public PlatformReport $report) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'report_response',
            'report_id' => $this->report->id,
            'message' => $this->report->admin_reply,
            'admin_name' => $this->report->respondedBy?->name,
            'article_slug' => $this->report->article?->slug,
        ];
    }
}
