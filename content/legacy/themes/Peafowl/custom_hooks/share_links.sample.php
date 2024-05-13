<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

global $share_links_networks;
$share_links_networks = [
    'mail' => [
        'url' => 'mailto:?subject=%TITLE%&body=%URL%',
        'label' => 'Email'
    ],
    'facebook' => [
        'url' => 'http://www.facebook.com/share.php?u=%URL%',
        'label' => 'Facebook'
    ],
    'twitter' => [
        'url' => 'https://twitter.com/intent/tweet?original_referer=%URL%&url=%URL%&via=%TWITTER%&text=%TITLE%',
        'label' => 'Twitter'
    ],
    'blogger' => [
        'url' => 'http://www.blogger.com/blog-this.g?n=%TITLE%&source=&b=%HTML%',
        'label' => 'Blogger'
    ],
    'tumblr' => [
        'url' => 'https://www.tumblr.com/widgets/share/tool/?canonicalUrl=%URL%&posttype=photo&content=%IMAGE%&caption=%TITLE%',
        'label' => 'Tumblr.'
    ],
    'pinterest' => [
        'url' => 'http://www.pinterest.com/pin/create/bookmarklet/?media=%IMAGE%&url=%URL%&is_video=false&description=%DESCRIPTION%&title=%TITLE%',
        'label' => 'Pinterest'
    ],
    'reddit' => [
        'url' => 'http://reddit.com/submit?url=%URL%',
        'label' => 'reddit'
    ],
    'vk' => [
        'url' => 'http://vk.com/share.php?url=%URL%',
        'label' => 'VK'
    ]
];
