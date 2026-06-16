<?php

namespace App\Http\Controllers;

use App\Support\PlatformSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminModeratorSettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/ModeratorSettings', [
            'settings' => [
                'hit_ratings_threshold' => PlatformSettings::hitRatingsThreshold(),
                'hit_ratings_days' => PlatformSettings::hitRatingsDays(),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'hit_ratings_threshold' => 'required|integer|min:0|max:10000',
            'hit_ratings_days' => 'required|integer|min:0|max:365',
        ]);

        PlatformSettings::set('hit_ratings_threshold', (int) $data['hit_ratings_threshold']);
        PlatformSettings::set('hit_ratings_days', (int) $data['hit_ratings_days']);

        return back()->with('success', 'Настройки модерации сохранены.');
    }
}
