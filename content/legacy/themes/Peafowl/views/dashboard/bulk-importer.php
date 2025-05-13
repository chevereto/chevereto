<?php
use Chevereto\Legacy\Classes\Import;
use function Chevereto\Legacy\G\absolute_to_relative;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Vars\env;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
?>
<div data-content="dashboard-imports" class="margin-top-20 margin-bottom-10">
<?php
$statusesDisplay = [
    'queued' => _s('Queued'),
    'working' => _s('Working'),
    'paused' => _s('Paused'),
    'canceled' => _s('Canceled'),
    'completed' => _s('Completed'),
];
$rowTpl = '<li data-status="%status%" data-id="%id%" data-object="%object%" data-errors="%errors%" data-started="%started%">
<span class="fluid-column c2 col-2-max display-table-cell padding-right-10">%id%</span>
<span class="fluid-column c2 col-2-max display-table-cell padding-right-10 text-transform-uppercase" title="' . _s('Top level folders as %s', '%parse%') . '">%shortParse%</span>
<span class="fluid-column c3 col-3-max display-table-cell padding-right-10">
<span class="icon icon-warning2 color-red" data-result="error"></span>
<span class="icon icon-checkmark-circle color-green" data-result="success"></span>
<span class="status-text">%displayStatus%</span>
</span>
<span class="fluid-column c7 col-7-max col-7-min display-table-cell padding-right-10 text-overflow-ellipsis phone-display-block" title="%path%">%path%</span>
<span class="fluid-column c2 display-table-cell padding-right-10"><span>%users%</span><span class="table-li--mobile-display"> ' . _n('User', 'Users', 20) . '</span></span>
<span class="fluid-column c2 display-table-cell padding-right-10"><span>%albums%</span><span class="table-li--mobile-display"> ' . _n('Album', 'Albums', 20) . '</span></span>
<span class="fluid-column c3 display-table-cell padding-right-10"><span>%images%</span><span class="table-li--mobile-display"> ' . _n('File', 'Files', 20) . '</span></span>
<div class="fluid-column c3 display-table-cell margin-bottom-0 phone-display-block">
<div class="loading"></div>
<div data-content="pop-selection" class="pop-btn">
    <span class="pop-btn-text">' . _s('Actions') . ' <span class="fas fa-angle-down"></span></span>
    <div class="pop-box arrow-box arrow-box-top anchor-left">
        <div class="pop-box-inner pop-box-menu">
            <ul>
                <li data-args="%id%" data-modal="form" data-target="modal-process-import"><a>' . _s('Process') . '</a></li>
                <li data-action="pause"><a>' . _s('Pause') . '</a></li>
                <li data-action="cancel"><a>' . _s('Cancel') . '</a></li>
                <li data-content="log-process"><a href="' . get_base_url('importer-jobs/%id%/process') . '" target="_blank">' . _s('Process log') . '</a></li>
                <li data-content="log-errors"><a href="' . get_base_url('importer-jobs/%id%/errors') . '" target="_blank">' . _s('Errors') . '</a></li>
                <li data-args="%id%" data-confirm="' . _s('Do you really want to remove the import ID %s?', '%id%') . '" data-submit-fn="CHV.fn.import.delete.submit" data-ajax-deferred="CHV.fn.import.delete.deferred"><a>' . _s('Delete') . '</a></li>
            </ul>
        </div>
    </div>
