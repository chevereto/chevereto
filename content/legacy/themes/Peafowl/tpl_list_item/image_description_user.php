<div class="list-item-desc">
	<div class="list-item-desc-title">
		<a href="%IMAGE_PATH_VIEWER%" class="list-item-desc-title-link" data-text="image-title" data-content="image-link">%IMAGE_TITLE%</a>
        <div class="list-item-from font-size-small"><?php _se('by %u', ['%u' => '<a href="%IMAGE_USER_URL%">%IMAGE_USER_NAME%</a>']); ?></div>
	</div>
</div>
<div class="list-item-image-tools --bottom --right">
    %tpl_list_item/item_share%
    %tpl_list_item/item_like%
</div>
