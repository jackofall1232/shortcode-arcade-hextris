/**
 * Hextris Arcade - Gutenberg Block
 */

(function(blocks, element, blockEditor, components, i18n) {
    var el = element.createElement;
    var useBlockProps = blockEditor.useBlockProps;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var SelectControl = components.SelectControl;
    var TextControl = components.TextControl;
    var ToggleControl = components.ToggleControl;
    var __ = i18n.__;

    blocks.registerBlockType('hextris-arcade/game', {
        title: __('Hextris Game', 'hextris-arcade'),
        description: __('Add the Hextris puzzle game to your page.', 'hextris-arcade'),
        icon: 'games',
        category: 'widgets',
        keywords: [
            __('game', 'hextris-arcade'),
            __('puzzle', 'hextris-arcade'),
            __('arcade', 'hextris-arcade'),
            __('hextris', 'hextris-arcade'),
            __('tetris', 'hextris-arcade')
        ],
        attributes: {
            width: {
                type: 'string',
                default: '100%'
            },
            height: {
                type: 'string',
                default: '500px'
            },
            difficulty: {
                type: 'string',
                default: 'normal'
            },
            colorScheme: {
                type: 'string',
                default: 'default'
            },
            showLeaderboard: {
                type: 'boolean',
                default: true
            }
        },

        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            var blockProps = useBlockProps({
                className: 'hextris-block-editor-preview'
            });

            return el(
                'div',
                blockProps,
                el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        { title: __('Game Settings', 'hextris-arcade'), initialOpen: true },
                        el(TextControl, {
                            label: __('Width', 'hextris-arcade'),
                            help: __('CSS width value (e.g., 100%, 500px)', 'hextris-arcade'),
                            value: attributes.width,
                            onChange: function(value) {
                                setAttributes({ width: value });
                            }
                        }),
                        el(TextControl, {
                            label: __('Height', 'hextris-arcade'),
                            help: __('CSS height value (e.g., 500px, 80vh)', 'hextris-arcade'),
                            value: attributes.height,
                            onChange: function(value) {
                                setAttributes({ height: value });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Difficulty', 'hextris-arcade'),
                            value: attributes.difficulty,
                            options: [
                                { label: __('Easy', 'hextris-arcade'), value: 'easy' },
                                { label: __('Normal', 'hextris-arcade'), value: 'normal' },
                                { label: __('Hard', 'hextris-arcade'), value: 'hard' }
                            ],
                            onChange: function(value) {
                                setAttributes({ difficulty: value });
                            }
                        })
                    ),
                    el(
                        PanelBody,
                        { title: __('Appearance', 'hextris-arcade'), initialOpen: false },
                        el(SelectControl, {
                            label: __('Color Scheme', 'hextris-arcade'),
                            value: attributes.colorScheme,
                            options: [
                                { label: __('Default', 'hextris-arcade'), value: 'default' },
                                { label: __('Dark', 'hextris-arcade'), value: 'dark' },
                                { label: __('Colorblind Friendly', 'hextris-arcade'), value: 'colorblind' },
                                { label: __('Custom (from settings)', 'hextris-arcade'), value: 'custom' }
                            ],
                            onChange: function(value) {
                                setAttributes({ colorScheme: value });
                            }
                        }),
                        el(ToggleControl, {
                            label: __('Show Leaderboard', 'hextris-arcade'),
                            checked: attributes.showLeaderboard,
                            onChange: function(value) {
                                setAttributes({ showLeaderboard: value });
                            }
                        })
                    )
                ),
                el(
                    'div',
                    {
                        className: 'hextris-block-preview',
                        style: {
                            width: attributes.width,
                            height: attributes.height,
                            minHeight: '300px',
                            maxHeight: '600px',
                            background: 'linear-gradient(135deg, #2c3e50 0%, #1a252f 100%)',
                            borderRadius: '8px',
                            display: 'flex',
                            flexDirection: 'column',
                            justifyContent: 'center',
                            alignItems: 'center',
                            color: '#fff',
                            fontFamily: 'sans-serif'
                        }
                    },
                    el('div', {
                        style: {
                            fontSize: '2.5rem',
                            fontWeight: '700',
                            letterSpacing: '0.2em',
                            marginBottom: '1rem'
                        }
                    }, 'HEXTRIS'),
                    el('div', {
                        style: {
                            fontSize: '0.9rem',
                            color: '#bdc3c7',
                            marginBottom: '0.5rem'
                        }
                    }, __('Game Preview', 'hextris-arcade')),
                    el('div', {
                        style: {
                            fontSize: '0.8rem',
                            color: '#7f8c8d',
                            padding: '0.5rem 1rem',
                            border: '1px solid #7f8c8d',
                            borderRadius: '4px'
                        }
                    }, __('Difficulty:', 'hextris-arcade') + ' ' + attributes.difficulty)
                )
            );
        },

        save: function() {
            // Dynamic block - rendered by PHP
            return null;
        }
    });

})(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.i18n
);
