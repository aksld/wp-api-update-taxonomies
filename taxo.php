add_filter( 'rest_pre_insert_event', 'add_taxonomies_to_update', 10, 2 );

/**
 * @param stdClass $prepared_post
 * @param WP_REST_Request $request
 *
 * Update taxonomies from WP Rest API
 *
 * @since  1.0.0
 * @author Axel DUCORON <axel.ducoron@gmail.com>
 *
 * @return WP_Error|stdClass
 */
function add_taxonomies_to_update( $prepared_post, $request ) {

	if ( ! isset( $request['taxonomies'] ) ) {
		return $prepared_post;
	}

	$taxonomies = wp_list_filter( get_object_taxonomies( 'event', 'objects' ), array( 'show_in_rest' => true ) );

	foreach ( $taxonomies as $taxonomy ) {
		$base           = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;
		$taxonomies_req = $request['taxonomies'];
		$append         = false;

		if ( ! isset( $taxonomies_req[ $base ] ) ) {
			continue;
		}

		if ( isset ( $request['append_taxonomies'] ) ) {
			$append = $request['append_taxonomies'];
		}

		foreach ( $taxonomies_req[ $base ] as $term_id ) {
			if ( ! get_term( $term_id, $taxonomy->name ) ) {
				continue;
			}

			if ( ! current_user_can( 'assign_term', (int) $term_id ) ) {
				continue;
			}
		}

		$result = wp_set_object_terms( $request['id'], $taxonomies_req[ $base ], $taxonomy->name, $append );

		if ( is_wp_error( $result ) ) {
			return $result;
		}
	}

	return $prepared_post;
}
