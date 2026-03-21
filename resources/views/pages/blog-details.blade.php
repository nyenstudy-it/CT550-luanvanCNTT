@extends('layout')

@section('hero')
    @include('pages.components.hero', ['showBanner' => false, 'heroNormal' => true])
@endsection

@section('content')

    <!-- Breadcrumb Section Begin -->
    <section class="breadcrumb-section set-bg" data-setbg="{{ asset('frontend/images/breadcrumb.jpg') }}">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">
                        <h2>{{ $blog->title }}</h2>
                        <div class="breadcrumb__option">
                            <a href="{{ route('pages.home') }}">Trang chủ</a>
                            <a href="{{ route('blogs.index') }}">Blog</a>
                            <span>{{ $blog->title }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Blog Details Section Begin -->
    <section class="blog-details spad">
        <div class="container">
            <div class="row">

                <!-- Blog Content -->
                <div class="col-lg-8 col-md-7">
                    <div class="blog__details__text">
                        <ul>
                            <li>
                                <i class="fa fa-calendar-o"></i>
                                {{ \Carbon\Carbon::parse($blog->created_at)->locale('vi')->translatedFormat('d F, Y') }}
                            </li>

                        </ul>
                        <h3>{{ $blog->title }}</h3>
                        <p>{{ $blog->summary ?? '—' }}</p>
                    </div>

                    <div class="blog__details__content">

                        {{-- Ảnh đại diện blog --}}
                        @if($blog->image)
                            <div class="blog__details__pic mb-4">
                                <img src="{{ asset('storage/' . $blog->image) }}" alt="{{ $blog->title }}">
                            </div>
                        @endif

                    {{-- Nội dung chính --}}
                    @if($blog->content)
                        <div class="blog__details__main mb-4">
                            <p>{!! nl2br(e($blog->content)) !!}</p>
                        </div>
                    @endif

                        {{-- Blocks blog --}}
                    @foreach ($blog->blocks as $block)
                        <div class="blog__details__block mb-4">
                            {{-- Nội dung text --}}
                            @if ($block->content)
                                <p>{!! nl2br(e($block->content)) !!}</p>
                            @endif

                            {{-- Hình ảnh --}}
                            @if ($block->image)
                                <div class="blog__details__pic mb-4">
                                    <img src="{{ asset('storage/' . $block->image) }}" alt="{{ $blog->title }}">
                                </div>
                            @endif
                        </div>
                    @endforeach
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4 col-md-5">
                    <div class="blog__sidebar">
                        <div class="blog__sidebar__item">
                            <h4>Bài viết gần đây</h4>
                            <div class="blog__sidebar__recent">
                                @foreach ($recentBlogs as $recent)
                                    <a href="{{ route('blogs.show', $recent->slug) }}" class="blog__sidebar__recent__item">
                                        <div class="blog__sidebar__recent__item__pic">
                                            <img src="{{ $recent->image ? asset('storage/' . $recent->image) : asset('frontend/img/blog/sidebar/default.jpg') }}"
                                                alt="{{ $recent->title }}">
                                        </div>
                                        <div class="blog__sidebar__recent__item__text">
                                            <h6>{{ Str::limit($recent->title, 40) }}</h6>
                                            <span>{{ $recent->created_at->format('d M, Y') }}</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- Blog Details Section End -->

@endsection