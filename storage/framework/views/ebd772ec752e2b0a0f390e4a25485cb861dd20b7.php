

<?php $__env->startSection('content'); ?>
<div class="container vertical-center">
    <div class="row justify-content-md-center">
        <div class="col-md-6 col-sm-12">   
            <div class="install-path text-center mt-9 mb-5">
                <span class="btn mr-2 active"><i class="fa-brands fa-instalod"></i></span>
                <span class="btn mr-2"><i class="fa-solid fa-ballot-check"></i></span>
                <span class="btn mr-2"><i class="fa-solid fa-file-check"></i></span>	
                <span class="btn mr-2"><i class="fa fa-database"></i></span>
                <span class="btn mr-2"><i class="fa-solid fa-file-certificate"></i></span>
                <span class="btn mr-2"><i class="fa-solid fa-shield-check"></i></span>
            </div>
            <div class="card border-0 special-shadow mt-5 mb-5">	
                						
                <div class="card-body mt-7">                                                  

                    <h3 class="text-center font-weight-bold mb-8"><?php echo e(__('Install')); ?> <span class="text-info"><?php echo e(config('app.name')); ?></span></h3>

                    <?php if($message = Session::get('success')): ?>
                        <div class="alert alert-login alert-success"> 
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <strong><i class="fa fa-check-circle"></i> <?php echo e($message); ?></strong>
                        </div>
                        <?php endif; ?>

                        <?php if($message = Session::get('error')): ?>
                        <div class="alert alert-login alert-danger">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <strong><i class="fa fa-exclamation-triangle"></i> <?php echo e($message); ?></strong>
                        </div>
                    <?php endif; ?>
                    
                    <div id="install-wrapper" class="text-center pb-7">
                        <span><i class="fa-brands fa-instalod"></i></span>
                        <p><?php echo e(config('app.name')); ?> <?php echo e(__('Installation Wizard')); ?></p>
                    </div>

       
                </div>

            

            </div>  
            <div class="form-group mb-0 text-center">                        
                <a href="<?php echo e(route('install.requirements')); ?>"  class="btn btn-primary pr-5 pl-5"><?php echo e(__('Start Installation')); ?></a>                                               
            </div>
                  
        </div>
         
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\uristemo\Laravel\openaidavinci-5.7\resources\views/install/index.blade.php ENDPATH**/ ?>