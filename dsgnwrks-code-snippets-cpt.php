<?php
/*
Plugin Name: Dsgnwrks Code Snippets CPT
Description: A code snippet custom post-type and shortcode for displaying your code snippets in your posts or pages.
Plugin URI: http://j.ustin.co/jAHRM3
Author: Jtsternberg
Author URI: http://about.me/jtsternberg
Donate link: http://j.ustin.co/rYL89n
Version: 1.0
*/

class CodeSnippitInit {

	private $plugin_name = 'Code Snippets CPT';
	private $cpt;

	function __construct() {

		define( 'DWSNIPPET_PATH', plugin_dir_path( __FILE__ ) );
		define( 'DWSNIPPET_URL', plugins_url('/', __FILE__ ) );

		// Custom Functions
		require_once( DWSNIPPET_PATH .'lib/functions.php' );

		// Snippet Post-Type Setup
		require_once( DWSNIPPET_PATH .'lib/CPT_Setup.php' );
		require_once( DWSNIPPET_PATH .'lib/Snippet_CPT_Setup.php' );
		$this->cpt = new Snippet_CPT_Setup( 'Code Snippet' );

		// Custom Taxonomy Setup
		require_once( DWSNIPPET_PATH .'lib/Snippet_Tax_Setup.php' );
		new Snippet_Tax_Setup( 'Snippet Category', 'Snippet Categories', array( $this->cpt->slug ) );
		new Snippet_Tax_Setup( 'Snippet Tag', '', array( $this->cpt->slug ), array( 'hierarchical' => false ) );
		$language = new Snippet_Tax_Setup( 'Language', '', array( $this->cpt->slug ),  array( 'public' => false, 'show_ui' => false ) );
		// Custom metabox for the programming languages taxonomy
		$language->init_select_box();

		// Snippet Shortcode Setup
		add_shortcode( 'snippet', array( $this, 'shortcode' ) );

		// Set default programming language taxonomy terms
		register_activation_hook( DWSNIPPET_PATH .'code-snippets-cpt.php', array( &$this, 'add_languages_event' ) );
		add_action( 'snippet_add_languages', array( &$this, 'add_languages' ) );


		// add_action( 'all_admin_notices', array( $this, 'testing_testing_testing' ) );
		add_action( 'wp_footer', array( $this, 'run_js' ) );
	}

	public function add_languages_event() {
		wp_schedule_single_event( ( time() + 2 ), 'snippet_add_languages' );
	}

	public function add_languages() {
		$languages = array( 'Python', 'HTML', 'CSS', 'JavaScript', 'PHP', 'SQL', 'Perl', 'Ruby' );
		foreach ( $languages as $language ) {
			wp_insert_term( $language, 'languages' );
		}
	}

	public function shortcode( $atts, $context ) {

		$args = array(
			'post_type' => 'code-snippets',
			'showposts' => 1,
			'post_status' => 'published'
		);

		if ( isset( $atts['id'] ) && is_numeric( $atts['id'] ) ) {
			$args['p'] = $atts['id'];
		} elseif ( isset( $atts['slug'] ) && is_numeric( $atts['slug'] ) ) {
			$args['name'] = $atts['slug'];
		}

		$content = '';
		$snippet = new WP_Query( $args );


		if( $snippet->have_posts() ) : while( $snippet->have_posts() ) : $snippet->the_post();

			$content = get_the_content();

		endwhile; endif;
		wp_reset_query();

		if ( !empty( $content ) ) {
			wp_enqueue_script( 'prettify' );
			wp_enqueue_style( 'prettify' );
			// wp_enqueue_script( 'syntax-highlighter-php', plugins_url('/lib/js/shBrushPhp.js', __FILE__ ), 'syntax-highlighter', '1.0', true );
		}

		return '<pre class="prettyprint linenums">'. htmlentities( $content ) .'</pre>';

	}

	public function run_js() {
		?>
		<script type="text/javascript">
			window.onload = function(){ prettyPrint(); };
		</script>
		<?php
	}


	public function testing_testing_testing() {
		echo '<div id="message" class="updated"><p>';
			echo '<pre>'. htmlentities( print_r( $this->cpt->labels->slug, true ) ) .'</pre>';
		echo '</p></div>';
	}

}

new CodeSnippitInit;

// Custom Post Types Icons
add_action('admin_head', 'dsgnwrks_html5presentation_icons');