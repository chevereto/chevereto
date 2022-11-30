<?php
use function Chevereto\Legacy\G\get_global;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<script>
	$(document).ready(function() {
		if(typeof CHV == "undefined") {
			CHV = {obj: {}, fn: {}, str:{}};
		} else {
			if(typeof CHV.obj.embed_share_tpl == "undefined") {
				CHV.obj.embed_share_tpl = {};
			}
			if(typeof CHV.obj.embed_upload_tpl == "undefined") {
				CHV.obj.embed_upload_tpl = {};
			}
		}
		CHV.obj.embed_share_tpl = <?php $embed_share_tpl = get_global('embed_share_tpl'); echo json_encode($embed_share_tpl); ?>;
		CHV.obj.embed_upload_tpl = <?php $embed_upload_tpl = get_global('embed_upload_tpl'); echo json_encode($embed_upload_tpl); ?>;
	});
</script>
<div data-modal="form-embed-codes" class="hidden">
	<span class="modal-box-title"><i class="fas fa-code"></i> <?php _se('Embed codes'); ?></span>
    <div class="image-preview"></div>
	<div class="input-label margin-bottom-0">
		<div class="c7 margin-bottom-10">
			<select name="form-embed-toggle" id="form-embed-toggle" class="text-input" data-combo="form-embed-toggle-combo">
				<?php
                    foreach (get_global('embed_share_tpl') as $key => $value) {
                        echo '<optgroup label="' . $value['label'] . '">' . "\n";
                        foreach ($value['options'] as $k => $v) {
                            if ($k === 'delete-links') {
                                continue;
                            }
                            echo '	<option value="' . $k . '" data-size="' . $v["size"] . '">' . $v["label"] . '</option>' . "\n";
                        }
                        echo '</optgroup>';
                    }
                ?>
			</select>
		</div>
		<div id="form-embed-toggle-combo">
			<?php
                $i = 0;
                foreach (get_global('embed_share_tpl') as $key => $value) {
                    foreach ($value['options'] as $k => $v) {
                        echo '<div data-combo-value="' . $k . '" class="switch-combo' . ($i > 0 ? " soft-hidden" : "") . '">
							<textarea id="modal-embed-code-' . $i . '" class="r3 resize-vertical" name="' . $k . '" data-size="' . $v["size"] . '" data-focus="select-all"></textarea>
							<button type="button" class="input-action" data-action="copy" data-action-target="#modal-embed-code-' . $i . '"><i class="far fa-copy"></i> ' . _s('copy') . '</button>
						</div>' . "\n";
                        $i++;
                    }
                }
            ?>
		</div>
	</div>
</div>
