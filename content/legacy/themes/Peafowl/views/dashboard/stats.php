<?php

use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\Stat;
use function Chevereto\Legacy\G\absolute_to_url;
use function Chevereto\Legacy\G\bytes_to_mb;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\get_client_ip;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\get_static_url;
use function Chevereto\Vars\env;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
?>
<div data-modal="welcome" id="modal-welcome" class="hidden">
    <span class="modal-box-title"><i class="fas fa-hand"></i> <?php _se('Hello and welcome, %s', Login::getUser()['username']); ?></span>
    <img src="<?php echo absolute_to_url(PATH_PUBLIC_CONTENT_LEGACY_SYSTEM . 'chevereto-ultimate-remix.png') ?>" alt="" width="100%%">
    <p><?php _se("Whether you're an individual creator or a company looking for a powerful media sharing platform, Chevereto has you covered.") ?></p>
    <p><?php _se("We're always looking for ways to improve, feel free to %s and suggestions.", '<a href="mailto:rodolfo@chevereto.com" target="_blank">' . _s('share your feedback') . '</a>'); ?> <?php _se('We are available for all your support and customization needs.'); ?></p>
    <p><?php _se('Thank you for choosing Chevereto.'); ?></p>
    <p class="font-style-italic">—<br><a href="https://rodolfoberrios.com" target="_blank">Rodolfo Berrios</a><br><?php _se('Chevereto creator'); ?></p>
</div>
<div data-modal="modal-license-key" class="hidden" data-submit-fn="CHV.fn.license.set.submit" data-ajax-deferred="CHV.fn.license.set.complete" data-ajax-url="<?php echo get_base_url('json'); ?>">
    <span class="modal-box-title"><i class="fas fa-key"></i> <?php _se('License key'); ?></span>
    <p><?php _se(
    "Provide Chevereto license key by assigning the environment variable %e or by creating the %f file containing the license key.",
    [
        '%e' => '<code class="code font-weight-bold word-break-break-all">CHEVERETO_LICENSE_KEY</code>',
        '%f' => '<code class="code font-weight-bold word-break-break-all">' . PATH_APP . 'CHEVERETO_LICENSE_KEY</code>',
    ]
); ?></p>
    <p><?php _se('You can also set the license key in the textarea below.'); ?></p>
    <div class="modal-form margin-top-20">
        <div class="input-label overflow-auto">
            <label for="chevereto-license-key"><?php _se('Chevereto license key'); ?></label>
            <textarea placeholder="<?php _se('PASTE LICENSE KEY HERE'); ?>" id="chevereto-license-key" class="r3 resize-vertical" name="chevereto-license-key"><?php echo Handler::var('licenseKey'); ?></textarea>
            <div class="input-below font-size-small"><?php _se('Get a license at %s to unlock all features and support.', '<a href="https://chevereto.com/pricing" target="_blank">chevereto.com</a>'); ?></div>
        </div>
    </div>
</div>
<div data-modal="modal-connecting-ip" class="hidden">
    <span class="modal-box-title"><i class="fas fa-question-circle"></i> <?php _se('Not your IP?'); ?></span>
    <div class="connecting-ip"><?php echo get_client_ip(); ?></div>
    <p><?php _se(
    "If the detected IP doesn't match yours it means that your web server is under a proxy. The connecting IP can be set using the HTTP header defined in the environment variable %e and when not set it fall-back to %v.",
    [
        '%v' => '<code class="code font-weight-bold">$_SERVER[\'REMOTE_ADDR\']</code>',
        '%e' => '<code class="code font-weight-bold">CHEVERETO_HEADER_CLIENT_IP</code>',
    ]
); ?></p>
    <p><?php _se("Make sure that you address this issue as the system relies on accurate IP detections to provide basic functionalities and to protect against spam, flooding, and brute force attacks."); ?></p>
</div>

<div class="dashboard-group">
<script type="text/javascript" src="<?php echo get_static_url(PATH_PUBLIC_CONTENT_LEGACY_THEMES_PEAFOWL_LIB . 'js/apexcharts.js'); ?>"></script>

    <div class="header header-tabs no-select margin-top-20">
        <h2><i class="header-icon fas fa-chart-line margin-right-5"></i><?php _se('Stats'); ?></h2>
    </div>

    <div id="dashboard-chart">
        <div class="toolbar">
            <button id="one_week">1W</button>
            <button id="one_month">1M</button>
            <button id="three_months">3M</button>
            <button id="six_months">6M</button>
            <button id="one_year" class="active">1Y</button>
        </div>
        <div id="dashboard-chart-timeline"></div>
    </div>
