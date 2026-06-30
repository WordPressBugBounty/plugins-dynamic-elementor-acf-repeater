<?php
/**
 * Getting Started template for the plugin settings page.
 *
 * @package    Dynamic_Elementor_ACF_Repeater
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
?>

<style>
.dear-getting-started {
    display: flex;
    margin-top: 20px;
    gap: 30px;
}

.dear-main-content {
    flex: 2;
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.dear-sidebar {
    flex: 1;
}

.dear-support-box {
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.dear-step {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.dear-step:last-child {
    border-bottom: none;
}

.dear-step h4 {
    color: #1d2327;
    margin-bottom: 15px;
    font-size: 1.1em;
}

.dear-step ul {
    margin: 0;
    padding-left: 25px;
}

.dear-step > ul {
    border-left: 3px solid #e5e7eb;
    list-style: none;
}

.dear-step > ul > li {
    margin-bottom: 12px;
    position: relative;
}

.dear-step > ul > li::before {
    content: "•";
    color: #2271b1;
    position: absolute;
    left: -15px;
    top: 0;
}

.dear-step ul ul {
    list-style: circle;
    margin-top: 8px;
}

.dear-step ul ul li {
    margin-bottom: 8px;
    color: #50575e;
}

.dear-step strong {
    color: #1d2327;
    display: block;
    margin-bottom: 4px;
}

.dear-pro-features {
    background: #f0f6fc;
    padding: 20px;
    border-radius: 6px;
    margin-top: 30px;
}

.dear-pro-features h3 {
    color: #1d2327;
    margin-top: 0;
}

.dear-pro-features ul {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin: 0;
}

.dear-pro-features li {
    background: #fff;
    padding: 15px;
    border-radius: 4px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

</style>

<div class="dear-getting-started">
    <div class="dear-main-content">
        <h2><?php esc_html_e('Getting Started with Dynamic Elementor ACF Repeater', 'dynamic-elementor-acf-repeater'); ?></h2>
        
        <p><?php esc_html_e('This plugin allows you to display ACF repeater field content in Elementor using dynamic tags and loop grids. Follow these steps to get started:', 'dynamic-elementor-acf-repeater'); ?></p>


        <div class="dear-step">
            <h4><?php esc_html_e('1. Create ACF Repeater Field', 'dynamic-elementor-acf-repeater'); ?></h4>
            <ul>
                <li><?php esc_html_e('Create a new ACF field group', 'dynamic-elementor-acf-repeater'); ?></li>
                <li><?php esc_html_e('Add a Repeater field with your desired sub-fields (text, image, etc.)', 'dynamic-elementor-acf-repeater'); ?></li>
                <li><?php esc_html_e('Set location rules for where this field group should appear', 'dynamic-elementor-acf-repeater'); ?></li>
            </ul>
        </div>

        <div class="dear-step">
            <h4><?php esc_html_e('2. Populate Repeater Fields', 'dynamic-elementor-acf-repeater'); ?></h4>
            <ul>
                <li><?php esc_html_e('Edit a post or custom post type where your ACF Repeater field is available', 'dynamic-elementor-acf-repeater'); ?></li>
                <li><?php esc_html_e('Add entries to the repeater, filling out the sub-fields', 'dynamic-elementor-acf-repeater'); ?></li>
                <li><?php esc_html_e('Save your changes', 'dynamic-elementor-acf-repeater'); ?></li>
            </ul>
        </div>

        <div class="dear-step">
            <h4><?php esc_html_e('3. Create Loop Item Template', 'dynamic-elementor-acf-repeater'); ?></h4>
            <ul>
                <li style="color: #943b7c; font-weight: bold;"><?php esc_html_e('Before adding Dynamic Tagc Content to your elements, you must select the ACF Repeater field in the Elementor Loop Item Page Settings. Otherwise, you will not see them in the dynamic tags.', 'dynamic-elementor-acf-repeater'); ?></li>
                <li><?php esc_html_e('In Elementor, go to Templates → Add New → Loop Item', 'dynamic-elementor-acf-repeater'); ?></li>
                <li><?php esc_html_e('In Elementor Loop Item Page Settings, select your ACF Repeater field', 'dynamic-elementor-acf-repeater'); ?></li>
                <li><?php esc_html_e('Design your loop item template using ACF Repeater Dynamic Tags', 'dynamic-elementor-acf-repeater'); ?></li>
            </ul>
        </div>

        <div class="dear-step">
            <h4><?php esc_html_e('4. Set Up Loop Grid Widget', 'dynamic-elementor-acf-repeater'); ?></h4>
            <ul>
                <li><?php esc_html_e('Add a Loop Grid widget to an Elementor template', 'dynamic-elementor-acf-repeater'); ?></li>
                <li><?php esc_html_e('Select your Loop Item template in the Layout section', 'dynamic-elementor-acf-repeater'); ?></li>
                <li><?php esc_html_e('Enable "Use ACF Repeater" in Query settings', 'dynamic-elementor-acf-repeater'); ?></li>
                <li><?php esc_html_e('Select your ACF Repeater field and configure display settings', 'dynamic-elementor-acf-repeater'); ?></li>
            </ul>
        </div>

        <?php if (function_exists('earluna_fs') && earluna_fs()->can_use_premium_code__premium_only()): ?>
        <div class="dear-step">
            <h4><?php esc_html_e('5. Configure Pro Features', 'dynamic-elementor-acf-repeater'); ?></h4>
            <ul>
                <li>
                    <strong><?php esc_html_e('ACF Relationship:', 'dynamic-elementor-acf-repeater'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('In Loop Grid settings, find the "ACF Relationship" section', 'dynamic-elementor-acf-repeater'); ?></li>
                        <li><?php esc_html_e('Enable the ACF Relationship feature', 'dynamic-elementor-acf-repeater'); ?></li>
                        <li><?php esc_html_e('Select the ACF Relationship field to use', 'dynamic-elementor-acf-repeater'); ?></li>
                        <li><?php esc_html_e('Choose how to display related content in your loop items', 'dynamic-elementor-acf-repeater'); ?></li>
                        <li><?php esc_html_e('Customize the query to filter or sort related posts as needed', 'dynamic-elementor-acf-repeater'); ?></li>
                    </ul>
                </li>
                <li>
                    <strong><?php esc_html_e('Lightbox:', 'dynamic-elementor-acf-repeater'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('In Loop Grid settings, find the "Repeaer Lightbox" section', 'dynamic-elementor-acf-repeater'); ?></li>
                        <li><?php esc_html_e('Enable the lightbox feature', 'dynamic-elementor-acf-repeater'); ?></li>
                        <li><?php esc_html_e('Customize lightbox settings as needed', 'dynamic-elementor-acf-repeater'); ?></li>
                    </ul>
                </li>
                <li>
                    <strong><?php esc_html_e('Lightbox Visibility:', 'dynamic-elementor-acf-repeater'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('For elements with ACF Repeater Dynamic Tags, a "Lightbox Visibility" control is available', 'dynamic-elementor-acf-repeater'); ?></li>
                        <li><?php esc_html_e('Options include:', 'dynamic-elementor-acf-repeater'); ?>
                            <ul>
                                <li><?php esc_html_e('Default: Element is visible in both loop and lightbox', 'dynamic-elementor-acf-repeater'); ?></li>
                                <li><?php esc_html_e('Hide in Lightbox: Element is hidden when viewed in the lightbox', 'dynamic-elementor-acf-repeater'); ?></li>
                                <li><?php esc_html_e('Show Only in Lightbox: Element is hidden in the loop but visible in the lightbox', 'dynamic-elementor-acf-repeater'); ?></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li>
                    <strong><?php esc_html_e('Swiper Integration:', 'dynamic-elementor-acf-repeater'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('In Loop Grid settings, locate the "Slider" section', 'dynamic-elementor-acf-repeater'); ?></li>
                        <li><?php esc_html_e('Enable the slider feature', 'dynamic-elementor-acf-repeater'); ?></li>
                        <li><?php esc_html_e('Adjust slider settings to your preferences', 'dynamic-elementor-acf-repeater'); ?></li>
                    </ul>
                </li>
                <li>
                    <strong><?php esc_html_e('Filtering:', 'dynamic-elementor-acf-repeater'); ?></strong>
                    <ul>
                        <li><?php esc_html_e('In Loop Grid settings, find the "Repeater Filter" section', 'dynamic-elementor-acf-repeater'); ?></li>
                        <li><?php esc_html_e('Enable filtering', 'dynamic-elementor-acf-repeater'); ?></li>
                        <li><?php esc_html_e('Choose the ACF field to use for filtering', 'dynamic-elementor-acf-repeater'); ?></li>
                        <li><?php esc_html_e('Customize filter appearance and behavior', 'dynamic-elementor-acf-repeater'); ?></li>
                    </ul>
                </li>
            </ul>
        </div>
        <?php else: ?>
        <div class="dear-pro-features">
            <h3><?php esc_html_e('Pro Features', 'dynamic-elementor-acf-repeater'); ?></h3>
            <ul>
            <li>
                    <strong><?php esc_html_e('Enable Lightbox Functionality', 'dynamic-elementor-acf-repeater'); ?></strong><br>
                    <?php esc_html_e('Transform your repeater loop grid content with an elegant lightbox display', 'dynamic-elementor-acf-repeater'); ?>
                </li>
                <li>
                    <strong><?php esc_html_e('Advanced Swiper Integration', 'dynamic-elementor-acf-repeater'); ?></strong><br>
                    <?php esc_html_e('Create stunning carousel experiences with smooth swiper navigation', 'dynamic-elementor-acf-repeater'); ?>
                </li>
                <li>
                    <strong><?php esc_html_e('Advanced Field Support', 'dynamic-elementor-acf-repeater'); ?></strong><br>
                    <?php esc_html_e('Use file, gallery, and relationship fields in your repeaters', 'dynamic-elementor-acf-repeater'); ?>
                </li>
                <li>
                    <strong><?php esc_html_e('Advanced Filtering', 'dynamic-elementor-acf-repeater'); ?></strong><br>
                    <?php esc_html_e('Add dynamic filters to your loop grid content', 'dynamic-elementor-acf-repeater'); ?>
                </li>
                <li>
                    <strong><?php esc_html_e('Visibility Control', 'dynamic-elementor-acf-repeater'); ?></strong><br>
                    <?php esc_html_e('Control element visibility in grid vs lightbox views', 'dynamic-elementor-acf-repeater'); ?>
                </li>
                <li>
                    <strong><?php esc_html_e('Relationship Fields Query', 'dynamic-elementor-acf-repeater'); ?></strong><br>
                    <?php esc_html_e('Display related content from across your site', 'dynamic-elementor-acf-repeater'); ?>
                </li>
            </ul>
        </div>
        <?php endif; ?>
    </div>

    <div class="dear-sidebar">
        <div class="dear-support-box">
            <h3><?php esc_html_e('Need Help?', 'dynamic-elementor-acf-repeater'); ?></h3>
            <p><?php esc_html_e('Check out these resources:', 'dynamic-elementor-acf-repeater'); ?></p>
            <ul>
                <li><a href="https://wordpress.org/support/plugin/dynamic-elementor-acf-repeater/" target="_blank"><?php esc_html_e('Support Forum', 'dynamic-elementor-acf-repeater'); ?></a></li>
                <li><a href="https://calculabs.github.io/elementor-acf-repeater-docs" target="_blank"><?php esc_html_e('Documentation', 'dynamic-elementor-acf-repeater'); ?></a></li>
            </ul>
        </div>

        <?php if (function_exists('earluna_fs') && !earluna_fs()->can_use_premium_code__premium_only()): ?>
        <div class="dear-support-box">
            <h3><?php esc_html_e('Upgrade to Pro', 'dynamic-elementor-acf-repeater'); ?></h3>
            <p><?php esc_html_e('Get access to all premium features and priority support.', 'dynamic-elementor-acf-repeater'); ?></p>
            <p><a href="<?php echo esc_url(earluna_fs()->get_upgrade_url()); ?>" class="button button-primary"><?php esc_html_e('Upgrade Now', 'dynamic-elementor-acf-repeater'); ?></a></p>
        </div>
        <?php endif; ?>
    </div>
</div> 