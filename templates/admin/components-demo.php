<?php
/**
 * Development component showcase.
 *
 * @package Fancyn_Plugin_Starter
 */

use Fancyn\PluginStarter\Support\Html;
use Fancyn\PluginStarter\UI\Button;
use Fancyn\PluginStarter\UI\Checkbox;
use Fancyn\PluginStarter\UI\FormField;
use Fancyn\PluginStarter\UI\FormSection;
use Fancyn\PluginStarter\UI\Input;
use Fancyn\PluginStarter\UI\Label;
use Fancyn\PluginStarter\UI\Radio;
use Fancyn\PluginStarter\UI\Select;
use Fancyn\PluginStarter\UI\SwitchField;
use Fancyn\PluginStarter\UI\Textarea;
use Fancyn\PluginStarter\UI\ValidationMessage;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Stateless components escape their own output.
$fancyn_plugin_starter_icon = '<svg viewBox="0 0 20 20" width="16" height="16" aria-hidden="true"><path d="M4 10h12" stroke="currentColor" stroke-width="2"/></svg>';

$fancyn_plugin_starter_buttons  = '<div class="fps-flex fps-flex-wrap fps-items-center fps-gap-3">';
$fancyn_plugin_starter_buttons .= Button::make(
	array(
		'variant' => 'primary',
		'size'    => 'sm',
		'text'    => __( 'Primary small', 'fancyn-plugin-starter' ),
	)
);
$fancyn_plugin_starter_buttons .= Button::make(
	array(
		'variant' => 'secondary',
		'size'    => 'md',
		'text'    => __( 'Secondary', 'fancyn-plugin-starter' ),
	)
);
$fancyn_plugin_starter_buttons .= Button::make(
	array(
		'variant' => 'outline',
		'size'    => 'lg',
		'text'    => __( 'Outline large', 'fancyn-plugin-starter' ),
	)
);
$fancyn_plugin_starter_buttons .= Button::make(
	array(
		'variant' => 'ghost',
		'size'    => 'md',
		'text'    => __( 'Ghost', 'fancyn-plugin-starter' ),
	)
);
$fancyn_plugin_starter_buttons .= Button::make(
	array(
		'variant' => 'danger',
		'size'    => 'md',
		'text'    => __( 'Danger', 'fancyn-plugin-starter' ),
	)
);
$fancyn_plugin_starter_buttons .= Button::make(
	array(
		'variant' => 'link',
		'size'    => 'md',
		'href'    => '#component-forms',
		'text'    => __( 'Link', 'fancyn-plugin-starter' ),
	)
);
$fancyn_plugin_starter_buttons .= Button::make(
	array(
		'variant'    => 'outline',
		'size'       => 'icon',
		'icon'       => $fancyn_plugin_starter_icon,
		'aria-label' => __( 'Add item', 'fancyn-plugin-starter' ),
	)
);
$fancyn_plugin_starter_buttons .= Button::make(
	array(
		'variant' => 'primary',
		'size'    => 'md',
		'text'    => __( 'Saving', 'fancyn-plugin-starter' ),
		'loading' => true,
	)
);
$fancyn_plugin_starter_buttons .= '</div>';

$fancyn_plugin_starter_inputs  = '<div class="fps-grid fps-grid-cols-1 fps-gap-6 md:fps-grid-cols-2">';
$fancyn_plugin_starter_inputs .= Input::make(
	array(
		'name'        => 'demo_default',
		'label'       => __( 'Default input', 'fancyn-plugin-starter' ),
		'description' => __( 'A standard text input.', 'fancyn-plugin-starter' ),
	)
);
$fancyn_plugin_starter_inputs .= Input::make(
	array(
		'name'  => 'demo_error',
		'label' => __( 'Error input', 'fancyn-plugin-starter' ),
		'value' => 'Invalid value',
		'error' => __( 'Enter a valid value.', 'fancyn-plugin-starter' ),
	)
);
$fancyn_plugin_starter_inputs .= Input::make(
	array(
		'name'     => 'demo_disabled',
		'label'    => __( 'Disabled input', 'fancyn-plugin-starter' ),
		'value'    => 'Unavailable',
		'disabled' => true,
	)
);
$fancyn_plugin_starter_inputs .= Input::make(
	array(
		'name'     => 'demo_readonly',
		'label'    => __( 'Readonly input', 'fancyn-plugin-starter' ),
		'value'    => 'Read only',
		'readonly' => true,
	)
);
$fancyn_plugin_starter_inputs .= '</div>';
$fancyn_plugin_starter_inputs .= Textarea::make(
	array(
		'name'              => 'demo_summary',
		'label'             => __( 'Summary', 'fancyn-plugin-starter' ),
		'value'             => 'A progressively enhanced textarea.',
		'maxlength'         => 160,
		'character_counter' => true,
		'auto_resize'       => true,
	)
);

