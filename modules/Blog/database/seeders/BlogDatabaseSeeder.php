<?php

namespace Modules\Blog\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Models\Category;
use Modules\Blog\Models\Post;

class BlogDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::first();

        $gettingStarted = Category::firstOrCreate(['name' => 'Getting Started']);
        $featuresModules = Category::firstOrCreate(['name' => 'Features & Modules']);
        $devExperience = Category::firstOrCreate(['name' => 'Developer Experience']);

        $content = fn (string $file): string => file_get_contents(__DIR__.'/content/'.$file);

        Post::firstOrCreate(
            ['title' => 'What Is Saucebase? The Modular Laravel Starter Kit'],
            [
                'excerpt' => 'Every SaaS starts the same way: weeks of setup before you write a single line of real product code. Saucebase is the modular Laravel starter kit that skips that part.',
                'content' => $content('post-1-what-is-saucebase.html'),
                'status' => PostStatus::Published,
                'published_at' => now()->subMonths(5),
                'category_id' => $gettingStarted->id,
                'author_id' => $author?->id,
            ]
        );

        Post::firstOrCreate(
            ['title' => 'Stop Rebuilding the Same Boilerplate Every Project'],
            [
                'excerpt' => "If you've launched more than one Laravel app, you know the feeling — two weeks gone before you write any real product code. There's a better way.",
                'content' => $content('post-2-stop-rebuilding-boilerplate.html'),
                'status' => PostStatus::Published,
                'published_at' => now()->subMonths(4),
                'category_id' => $gettingStarted->id,
                'author_id' => $author?->id,
            ]
        );

        Post::firstOrCreate(
            ['title' => 'The VILT Stack: Laravel, Vue, Inertia, and Tailwind Done Right'],
            [
                'excerpt' => "Modern web dev has a tension between server productivity and rich interactivity. The VILT stack resolves it — and Saucebase wires all four pieces together so you don't have to.",
                'content' => $content('post-3-vilt-stack.html'),
                'status' => PostStatus::Published,
                'published_at' => now()->subMonths(3),
                'category_id' => $devExperience->id,
                'author_id' => $author?->id,
            ]
        );

        $post4 = Post::firstOrCreate(
            ['title' => 'Your First Module: Scaffold and Ship in Under 10 Minutes'],
            [
                'excerpt' => "One Artisan command scaffolds a fully structured module — service provider, controller, routes, Vue pages, migrations, factory, tests, and Vite config. Here's how it works.",
                'content' => $content('post-4-your-first-module.html'),
                'status' => PostStatus::Published,
                'published_at' => now()->subMonths(2),
                'category_id' => $featuresModules->id,
                'author_id' => $author?->id,
            ]
        );

        $image4 = public_path('images/blog/add-your-saucebase.jpg');
        if (file_exists($image4) && ! $post4->hasMedia('cover')) {
            $post4->addMedia($image4)->preservingOriginal()->toMediaCollection('cover');
        }

        $post5 = Post::firstOrCreate(
            ['title' => 'Auth, Billing, and Privacy: The Three Modules Every SaaS Needs'],
            [
                'excerpt' => "Three modules every SaaS needs — and Saucebase ships them already built. Here's what's included and why they matter.",
                'content' => $content('post-5-auth-billing-privacy.html'),
                'status' => PostStatus::Published,
                'published_at' => now()->subMonth(),
                'category_id' => $featuresModules->id,
                'author_id' => $author?->id,
            ]
        );

        $image5 = public_path('images/blog/cookies-or-privacy.jpg');
        if (file_exists($image5) && ! $post5->hasMedia('cover')) {
            $post5->addMedia($image5)->preservingOriginal()->toMediaCollection('cover');
        }

        $post6 = Post::firstOrCreate(
            ['title' => 'Copy-and-Own: The Philosophy Behind Saucebase Modules'],
            [
                'excerpt' => "Most starter kits give you a locked box. Saucebase takes the opposite approach: when you install a module, the source code lands in your repo and it's yours to read, edit, and own.",
                'content' => $content('post-6-copy-and-own.html'),
                'status' => PostStatus::Published,
                'published_at' => now()->subWeeks(2),
                'category_id' => $devExperience->id,
                'author_id' => $author?->id,
            ]
        );

        $image6 = public_path('images/blog/your-recipes.jpg');
        if (file_exists($image6) && ! $post6->hasMedia('cover')) {
            $post6->addMedia($image6)->preservingOriginal()->toMediaCollection('cover');
        }
    }
}
