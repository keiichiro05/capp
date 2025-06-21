<?php
date_default_timezone_set('Asia/Jakarta');
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour < 18) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}
?>
<section class="content-header custom-dashboard-header">
    <div class="row">
        <div class="col-xs-12">
            
            <h1><?php echo htmlspecialchars($greeting); ?>, <?php echo htmlspecialchars($username); ?>! 
                <small>Welcome to Warehouse Dashboard</small>
            </h1>
            <p class="header-date-time">
                <i class="fa fa-calendar"></i> <?php echo date('l, F j, Y'); ?> 
                <span class="pull-right"><i class="fa fa-clock-o"></i> <span id="live-clock"><?php echo date('H:i:s'); ?></span></span>
            </p>
        </div>
    </div>
</section>