$fancyn_plugin_starter_choices  = '<div class="fps-grid fps-grid-cols-1 fps-gap-6 lg:fps-grid-cols-2">';
$fancyn_plugin_starter_choices .= Select::make(
	array(
		'name'        => 'demo_native_select',
		'label'       => __( 'Native select', 'fancyn-plugin-starter' ),
		'placeholder' => __( 'Choose a region', 'fancyn-plugin-starter' ),
		'options'     => array(
			'vn' => 'Vietnam',
			'sg' => 'Singapore',
			'jp' => 'Japan',
		),
	)
);
$fancyn_plugin_starter_choices .= Select::make(
	array(
		'name'        => 'demo_enhanced_select',
		'label'       => __( 'Enhanced select', 'fancyn-plugin-starter' ),
		'placeholder' => __( 'Choose services', 'fancyn-plugin-starter' ),
		'options'     => array(
			'seo'     => 'SEO',
			'ads'     => 'Advertising',
			'content' => 'Content',
		),
		'multiple'    => true,
		'enhanced'    => true,
		'searchable'  => true,
		'clearable'   => true,
		'attributes'  => array( 'data-enhanced-select' => '1' ),
	)
);
$fancyn_plugin_starter_choices .= Checkbox::make(
	array(
		'name'        => 'demo_checkbox',
		'label'       => __( 'Email reports', 'fancyn-plugin-starter' ),
		'description' => __( 'Receive a weekly summary.', 'fancyn-plugin-starter' ),
		'checked'     => true,
	)
);
$fancyn_plugin_starter_choices .= SwitchField::make(
	array(
		'name'        => 'demo_switch',
		'label'       => __( 'Enable integration', 'fancyn-plugin-starter' ),
		'description' => __( 'Unsaved until the form is submitted.', 'fancyn-plugin-starter' ),
		'checked'     => true,
	)
);
$fancyn_plugin_starter_choices .= '</div>';
$fancyn_plugin_starter_choices .= Radio::make(
	array(
		'name'    => 'demo_mode',
		'label'   => __( 'Processing mode', 'fancyn-plugin-starter' ),
		'value'   => 'balanced',
		'options' => array(
			'fast'     => __( 'Fast', 'fancyn-plugin-starter' ),
			'balanced' => __( 'Balanced', 'fancyn-plugin-starter' ),
			'precise'  => __( 'Precise', 'fancyn-plugin-starter' ),
		),
	)
);

$fancyn_plugin_starter_feedback  = '<div class="fps-grid fps-grid-cols-1 fps-gap-3 md:fps-grid-cols-2">';
$fancyn_plugin_starter_feedback .= ValidationMessage::make(
	array(
		'variant' => 'error',
		'message' => __( 'The submitted value is invalid.', 'fancyn-plugin-starter' ),
	)
);
$fancyn_plugin_starter_feedback .= ValidationMessage::make(
	array(
		'variant' => 'warning',
		'message' => __( 'Review this value before saving.', 'fancyn-plugin-starter' ),
	)
);
$fancyn_plugin_starter_feedback .= ValidationMessage::make(
	array(
		'variant' => 'success',
		'message' => __( 'Configuration is valid.', 'fancyn-plugin-starter' ),
	)
);
$fancyn_plugin_starter_feedback .= ValidationMessage::make(
	array(
		'variant' => 'info',
		'message' => __( 'This setting affects generated reports.', 'fancyn-plugin-starter' ),
	)
);
$fancyn_plugin_starter_feedback .= '</div>';
$fancyn_plugin_starter_feedback .= Label::make(
	array(
		'for'      => 'standalone-control',
		'text'     => __( 'Standalone label', 'fancyn-plugin-starter' ),
		'required' => true,
	)
);
$fancyn_plugin_starter_feedback .= FormField::make(
	array(
		'name'        => 'standalone-control',
		'label'       => __( 'Composed FormField', 'fancyn-plugin-starter' ),
		'description' => __( 'FormField owns this accessible relationship.', 'fancyn-plugin-starter' ),
		'control'     => static fn ( array $attributes ): string => '<input class="fps-h-11 fps-w-full fps-rounded-control fps-border fps-border-line fps-px-3"' . Html::attributes( $attributes ) . '>',
	)
);

