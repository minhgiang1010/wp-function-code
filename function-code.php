//Disable wordpress redirect to a "similar page" in case of 404 error
function stop_404_guessing( $url ) {
	$lang = function_exists('pll_current_language') ? get_site_url().'/'. pll_current_language('slug') : '';
	if (strpos($url, $lang) === false){
		return ( is_404() ) ? false : $url;
	}
}
add_filter( 'redirect_canonical', 'stop_404_guessing' );
