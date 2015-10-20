<?php
/*
Plugin Name: mypace Custom SEO Metatag
Plugin URI: https://github.com/mypacecreator/mypace-custom-seo-metatag
Description: meta内の不要なタグを非出力にしたり、記事が1件しかないカテゴリーorタグアーカイブ、および年月アーカイブ、404ページでnoindex出力したりする
Author: Kei Nomura
Version: 0.2
Author URI: http://mypacecreator.net/
*/

require 'plugin-updates/plugin-update-checker.php';
$className = PucFactory::getLatestClassVersion('PucGitHubChecker');
$myUpdateChecker = new $className(
		'https://github.com/mypacecreator/mypace-custom-seo-metatag/',
		__FILE__,
		'master'
);
$myUpdateChecker->setAccessToken( '72e41eedb3fc40f7e6b3a1aa07e2704454dd580f');

// wp_head()の出力タグの消去
	//remove_action('wp_head', 'wp_enqueue_scripts', 1);
	remove_action('wp_head', 'feed_links_extra',3,0);
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'index_rel_link');
	remove_action('wp_head', 'parent_post_rel_link');
	remove_action('wp_head', 'start_post_rel_link');
	remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
	remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
	//remove_action('wp_head', 'rel_canonical');
	remove_action('wp_head', 'wp_generator');

//記事が1件しかないカテゴリーorタグアーカイブ、および年月アーカイブ、404ページでnoindex出力
function mypace_output_noindex(){
	global $wp_query;
	$number = 2;
	if ( ( is_tag() || is_category() ) && (int) $wp_query->found_posts < $number ) {
		echo '<meta name="robots" content="noindex" />' . "\n";
	} elseif ( is_date() || is_404() ){
		echo '<meta name="robots" content="noindex" />' . "\n";
	}
}
add_action('wp_head','mypace_output_noindex');

//rel="nextとrel="prev"を適切に出力
function mypace_output_linkrelnext($label = null, $max_page = 0) {
	global $paged, $wp_query;
	if ( !$max_page )
		$max_page = $wp_query->max_num_pages;
	if ( !$paged )
		$paged = 1;
		$nextpage = intval($paged) + 1;
	if ( null === $label )
		$label = __( 'Next Page &raquo;' );
	if ( !is_singular() && ( $nextpage <= $max_page ) ) {
		echo '<link rel="next" href="'. next_posts( $max_page, false ) .'" />' . "\n";
	}
	if ( null === $label )
		$label = __( '&laquo; Previous Page' );
	if ( !is_singular() && $paged > 1 ) {
		echo '<link rel="prev" href="'. previous_posts( false ) .'" />' . "\n";
	}
}
add_action('wp_head','mypace_output_linkrelnext');

//「続きを読む」クリック後のURLから#more-$id を削除
function mypace_more_link($output) {
	$output = preg_replace('/#more-[\d]+/i', '', $output );
	return $output;
}
add_filter( 'the_content_more_link', 'mypace_more_link' );

//親子カテゴリーがあるときチェック済カテゴリーが上に来ないように
function mypace_terms_checklist_args( $args, $post_id ){
	if ( $args['checked_ontop'] !== false ){
		$args['checked_ontop']= false;
	}
	return $args;
}
add_filter( 'wp_terms_checklist_args', 'mypace_terms_checklist_args' , 10, 2 );