$fancyn_plugin_starter_wordpress  = '<div class="fps-grid fps-grid-cols-1 fps-gap-6 lg:fps-grid-cols-2">';
$fancyn_plugin_starter_wordpress .= Input::make(
	array(
		'name'       => 'demo_color',
		'label'      => __( 'WordPress color picker', 'fancyn-plugin-starter' ),
		'value'      => '#F4511E',
		'attributes' => array( 'data-color-picker' => '1' ),
	)
);
$fancyn_plugin_starter_wordpress .= '<div class="fps-space-y-2" data-media-field data-media-type="image"><p class="fps-text-sm fps-font-semibold fps-text-ink">' . esc_html__( 'WordPress media uploader', 'fancyn-plugin-starter' ) . '</p><input type="hidden" data-media-id><img class="fps-h-24 fps-w-24 fps-rounded-control fps-border fps-border-line fps-object-cover" data-media-preview hidden alt=""><div class="fps-flex fps-gap-2">';
$fancyn_plugin_starter_wordpress .= Button::make(
	array(
		'variant'    => 'outline',
		'text'       => __( 'Select image', 'fancyn-plugin-starter' ),
		'attributes' => array( 'data-media-select' => '1' ),
	)
);
$fancyn_plugin_starter_wordpress .= Button::make(
	array(
		'variant'    => 'ghost',
		'text'       => __( 'Remove', 'fancyn-plugin-starter' ),
		'attributes' => array( 'data-media-remove' => '1' ),
	)
);
$fancyn_plugin_starter_wordpress .= '</div></div></div>';
ob_start();
wp_editor(
	'',
	'fancyn_plugin_starter_demo_editor',
	array(
		'textarea_name' => 'fancyn_plugin_starter_demo_editor',
		'textarea_rows' => 6,
	)
);
$fancyn_plugin_starter_wordpress .= '<div class="fps-mt-6">' . (string) ob_get_clean() . '</div>';
?>
<div id="fancyn-plugin-starter-app" class="fps-plugin-root fps-min-h-screen fps-bg-surface-page fps-p-4 md:fps-p-6">
	<div class="fps-mx-auto fps-max-w-7xl fps-space-y-6">
		<?php
		$this->render(
			'partials/page-header',
			array(
				'eyebrow'     => __( 'Development', 'fancyn-plugin-starter' ),
				'title'       => __( 'Plugin UI Kit', 'fancyn-plugin-starter' ),
				'description' => __( 'All v1.0 components, states, integrations, and interaction patterns in one responsive page.', 'fancyn-plugin-starter' ),
			)
		);
		echo FormSection::make(
			array(
				'title'       => __( 'Buttons', 'fancyn-plugin-starter' ),
				'description' => __( 'Variants, sizes, icon-only, and loading states.', 'fancyn-plugin-starter' ),
				'content'     => $fancyn_plugin_starter_buttons,
			)
		);
		echo FormSection::make(
			array(
				'title'   => __( 'Inputs and textarea', 'fancyn-plugin-starter' ),
				'content' => $fancyn_plugin_starter_inputs,
			)
		);
		echo FormSection::make(
			array(
				'title'      => __( 'Choice controls', 'fancyn-plugin-starter' ),
				'content'    => $fancyn_plugin_starter_choices,
				'attributes' => array( 'id' => 'component-forms' ),
			)
		);
		echo FormSection::make(
			array(
				'variant' => 'divided',
				'title'   => __( 'Validation and composition', 'fancyn-plugin-starter' ),
				'content' => $fancyn_plugin_starter_feedback,
			)
		);
		echo FormSection::make(
			array(
				'title'   => __( 'WordPress APIs', 'fancyn-plugin-starter' ),
				'content' => $fancyn_plugin_starter_wordpress,
			)
		);
		?>

		<section class="fps-grid fps-grid-cols-1 fps-gap-6 lg:fps-grid-cols-3">
			<div class="fps-rounded-card fps-bg-surface fps-p-6 fps-shadow-card" x-data="modal" @keydown.window="onKeydown($event)">
				<h2 class="fps-text-lg fps-font-bold fps-text-ink"><?php esc_html_e( 'Modal', 'fancyn-plugin-starter' ); ?></h2>
				<div class="fps-mt-4">
				<?php
				echo Button::make(
					array(
						'text'       => __( 'Open modal', 'fancyn-plugin-starter' ),
						'attributes' => array( '@click' => 'show($event.currentTarget)' ),
					)
				);
				?>
				</div>
				<div x-cloak x-show="isOpen" class="fps-fixed fps-inset-0 fps-z-50 fps-flex fps-items-center fps-justify-center fps-bg-black/40 fps-p-4" role="presentation">
					<div x-ref="panel" role="dialog" aria-modal="true" aria-labelledby="demo-modal-title" class="fps-w-full fps-max-w-lg fps-rounded-card fps-bg-surface fps-p-6 fps-shadow-popover">
						<h3 id="demo-modal-title" class="fps-text-xl fps-font-bold fps-text-ink"><?php esc_html_e( 'Accessible modal', 'fancyn-plugin-starter' ); ?></h3>
						<p class="fps-mt-2 fps-text-sm fps-text-ink-secondary"><?php esc_html_e( 'Press Escape to close. Focus remains inside while open.', 'fancyn-plugin-starter' ); ?></p>
						<div class="fps-mt-5">
						<?php
						echo Button::make(
							array(
								'variant'    => 'outline',
								'text'       => __( 'Close', 'fancyn-plugin-starter' ),
								'attributes' => array( '@click' => 'close()' ),
							)
						);
						?>
						</div>
					</div>
				</div>
			</div>

			<div class="fps-rounded-card fps-bg-surface fps-p-6 fps-shadow-card lg:fps-col-span-2" x-data="tabs(['general', 'advanced', 'api'])">
				<h2 class="fps-text-lg fps-font-bold fps-text-ink"><?php esc_html_e( 'Tabs', 'fancyn-plugin-starter' ); ?></h2>
				<div class="fps-mt-4 fps-flex fps-overflow-x-auto fps-border-b fps-border-line" role="tablist">
					<?php
					foreach ( array(
						'general'  => __( 'General', 'fancyn-plugin-starter' ),
						'advanced' => __( 'Advanced', 'fancyn-plugin-starter' ),
						'api'      => __( 'API', 'fancyn-plugin-starter' ),
					) as $fancyn_plugin_starter_tab_id => $fancyn_plugin_starter_tab_label ) :
						?>
						<button type="button" role="tab" x-ref="tab-<?php echo esc_attr( $fancyn_plugin_starter_tab_id ); ?>" @click="activate('<?php echo esc_attr( $fancyn_plugin_starter_tab_id ); ?>')" @keydown="onKeydown($event, <?php echo esc_attr( (string) array_search( $fancyn_plugin_starter_tab_id, array( 'general', 'advanced', 'api' ), true ) ); ?>)" :aria-selected="active === '<?php echo esc_attr( $fancyn_plugin_starter_tab_id ); ?>'" :tabindex="active === '<?php echo esc_attr( $fancyn_plugin_starter_tab_id ); ?>' ? 0 : -1" class="fps-border-b-2 fps-border-transparent fps-px-4 fps-py-3 fps-text-sm fps-font-semibold fps-text-ink-secondary"><?php echo esc_html( $fancyn_plugin_starter_tab_label ); ?></button>
					<?php endforeach; ?>
				</div>
				<div class="fps-py-5 fps-text-sm fps-text-ink-secondary" x-show="active === 'general'" role="tabpanel"><?php esc_html_e( 'General tab content.', 'fancyn-plugin-starter' ); ?></div>
				<div class="fps-py-5 fps-text-sm fps-text-ink-secondary" x-cloak x-show="active === 'advanced'" role="tabpanel"><?php esc_html_e( 'Advanced tab content.', 'fancyn-plugin-starter' ); ?></div>
				<div class="fps-py-5 fps-text-sm fps-text-ink-secondary" x-cloak x-show="active === 'api'" role="tabpanel"><?php esc_html_e( 'API tab content.', 'fancyn-plugin-starter' ); ?></div>
			</div>

			<div class="fps-relative fps-rounded-card fps-bg-surface fps-p-6 fps-shadow-card" x-data="dropdown" @click.outside="close()" @keydown="onKeydown($event)">
				<h2 class="fps-text-lg fps-font-bold fps-text-ink"><?php esc_html_e( 'Dropdown', 'fancyn-plugin-starter' ); ?></h2>
				<div class="fps-mt-4">
				<?php
				echo Button::make(
					array(
						'variant'    => 'outline',
						'text'       => __( 'Actions', 'fancyn-plugin-starter' ),
						'attributes' => array(
							'@click'         => 'toggle()',
							':aria-expanded' => 'isOpen',
						),
					)
				);
				?>
				</div>
				<div x-cloak x-show="isOpen" class="fps-absolute fps-left-6 fps-top-24 fps-z-10 fps-min-w-48 fps-rounded-control fps-border fps-border-line fps-bg-surface fps-p-1 fps-shadow-popover" role="menu">
					<button type="button" class="fps-block fps-min-h-9 fps-w-full fps-rounded-lg fps-px-3 fps-py-2 fps-text-left fps-text-sm hover:fps-bg-surface-muted" role="menuitem"><?php esc_html_e( 'Edit', 'fancyn-plugin-starter' ); ?></button>
					<button type="button" class="fps-block fps-min-h-9 fps-w-full fps-rounded-lg fps-px-3 fps-py-2 fps-text-left fps-text-sm fps-text-danger hover:fps-bg-danger-soft" role="menuitem"><?php esc_html_e( 'Delete', 'fancyn-plugin-starter' ); ?></button>
				</div>
			</div>
		</section>
	</div>
</div>
<?php // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped ?>
