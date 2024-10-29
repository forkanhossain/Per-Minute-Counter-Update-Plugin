<?php
/*
Plugin Name: Per Minute Counter Update
Plugin URI: 
Description: A counter that increments by a random amount (8-9) per minute and saves it in the database.
Version: 1.0
Author: WebCityLab team
Author URI: https://webcitylab.com/
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Initialize the counter and timestamp on plugin activation
function allied_daily_counter_activate() {
    if (get_option('allied_daily_counter_value') === false) {
        update_option('allied_daily_counter_value', 905369);
    }
    if (!wp_next_scheduled('allied_minute_increment')) {
        wp_schedule_event(time(), 'every_minute', 'allied_minute_increment');
    }
}
register_activation_hook(__FILE__, 'allied_daily_counter_activate');

// Deactivation: Clear the scheduled event
function allied_daily_counter_deactivate() {
    $timestamp = wp_next_scheduled('allied_minute_increment');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'allied_minute_increment');
    }
}
register_deactivation_hook(__FILE__, 'allied_daily_counter_deactivate');

// Register a custom interval for one minute
function allied_custom_cron_intervals($schedules) {
    $schedules['every_minute'] = array(
        'interval' => 60, // 1 minute
        'display' => __('Every Minute')
    );
    return $schedules;
}
add_filter('cron_schedules', 'allied_custom_cron_intervals');

// Increment the counter every minute
function allied_minute_increment() {
    $current_value = get_option('allied_daily_counter_value', 905369);
    $increment_amount = rand(8, 9); // Random increment between 8 and 9
    $new_value = $current_value + $increment_amount;
    update_option('allied_daily_counter_value', $new_value);
}
add_action('allied_minute_increment', 'allied_minute_increment');

// Shortcode to display the counter
function allied_daily_counter_display() {
    $current_value = get_option('allied_daily_counter_value', 905369); // Default to 905369 if option is missing
    $formatted_value = number_format($current_value, 0, '', ',');

    // Output counter with JavaScript for animation
    $output = "
        <div id='allied-daily-counter'>
            <p class='allied-transactions-counter' data-count='{$formatted_value}'>1</p>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const counterElement = document.querySelector('.allied-transactions-counter');
                const targetCount = parseInt(counterElement.getAttribute('data-count').replace(',', ''));
                let count = 1;
                const duration = 2000;
                const increment = Math.ceil(targetCount / (duration / 10));

                function updateCounter() {
                    count += increment;
                    if (count > targetCount) count = targetCount;
                    counterElement.textContent = count.toLocaleString();

                    if (count < targetCount) {
                        setTimeout(updateCounter, 10);
                    }
                }
                updateCounter();
            });
        </script>
    ";
    
    return $output;
}
add_shortcode('allied_daily_counter', 'allied_daily_counter_display');

// Optional: Add admin menu for manual counter reset
function allied_daily_counter_menu() {
    add_menu_page('Allied Counter Settings', 'Allied Counter Settings', 'manage_options', 'allied-daily-counter', 'allied_daily_counter_settings_page');
}
add_action('admin_menu', 'allied_daily_counter_menu');

// Settings page content
function allied_daily_counter_settings_page() {
    if (isset($_POST['set_allied_counter_value'])) {
        update_option('allied_daily_counter_value', intval($_POST['allied_counter_value']));
        echo "<div class='updated'><p>Counter value updated!</p></div>";
    }

    $current_value = get_option('allied_daily_counter_value', 905369);
    ?>
    <div class="wrap">
        <h1>Allied Counter Settings</h1>
        <form method="post" action="">
            <label for="allied_counter_value">Set Counter Value:</label>
            <input type="number" name="allied_counter_value" value="<?php echo esc_attr($current_value); ?>" />
            <input type="submit" name="set_allied_counter_value" value="Set Counter" class="button button-primary" />
        </form>
        <p>Add this shortcode [allied_daily_counter] where you want to show the counter</p>
    </div>
    <?php
}
