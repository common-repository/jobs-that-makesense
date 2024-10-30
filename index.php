<?php
/*
Plugin Name: jobs_that_makesense
Description: Afficher les annonces de votre espace recruteur jobs.makesense.org
Version: 1.3.2
Contributors: jcmakesense
Tags: jobs
Stable Tag: 1.3.2
Tested up to: 6.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

namespace JobsThatMakesense;

class PluginJobsList {
    static $instance = false;
    // private $api_base = 'http://localhost:3000/api/v1';
    // private $api_base = 'http://host.docker.internal:3000/api/v1';
    private $api_base = 'https://jobs.makesense.org/api/v1';
    private $domain;

    /**
     * Register init action
     */
    public function __construct () {
        add_action('init', array($this, 'init'));
        $this->domain = parse_url(get_site_url(), PHP_URL_HOST);
    }

    /**
     * Get singleton instance
     * @return PluginJobsList
     */
    public static function get_instance() {
        if ( !self::$instance )
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * Register actions and shortcodes
     */
    public function init () {
        add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts') );
        add_action( 'wp_footer', array( $this, 'print_modal' ) );
        add_action( 'wp_ajax_jtms_jobs_pagination', array( $this, 'jobs_pagination') );
        add_action( 'wp_ajax_nopriv_jtms_jobs_pagination', array( $this, 'jobs_pagination') );
        add_action( 'wp_ajax_jtms_children_pagination', array( $this, 'children_pagination') );
        add_action( 'wp_ajax_nopriv_jtms_children_pagination', array( $this, 'children_pagination') );

        add_shortcode( 'jobs_that_makesense', array( $this, 'jobs_shortcode' ) );
        add_shortcode( 'jobs_that_makesense_jobs', array( $this, 'jobs_shortcode' ) );
        add_shortcode( 'jobs_that_makesense_projects', array( $this, 'children_shortcode' ) );
        add_shortcode( 'jobs_that_makesense_children', array( $this, 'children_shortcode' ) );
    }

    function plugin_version () {
        $plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
        return $plugin_data['Version'];
    }

    /**
     * Enqueue the necessary js and css
     */
    public function register_scripts () {
        $plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
        $plugin_version = $plugin_data['Version'];
        wp_enqueue_style( 'jobs-that-makesense-css', plugins_url( '/css/styles.css', __FILE__ ), null, $plugin_version);
        wp_enqueue_script( 'jobs-that-makesense-js', plugins_url( '/js/scripts.js', __FILE__ ), null, $plugin_version, true);
        wp_localize_script(
            'jobs-that-makesense-js',
            'jobs_that_makesense_ajax_object',
            array(
                'ajaxurl' => admin_url( 'admin-ajax.php' )
            )
        );
    }

    /**
     * Print the modal markup
     */
    function print_modal($atts) {
        ?>
        <div class="jobs-that-makesense_modal" id="jobs-that-makesense_modal">
            <div class="jobs-that-makesense_modal-bg jobs-that-makesense_modal-exit"></div>
            <div class="jobs-that-makesense_modal-container">
            <iframe id="jobs-that-makesense_modal-iframe"
                width="100%"
                height="100%"
                src="">
            </iframe>
                <button class="jobs-that-makesense_modal-close jobs-that-makesense_modal-exit"><svg style="stroke: #000;" data-v-779356a2="" data-v-905240ea="" role="img" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-x icon--color-inherit"><title id="x"></title><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
            </div>
        </div>
        <?php
    }

    /**
     * Render the jobs list shortcode
     */
    public function jobs_shortcode ($atts) {
        $options = shortcode_atts( array(
            'number' => 100,
            'direction' => 'largeur',
            'key' => '',
            'id' => '',
            'child-jobs' => 'false',
            'columns' => '1',
            'fields' => '',
            'text-color' => '',
            'border-color' => '',
            'over-background-color' => '',
            'paginate' => 24
        ), $atts );

        $key = $options['key'];
        $id = $options['id'];
        $number = $options['number'];
        $direction = $options['direction'];
        $column = $options['columns'];
        $extraFields = explode(',', $options['fields']);
        $textColor = $options['text-color'];
        $borderColor = $options['border-color'];
        $overBackgroundColor = $options['over-background-color'];
        $childJobs = $options['child-jobs'];
        $paginate = $options['paginate'];

        ob_start();

        $parameters = array(
            'key' => $key
        );

        $plugin_version = $this->plugin_version();

        $url = $this->api_base . "/projects/" . $id . "/jobs?locale=fr-FR&token=" . $key . '&version=' . $plugin_version . "&withChildren=" . $childJobs . ($paginate != false ? "&jobsPerPage=" . $paginate : "");

        $response = wp_remote_get( $url );
        $body     = wp_remote_retrieve_body( $response );

        $result = json_decode($body);

        $styles = array();

        if($textColor && $textColor != '')
            array_push($styles, '.job {color: ' . $textColor . ' !important;}');

        if($borderColor && $borderColor != '')
            array_push($styles, '.job {border-color: ' . $borderColor . ' !important;}');

        if($overBackgroundColor && $overBackgroundColor != '')
            array_push($styles, '.job:hover { background-color: ' . $overBackgroundColor . ' !important;}');

        if(!isset($result->success) || $result->success === false)
            echo esc_attr($result->message);
        else
        {
            $option_key = 'jobs_that_makesense_api_key_' . $id;
            $option = get_option( $option_key );
            if (!$option) {
                add_option( $option_key, $key );
            }
            else {
                update_option( $option_key, $key );
            }
            $jobs = $result->jobs;
            $total = $result->total;
            ?>
            <div class="jobs-that-makesense_list jobs-that-makesense_count-<?php echo esc_attr($number); ?> jobs-that-makesense_<?php echo esc_attr($direction); ?> <?php echo (isset($column) && $column != '')?'jobs-that-makesense_column jobs-that-makesense_column-' . esc_attr($column):''; ?> clearfixe" data-fields="<?php echo esc_attr($options['fields']); ?>" data-paginate="<?php echo esc_attr($paginate); ?>" data-child-jobs="<?php echo esc_attr($childJobs); ?>" data-id="<?php echo esc_attr($id); ?>">
                <?php $this->render_jobs($jobs, $result->redirection, $extraFields, $paginate, $total, 1); ?>
            </div>
            <?php if(count($styles) > 0): ?>
                <style type="text/css">
                    <?php

                    foreach ($styles as $value){
                        echo '.jobs-that-makesense_list ' . esc_attr($value);
                    }

                    ?>
                </style>
            <?php endif;
        }

        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    public function jobs_pagination () {
        $paginate = $_GET['paginate'];
        $page = $_GET['page'];
        $fields = $_GET['fields'];
        $childJobs = $_GET['child-jobs'];
        $id = $_GET['id'];
        $extraFields = explode( ',', $fields );
        $key = get_option( 'jobs_that_makesense_api_key_' . $id );

        $plugin_version = $this->plugin_version();

        $url = $this->api_base . "/projects/" . $id . "/jobs?locale=fr-FR&token=" . $key . '&version=' . $plugin_version . "&withChildren=" . $childJobs . "&jobsPerPage=" . $paginate . "&page=" . $page;

        $response = wp_remote_get( $url );
        $body     = wp_remote_retrieve_body( $response );

        $result = json_decode($body);

        $this->render_jobs($result->jobs, $result->redirection, $extraFields, $paginate, $result->total, $page);
        die();
    }

    /**
     * Render the jobs list items
     */
    function render_jobs ($jobs, $redirection, $extraFields, $jobsPerPage, $totalJobs, $currentPage) {
        $locale = 'fr_FR';

        if(function_exists('get_locale'))
            $locale = get_locale();
        $published_label = 'Publiée le';
        if($locale == 'en_US')
            $published_label = 'Posted';

        foreach ($jobs as $job)
        {
            $job_address = isset($job->location->formatted_address) ? $job->location->formatted_address : $job->location->formattedAddress;
            ?>
            <a class="job" target="_blank" href="<?php echo esc_url($job->link . "?source=wordpress-integration&utm_source=wordpress-integration&utm_campaign=" . $this->domain); ?>" data-redirection="<?php echo esc_attr($redirection)?>" data-id="<?php echo esc_attr($job->id); ?>" id="jobs-that-makesense_<?php echo esc_attr($job->id); ?>">
                <?php if(in_array('logotype', $extraFields) || in_array('name', $extraFields)): ?>
                <div class="job__project">
                    <?php if(in_array('logotype', $extraFields)): ?><div class="project__logotype"><img src="<?php echo esc_url($job->projectLogotype); ?>"/></div><?php endif; ?>
                    <?php if(in_array('name', $extraFields)): ?><div class="project__name"><?php echo esc_attr($job->projectName); ?></div><?php endif; ?>
                </div>
                <?php endif; ?>
                <h4 class="job__title"><?php echo esc_attr($job->title); ?></h4>
                <p class="job__metas"><?php echo esc_attr(implode(', ', $job->contracts)) ?> - <?php echo esc_attr($job_address) ?></p>
                <p class="job__company"><?php echo esc_attr($published_label); ?> <span class="job__date"><?php echo esc_attr(date("d/m/Y", strtotime($job->createdAt))); ?></span></p>
            </a>
            <?php
        }
        ?>
        <div class="jobs-that-makesense_pagination">
        <?php
            if (is_numeric($jobsPerPage) && $jobsPerPage != 0) {
                echo paginate_links([
                    'base' => '#',
                    'total' => ceil($totalJobs / $jobsPerPage),
                    'current' => $currentPage
                ]);
            }
        ?>
        </div>
        <?php
    }


    /**
     * Render the children list shortcode
     */
    public function children_shortcode ($atts, $content, $name) {
        $options = shortcode_atts( array(
            'key' => '',
            'id' => '',
            'direction' => 'largeur',
            'columns' => '1',
            'text-color' => '',
            'border-color' => '',
            'over-background-color' => '',
            'featured' => '',
            'paginate' => 24
        ), $atts );


        $key = $options['key'];
        $id = $options['id'];
        $direction = $options['direction'];
        $column = $options['columns'];
        $textColor = $options['text-color'];
        $borderColor = $options['border-color'];
        $overBackgroundColor = $options['over-background-color'];
        $featured = $options['featured'];
        $paginate = $options['paginate'];

        ob_start();

        $parameters = array(
            'key' => $key
        );

        $locale = 'fr_FR';
        if(function_exists('get_locale'))
        $locale = get_locale();

        $plugin_version = $this->plugin_version();

        $url = $this->api_base . "/projects/" . $id . "/children?locale=fr-FR&token=" . $key . '&version=' . $plugin_version . ($paginate != false ? "&childrenPerPage=" . $paginate : "") . "&featured=" . $featured;

        $response = wp_remote_get( $url );
        $body     = wp_remote_retrieve_body( $response );

        $result = json_decode($body);

        $styles = array();

        if($textColor && $textColor != '')
            array_push($styles, '.project {color: ' . $textColor . ' !important;}');

        if($borderColor && $borderColor != '')
            array_push($styles, '.project {border-color: ' . $borderColor . ' !important;}');

        if($overBackgroundColor && $overBackgroundColor != '')
            array_push($styles, '.project:hover { background-color: ' . $overBackgroundColor . ' !important;}');


        if(!isset($result->success) || $result->success === false)
            echo esc_attr($result->message);
        else
        {
            $option_key = 'jobs_that_makesense_api_key_' . $id;
            $option = get_option( $option_key );
            if (!$option) {
                add_option( $option_key, $key );
            }
            else {
                update_option( $option_key, $key );
            }
            $projects = $result->children;


            ?>
                <div class="jobs-that-makesense_list jobs-that-makesense_children_list jobs-that-makesense_<?php echo esc_attr($direction); ?> <?php echo (isset($column) && $column != '')?'jobs-that-makesense_column jobs-that-makesense_column-' . esc_attr($column):''; ?> clearfixe" data-paginate="<?php echo esc_attr($paginate); ?>" data-id="<?php echo esc_attr($id); ?>" data-featured="<?php echo esc_attr($featured); ?>">
                <?php $this->render_children($projects, $paginate, $result->total, 1); ?>
                </div>
                <?php if(count($styles) > 0): ?>
                        <style type="text/css">
                            <?php

                            foreach ($styles as $value){
                                echo '.jobs-that-makesense_list ' . esc_attr($value);
                            }

                            ?>
                        </style>
                    <?php endif; ?>
            <?php
        }


        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    public function children_pagination () {
        $paginate = $_GET['paginate'];
        $page = $_GET['page'];
        $fields = $_GET['fields'];
        $childJobs = $_GET['child-jobs'];
        $featured = $_GET['featured'];
        $id = $_GET['id'];
        $extraFields = explode( ',', $fields );
        $key = get_option( 'jobs_that_makesense_api_key_' . $id );

        $plugin_version = $this->plugin_version();

        $url = $this->api_base . "/projects/" . $id . "/children?locale=fr-FR&token=" . $key . '&version=' . $plugin_version . ($paginate != false ? "&childrenPerPage=" . $paginate : "") . "&page=" . $page. "&featured=" . $featured;

        $response = wp_remote_get( $url );
        $body     = wp_remote_retrieve_body( $response );

        $result = json_decode($body);
        $this->render_children ($result->children, $paginate, $result->total, $page);
        die();
    }

    /**
     * Render the children list items
     */
    function render_children ($children, $childrenPerPage, $total, $currentPage) {
        foreach ($children as $project)
        {
            ?>
            <a class="project" target="_blank" href="<?php echo esc_url($project->link . "?source=wordpress-integration&utm_source=wordpress-integration&utm_campaign=" . $this->domain); ?>" id="jobs-that-makesense_<?php echo esc_attr($project->id); ?>">
                <div class="project__visuals" style="background-image: url(<?php echo esc_url($project->cover) ?>);">
                    <img class="project__logotype" src="<?php echo esc_url($project->logotype); ?>" />
                </div>
                <div class="project__informations">
                <h4 class="project__name"><?php echo esc_attr($project->name); ?></h4>
                <div class="project__mission"><?php echo esc_attr($project->mission); ?></div>
                </div>
            </a>
            <?php
        }
        ?>
        <div class="jobs-that-makesense_pagination">
        <?php
            if (is_numeric($childrenPerPage) && $childrenPerPage != 0) {
                echo paginate_links([
                    'base' => '#',
                    'total' => ceil($total / $childrenPerPage),
                    'current' => $currentPage
                ]);
            }
        ?>
        </div>
        <?php
    }
}

PluginJobsList::get_instance();
