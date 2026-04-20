<?php

namespace Modules\Demo\Http\Controllers;

use Inertia\Inertia;
use Modules\Billing\Models\Product;
use Modules\Blog\Data\PostData;
use Modules\Blog\Models\Post;

class DemoController
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke()
    {
        $data = [];

        if (app('modules')->isEnabled('Billing')) {
            $data['products'] = Product::displayable()->get();
        }

        if (app('modules')->isEnabled('Blog')) {
            $posts = Post::published()
                ->with(['category', 'author'])
                ->orderByDesc('published_at')
                ->limit(3)
                ->get();

            $data['latestPosts'] = $posts->map(fn (Post $post) => PostData::fromPost($post));
        }

        return Inertia::render('Demo::Index', $data)->withSSR();
    }
}
