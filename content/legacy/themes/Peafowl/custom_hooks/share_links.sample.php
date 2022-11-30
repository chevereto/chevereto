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
        'url' => 'http://www.tumblr.com/share/photo?source=%PHOTO_URL%&caption=%TITLE%&clickthru=%URL%&title=%TITLE%',
        'label' => 'Tumblr.'
    ],
    'pinterest' => [
        'url' => 'http://www.pinterest.com/pin/create/bookmarklet/?media=%PHOTO_URL%&url=%URL%&is_video=false&description=%DESCRIPTION%&title=%TITLE%',
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
