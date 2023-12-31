<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <form action="options.php" method="post">
    <?php 
        settings_fields( 'gil_group' );
        do_settings_sections( 'gil_page1' );            
        ?>        
        
        <a onclick="return confirm( 'Are you sure you want to clear the current state of the queue?' )" 
            class="link" href="<?php echo esc_attr( get_admin_url() . 'admin.php?page=gil_admin&clear-queue'); ?>"> 
            <?php _e('Clear the queue') ?> 
        </a>
        
        <?php submit_button( esc_html__( 'Save Settings', 'gil' ) ); ?>

    </form>
</div>