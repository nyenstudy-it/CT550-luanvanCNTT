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
                        <h2>Blog</h2>
                        <div class="breadcrumb__option">
                            <a href="{{ route('pages.home') }}">Trang chủ</a>
                            <span>Blog</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Blog Section Begin -->
    <section class="blog spad">
        <div class="container">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-4 col-md-5">
                    <div class="blog__sidebar">
                        <!-- Search -->
                        <div class="blog__sidebar__search">
                            <form action="{{ route('blogs.index') }}" method="GET">
                                <input type="text" name="search" placeholder="Tìm kiếm..." value="{{ request('search') }}">
                                <button type="submit"><span class="icon_search"></span></button>
                            </form>
                        </div>

                        <!-- Bài viết gần đây -->
                        <div class="blog__sidebar__item">
                            <h4>Bài viết gần đây</h4>
                            <div class="blog__sidebar__recent">
                                @foreach ($recentBlogs as $recent)
                                    <a href="{{ route('blogs.show', $recent->slug) }}" class="blog__sidebar__recent__item">
                                        <div class="blog__sidebar__recent__item__pic">
                                            <img src="{{ $recent->image ? asset('storage/' . $recent->image) : asset('img/blog/sidebar/default.jpg') }}"
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

                        <!-- Tags (tuỳ chọn) -->
                        <div class="blog__sidebar__item">
                            <h4>Thẻ</h4>
                            <div class="blog__sidebar__item__tags">
                                <a href="#">Ẩm thực</a>
                                <a href="#">Phong cách sống</a>
                                <a href="#">Du lịch</a>
                                <a href="#">Sức khỏe</a>
                                <a href="#">Nấu ăn</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danh sách Blog -->
                <div class="col-lg-8 col-md-7">
                    <div class="row g-4">
                        @forelse ($blogs as $blog)
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="blog__item">
                                    <div class="blog__item__pic">
                                        <img src="{{ $blog->image ? asset('storage/' . $blog->image) : asset('img/blog/blog-1.jpg') }}"
                                            alt="{{ $blog->title }}">
                                    </div>
                                    <div class="blog__item__text">
                                        <ul>
                                            <li>
                                                <i class="fa fa-calendar-o"></i>
                                                {{ \Carbon\Carbon::parse($blog->created_at)->locale('vi')->translatedFormat('d F, Y') }}
                                            </li>

                                            <li><i class="fa fa-comment-o"></i> 0</li>
                                        </ul>
                                        <h5><a href="{{ route('blogs.show', $blog->slug) }}">{{ $blog->title }}</a></h5>
                                        <p>{{ Str::limit($blog->summary, 120) }}</p>
                                        <a href="{{ route('blogs.show', $blog->slug) }}" class="blog__btn">Xem thêm <span
                                                class="arrow_right"></span></a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted">
                                Chưa có blog nào.
                            </div>
                        @endforelse

                        <!-- Phân trang -->
                        <div class="col-lg-12">
                            <div class="product__pagination blog__pagination">
                                {{ $blogs->links() }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- Blog Section End -->
@endsection