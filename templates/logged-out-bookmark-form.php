<?php

/**
 * Notice to show when user is logged out.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-bookmarks/logged-out-bookmark-form.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager - Bookmarks
 * @category    Template
 * @version     1.2.0
 */

if (! defined('ABSPATH')) {
    exit;
}

global $wp;
?>
<form method="post" action="<?php echo defined('DOING_AJAX') ? '' : esc_url(remove_query_arg(array('page', 'paged'), add_query_arg($wp->query_string, '', home_url($wp->request)))); ?>" class="ae_job_card-bookmark_form <?php echo $is_bookmarked ? 'has-bookmark' : ''; ?>">
    <a class="add-bookmark" href="#" data-tooltip="<?php echo $is_bookmarked ? 'Update/Remove Bookmark' : 'Bookmark this Job'; ?>">
        <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M16.1269 3.04559C17.1353 3.16292 17.875 4.03284 17.875 5.0485V19.2504L11 15.8129L4.125 19.2504V5.0485C4.125 4.03284 4.86383 3.16292 5.87308 3.04559C9.27959 2.65017 12.7204 2.65017 16.1269 3.04559Z"
                stroke="#636363" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </a>
    <div class="bookmark-details">
        <?php if (! is_user_logged_in()) : ?>
            <div class="job-manager-form wp-job-manager-bookmarks-form">
                <div><a class="bookmark-notice" href="<?php echo apply_filters('job_manager_bookmark_form_login_url', wp_login_url(get_permalink())); ?>"><?php printf(__('Login to bookmark this %s', 'wp-job-manager-bookmarks'), $post_type->labels->singular_name); ?></a></div>
            </div>
        <?php else : ?>
            <div class="form-group">
                <label for="bookmark_notes"><?php _e('Notes:', 'wp-job-manager-bookmarks'); ?></label>
                <textarea name="bookmark_notes" id="bookmark_notes" cols="25" rows="3"><?php echo esc_textarea($note); ?></textarea>
            </div>
            <div>
                <?php wp_nonce_field('update_bookmark'); ?>
                <input type="hidden" name="bookmark_post_id" value="<?php echo absint($post->ID); ?>" />
                <input type="submit" class="submit-bookmark-button" name="submit_bookmark" value="<?php echo $is_bookmarked ? __('Update Bookmark', 'wp-job-manager-bookmarks') : __('Add Bookmark', 'wp-job-manager-bookmarks'); ?>" />
                <?php
                if ($is_bookmarked) {
                ?>
                    <a class="remove-bookmark" href="<?php echo wp_nonce_url(add_query_arg('remove_bookmark', absint($post->ID), add_query_arg($_GET, '', get_permalink())), 'remove_bookmark'); ?>">
                        Remove Bookmark
                    </a>
                <?php
                }
                ?>
                <span class="spinner" style="background-image: url(<?php echo includes_url('images/spinner.gif'); ?>);"></span>
            </div>
        <?php endif; ?>
    </div>
</form>