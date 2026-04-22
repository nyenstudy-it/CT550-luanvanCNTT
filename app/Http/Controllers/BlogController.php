<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\BlogBlock;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    // Trang danh sách blog khách hàng
    public function index()
    {
        $blogs = Blog::latest()->paginate(6);
        $recentBlogs = Blog::latest()->take(5)->get();
        return view('pages.blogs', compact('blogs', 'recentBlogs'));
    }

    // Trang chi tiết blog khách hàng
    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)->firstOrFail();
        $recentBlogs = Blog::latest()->take(5)->get();

        $blog->load('blocks');

        return view('pages.blog-details', compact('blog', 'recentBlogs'));
    }


    // Admin
    public function adminIndex(Request $request)
    {
        $query = Blog::with('blocks');

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->keyword);
            $query->where(function ($q) use ($keyword) {
                $escaped = addcslashes($keyword, '\\%_');
                $q->where('title', 'like', '%' . $escaped . '%')
                    ->orWhere('slug', 'like', '%' . $escaped . '%')
                    ->orWhere('summary', 'like', '%' . $escaped . '%');
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $blogs = $query->latest()->paginate(10)->appends($request->query());

        $summary = [
            'total' => Blog::count(),
            'this_month' => Blog::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'with_image' => Blog::whereNotNull('image')->where('image', '!=', '')->count(),
        ];

        return view('admin.blogs.index', compact('blogs', 'summary'));
    }


    public function create()
    {
        return view('admin.blogs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'blocks.*.content' => 'nullable|string',
            'blocks.*.image' => 'nullable|image',
        ]);

        $data['slug'] = Str::slug($data['title']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('blogs', 'public');
        }

        DB::transaction(function () use ($data, $request) {
            $blog = Blog::create($data);

            if ($request->has('blocks')) {
                foreach ($request->blocks as $index => $block) {
                    $blockData = [
                        'blog_id' => $blog->id,

                        'content' => $block['content'] ?? null,
                        'position' => $index,
                    ];

                    if (isset($block['image']) && $block['image'] instanceof \Illuminate\Http\UploadedFile) {
                        $blockData['image'] = $block['image']->store('blog_blocks', 'public');
                    }

                    BlogBlock::create($blockData);
                }
            }
        });

        return redirect()->route('admin.blogs.index')->with('success', 'Blog đã được tạo!');
    }

    public function edit($id)
    {
        $blog = Blog::findOrFail($id);
        return view('admin.blogs.edit', compact('blog'));
    }

    public function update(Request $request, $id)
    {
        $blog = Blog::with('blocks')->findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'blocks' => 'nullable|array',
            'blocks.*.content' => 'nullable|string',
            'blocks.*.image' => 'nullable|image',
        ]);

        $data['slug'] = Str::slug($data['title']);

        // Cập nhật hình đại diện blog
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('blogs', 'public');
        }

        $blog->update($data);

        // Xóa tất cả block cũ trước khi tạo mới
        $blog->blocks()->delete();

        if (!empty($data['blocks'])) {
            foreach ($request->blocks as $index => $blk) {
                $blockData = [
                    'content' => $blk['content'] ?? null,
                    'position' => $index,
                ];

                if (!empty($blk['image'])) {
                    // upload mới
                    $blockData['image'] = $blk['image']->store('blog_blocks', 'public');
                } elseif (!empty($blk['old_image'])) {
                    // giữ ảnh cũ
                    $blockData['image'] = $blk['old_image'];
                }
                $blog->blocks()->create($blockData);
            }
        }

        return redirect()->route('admin.blogs.index')->with('success', 'Blog đã được cập nhật!');
    }

    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);
        $blog->delete();
        return redirect()->route('admin.blogs.index')->with('success', 'Blog đã được xóa!');
    }
}
