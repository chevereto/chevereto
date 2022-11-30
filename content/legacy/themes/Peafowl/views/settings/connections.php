<?php

use Chevereto\Legacy\Classes\Login;
use function Chevereto\Legacy\G\get_base_url;
use Chevereto\Legacy\G\Handler;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
if (Handler::cond('dashboard_user')) {
    ?>
    <p data-content="empty-message" <?php if (count(Handler::var('connections')) > 0) {
        ?>class="soft-hidden" <?php
    } ?>><?php _se('User has no connections.'); ?></p>
<?php
} else {
        ?>
    <p><?php _se('Link your account to external login providers to be able to login here.'); ?></p>
<?php
    } ?>
<?php
    foreach (Handler::var('connections') as $name => $connection) {
        $isConnectedNow = false;
        if (Handler::cond('dashboard_user')) {
            $confirm_message = _s('Do you really want to disconnect %s from this account?', $connection['label'] ?? '');
            $title = _s('This account is connected to %s', $connection['label'] ?? '');
        } else {
            $confirm_message = _s('Do you really want to disconnect your %s account?', $connection['label'] ?? '');
            if (Login::getSession()['type'] == 'cookie_' . $name) {
                $isConnectedNow = true;
                $confirm_message .= ' ' . _s("You will be logged out and you won't be able to login to your account using this %s account.", $connection['label'] ?? '');
            }
            $title = _s('Connected to %s', $connection['label'] ?? '');
        }
        $title .= ' ' . strtr('(%display% — %id%)', [
            '%display%' => $connection['resource_name'] ?? '',
            '%id%' => $connection['resource_id'] ?? '',
        ]); ?>
    <div id="<?php echo $name; ?>" class="account-link account-linked input-label" data-connection="<?php echo $name; ?>">
        <h3><span class="btn-icon fab fa-<?php echo $name; ?>"></span> <?php echo $connection['label']; ?><?php echo $isConnectedNow ? ' <i class="far fa-check-circle color-success" rel="toolTip" title="' . _s('Logged in') . '" data-tipTip="top"></i>' : ''; ?></h3>
        <div class="margin-bottom-5"><?php echo $title; ?></div>
        <a class="btn btn-small default" data-action="disconnect" data-connection="<?php echo $name; ?>" data-confirm-message="<?php echo $confirm_message; ?>"><i class="fas fa-trash-alt margin-right-5"></i><?php _se('Delete'); ?></a>
    </div>
<?php
    } ?>
<?php
    if (!Handler::cond('dashboard_user')) {
        $tpl = '<h3><span class="fab fa-%s"></span> %label%</h3>
        <a class="login-provider-button login-provider-button--%s" href="%u"><span class="fab fa-%s"> </span><span class="text">' . _s('Connect %label%') . '</span></a>'; ?>
        <div class="c6 phone-c1 phablet-c2 margin-top-20">
        <?php
        foreach (Handler::var('providers_enabled') as $name => $provider) {
            ?>
        <div id="<?php echo $name; ?>" class="account-link input-label<?php if (isset(Handler::var('connections')[$name])) {
                echo ' soft-hidden';
            } ?>" data-connect="<?php echo $name; ?>">

            <?php echo strtr($tpl, [
                    '%s' => $name,
                    '%label%' => $provider['label'],
                    '%u' => get_base_url('connect/' . $name),
            ]); ?>
        </div>
<?php
        } ?>
        </div>
<?php
    } ?>
