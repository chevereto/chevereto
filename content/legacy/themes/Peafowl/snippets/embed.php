<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
global $embed_upload_tpl, $embed_unapproved_tpl, $embed_share_tpl;
$embed_upload_tpl = [
    'links' => [
        'label' => _s('Link'),
        'options' => [
            'viewer-links' => [
                'label' => _s('%s link', _s('Viewer')),
                'template' => '%URL_VIEWER%',
                'size' => 'viewer',
            ],
            'direct-links' => [
                'label' => _s('%s link', _s('Direct')),
                'template' => '%URL%',
                'size' => 'full',
            ],
            'frame-links' => [
                'label' => _s('%s link', _s('Frame')),
                'template' => '%URL_FRAME%',
                'size' => 'full',
            ],
            'thumb-links' => [
                'label' => _s('%s link', _s('Thumbnail')),
                'template' => '%THUMB_URL%',
                'size' => 'thumb',
            ],
            'medium-links' => [
                'label' => _s('%s link', _s('Medium')),
                'template' => '%MEDIUM_URL%',
                'size' => 'medium',
            ],
            'delete-links' => [
                'label' => _s('%s link', _s('Delete')),
                'template' => '%DELETE_URL%',
                'size' => 'full',
            ],
        ],
    ],
    'html-codes' => [
        'label' => 'HTML',
        'options' => [
            'html-embed' => [
                'label' => sprintf('HTML %s', _s('embed')),
                'template' => [
                    'image' => '<img src="%URL%" alt="%DISPLAY_TITLE%" border="0">',
                    'video' => '<video src="%URL%" controls poster="%URL_FRAME%"></video>',
                ],
                'size' => 'full',
            ],
            'full-html-embed' => [
                'label' => _s('%s full linked', 'HTML'),
                'template' => [
                    'image' => '<a href="%URL_VIEWER%"><img src="%URL%" alt="%DISPLAY_TITLE%" border="0"></a>',
                    'video' => '<a href="%URL_VIEWER%"><video src="%URL%" controls poster="%URL_FRAME%"></video></a>',
                ],
                'size' => 'full',
            ],
            'medium-html-embed' => [
                'label' => _s('%s medium linked', 'HTML'),
                'template' => '<a href="%URL_VIEWER%"><img src="%MEDIUM_URL%" alt="%DISPLAY_TITLE%" border="0"></a>',
                'size' => 'medium',
            ],
            'thumbnail-html-embed' => [
                'label' => _s('%s thumbnail linked', 'HTML'),
                'template' => '<a href="%URL_VIEWER%"><img src="%THUMB_URL%" alt="%DISPLAY_TITLE%" border="0"></a>',
                'size' => 'thumb',
            ],
        ],
    ],
    'markdown' => [
        'label' => 'Markdown',
        'options' => [
            'markdown-embed' => [
                'label' => _s('%s full', 'Markdown'),
                'template' => [
                    'image' => '![%DISPLAY_TITLE%](%URL%)',
                    'video' => '[![%DISPLAY_TITLE%](%URL_FRAME%)](%URL_VIEWER%)',
                ],
                'size' => 'full',
            ],
            'full-markdown-embed' => [
                'label' => _s('%s full linked', 'Markdown'),
                'template' => [
                    'image' => '[![%DISPLAY_TITLE%](%URL%)](%URL_VIEWER%)',
                    'video' => '[![%DISPLAY_TITLE%](%URL_FRAME%)](%URL_VIEWER%)',
                ],
                'size' => 'full',
            ],
            'medium-markdown-embed' => [
                'label' => _s('%s medium linked', 'Markdown'),
                'template' => '[![%DISPLAY_TITLE%](%MEDIUM_URL%)](%URL_VIEWER%)',
                'size' => 'medium',
            ],
            'thumbnail-markdown-embed' => [
                'label' => _s('%s thumbnail linked', 'Markdown'),
                'template' => '[![%DISPLAY_TITLE%](%THUMB_URL%)](%URL_VIEWER%)',
                'size' => 'thumb',
            ],
        ],
    ],
    'bbcodes' => [
        'label' => 'BBCode',
        'options' => [
            'bbcode-embed' => [
                'label' => _s('%s full', 'BBCode'),
                'template' => [
                    'image' => '[img]%URL%[/img]',
                    'video' => '[video]%URL%[/video]',
                ],
                'size' => 'full',
            ],
            'full-bbcode-embed' => [
                'label' => _s('%s full linked', 'BBCode'),
                'template' => [
                    'image' => '[url=%URL_VIEWER%][img]%URL%[/img][/url]',
                    'video' => '[url=%URL_VIEWER%][video]%URL%[/video][/url]',
                ],
                'size' => 'full',
            ],
            'medium-bbcode-embed' => [
                'label' => _s('%s medium linked', 'BBCode'),
                'template' => '[url=%URL_VIEWER%][img]%MEDIUM_URL%[/img][/url]',
                'size' => 'medium',
            ],
            'thumbnail-bbcode-embed' => [
                'label' => _s('%s thumbnail linked', 'BBCode'),
                'template' => '[url=%URL_VIEWER%][img]%THUMB_URL%[/img][/url]',
                'size' => 'thumb',
            ],
        ],
    ],
];
$embed_unapproved_tpl = [
    'links' => [
        'label' => _s('Link'),
        'options' => [
            'viewer-links' => [
                'label' => _s('%s link', _s('Viewer')),
                'template' => '%URL_VIEWER%',
                'size' => 'viewer',
            ],
        ],
    ],
];
$embed_share_tpl = $embed_upload_tpl;
unset($embed_share_tpl['links']['options']['delete-links']);
