<?php

use Chevereto\Legacy\Classes\Stat;
use function Chevereto\Legacy\G\bytes_to_mb;
use function Chevereto\Legacy\G\get_client_ip;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\get_static_url;
use function Chevereto\Vars\env;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
?>
<div data-modal="modal-connecting-ip" class="hidden">
    <span class="modal-box-title"><i class="fas fa-question-circle"></i> <?php _se('Not your IP?'); ?></span>
    <div class="connecting-ip"><?php echo get_client_ip(); ?></div>
    <p><?php _se(
    "If the detected IP doesn't match yours it means that your web server is under a proxy. The connecting IP can be set using the HTTP header defined in the environment variable %env% and when not set it fall-back to %var%.",
    [
        '%var%' => '<code class="code font-weight-bold">$_SERVER[\'REMOTE_ADDR\']</code>',
        '%env%' => '<code class="code font-weight-bold">CHEVERETO_HEADER_CLIENT_IP</code>',
    ]
); ?></p>
    <p><?php _se("Make sure that you address this issue as the system relies on accurate IP detections to provide basic functionalities and to protect against spam, flooding, and brute force attacks."); ?></p>
</div>
<div class="dashboard-group">
<script type="text/javascript" src="<?php echo get_static_url(PATH_PUBLIC_CONTENT_LEGACY_THEMES_PEAFOWL_LIB . 'js/apexcharts.js'); ?>"></script>
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
}
$min = $imageDataSeries[0][0];
$max = $imageDataSeries[array_key_last($imageDataSeries)][0];
$themeMode = in_array(Handler::var('theme_palette_handle'), ['dark', 'imgur', 'deviantart', 'cmyk'])
    ? 'dark' : 'light';
$series = [
    [
        'name' => _s('Images'),
        'data' => $imageDataSeries,
    ],
    [
        'name' => _s('Disk'),
        'data' => $diskDataSeries,
    ],
    [
        'name' => _s('Users'),
        'data' => $userDataSeries,
    ],
    [
        'name' => _s('Albums'),
        'data' => $albumDataSeries,
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
						<div class="stats-block c6 fluid-column display-inline-block" <?php if (Handler::var('totals')['images'] > 999999) {
    echo ' rel="tooltip" data-tipTip="top" title="' . number_format((float) Handler::var('totals')['images']) . '"';
} ?>>
							<span class="stats-big-number">
								<strong class="number"><?php echo Handler::var('totals')['images'] > 999999 ? Handler::var('totals_display')['images'] : number_format((float) Handler::var('totals')['images']); ?></strong>
								<span class="label"><span class="fas fa-image"></span> <?php _ne('Image', 'Images', Handler::var('totals')['images']); ?></span>
							</span>
						</div>
						<div class="stats-block c6 fluid-column display-inline-block" <?php if (Handler::var('totals')['albums'] > 999999) {
    echo ' rel="tooltip" data-tipTip="top" title="' . number_format((float) Handler::var('totals')['albums']) . '"';
} ?>>
							<span class="stats-big-number">
								<strong class="number"><?php echo Handler::var('totals')['albums'] > 999999 ? Handler::var('totals_display')['albums'] : number_format((float) Handler::var('totals')['albums']); ?></strong>
								<span class="label"><span class="fas fa-images"></span> <?php _ne('Album', 'Albums', Handler::var('totals')['albums']); ?></span>
							</span>
						</div>
                        <?php if ((bool) env()['CHEVERETO_ENABLE_USERS']) { ?>
						<div class="stats-block c6 fluid-column display-inline-block" <?php if (Handler::var('totals')['users'] > 999999) {
    echo ' rel="tooltip" data-tipTip="top" title="' . number_format((float) Handler::var('totals')['users']) . '"';
} ?>>
							<span class="stats-big-number">
								<strong class="number"><?php echo Handler::var('totals')['users'] > 999999 ? Handler::var('totals_display')['users'] : number_format((float) Handler::var('totals')['users']); ?></strong>
								<span class="label"><span class="fas fa-users"></span> <?php _ne('User', 'Users', Handler::var('totals')['users']); ?></span>
							</span>
						</div>
                        <?php } ?>
						<div class="stats-block c6 fluid-column display-inline-block">
							<div class="stats-big-number">
								<strong class="number"><?php echo Handler::var('totals_display')['disk']['used']; ?> <span><?php echo Handler::var('totals_display')['disk']['unit']; ?></span></strong>
								<span class="label"><span class="fas fa-hdd"></span> <?php _se('Disk used'); ?></span>
							</div>
						</div>
					</div>

                    <div class="header header-tabs no-select">
                        <h2><i class="header-icon fas fa-rss"></i> <?php _se('%s News', 'Chevereto'); ?></h2>
                    </div>

                    <div class="card-wrapper margin-bottom-40">
                        <div class="card-slider">
                            <?php foreach (array_slice(Handler::var('chevereto_news'), 0, 8) as $k => $v) {
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
            <h2><i class="header-icon fas fa-server"></i> <?php _se('Installation details'); ?></h2>
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
