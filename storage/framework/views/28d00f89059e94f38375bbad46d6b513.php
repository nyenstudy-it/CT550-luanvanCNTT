

<?php $__env->startSection('hero'); ?>
    <?php echo $__env->make('pages.components.hero', ['showBanner' => false, 'heroNormal' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <!-- Breadcrumb Section Begin -->
    <section class="breadcrumb-section set-bg" data-setbg="<?php echo e(asset('frontend/images/breadcrumb.jpg')); ?>">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">
                        <h2><?php echo e($blog->title); ?></h2>
                        <div class="breadcrumb__option">
                            <a href="<?php echo e(route('pages.home')); ?>">Trang chủ</a>
                            <a href="<?php echo e(route('blogs.index')); ?>">Blog</a>
                            <span><?php echo e($blog->title); ?></span>
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
                                <?php echo e(\Carbon\Carbon::parse($blog->created_at)->locale('vi')->translatedFormat('d F, Y')); ?>

                            </li>

                        </ul>
                        <h3><?php echo e($blog->title); ?></h3>
                        <p><?php echo e($blog->summary ?? '—'); ?></p>
                    </div>

                    <div class="blog__details__content">

                        
                        <?php if($blog->image): ?>
                            <div class="blog__details__pic mb-4">
                                <img src="<?php echo e(asset('storage/' . $blog->image)); ?>" alt="<?php echo e($blog->title); ?>">
                            </div>
                        <?php endif; ?>

                    
                    <?php if($blog->content): ?>
                        <div class="blog__details__main mb-4">
                            <p><?php echo nl2br(e($blog->content)); ?></p>
                        </div>
                    <?php endif; ?>

                        
                    <?php $__currentLoopData = $blog->blocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="blog__details__block mb-4">
                            
                            <?php if($block->content): ?>
                                <p><?php echo nl2br(e($block->content)); ?></p>
                            <?php endif; ?>

                            
                            <?php if($block->image): ?>
                                <div class="blog__details__pic mb-4">
                                    <img src="<?php echo e(asset('storage/' . $block->image)); ?>" alt="<?php echo e($blog->title); ?>">
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4 col-md-5">
                    <div class="blog__sidebar">
                        <div class="blog__sidebar__item">
                            <h4>Bài viết gần đây</h4>
                            <div class="blog__sidebar__recent">
                                <?php $__currentLoopData = $recentBlogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a href="<?php echo e(route('blogs.show', $recent->slug)); ?>" class="blog__sidebar__recent__item">
                                        <div class="blog__sidebar__recent__item__pic">
                                            <img src="<?php echo e($recent->image ? asset('storage/' . $recent->image) : asset('frontend/img/blog/sidebar/default.jpg')); ?>"
                                                alt="<?php echo e($recent->title); ?>">
                                        </div>
                                        <div class="blog__sidebar__recent__item__text">
                                            <h6><?php echo e(Str::limit($recent->title, 40)); ?></h6>
                                            <span><?php echo e($recent->created_at->format('d M, Y')); ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- Blog Details Section End -->

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/pages/blog-details.blade.php ENDPATH**/ ?>