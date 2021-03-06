<?php
/*
Plugin Name: mypace Custom SEO Metatag
Plugin URI: https://github.com/mypacecreator/mypace-custom-seo-metatag
Description: meta内の不要なタグを非出力にしたり、記事が1件しかないカテゴリーorタグアーカイブ、および年月アーカイブ、404ページでnoindex出力したりする
Author: Kei Nomura
Version: 0.8
Author URI: http://mypacecreator.net/
*/

include_once 'updater.php';
include_once 'updater-config.php';

// wp_head()の出力タグの消去
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'index_rel_link');
	remove_action('wp_head', 'parent_post_rel_link');
	remove_action('wp_head', 'start_post_rel_link');
	remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
	remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
	remove_action('wp_head', 'wp_generator');

//パンくず調整 リッチスニペット対応
function rich_bread_crumb($output, $args) {
	if ($args['type'] == 'list') {
			$output = preg_replace('|<li\s+(.*?)>|mi','<li ${1} itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb">',$output);
			$output = preg_replace('|<li\s+class="(.*?current.*?)".*?>|mi','<li class="${1}">',$output);
			$output = preg_replace('|<a\s+(.*?)>|mi','<a ${1} itemprop="url"><span itemprop="title">',$output);
			$output = str_replace('</a>','</span></a>',$output);
	}
	return $output;
}
add_filter('bread_crumb', 'rich_bread_crumb',10,2);

//記事が1件しかないカテゴリーorタグアーカイブ、および年月アーカイブ、検索結果、ページネーションした2ページ目以降、404ページでnoindex出力
function mypace_output_noindex(){
	global $wp_query;
	$number = 2;
	if ( ( is_tag() || is_category() ) && (int) $wp_query->found_posts < $number ) {
		echo '<meta name="robots" content="noindex, follow" />' . "\n";
	} elseif ( is_date() || is_search() || is_paged() || is_404() ){
		echo '<meta name="robots" content="noindex, follow" />' . "\n";
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
function mypace_terms_checklist_args( $args, $post_id = null ) {
	$args['checked_ontop'] = false;
	return $args;
}
add_filter( 'wp_terms_checklist_args', 'mypace_terms_checklist_args' , 10, 2 );

//フロントページの出力条件を今までどおりに
function mypace_custom_title( $title ){
	if ( !is_home() && is_front_page()  ) {
				$post_id = get_the_ID();
				$my_title = get_post_meta( $post_id, 'mypace_title_tag', true );
				if( !$my_title ){ //mypace Custom Title Tag プラグインでの指定があればそれを優先
					$title = get_bloginfo( 'name' ) . " | " . get_bloginfo( 'description' );
				}
		}
	return $title;
}
add_filter( 'pre_get_document_title', 'mypace_custom_title', 10, 2 );

//プラグインの読み込み順を制御し、最後に実行するように
function mypace_plugin_last_load() {
	$this_activeplugin  = '';
	$this_plugin        = 'mypace-custom-seo-metatag/mypace-custom-seo-metatag.php';
	$active_plugins     = get_option( 'active_plugins' );
	$new_active_plugins = array();

	foreach ( $active_plugins as $plugins ) {
		if ( $plugins != $this_plugin ){
			$new_active_plugins[] = $plugins;
		} else {
			$this_activeplugin = $this_plugin;
		}
	}

	if ( $this_activeplugin ){
		$new_active_plugins[] = $this_activeplugin;
	}

	if ( ! empty( $new_active_plugins ) ){
		update_option( 'active_plugins' ,  $new_active_plugins );
	}

}
add_action( 'activated_plugin', 'mypace_plugin_last_load' );
