<?php
/**
 * Starter dashboard template.
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
				'eyebrow'     => __( 'Dashboard', 'fancyn-plugin-starter' ),
				'title'       => __( 'Fancyn Plugin Starter', 'fancyn-plugin-starter' ),
				'description' => __( 'A scoped WordPress admin foundation built with PHP components, Tailwind CSS, and Vite.', 'fancyn-plugin-starter' ),
			)
		);
		?>
		<div class="fps-grid fps-grid-cols-1 fps-gap-5 md:fps-grid-cols-2 xl:fps-grid-cols-4">
			<?php foreach ( array( __( 'PHP components', 'fancyn-plugin-starter' ), __( 'Scoped styles', 'fancyn-plugin-starter' ), __( 'Vite assets', 'fancyn-plugin-starter' ), __( 'Secure APIs', 'fancyn-plugin-starter' ) ) as $fancyn_plugin_starter_feature ) : ?>
				<div class="fps-rounded-card fps-bg-surface fps-p-5 fps-shadow-card"><p class="fps-text-sm fps-font-semibold fps-text-ink"><?php echo esc_html( $fancyn_plugin_starter_feature ); ?></p></div>
			<?php endforeach; ?>
		</div>
		<?php
		$this->render(
			'partials/empty-state',
			array(
				'title'       => __( 'Ready for your plugin features', 'fancyn-plugin-starter' ),
				'description' => __( 'Rename the starter, then replace this dashboard with your project-specific workflow.', 'fancyn-plugin-starter' ),
			)
		);
		?>
	</div>
</div>
