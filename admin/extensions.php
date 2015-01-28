<?php
/**
 * Class control output of AnsPress extensions
 * @package AnsPress
 * @since 2.0.0-alpha2
 */
class AnsPress_Extensions
{
    /**
     * Hold the result fetched from AnsPress project site
     * @var array
     */
    private $result ;

    private $API_url = 'http://wp3.in/json_view';

    public function __construct()
    {
        $this->API_url = add_query_arg(array('main_product' => 'anspress'), $this->API_url);
        $this->set_results();
    }

    /**
     * Fetch the results from AnsPress project site and cache it locally
     * @since 2.0.0-alpha2
     * @return void
     */
    public function set_results()
    {
        $fetch = get_transient( 'anspress_extensions_fetch' );

        if($fetch === false){
            $fetch = wp_remote_get( $this->API_url);
            set_transient('anspress_extensions_fetch', $fetch, 60 * 5);
        }

        if(! is_wp_error($fetch)){
            $this->result = json_decode($fetch['body']);
        }
        
    }

    /**
     * Print lists of extensosn
     * @return void
     * @since 2.0.0-alpha2
     */
    public function extensions_lists()
    {
        $this->extension_cards();
    }

    public function extension_cards()
    {
        global $wp_version;
        if(!empty($this->result) && is_object($this->result)){
            foreach($this->result->extensions as $ext):
            ?>
            <div class="plugin-card">
                <div class="plugin-card-top">
                    <a class="plugin-icon" href="<?php echo $ext->plugin_link ?>" target="_blank"><img src="<?php echo $ext->thumb_url ?>"></a>
                    <div class="name column-name">
                        <h4><a class="thickbox" target="_blank" href="<?php echo $ext->plugin_link ?>"><?php echo $ext->plugin_name ?></a></h4>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <li>
                                <a target="_blank" href="<?php echo $ext->plugin_link ?>" class="install-now button"><?php _e('Download', 'ap') ?></a>
                            </li>
                     </div>
                    <div class="desc column-description">
                        <p><?php echo substr(strip_tags($ext->description), 0, 100) ?></p>
                        <p class="authors"> <cite><?php printf(__('By %s', 'ap'), '<a href="'.$ext->author_link.'">'. $ext->author.'</a>') ?></cite></p>
                    </div>
                </div>
                <div class="plugin-card-bottom">
                    <div class="column-updated">
                        <strong><?php _e('Last Updated:', 'ap') ?></strong><span><?php echo human_time_diff( $ext->last_updated, current_time('timestamp') ) . ' ago'; ?></span>
                    </div>
                    <div class="column-compatibility">
                        <?php if($wp_version > $ext->tested_upto): ?>
                            <span class="compatibility-untested"><?php _e('Untested with your version of WordPress', 'ap') ?></span>
                        <?php else: ?>
                            <span class="compatibility-compatible"><?php _e('<strong>Compatible</strong> with your version of WordPress', 'ap') ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
            endforeach;

        }
    }
}
