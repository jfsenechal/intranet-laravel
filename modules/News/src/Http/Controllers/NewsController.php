<?php

declare(strict_types=1);

namespace AcMarche\News\Http\Controllers;

use AcMarche\News\Models\News;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class NewsController extends Controller
{
    /**
     * Publicly display a single news article.
     */
    public function show(News $news): View
    {
        $news->load('category');

        return view('news::public.show', ['news' => $news]);
    }
}
