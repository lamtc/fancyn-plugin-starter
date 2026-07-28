<?php
/**
 * Starter settings template.
 *
 * @package Fancyn_Plugin_Starter
 */

use Fancyn\PluginStarter\UI\Button;
use Fancyn\PluginStarter\UI\Input;
use Fancyn\PluginStarter\UI\SwitchField;

$fancyn_plugin_starter_fields  = Input::make(
	array(
		'name'        => $option . '[company_name]',
		'label'       => __( 'Company name', 'fancyn-plugin-starter' ),
		'value'       => $options['company_name'],
		'description' => __( 'Displayed in generated reports.', 'fancyn-plugin-starter' ),
	)
);
$fancyn_plugin_starter_fields .= Input::make(
	array(
		'name'  => $option . '[company_email]',
		'type'  => 'email',
		'label' => __( 'Company email', 'fancyn-plugin-starter' ),
		'value' => $options['company_email'],
	)
);
$fancyn_plugin_starter_fields .= Input::make(
	array(
		'name'  => $option . '[website_url]',
		'type'  => 'url',
		'label' => __( 'Website URL', 'fancyn-plugin-starter' ),
		'value' => $options['website_url'],
	)
);
$fancyn_plugin_starter_fields .= SwitchField::make(
	array(
		'name'        => $option . '[enabled]',
		'label'       => __( 'Enable reports', 'fancyn-plugin-starter' ),
		'description' => __( 'This change is saved when you submit the form.', 'fancyn-plugin-starter' ),
		'checked'     => $options['enabled'],
	)
);
?>
<div id="fancyn-plugin-starter-app" class="fps-plugin-root fps-min-h-screen fps-bg-surface-page fps-p-4 md:fps-p-6">
	<div class="fps-mx-auto fps-max-w-7xl fps-space-y-6">
		<?php
		$this->render(
			'partials/page-header',
			array(
				'eyebrow'     => __( 'Settings', 'fancyn-plugin-starter' ),
				'title'       => __( 'Plugin configuration', 'fancyn-plugin-starter' ),
				'description' => __( 'A real Settings API example with field-specific sanitization.', 'fancyn-plugin-starter' ),
			)
		);
		settings_errors();
		?>
		<form action="options.php" method="post" class="fps-grid fps-grid-cols-1 fps-gap-6 lg:fps-grid-cols-12">
			<?php settings_fields( $group ); ?>
			<div class="lg:fps-col-span-8">
				<?php
				// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Component renderers escape their own output.
				echo Fancyn\PluginStarter\UI\FormSection::make( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Component escapes output.
					array(
						'title'       => __( 'General information', 'fancyn-plugin-starter' ),
						'description' => __( 'Core settings used by the demonstration plugin.', 'fancyn-plugin-starter' ),
						'content'     => $fancyn_plugin_starter_fields,
						'actions'     => Button::make(
							array(
								'type' => 'submit',
								'text' => __( 'Save changes', 'fancyn-plugin-starter' ),
							)
						),
					)
				);
				// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</div>
			<aside class="lg:fps-col-span-4">
				<div class="fps-rounded-card fps-bg-surface fps-p-5 fps-shadow-card"><p class="fps-text-sm fps-leading-6 fps-text-ink-secondary"><?php esc_html_e( 'All changes use the WordPress nonce and capability checks provided by the Settings API.', 'fancyn-plugin-starter' ); ?></p></div>
			</aside>
		</form>
	</div>
</div>
