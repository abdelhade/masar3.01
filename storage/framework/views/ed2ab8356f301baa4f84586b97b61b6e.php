<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(__('errors.access_forbidden_title')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-result {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; }
        .loading { background-color: #fff3cd; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4"><?php echo e(__('errors.access_forbidden_title')); ?></h1>
        <p class="text-center"><?php echo e(__('errors.access_forbidden_message')); ?></p>
        
        <div class="text-center mt-4">
            <a href="<?php echo e(route('home')); ?>" class="btn btn-primary"><?php echo e(__('errors.go_home')); ?></a>
            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-secondary"><?php echo e(__('common.back')); ?></a>
        </div>
    </div>
</body>
</html>
<?php /**PATH D:\laragon\www\massar1.02\resources\views/errors/403.blade.php ENDPATH**/ ?>