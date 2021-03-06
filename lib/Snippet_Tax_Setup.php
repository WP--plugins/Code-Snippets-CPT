<?php
/**
 * Plugin class for Code Snipet CPT and it's associated taxonomies.
 *
 */
class Snippet_Tax_Setup {

	private $singular;
	private $plural;
	private $slug;
	private $object_types;
	private $args;

	public function __construct( $singular, $plural = '', $object_types, $args = array() ) {

		if( ! $singular )
			wp_die( 'No taxonomy ID given' );

		$this->singular     = $singular;
		$this->plural       = ( empty( $plural ) ) ? $singular .'s' : $plural;
		$this->slug         = sanitize_title( $this->plural );
		$this->object_types = (array) $object_types;
		$this->args         = (array) $args;

		add_action( 'init', array( $this, 'tax_loop' ) );

	}

	public function tax_loop() {

		$labels = array(
			'name' => $this->plural,
			'singular_name' => $this->singular,
			'search_items' =>  'Search '.$this->plural,
			'all_items' => 'All '.$this->plural,
			'parent_item' => 'Parent '.$this->singular,
			'parent_item_colon' => 'Parent '.$this->singular.':',
			'edit_item' => 'Edit '.$this->singular,
			'update_item' => 'Update '.$this->singular,
			'add_new_item' => 'Add New '.$this->singular,
			'new_item_name' => 'New '.$this->singular.' Name',
		);
		$defaults = array(
			'hierarchical' => true,
			'labels' => $labels,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => $this->slug ),
		);

		$args = wp_parse_args( $this->args, $defaults );

		register_taxonomy( $this->slug, $this->object_types, $args );

	}

	public function init_select_box() {
		add_action( 'admin_menu', array( $this, 'add_select_box' ) );
	}

	public function add_select_box() {
		foreach ( $this->object_types as $key => $cpt ) {
			//remove default
			remove_meta_box( $this->slug .'div', $cpt, 'core' );
			// add custom
			add_meta_box( $this->slug .'_dropdown', 'Programming '. $this->singular, array( &$this, 'select_box' ), $cpt, 'side', 'high' );
		}
	}

	public function select_box() {

		echo '<input type="hidden" name="taxonomy_noncename" id="taxonomy_noncename" value="' .
		wp_create_nonce( 'taxonomy_'. $this->slug ) . '" />';

		$checked = $editor_picks_checked = "";
		// Get all blog taxonomy terms
		$terms = get_terms( $this->slug, 'hide_empty=0');
		$names = wp_get_object_terms( get_the_ID(), $this->slug);

		$existing = array();
		if ( !is_wp_error( $names ) && !empty( $names ) ) {
			foreach ( $names as $name ) {
				$existing[] = $name->term_id;
			}
		}

		echo "<div style='margin-bottom: 5px;'>",
		"<select name='tax_input[". $this->slug ."][]'>";

		foreach ( $terms as $term ) {

			echo "<option value='" . $term->term_id . "'";
			if ( !empty( $existing ) && in_array( $term->term_id, $existing ) ) {
				echo " selected";
			}
			echo ">" . $term->name . "</option>";
		}
		echo "</select></div>\n";

	}

	public function language_slug_from_post( $post_id ) {
		if ( $lang = $this->get_lang( $post_id ) ) {
			return $this->language_slug( $lang->slug );
		}
		return false;
	}

	public function language_slug( $slug_to_check ) {
		$slug_to_check = sanitize_html_class( strtolower( $slug_to_check ) );
		$slugs = array(
			'bsh' => 'bash',
			'c' => 'c',
			'cc' => '',
			'cpp' => '',
			'cs' => 'css',
			'csh' => '',
			'cyc' => '',
			'cv' => '',
			'htm' => '',
			'html' => 'html',
			'java' => '',
			'js' => 'javascript',
			'm' => '',
			'mxml' => '',
			'perl' => 'perl',
			'php' => 'php',
			'pl' => '',
			'pm' => '',
			'py' => 'python',
			'rb' => 'ruby',
			'sh' => '',
			'xhtml' => '',
			'xml' => '',
			'xsl' => '',
		);
		$key = array_search( $slug_to_check, $slugs );
		return $key ? $key : $slug_to_check;
	}

	public function get_lang( $post_id ) {
		$langs = get_the_terms( $post_id, 'languages' );
		$lang = !empty( $langs ) ? array_pop( $langs ) : false;
		return $lang;
	}


}

// new Snippet_Tax_Setup;
