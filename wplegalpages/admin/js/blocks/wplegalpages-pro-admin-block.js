(function (blocks, editor, components, i18n, element) {
	var __                = i18n.__
	var el                = element.createElement
	var registerBlockType = blocks.registerBlockType
	var RichText          = editor.RichText
	var BlockControls     = editor.BlockControls
	var AlignmentToolbar  = editor.AlignmentToolbar
	const { InspectorControls, withColors, PanelColorSettings, getColorClassName } = editor;

	registerBlockType(
		'wplegal/affiliate-disclosure-block',
		{
			title: __( 'Affiliate Disclosure Block' ),
			description: __( 'A custom block for displaying affiliate disclosure notice.' ),
			icon: 'welcome-learn-more',
			category: 'common',
			keywords:['affiliate', 'disclosure', 'wplegalpages'],
			attributes: {
				content: {
					type: 'string',
					source: 'html',
					default: '<em>Disclosure: Some of the links in this post are \'affiliate links\'. This means if you click on the link and purchase the item, I will receive an affiliate commission.</em>'
				},
				alignment: {
					type: 'string',
					default: 'left'
				},
				textColor: {
					type: 'string'
				},
				customTextColor: {
					type: 'string'
				},
				backgroundColor: {
					type: 'string'
				},
				customBackgroundColor: {
					type: 'string'
				}
			},

			edit: withColors( 'backgroundColor', 'textColor' )(
				function (props) {
					var attributes        = props.attributes
					var alignment         = props.attributes.alignment
					var styles            = {}
					var backgroundClasses = (( props.backgroundColor.class || '' ) + ' ' + props.className ).trim();
					var textClasses       = (( getColorClassName( 'color', props.attributes.textColor ) || '' ) + ' ' + props.className ).trim();

					if (props.backgroundColor.class) {
						backgroundClasses = 'has-background ' + backgroundClasses
					}
					if (props.textColor.class) {
						textClasses = 'has-text-color ' + textClasses
					}
					if (props.attributes.customBackgroundColor) {
						backgroundClasses         = 'has-background ' + backgroundClasses
						styles['backgroundColor'] = props.attributes.customBackgroundColor ? props.attributes.customBackgroundColor : undefined
					}
					if (props.attributes.customTextColor) {
						textClasses = 'has-text-color ' + textClasses

						styles['color'] = props.attributes.customTextColor ? props.attributes.customTextColor : undefined

					}

					function onChangeAlignment (newAlignment) {
						props.setAttributes( { alignment: newAlignment } )
					}

					return [
					el(
						BlockControls,
						{ key: 'controls' },
						el(
							AlignmentToolbar,
							{
								value: alignment,
								onChange: onChangeAlignment
							}
						)
					),
					el(
						InspectorControls,
						{},
						el(
							PanelColorSettings,
							{
								title: 'Color settings',
								colorSettings: [
								{
									value: props.textColor.color,
									label: 'Text Color',
									onChange: props.setTextColor,
								},
								{
									value: props.backgroundColor.color,
									label: 'Background Color',
									onChange: props.setBackgroundColor,
								}
								]
							},
						),
					),
					el(
						RichText,
						{
							style: styles,
							className: textClasses + ' ' + backgroundClasses + ' has-text-align-' + alignment,
							tagName: 'p',
							placeholder: __( 'Affiliate disclosure block' ),
							keepPlaceholderOnFocus: true,
							value: attributes.content,
							onChange: function (newContent) {
								props.setAttributes( { content: newContent } )
							}
						}
					)
					]
				}
			),

		save: function (props) {
			var attributes      = props.attributes
			var alignment       = props.attributes.alignment
			var styles          = {}
			var backgroundClass = getColorClassName( 'background-color', props.attributes.backgroundColor );
			var textClass       = getColorClassName( 'color', props.attributes.textColor );

			var backgroundClasses = backgroundClass || '';
			if (backgroundClasses) {
				backgroundClasses = 'has-background ' + backgroundClasses;
			}
			var textClasses = textClass || '';
			if (textClasses) {
				textClasses = 'has-text-color ' + textClasses;
			}
			if (props.attributes.customBackgroundColor) {
				backgroundClasses         = 'has-background ' + backgroundClasses;
				styles['backgroundColor'] = props.attributes.customBackgroundColor ? props.attributes.customBackgroundColor : undefined
			}
			if (props.attributes.customTextColor) {
				textClasses     = 'has-text-color ' + textClasses;
				styles['color'] = props.attributes.customTextColor ? props.attributes.customTextColor : undefined
			}

			return (
			el(
				RichText.Content,
				{
					style: styles,
					className: textClasses + ' ' + backgroundClasses + ' has-text-align-' + alignment,
					tagName: 'p',
					value: attributes.content
				}
			)
			)
		}
		}
	)
})(
	window.wp.blocks,
	window.wp.editor,
	window.wp.components,
	window.wp.i18n,
	window.wp.element
)
