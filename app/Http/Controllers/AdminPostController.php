<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Traits\EmailConfig;


class AdminPostController extends Controller
{
    use EmailConfig;

    public function index()
    {
        return view('admin.posts.index', [
            'posts' => Post::latest()->paginate(50)
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.posts.posts-create');
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'title' => [
                'required',
            ],
            'slug' => [
                'required',
                Rule::unique('posts', 'slug')
            ],
            'thumbnail' => [
                'required',
                'image',
            ],
            'excerpt' => [
                'required',
            ],
            'body' => [
                'required',
            ],
        ]);

        $attributes['user_id'] = auth()->id();
        $attributes['thumbnail'] = request()->file('thumbnail')->store('thumbnails', 'public');

        Post::create($attributes);

        $data = [
            'type' => "post",
            'subject' => "Nový příspěvek na webu Markrabka",
        ];
        $response = $this->createEmail($data);

        $users_emails = User::all()->pluck('email')->toArray(); // pole emailu vsech uzivatelu
        $this->sendEmailTo($response['email'], $users_emails);

        return redirect('/')->with('success', 'Příspěvek byl vytvořen!');
    }

    public function edit(Post $post)
    {
        return view('admin.posts.edit', ['post' => $post]);
    }

    public function update(Post $post)
    {

        $attributes = request()->validate([
            'title' => 'required',
            'slug' => ['required', Rule::unique('posts', 'slug')->ignore($post->id)],
            'thumbnail' => 'image',
            'excerpt' => 'required',
            'body' => 'required',
        ]);

        if (isset($attributes['thumbnail'])) {
            $attributes['thumbnail'] = request()->file('thumbnail')->store('thumbnails');
        }

        $post->update($attributes);

        return redirect('/admin/posts')->with('success', 'Příspěvek byl aktualizován!');
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return back()->with('success', 'Příspěvek byl odstraněn!');
    }
}
