<div class="list-item c%COLUMN_SIZE_ALBUM% gutter-margin-right-bottom" data-type="album" data-id="%ALBUM_ID_ENCODED%" data-liked="%ALBUM_LIKED%" data-flag="%ALBUM_COVER_FLAG%" data-privacy="%ALBUM_PRIVACY%" data-url-short="%ALBUM_URL_SHORT%">
	<div class="list-item-image fixed-size">
		<a href="%ALBUM_URL%" class="image-container %tpl_list_item/item_cover_type%">
			%tpl_list_item/album_cover_password%
		</a>
		%tpl_list_item/item_privacy%
		%tpl_list_item/item_album_admin_tools%
	</div>
	<div class="list-item-desc">
		<div class="list-item-desc-title">
			<a class="list-item-desc-title-link" href="%ALBUM_URL%"><?php _se('Private album'); ?></a>
			<div class="list-item-from font-size-small phone-hide"><span class="fas fa-image margin-right-5"></span><?php _se('Password protected'); ?></div>
		</div>
	</div>
</div>
