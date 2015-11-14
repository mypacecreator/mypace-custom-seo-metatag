<?php
function mypace_github_updater() {
	if( is_admin() && class_exists('WP_GitHub_Updater') ) {
		new WP_GitHub_Updater(
			array(
				'slug' => plugin_basename(__FILE__),
				'proper_folder_name' => 'mypace-custom-seo-metatag',
				'api_url' => 'https://api.github.com/repos/mypacecreator/mypace-custom-seo-metatag',
				'raw_url' => 'https://raw.githubusercontent.com/mypacecreator/mypace-custom-seo-metatag/master',
				'github_url' => 'https://github.com/mypacecreator/mypace-custom-seo-metatag',
				'zip_url' => 'https://github.com/mypacecreator/mypace-custom-seo-metatag/archive/master.zip',
				'sslverify' => true,
				'requires' => '4.0',
				'tested' => '4.3.1',
				'readme' => 'README.md',
			)
		);
	}
}