<?php
$dateCumulative = Stat::getByDateCumulative();
$imageDataSeries = [];
$diskDataSeries = [];
$userDataSeries = [];
$albumDataSeries = [];
$tagDataSeries = [];
foreach ($dateCumulative as $data) {
    $datetime = new DateTime(
        $data['date_gmt'],
        new DateTimeZone('UTC')
    );
    $timestamp = $datetime->getTimestamp() * 1000;
    $imageDataSeries[] = [$timestamp, $data['images_acc']];
    $diskDataSeries[] = [$timestamp, bytes_to_mb($data['disk_used_acc'])];
    $userDataSeries[] = [$timestamp, $data['users_acc']];
    $albumDataSeries[] = [$timestamp, $data['albums_acc']];
    $tagDataSeries[] = [$timestamp, $data['tags_acc']];
}
$min = $imageDataSeries[0][0] ?? 0;
$max = $imageDataSeries[array_key_last($imageDataSeries)][0] ?? 0;
$themeMode = in_array(Handler::var('theme_palette_handle'), ['dark', 'imgur', 'deviantart', 'cmyk'])
    ? 'dark' : 'light';
$series = [
    [
        'name' => _n('File', 'Files', 20),
        'data' => $imageDataSeries,
    ],
    [
        'name' => _s('Disk'),
        'data' => $diskDataSeries,
    ],
    [
        'name' => _n('User', 'Users', 20),
        'data' => $userDataSeries,
    ],
    [
        'name' => _n('Album', 'Albums', 20),
        'data' => $albumDataSeries,
    ],
    [
        'name' => _n('Tag', 'Tags', 20),
        'data' => $tagDataSeries,
    ],
];
if (!(bool) env()['CHEVERETO_ENABLE_USERS']) {
    unset($series[2]);
    $series = array_values($series);
}
?>
<script>
var options = {
    series: <?php echo json_encode($series); ?>,
    colors: [
        '#008ffb',
        '#00e396',
        '#feb019',
        '#ff4560',
        '#9b59b6',
    ],
    chart: {
        id: 'area-datetime',
        type: 'area',
        stacked: false,
        height: 450,
        zoom: {
            autoScaleYaxis: true
        },
    },
    dataLabels: {
        enabled: false
    },
    markers: {
        size: 0,
        style: 'hollow',
    },
    xaxis: {
        type: 'datetime',
        min: <?php echo $min; ?>,
        tickAmount: 6,
    },
    tooltip: {
        x: {
            format: 'dd MMM yyyy'
        }
    },
    theme: {
        mode: '<?php echo $themeMode; ?>',
    }
};
var chart = new ApexCharts(document.querySelector("#dashboard-chart-timeline"), options);
chart.render();
var resetCssClasses = function(activeEl) {
    var els = document.querySelectorAll('button');
    Array.prototype.forEach.call(els, function(el) {
        el.classList.remove('active');
    });
    activeEl.target.classList.add('active');
}
document.querySelector('#one_week').addEventListener('click', function(e) {
    resetCssClasses(e);
    chart.zoomX(
        <?php echo $max - (86400 * 7 * 1000); ?>,
        <?php echo $max; ?>
    );
});
document.querySelector('#one_month').addEventListener('click', function(e) {
    resetCssClasses(e);
    chart.zoomX(
        <?php echo $max - (86400 * 30 * 1000); ?>,
        <?php echo $max; ?>
    );
});
document.querySelector('#three_months').addEventListener('click', function(e) {
    resetCssClasses(e);
    chart.zoomX(
        <?php echo $max - (86400 * 30 * 3 * 1000); ?>,
        <?php echo $max; ?>
    );
});
document.querySelector('#six_months').addEventListener('click', function(e) {
    resetCssClasses(e);
    chart.zoomX(
        <?php echo $max - (86400 * 30 * 6 * 1000); ?>,
        <?php echo $max; ?>
    );
});
document.querySelector('#one_year').addEventListener('click', function(e) {
    resetCssClasses(e);
    chart.zoomX(
        <?php echo $max - (86400 * 30 * 12 * 1000); ?>,
        <?php echo $max; ?>
    );
});
</script>
    <div class="overflow-auto text-align-center margin-top-20 margin-bottom-40">
        <a href="<?php echo get_base_url('dashboard/files'); ?>" class="stats-block c6 fluid-column display-inline-block" <?php if (Handler::var('totals')['images'] > 999999) {
    echo ' rel="tooltip" data-tipTip="top" title="' . number_format((float) Handler::var('totals')['images']) . '"';
} ?>>
            <span class="stats-big-number">
                <strong class="number"><?php echo Handler::var('totals')['images'] > 999999 ? Handler::var('totals_display')['images'] : number_format((float) Handler::var('totals')['images']); ?></strong>
                <span class="label"><span class="margin-right-5 fas fa-photo-film"></span><span class="label-text"><?php _ne('File', 'Files', Handler::var('totals')['images']); ?></span></span>
            </span>
        </a>
        <a href="<?php echo get_base_url('dashboard/albums'); ?>" class="stats-block c6 fluid-column display-inline-block" <?php if (Handler::var('totals')['albums'] > 999999) {
    echo ' rel="tooltip" data-tipTip="top" title="' . number_format((float) Handler::var('totals')['albums']) . '"';
} ?>>
            <span class="stats-big-number">
                <strong class="number"><?php echo Handler::var('totals')['albums'] > 999999 ? Handler::var('totals_display')['albums'] : number_format((float) Handler::var('totals')['albums']); ?></strong>
                <span class="label"><span class="margin-right-5 fas fa-images"></span><span class="label-text"><?php _ne('Album', 'Albums', Handler::var('totals')['albums']); ?></span></span>
            </span>
        </a>
        <?php if ((bool) env()['CHEVERETO_ENABLE_USERS']) { ?>
        <a href="<?php echo get_base_url('dashboard/users'); ?>" class="stats-block c6 fluid-column display-inline-block" <?php if (Handler::var('totals')['users'] > 999999) {
    echo ' rel="tooltip" data-tipTip="top" title="' . number_format((float) Handler::var('totals')['users']) . '"';
} ?>>
            <span class="stats-big-number">
                <strong class="number"><?php echo Handler::var('totals')['users'] > 999999 ? Handler::var('totals_display')['users'] : number_format((float) Handler::var('totals')['users']); ?></strong>
                <span class="label"><span class="margin-right-5 fas fa-users"></span><span class="label-text"><?php _ne('User', 'Users', Handler::var('totals')['users']); ?></span></span>
            </span>
        </a>
        <?php } ?>
        <a href="<?php echo get_base_url('dashboard/tags'); ?>" class="stats-block c6 fluid-column display-inline-block">
            <div class="stats-big-number">
                <strong class="number"><?php echo Handler::var('totals_display')['tags']; ?></strong>
                <span class="label"><span class="margin-right-5 fas fa-tags"></span><span class="label-text"><?php _ne('Tag', 'Tags', 20); ?></span></span>
            </div>
        </a>
        <div class="stats-block c6 fluid-column display-inline-block">
            <div class="stats-big-number">
                <strong class="number"><?php echo Handler::var('totals_display')['disk']['used']; ?> <span><?php echo Handler::var('totals_display')['disk']['unit']; ?></span></strong>
                <span class="label"><span class="margin-right-5 fas fa-hdd"></span><span class="label-text"><?php _se('Disk used'); ?></span></span>
            </div>
        </div>
    </div>

    <div class="header header-tabs no-select">
        <h2><i class="header-icon fas fa-rss margin-right-5"></i><?php _se('%s News', 'Chevereto'); ?></h2>
    </div>
    <div class="card-wrapper margin-bottom-40">
        <div class="card-slider">
            <?php foreach (array_slice(Handler::var('chevereto_news'), 0, 10) as $k => $v) {
    echo strtr('<article class="card-container">
                <div class="card">
                    <a class="card-header-image" href="%url%" target="_blank" style="background-image: url(%image%);">
                        <span class="animate card-header-image-mask"></span>
                        <span class="card-text">
                            <h3>%title%</h3>
                            <span>%summary%</span>
                        </span>
                    </a>
                </div>
            </article>' . "\n", [
                    '%url%' => $v->url,
                    '%image%' => $v->image,
                    '%title%' => $v->title,
                    '%summary%' => $v->summary,
                ]);
} ?>
        </div>
    </div>

    <div class="header header-tabs no-select">
        <h2><i class="header-icon fas fa-server margin-right-5"></i>Chevereto <span class="edition-label"><?php _se('%s edition', ucfirst(env()['CHEVERETO_EDITION'])); ?></span></h2>
    </div>

    <ul class="tabbed-content-list table-li margin-top-20 padding-bottom-20">
        <?php
        foreach (Handler::var('system_values') as $v) {
            ?>
            <li><span class="font-weight-bold c6 display-table-cell padding-right-10 phone-display-block"><?php echo $v['label']; ?><span style="opacity: 0;">:</span></span><span class="display-table-cell phone-display-block word-break-break-all"><?php echo $v['content']; ?></span></li>
        <?php
        }
        ?>
    </ul>

</div>
<?php