</div>
</div>
</li>';
if ($continuous = Import::getContinuous()) {
    foreach ($continuous as $v) {
        $boxTpl = '<div class="importing phone-c1 phablet-c1 c8 fluid-column display-inline-block" data-status="%status%" data-id="%id%" data-object="%object%" data-errors="%errors%" data-started="%started%">
        <h3 class="margin-bottom-5"><i class="fas fa-folder"></i> <b title="%path%">%pathRelative%</b></h3>
        <span data-content="log-errors" class="icon fas fa-exclamation-triangle color-red position-absolute top-10 right-10"></span>
        <div data-content="pop-selection" class="pop-btn">
        <span class="pop-btn-text">' . _s('Actions') . ' <span class="fas fa-angle-down"></span></span>
            <div class="pop-box arrow-box arrow-box-top anchor-left">
                <div class="pop-box-inner pop-box-menu">
                    <ul>
                        <li data-action="reset"><a>' . _s('Reset') . '</a></li>
                        <li data-action="pause"><a>' . _s('Pause') . '</a></li>
                        <li data-action="resume"><a>' . _s('Resume') . '</a></li>
                        <li data-content="log-process"><a href="' . get_base_url('importer-jobs/%id%/process') . '" target="_blank">' . _s('Process log') . '</a></li>
                        <li data-content="log-errors"><a href="' . get_base_url('importer-jobs/%id%/errors') . '" target="_blank"><span class="icon fas fa-exclamation-triangle color-red"></span> ' . _s('Errors') . '</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="importing-stats">
            <span class="figure"><span data-content="images">%images%</span> ' . _n('File', 'Files', 20) . '</span>
            <span class="figure"><span data-content="albums">%albums%</span> ' . _n('Album', 'Albums', 20) . '</span>
            <span class="figure"><span data-content="users">%users%</span> ' . _n('User', 'Users', 20) . '</span>
        </div>
        <div class="importing-status">' . _s('Status') . ': <span data-content="status">%displayStatus%</span></div>
        <div class="importing-status">@<span data-content="dateTime">%dateTime%</span> UTC</div>
        <div class="loading-container position-absolute right-10 bottom-10"><div class="loading"></div></div>
        </div>';
        echo strtr($boxTpl, [
            '%id%' => $v['id'],
            '%dateTime%' => $v['time_updated'],
            '%object%' => htmlspecialchars(json_encode($v), ENT_QUOTES, 'UTF-8'),
            '%status%' => $v['status'],
            '%parse%' => $v['options']['root'],
            '%shortParse%' => $v['options']['root'][0],
            '%displayStatus%' => $statusesDisplay[$v['status']],
            '%path%' => $v['path'],
            '%pathRelative%' => '.' . absolute_to_relative($v['path']),
            '%users%' => $v['users'] ?: 0,
            '%images%' => $v['images'] ?: 0,
            '%albums%' => $v['albums'] ?: 0,
            '%errors%' => $v['errors'] ?: 0,
            '%started%' => $v['started'] ?: 0,
        ]);
    }
} ?>
</div>
<?php if (env()['CHEVERETO_CONTEXT'] !== 'saas') { ?>
<div>
    <p><?php _se('Run the following command to import content to Chevereto:'); ?></p>
    <?php
        $code = env()['CHEVERETO_SERVICING'] === 'docker'
            ? "docker exec -it --user www-data " . (gethostname() ?: 'chv-container') . " app/bin/cli -C bulk-importer"
            : 'sudo -u www-data php ' . PATH_PUBLIC . 'app/bin/cli -C bulk-importer'
    ?>
    <div class="margin-bottom-10 margin-top-10">
        <code class="code code--command display-inline-block" data-click="select-all"><?php echo $code; ?></code>
    </div>
    <p><?php _se('Read the %s for more information about this feature.', get_admin_docs_link('dashboard/bulk-importer.html', _s('documentation'))); ?></p>
</div>
<?php } ?>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    CHV.obj.import = {
        working: {
            // importId: {
            // 	threads: {threadId: xhr},
            // 	interval: interval,
            // 	stats: xhr
            // }
        },
        aborted: [],
        boxTpl: <?php echo json_encode($boxTpl ?? ''); ?>,
        rowTpl: <?php echo json_encode($rowTpl); ?>,
        importTr: {
            'id': null,
            // 'object' : null,
            'status': null,
            'parse': null,
            'shortParse': null,
            'displayStatus': null,
            'path': null,
            'users': null,
            'images': null,
            'albums': null,
            'errors': null,
            'started': null,
        },
        sel: {
            root: "[data-content=dashboard-imports]",
            header: ".table-li-header"
        },
        statusesDisplay: <?php echo json_encode($statusesDisplay); ?>
    };
    var updateContinuous = function( object) {
        var $sel = $("[data-id=" + object.id + "]", CHV.obj.import.sel.root);
        $sel.attr({
            "data-status": object.status,
            "data-object": JSON.stringify(object),
            "data-errors": object.errors,
            "data-started": object.started,
        });
        $("[data-content=images]", $sel).text(object.images);
        $("[data-content=dateTime]", $sel).text(object.time_updated);
        $("[data-content=users]", $sel).text(object.users);
        $("[data-content=albums]", $sel).text(object.albums);
        $("[data-content=status]", $sel).text(CHV.obj.import.statusesDisplay[object.status]);
    };
    $('.importing', '[data-content=dashboard-imports]').each(function(i, v) {
        var id = $(this).data("id");
        var $this = $(this);
        CHV.obj.import.working[id] = {
            threads: {},
            interval: setInterval(function() {
                var $loading = $this.find(".loading");
                if($loading.html() !== "") {
                    $loading.removeClass("hidden");
                } else {
                    PF.fn.loading.inline($loading, {
                        size: "small"
                    });
                }
                CHV.obj.import.working[id].stats = $.ajax({
                    type: "POST",
                    data: {
                        action: "importStats",
                        id: id
                    }
                });
                CHV.obj.import.working[id].stats.complete(function (XHR) {
                    var response = XHR.responseJSON;
                    if (response) {
                        updateContinuous(response.import);
                    }
                    $loading.addClass("hidden");
                });
            }, 10000),
            stats: {}
        };
    });
    $(document).on("click", CHV.obj.import.sel.root + " [data-action]", function() {
        var $el = $(this).closest("[data-object]");
        var $loading = $el.find(".loading");
        var $actions = $el.find("[data-content=pop-selection]");
        var localData = $el.data("object");
        var backupData = $.extend({}, localData);
        var action = $(this).data("action");
        var data = {};
        if (localData.id) {
            data.id = localData.id;
        }
        if($el.is("li")) {
            CHV.fn.import.process.abortAll(data.id);
        }
        switch (action) {
            case "resume":
                data.action = "importResume";
                localData.status = "working";
                break;
            case "reset":
                data.action = "importReset";
                localData.status = "working";
                break;
            case "pause":
                data.action = "importEdit";
                localData.status = "paused";
                data.values = {
                    status: localData.status
                };
                break;
            case "cancel":
                localData.status = "canceled";
                data.action = "importEdit";
                data.values = {
                    status: localData.status
                };
                break;
            case "process":
                localData.status = "working";
                data.action = "importEdit";
                data.values = {
                    status: localData.status
                };
                break;
            default:
                alert('null');
                return;
                break;
        }
        if($loading.html() !== "") {
            $loading.removeClass("hidden");
        } else {
            PF.fn.loading.inline($loading, {
                size: "small"
            });
        }
        $actions.addClass("pointer-events-none");
        $.ajax({
            type: "POST",
            data: data
        }).complete(function(XHR) {
            var response = XHR.responseJSON;
            if (XHR.status == 200) {
                var dataset = response.import;
                if($el.is("li")) {
                    var $html = CHV.fn.import.parseTemplate(dataset);
                    $el.replaceWith($html);
                    if (action == "process") {
                        CHV.fn.import.process.deferred.success(XHR);
                    }
                } else {
                    updateContinuous(response.import);
                }
            } else {
                PF.fn.growl.call(response.error.message);
            }
            $loading.addClass("hidden");
            $actions.removeClass("pointer-events-none");
        });
    });
});
</script>
