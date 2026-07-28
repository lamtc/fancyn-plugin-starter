<?php
/**
 * Development component showcase placeholder.
 *
 * @package Fancyn_Plugin_Starter
 */

?>
<div id="fancyn-plugin-starter-app" class="fps-plugin-root fps-min-h-screen fps-bg-surface-page fps-p-4 md:fps-p-6">
	<div class="fps-mx-auto fps-max-w-7xl fps-space-y-6">
		<?php
		$this->render(
			'partials/page-header',
			array(
				'eyebrow'     => __( 'Development', 'fancyn-plugin-starter' ),
				'title'       => __( 'Plugin UI Kit', 'fancyn-plugin-starter' ),
				'description' => __( 'The complete component showcase is available only when development mode is enabled.', 'fancyn-plugin-starter' ),
			)
		);
		?>
	</div>
</div>
