<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class NewsSourceController extends Controller
{
    public function index()
    {
        $sources = NewsSource::withCount('articles')->get();
        return view('admin.news_sources.index', compact('sources'));
    }

    public function toggleActive(NewsSource $newsSource)
    {
        $newsSource->update(['is_active' => !$newsSource->is_active]);
        return redirect()->back()->with('success', 'Source status updated successfully.');
    }

    public function sync(NewsSource $newsSource)
    {
        Artisan::call('news:sync', ['--source' => $newsSource->slug, '--force' => true]);
        return redirect()->back()->with('success', 'Manual sync triggered. ' . Artisan::output());
    }
}
