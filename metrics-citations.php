<?php

/**

 * Plugin Name: Metrics and Citations (ACF)

 * Description: Adds a Metrics and Citations drawer with views, downloads, Altmetric, citations, and export links using ACF fields.

 * Version: 3.1

 * Author: Arman

 */



if (!defined('ABSPATH')) exit;



/* ---------------------------

 * Track Post Views

 * --------------------------*/

function mc_set_post_views($postID) {

    $count_key = 'mc_post_views_count';

    $count = get_post_meta($postID, $count_key, true);

    $count = $count === '' ? 0 : intval($count);

    $count++;

    update_post_meta($postID, $count_key, $count);

}

add_action('wp_head', function() {

    if (is_single()) {

        global $post;

        mc_set_post_views($post->ID);

    }

});



function mc_get_post_views($postID) {

    return intval(get_post_meta($postID, 'mc_post_views_count', true) ?: 0);

}



/* ---------------------------

 * Track Downloads Automatically

 * --------------------------*/

function mc_track_downloads_auto() {

    if (is_attachment()) {

        global $post;

        if ($post && $post->post_parent) {

            $parent_id = $post->post_parent;

            $count = intval(get_post_meta($parent_id, 'mc_post_download_count', true) ?: 0);

            update_post_meta($parent_id, 'mc_post_download_count', $count + 1);

        }

    }

}

add_action('template_redirect', 'mc_track_downloads_auto');



function mc_get_post_downloads($postID) {

    return intval(get_post_meta($postID, 'mc_post_download_count', true) ?: 0);

}



/* ---------------------------

 * Render Metrics Drawer Content

 * --------------------------*/

function mc_render_metrics_content($post_id = null) {

    if (!$post_id) $post_id = get_the_ID();

    if (!$post_id) return '';



    $views = mc_get_post_views($post_id);

    $downloads = mc_get_post_downloads($post_id);



    // ACF fields

    $journal = get_field('journal_name', $post_id);

    $doi = get_field('doi', $post_id);

    $publication = get_field('publication_usage', $post_id);

    $citations = get_field('citations', $post_id);        // repeater

    $exports = get_field('export_links', $post_id);       // repeater



    ob_start();

    ?>

    <div class="mc-metrics-box">

        <h3>Metrics and Citations</h3>

        <hr>

        <h4>Journal metrics</h4>

        <p>This article was published in <?php echo esc_html($journal ?: 'My WordPress Journal'); ?>.</p>

        <hr>

        <h4>Publication usage</h4>

        <p><strong>Total views & downloads:</strong> <?php echo intval($views); ?></p>

        <p> <?php echo esc_html($publication); ?></p>



        <?php if ($doi): ?>

            <h4>Altmetric</h4>

            <p>See the impact this article is making through the number of times it’s been read, and the Altmetric Score.</p>

            <div class="altmetric-embed" data-doi="<?php echo esc_attr($doi); ?>"></div>

        <?php endif; ?>



        <?php if ($citations): ?>

            <div class="mc-citations">

                <h4>Cite </h4>

                <table class="mc-citations-table">

                    <tbody>

                        <?php foreach ($citations as $c): ?>

                            <tr>

                                <td class="mc-style"><?php echo esc_html($c['style']); ?></td>

                                <td class="mc-text"><?php echo esc_html($c['citation_text']); ?></td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>



        <?php if ($exports): ?>

            <div class="mc-export-links">

                <?php foreach ($exports as $e): ?>

                    <a href="<?php echo esc_url($e['url']); ?>" target="_blank" rel="noopener"><?php echo esc_html($e['label']); ?></a>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

    <?php

    return ob_get_clean();

}



/* ---------------------------

 * Enqueue CSS + JS

 * --------------------------*/

add_action('wp_enqueue_scripts', function(){
    if (is_single() || is_page()) {
        wp_enqueue_style('mc-drawer-css', plugin_dir_url(__FILE__) . 'assets/css/mc-drawer.css', array(), '1.1.0');
        wp_enqueue_script('mc-drawer-js', plugin_dir_url(__FILE__) . 'assets/js/mc-drawer.js', array(), '1.1.0', true);
    }
});


/* ---------------------------

 * Drawer HTML + Toolbar

 * --------------------------*/



add_action('wp_footer', function() {
    if (is_single() || is_page()) {
        echo '
        <div class="mc-bar">
            <span id="open-mc-drawer" class="mc-btn"> Metrics and citations</span>
        </div>
        <div id="mc-drawer" class="mc-drawer">
            <button class="mc-close">✖</button>
            ' . mc_render_metrics_content() . '
        </div>
        ';
    }
});


// Always load 4 default citation rows on new posts/pages (editable by user)
add_filter('acf/load_value/name=citations', function($value, $post_id, $field) {
    // Keep existing values if already set (don’t overwrite user edits)

    // If there are saved values in the DB, return them
    if (!empty($value)) {
        return $value;
    }

    // Check if this post already exists in DB (not a brand new one)
    if (get_post_status($post_id) !== 'auto-draft') {
        return $value; // keep it empty if user deleted all rows
    }

    // Default citation rows with field keys
    return [
        [
            'field_citation_style' => 'MLA',
            'field_citation_text'  => ' Falagas, Matthew E., et al. "Comparison of PubMed, Scopus, web of science, and Google scholar: strengths and
weaknesses." The FASEB journal 22.2 (2008): 338-342.'
        ],
        [
            'field_citation_style' => 'APA',
            'field_citation_text'  => 'Falagas, M. E., Pitsouni, E. I., Malietzis, G. A., & Pappas, G. (2008). Comparison of PubMed, Scopus, web of science, and Google scholar: strengths and weaknesses. The FASEB journal, 22(2), 338-342.'
        ],
        [
            'field_citation_style' => 'Chicago',
            'field_citation_text'  => 'Falagas, Matthew E., Eleni I. Pitsouni, George A. Malietzis, and Georgios Pappas. "Comparison of PubMed, Scopus, web of science, and Google scholar: strengths and weaknesses." The FASEB journal 22, no. 2 (2008): 338-342.'
        ],
        [
            'field_citation_style' => 'Harvard',
            'field_citation_text'  => 'Falagas, M.E., Pitsouni, E.I., Malietzis, G.A. and Pappas, G., 2008. Comparison of PubMed, Scopus, web of science, and Google scholar: strengths and weaknesses. The FASEB journal, 22(2), pp.338-342.'
        ]
    ];
}, 10, 3);





