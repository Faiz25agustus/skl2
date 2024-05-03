<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::latest()->paginate(5);
        return view('posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('posts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'image' => 'required|image|mimes:jpeg,jpg,png|max:2408',
            'title' => 'required|min:5',
            'content' => 'required|min:10'
        ]);

        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content
        ]);

        return redirect()->route('post.index')->with('success', 'Data Saved');
    }

    public function show(string $id): View
    {
        $post = Post::findOrFail($id);
        return view('posts.show', compact('post'));
    }

    public function edit(string $id): View
    {
        $post = Post::findOrFail($id);
        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $post = Post::findOrFail($id);

        $this->validate($request, [
            'image' => 'image|mimes:jpeg,jpg,png|max:2408',
            'title' => 'required|min:5',
            'content' => 'required|min:10'
        ]);

        if ($request->hasFile('image')) {
            Storage::delete('public/posts/' . $post->image);
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());
            $post->image = $image->hashName();
        }

        $post->title = $request->title;
        $post->content = $request->content;
        $post->save();

        return redirect()->route('post.index')->with('success', 'Post updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        $post = Post::findOrFail($id);

        Storage::delete('public/posts/' . $post->image);

        $post->delete();

        return redirect()->route('post.index')->with('success', 'Post deleted successfully');
    }
}
