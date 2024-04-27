/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function () {
    var resizedFinished;
    $(window).resize(function (e) {
        clearTimeout(resizedFinished);
        resizedFinished = setTimeout(function () {
            // if ($("body#image").exists() && prevWidth != $(window).width()) {
            //     CHV.fn.image_viewer_full_fix();
            // }
            CHV.fn.uploader.boxSizer();
            CHV.fn.bindSelectableItems();
            CHV.fn.listingViewer.placeholderSizing();
            prevWidth = $(window).width();
            prevHeight = $(window).height();
        }, 10);
    });

    if (window.opener) {
        $(window).on("load", function (e) {
            window.opener.postMessage({
                id: window.name,
                requestAction: "postSettings",
            },
                "*"
            );
        });
        $(window).on("message", function (e) {
            var data = e.originalEvent.data;
            if (
                typeof data.id == typeof undefined ||
                typeof data.settings == typeof undefined
            ) {
                return;
            }
            if (window.name !== data.id) {
                return;
            }
            CHV.obj.opener.uploadPlugin[data.id] = data.settings;
        });
    }
    // Landing fancy load
    if ($("#home-cover, #maintenance-wrapper, #login").exists()) {
        var landing_src = $("#maintenance-wrapper").exists() ?
            $("#maintenance-wrapper")
                .css("background-image")
                .slice(4, -1)
                .replace(/^\"|\"$/g, "") :
            $(".home-cover-img", "#home-cover-slideshow").first().attr("data-src");

        function showHomeCover() {
            $("body").addClass("load");
            if (!$("#maintenance-wrapper").exists()) {
                $(".home-cover-img", "#home-cover-slideshow")
                    .first()
                    .css("background-image", "url(" + landing_src + ")")
                    .addClass("animate-in--alt")
                    .removeAttr("data-src");
            }
            setTimeout(function () {
                setTimeout(function () {
                    $("body").addClass("loaded");
                }, 400 * 3);

                setTimeout(function () {
                    showHomeSlideshow();
                }, 7000);
            }, 400 * 1.5);
        }

        var showHomeSlideshowInterval = function () {
            setTimeout(function () {
                showHomeSlideshow();
            }, 8000);
        };

        function showHomeSlideshow() {
            var $image = $(
                ".home-cover-img[data-src]",
                "#home-cover-slideshow"
            ).first();
            var $images = $(".home-cover-img", "#home-cover-slideshow");
            if ($image.length == 0) {
                if ($images.length == 1) return;
                $images.first().removeClass("animate-in");
                $("#home-cover-slideshow").append($images.first());
                setTimeout(function () {
                    $(".home-cover-img:last", "#home-cover-slideshow").addClass(
                        "animate-in"
                    );
                }, 20);
                setTimeout(function () {
                    $(".home-cover-img:not(:last)", "#home-cover-slideshow").removeClass(
                        "animate-in"
                    );
                }, 4000);
                showHomeSlideshowInterval();
            } else {
                var src = $image.attr("data-src");
                $("<img/>")
                    .attr("src", src)
                    .on("load error", function () {
                        $(this).remove();
                        $image
                            .css("background-image", "url(" + src + ")")
                            .addClass("animate-in")
                            .removeAttr("data-src");
                        setTimeout(function () {
                            $(
                                ".home-cover-img:not(:last)",
                                "#home-cover-slideshow"
                            ).removeClass("animate-end animate-in--alt");
                        }, 2000);
                        showHomeSlideshowInterval();
                    });
            }
        }

        if (landing_src) {
            $("<img/>")
                .attr("src", landing_src)
                .on("load error", function () {
                    $(this).remove();
                    showHomeCover();
                });
        } else {
            showHomeCover();
        }
    }

    // Set the anywhere objects, just for shorter calling in $.
    var anywhere_upload = CHV.fn.uploader.selectors.root,
        anywhere_upload_queue = CHV.fn.uploader.selectors.queue,
        $anywhere_upload = $(anywhere_upload),
        $anywhere_upload_queue = $(anywhere_upload_queue);

    $(document).on("click", "[data-action=top-bar-upload]", function (e) {
        if (!$("body").is("#upload") && $(this).data("link") === 'js') {
            CHV.fn.uploader.toggle({ reset: false });
        }
        if ($(this).data("link") !== 'page') {
            e.preventDefault();
            e.stopPropagation();
        }
    });

    var timeoutPalette;
    $(document).on("click mouseover mouseout", "[data-action=palette]", function (e) {
        clearTimeout(timeoutPalette);
        e.preventDefault();
        var palette = $(this).data("palette");
        switch (e.type) {
            case "mouseover":
                timeoutPalette = setTimeout(function () {
                    CHV.fn.Palettes.preview(palette);
                }, 1000);
                break;
            case "mouseout":
                palette = $("html").attr("data-palette") || '';
                CHV.fn.Palettes.set(palette);
                break;
            case "click":
                e.stopPropagation();
                $("[data-action=palette]", "[data-content=palettes]").removeClass("current");
                $(this).addClass("current");
                CHV.fn.Palettes.set(palette);
                CHV.fn.Palettes.save();
                break;
        }
    });
    $(document).on("change", "#palettes", function (e) {
        CHV.fn.Palettes.set(this.value);
        CHV.fn.Palettes.save();
    });

    $("[data-action=close-upload]", $anywhere_upload).click(function () {
        if ($anywhere_upload.is(":animated")) {
            return;
        }
        $("[data-action=top-bar-upload]", "#top-bar").trigger("click");
    });

    // Reset upload box
    $("[data-action=reset-upload]", $anywhere_upload).click(function () {
        if (CHV.fn.uploader.isUploading) {
            $(
                "[data-action=cancel-upload-remaining], [data-action=cancel-upload]",
                $anywhere_upload
            ).trigger("click");
        }
        CHV.fn.uploader.reset();
    });

    // Cancel remaining uploads
    $(
        "[data-action=cancel-upload-remaining], [data-action=cancel-upload]",
        $anywhere_upload
    ).click(function () {
        CHV.fn.uploader.isUploading = false;
        $("[data-action=cancel]", $anywhere_upload_queue).click();
        if (Object.size(CHV.fn.uploader.results.success) > 0) {
            CHV.fn.uploader.displayResults();
            return;
        } else {
            CHV.fn.uploader.reset();
        }
    });

    // Toggle upload privacy
    $(document).on(
        "click",
        "[data-action=upload-privacy]:not(disabled)",
        function (e) {
            if (e.isDefaultPrevented()) return;
            current_privacy = $(this).data("privacy");
            target_privacy = current_privacy == "public" ? "private" : "public";
            this_lock = $(".icon", this).data("lock");
            this_unlock = $(".icon", this).data("unlock");
            $(".icon", this)
                .removeClass(this_lock + " " + this_unlock)
                .addClass(current_privacy == "public" ? this_lock : this_unlock);
            $(this).data("privacy", target_privacy);

            $("[data-action=upload-privacy-copy]").html(
                $("[data-action=upload-privacy]").html()
            );

            $upload_button = $("[data-action=upload]", $anywhere_upload);
            $upload_button.text($upload_button.data(target_privacy));

            $(this).tipTip("hide");
        }
    );

    // Do the thing when the fileupload changes
    $(CHV.fn.uploader.selectors.file + ", " + CHV.fn.uploader.selectors.camera)
        .on("change", function (e) {
            if (!$(CHV.fn.uploader.selectors.root).data("shown")) {
                CHV.fn.uploader.toggle({
                    callback: function (e) {
                        CHV.fn.uploader.add(e);
                    },
                },
                    e
                );
            } else {
                CHV.fn.uploader.add(e);
            }
        })
        .on("click", function (e) {
            if ($(this).data("login-needed") && !PF.fn.is_user_logged()) {
                return;
            }
        });

    function isFileTransfer(e) {
        var e = e.originalEvent,
            isFileTransfer = false;
        if (e.dataTransfer.types) {
            for (var i = 0; i < e.dataTransfer.types.length; i++) {
                if (e.dataTransfer.types[i] == "Files") {
                    isFileTransfer = true;
                    break;
                }
            }
        }
        return isFileTransfer;
    }

    if ($(CHV.fn.uploader.selectors.root).exists()) {
        $("body").on({
            dragenter: function (e) {
                e.preventDefault();
                if (!isFileTransfer(e)) {
                    return false;
                }
                if (!$(CHV.fn.uploader.selectors.dropzone).exists()) {
                    $("body").append(
                        $(
                            '<div id="' +
                            CHV.fn.uploader.selectors.dropzone.replace("#", "") +
                            '"/>'
                        ).css({
                            width: "100%",
                            height: "100%",
                            position: "fixed",
                            /* opacity: 0.5, background: "red",*/
                            zIndex: 1000,
                            left: 0,
                            top: 0,
                        })
                    );
                }
            },
        });
        $(document).on({
            dragover: function (e) {
                e.preventDefault();
                if (!isFileTransfer(e)) {
                    return false;
                }
                if (!$(CHV.fn.uploader.selectors.root).data("shown")) {
                    CHV.fn.uploader.toggle({
                        reset: false,
                    });
                }
            },
            dragleave: function (e) {
                $(CHV.fn.uploader.selectors.dropzone).remove();
                if ($.isEmptyObject(CHV.fn.uploader.files)) {
                    CHV.fn.uploader.toggle();
                }
            },
            drop: function (e) {
                e.preventDefault();
                CHV.fn.uploader.add(e);
                $(CHV.fn.uploader.selectors.dropzone).remove();
            },
        },
            CHV.fn.uploader.selectors.dropzone
        );
    }

    $(document).on("keyup change", "[data-action=resize-combo-input]", function (
        e
    ) {
        var $parent = $(this).closest("[data-action=resize-combo-input]");
        var $input_width = $("[name=form-width]", $parent);
        var $input_height = $("[name=form-height]", $parent);
        var ratio = $input_width.data("initial") / $input_height.data("initial");
        var image = {
            width: Math.round($input_width.prop("value") / ratio),
            height: Math.round($input_height.prop("value") * ratio),
        };
        if ($(e.target).is($input_width)) {
            $input_height.prop("value", Math.round(image.width));
        } else {
            $input_width.prop("value", Math.round(image.height));
        }
    });

    $(document).on(
        "click",
        anywhere_upload_queue + " [data-action=edit]",
        function () {
            var $item = $(this).closest("li"),
                $queue = $item.closest("ul"),
                id = $item.data("id"),
                file = CHV.fn.uploader.files[id]
                media = file.type.substring(0, file.type.indexOf("/"));
            var modal = PF.obj.modal.selectors.root;
            var queueObject = $.extend({}, file.formValues || file.parsedMeta);
            var injectKeys = ["album_id", "category_id", "nsfw"];
            for (var i = 0; i < injectKeys.length; i++) {
                var key = injectKeys[i];
                if (typeof queueObject[key] == typeof undefined) {
                    var $object = $(
                        "[name=upload-" + key.replace("_", "-") + "]",
                        CHV.fn.uploader.selectors.root
                    );
                    var value = $object.prop(
                        $object.is(":checkbox") ? "checked" : "value"
                    );
                    queueObject[key] = $object.is(":checkbox") ?
                        value ?
                            "1" :
                            null :
                        value;
                }
            }
            PF.fn.modal.call({
                type: "html",
                template: $("#anywhere-upload-edit-item").html(),
                callback: function () {
                    $("[data-content=icon]", modal).addClass('fa-file-' + media);
                    var imageMaxCfg = {
                        width: CHV.obj.config.image.max_width != 0 ?
                            CHV.obj.config.image.max_width : queueObject.width,
                        height: CHV.obj.config.image.max_height != 0 ?
                            CHV.obj.config.image.max_height : queueObject.height,
                    };

                    var imageMax = $.extend({}, imageMaxCfg);
                    var ratio = queueObject.width / queueObject.height;

                    imageMax.width = Math.round(imageMaxCfg.height * ratio);
                    imageMax.height = Math.round(imageMaxCfg.width / ratio);

                    if (imageMax.height > imageMaxCfg.height) {
                        imageMax.height = imageMaxCfg.height;
                        imageMax.width = Math.round(imageMax.height * ratio);
                    }

                    if (imageMax.width > imageMaxCfg.width) {
                        imageMax.width = imageMaxCfg.width;
                        imageMax.height = Math.round(imageMax.width / ratio);
                    }

                    $.each(queueObject, function (i, v) {
                        var name = "[name=form-" + i.replace(/_/g, "-") + "]";
                        var $input = $(name, modal);

                        if (!$input.exists()) return true;

                        if ($input.is(":checkbox")) {
                            $input.prop("checked", $input.attr("value") == v);
                        } else if ($input.is("select")) {
                            var $option = $input.find("[value=" + v + "]");
                            if (!$option.exists()) {
                                $option = $input.find("option:first");
                            }
                            $option.prop("selected", true);
                        } else {
                            $input.prop("value", v);
                        }

                        if (i == "width" || i == "height") {
                            var max = imageMax[i];
                            var value = file.parsedMeta[i] > max ? max : file.parsedMeta[i];
                            $input
                                .prop("max", value)
                                .data("initial", file.parsedMeta[i])
                                .prop("value", value);
                            if(media !== "image") {
                                $input
                                    .prop("disabled", true)
                                    .closest("[data-action=resize-combo-input]").hide();
                            }
                        }
                    });

                    if (file.parsedMeta.mimetype !== "image/gif") {
                        $("[ data-content=animated-gif-warning]", modal).remove();
                    }

                    $(".image-preview", modal).append(
                        $("<canvas/>", {
                            class: "canvas checkered-background",
                        })
                    );
                    var source_canvas = $(".queue-item[data-id=" + id + "] .preview .canvas")[0];
                    var target_canvas = $(".image-preview .canvas", modal)[0];
                    target_canvas.width = source_canvas.width;
                    target_canvas.height = source_canvas.height;
                    var target_canvas_ctx = target_canvas.getContext("2d");
                    target_canvas_ctx.drawImage(source_canvas, 0, 0);
                },
                confirm: function () {
                    if (!PF.fn.form_modal_has_changed()) {
                        PF.fn.modal.close();
                        return;
                    }

                    // Validations (just in case)
                    var errors = false;
                    $.each(["width", "height"], function (i, v) {
                        var $input = $("[name=form-" + v + "]", modal);
                        var input_val = parseInt($input.val());
                        var min_val = parseInt($input.attr("min"));
                        var max_val = parseInt($input.attr("max"));
                        if (input_val > max_val || input_val < min_val) {
                            $input.highlight();
                            errors = true;
                            return true;
                        }
                    });

                    if (errors) {
                        PF.fn.growl.expirable(
                            PF.fn._s("Check the errors in the form to continue.")
                        );
                        return false;
                    }

                    if (typeof file.formValues == typeof undefined) {
                        // Stock formvalues object
                        file.formValues = {
                            title: null,
                            category_id: null,
                            width: null,
                            height: null,
                            nsfw: null,
                            expiration: null,
                            description: null,
                            album_id: null,
                        };
                    }

                    $(":input[name]", modal).each(function (i, v) {
                        var key = $(this)
                            .attr("name")
                            .replace("form-", "")
                            .replace(/-/g, "_");
                        if (typeof file.formValues[key] == typeof undefined) return true;
                        file.formValues[key] = $(this).is(":checkbox") ?
                            $(this).is(":checked") ?
                                $(this).prop("value") :
                                null :
                            $(this).prop("value");
                    });

                    CHV.fn.uploader.files[id].formValues = file.formValues;

                    return true;
                },
            });
        }
    );

    // Remove item from queue
    $(document).on(
        "click",
        anywhere_upload_queue + " [data-action=cancel]",
        function () {
            var $item = $(this).closest("li"),
                $queue = $item.closest("ul"),
                id = $item.data("id"),
                queue_height = $queue.height(),
                item_xhr_cancel = false;

            if ($item.hasClass("completed") || $item.hasClass("failed")) {
                return;
            }

            $("#tiptip_holder").hide();

            $item.tipTip("destroy").remove();

            if (queue_height !== $queue.height()) {
                CHV.fn.uploader.boxSizer();
            }
            if (!$("li", $anywhere_upload_queue).exists()) {
                $(
                    "[data-group=upload-queue-ready], [data-group=upload-queue], [data-group=upload-queue-ready]",
                    $anywhere_upload
                ).css("display", "");
            }

            if (
                CHV.fn.uploader.files[id] &&
                typeof CHV.fn.uploader.files[id].xhr !== "undefined"
            ) {
                CHV.fn.uploader.files[id].xhr.abort();
                item_xhr_cancel = true;
            }

            if (
                typeof CHV.fn.uploader.files[id] !== typeof undefined &&
                typeof CHV.fn.uploader.files[id].fromClipboard !== typeof undefined
            ) {
                var c_md5 = CHV.fn.uploader.files[id].md5;
                var c_index = CHV.fn.uploader.clipboardImages.indexOf(c_md5);
                if (c_index > -1) {
                    CHV.fn.uploader.clipboardImages.splice(c_index, 1);
                }
            }

            delete CHV.fn.uploader.files[id];

            CHV.fn.uploader.queueSize();

            if (Object.size(CHV.fn.uploader.files) == 0) {
                // No queue left
                // Null result ?
                if (!("success" in CHV.fn.uploader) ||
                    !("results" in CHV.fn.uploader) ||
                    (Object.size(CHV.fn.uploader.results.success) == 0 &&
                        Object.size(CHV.fn.uploader.results.error) == 0)
                ) {
                    CHV.fn.uploader.reset();
                }
            } else {
                // Do we need to process the next item?
                if (item_xhr_cancel && $("li.waiting", $queue).first().length !== 0) {
                    CHV.fn.uploader.upload($("li.waiting", $queue).first());
                }
            }
        }
    );

    $(document).on("click", "[data-action=upload]", function () {
        if (typeof CHV.obj.logged_user === "undefined" && $('#upload-tos').prop('checked') === false) {
            PF.fn.growl.call("You must agree to the terms and privacy policy");
            return;
        }
        $(
            "[data-group=upload], [data-group=upload-queue-ready]",
            $anywhere_upload
        ).hide();
        $anywhere_upload
            .removeClass("queueReady")
            .addClass("queueUploading")
            .find("[data-group=uploading]")
            .show();
        CHV.fn.uploader.queueSize();
        CHV.fn.uploader.canAdd = false;
        $queue_items = $("li", $anywhere_upload_queue);
        $queue_items.addClass("uploading waiting");
        CHV.fn.uploader.timestamp = new Date().getTime();
        CHV.fn.uploader.upload($queue_items.first("li"));
    });

    if ($("body#user").exists()) {
        if (PF.obj.listing.query_string.page > 1) {
            var State = History.getState();
            if (State.data && typeof State.data.scrollTop !== "undefined") {
                if ($(window).scrollTop() !== State.data.scrollTop) {
                    $(window).scrollTop(State.data.scrollTop);
                }
            } else {
                //var scrollTop = $(".follow-scroll").offset().top - $(".follow-scroll").height();
                var scrollTop = $("#background-cover").height() - 160;
                $("html, body").animate({
                    scrollTop: scrollTop,
                },
                    0
                );
            }
        }
    }

    if ($("#top-bar-shade").exists() && $("#top-bar-shade").css("opacity")) {
        $("#top-bar-shade").data(
            "initial-opacity",
            Number($("#top-bar-shade").css("opacity"))
        );
    }

    CHV.fn.bindSelectableItems();

    if ($("body#image").exists()) {
        if ($(CHV.obj.image_viewer.selector + " [data-load=full]").length > 0) {
            $(document).on("click", CHV.obj.image_viewer.loader, function (e) {
                CHV.fn.viewerLoadImage();
            });
            if (
                $(CHV.obj.image_viewer.loader).data("size") >
                CHV.obj.config.image.load_max_filesize.getBytes()
            ) {
                $(CHV.obj.image_viewer.loader).css("display", "block");
            } else {
                CHV.fn.viewerLoadImage();
            }
        }
        new MutationObserver(() => {
            if (
                $("html").height() > $(window).innerHeight() &&
                !$("html").hasClass("scrollbar-y")
            ) {
                $("html").addClass("scrollbar-y");
                $(document).data({
                    width: $(this).width(),
                    height: $(this).height(),
                });
            }
        }).observe(document, { childList: true });
        $(document).on("keyup", function (e) {
            var $this = $(e.target),
                event = e.originalEvent;
            if ($this.is(":input")) {
                return;
            } else {
                if (
                    CHV.obj.image_viewer.$navigation.exists() &&
                    (event.key == "ArrowLeft" || event.key == "ArrowRight")
                ) {
                    var navigation_jump_url = $(
                        "[data-action=" + (event.key == "ArrowLeft" ? "prev" : "next") + "]",
                        CHV.obj.image_viewer.$navigation
                    ).attr("href");
                    if (
                        typeof navigation_jump_url !== "undefined" &&
                        navigation_jump_url !== ""
                    ) {
                        window.location = $(
                            "[data-action=" + (event.key == "ArrowLeft" ? "prev" : "next") + "]",
                            CHV.obj.image_viewer.$navigation
                        ).attr("href");
                    }
                }
            }
        });
    }

    $(document)
        .on("click", CHV.obj.image_viewer.container + " img", function (e) {
            if($(CHV.obj.image_viewer.loader).exists()) {
                $(CHV.obj.image_viewer.loader).trigger("click");
                return;
            }
            $(this).toggleClass("zoom-natural");
        })
        .on("contextmenu", CHV.obj.image_viewer.container, function (e) {
            if (!CHV.obj.config.image.right_click) {
                e.preventDefault();
                return false;
            }
        });

    $(document).on(
        "contextmenu",
        "html.device-mobile a.image-container",
        function (e) {
            e.preventDefault();
            e.stopPropagation();
        }
    );

    $(document).on("keyup", "input[data-dashboard-tool]", function (e) {
        if (e.keyCode == 13) {
            var $button = $("[data-action=" + $(this).data("dashboard-tool") + "]");
            $button.click();
        }
    });

    $(document).on("click", "[data-action=dashboardTool]", function (e) {
        e.preventDefault();
        var tool = $(this).data("tool");
        var dataSet = $(this).data("data");
        var data = $.extend({}, dataSet);
        var inputs = {};
        for (var key in data) {
            var val = $(data[key]).val();
            if ($(data[key]).prop("disabled") || !val) {
                return;
            }
            inputs[key] = $(data[key]);
            data[key] = val;
        }
        data.action = tool;
        var ajaxObj = {
            type: "GET", // !
            cache: false,
        };
        ajaxObj.data = data;
        var $parent = $(this).closest(".input-label");
        var validate = true;
        var message;

        if (validate == false) {
            PF.fn.growl.expirable(message);
            return;
        }
        for (var key in inputs) {
            // inputs[key].prop("disabled", true);
        }
        PF.fn.loading.inline($(".loading", $parent), {
            size: "small",
            valign: "middle",
        });
        $parent.find(".btn .text").hide();
        $.ajax(ajaxObj).complete(function (XHR) {
            var response = XHR.responseJSON;
            // inputs[key].prop("disabled", false);
            $(".loading", $parent).empty();
            $parent.find(".btn .text").show();
            if (
                response.status_code == 200 &&
                typeof response.success.redirURL !== typeof undefined
            ) {
                window.location.href = response.success.redirURL;
                return;
            }
            PF.fn.growl.call(
                response[response.status_code == 200 ? "success" : "error"].message
            );
        });
    });

    // Third-party plugin, magic comes in 3...
    $(document).on("click", "[data-action=openerPostMessage]", function (e) {
        if (!window.opener) return;
        e.preventDefault();
        var target_attr = "data-action-target";
        var $target = $(
            $(this).is("[" + target_attr + "]") ? $(this).attr(target_attr) : this
        );
        var val = $target[$target.is(":input") ? "val" : "html"]();
        window.opener.postMessage({
            id: window.name,
            message: val,
        },
            "*"
        );
    });

    /*
        // Input copy
        $(document).on("mouseenter mouseleave", ".input-copy", function(e){
            if(navigator.userAgent.match(/(iPad|iPhone|iPod)/i)) {
                return;
            }
            $(".btn-copy", this)[e.type == "mouseenter" ? "show" : "hide"]();
        });

        $(document).on("click", ".input-copy .btn-copy", function(){
            var $input = $(this).closest(".input-copy").find("input");
            $(this).hide();
            $input.highlight();
        });
        */

    /**
     * USER SIDE LISTING EDITOR
     * -------------------------------------------------------------------------------------------------
     */

    $(document).on("click", "[data-action=list-tools] [data-action]", function (
        e
    ) {
        var $this = $(e.target),
            $list_item = $this.closest("[data-id]");
        if (
            $list_item &&
            $list_item.find("[data-action=select]").exists() &&
            (e.ctrlKey || e.metaKey) &&
            e.altKey
        ) {
            CHV.fn.list_editor.toggleSelectItem(
                $list_item, !$list_item.hasClass("selected")
            );
            e.preventDefault();
            e.stopPropagation();
        }
    });

    // On listing ajax, clear the "Clear selection" toggle
    PF.fn.listing.ajax.callback = function (XHR) {
        if (XHR.status !== 200) return;
        CHV.fn.list_editor.listMassActionSet("select");
    };

    // Select all
    $(document).on("click", "[data-action=list-select-all]", function (e) {
        if ($(this).closest('.disabled').exists()) {
            return false;
        }
        CHV.fn.list_editor.selectAll(e);
    });
    // Clear all
    $(document).on("click", "[data-action=list-clear-all]", function () {
        // PF.fn.close_pops();
        CHV.fn.list_editor.clearSelection();
    });


    $(document).on("click", "[data-action=share]", function (e) {
        if($(PF.obj.modal.selectors.box).exists()) {
            return;
        }
        var $list_item;
        if ($('.viewer:visible').exists()) {
            $list_item = $(PF.obj.listing.selectors.list_item + '[data-id=' + $('.viewer').attr('data-id') + ']', '.content-listing').first();
        } else {
            $list_item = $(this).closest(PF.obj.listing.selectors.list_item).first();
        }
        var url;
        var image;
        var title;
        var link;
        var modal_tpl;
        var modal_sel = "#modal-share";
        if ($list_item.exists()) {
            modal_tpl = CHV.fn.modal.getTemplateWithPreview(modal_sel, $list_item);
            if (typeof $list_item.attr("data-type") === "undefined") {
                console.error("Error: data-type not defined");
                return;
            }
            link = $list_item.find('.list-item-desc-title-link').first();
            image = $list_item.find('.image-container img').first().attr('src');
            url = $list_item.attr('data-url-short');
        } else {
            modal_tpl = $(modal_sel).html();
            dealing_with = CHV.obj.resource.type;
            url = window.location.href;
            image = $('#image-viewer-container').find('img').first().attr('src');
            link = $(".header > h1 > a");
        }
        title = PF.fn.htmlEncode(link.text());

        var privacy = $list_item.data("privacy") || CHV.obj.resource.privacy;
        var privacy_notes = '';
        switch (privacy) {
            case 'private_but_link':
                privacy_notes = PF.fn._s('Note: This content is private but anyone with the link will be able to see this.');
                break;
            case 'password':
                privacy_notes = PF.fn._s('Note: This content is password protected. Remember to pass the content password to share.');
                break;
            case 'private':
                privacy_notes = PF.fn._s('Note: This content is private. Change privacy to "public" to share.');
                break;
        }
        modal_tpl = modal_tpl
            .replaceAll('__url__', url)
            .replaceAll('__image__', image)
            .replaceAll('__title__', title)
            .replaceAll('__privacy__', privacy)
            .replaceAll('__privacy_notes__', privacy_notes);
        PF.fn.modal.call({
            type: "html",
            buttons: false,
            template: modal_tpl,
        });
    });

    $(document).on("click", "[data-action=list-tools] [data-action]", function (
        e
    ) {
        if (e.isPropagationStopped()) return false;

        var $list_item;
        if ($('.viewer:visible').exists()) {
            $list_item = $(PF.obj.listing.selectors.list_item + '[data-id=' + $('.viewer').attr('data-id') + ']', '.content-listing').first();
        } else {
            $list_item = $(this).closest(PF.obj.listing.selectors.list_item).first();
        }
        var id = $list_item.attr("data-id");

        if (typeof $list_item.attr("data-type") !== "undefined") {
            dealing_with = $list_item.attr("data-type");
        } else {
            console.error("Error: data-type not defined");
            return;
        }

        var $targets = $("[data-type=" + dealing_with + "][data-id=" + id + "]");
        var dealing_with;

        switch ($(this).data("action")) {
            case "select":
                CHV.fn.list_editor.toggleSelectItem(
                    $list_item, !$list_item.hasClass("selected")
                );
                break;

            case "edit":
                var modal_source = "[data-modal=form-edit-single]";
                switch (dealing_with) {
                    case "image":
                        $("[name=form-image-title]", modal_source).attr({
                            value: $list_item.attr("data-title"),
                            autocomplete: "off"
                        });
                        $("[name=form-image-description]", modal_source).html(
                            PF.fn.htmlEncode($list_item.data("description"))
                        );

                        $("[name=form-album-id]", modal_source)
                            .find("option")
                            .removeAttr("selected");
                        $("[name=form-album-id]", modal_source)
                            .find(
                                "[value=" +
                                $list_item.data(dealing_with == "image" ? "album-id" : "id") +
                                "]"
                            )
                            .attr("selected", true);

                        $("[name=form-category-id]", modal_source)
                            .find("option")
                            .removeAttr("selected");
                        $("[name=form-category-id]", modal_source)
                            .find("[value=" + $list_item.data("category-id") + "]")
                            .attr("selected", true);

                        $("[name=form-nsfw]", modal_source).attr(
                            "checked",
                            $list_item.data("flag") == "unsafe"
                        );

                        // Just in case...
                        $("[name=form-album-name]", modal_source).attr({ value: "", autocomplete: "off" });
                        $("[name=form-album-description]", modal_source).html("");
                        $("[name=form-privacy]", modal_source)
                            .find("option")
                            .removeAttr("selected");

                        break;
                    case "album":
                        $("[data-action=album-switch]", modal_source).remove();
                        $("[name=form-album-name]", modal_source).attr({
                            value: $list_item.data("name"),
                            autocomplete: "off"
                        });
                        $("[name=form-album-description]", modal_source).html(
                            PF.fn.htmlEncode($list_item.data("description"))
                        );
                        $("[name=form-privacy]", modal_source)
                            .find("option")
                            .removeAttr("selected");
                        $("[name=form-privacy]", modal_source)
                            .find("[value=" + $list_item.data("privacy") + "]")
                            .attr("selected", true);
                        if ($list_item.data("privacy") == "password") {
                            $("[data-combo-value=password]").show();
                            $("[name=form-album-password]", modal_source).attr(
                                "value",
                                $list_item.data("password")
                            );
                        } else {
                            $("[data-combo-value=password]").hide();
                            $("[name=form-album-password]", modal_source).attr("value", "");
                        }
                        break;
                }

                PF.fn.modal.call({
                    type: "html",
                    template: CHV.fn.modal.getTemplateWithPreview(modal_source, $list_item),
                    ajax: {
                        url: PF.obj.config.json_api,
                        deferred: {
                            success: function (XHR) {
                                CHV.fn.list_editor.updateItem(
                                    "[data-type=" + dealing_with + "][data-id=" + id + "]",
                                    XHR.responseJSON[dealing_with],
                                    "edit"
                                );
                            },
                        },
                    },
                    confirm: function () {
                        var $modal = $(PF.obj.modal.selectors.root);

                        if (
                            (dealing_with == "image" || dealing_with == "album") &&
                            $("[data-content=form-new-album]", $modal).is(":visible") &&
                            $("[name=form-album-name]", $modal).val() == ""
                        ) {
                            PF.fn.growl.call(PF.fn._s("You must enter the album name."));
                            $("[name=form-album-name]", $modal).highlight();
                            return false;
                        }

                        if (!PF.fn.form_modal_has_changed()) {
                            PF.fn.modal.close();
                            return;
                        }

                        PF.obj.modal.form_data = {
                            action: "edit", // use the same method applied in viewer
                            edit: $list_item.data("type"),
                            single: true,
                            owner: CHV.obj.resource.user.id,
                            editing: {
                                id: id,
                                description: $(
                                    "[name=form-" + dealing_with + "-description]",
                                    $modal
                                ).val(),
                            },
                        };

                        switch (dealing_with) {
                            case "image":
                                PF.obj.modal.form_data.editing.title = $(
                                    "[name=form-image-title]",
                                    $modal
                                ).val();
                                PF.obj.modal.form_data.editing.category_id =
                                    $("[name=form-category-id]", $modal).val() || null;
                                PF.obj.modal.form_data.editing.nsfw = $(
                                    "[name=form-nsfw]",
                                    $modal
                                ).prop("checked") ?
                                    1 :
                                    0;
                                break;
                            case "album":
                                PF.obj.modal.form_data.editing.name = $(
                                    "[name=form-album-name]",
                                    $modal
                                ).val();
                                PF.obj.modal.form_data.editing.privacy = $(
                                    "[name=form-privacy]",
                                    $modal
                                ).val();
                                if (PF.obj.modal.form_data.editing.privacy == "password") {
                                    PF.obj.modal.form_data.editing.password = $(
                                        "[name=form-album-password]",
                                        $modal
                                    ).val();
                                }
                                break;
                        }

                        PF.obj.modal.form_data.editing.new_album = $(
                            "[data-content=form-new-album]",
                            $modal
                        ).is(":visible");

                        if (PF.obj.modal.form_data.editing.new_album) {
                            PF.obj.modal.form_data.editing.album_name = $(
                                "[name=form-album-name]",
                                $modal
                            ).val();
                            PF.obj.modal.form_data.editing.album_privacy = $(
                                "[name=form-privacy]",
                                $modal
                            ).val();
                            if (PF.obj.modal.form_data.editing.album_privacy == "password") {
                                PF.obj.modal.form_data.editing.album_password = $(
                                    "[name=form-album-password]",
                                    $modal
                                ).val();
                            }
                            PF.obj.modal.form_data.editing.album_description = $(
                                "[name=form-album-description]",
                                $modal
                            ).val();
                        } else {
                            PF.obj.modal.form_data.editing.album_id = $(
                                "[name=form-album-id]",
                                $modal
                            ).val();
                        }

                        return true;
                    },
                });
                break;

            case "create-album":
            case "move": // Move or create album
                var template = $(this).data("action") == "move" ?
                    "form-move-single" :
                    "form-create-album",
                    modal_source = "[data-modal=" + template + "]";
                $("[name=form-album-id]", modal_source)
                    .find("option")
                    .removeAttr("selected");
                $("[name=form-album-id]", modal_source)
                    .find(
                        "[value=" +
                        $list_item.data(dealing_with == "image" ? "album-id" : "id") +
                        "]"
                    )
                    .attr("selected", true);
                $("[name=form-album-name]", modal_source).attr({ value: "", autocomplete: "off" });
                $("[name=form-album-description]", modal_source).html("");
                $("[name=form-privacy]", modal_source)
                    .find("option")
                    .removeAttr("selected");

                PF.fn.modal.call({
                    type: "html",
                    template: CHV.fn.modal.getTemplateWithPreview(modal_source, $targets),
                    ajax: {
                        url: PF.obj.config.json_api,
                        deferred: {
                            success: function (XHR) {
                                CHV.fn.list_editor.updateMoveItemLists(
                                    XHR.responseJSON,
                                    dealing_with,
                                    $targets
                                );
                            },
                        },
                    },
                    load: function () {
                        //$("[name=form-album-id]", PF.obj.modal.selectors.root).focus();
                    },
                    confirm: function () {
                        var $modal = $(PF.obj.modal.selectors.root);

                        if (
                            $("[data-content=form-new-album]", $modal).is(":visible") &&
                            $("[name=form-album-name]", $modal).val() == ""
                        ) {
                            PF.fn.growl.call(PF.fn._s("You must enter the album name."));
                            $("[name=form-album-name]", $modal).highlight();
                            return false;
                        }

                        if (!PF.fn.form_modal_has_changed()) {
                            PF.fn.modal.close();
                            return;
                        }

                        PF.obj.modal.form_data = {
                            action: "edit", // use the same method applied in viewer
                            edit: $list_item.data("type"),
                            single: true,
                            owner: CHV.obj.resource.user.id,
                            editing: {
                                id: id,
                            },
                        };

                        PF.obj.modal.form_data.editing.new_album = $(
                            "[data-content=form-new-album]",
                            $modal
                        ).is(":visible");

                        if (PF.obj.modal.form_data.editing.new_album) {
                            PF.obj.modal.form_data.editing.album_name = $(
                                "[name=form-album-name]",
                                $modal
                            ).val();
                            PF.obj.modal.form_data.editing.album_privacy = $(
                                "[name=form-privacy]",
                                $modal
                            ).val();
                            if (PF.obj.modal.form_data.editing.album_privacy == "password") {
                                PF.obj.modal.form_data.editing.album_password = $(
                                    "[name=form-album-password]",
                                    $modal
                                ).val();
                            }
                            PF.obj.modal.form_data.editing.album_description = $(
                                "[name=form-album-description]",
                                $modal
                            ).val();
                        } else {
                            PF.obj.modal.form_data.editing.album_id = $(
                                "[name=form-album-id]",
                                $modal
                            ).val();
                        }

                        return true;
                    },
                });

                break;

            case "approve":
                PF.fn.modal.call({
                    type: "html",
                    template: CHV.fn.modal.getTemplateWithPreview("[data-modal=form-approve-single]", $list_item),
                    button_submit: PF.fn._s("Confirm"),
                    ajax: {
                        url: PF.obj.config.json_api,
                        deferred: {
                            success: function (XHR) {
                                CHV.fn.list_editor.removeFromList(
                                    $list_item,
                                    PF.fn._s("The content has been approved.")
                                );
                            },
                        },
                    },
                    confirm: function () {
                        PF.obj.modal.form_data = {
                            action: "approve",
                            single: true,
                            approve: $list_item.data("type"),
                            approving: {
                                id: id,
                            },
                        };
                        return true;
                    },
                });
                break;
            case "delete":
                PF.fn.modal.call({
                    type: "html",
                    template: CHV.fn.modal.getTemplateWithPreview("[data-modal=form-delete-single]", $list_item),
                    button_submit: PF.fn._s("Confirm"),
                    ajax: {
                        url: PF.obj.config.json_api,
                        deferred: {
                            success: function (XHR) {
                                if (dealing_with == "album") {
                                    $("[name=form-album-id]", "[data-modal]")
                                        .find("[value=" + id + "]")
                                        .remove();
                                    CHV.fn.list_editor.updateUserCounters(
                                        "image",
                                        XHR.responseJSON.success.affected,
                                        "-"
                                    );
                                }
                                CHV.fn.list_editor.deleteFromList($list_item);
                                CHV.fn.listingViewer.close();
                            },
                        },
                    },
                    confirm: function () {
                        PF.obj.modal.form_data = {
                            action: "delete",
                            single: true,
                            delete: $list_item.data("type"),
                            deleting: {
                                id: id,
                            },
                        };
                        return true;
                    },
                });

                break;

            case "flag":
                $.ajax({
                    type: "POST",
                    data: {
                        action: "edit",
                        edit: "image",
                        single: true,
                        editing: {
                            id: id,
                            nsfw: $list_item.data("flag") == "unsafe" ? 0 : 1,
                        },
                    },
                }).complete(function (XHR) {
                    var response = XHR.responseJSON;
                    if (response.status_code == 200) {
                        var flag = response.image.nsfw == 1 ? "unsafe" : "safe";
                        $targets.attr("data-flag", flag).data("flag", flag);
                    } else {
                        PF.fn.growl.call(response.error.message);
                    }
                    CHV.fn.list_editor.selectionCount();
                });
                break;
        }
    });

    $(".pop-box-menu a", "[data-content=list-selection]").click(function (e) {
        var $content_listing = $(PF.obj.listing.selectors.content_listing_visible);

        if (typeof $content_listing.data("list") !== "undefined") {
            dealing_with = $content_listing.data("list");
        } else {
            console.error("Error: data-list not defined");
            return;
        }

        var $targets = $(
            PF.obj.listing.selectors.list_item + ".selected",
            $content_listing
        ),
            ids = $.map($targets, function (e, i) {
                return $(e).data("id");
            });

        PF.fn.close_pops();
        if ($(this).data("action") !== 'list-select-all') {
            e.stopPropagation();
        }

        switch ($(this).data("action")) {
            case "get-embed-codes":
                var template = "[data-modal=form-embed-codes]";
                var objects = [];
                $("textarea", template).html("");
                $targets.each(function () {
                    var aux = {
                        image: JSON.parse(decodeURIComponent($(this).data("object"))),
                    };
                    if ("url" in aux.image) {
                        objects.push(aux);
                    }
                });
                CHV.fn.fillEmbedCodes(objects, template, "html");
                PF.fn.modal.call({
                    type: "html",
                    template: CHV.fn.modal.getTemplateWithPreviews(template, $targets),
                    buttons: false,
                });


                break;

            case "clear":
                CHV.fn.list_editor.clearSelection();
                break;

            case "list-select-all":
                CHV.fn.list_editor.selectAll(e);
                break;

            case "move":
            case "create-album":
                var template = $(this).data("action") == "move" ?
                    "form-move-multiple" :
                    "form-create-album",
                    modal_source = "[data-modal=" + template + "]",
                    dealing_id_data = /image/.test(dealing_with) ? "album-id" : "id";

                $("[name=form-album-id]", modal_source).find("[value=null]").remove();

                $("[name=form-album-id]", modal_source)
                    .find("option")
                    .removeAttr("selected");

                $("[name=form-album-name]", modal_source).attr({ value: "", autocomplete: "off" });
                $("[name=form-album-description]", modal_source).html("");
                $("[name=form-privacy]", modal_source)
                    .find("option")
                    .removeAttr("selected");

                var album_id = $targets.first().data(dealing_id_data),
                    same_album = true;

                $targets.each(function () {
                    if ($(this).data(dealing_id_data) !== album_id) {
                        same_album = false;
                        return false;
                    }
                });

                if (!same_album) {
                    $("[name=form-album-id]", modal_source).prepend(
                        '<option value="null">' +
                        PF.fn._s("Select existing album") +
                        "</option>"
                    );
                }

                $("[name=form-album-id]", modal_source)
                    .find(
                        "[value=" +
                        (same_album ? $targets.first().data(dealing_id_data) : "null") +
                        "]"
                    )
                    .attr("selected", true);

                PF.fn.modal.call({
                    type: "html",
                    template: CHV.fn.modal.getTemplateWithPreviews(modal_source, $targets),
                    ajax: {
                        url: PF.obj.config.json_api,
                        deferred: {
                            success: function (XHR) {
                                CHV.fn.list_editor.updateMoveItemLists(
                                    XHR.responseJSON,
                                    dealing_with,
                                    $targets
                                );
                            },
                        },
                    },
                    load: function () {
                        if (template == "form-move-multiple") {
                            //$("[name=form-album-id]", PF.obj.modal.selectors.root).focus();
                        }
                    },
                    confirm: function () {
                        var $modal = $(PF.obj.modal.selectors.root),
                            new_album = false;

                        if (
                            $("[data-content=form-new-album]", $modal).is(":visible") &&
                            $("[name=form-album-name]", $modal).val() == ""
                        ) {
                            PF.fn.growl.call(PF.fn._s("You must enter the album name."));
                            $("[name=form-album-name]", $modal).highlight();
                            return false;
                        }

                        if ($("[data-content=form-new-album]", $modal).is(":visible")) {
                            new_album = true;
                        }

                        if (!PF.fn.form_modal_has_changed()) {
                            PF.fn.modal.close();
                            return;
                        }

                        var album_object = new_album ? "creating" : "moving";

                        PF.obj.modal.form_data = {
                            action: new_album ? "create-album" : "move",
                            type: dealing_with,
                            owner: CHV.obj.resource.user.id,
                            multiple: true,
                            album: {
                                ids: ids,
                                new: new_album,
                            },
                        };

                        if (new_album) {
                            PF.obj.modal.form_data.album.name = $(
                                "[name=form-album-name]",
                                $modal
                            ).val();
                            PF.obj.modal.form_data.album.privacy = $(
                                "[name=form-privacy]",
                                $modal
                            ).val();
                            if (PF.obj.modal.form_data.album.privacy == "password") {
                                PF.obj.modal.form_data.album.password = $(
                                    "[name=form-album-password]",
                                    $modal
                                ).val();
                            }
                            PF.obj.modal.form_data.album.description = $(
                                "[name=form-album-description]",
                                $modal
                            ).val();
                        } else {
                            PF.obj.modal.form_data.album.id = $(
                                "[name=form-album-id]",
                                $modal
                            ).val();
                        }

                        return true;
                    },
                });

                break;

            case "approve":
                PF.fn.modal.call({
                    template: CHV.fn.modal.getTemplateWithPreviews("[data-modal=form-approve-multiple]", $targets),
                    button_submit: PF.fn._s("Confirm"),
                    ajax: {
                        url: PF.obj.config.json_api,
                        deferred: {
                            success: function (XHR) {
                                CHV.fn.list_editor.removeFromList(
                                    $targets,
                                    PF.fn._s("The content has been approved.")
                                );
                            },
                        },
                    },
                    confirm: function () {
                        PF.obj.modal.form_data = {
                            action: "approve",
                            from: "list",
                            approve: dealing_with,
                            multiple: true,
                            approving: {
                                ids: ids,
                            },
                        };

                        return true;
                    },
                });

                break;

            case "delete":
                PF.fn.modal.call({
                    template: CHV.fn.modal.getTemplateWithPreviews("[data-modal=form-delete-multiple]", $targets),
                    button_submit: PF.fn._s("Confirm"),
                    ajax: {
                        url: PF.obj.config.json_api,
                        deferred: {
                            success: function (XHR) {
                                // unificar
                                if (dealing_with == "albums") {
                                    $targets.each(function () {
                                        $("[name=form-album-id]", "[data-modal]")
                                            .find("[value=" + $(this).data("id") + "]")
                                            .remove();
                                    });
                                    CHV.fn.list_editor.updateUserCounters(
                                        "image",
                                        XHR.responseJSON.success.affected,
                                        "-"
                                    );
                                }
                                CHV.fn.list_editor.deleteFromList($targets);
                            },
                        },
                    },
                    confirm: function () {
                        PF.obj.modal.form_data = {
                            action: "delete",
                            from: "list",
                            delete: dealing_with,
                            multiple: true,
                            deleting: {
                                ids: ids,
                            },
                        };

                        return true;
                    },
                });

                break;

            case "assign-category":
                var category_id = $targets.first().data("category-id"),
                    same_category = true;

                $targets.each(function () {
                    if ($(this).data("category-id") !== category_id) {
                        same_category = false;
                        return false;
                    }
                });

                PF.fn.modal.call({
                    type: "html",
                    template: CHV.fn.modal.getTemplateWithPreviews("[data-modal=form-assign-category]", $targets),
                    forced: true,
                    ajax: {
                        url: PF.obj.config.json_api,
                        deferred: {
                            success: function (XHR) {
                                $targets.each(function () {
                                    var response = XHR.responseJSON;
                                    $(this).data("category-id", response.category_id);
                                });
                                CHV.fn.list_editor.clearSelection();
                            },
                        },
                    },
                    confirm: function () {
                        var $modal = $(PF.obj.modal.selectors.root),
                            form_category =
                                $("[name=form-category-id]", $modal).val() || null;

                        if (same_category && category_id == form_category) {
                            PF.fn.modal.close(function () {
                                CHV.fn.list_editor.clearSelection();
                            });
                            return false;
                        }

                        PF.obj.modal.form_data = {
                            action: "edit-category",
                            from: "list",
                            multiple: true,
                            editing: {
                                ids: ids,
                                category_id: form_category,
                            },
                        };
                        return true;
                    },
                });
                break;

            case "flag-safe":
            case "flag-unsafe":
                var action = $(this).data("action"),
                    flag = action == "flag-safe" ? "safe" : "unsafe";

                PF.fn.modal.call({
                    template: CHV.fn.modal.getTemplateWithPreviews("[data-modal=form-" + action + "]", $targets),
                    button_submit: PF.fn._s("Confirm"),
                    ajax: {
                        url: PF.obj.config.json_api,
                        deferred: {
                            success: function (XHR) {
                                $targets.each(function () {
                                    $(this)
                                        .removeClass("safe unsafe")
                                        .addClass(flag)
                                        .removeAttr("data-flag")
                                        .attr("data-flag", flag)
                                        .data("flag", flag);
                                });
                                CHV.fn.list_editor.clearSelection();
                            },
                        },
                    },
                    confirm: function () {
                        PF.obj.modal.form_data = {
                            action: action,
                            from: "list",
                            multiple: true,
                            editing: {
                                ids: ids,
                                nsfw: action == "flag-safe" ? 0 : 1,
                            },
                        };

                        return true;
                    },
                });

                break;
        }

        if (PF.fn.isDevice(["phone", "phablet"])) {
            return false;
        }
    });

    $(document).on("click", "[data-action=disconnect]", function () {
        var $this = $(this),
            connection = $this.data("connection");

        PF.fn.modal.confirm({
            message: $this.data("confirm-message"),
            ajax: {
                data: {
                    action: "disconnect",
                    disconnect: connection,
                    user_id: CHV.obj.resource.user.id,
                },
                deferred: {
                    success: function (XHR) {
                        var response = XHR.responseJSON;
                        $("[data-connection=" + connection + "]").fadeOut(function () {
                            $($("[data-connect=" + connection + "]")).fadeIn();
                            $(this).remove();
                            if ($("[data-connection]").length == 0) {
                                $("[data-content=empty-message]").show();
                            }
                            PF.fn.growl.expirable(response.success.message);
                        });
                        if (response.success.redirect !== '') {
                            window.location.href = response.success.redirect;
                        }
                    },
                    error: function (XHR) {
                        var response = XHR.responseJSON;
                        PF.fn.growl.call(response.error.message);
                    },
                },
            },
        });
    });

    $(document).on("click", "[data-action=delete-avatar]", function () {
        var $parent = $(".user-settings-avatar"),
            $loading = $(".loading-placeholder", $parent),
            $top = $("#top-bar");

        $loading.removeClass("hidden");

        PF.fn.loading.inline($loading, {
            center: true,
        });

        $.ajax({
            type: "POST",
            data: {
                action: "delete",
                delete: "avatar",
                owner: CHV.obj.resource.user.id,
            },
        }).complete(function (XHR) {
            $loading.addClass("hidden").empty();
            if (XHR.status == 200) {
                if (CHV.obj.logged_user.id == CHV.obj.resource.user.id) {
                    $("img.user-image", $top).hide();
                    $(".default-user-image", $top).removeClass("hidden");
                }
                $(".default-user-image", $parent).removeClass("hidden").css({
                    opacity: 0,
                });
                $('[data-action="delete-avatar"]', $parent).parent().addClass("soft-hidden");
                $("img.user-image", $parent).fadeOut(function () {
                    $(".default-user-image", $parent).animate({
                        opacity: 1,
                    });
                });
            } else {
                PF.fn.growl.expirable(
                    PF.fn._s("An error occurred. Please try again later.")
                );
            }
        });
    });

    $(document).on("change", "[data-content=user-avatar-upload-input]", function (
        e
    ) {
        e.preventDefault();
        e.stopPropagation();
        var $this = $(this),
            $parent = $(".user-settings-avatar"),
            $loading = $(".loading-placeholder", ".user-settings-avatar"),
            $top = $("#top-bar"),
            user_avatar_file = $(this)[0].files[0];

        if ($this.data("uploading")) {
            return;
        }
        if (/^image\/.*$/.test(user_avatar_file.type) == false) {
            PF.fn.growl.call(PF.fn._s("Please select a valid image file type."));
            return;
        }
        if (
            user_avatar_file.size > CHV.obj.config.user.avatar_max_filesize.getBytes()
        ) {
            PF.fn.growl.call(
                PF.fn._s(
                    "Please select a picture of at most %s size.",
                    CHV.obj.config.user.avatar_max_filesize
                )
            );
            return;
        }
        var deleteAvatar = $('[data-action="delete-avatar"]');
        $loading.removeClass("hidden");
        PF.fn.loading.inline($loading, {
            center: true,
        });
        $this.data("uploading", true);
        var user_avatar_fd = new FormData();
        user_avatar_fd.append("source", user_avatar_file);
        user_avatar_fd.append("action", "upload");
        user_avatar_fd.append("type", "file");
        user_avatar_fd.append("what", "avatar");
        user_avatar_fd.append("owner", CHV.obj.resource.user.id);
        user_avatar_fd.append("auth_token", PF.obj.config.auth_token);
        avatarXHR = new XMLHttpRequest();
        avatarXHR.open("POST", PF.obj.config.json_api, true);
        avatarXHR.send(user_avatar_fd);
        avatarXHR.onreadystatechange = function () {
            if (this.readyState == 4) {
                var response =
                    this.responseType !== "json" ?
                        JSON.parse(this.response) :
                        this.response,
                    image = response.success.image;

                $loading.addClass("hidden").empty();
                if (this.status == 200) {
                    change_avatar = function (parent) {
                        deleteAvatar.parent().removeClass("soft-hidden");
                        $("img.user-image", parent)
                            .attr("src", image.url)
                            .removeClass("hidden")
                            .show();
                    };
                    hide_default = function (parent) {
                        $(".default-user-image", parent).addClass("hidden");
                    };
                    hide_default($parent);
                    $(".btn-alt", $parent).closest("div").show();
                    change_avatar($parent);
                    if (CHV.obj.logged_user.id == CHV.obj.resource.user.id) {
                        change_avatar($top);
                        hide_default($top);
                    }
                    PF.fn.growl.expirable(PF.fn._s("Profile image updated."));
                } else {
                    PF.fn.growl.expirable(
                        PF.fn._s("An error occurred. Please try again later.")
                    );
                }

                $this.data("uploading", false);
            }
        };
    });

    $(document).on(
        "change",
        "[data-content=user-background-upload-input]",
        function (e) {
            e.preventDefault();
            e.stopPropagation();

            var $this = $(this),
                $parent = $("[data-content=user-background-cover]"),
                $src = $("[data-content=user-background-cover-src]"),
                $loading = $(".loading-placeholder", $parent),
                $top = $("#top-bar"),
                user_file = $(this)[0].files[0];

            if ($this.data("uploading")) {
                return;
            }

            if (/^image\/.*$/.test(user_file.type) == false) {
                PF.fn.growl.call(PF.fn._s("Please select a valid image file type."));
                return;
            }

            if (
                user_file.size > CHV.obj.config.user.background_max_filesize.getBytes()
            ) {
                PF.fn.growl.call(
                    PF.fn._s(
                        "Please select a picture of at most %s size.",
                        CHV.obj.config.user.background_max_filesize
                    )
                );
                return;
            }

            $loading.removeClass("hidden");

            PF.fn.loading.inline($loading, {
                center: true,
                size: "big",
                color: "#FFF",
            });

            $this.data("uploading", true);

            var user_picture_fd = new FormData();
            user_picture_fd.append("source", user_file);
            user_picture_fd.append("action", "upload");
            user_picture_fd.append("type", "file");
            user_picture_fd.append("what", "background");
            user_picture_fd.append("owner", CHV.obj.resource.user.id);
            user_picture_fd.append("auth_token", PF.obj.config.auth_token);
            avatarXHR = new XMLHttpRequest();
            avatarXHR.open("POST", PF.obj.config.json_api, true);
            avatarXHR.send(user_picture_fd);
            avatarXHR.onreadystatechange = function () {
                if (this.readyState == 4) {
                    var response =
                        this.responseType !== "json" ?
                            JSON.parse(this.response) :
                            this.response,
                        image = response.success.image;

                    if (this.status == 200) {
                        var $img = $("<img/>");
                        $img.attr("src", image.url).imagesLoaded(function () {
                            $loading.addClass("hidden").empty();
                            $src
                                .css("background-image", "url(" + image.url + ")")
                                .hide()
                                .fadeIn();
                            $("[data-content=user-change-background]", $parent).removeClass(
                                "hidden"
                            );
                            $($parent).removeClass("no-background");
                            $(".top-user").removeClass("no-background");
                            $("[data-content=user-upload-background]").hide();
                            $("[data-content=user-change-background]").show();
                            PF.fn.growl.expirable(
                                PF.fn._s("Profile background image updated.")
                            );
                            $img.remove();
                        });
                    } else {
                        $loading.addClass("hidden").empty();
                        PF.fn.growl.expirable(
                            PF.fn._s("An error occurred. Please try again later.")
                        );
                    }

                    $this.data("uploading", false);
                }
            };
        }
    );

    CHV.fn.user_background = {
        delete: {
            submit: function () {
                PF.obj.modal.form_data = {
                    action: "delete",
                    delete: "background",
                    owner: CHV.obj.resource.user.id,
                };
                return true;
            },
            deferred: {
                success: {
                    before: function (XHR) {
                        $("[data-content=user-background-cover-src]").css(
                            "background-image",
                            "none"
                        );
                        $("[data-content=user-background-cover], .top-user")
                            .addClass("no-background");
                        $("[data-content=user-background-cover]").height("");
                        $("[data-content=user-upload-background]")
                            .removeClass("hidden")
                            .show();
                        $("[data-content=user-change-background]").hide();
                    },
                    done: function (XHR) {
                        PF.fn.modal.close(function () {
                            PF.fn.growl.expirable(
                                PF.fn._s("Profile background image deleted.")
                            );
                        });
                    },
                },
                error: function (XHR) {
                    PF.fn.growl.expirable(
                        PF.fn._s("Error deleting profile background image.")
                    );
                },
            },
        },
    };

    CHV.fn.user_api = {
        delete: {
            submit: function () {
                PF.obj.modal.form_data = {
                    action: "delete",
                    delete: "api_key",
                    owner: CHV.obj.resource.user.id,
                };
                return true;
            },
            deferred: {
                success: {
                    before: function (XHR) {
                    },
                    done: function (XHR) {
                        PF.fn.modal.close(function () {
                            location.reload();
                        });
                    },
                },
                error: function (XHR) {
                    PF.fn.growl.expirable(
                        XHR.responseJSON.error.message
                    );
                },
            },
        },
    };

    CHV.fn.user_two_factor = {
        delete: {
            submit: function () {
                PF.obj.modal.form_data = {
                    action: "delete",
                    delete: "two_factor",
                    owner: CHV.obj.resource.user.id,
                };
                return true;
            },
            deferred: {
                success: {
                    before: function (XHR) {
                    },
                    done: function (XHR) {
                        PF.fn.modal.close(function () {
                            location.reload();
                        });
                    },
                },
                error: function (XHR) {
                    PF.fn.growl.expirable(
                        XHR.responseJSON.error.message
                    );
                },
            },
        },
    };

    // Form things
    CHV.str.mainform = "[data-content=main-form]";
    CHV.obj.timezone = {
        selector: "[data-content=timezone]",
        input: "#timezone-region",
    };

    // Detect form changes
    $(document).on("keyup change", CHV.str.mainform + " :input", function () {
        if ($(this).is("[name=username]")) {
            $("[data-text=username]").text($(this).val());
        }
    });

    // Timezone handler
    $(document).on("change", CHV.obj.timezone.input, function () {
        var value = $(this).val(),
            $timezone_combo = $("#timezone-combo-" + value);
        $timezone_combo.find("option:first").prop("selected", true);
        $(CHV.obj.timezone.selector).val($timezone_combo.val()).change();
    });
    $(document).on("change", "[id^=timezone-combo-]", function () {
        var value = $(this).val();
        $(CHV.obj.timezone.selector).val(value).change();
    });

    // Password match
    $(document).on("keyup change blur", "[name^=new-password]", function () {
        var $new_password = $("[name=new-password]"),
            $new_password_confirm = $("[name=new-password-confirm]"),
            hide = $new_password.val() == $new_password_confirm.val(),
            $warning = $new_password_confirm
                .closest(".input-password")
                .find(".input-warning");
        if ($warning.exists() == false) {
            $warning = $("[data-message=new-password-confirm]");
        }

        if ($(this).is($new_password_confirm)) {
            $new_password_confirm.data("touched", true);
        }

        if ($new_password_confirm.data("touched")) {
            $warning
                .text(!hide ? $warning.data("text") : "")[!hide ? "removeClass" : "addClass"]("hidden-visibility");
        }
    });

    // Submit form
    $(document).on("submit", CHV.obj.mainform, function () {
        switch ($(this).data("type")) {
            case "password":
                var $p1 = $("[name=new-password]", this),
                    $p2 = $("[name=new-password-confirm]", this);
                if ($p1.val() !== "" || $p2.val() !== "") {
                    if ($p1.val() !== $p2.val()) {
                        $p1.highlight();
                        $p2.highlight();
                        PF.fn.growl.expirable(PF.fn._s("Passwords don't match"));
                        return false;
                    }
                }
                break;
        }
    });

    $(document).on("click", "[data-action=check-for-updates]", function () {
        PF.fn.loading.fullscreen();
        CHV.fn.system.checkUpdates(function (XHR) {
            PF.fn.loading.destroy("fullscreen");
            if (XHR.status !== 200) {
                PF.fn.growl.call(
                    PF.fn._s("An error occurred. Please try again later.")
                );
                return;
            }
            var data = XHR.responseJSON.software;
            if (
                PF.fn.versionCompare(
                    CHV.obj.system_info.version,
                    data.current_version
                ) == -1
            ) {
                PF.fn.modal.simple({
                    title: '<i class="fas fa-arrow-alt-circle-up"></i> ' + PF.fn._s("Chevereto v%s available", data.current_version),
                    message: "<p>" +
                        PF.fn._s("There is a new Chevereto version available with the following release notes.") +
                        ' ' +
                        PF.fn._s("Check %s for a complete changelog since you last upgrade.", '<a href="https://releases.chevereto.com/4.X/4.0/' + CHV.obj.system_info.version + '" target="_blank">' + CHV.obj.system_info.version + '<span class="btn-icon fas fas fa-code-branch"></span></a>') +
                        '</p>' +
                        '<textarea class="r4 resize-vertical">' +
                        data.release_notes.trim() +
                        "</textarea>" +
                        '<p>' +
                        PF.fn._s("Check the %s for alternative update methods.", '<a href="https://chv.to/v4update" target="_blank">' + PF.fn._s('documentation') + '</a>') +
                        '</p>' +
                        '<div class="btn-container margin-bottom-0">' +
                        '<a href="' + PF.obj.config.base_url + 'dashboard/upgrade/?auth_token=' + PF.obj.config.auth_token
                        + '" class="btn btn-input accent">' +
                        '<span class="btn-icon fas fa-download user-select-none"></span>' +
                        '<span class="btn-text user-select-none">' +
                        PF.fn._s("Upgrade now") +
                        '</span>' +
                        '</a> ' +
                        '</div>',
                    html: true,
                });
            } else {
                PF.fn.growl.call(
                    PF.fn._s(
                        "This website is running latest %s version",
                        CHEVERETO.edition
                    )
                );
            }
        });
    });

    if (typeof PF.fn.get_url_var("checkUpdates") !== typeof undefined) {
        $("[data-action=check-for-updates]").trigger("click");
    }
    if (typeof PF.fn.get_url_var("upgrade") !== typeof undefined) {
        $("[data-action=upgrade]").trigger("click");
    }
    if (typeof PF.fn.get_url_var("license") !== typeof undefined) {
        $("[data-action='license']").trigger("click");
    }
    if (typeof PF.fn.get_url_var("welcome") !== typeof undefined) {
        PF.fn.modal.call({
            template: $("[data-modal=welcome]").html(),
            buttons: false,
        });
    }
    if (typeof PF.fn.get_url_var("installed") !== typeof undefined) {
        PF.fn.modal.simple({
            title: '<i class="fas fa-code-branch"></i> ' + PF.fn._s("Chevereto v%s installed", CHV.obj.system_info.version),
            message: "<p>" +
                PF.fn._s('Usage of Chevereto Software must be in compliance with the software license terms known as "The Chevereto License".') +
                '</p>' +
                '<div class="btn-container margin-bottom-0">' +
                '<a href="https://chevereto.com/license" target="_blank" class="btn btn-input accent">' +
                '<span class="btn-icon fas fa-file-contract user-select-none"></span>' +
                '<span class="btn-text user-select-none">' +
                PF.fn._s("License agreement") +
                '</span>' +
                '</a> ' +
                '</div>',
            html: true,
        });
    }
    $(document).on("click", "[data-action=system-update]", function (e) {
        if (!$("input#system-update").prop("checked")) {
            PF.fn.growl.call(
                PF.fn._s('Please review the system requirements before proceeding')
            );
            e.preventDefault();
            return;
        }
    });
    $(document).on("click", "[data-action=toggle-storage-https]", function () {
        CHV.fn.storage.toggleHttps(
            $(this).closest("[data-content=storage]").data("storage-id")
        );
    });
    $(document).on("click", "[data-action=toggle-storage-active]", function () {
        CHV.fn.storage.toggleActive(
            $(this).closest("[data-content=storage]").data("storage-id")
        );
    });

    // Detect paste image event
    if ($(CHV.fn.uploader.selectors.root).exists()) {
        CHV.fn.uploader.$pasteCatcher = $("<div />", {
            contenteditable: "true",
            id: CHV.fn.uploader.selectors.paste.replace(/#/, ""),
        });
        $("body").append(CHV.fn.uploader.$pasteCatcher);

        // Hack Ctrl/Cmd+V to focus pasteCatcher
        $(document).on("keydown", function (e) {
            if ((e.ctrlKey || e.metaKey) && e.originalEvent.code == 'KeyV' && !$(e.target).is(":input")) {
                PF.fn.keyFeedback.spawn(e);
                CHV.fn.uploader.$pasteCatcher.focus(e);
            }
        });
        document.addEventListener("dragover", function (e) {
            e.preventDefault();
        });
        document.addEventListener("drop", function (e) {
            if (!CHV.obj.config.upload.url) {
                return;
            }
            e.preventDefault();
            var imageUrl = e.dataTransfer.getData('text/html');
            var rex = /src="?([^"\s]+)"?\s*/;
            var url, res;
            url = rex.exec(imageUrl);
            if (url) {
                CHV.fn.uploader.toggle({ show: true });
                CHV.fn.uploader.add({}, url[1]);
            }
        });
        window.addEventListener("paste", CHV.fn.uploader.pasteImageHandler);
    }

    $(document).on("click", "[data-action=like]", function () {
        if (!PF.fn.is_user_logged()) {
            window.location.href = CHV.obj.vars.urls.login;
            return;
        }
        var $this = $(this);
        if ($this.data("XHR")) return;
        $this.data("XHR", true);
        var $object = $(this).is("[data-liked]") ?
            $(this) :
            $(this).closest("[data-liked]");
        var isSingle = !$object.closest("[data-list], .viewer").exists() &&
            typeof CHV.obj.resource !== typeof undefined;
        var liked = $object.is("[data-liked=1]");
        var action = !liked ? "like" : "dislike";
        var content = {
            id: isSingle ?
                CHV.obj.resource.id : $(this).closest("[data-id]").attr("data-id"),
            type: isSingle ?
                CHV.obj.resource.type : $(this).closest("[data-type]").attr("data-type"),
        };
        var $targets = isSingle ?
            $this :
            $("[data-type=" + content.type + "][data-id=" + content.id + "]");
        var ajax = {
            type: "POST",
            data: {
                action: action,
            },
            cache: false,
        };
        ajax.data[action] = {
            object: content.type,
            id: content.id,
        };
        $.ajax(ajax).complete(function (XHR) {
            var response = XHR.responseJSON;
            $this.data("XHR", false);
            if (response.status_code !== 200) {
                PF.fn.growl.expirable(
                    PF.fn._s("An error occurred. Please try again later.")
                );
                return;
            }
            if (isSingle && typeof response.content !== typeof undefined) {
                $("[data-text=likes-count]").html(response.content.likes);
            }
            $targets.closest("[data-liked]").attr("data-liked", liked ? 0 : 1);
        });
    });

    $(document).on("click", "[data-action=album-cover]", function () {
        var $this = $(this);
        if ($this.data("XHR")) return;
        $this.data("XHR", true);
        var $object = $(this).is("[data-cover]") ?
            $(this) :
            $(this).closest("[data-cover]");
        var covered = $object.is("[data-cover=1]");
        var action = !covered ? "album-cover-set" : "album-cover-unset";
        var content = {
            id: CHV.obj.resource.id,
            type: 'image',
        };

        var $targets = $this.closest("[data-cover]");
        var ajax = {
            type: "POST",
            data: {
                action: action,
            },
            cache: false,
        };
        ajax.data[action] = {
            "album_id": $targets.data("album-id"),
            "image_id": $targets.data("id"),
        };
        $.ajax(ajax).complete(function (XHR) {
            var response = XHR.responseJSON;
            $this.data("XHR", false);
            if (response.status_code !== 200) {
                PF.fn.growl.expirable(
                    PF.fn._s("An error occurred. Please try again later.")
                );
                return;
            }
            $targets.attr("data-cover", covered ? 0 : 1);
        });
    });

    $(document).on("click", "[data-action=follow]", function () {
        if (!PF.fn.is_user_logged()) {
            PF.fn.modal.call({
                type: "login",
            });
            return;
        }

        var $this = $(this);
        if ($this.data("XHR")) return;
        $this.data("XHR", true);

        var $object = $(this).is("[data-followed]") ?
            $(this) :
            $(this).closest("[data-followed]");
        var isSingle = typeof CHV.obj.resource !== typeof undefined;
        var followed = $object.is("[data-followed=1]");
        var action = !followed ? "follow" : "unfollow";
        var content = {
            id: isSingle ?
                CHV.obj.resource.id : $(this).closest("[data-id]").data("id"),
            type: isSingle ?
                CHV.obj.resource.type : $(this).closest("[data-type]").data("type"),
        };
        var ajax = {
            type: "POST",
            data: {
                action: action,
            },
            cache: false,
        };
        ajax.data[action] = {
            object: content.type,
            id: content.id,
        };
        $.ajax(ajax).complete(function (XHR) {
            var response = XHR.responseJSON;
            $this.data("XHR", false);
            if (response.status_code !== 200) {
                PF.fn.growl.expirable(
                    PF.fn._s("An error occurred. Please try again later.")
                );
                return;
            }
            if (isSingle) {
                if (typeof response.user_followed !== typeof undefined) {
                    var $followersLabel = $("[data-text=followers-label]");
                    var label = {
                        single: $followersLabel.data("label-single"),
                        plural: $followersLabel.data("label-plural"),
                    };
                    $("[data-text=followers-count]").html(
                        response.user_followed.followers
                    );
                    $followersLabel.html(
                        PF.fn._n(
                            label.single,
                            label.plural,
                            response.user_followed.followers
                        )
                    );
                }
            }
            $object.attr("data-followed", followed ? 0 : 1); // Toggle indicator
        });
    });

    $(document).on("click", "[data-action=user_ban],[data-action=user_unban]", function () {
        var $this = $(this);
        if ($this.data("XHR")) return;
        $this.data("XHR", true);
        var $object = $(this).closest("[data-banned]");
        var isSingle = true;
        var banned = $object.is("[data-banned=1]");
        var action = $this.attr("data-action");
        var content = {
            id: isSingle ?
                CHV.obj.resource.id : $(this).closest("[data-id]").data("id"),
            type: isSingle ?
                CHV.obj.resource.type : $(this).closest("[data-type]").data("type"),
        };
        var ajax = {
            type: "POST",
            data: {
                action: action,
            },
            cache: false,
        };
        ajax.data[action] = {
            user_id: content.id,
        };
        $.ajax(ajax).complete(function (XHR) {
            var response = XHR.responseJSON;
            $this.data("XHR", false);
            if (response.status_code !== 200) {
                PF.fn.growl.expirable(
                    PF.fn._s("An error occurred. Please try again later.")
                );
                return;
            }
            $object.attr("data-banned", banned ? 0 : 1);
        });
    });

    function notifications_scroll() {
        if (PF.fn.isDevice(["phone", "phablet"])) return;
        var $visible_list = $(".top-bar-notifications-list ul", ".top-bar:visible");
        var height;
        var height_auto;
        $visible_list.css("height", ""); // Reset any change
        height = $visible_list.height();
        $visible_list.data("height", height).css("height", "auto");
        height_auto = $visible_list.height();
        if (height_auto > height) {
            $visible_list.height(height);
            $visible_list.closest(".antiscroll-wrap").antiscroll();
        }
    }

    $(document).on("click", "[data-action=top-bar-notifications]", function (e) {
        var _this = this;
        var $this = $(this);
        var $container = $(".top-bar-notifications-container", $this);
        var $list = $(".top-bar-notifications-list", $this);
        var $ul = $("ul", $list);
        var $loading = $(".loading", $container);
        if ($this.data("XHR")) {
            return;
        } else {
            $loading.removeClass("hidden");
            PF.fn.loading.inline($loading, {
                size: "small",
                message: PF.fn._s("loading"),
            });
        }
        $.ajax({
            type: "POST",
            data: {
                action: "notifications",
            },
            cache: false,
        }).complete(function (XHR) {
            var response = XHR.responseJSON;
            if (response.status_code !== 200) {
                PF.fn.growl.expirable(
                    PF.fn._s("An error occurred. Please try again later.")
                );
                $this.data("XHR", false);
                $loading.addClass("hidden").html("");
                return;
            }
            $this.data("XHR", true);
            $loading.remove();
            if (!response.html) {
                $(".empty", $container).removeClass("hidden");
                return;
            }
            $list.removeClass("hidden");
            $ul.html(response.html);
            notifications_scroll();
            var $li = $("li.new", $ul);
            $li.addClass("transition");
            setTimeout(function () {
                $li.removeClass("new");
                $("[data-content=notifications-counter]", _this)
                    .removeClass("on")
                    .html("0");
                setTimeout(function () {
                    $li.removeClass("transition");
                }, 150);
            }, 1500);
        });
    });

    // Invoke Captcha
    if (
        $("#g-recaptcha").is(":empty") &&
        CHV.obj.config.captcha.enabled &&
        CHV.obj.config.captcha.sitekey
    ) {
        if(CHV.obj.config.captcha.version == '3' || !CHV.obj.config.captcha.isNeeded) {
            $('label[for="recaptcha_response_field"]').remove();
        }
    }

    $(document).on("click", PF.obj.listing.selectors.list_item + " a.image-container", function (e) {
        var $parent = $(this).closest(PF.obj.listing.selectors.list_item);
        var $loadBtn = $parent.find("[data-action=load-image]");
        if ($loadBtn.length > 0) {
            loadImageListing($loadBtn);
            e.preventDefault();
        }
        return;
    });

    // Load image from listing
    $(document).on("click", PF.obj.listing.selectors.list_item + " [data-action=load-image]", function (e) {
        loadImageListing($(this));
        e.preventDefault();
        e.stopPropagation();
        return;
    });

    function loadImageListing($this) {
        $this.addClass("list-item-play-gif--loading");
        var $parent = $this.closest(PF.obj.listing.selectors.list_item);
        var $imageContainer = $(".image-container", $parent);
        var $image = $("img", $imageContainer);
        var imageSrc = $image.attr("src");
        var md = ".md";
        var mdIndex = imageSrc.lastIndexOf(md);
        if (mdIndex == -1) {
            var md = ".th";
            var mdIndex = imageSrc.lastIndexOf(md);
        }
        var loadSrc =
            imageSrc.substr(0, mdIndex) +
            imageSrc.substr(mdIndex + md.length, imageSrc.length);
        $imageContainer.append($imageContainer.html());
        $load = $parent
            .find(".image-container img")
            .eq(1)
            .attr("src", loadSrc)
            .addClass("hidden");
        $load.imagesLoaded(function () {
            $this.remove();
            $image.remove();
            $("img", $imageContainer).show();
            $(this.elements).removeClass("hidden");
        });
    }

    $(document).on("click", "#album [data-tab=tab-embeds]", function (e) {
        e.preventDefault;
        CHV.fn.album.showEmbedCodes();
    });

    if ($("body").is("#upload")) {
        CHV.fn.uploader.toggle({
            show: true,
        });
    }

    // Listing + viewer keys
    $(document).on("keyup", function (e) {
        if ($(e.target).is(":input") || (e.ctrlKey || e.metaKey || e.altKey)) {
            return;
        }
        var isModalVisible = $("#fullscreen-modal:visible").exists();
        var $viewer = $(".viewer");
        var $listSelection = $(".list-selection:visible");
        var $listTools = $listSelection.find("[data-content=pop-selection]:visible:not(.disabled)");
        var viewerShown = $("body").hasClass("--viewer-shown");
        var uploaderShown = $(CHV.fn.uploader.selectors.root + CHV.fn.uploader.selectors.show).exists();
        var keyCode = e.originalEvent.code;
        if (e.originalEvent.code === 'Escape') {
            if (isModalVisible) {
                return;
            }
            if (uploaderShown) {
                CHV.fn.uploader.toggle({ reset: false });
            }
        }
        if ($viewer.exists() && viewerShown) {
            if (keyCode in CHV.fn.listingViewer.keys) {
                var direct = ["KeyW", "Escape", "ArrowLeft", "ArrowRight"];
                var action = CHV.fn.listingViewer.keys[keyCode];
                if (direct.indexOf(keyCode) == -1) {
                    $("[data-action=" + action + "]", CHV.fn.listingViewer.selectors.root).click();
                } else {
                    if (action in CHV.fn.listingViewer) {
                        CHV.fn.listingViewer[action]();
                    }
                }
                PF.fn.keyFeedback.spawn(e);
            }
            return;
        }
        var $button;
        var keyMapListing = {
            'Period': 'list-select-all',
            'KeyK': 'get-embed-codes',
            'KeyZ': 'clear',
            'KeyA': 'create-album',
            'KeyM': 'move',
            'KeyO': 'approve',
            'Delete': 'delete',
            'KeyC': 'assign-category',
            'KeyV': 'flag-safe',
            'KeyF': 'flag-unsafe',
            'KeyH': 'album-cover',
        }
        var keyMapResource = {
            'KeyE': 'edit',
            'KeyL': 'like',
            'KeyS': 'share',
            'KeyJ': 'sub-album',
            'KeyP': 'upload-to-album',
        }
        var action = keyMapListing[keyCode] || keyMapResource[keyCode];
        if (typeof action == typeof undefined) {
            return;
        }
        if ($listSelection.exists()) {
            if (!viewerShown && !isModalVisible) {
                if (parseInt($('[data-text=selection-count]:visible', $listTools).text()) > 0) {
                    $button = $("[data-action=" + action + "]", $listSelection.closest(".list-selection"));
                }
            }
        }
        if (typeof $button === typeof undefined) {
            $button = $("[data-action=" + action + "]:visible").not("#content-listing-tabs *");
        }
        if ($button instanceof jQuery && $button.length > 0) {
            $button.first().trigger("click");
            PF.fn.keyFeedback.spawn(e);
        }
    });

    $(document).on(
        "click",
        CHV.fn.listingViewer.selectors.root + " [data-action^=viewer-]",
        function () {
            var action = $(this).data("action").substring("viewer-".length);
            if (action in CHV.fn.listingViewer) {
                CHV.fn.listingViewer[action]();
            }
        }
    );

    $(document).on(
        "click",
        "a[data-href]:not([rel=popup-link]):not(.popup-link)",
        function () {
            var data = $(this).attr("data-href");
            var href = $(this).attr("href");
            if (!data && !href) return;
            location.href = href ? href : data;
        }
    );
    function toggleListSelect(that, e) {
        var $item = $(that).closest(PF.obj.listing.selectors.list_item);
        CHV.fn.list_editor.blink($item);
        CHV.fn.list_editor.toggleSelectItem($item);
        PF.fn.keyFeedback.spawn(e);
        e.preventDefault();
        e.stopPropagation();
    }
    var selectableItemSelector = PF.obj.listing.selectors.list_item + ", .image-container";
    $(document).on("contextmenu click", selectableItemSelector, function (e) {
        if (!$(".list-selection:visible").exists()
            || $(e.target).closest(".list-item-desc").exists()
            || $(this).closest(CHV.fn.listingViewer.selectors.root).exists()
            || (e.type == "click" && !(e.ctrlKey || e.metaKey))
        ) {
            return;
        }
        toggleListSelect(this, e);
    });
    if(navigator.userAgent.match(/(iPad|iPhone|iPod)/i)) {
        var pressTimer;
        $(document)
            .on("mouseup mousemove", selectableItemSelector, function(e) {
                clearTimeout(pressTimer);
                return false;
            })
            .on("mousedown", selectableItemSelector, function(e) {
                var that = this;
                var event = e;
                pressTimer = window.setTimeout(function() {
                    if (!$(".list-selection:visible").exists()
                        || $(that).closest(CHV.fn.listingViewer.selectors.root).exists()) {
                        return;
                    }
                    toggleListSelect(that, event);
                }, 500);
                return false;
            });
    }

    if (
        typeof CHV.obj.config !== typeof undefined &&
        CHV.obj.config.listing.viewer
    ) {
        $(document).on(
            "click",
            PF.obj.listing.selectors.list_item +
            "[data-type=image] .image-container",
            function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (e.clientX === 0 && e.clientY === 0) {
                    PF.fn.keyFeedback.spawn(e);
                    return;
                }
                var $item = $(this).closest(PF.obj.listing.selectors.list_item);
                if (!$item.exists()) return;
                if (e.ctrlKey || e.metaKey) {
                    return;
                }
                CHV.fn.listingViewer.open($item);
            }
        );
    }

    $(document).on("contextmenu", CHV.fn.listingViewer.selectors.root, function (
        e
    ) {
        e.preventDefault();
        CHV.fn.listingViewer.zoom();
        PF.fn.keyFeedback.spawn(e);
        return false;
    });

    var UrlParams = PF.fn.deparam(window.location.search);
    if (UrlParams && "viewer" in UrlParams) {
        var $parent = $(PF.obj.listing.selectors.content_listing_visible);
        if ($parent.data("list") == "images") {
            var $item = $(PF.obj.listing.selectors.list_item, $parent)[
                UrlParams.viewer == "next" ? "first" : "last"
            ]();
            CHV.fn.listingViewer.open($item);
        }
    }
    var resizeTimer;
    $(window).on("DOMContentLoaded load resize scroll", function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            $(PF.obj.listing.selectors.list_item + ":visible").each(function () {
                var loadBtn = $(this).find('[data-action="load-image"]').first();
                var paramsHidden = PF.fn.deparam($(PF.obj.listing.selectors.list_item + '[data-id=' + $(this).attr("data-id") + ']').closest(".content-listing").data("params-hidden"));
                var autoLoad = paramsHidden && "is_animated" in paramsHidden ?
                    paramsHidden.is_animated :
                    $(this).data("size") <= CHV.obj.config.image.load_max_filesize.getBytes();
                if (loadBtn.exists() && autoLoad && $(this).is_within_viewport(50)) {
                    loadImageListing(loadBtn);
                }
            });
        }, 1000);
    });

    $(document).on("click", "[data-action=logout]", function () {
        let $form = $("form#form-logout");
        $form.submit();
    });

    if(Boolean(window.navigator.vibrate)) {
        $(document).on("click",
            "button, .btn, .pop-btn, .top-btn-el, [data-action], .content-tabs a, .top-bar-logo a, .login-provider-button, .panel-share-networks li a, #image-viewer-loader",
            function(e) {
                if($(this).is("[data-action=top-bar-menu-full]")) {
                    return;
                }
                window.navigator.vibrate(0);
                window.navigator.vibrate(15);
            }
        );
    }

    $(document).on("change keyup", CHV.fn.ctaForm.selectors.rows + " input[name^='cta-']", function() {
        CHV.fn.ctaForm.update($(this));
    });

    $(document).on("click", CHV.fn.ctaForm.selectors.rows + " [data-action=cta-add]", function () {
        CHV.fn.ctaForm.insert($(this));
    });

    $(document).on("click", CHV.fn.ctaForm.selectors.rows + " [data-action=cta-remove]", function () {
        CHV.fn.ctaForm.remove($(this));
        if(CHV.fn.ctaForm.array.length == 0) {
            $(CHV.fn.ctaForm.selectors.root + " " + CHV.fn.ctaForm.selectors.enable).prop("checked", false).trigger("change");
        }

    });
    $(document).on("change", CHV.fn.ctaForm.selectors.root + " " + CHV.fn.ctaForm.selectors.enable, function() {
        let $combo = $(CHV.fn.ctaForm.selectors.combo, CHV.fn.ctaForm.selectors.root);
        let checked = $(this).is(":checked");
        $combo.toggleClass("soft-hidden", !checked);
        if(checked) {
            if(CHV.fn.ctaForm.array.length == 0) {
                CHV.fn.ctaForm.add();
            }
            CHV.fn.ctaForm.render();
        }
        CHV.fn.ctaForm.setEnable(checked ? 1 : 0);
    });

    $(document).on("change keyup", CHV.fn.ctaForm.selectors.root + " input[name^='cta-icon_']", function() {
        let $row = CHV.fn.ctaForm.getRow($(this));
        let $icon = $row.find("label[for^='cta-icon_'] [data-content=icon]");
        $icon.removeClass();
        let iconClass = CHV.fn.ctaForm.getIconClass($(this).val());
        $icon.addClass(iconClass);
    });

    $(document).on("click", "[href^='https://chevereto.com/']", function(e) {
        let hasBadge = $(this).find(".badge--paid").exists();
        if(!hasBadge) {
            return;
        }
        let href = $(this).attr("href");
        let buyFrom = PF.fn._s('Get a license at %s to unlock all features and support.', '<a href="'+href+'" target="_blank">chevereto.com</a>');
        let instructions = PF.fn._s('You can enter your license key in the dashboard panel.');
        e.preventDefault();
        e.stopPropagation();
        PF.fn.modal.simple({
            html: true,
            title: '<i class="fa-solid fa-boxes-packing"></i> Upgrade Chevereto',
            message: "<p>" + buyFrom +
            " " + instructions +  "</p>" +
            '<div class="btn-container margin-bottom-0">' +
            '<a href="' + PF.obj.config.base_url + 'dashboard/?license" class="btn btn-input accent">' +
            '<span class="btn-icon fas fa-key user-select-none"></span>' +
            '<span class="btn-text user-select-none">' +
            PF.fn._s("Enter license") +
            '</span>' +
            '</a> ' +
            '</div>',
        });
    });
    $(document).on("focus", "input[name='form-album-password']", function() {
        $(this).get(0).type = "text";
    });
    $(document).on("blur", "input[name='form-album-password']", function() {
        $(this).get(0).type = "password";
    });
});

if (typeof CHV == "undefined") {
    CHV = {
        obj: {},
        fn: {},
        str: {},
    }
}

if (window.opener) {
    CHV.obj.opener = {
        uploadPlugin: {},
    }
}

CHV.fn.ctaButtons = {
    selectors: {
        container: "[data-contains=cta-album]",
    },
    render: function(html="") {
        $(this.selectors.container).each(function() {
            $(this).html(html);
        });
    }
}
CHV.fn.ctaForm = {
    enable: 0,
    array: [],
    selectors: {
        root: "#cta-form",
        rows: "#cta-rows",
        enable: "#cta-enable",
        template: "#cta-row-template",
        combo: "#cta-combo",
        row: ".cta-row"
    },
    update: function($atElement) {
        let pos = this.getPos($atElement);
        let key = $atElement.attr("name").match(/cta-(.*)?_\d+/)[1]
        this.array[pos-1][key] = $atElement.val();
    },
    add: function(label="", icon="", href="") {
        this.array.push(this.getRowObject(label, icon, href));
        this.render();
    },
    insert: function($atElement) {
        let pos = this.getPos($atElement);
        this.array.splice(pos, 0, this.getRowObject());
        this.render();
    },
    remove: function($atElement) {
        let pos = this.getPos($atElement);
        this.array.splice(pos-1, 1);
        this.render();
    },
    getRowObject: function(label="", icon="", href="") {
        return {
            "label": label,
            "icon": icon,
            "href": href
        }
    },
    getIconClass: function(icon) {
        if(!/\s/g.test(icon)) {
            return "fa-solid fa-" + icon;
        }

        return icon;
    },
    getRow: function($element) {
        return $element.closest(this.selectors.row);
    },
    getPos: function($element) {
        return this.getRow($element).data("pos");
    },
    getTemplateHtml: function() {
        return $(this.selectors.template).html();
    },
    getRowHtml: function(pos, data) {
        return this.getTemplateHtml()
            .replaceAll(/%pos%/g, pos)
            .replaceAll(/%label%/g, data.label)
            .replaceAll(/%href%/g, data.href)
            .replaceAll(/%icon%/g, data.icon)
            .replaceAll(
                /%iconClass%/g,
                this.getIconClass(data.icon)
            );
    },
    render: function() {
        let $ctaForm = $(this.selectors.root);
        let $ctaRows = $ctaForm.find(this.selectors.rows);
        let $this = this;
        this.destroy();
        $.each(this.array, function(index, data) {
            $ctaRows.append($this.getRowHtml(index+1, data));
        });
        this.setEnable(this.enable);
        $ctaRows.sortable({
            cursor: "grabbing",
            axis: "y",
            update: function() {
                let array = [];
                $(this).find($this.selectors.row).each(function() {
                    let pos = $this.getPos($(this));
                    array.push($this.array[pos-1]);
                });
                $this.array = array;
                $this.render();
            }
        });
    },
    setEnable: function(integer) {
        let $ctaRows = $(this.selectors.rows, this.selectors.root);
        this.enable = integer;
        let enable = this.enable === 1;
        $('input[data-required]', $ctaRows).each(function() {
            $(this).attr("required", enable);
        });
    },
    destroy: function() {
        let $ctaForm = $(this.selectors.root);
        let $ctaRows = $ctaForm.find(this.selectors.rows);
        try {
            $ctaRows.sortable("destroy");
        } catch(e) {}
        $ctaRows.empty();
    }
}

CHV.fn.album = {
    showEmbedCodes: function () {
        var $loading = $(".content-listing-loading", "#tab-embeds");
        if (!$loading.exists()) {
            return;
        }
        var $embed_codes = $("#embed-codes");
        $.ajax({
            url: PF.obj.config.json_api,
            type: "POST",
            dataType: "json",
            data: {
                action: "get-album-contents",
                albumid: CHV.obj.resource.id,
                auth_token: PF.obj.config.auth_token
            },
            cache: false,
        }).always(function (XHR) {
            PF.fn.loading.destroy($loading);
            if (XHR.status_code == 200) {
                CHV.fn.fillEmbedCodes(XHR.contents, "#tab-embeds");
                $("#tab-embeds").addClass("visible");
                $embed_codes.removeClass("soft-hidden");
            }
        });
    },
}

CHV.fn.modal = {
    getTemplateWithPreview: function (selector, $target) {
        var template = $(selector).html();
        var div = $("<div/>");
        var html = '';
        var src = $target.find('.image-container img').attr('src');
        if (typeof src !== typeof undefined) {
            html += '<a href="' + $target.attr('data-url-short') + '" target="_blank"><img class="canvas checkered-background" src=' + src + ' /></a>';
        }
        div.html(template).find('.image-preview').html(html);

        return div.html();
    },
    getTemplateWithPreviews: function (selector, $targets, limit = 50) {
        var template = $(selector).html();
        var div = $("<div/>");
        var html = '';
        var counter = 0;
        $targets.each(function () {
            if (counter >= limit) {
                return false;
            }
            html += '<a class="image-preview-container checkered-background" href="' + $(this).attr('data-url-short') + '" target="_blank">';
            var src = $(this).find('.image-container img');
            if (src.exists()) {
                html += '<canvas width="160" height="160" class="thumb" style="background-image: url(' + src.attr("src") + ');" />';
            } else {
                html += '<canvas width="160" height="160" class="thumb" />';
                html += '<span class="empty icon fas fa-inbox"></span>';
            }
            html += '</a>';
            counter++;
        });
        div.html(template).find('.image-preview').html(html);

        return div.html();
    }
}

CHV.fn.listingViewer = {
    selectors: {
        bodyShown: ".--viewer-shown",
        content: ".viewer-content",
        template: "#viewer-template",
        root: ".viewer",
        rootShow: ".viewer--show",
        rootHide: ".viewer--hide",
        rootZero: ".viewer--zero",
        rootNavPrev: ".viewer--nav-prev",
        rootNavNext: ".viewer--nav-next",
        src: ".viewer-src",
        tools: ".viewer-tools",
        loader: ".viewer-loader",
        owner: ".viewer-owner",
        ownerGuest: ".viewer-owner--guest",
        ownerUser: ".viewer-owner--user",
        inputMap: ".viewer-kb-input",
    },
    keys: {
        "ArrowLeft": "prev",
        "ArrowRight": "next",
        "Delete": "delete",
        "Escape": "close",
        "KeyA": "create-album",
        "KeyE": "edit",
        "KeyF": "flag",
        "KeyL": "like",
        "KeyM": "move",
        "KeyO": "approve",
        "KeyS": "share",
        "KeyW": "zoom",
        "Period": "select",
    },
    keymap: {
        "create-album": ["A", PF.fn._s("Create album")],
        approve: ["O", PF.fn._s("Approve")],
        close: ["Esc", PF.fn._s("Close")],
        delete: ["Del", PF.fn._s("Delete")],
        edit: ["E", PF.fn._s("Edit")],
        flag: ["F", PF.fn._s("Toggle flag")],
        like: ["L", PF.fn._s("Like")],
        move: ["M", PF.fn._n("Move")],
        next: ["►", PF.fn._s("Next")],
        prev: ["◄", PF.fn._s("Previous")],
        select: [".", PF.fn._s("Toggle select")],
        share: ["S", PF.fn._s("Share")],
        zoom: ["W", PF.fn._s("Zoom")],
    },
    loading: null,
    idleTimer: 0,
    $item: null,
    show: function () {
        var paramsHidden = PF.fn.deparam(this.$item.closest(PF.obj.listing.selectors.content_listing_visible).data("params-hidden"));
        this.getEl("root")
            .removeClass(this.selectors.rootHide.substring(1))
            .addClass(this.selectors.rootShow.substring(1));
        $("body").addClass(this.selectors.bodyShown.substring(1));
        var hammertime = new Hammer($(CHV.fn.listingViewer.selectors.root).get(0), {
            direction: Hammer.DIRECTION_VERTICAL,
        });
        hammertime.on("swipeleft swiperight", function (e) {
            // left -> next, right -> prev
            var swipe = e.type.substring("swipe".length) == "left" ? "next" : "prev";
            CHV.fn.listingViewer[swipe]();
        });
        this.getEl("root")[
            (PF.fn.isDevice(["phone", "phablet"]) ?
                "add" :
                "remove"
            ) + "Class"]("--over");
    },
    getItem: function () {
        return this.$item;
    },
    getEl: function (sel) {
        var context =
            sel.startsWith("template") || sel.startsWith("root") ?
                false :
                this.selectors.root;
        return context ? $(this.selectors[sel], context) : $(this.selectors[sel]);
    },
    getObject: function (fresh) {
        if (fresh || typeof this.object == typeof undefined) {
            var json = decodeURIComponent(this.getItem().attr("data-object"));
            this.object = (JSON && JSON.parse(json)) || $.parseJSON(json);
        }
        return this.object;
    },
    placeholderSizing: function () {
        if (!this.getEl("root").exists()) return;
        var vW = Math.max(
            document.documentElement.clientWidth,
            window.innerWidth || 0
        );
        var vH = Math.max(
            document.documentElement.clientHeight,
            window.innerHeight || 0
        );
        var vR = vW / vH;
        var eSrc = this.getEl("src")[0];
        var eW = eSrc.getAttribute("width");
        var eH = eSrc.getAttribute("height");
        var eR = eW / eH;
        var c = vR < eR;
        eSrc.classList.remove("--width-auto", "--height-auto");
        eSrc.classList.add("--" + (c ? "height" : "width") + "-auto");
    },
    filler: function (isOpened) {
        var _this = this;
        var $viewer = this.getEl("root");
        if (isOpened) {
            var $parsed = $(this.getParsedTemplate());
            $viewer.html($parsed.html());
        }
        $viewer[(this.getItem().hasClass("selected") ? "add" : "remove") + "Class"](
            "selected"
        );
        var navActions = ["prev", "next"];
        $.each(navActions, function (i, v) {
            var navSelector =
                _this.selectors[
                "rootNav" + (v.charAt(0).toUpperCase() + v.slice(1).toLowerCase())
                ];
            var action =
                $(PF.obj.listing.selectors.content_listing_pagination + ":visible")
                    .length > 0 ?
                    "add" :
                    _this.getItem()[v]().exists() ?
                        "add" :
                        "remove";
            $viewer[action + "Class"](navSelector.substring(1));
        });
        $.each(this.getItem().get(0).attributes, function (i, attr) {
            if (!attr.name.startsWith("data-")) return true;
            $viewer.attr(attr.name, attr.value);
        });
        var handle = typeof this.object.user == typeof undefined ? "user" : "guest";
        handle =
            "owner" +
            (handle.charAt(0).toUpperCase() + handle.slice(1).toLowerCase());
        this.getEl(handle).remove();
        if (typeof this.object.user !== typeof undefined) {
            $(
                this.object.user.avatar ? ".default-user-image" : "img.user-image",
                this.getEl("ownerUser")
            ).remove();
        }
        var $tools = this.getItem().find(".list-item-image-tools[data-action='list-tools']");
        this.getEl("tools").append($tools.html());
        let $this = this;
        this.getEl("tools").find(".list-tool[data-action]").each(function() {
            $(this).attr("title", $(this).attr("title") + " ("+$this.keymap[$(this).attr("data-action")][0]+")");
        });
        this.placeholderSizing();
        this.trickyLoad();
    },
    zoom: function () {
        this.getEl("root").attr("data-cover", this.getEl("root").attr("data-cover") == "1" ? "0" : "1");
    },
    remove: function () {
        this.getEl("root").remove();
    },
    getParsedTemplate: function () {
        var object = this.getObject(true);
        var template = this.getEl("template").html();
        var matches = template.match(/%(\S+)%/g);
        if (matches) {
            $.each(matches, function (i, v) {
                var handle = v.slice(1, -1).split(".");
                var value;
                handle.map(function (k) {
                    var aux = !value ? object : value;
                    if (k in aux) {
                        value = aux[k];
                    }
                });
                var regex = new RegExp(v, "g");
                value = typeof value == typeof undefined ? "" : value;
                template = template.replace(regex, value);
            });
        }
        return template;
    },
    insertEl: function () {
        var html = this.getParsedTemplate();
        this.getEl("rootZero").remove();
        $(html).appendTo("body");
    },
    toggleIdle: function (idle, refresh) {
        var _this = this;
        var refresh = typeof refresh == typeof undefined ? true : refresh;
        $("html")[(idle ? "add" : "remove") + "Class"]("--idle");
        if (!idle) {
            clearTimeout(_this.idleTimer);
            if (refresh) {
                _this.idleTimer = setTimeout(function () {
                    var $fs = $(".fullscreen");
                    var $el = _this.getEl("root");
                    _this.toggleIdle($el.length > 0 && $fs.length == 0);
                }, 5000);
            }
        }
    },
    open: function ($item) {
        if (!$item.exists()) {
            this.getEl("rootZero").remove();
            return;
        }
        this.setItem($item);
        this.insertEl();
        this.filler();
        this.show();
        this.toggleIdle(false); // init idler
        var _this = this;
        this.getEl("root").on("mousemove mouseout", function () {
            _this.toggleIdle(false);
        });
    },
    setItem: function ($item) {
        this.$item = $item;
    },
    trickyLoad: function () {
        if (this.object.image.url == this.object.display_url) {
            return;
        }
        var srcHtml = this.getEl("src").parent().html();
        var $src = $(srcHtml).attr("src", this.object.image.url);
        $src.insertBefore(this.getEl("src"));
        var mediaTarget = $src.eq(0);
        if(mediaTarget.attr('data-type') === 'video') {
            mediaTarget.replaceWith(
                '<video class="viewer-src no-select" playsinline controls autoplay src="'+this.object.image.url+'"></video>'
            );
            mediaTarget.src = this.object.image.url;
        } else {
            mediaTarget.attr("src", this.object.image.url);
        }
        $src.imagesLoaded(function () {
            $src.next().remove();
        });
    },
    close: function () {
        var _this = this;
        $(this.selectors.root)
            .removeClass(this.selectors.rootShow.substring(1))
            .addClass(this.selectors.rootHide.substring(1));
        $("body").removeClass(this.selectors.bodyShown.substring(1));
        this.toggleIdle(false, false);
        if (this.getItem() !== null) {
            $(window).scrollTop(this.getItem().offset().top);
        }
        var subjects = $("#top-bar, .follow-scroll");
        subjects.attr("data-scroll-lock", "1");
        setTimeout(function () {
            _this.remove();
        }, 250);
        setTimeout(function () {
            subjects.removeAttr("data-scroll-lock");
        }, 300);
    },
    browse: function (direction) {
        var $item = this.getItem()[direction]();
        if (!$item.exists()) {
            var $pagination = $(
                "[data-pagination=" + direction + "]",
                PF.obj.listing.selectors.content_listing_pagination + ":visible"
            );
            var href = $pagination.attr("href");
            if (!href) return;
            var UrlParams = PF.fn.deparam(window.location.search);
            window.location.href = href + "&viewer=" + direction;
            return;
        }
        this.setItem($item);
        this.filler(true);
        var $loadMore = $(PF.obj.listing.selectors.content_listing_visible).find(
            "[data-action=load-more]"
        );
        var padding = $item[direction + "All"]().length;
        if (
            $loadMore.length > 0 &&
            padding <= 5 &&
            !PF.obj.listing.calling &&
            direction == "next"
        ) {
            $("[data-action=load-more]").click();
        }
    },
    prev: function () {
        this.browse("prev");
    },
    next: function () {
        this.browse("next");
    },
};

CHV.obj.image_viewer = {
    selector: "#image-viewer",
    container: "#image-viewer",
    navigation: ".image-viewer-navigation",
    loading: "#image-viewer-loading",
    loader: "#image-viewer-loader",
};
CHV.obj.image_viewer.$container = $(CHV.obj.image_viewer.container);
CHV.obj.image_viewer.$navigation = $(CHV.obj.image_viewer.navigation);
CHV.obj.image_viewer.$loading = $(CHV.obj.image_viewer.loading);

CHV.fn.system = {
    checkUpdates: function (callback) {
        $.ajax({
            url: CHEVERETO.api.get.info + "/",
            data: { id: CHEVERETO.id },
            cache: false,
        }).always(function (data, status, XHR) {
            if (typeof callback == "function") {
                callback(XHR);
            }
        });
    },
};
if((navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 0) || navigator.platform === 'iPad') {
    $("html").removeClass("device-nonmobile");
}
CHV.fn.bindSelectableItems = function () {
    var el = "content-listing-wrapper";
    var sel = "#" + el;
    if (!$(sel).exists()) {
        $("#content-listing-tabs")
            .wrap("<div id='" + el + "' />");
    } else if ($(sel).hasClass("ui-selectable")) {
        $(sel).selectable("destroy");
    }
    if (!$("[data-content=list-selection]").exists()) {
        return;
    }
    $("html.device-nonmobile " + sel).selectable({
        delay: 150,
        filter: PF.obj.listing.selectors.list_item,
        cancel: ".content-empty, .header, #tab-share, #tab-info, .viewer-title, .header-link, .top-bar, .content-listing-pagination *, #fullscreen-modal, #top-user, #background-cover, .list-item-desc, .list-item-image-tools, [data-action=load-image], #tab-embeds",
        classes: {
            "ui-selected": "selected",
        },
        selected: function (event, ui) {
            $(ui.selected).removeClass('ui-selected');
        },
        selecting: function (event, ui) {
            var $this = $(ui.selecting);
            var unselect = $this.hasClass("selected");
            CHV.fn.list_editor[(unselect ? "unselect" : "select") + "Item"]($this);
        },
        unselecting: function (event, ui) {
            CHV.fn.list_editor.unselectItem($(ui.unselecting));
        },
    });
};

CHV.fn.isCachedImage = function (src) {
    var image = new Image();
    image.src = src;
    return image.complete || image.width + image.height > 0;
};

CHV.fn.viewerLoadImage = function () {
    if (CHV.obj.image_viewer.$loading.exists()) {
        CHV.obj.image_viewer.$loading.removeClass("soft-hidden").css({
            zIndex: 2,
        });
        PF.fn.loading.inline(CHV.obj.image_viewer.$loading, {
            color: "white",
            size: "small",
            center: true,
            valign: true,
        });
        CHV.obj.image_viewer.$loading.hide().fadeIn("slow");
    }
    $(CHV.obj.image_viewer.loader).remove();
    if (CHV.obj.image_viewer.image.is_360) {
        PF.fn.loading.destroy(CHV.obj.image_viewer.$loading);
        pannellum.viewer('image-viewer-360', {
            autoLoad: true,
            type: "equirectangular",
            panorama: CHV.obj.image_viewer.image.url,
            preview: CHV.obj.image_viewer.$container.find(".media").eq(0).attr("src"),
            pitch: 2.3,
            yaw: -135.4,
            hfov: 120
        });
        $("#image-viewer-360").removeClass("soft-hidden");
        CHV.obj.image_viewer.$container.find(".media").eq(0).remove();
        return;
    }
    CHV.obj.image_viewer.image.html = CHV.obj.image_viewer.$container.html();
    CHV.obj.image_viewer.$container
        .css("height", CHV.obj.image_viewer.$container.height())
        .prepend(
            $(CHV.obj.image_viewer.image.html).css({
                top: 0,
                zIndex: 0,
                opacity: 0,
                position: "absolute"
            })
        );
    CHV.obj.image_viewer.$container.find(".media").eq(0).css("zIndex", 1);
    var mediaTarget = CHV.obj.image_viewer.$container.find(".media").eq(1);
    var width = mediaTarget.css("width");
    var height = mediaTarget.css("height");
    if(mediaTarget.attr('data-type') === 'video') {
        mediaTarget.replaceWith(
            '<video class="media animate" controls autoplay width="'+width+'" height="'+height+'" src="'+CHV.obj.image_viewer.image.url+'" style="opacity: 0;"></video>'
        );
        mediaTarget.src = CHV.obj.image_viewer.image.url;
    } else {
        mediaTarget.attr("src", CHV.obj.image_viewer.image.url);
    }
    mediaTarget
        .imagesLoaded(function () {
            CHV.obj.image_viewer.$container.find(".media").eq(1).css({ position: "", display: "", opacity: 1});
            CHV.obj.image_viewer.$container.find(".media").eq(0).remove();
            $(CHV.obj.image_viewer.container).css('height', '');
            PF.fn.loading.destroy(CHV.obj.image_viewer.$loading);
        });
};

CHV.obj.embed_share_tpl = {};
CHV.obj.embed_upload_tpl = {};

CHV.obj.topBar = {
    transparencyScrollToggle: function () {
        var Y = $(window).scrollTop();
        $("#top-bar")[(Y > 0 ? "remove" : "add") + "Class"]("transparent");
    },
};

CHV.obj.uploaderReset = {
    isUploading: false,
    canAdd: true,
    queueStatus: "ready",
    uploadThreads: 0,
    uploadParsedIds: [],
    uploadProcessedIds: [],
    files: {},
    results: {
        success: {},
        error: {},
    },
    toggleWorking: 0,
    filesAddId: 0,
    clipboardImages: [],
};

CHV.fn.uploader = {
    files: {},
    selectors: {
        root: "#anywhere-upload",
        show: ".upload-box--show",
        queue: "#anywhere-upload-queue",
        queue_complete: ".queue-complete",
        queue_item: ".queue-item",
        close_cancel: "[data-button=close-cancel]",
        file: "#anywhere-upload-input",
        camera: "#anywhere-upload-input-camera",
        upload_item_template: "#anywhere-upload-item-template",
        item_progress_bar: "[data-content=progress-bar]",
        failed_result: "[data-content=failed-upload-result]",
        fullscreen_mask: "#fullscreen-uploader-mask",
        dropzone: "#uploader-dropzone",
        paste: "#anywhere-upload-paste",
        input: "[data-action=anywhere-upload-input]",
    },
    toggle: function (options, args) {
        this.queueSize();

        var $switch = $("[data-action=top-bar-upload]", ".top-bar");
        var show = !$(CHV.fn.uploader.selectors.root).data("shown");
        var options = $.extend({
            callback: null,
            reset: true,
        },
            options
        );

        if (typeof options.show !== typeof undefined && options.show) {
            show = true;
        }

        PF.fn.growl.close(true);
        PF.fn.close_pops();

        if (
            this.toggleWorking == 1 ||
            $(CHV.fn.uploader.selectors.root).is(":animated") ||
            CHV.fn.uploader.isUploading ||
            ($switch.data("login-needed") && !PF.fn.is_user_logged())
        )
            return;

        this.toggleWorking = 1;

        var animation = {
            time: 500,
            easing: null,
        };
        var callbacks = function () {
            if (!show && options.reset) {
                CHV.fn.uploader.reset();
            }
            PF.fn.topMenu.hide();
            if (typeof options.callback == "function") {
                options.callback(args);
            }
            CHV.fn.uploader.boxSizer();
            CHV.fn.uploader.toggleWorking = 0;
        };

        $(CHV.fn.uploader.selectors.root)[(show ? "add" : "remove") + "Class"](
            this.selectors.show.substring(1)
        );

        if (show) {
            $("html")
                .data({
                    "followed-scroll": $("html").hasClass("followed-scroll"),
                    "top-bar-box-shadow-prevent": true,
                })
                .removeClass("followed-scroll")
                .addClass("overflow-hidden top-bar-box-shadow-none");
            $("#top-bar")
                .data({
                    stock_classes: $("#top-bar").attr("class"),
                })
                .addClass("scroll-up");
            $(".current[data-nav]", ".top-bar").each(function () {
                if ($(this).is("[data-action=top-bar-menu-full]")) return;
                $(this).removeClass("current").attr("data-current", 1);
            });
            if (PF.fn.isDevice("mobile")) {
                var $upload_heading = $(
                    ".upload-box-heading",
                    $(CHV.fn.uploader.selectors.root)
                );
                $upload_heading.css({
                    position: "relative",
                    top: 0.5 * ($(window).height() - $upload_heading.height()) + "px",
                });
            }
            CHV.fn.uploader.focus(function () {
                setTimeout(function () {
                    callbacks();
                }, animation.time);
            });
        } else {
            $("#top-bar")[0].className = $("#top-bar").data('stock_classes');
            $("[data-nav][data-current=1]", ".top-bar").each(function () {
                $(this).addClass("current");
            });
            setTimeout(function () {
                $(CHV.fn.uploader.selectors.fullscreen_mask).css({
                    opacity: 0,
                });
            }, 0.1 * animation.time);
            setTimeout(function () {
                $(CHV.fn.uploader.selectors.fullscreen_mask).remove();
            }, animation.time);

            var _uploadBoxHeight = $(CHV.fn.uploader.selectors.root).outerHeight();
            var _uploadBoxPush =
                _uploadBoxHeight -
                parseInt($(CHV.fn.uploader.selectors.root).data("initial-height")) +
                "px";
            $(CHV.fn.uploader.selectors.root).css({
                transform: "translate(0,-" + _uploadBoxPush + ")",
            });

            setTimeout(function () {
                $(CHV.fn.uploader.selectors.root).css({
                    top: "",
                });
                callbacks();
                $("html,body").removeClass("overflow-hidden").data({
                    "top-bar-box-shadow-prevent": false,
                });
                $("#top-bar *").trigger("blur");
            }, animation.time);
        }

        $(CHV.fn.uploader.selectors.root).data("shown", show);

        $switch.toggleClass("current").removeClass("opened");
    },

    reset: function () {
        $.extend(this, $.extend(true, {}, CHV.obj.uploaderReset));

        $("li", this.selectors.queue).remove();
        $(this.selectors.root).height("").css({
            "overflow-y": "",
            "overflow-x": "",
        });

        $(this.selectors.queue)
            .addClass("queueEmpty")
            .removeClass(this.selectors.queue_complete.substring(1));

        $(this.selectors.input, this.selectors.root).each(function () {
            $(this).prop("value", null);
        });
        $("[data-group=upload-result] textarea", this.selectors.root).prop(
            "value",
            ""
        );
        $.each(
            [
                "upload-queue-ready",
                "uploading",
                "upload-result",
                "upload-queue-ready",
                "upload-queue",
            ],
            function (i, v) {
                $("[data-group=" + v + "]").hide();
            }
        );
        $("[data-group=upload]", this.selectors.root).show();
        // Force HTML album selection (used for upload to current album)
        $("[name=upload-album-id]", this.selectors.root).prop("value", function () {
            var $selected = $("option[selected]", this);
            if ($selected.exists()) {
                return $selected.attr("value");
            }
        });

        $(this.selectors.root)
            .removeClass("queueCompleted queueReady queueHasResults")
            .addClass("queueEmpty")
            .attr("data-queue-size", 0);

        // Always ask for category
        $("[name=upload-category-id]", this.selectors.root).prop("value", "");
        $("[name=upload-nsfw]", this.selectors.root).prop(
            "checked",
            this.defaultChecked
        );

        this.boxSizer(true);
    },

    focus: function (callback) {
        if ($(this.selectors.fullscreen_mask).exists()) return;
        if (!$("body").is("#upload")) {
            $("body").append(
                $("<div/>", {
                    id: this.selectors.fullscreen_mask.replace("#", ""),
                    class: "fullscreen black",
                }).css({
                    top: PF.fn.isDevice("phone") ?
                        0 : $(CHV.fn.uploader.selectors.root).data("top"),
                })
            );
        }
        setTimeout(function () {
            if (!$("body").is("#upload")) {
                $(CHV.fn.uploader.selectors.fullscreen_mask).css({
                    opacity: 1,
                });
            }
            setTimeout(
                function () {
                    if (typeof callback == "function") {
                        callback();
                    }
                },
                PF.fn.isDevice(["phone", "phablet"]) ? 0 : 250
            );
        }, 1);
    },

    boxSizer: function (forced) {
        var shown = $(this.selectors.root).is(this.selectors.show);
        var doit = shown || forced;
        if (shown) {
            $("html").addClass("overflow-hidden");
        }
        if (!doit) return;
        $(this.selectors.root).height("");
        if (!$("body").is("#upload") &&
            $(this.selectors.root).height() > $(window).height()
        ) {
            $(this.selectors.root).height(
                $(window).height() - $("#top-bar").height()
            ).css({
                "overflow-y": "scroll",
                "overflow-x": "auto",
            });
            $("html").addClass("overflow-hidden");
        } else {
            $(this.selectors.root).css("overflow-y", "");
        }
    },

    pasteURL: function () {
        var textarea = $("[name=urls]", PF.obj.modal.selectors.root);
        var value = textarea.val();
        if (value) {
            CHV.fn.uploader.toggle({ show: true });
            CHV.fn.uploader.add({}, value);
        }
    },

    pasteImageHandler: function (e) {
        // Leave the inputs alone
        if ($(e.target).is(":input")) {
            return;
        }
        // Get the items from the clipboard
        if (typeof e.clipboardData !== typeof undefined && e.clipboardData.items) {
            var items = e.clipboardData.items;
        } else {
            setTimeout(function () {
                // Hack to get the items after paste
                e.clipboardData = {};
                e.clipboardData.items = [];
                $.each($("img", CHV.fn.uploader.$pasteCatcher), function (i, v) {
                    e.clipboardData.items.push(PF.fn.dataURItoBlob($(this).attr("src")));
                });
                $(CHV.fn.uploader.selectors.paste).html("");
                return CHV.fn.uploader.pasteImageHandler(e);
            }, 1);
        }
        if (items) {
            const files = new Array();
            const urls = new Array();
            const regex = new RegExp("^(image|video)/", "i");
            let uploaderIsVisible = $(CHV.fn.uploader.selectors.root).data("shown");
            for (var i = 0; i < items.length; i++) {
                if (regex.test(items[i].type)) {
                    let file = items[i].getAsFile();
                    files.push(file);
                } else if (items[i].kind == 'string') {
                    if (!CHV.obj.config.upload.url) {
                        continue;
                    }
                    items[i].getAsString(function (s) {
                        CHV.fn.uploader.add({}, s);
                    })
                    urls.push(i);
                }
            }
            if (files.length == 0 && urls.length == 0) {
                return;
            }

            var pushEvent = {
                originalEvent: {
                    dataTransfer: {
                        files: [...files]
                    },
                    preventDefault: function () { },
                    stopPropagation: function () { },
                }
            }
            if (!uploaderIsVisible) {
                CHV.fn.uploader.toggle({
                    callback: function () {
                        CHV.fn.uploader.add(pushEvent);
                    },
                });
            } else {
                CHV.fn.uploader.add(pushEvent);
            }
        }
    },

    add: function (e, urls) {
        var md5;

        // Prevent add items ?
        if (!this.canAdd) {
            var e = e.originalEvent;
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        var $file_input = $(this.selectors.file);
        $file_input.replaceWith(($file_input = $file_input.clone(true)));
        var item_queue_template = $(this.selectors.upload_item_template).html();
        let files = [];
        let directories = [];

        function addDirectoryItem(item, files, directories, isLast) {
            if (item.isDirectory) {
                var directoryReader = item.createReader();
                directoryReader.readEntries(function (entries) {
                    var size = entries.length;
                    var i = 0;
                    entries.forEach(function (entry) {
                        i++;
                        if (entry.name === '.DS_Store') {
                            return;
                        }
                        addDirectoryItem(entry, files, directories, size === i);
                    });
                });
                directories.push(item.name)
            } else {
                item.file(function (file) {
                    files.push(file);
                    if (isLast) {
                        CHV.fn.uploader.add({
                            originalEvent: {
                                preventDefault: function () { },
                                stopPropagation: function () { },
                                dataTransfer: {
                                    parsedItems: true,
                                    files: [...files]
                                }
                            }
                        })
                    }
                });
            }
        }

        if (typeof urls == typeof undefined) {
            var e = e.originalEvent;
            e.preventDefault();
            e.stopPropagation();
            var data = e.dataTransfer || e.target;

            if ("items" in data) {
                var items = data.items;
                for (var i = 0; i < items.length; i++) {
                    var item = items[i].webkitGetAsEntry();
                    if (item) {
                        addDirectoryItem(item, files, directories, false);
                    }
                }
            }
            if ("files" in data) {
                files = Array.isArray(data.files)
                    ? data.files.slice()
                    : $.makeArray(data.files);

                files = files.filter(function (o) {
                    return (directories.indexOf(o.name) < 0);
                });
            }

            // Keep a map for the clipboard images
            // if (e.clipboard) {
            //     md5 = PF.fn.md5(e.dataURL);
            //     if ($.inArray(md5, this.clipboardImages) != -1) {
            //         return null;
            //     }
            //     this.clipboardImages.push(md5);
            // }

            // Filter non-images
            var failed_files = [];
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                if (directories.includes(file.name)) {
                    continue;
                }
                var image_type_str;
                if (typeof file.type == "undefined" || file.type == "") {
                    // Some browsers (Android) don't set the correct file.type
                    image_type_str = file.name
                        .substr(file.name.lastIndexOf(".") + 1)
                        .toLowerCase();
                } else {
                    image_type_str = file.type
                        .replace("image/", "")
                        .replace("video/", "");
                }
                // Size filter
                if (file.size > CHV.obj.config.image.max_filesize.getBytes()) {
                    failed_files.push({
                        uid: i,
                        name: file.name.truncate_middle() + " - " + PF.fn._s("File too big."),
                        error: 'MEDIA_ERR_FILE_SIZE',
                    });
                    continue;
                }
                // Android can output something like image:10 as the full file name so ignore this filter
                if (
                    CHV.obj.config.upload.image_types.indexOf(image_type_str) == -1 &&
                    /android/i.test(navigator.userAgent) == false
                ) {
                    failed_files.push({
                        uid: i,
                        name: file.name.truncate_middle() +
                            " - " +
                            PF.fn._s("Invalid or unsupported file format."),
                        error: 'MEDIA_ERR_FILETYPE',
                    });
                    continue;
                }
                if (md5) {
                    file.md5 = md5;
                }
                file.fromClipboard = e.clipboard == true;
                file.uid = i;
            }
            for (var i = 0; i < failed_files.length; i++) {
                var failed_file = failed_files[i];
                files.splice(failed_file.id, 1);
            }
            if (failed_files.length > 0 && files.length == 0) {
                var failed_message = "";
                for (var i = 0; i < failed_files.length; i++) {
                    failed_message +=
                        "<li>" + PF.fn.htmlEncode(failed_files[i].name) + "</li>";
                }
                PF.fn.modal.simple({
                    title: PF.fn._s("Some files couldn't be added"),
                    message: "<ul>" + "<li>" + failed_message + "</ul>",
                });
                return;
            }

            if (files.length == 0) {
                return;
            }
        } else {
            // Remote files
            // Strip HTML + BBCode
            urls = urls.replace(/(<([^>]+)>)/g, "").replace(/(\[([^\]]+)\])/g, "");
            files = urls.match_urls();
            if (!files) return;
            files = files.array_unique();
            files = $.map(files, function (file, i) {
                return {
                    uid: i,
                    name: file,
                    url: file,
                };
            });
        }

        // Empty current files object?
        if ($.isEmptyObject(this.files)) {
            for (var i = 0; i < files.length; i++) {
                this.files[files[i].uid] = files[i];
                this.filesAddId++;
            }
        } else {
            /**
             * Check duplicates by file name (local and remote)
             * This is basic but is the quickest way to do it
             * Note: it doesn't work on iOS for local files http://stackoverflow.com/questions/18412774/get-real-file-name-in-ios-6-x-filereader
             */
            var current_files = [];
            for (var key in this.files) {
                if (
                    typeof this.files[key] == "undefined" ||
                    typeof this.files[key] == "function"
                )
                    continue;
                current_files.push(encodeURI(this.files[key].name));
            }
            files = $.map(files, function (file, i) {
                if ($.inArray(encodeURI(file.name), current_files) != -1) {
                    return null;
                }
                file.uid = CHV.fn.uploader.filesAddId;
                CHV.fn.uploader.filesAddId++;
                return file;
            });
            for (var i = 0; i < files.length; i++) {
                this.files[files[i].uid] = files[i];
            }
        }

        $(this.selectors.queue, this.selectors.root).append(
            item_queue_template.repeat(files.length)
        );

        $(
            this.selectors.queue +
            " " +
            this.selectors.queue_item +
            ":not([data-id])",
            this.selectors.root
        ).hide(); // hide the stock items

        var failed_before = failed_files,
            failed_files = [],
            j = 0,
            default_options = {
                canvas: true,
                maxWidth: 610,
            };

        function CHVLoadImage(i) {
            if (typeof i == typeof undefined) {
                var i = 0;
            }
            if (!(i in files)) {
                PF.fn.loading.destroy("fullscreen");
                return;
            }
            var file = files[i];
            if (directories.includes(file.name)) {
                return;
            }
            $(
                CHV.fn.uploader.selectors.queue_item + ":not([data-id]) .load-url",
                CHV.fn.uploader.selectors.queue
            )[typeof file.url !== "undefined" ? "show" : "remove"]();

            loadImage.parseMetaData(file.url ? file.url : file, function (data) {
                // Set the queue item placeholder ids
                $(
                    CHV.fn.uploader.selectors.queue_item +
                    ":not([data-id]) .preview:empty",
                    CHV.fn.uploader.selectors.queue
                )
                    .first()
                    .closest("li")
                    .attr("data-id", file.uid);

                function getQueueItem(uid) {
                    return $(
                        CHV.fn.uploader.selectors.queue_item +
                        "[data-id=" + uid +"]",
                        CHV.fn.uploader.selectors.queue
                    );
                }

                function displayQueueIfNotVisible() {
                    if (!$(
                        "[data-group=upload-queue]",
                        CHV.fn.uploader.selectors.root
                    ).is(":visible")) {
                        $(
                            "[data-group=upload-queue]",
                            CHV.fn.uploader.selectors.root
                        ).css("display", "block");
                    }
                }

                function getTitle(file) {
                    var title = null;
                    if (typeof file.name !== typeof undefined) {
                        var basename = PF.fn.baseName(file.name);
                        title = $.trim(
                            basename
                                .substring(0, 100)
                                .capitalizeFirstLetter()
                        );
                    }
                    return title;
                }

                function loadVideo(url, callback) {
                    const video = document.createElement("video");
                    video.onerror = (e) => {
                        const videoError = {
                            1: "MEDIA_ERR_ABORTED",
                            2: "MEDIA_ERR_NETWORK",
                            3: "MEDIA_ERR_DECODE",
                            4: "MEDIA_ERR_SRC_NOT_SUPPORTED",
                        }
                        var error = videoError[video.error.code];
                        callback({ type: "error", error: error })
                        console.error("Error loading video", error)
                    }
                    video.addEventListener("loadedmetadata", function () {
                        const seek = parseInt(video.duration / 4);
                        setTimeout(() => {
                            video.currentTime = seek;
                            video.pause();
                        }, 200);
                        video.addEventListener("seeked", () => {
                            const canvas = document.createElement("canvas");
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;
                            const ctx = canvas.getContext("2d");
                            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                            ctx.canvas.toBlob(
                                blob => {
                                    callback(video, canvas)
                                },
                                "image/jpeg",
                                0.90
                            );
                        }, false);
                    });
                    if (/iPad|iPhone|iPod|Safari/.test(navigator.userAgent)) {
                        video.autoplay = true;
                        video.playsInline = true;
                        video.muted = true;
                    }
                    video.preload = "metadata";
                    video.src = url;
                }

                function setQueueReady($queue_item, img) {
                    $queue_item.show();
                    $(CHV.fn.uploader.selectors.root)
                        .addClass("queueReady")
                        .removeClass("queueEmpty");
                    $("[data-group=upload-queue-ready]", CHV.fn.uploader.selectors.root).show();
                    $("[data-group=upload]", CHV.fn.uploader.selectors.root).hide();
                    $queue_item.find(".load-url").remove();
                    $queue_item
                        .find(".preview")
                        .removeClass("soft-hidden")
                        .show()
                        .append(img);
                    $img = $queue_item.find(".preview").find("img,canvas");
                    $img.attr("class", "canvas");
                    queue_item_h = $queue_item.height();
                    queue_item_w = $queue_item.width();
                    var img_w = parseInt($img.attr("width")) || $img.width();
                    var img_h = parseInt($img.attr("height")) || $img.height();
                    var img_r = img_w / img_h;
                    $img.hide();
                    if (img_w > img_h || img_w == img_h) {
                        // Landscape
                        var queue_img_h = img_h < queue_item_h ? img_h : queue_item_h;
                        if (img_w > img_h) {
                            $img.height(queue_img_h).width(queue_img_h * img_r);
                        }
                    }
                    if (img_w < img_h || img_w == img_h) {
                        // Portrait
                        var queue_img_w = img_w < queue_item_w ? img_w : queue_item_w;
                        if (img_w < img_h) {
                            $img.width(queue_img_w).height(queue_img_w / img_r);
                        }
                    }
                    if (img_w == img_h) {
                        $img.height(queue_img_h).width(queue_img_w);
                    }
                    $img
                        .css({
                            marginTop: -$img.height() / 2,
                            marginLeft: -$img.width() / 2,
                        })
                        .show();
                    displayQueueIfNotVisible();
                    CHV.fn.uploader.boxSizer();
                }

                function someFilesFailed(j, files, failed_files) {
                    if (j !== files.length) {
                        return;
                    }
                    if (typeof failed_before !== "undefined") {
                        failed_files = failed_files.concat(failed_before);
                    }
                    PF.fn.loading.destroy("fullscreen");
                    if (failed_files.length > 0) {
                        var failed_message = "";
                        for (var i = 0; i < failed_files.length; i++) {
                            failed_message +=
                                "<li>" +
                                PF.fn.htmlEncode(failed_files[i].name) +
                                " - " +
                                PF.fn.htmlEncode(failed_files[i].error) +
                                "</li>";
                            delete CHV.fn.uploader.files[failed_files[i].uid];
                            $(
                                "li[data-id=" + failed_files[i].uid + "]",
                                CHV.fn.uploader.selectors.queue
                            )
                                .find("[data-action=cancel]")
                                .click();
                        }
                        PF.fn.modal.simple({
                            title: PF.fn._s("Some files couldn't be loaded"),
                            message: "<ul>" + failed_message + "</ul>",
                        });
                    } else {
                        CHV.fn.uploader.focus();
                    }
                    CHV.fn.uploader.boxSizer();
                }

                // Load the image (async)
                if(typeof file.type !== "undefined" && file.type.startsWith('video/')) {
                    var $queue_item = getQueueItem(file.uid);
                    var title = getTitle(file);
                    var videoUrl = URL.createObjectURL(file);
                    loadVideo(
                        videoUrl,
                        function(video, canvas) {
                            ++j;
                            // var $queue_item = getQueueItem(file.uid);
                            if (video.type === "error") {
                                failed_files.push({
                                    uid: file.uid,
                                    name: file.name.truncate_middle(),
                                    error: video.error
                                });
                            } else {
                                CHV.fn.uploader.files[file.uid].parsedMeta = {
                                    title: title,
                                    width: video.videoWidth,
                                    height: video.videoHeight,
                                    mimetype: file.type,
                                };
                                setQueueReady($queue_item, canvas);
                            }
                            someFilesFailed(j, files, failed_files);
                        }
                    )
                } else {
                    loadImage(
                        file.url ? file.url : file,
                        function (img) {
                            ++j;
                            var $queue_item = getQueueItem(file.uid);
                            if (img.type === "error") {
                                failed_files.push({
                                    uid: file.uid,
                                    name: file.name.truncate_middle(),
                                    error: 'MEDIA_ERR_SRC_FORMAT'
                                });
                            } else {
                                displayQueueIfNotVisible();
                                // Detect true mimetype
                                var mimetype = "image/jpeg"; // Default unknown mimetype
                                if (typeof data.buffer !== typeof undefined) {
                                    var buffer = new Uint8Array(data.buffer).subarray(0, 4);
                                    var header = "";
                                    for (var i = 0; i < buffer.length; i++) {
                                        header += buffer[i].toString(16);
                                    }
                                    var header_to_mime = {
                                        "89504e47": "image/png",
                                        "47494638": "image/gif",
                                        "ffd8ffe0": "image/jpeg",
                                        "ffd8ffe1": "image/jpeg",
                                        "ffd8ffe2": "image/jpeg",
                                        "ffd8ffe3": "image/jpeg",
                                        "ffd8ffe8": "image/jpeg"
                                    };
                                    if (typeof header_to_mime[header] !== typeof undefined) {
                                        mimetype = header_to_mime[header];
                                    }
                                }
                                var title = getTitle(file);
                                CHV.fn.uploader.files[file.uid].parsedMeta = {
                                    title: title,
                                    width: img.originalWidth,
                                    height: img.originalHeight,
                                    mimetype: mimetype,
                                };
                                setQueueReady($queue_item, img);
                            }
                            someFilesFailed(j, files, failed_files);
                        },
                        $.extend({}, default_options, {
                            orientation: data.exif ? data.exif.get("Orientation") : 1,
                        })
                    );
                }

                // Next one
                setTimeout(function () {
                    CHVLoadImage(i + 1);
                }, 25);
            });
        }

        PF.fn.loading.fullscreen();
        CHVLoadImage();
        this.queueSize();
    },

    queueSize: function () {
        $(this.selectors.root).attr("data-queue-size", Object.size(this.files));
        $("[data-text=queue-objects]", this.selectors.root).text(
            PF.fn._n("file", "files", Object.size(this.files))
        );
        $("[data-text=queue-size]", this.selectors.root).text(
            Object.size(this.files)
        );
    },

    queueProgress: function (e, id) {
        var queue_size = Object.size(this.files);
        this.files[id].progress = e.loaded / e.total;
        var progress = 0;
        for (var i = 0; i < queue_size; i++) {
            if (
                typeof this.files[i] == typeof undefined ||
                !("progress" in this.files[i])
            )
                continue;
            progress += this.files[i].progress;
        }
        $("[data-text=queue-progress]", this.selectors.root).text(
            parseInt((100 * progress) / queue_size)
        );
    },

    upload: function ($queue_item) {
        var id = $queue_item.data("id");
        var nextId = $queue_item.next().exists() ?
            $queue_item.next().data("id") :
            false;

        // Already working on this?
        if ($.inArray(id, this.uploadParsedIds) !== -1) {
            if ($queue_item.next().exists()) {
                this.upload($queue_item.next());
            }
            return;
        }

        var self = this;

        this.uploadParsedIds.push(id);

        var f = this.files[id];
        if (typeof f == typeof undefined) {
            return;
        }
        var queue_is_url = typeof f.url !== typeof undefined;
        var source = queue_is_url ? f.url : f;
        var hasForm = typeof f.formValues !== typeof undefined;

        if (typeof f == typeof undefined) {
            if ($queue_item.next().exists()) {
                this.upload($queue_item.next());
            }
            return;
        }

        this.uploadThreads += 1;

        if (this.uploadThreads < CHV.obj.config.upload.threads && nextId) {
            this.upload($queue_item.next());
        }

        this.isUploading = true;

        var form = new FormData();
        var formData = {
            source: null,
            type: queue_is_url ? "url" : "file",
            action: "upload",
            privacy: $("[data-privacy]", this.selectors.root).first().data("privacy"),
            timestamp: this.timestamp,
            auth_token: PF.obj.config.auth_token,
            expiration: $("[name=upload-expiration]", this.selectors.root).val() || '',
            category_id: $("[name=upload-category-id]", this.selectors.root).val() || null,
            nsfw: $("[name=upload-nsfw]", this.selectors.root).prop("checked") ?
                1 : 0,
            album_id: $("[name=upload-album-id]", this.selectors.root).val() || null,
            mimetype: f.type
        };

        // Append URL BLOB source
        if (queue_is_url) {
            formData.source = source;
        } else {
            form.append("source", source, f.name); // Stupid 3rd argument for file
        }
        if (hasForm) {
            // Merge with each queue item form data
            $.each(f.formValues, function (i, v) {
                formData[i.replace(/image_/g, "")] = v;
            });
        }

        $.each(formData, function (i, v) {
            if (v === null) return true;
            form.append(i, v);
        });

        this.files[id].xhr = new XMLHttpRequest();

        $queue_item.removeClass("waiting");
        $(".block.edit, .queue-item-button.edit", $queue_item).remove();

        if (!queue_is_url) {
            this.files[id].xhr.upload.onprogress = function (e) {
                if (e.lengthComputable) {
                    CHV.fn.uploader.queueProgress(e, id);
                    percentComplete = parseInt((e.loaded / e.total) * 100);
                    $(CHV.fn.uploader.selectors.item_progress_bar, $queue_item).width(
                        100 - percentComplete + "%"
                    );

                    if (percentComplete == 100) {
                        CHV.fn.uploader.itemLoading($queue_item);
                    }
                }
            };
        } else {
            this.queueSize();
            this.queueProgress({
                loaded: 1,
                total: 1,
            },
                id
            );
            this.itemLoading($queue_item);
        }

        this.files[id].xhr.onreadystatechange = function () {
            var is_error = false;

            if (
                this.readyState == 4 &&
                typeof CHV.fn.uploader.files[id].xhr !== "undefined" &&
                CHV.fn.uploader.files[id].xhr.status !== 0
            ) {
                self.uploadProcessedIds.push(id);
                self.uploadThreads -= 1;

                $(".loading-indicator", $queue_item).remove();
                $queue_item.removeClass("waiting uploading");

                try {
                    // Parse the json response
                    var JSONresponse =
                        this.responseType !== "json" ?
                            JSON.parse(this.response) :
                            this.response;
                    if (typeof JSONresponse !== "undefined" && this.status == 200) {
                        $("[data-group=image-link]", $queue_item).attr(
                            "href",
                            JSONresponse.image.path_viewer
                        );
                    } else {
                        if (JSONresponse.error.context == "PDOException") {
                            JSONresponse.error.message = "Database error";
                        }
                        JSONresponse.error.message =
                            PF.fn.htmlEncode(CHV.fn.uploader.files[id].name.truncate_middle()) +
                            " - " +
                            JSONresponse.error.message;
                    }

                    // Save the server response (keeping indexing for results)
                    CHV.fn.uploader.results[this.status == 200 ? "success" : "error"][
                        id
                    ] = JSONresponse;

                    if (this.status !== 200) is_error = true;
                } catch (err) {
                    is_error = true;

                    var err_handle;

                    if (typeof JSONresponse == typeof undefined) {
                        // Server epic error
                        err_handle = {
                            status: 500,
                            statusText: "Internal server error",
                        };
                    } else {
                        err_handle = {
                            status: 400,
                            statusText: JSONresponse.error.message,
                        };
                    }

                    JSONresponse = {
                        status_code: err_handle.status,
                        error: {
                            message: PF.fn.htmlEncode(CHV.fn.uploader.files[id].name.truncate_middle()) +
                                " - Server error (" +
                                err_handle.statusText +
                                ")",
                            code: err_handle.status,
                            context: "XMLHttpRequest",
                        },
                        status_txt: err_handle.statusText,
                    };

                    var error_key = Object.size(CHV.fn.uploader.results.error) + 1;

                    CHV.fn.uploader.results.error[error_key] = JSONresponse;
                }

                $queue_item.addClass(!is_error ? "completed" : "failed");

                if (
                    typeof JSONresponse.error !== "undefined" &&
                    typeof JSONresponse.error.message !== "undefined"
                ) {
                    $queue_item
                        .attr("rel", "tooltip")
                        .data("tiptip", "top")
                        .attr("title", JSONresponse.error.message);
                    PF.fn.bindtipTip($queue_item);
                }

                if (self.uploadThreads < CHV.obj.config.upload.threads && nextId) {
                    CHV.fn.uploader.upload($queue_item.next());
                    $(CHV.fn.uploader.selectors.root).addClass("queueHasResults");
                }

                if (self.uploadProcessedIds.length == Object.size(self.files)) {
                    CHV.fn.uploader.displayResults();
                }

                $(".done", $queue_item).fadeOut();
            }
        };

        this.files[id].xhr.open("POST", PF.obj.config.json_api, true);
        this.files[id].xhr.setRequestHeader("Accept", "application/json");
        this.files[id].xhr.send(form);
    },

    itemLoading: function ($queue_item) {
        PF.fn.loading.inline($(".progress", $queue_item), {
            color: "#FFF",
            size: "normal",
            center: true,
            position: "absolute",
            shadow: true,
        });
        $("[data-action=cancel], [data-action=edit]", $queue_item).hide();
    },

    displayResults: function () {
        CHV.fn.uploader.isUploading = false;

        var group_result = "[data-group=upload-result][data-result=%RESULT%]",
            result_types = ["error", "mixed", "success"],
            results = {};

        for (var i = 0; i < result_types.length; i++) {
            results[result_types[i]] = group_result.replace(
                "%RESULT%",
                result_types[i]
            );
        }

        if (Object.size(this.results.error) > 0) {
            var error_files = [];
            for (var i in this.results.error) {
                if (typeof this.results.error[i] !== "object") continue;
                error_files[i] = this.results.error[i].error.message;
            }
            if (error_files.length > 0) {
                $(this.selectors.failed_result).html(
                    "<li>" + error_files.join("</li><li>") + "</li>"
                );
            }
        } else {
            $(results.error, this.selectors.root).hide();
        }
        if (!window.opener &&
            CHV.obj.config.upload.moderation == 0 &&
            CHV.obj.config.upload.redirect_single_upload &&
            Object.size(this.results.success) == 1 &&
            Object.size(this.results.error) == 0
        ) {
            window.location.href = this.results.success[Object.keys(this.results.success)[0]]
                .image.path_viewer;
            return false;
        }

        $("[data-text=queue-progress]", this.selectors.root).text(100);
        $("[data-group=uploading]", this.selectors.root).hide();

        $(this.selectors.root)
            .removeClass("queueUploading queueHasResults")
            .addClass("queueCompleted");

        $(this.selectors.queue).addClass(
            this.selectors.queue_complete.substring(1)
        );

        // Append the embed codes
        if (
            Object.size(this.results.success) > 0 &&
            $("[data-group=upload-result] textarea", this.selectors.root).exists()
        ) {
            CHV.fn.fillEmbedCodes(
                this.results.success,
                CHV.fn.uploader.selectors.root,
                "val"
            );
        }

        if (
            Object.size(this.results.success) > 0 &&
            Object.size(this.results.error) > 0
        ) {
            $(results.mixed + ", " + results.success, this.selectors.root).show();
        } else if (Object.size(this.results.success) > 0) {
            $(results.success, this.selectors.root).show();
        } else if (Object.size(this.results.error) > 0) {
            $(results.error, this.selectors.root).show();
        }

        if ($(results.success, this.selectors.root).is(":visible")) {
            $(results.success, this.selectors.root)
                .find("[data-group^=user], [data-group=guest]")
                .hide();
            $(results.success, this.selectors.root)
                .find(
                    "[data-group=" + (PF.fn.is_user_logged() ? "user" : "guest") + "]"
                )
                .show();
            var firstKey = Object.keys(this.results.success)[0];
            if (typeof this.results.success[firstKey].image.album !== "undefined") {
                var albums = [];
                for (var key in this.results.success) {
                    var image = this.results.success[key].image;
                    if (
                        image.album &&
                        !!image.album.id_encoded &&
                        albums.indexOf(image.album.id_encoded) == -1
                    ) {
                        albums.push(image.album.id_encoded);
                    }
                }
                var targetAlbum = {
                    link: null,
                    text: null,
                };

                if (albums.length <= 1) {
                    targetAlbum.link = this.results.success[firstKey].image.album.url;
                    targetAlbum.text = this.results.success[firstKey].image.album.name;
                } else {
                    targetAlbum.link = this.results.success[
                        firstKey
                    ].image.user.url_albums;
                    targetAlbum.text = PF.fn._s(
                        "%s's Albums",
                        this.results.success[firstKey].image.user.name_short_html
                    );
                }

                $("[data-text=upload-target]", this.selectors.root).text(
                    targetAlbum.text
                );
                $("[data-link=upload-target]", this.selectors.root).attr(
                    "href",
                    targetAlbum.link
                );

                if (PF.fn.is_user_logged()) {
                    var show_user_stuff = albums.length > 0 ? "album" : "stream";
                    $(
                        "[data-group=user-" + show_user_stuff + "]",
                        this.selectors.root
                    ).show();
                }
            }
        }

        this.boxSizer();
        this.queueStatus = "done";

        // Detect plugin stuff
        if (
            window.opener &&
            typeof CHV.obj.opener.uploadPlugin[window.name] !== typeof undefined
        ) {
            $('[data-action="copy"]', this.selectors.root).remove();
            if (
                CHV.obj.opener.uploadPlugin[window.name].hasOwnProperty("autoInsert") &&
                CHV.obj.opener.uploadPlugin[window.name].autoInsert
            ) {
                var $target = $(
                    ':input[name="' +
                    CHV.obj.opener.uploadPlugin[window.name].autoInsert +
                    '"]',
                    CHV.fn.uploader.selectors.root
                );
                var value = $target.val();
                if (value) {
                    window.opener.postMessage({
                        id: window.name,
                        message: value,
                    },
                        "*"
                    );
                    window.close();
                    return;
                }
            }
        } else {
            $('[data-action="openerPostMessage"]', this.selectors.root).remove();
        }
    },
};

$.extend(CHV.fn.uploader, $.extend(true, {}, CHV.obj.uploaderReset));

CHV.fn.fillEmbedCodes = function (elements, parent, fn) {
    if (typeof fn == "undefined") {
        fn = "val";
    }
    var embed_tpl = CHV.fn.uploader.selectors.root == parent ? "embed_upload_tpl" : "embed_share_tpl";
    var hasVideo = false;
    $.each(elements, function (key, value) {
        if (typeof value == typeof undefined) return;
        var image = "id_encoded" in value ? value : value.image;
        if (!image.medium) {
            image.medium = {};
            var imageProp = [
                "filename",
                "name",
                "width",
                "height",
                "extension",
                "size",
                "size_formatted",
                "url",
            ];
            for (var i = 0; i < imageProp.length; i++) {
                image.medium[imageProp[i]] = image[imageProp[i]];
            }
            if(image.type === 'video') {
                image.medium.url = image.url_frame;
            }
        }
        var flatten_image = Object.flatten(image);
        $.each(CHV.obj[embed_tpl], function (key, value) {
            $.each(value.options, function (k, v) {
                var embed = v,
                    $embed = $("textarea[name=" + k + "]", parent),
                    template = v.template;
                if(typeof template === 'object' && template.hasOwnProperty(flatten_image["type"])
                ) {
                    template = template[flatten_image["type"]]
                }
                if(flatten_image["type"] === "video") {
                    hasVideo = true;
                }
                if(flatten_image["type"] !== "video") {
                    template = template.replaceAll("%URL_FRAME%", "");
                }
                for (var i in flatten_image) {
                    if (!flatten_image.hasOwnProperty(i)) {
                        continue;
                    }
                    template = template.replace(
                        new RegExp("%" + i.toUpperCase() + "%", "g"),
                        PF.fn.htmlEncode(PF.fn.htmlEncode(flatten_image[i]))
                    );
                }
                $embed[fn](
                    $embed.val() +
                    template +
                    ($embed.data("size") == "thumb" ? " " : "\n")
                );
            });
        });
    });
    $("option[value=frame-links]", parent).prop("hidden", !hasVideo);
    $.each(CHV.obj[embed_tpl], function (key, value) {
        $.each(value.options, function (k, v) {
            var $embed = $("textarea[name=" + k + "]", parent);
            $embed[fn]($.trim($embed.val()));
        });
    });
};

CHV.fn.resource_privacy_toggle = function (privacy) {
    CHV.obj.resource.privacy = privacy;
    if (!privacy) privacy = "public";
    $("[data-content=privacy-private]").hide();
    if (privacy !== "public") {
        $("[data-content=privacy-private]").show();
    }
};

CHV.fn.submit_create_album = function () {
    var $modal = $(PF.obj.modal.selectors.root);
    if ($("[name=form-album-name]", $modal).val() == "") {
        PF.fn.growl.call(PF.fn._s("You must enter the album name."));
        $("[name=form-album-name]", $modal).highlight();
        return false;
    }
    PF.obj.modal.form_data = {
        action: "create-album",
        type: "album",
        album: {
            parent_id: $("[name=form-album-parent-id]", $modal).val(),
            name: $("[name=form-album-name]", $modal).val(),
            description: $("[name=form-album-description]", $modal).val(),
            privacy: $("[name=form-privacy]", $modal).val(),
            password: $("[name=form-privacy]", $modal).val() == "password" ?
                $("[name=form-album-password]", $modal).val() : null,
            new: true,
        },
    };
    return true;
};
CHV.fn.complete_create_album = {
    success: function (XHR) {
        var response = XHR.responseJSON.album;
        window.location = response.url;
    },
    error: function (XHR) {
        var response = XHR.responseJSON;
        PF.fn.growl.call(PF.fn._s(response.error.message));
    },
};

// Upload edit (move to album or create new)
CHV.fn.submit_upload_edit = function () {
    var $modal = $(PF.obj.modal.selectors.root),
        new_album = false;

    if (
        $("[data-content=form-new-album]", $modal).is(":visible") &&
        $("[name=form-album-name]", $modal).val() == ""
    ) {
        PF.fn.growl.call(PF.fn._s("You must enter the album name."));
        $("[name=form-album-name]", $modal).highlight();
        return false;
    }

    if ($("[data-content=form-new-album]", $modal).is(":visible")) {
        new_album = true;
    }

    PF.obj.modal.form_data = {
        action: new_album ? "create-album" : "move",
        type: "images",
        album: {
            ids: $.map(CHV.fn.uploader.results.success, function (v) {
                return v.image.id_encoded;
            }),
            new: new_album,
        },
    };

    if (new_album) {
        PF.obj.modal.form_data.album.name = $(
            "[name=form-album-name]",
            $modal
        ).val();
        PF.obj.modal.form_data.album.description = $(
            "[name=form-album-description]",
            $modal
        ).val();
        PF.obj.modal.form_data.album.privacy = $(
            "[name=form-privacy]",
            $modal
        ).val();
        if (PF.obj.modal.form_data.album.privacy == "password") {
            PF.obj.modal.form_data.album.password = $(
                "[name=form-album-password]",
                $modal
            ).val();
        }
    } else {
        PF.obj.modal.form_data.album.id = $("[name=form-album-id]", $modal).val();
    }

    return true;
};
CHV.fn.complete_upload_edit = {
    success: function (XHR) {
        var response = XHR.responseJSON.album;
        window.location = response.url;
    },
    error: function (XHR) {
        var response = XHR.responseJSON;
        PF.fn.growl.call(PF.fn._s(response.error.message));
    },
};

// Image edit
CHV.fn.before_image_edit = function () {
    var $modal = $("[data-ajax-deferred='CHV.fn.complete_image_edit']");
    $("[data-content=form-new-album]", $modal).hide();
    $("#move-existing-album", $modal).show();
};
CHV.fn.submit_image_edit = function () {
    var $modal = $(PF.obj.modal.selectors.root),
        new_album = false;

    if (
        $("[data-content=form-new-album]", $modal).is(":visible") &&
        $("[name=form-album-name]", $modal).val() == ""
    ) {
        PF.fn.growl.call(PF.fn._s("You must enter the album name."));
        $("[name=form-album-name]", $modal).highlight();
        return false;
    }

    if ($("[data-content=form-new-album]", $modal).is(":visible")) {
        new_album = true;
    }

    PF.obj.modal.form_data = {
        action: "edit",
        edit: "image",
        editing: {
            id: CHV.obj.resource.id,
            category_id: $("[name=form-category-id]", $modal).val() || null,
            title: $("[name=form-image-title]", $modal).val() || null,
            description: $("[name=form-image-description]", $modal).val() || null,
            nsfw: $("[name=form-nsfw]", $modal).prop("checked") ? 1 : 0,
            new_album: new_album,
        },
    };

    if (new_album) {
        PF.obj.modal.form_data.editing.album_privacy = $(
            "[name=form-privacy]",
            $modal
        ).val();
        if (PF.obj.modal.form_data.editing.album_privacy == "password") {
            PF.obj.modal.form_data.editing.album_password = $(
                "[name=form-album-password]",
                $modal
            ).val();
        }
        PF.obj.modal.form_data.editing.album_name = $(
            "[name=form-album-name]",
            $modal
        ).val();
        PF.obj.modal.form_data.editing.album_description = $(
            "[name=form-album-description]",
            $modal
        ).val();
    } else {
        PF.obj.modal.form_data.editing.album_id = $(
            "[name=form-album-id]",
            $modal
        ).val();
    }

    return true;
};
CHV.fn.complete_image_edit = {
    success: function (XHR) {
        var response = XHR.responseJSON.image;

        if (!response.album.id_encoded) response.album.id_encoded = "";

        // Detect album change
        if (CHV.obj.image_viewer.album.id_encoded !== response.album.id_encoded) {
            CHV.obj.image_viewer.album.id_encoded = response.album.id_encoded;

            var slice = {
                html: response.album.slice && response.album.slice.html ?
                    response.album.slice.html : null,
                prev: response.album.slice && response.album.slice.prev ?
                    response.album.slice.prev : null,
                next: response.album.slice && response.album.slice.next ?
                    response.album.slice.next : null,
            };

            $("[data-content=album-slice]").html(slice.html);
            $("[data-content=album-panel-title]")[slice.html ? "show" : "hide"]();

            $("a[data-action=prev]").attr("href", slice.prev);
            $("a[data-action=next]").attr("href", slice.next);

            $("a[data-action]", ".image-viewer-navigation").each(function () {
                $(this)[
                    typeof $(this).attr("href") == "undefined" ?
                        "addClass" :
                        "removeClass"
                ]("hidden");
            });
        }

        CHV.fn.resource_privacy_toggle(response.album.privacy);

        $.each(["description", "title"], function (i, v) {
            var $obj = $("[data-text=image-" + v + "]");
            $obj.html(PF.fn.nl2br(PF.fn.htmlEncode(response[v])));
            if ($obj.html() !== "") {
                $obj.show();
            }
        });

        CHV.fn.common.updateDoctitle(response.title);

        PF.fn.growl.expirable(PF.fn._s("File edited successfully."));

        // Add album to modals
        CHV.fn.list_editor.addAlbumtoModals(response.album);

        // Reset modal
        var $modal = $("[data-submit-fn='CHV.fn.submit_image_edit']");

        $.each(["description", "name", "password"], function (i, v) {
            var $input = $("[name=form-album-" + v + "]", $modal);
            if ($input.is("textarea")) {
                $input.val("").html("");
            } else {
                $input.val("").attr("value", "");
            }
        });
        $("[name=form-privacy] option", $modal).each(function () {
            $(this).removeAttr("selected");
        });
        $("[data-combo-value=password]", $modal).hide();

        // Select the album
        $("[name=form-album-id]", $modal).find("option").removeAttr("selected");
        $("[name=form-album-id]", $modal)
            .find("[value=" + response.album.id_encoded + "]")
            .attr("selected", true);
    },
};

CHV.fn.albumEdit = {
    before: function () {
        var modal_source = "[data-before-fn='CHV.fn.albumEdit.before']";
        $("[data-action=album-switch]", modal_source).remove();
        var $enableCta = $(CHV.fn.ctaForm.selectors.enable, modal_source);
        CHV.fn.ctaForm.destroy();
        if(CHV.fn.ctaForm.enable) {
            $enableCta.prop("checked", true).trigger("change");
        }
    },
    load: function() {
        var $enableCta = $(CHV.fn.ctaForm.selectors.enable, PF.obj.modal.selectors.root);
        if($enableCta.is(":checked")) {
            $enableCta.prop("checked", true).trigger("change");
        }
    },
    submit: function() {
        var $modal = $(PF.obj.modal.selectors.root);
        if (!$("[name=form-album-name]", $modal).val()) {
            PF.fn.growl.call(PF.fn._s("You must enter the album name."));
            $("[name=form-album-name]", $modal).highlight();
            return false;
        }
        PF.obj.modal.form_data = {
            action: "edit",
            edit: "album",
            editing: {
                id: CHV.obj.resource.id,
                name: $("[name=form-album-name]", $modal).val(),
                privacy: $("[name=form-privacy]", $modal).val(),
                description: $("[name=form-album-description]", $modal).val(),
                cta_enable: + CHV.fn.ctaForm.enable,
                cta: JSON.stringify(CHV.fn.ctaForm.array),
            },
        };
        if (PF.obj.modal.form_data.editing.privacy == "password") {
            PF.obj.modal.form_data.editing.password = $(
                "[name=form-album-password]",
                $modal
            ).val();
        }

        return true;
    },
    complete: {
        success: function (XHR) {
            var album = XHR.responseJSON.album;
            $("[data-text=album-name]").html(PF.fn.htmlEncode(album.name));
            $("[data-text=album-description]").html(
                PF.fn.htmlEncode(album.description)
            );
            CHV.fn.resource_privacy_toggle(album.privacy);
            var stock = CHV.obj.resource.type;
            CHV.obj.resource.type = null;
            CHV.fn.list_editor.updateItem($(PF.obj.listing.selectors.list_item, PF.obj.listing.selectors.content_listing_visible), XHR.responseJSON);
            CHV.obj.resource.type = stock;
            $("[data-modal]").each(function () {
                $("option[value=" + album.id_encoded + "]", this).text(
                    album.name +
                    (album.privacy !== "public" ? " (" + PF.fn._s("private") + ")" : "")
                );
            });
            CHV.fn.common.updateDoctitle(album.name);
            CHV.fn.ctaButtons.render(album.cta_html);
            PF.fn.growl.expirable(PF.fn._s("The content has been edited."));
        },
    },
};

// Category edit
CHV.fn.category = {
    formFields: ["id", "name", "url_key", "description"],
    validateForm: function (id) {
        var modal = PF.obj.modal.selectors.root,
            submit = true,
            used_url_key = false;

        if (!CHV.fn.common.validateForm(modal)) {
            return false;
        }

        if (
            /^[-\w]+$/.test($("[name=form-category-url_key]", modal).val()) === false
        ) {
            PF.fn.growl.call(PF.fn._s("Invalid URL key."));
            $("[name=form-category-url_key]", modal).highlight();
            return false;
        }

        if (Object.size(CHV.obj.categories) > 0) {
            $.each(CHV.obj.categories, function (i, v) {
                if (typeof id !== "undefined" && v.id == id) return true;
                if (v.url_key == $("[name=form-category-url_key]", modal).val()) {
                    used_url_key = true;
                    return false;
                }
            });
        }
        if (used_url_key) {
            PF.fn.growl.call(PF.fn._s("Category URL key already being used."));
            $("[name=form-category-url_key]", modal).highlight();
            return false;
        }

        return true;
    },
    edit: {
        before: function (e) {
            var $this = $(e.target),
                id = $this.data("category-id"),
                category = CHV.obj.categories[id],
                modal_source = "[data-modal=" + $this.data("target") + "]";
            $.each(CHV.fn.category.formFields, function (i, v) {
                var i = "form-category-" + v,
                    v = category[v],
                    $input = $("[name=" + i + "]", modal_source);
                if ($input.is("textarea")) {
                    $input.html(PF.fn.htmlEncode(v));
                } else {
                    $input.attr("value", v);
                }
            });
        },
        submit: function () {
            var modal = PF.obj.modal.selectors.root,
                id = $("[name=form-category-id]", modal).val();

            if (!CHV.fn.category.validateForm(id)) {
                return false;
            }

            PF.obj.modal.form_data = {
                action: "edit",
                edit: "category",
                editing: {},
            };
            $.each(CHV.fn.category.formFields, function (i, v) {
                PF.obj.modal.form_data.editing[v] = $(
                    "[name=form-category-" + v + "]",
                    modal
                ).val();
            });

            return true;
        },
        complete: {
            success: function (XHR) {
                var category = XHR.responseJSON.category,
                    parent =
                        "[data-content=category][data-category-id=" + category.id + "]";

                $.each(category, function (i, v) {
                    $("[data-content=category-" + i + "]", parent).html(
                        PF.fn.htmlEncode(v)
                    );
                });
                $("[data-link=category-url]").attr("href", category.url);
                CHV.obj.categories[category.id] = category;
                PF.fn.growl.expirable(PF.fn._s("The content has been edited."))
            },
        },
    },
    delete: {
        before: function (e) {
            var $this = $(e.target),
                id = $this.data("category-id"),
                category = CHV.obj.categories[id];
            $this.attr(
                "data-confirm",
                $this.attr("data-confirm").replace("%s", '"' + category.name + '"')
            );
        },
        submit: function (id) {
            PF.obj.modal.form_data = {
                action: "delete",
                delete: "category",
                deleting: {
                    id: id,
                },
            };
            return true;
        },
        complete: {
            success: function (XHR) {
                PF.fn.growl.expirable(PF.fn._s("Category successfully deleted."));
                var id = XHR.responseJSON.request.deleting.id;
                $("[data-content=category][data-category-id=" + id + "]").remove();
                delete CHV.obj.categories[id];
            },
        },
    },
    add: {
        submit: function () {
            var modal = PF.obj.modal.selectors.root;

            if (!CHV.fn.category.validateForm()) {
                return false;
            }

            PF.obj.modal.form_data = {
                action: "add-category",
                category: {},
            };
            $.each(CHV.fn.category.formFields, function (i, v) {
                if (v == "id") return;
                PF.obj.modal.form_data.category[v] = $(
                    "[name=form-category-" + v + "]",
                    modal
                ).val();
            });

            return true;
        },
        complete: {
            success: function (XHR) {
                var category = XHR.responseJSON.category,
                    list = "[data-content=dashboard-categories-list]",
                    html = $("[data-content=category-dashboard-template]").html(),
                    replaces = {};

                $.each(category, function (i, v) {
                    html = html.replace(
                        new RegExp("%" + i.toUpperCase() + "%", "g"),
                        v ? v : ""
                    );
                });

                $(list).append(html);

                if (Object.size(CHV.obj.categories) == 0) {
                    CHV.obj.categories = {};
                }
                CHV.obj.categories[category.id] = category;

                PF.fn.growl.call(
                    PF.fn._s("Category %s added.", '"' + category.name + '"')
                );
            },
        },
    },
};

// IP ban edit
CHV.fn.ip_ban = {
    formFields: ["id", "ip", "expires", "message"],
    validateForm: function (id) {
        var modal = PF.obj.modal.selectors.root,
            submit = true,
            already_banned = false,
            ip = $("[name=form-ip_ban-ip]", modal).val();

        if (!CHV.fn.common.validateForm(modal)) {
            return false;
        }

        if (
            $("[name=form-ip_ban-expires]", modal).val() !== "" &&
            /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(
                $("[name=form-ip_ban-expires]", modal).val()
            ) == false
        ) {
            PF.fn.growl.call(PF.fn._s("Invalid expiration date."));
            $("[name=form-ip_ban-expires]", modal).highlight();
            return false;
        }

        if (Object.size(CHV.obj.ip_bans) > 0) {
            $.each(CHV.obj.ip_bans, function (i, v) {
                if (typeof id !== "undefined" && v.id == id) return true;
                if (v.ip == ip) {
                    already_banned = true;
                    return false;
                }
            });
        }
        if (already_banned) {
            PF.fn.growl.call(PF.fn._s("IP %s already banned.", ip));
            $("[name=form-ip_ban-ip]", modal).highlight();
            return false;
        }

        return true;
    },

    add: {
        submit: function () {
            var modal = PF.obj.modal.selectors.root;

            if (!CHV.fn.ip_ban.validateForm()) {
                return false;
            }

            PF.obj.modal.form_data = {
                action: "add-ip_ban",
                ip_ban: {},
            };
            $.each(CHV.fn.ip_ban.formFields, function (i, v) {
                if (v == "id") return;
                PF.obj.modal.form_data.ip_ban[v] = $(
                    "[name=form-ip_ban-" + v + "]",
                    modal
                ).val();
            });

            return true;
        },
        complete: {
            success: function (XHR) {
                var ip_ban = XHR.responseJSON.ip_ban,
                    list = "[data-content=dashboard-ip_bans-list]",
                    html = $("[data-content=ip_ban-dashboard-template]").html(),
                    replaces = {};

                if (typeof html !== "undefined") {
                    $.each(ip_ban, function (i, v) {
                        html = html.replace(
                            new RegExp("%" + i.toUpperCase() + "%", "g"),
                            v ? v : ""
                        );
                    });
                    $(list).append(html);
                }
                if (Object.size(CHV.obj.ip_bans) == 0) {
                    CHV.obj.ip_bans = {};
                }
                CHV.obj.ip_bans[ip_ban.id] = ip_ban;
                $("[data-content=ban_ip]").addClass("hidden");
                $("[data-content=banned_ip]").removeClass("hidden");
                PF.fn.growl.call(PF.fn._s("IP %s banned.", ip_ban.ip));
            },
            error: function (XHR) {
                // experimental
                var error = XHR.responseJSON.error;
                PF.fn.growl.call(PF.fn._s(error.message));
            },
        },
    },

    edit: {
        before: function (e) {
            var $this = $(e.target),
                id = $this.data("ip_ban-id"),
                target = CHV.obj.ip_bans[id],
                modal_source = "[data-modal=" + $this.data("target") + "]";
            $.each(CHV.fn.ip_ban.formFields, function (i, v) {
                var i = "form-ip_ban-" + v,
                    v = target[v],
                    $input = $("[name=" + i + "]", modal_source);
                if ($input.is("textarea")) {
                    $input.html(PF.fn.htmlEncode(v));
                } else {
                    $input.attr("value", v);
                }
            });
        },
        submit: function () {
            var modal = PF.obj.modal.selectors.root,
                id = $("[name=form-ip_ban-id]", modal).val();

            if (!CHV.fn.ip_ban.validateForm(id)) {
                return false;
            }

            PF.obj.modal.form_data = {
                action: "edit",
                edit: "ip_ban",
                editing: {},
            };
            $.each(CHV.fn.ip_ban.formFields, function (i, v) {
                PF.obj.modal.form_data.editing[v] = $(
                    "[name=form-ip_ban-" + v + "]",
                    modal
                ).val();
            });

            return true;
        },
        complete: {
            success: function (XHR) {
                var ip_ban = XHR.responseJSON.ip_ban,
                    parent = "[data-content=ip_ban][data-ip_ban-id=" + ip_ban.id + "]";

                $.each(ip_ban, function (i, v) {
                    $("[data-content=ip_ban-" + i + "]", parent).html(
                        PF.fn.htmlEncode(v)
                    );
                });
                CHV.obj.ip_bans[ip_ban.id] = ip_ban;
            },
            error: function (XHR) {
                var error = XHR.responseJSON.error;
                PF.fn.growl.call(PF.fn._s(error.message));
            },
        },
    },

    delete: {
        before: function (e) {
            var $this = $(e.target),
                id = $this.data("ip_ban-id"),
                ip_ban = CHV.obj.ip_bans[id];
            $this.attr(
                "data-confirm",
                $this.attr("data-confirm").replace("%s", ip_ban.ip)
            );
        },
        submit: function (id) {
            PF.obj.modal.form_data = {
                action: "delete",
                delete: "ip_ban",
                deleting: {
                    id: id,
                },
            };
            return true;
        },
        complete: {
            success: function (XHR) {
                PF.fn.growl.expirable(PF.fn._s("IP ban successfully deleted."));
                var id = XHR.responseJSON.request.deleting.id;
                $("[data-content=ip_ban][data-ip_ban-id=" + id + "]").remove();
                delete CHV.obj.ip_bans[id];
            },
        },
    },
};

// Storage edit
CHV.fn.storage = {
    formFields: [
        "id",
        "name",
        "api_id",
        "bucket",
        "server",
        "service",
        "capacity",
        "region",
        "key",
        "secret",
        "url",
        "account_id",
        "account_name",
        "type_chain"
    ],
    chain: [
        "other",
        "document",
        "audio",
        "video",
        "image",
    ],
    calling: false,
    validateForm: function () {
        var modal = PF.obj.modal.selectors.root,
            id = $("[name=form-storage-id]", modal).val(),
            submit = true;

        $.each($(":input", modal), function (i, v) {
            if ($(this).is(":hidden")) {
                if ($(this).attr("required")) {
                    $(this).removeAttr("required").attr("data-required", 1);
                }
            } else {
                if ($(this).attr("data-required") == 1) {
                    $(this).attr("required", "required");
                }
            }
            if (
                $(this).is(":visible") &&
                $(this).val() == "" &&
                $(this).attr("required")
            ) {
                $(this).highlight();
                submit = false;
            }
        });

        if (!submit) {
            PF.fn.growl.call(PF.fn._s("Please fill all the required fields."));
            return false;
        }

        // Validate storage capacity
        var $storage_capacity = $("[name=form-storage-capacity]", modal),
            storage_capacity = $storage_capacity.val(),
            capacity_error_msg;

        if (storage_capacity !== "") {
            if (
                /^[\d\.]+\s*[A-Za-z]{2}$/.test(storage_capacity) == false ||
                typeof storage_capacity.getBytes() == "undefined"
            ) {
                capacity_error_msg = PF.fn._s(
                    "Invalid storage capacity value. Make sure to use a valid format."
                );
            } else if (
                typeof CHV.obj.storages[id] !== "undefined" &&
                storage_capacity.getBytes() < CHV.obj.storages[id].space_used
            ) {
                capacity_error_msg = PF.fn._s(
                    "Storage capacity can't be lower than its current usage (%s).",
                    CHV.obj.storages[id].space_used.formatBytes()
                );
            }
            if (capacity_error_msg) {
                PF.fn.growl.call(capacity_error_msg);
                $storage_capacity.highlight();
                return false;
            }
        }
        if (
            /^https?:\/\/.+$/.test($("[name=form-storage-url]", modal).val()) == false
        ) {
            PF.fn.growl.call(PF.fn._s("Invalid URL."));
            $("[name=form-storage-url]", modal).highlight();
            return false;
        }
        return true;
    },
    toggleHttps: function (id) {
        this.toggleBool(id, "https");
    },
    toggleActive: function (id) {
        this.toggleBool(id, "active");
    },
    toggleBool: function (id, string) {
        if (this.calling) return;

        this.calling = true;

        var $root = $("[data-storage-id=" + id + "]"),
            $parent = $("[data-content=storage-" + string + "]", $root),
            $el = $("[data-checkbox]", $parent),
            checked = CHV.obj.storages[id]["is_" + string],
            toggle = checked == 0 ? 1 : 0,
            data = {
                action: "edit",
                edit: "storage",
                editing: {
                    id: id,
                },
            };
        data.editing["is_" + string] = toggle;
        if (string == "https") {
            data.editing.url = CHV.obj.storages[id].url;
        }

        PF.fn.loading.fullscreen();

        $.ajax({
            type: "POST",
            data: data,
        }).always(function (data, status, XHR) {
            CHV.fn.storage.calling = false;
            PF.fn.loading.destroy("fullscreen");

            if (typeof data.storage == "undefined") {
                PF.fn.growl.call(data.responseJSON.error.message);
                return;
            }

            var storage = data.storage;
            CHV.obj.storages[storage.id] = storage;

            PF.fn.growl.expirable(PF.fn._s("Storage successfully edited."));

            switch (string) {
                case "https":
                    $("[data-content=storage-url]", $root).html(storage.url);
                    break;
            }

            CHV.fn.storage.toggleBoolDisplay($el, toggle);
        });
    },
    edit: {
        before: function (e) {
            var $this = $(e.target),
                id = $this.data("storage-id"),
                storage = CHV.obj.storages[id],
                modal_source = "[data-modal=" + $this.data("target") + "]",
                combo = "[data-combo-value~=" + storage["api_id"] + "]";

            $.each(CHV.fn.storage.formFields, function (i, v) {
                var i = "form-storage-" + v,
                    v = storage[v],
                    $combo_input = $(combo + " [name=" + i + "]", modal_source),
                    $global_input = $("[name=" + i + "]", modal_source),
                    $input = $combo_input.exists() ? $combo_input : $global_input;
                if ($input.is("textarea")) {
                    $input.html(PF.fn.htmlEncode(v));
                } else if ($input.is("select")) {
                    $("option", $input).removeAttr("selected");
                    $("option", $input).each(function () {
                        if ($(this).attr("value") == v) {
                            $(this).attr("selected", "selected");
                            return false;
                        }
                    });
                } else {
                    if (
                        $input.is("[name=form-storage-capacity]") &&
                        typeof v !== "undefined" &&
                        v > 0
                    ) {
                        v = String(v).formatBytes(2);
                    }
                    $input.attr("value", v);
                }
                if(i === "form-storage-type_chain") {
                    let chain = (parseInt(v) >>> 0)
                        .toString(2)
                        .paddingLeft(
                            "0".repeat(CHV.fn.storage.chain.length)
                        )
                        .split("");
                    CHV.fn.storage.chain.forEach(function(key, i) {
                        $('#storage_type_enable_'+key, modal_source)
                            .removeAttr("checked")
                            .attr("checked", chain[i] == 1);
                    });
                }
            });
            $("[data-combo-value]").addClass("soft-hidden");
            $(combo).removeClass("soft-hidden");
        },
        submit: function () {
            var modal = PF.obj.modal.selectors.root,
                id = $("[name=form-storage-id]", modal).val(),
                used_url_key = false;

            if (!CHV.fn.storage.validateForm()) {
                return false;
            }
            PF.obj.modal.form_data = {
                action: "edit",
                edit: "storage",
                editing: {},
            };
            $.each(CHV.fn.storage.formFields, function (i, v) {
                var sel;
                sel = "[name=form-storage-" + v + "]";
                if ($(sel, modal).attr("type") !== "hidden") {
                    sel += ":visible";
                }
                PF.obj.modal.form_data.editing[v] = $(sel, modal).val();
            });
            let chain = CHV.fn.storage.chain.map(function(key) {
                return $('#storage_type_enable_'+key, modal).prop("checked") ? 1 : 0;
            });
            PF.obj.modal.form_data.editing.type_chain = parseInt(chain.join(""), 2);

            return true;
        },
        complete: {
            success: function (XHR) {
                var storage = XHR.responseJSON.storage,
                    parent = "[data-content=storage][data-storage-id=" + storage.id + "]",
                    $el = $("[data-action=toggle-storage-https]", parent);
                $.each(storage, function (i, v) {
                    $("[data-content=storage-" + i + "]", parent).html(
                        PF.fn.htmlEncode(v)
                    );
                });
                CHV.obj.storages[storage.id] = storage;
                CHV.fn.storage.toggleBoolDisplay($el, storage["is_https"] == 1);
            },
            error: function (XHR) {
                var response = XHR.responseJSON,
                    message = response.error.message;
                PF.fn.growl.call(message);
            },
        },
    },
    add: {
        submit: function () {
            if (!CHV.fn.storage.validateForm()) {
                return false;
            }
            var modal = PF.obj.modal.selectors.root;

            PF.obj.modal.form_data = {
                action: "add-storage",
                storage: {},
            };
            $.each(CHV.fn.storage.formFields, function (i, v) {
                if (v == "id") return;
                var sel;
                sel = "[name=form-storage-" + v + "]";
                if ($(sel, modal).attr("type") !== "hidden") {
                    sel += ":visible";
                }
                PF.obj.modal.form_data.storage[v] = $(sel, modal).val();
            });

            return true;
        },
        complete: {
            success: function (XHR) {
                var storage = XHR.responseJSON.storage,
                    list = "[data-content=dashboard-storages-list]",
                    html = $("[data-content=storage-dashboard-template]").html(),
                    replaces = {};

                $.each(storage, function (i, v) {
                    var upper = i.toUpperCase();
                    if (i == "is_https" || i == "is_active") {
                        var v = CHV.obj.storageTemplate.icon
                            .replace("%TITLE%", CHV.obj.storageTemplate.messages[i])
                            .replace("%ICON%", CHV.obj.storageTemplate.checkboxes[v])
                            .replace("%PROP%", i.replace("is_", ""));
                    }
                    html = html.replace(new RegExp("%" + upper + "%", "g"), v ? v : "");
                });

                $(list).append(html);

                PF.fn.bindtipTip($("[data-storage-id=" + storage.id + "]"));

                if (CHV.obj.storages.length == 0) {
                    CHV.obj.storages = {};
                }
                CHV.obj.storages[storage.id] = storage;
            },
            error: function (XHR) {
                var response = XHR.responseJSON,
                    message = response.error.message;
                PF.fn.growl.call(message);
            },
        },
    },
    toggleBoolDisplay: function ($el, toggle) {
        var icons = {
            0: $el.data("unchecked-icon"),
            1: $el.data("checked-icon"),
        };
        $el.removeClass(icons[0] + " " + icons[1]).addClass(icons[toggle ? 1 : 0]);
    },
};

CHV.fn.common = {
    validateForm: function (modal) {
        if (typeof modal == "undefined") {
            var modal = PF.obj.modal.selectors.root;
        }

        var submit = true;

        $.each($(":input:visible", modal), function (i, v) {
            if ($(this).val() == "" && $(this).attr("required")) {
                $(this).highlight();
                submit = false;
            }
        });
        if (!submit) {
            PF.fn.growl.call(PF.fn._s("Please fill all the required fields."));
            return false;
        }

        return true;
    },
    updateDoctitle: function (pre_doctitle) {
        if (typeof CHV.obj.page_info !== typeof undefined) {
            CHV.obj.page_info.pre_doctitle = pre_doctitle;
            CHV.obj.page_info.doctitle =
                CHV.obj.page_info.pre_doctitle + CHV.obj.page_info.pos_doctitle;
            document.title = CHV.obj.page_info.doctitle;
        }
    },
};

CHV.fn.user = {
    add: {
        submit: function () {
            var $modal = $(PF.obj.modal.selectors.root),
                submit = true;

            $.each($(":input", $modal), function (i, v) {
                if ($(this).val() == "" && $(this).attr("required")) {
                    $(this).highlight();
                    submit = false;
                }
            });

            if (!submit) {
                PF.fn.growl.call(PF.fn._s("Please fill all the required fields."));
                return false;
            }

            PF.obj.modal.form_data = {
                action: "add-user",
                user: {
                    username: $("[name=form-username]", $modal).val(),
                    email: $("[name=form-email]", $modal).val(),
                    password: $("[name=form-password]", $modal).val(),
                    role: $("[name=form-role]", $modal).val(),
                },
            };

            return true;
        },
        complete: {
            success: function (XHR) {
                var response = XHR.responseJSON;
                PF.fn.growl.expirable(PF.fn._s("%s added successfully.", PF.fn._n("User", "Users", 1)));
            },
            error: function (XHR) {
                var response = XHR.responseJSON;
                PF.fn.growl.call(PF.fn._s(response.error.message));
            },
        },
    },
    delete: {
        submit: function () {
            PF.obj.modal.form_data = {
                action: "delete",
                delete: "user",
                owner: CHV.obj.resource.user.id,
                deleting: CHV.obj.resource.user,
            };
            return true;
        },
    },
    ban: {
        submit: function () {
            PF.obj.modal.form_data = {
                action: "ban",
                ban: "user",
                banning: CHV.obj.resource.user.id,
            };
            return true;
        },
        success: function () {

        }
    }
};

CHV.fn.submit_resource_approve = function () {
    PF.obj.modal.form_data = {
        action: "approve",
        approve: CHV.obj.resource.type,
        from: "resource",
        owner: typeof CHV.obj.resource.user !== "undefined" ?
            CHV.obj.resource.user.id : null,
        approving: CHV.obj.resource,
    };
    return true;
};
CHV.fn.complete_resource_approve = {
    success: function (XHR) {
        var response = XHR.responseJSON;
        $("body").fadeOut("normal", function () {
            redir = CHV.obj.resource.url;
            window.location = redir;
        });
    },
};

CHV.fn.submit_resource_delete = function () {
    PF.obj.modal.form_data = {
        action: "delete",
        delete: CHV.obj.resource.type,
        from: "resource",
        owner: typeof CHV.obj.resource.user !== "undefined" ?
            CHV.obj.resource.user.id : null,
        deleting: CHV.obj.resource,
    };
    return true;
};
CHV.fn.complete_resource_delete = {
    success: function (XHR) {
        var response = XHR.responseJSON;
        $("body").fadeOut("normal", function () {
            var redir;
            if (
                CHV.obj.resource.type == "album" ||
                CHV.obj.resource.type == "image"
            ) {
                redir = CHV.obj.resource.parent_url;
            } else {
                redir = CHV.obj.resource.user ?
                    CHV.obj.resource.user.url :
                    CHV.obj.resource.url;
            }
            if (typeof redir !== "undefined") {
                window.location = redir.replace(/\/?$/, '/') + "?deleted";
            }
        });
    },
};

CHV.fn.list_editor = {
    blink: function ($target) {
        $target.addClass('ui-selecting');
        setTimeout(function () {
            $target.removeClass('ui-selecting');
        }, 200);
    },
    selectionCount: function () {
        var $content_listing = $(PF.obj.listing.selectors.content_listing);

        $content_listing.each(function () {
            var $listing_options = $(
                "[data-content=pop-selection]",
                "[data-content=list-selection][data-tab=" + $(this).attr("id") + "]"
            ),
                selection_count = $(
                    PF.obj.listing.selectors.list_item + ".selected",
                    this
                ).length;
            all_count = $(PF.obj.listing.selectors.list_item, this).length;
            $listing_options.toggleClass("disabled", selection_count == 0);
            $("[data-text=selection-count]", $listing_options).text(
                selection_count > 0 ? selection_count : ""
            );

            // Sensitive display
            if ($content_listing.data("list") == "images" && selection_count > 0) {
                var has_sfw =
                    $(
                        PF.obj.listing.selectors.list_item + ".selected[data-flag=safe]",
                        this
                    ).length > 0,
                    has_nsfw =
                        $(
                            PF.obj.listing.selectors.list_item +
                            ".selected[data-flag=unsafe]",
                            this
                        ).length > 0;
                $("[data-action=flag-safe]", $listing_options)[
                    (has_nsfw ? "remove" : "add") + "Class"
                ]("hidden");
                $("[data-action=flag-unsafe]", $listing_options)[
                    (has_sfw ? "remove" : "add") + "Class"
                ]("hidden");
            }

            if ($(this).is(":visible")) {
                $("body").toggleClass('--has-selection', selection_count > 0);
                CHV.fn.list_editor.listMassActionSet(
                    all_count == selection_count ? "clear" : "select"
                );
            }
        });
    },

    // Remove (delete or move) items from list
    removeFromList: function ($target, msg) {
        if (typeof $target == "undefined") return;

        var $target = $target instanceof jQuery == false ? $($target) : $target,
            $content_listing = $(PF.obj.listing.selectors.content_listing_visible),
            target_size = $target.length;

        $target.fadeOut("fast"); // Promise

        // Update counts
        var type = $target.first().data("type"),
            new_count =
                parseInt($("[data-text=" + type + "-count]").text()) - target_size;

        CHV.fn.list_editor.updateUserCounters(
            $target.first().data("type"),
            target_size,
            "-"
        );

        $target.promise().done(function () {
            $(document).removeClass(
                CHV.fn.listingViewer.selectors.bodyShown.substr(1)
            );

            // Get count related to each list
            var affected_content_lists = {};
            $target.each(function () {
                $("[data-id=" + $(this).data("id") + "]").each(function () {
                    var list_id = $(this)
                        .closest(PF.obj.listing.selectors.content_listing)
                        .attr("id");

                    if (!affected_content_lists[list_id]) {
                        affected_content_lists[list_id] = 0;
                    }
                    affected_content_lists[list_id] += 1;
                });
            });

            if (target_size == 1) {
                $("[data-id=" + $(this).data("id") + "]").remove();
            } else {
                $target.each(function () {
                    $("[data-id=" + $(this).data("id") + "]").remove();
                });
            }

            PF.fn.listing.columnizerQueue();
            PF.fn.listing.refresh();

            CHV.fn.list_editor.selectionCount();

            if (typeof msg !== "undefined" && typeof msg == "string") {
                PF.fn.growl.expirable(msg);
            }
            if (!$(
                PF.obj.listing.selectors.content_listing_pagination,
                $content_listing
            ).exists() &&
                $(PF.obj.listing.selectors.list_item, $content_listing).length == 0
            ) {
                new_count = 0;
            }

            // On zero add the empty template
            if (new_count == 0) {
                $content_listing.html(PF.obj.listing.template.empty);
                // Reset ajaxed status of all
                $(
                    PF.obj.listing.selectors.content_listing +
                    ":not(" +
                    PF.obj.listing.selectors.content_listing_visible +
                    ")"
                ).data({
                    empty: null,
                    load: "ajax",
                });
                $(
                    "[data-content=list-selection][data-tab=" +
                    $content_listing.attr("id") +
                    "]"
                ).addClass("disabled");
            } else {
                // Count isn't zero.. But the view?
                if (
                    $(PF.obj.listing.selectors.list_item, $content_listing).length == 0
                ) {
                    $(PF.obj.listing.selectors.pad_content).height(0);
                    if ($("[data-action=load-more]", $content_listing).exists()) {
                        $(PF.obj.listing.selectors.content_listing_visible).data("page", 0);
                        $("[data-action=load-more]", $content_listing).click();
                        PF.obj.listing.recolumnize = true;
                        return;
                    }
                    var $pagNext = $("[data-pagination=next]", $content_listing);
                    if ($pagNext.exists()) {
                        var hrefNext = $pagNext.attr("href");
                        var params = PF.fn.deparam(hrefNext);
                        if ("page" in params && params.page > 1) {
                            hrefNext = hrefNext.changeURLParameterValue(
                                "page",
                                params.page - 1
                            );
                        }
                        window.location = hrefNext;
                        return;
                    }
                }
            }
        });
    },

    deleteFromList: function ($target) {
        if (typeof growl == "undefined") {
            var growl = true;
        }
        var $target = $target instanceof jQuery == false ? $($target) : $target;
        this.removeFromList(
            $target,
            growl ? PF.fn._s("The content has been deleted.") : null
        );
    },

    moveFromList: function ($target, growl) {
        if (typeof growl == "undefined") {
            var growl = true;
        }
        var $target = $target instanceof jQuery == false ? $($target) : $target;
        this.removeFromList(
            $target,
            growl ? PF.fn._s("The content has been moved.") : null
        );
    },

    toggleSelectItem: function ($list_item, select) {
        if (typeof select !== "boolean") {
            var select = !$list_item.hasClass('selected');
        }

        var $target = $(".viewer").is(":visible") ?
            $("[data-type=image][data-id=" + $list_item.attr("data-id") + "]") :
            $list_item;
        var $icon = $("[data-action=select] .btn-icon", $target);
        var add_class, remove_class, label_text;

        if ($target.hasClass('unselect')) {
            return;
        }
        $target.addClass("unselect");
        if (!select) {
            $target.removeClass("selected ui-selected");
            add_class = $icon.data("icon-unselected");
            remove_class = $icon.data("icon-selected");
            label_text = PF.fn._s("Select");
        } else {
            if(Boolean(window.navigator.vibrate)) {
                window.navigator.vibrate([15, 125, 25]);
            }
            $target.addClass("selected");
            add_class = $icon.data("icon-selected");
            remove_class = $icon.data("icon-unselected");
            label_text = PF.fn._s("Unselect");
        }
        $icon.removeClass(remove_class).addClass(add_class);
        setTimeout(function () {
            $target.removeClass("unselect")
        }, 350)
        $("[data-action=select] .label", $target).text(label_text);
        CHV.fn.list_editor.selectionCount();
    },
    selectItem: function ($list_item) {
        this.toggleSelectItem($list_item, true);
    },
    unselectItem: function ($list_item) {
        this.toggleSelectItem($list_item, false);
        $list_item.removeClass('selected')
    },
    selectAll: function (e) {
        this.selectItem($(PF.obj.listing.selectors.list_item + ":visible:not(.selected)"));
        this.listMassActionSet("clear");
        e.stopPropagation();
    },
    clearSelection: function (all) {
        var $targets = $(
            PF.obj.listing.selectors.list_item + ".selected",
            PF.obj.listing.selectors[
            all ? "content_listing" : "content_listing_visible"
            ]
        );
        this.unselectItem($targets);
        this.listMassActionSet("select");
    },

    listMassActionSet: function (action) {
        var current = action == "select" ? "clear" : "select";
        var $target = $("[data-text-select-all][data-action=list-" + current + "-all]:visible",);
        var text = $target.data("text-" + action + "-all");
        $target.text(text).attr("data-action", "list-" + action + "-all");
        PF.fn.close_pops();
    },

    updateItem: function ($target, response, action, growl) {
        if ($target instanceof jQuery == false) {
            var $target = $($target);
        }

        var dealing_with = $target.data("type"),
            album = dealing_with == "image" ? response.album : response;

        this.addAlbumtoModals(album);

        $("option[value=" + album.id_encoded + "]", "[name=form-album-id]").html(
            PF.fn.htmlEncode(album.name_with_privacy_readable_html)
        );

        if (typeof action == "undefined") {
            var action = "edit";
        }

        if (action == "edit" || action == "move") {
            if (action == "move" && CHV.obj.resource.type == "album") {
                CHV.fn.list_editor.moveFromList($target, growl);
                return;
            }
            $target.attr("data-description", response.description);

            if (dealing_with == "image") {
                if (typeof response.title !== typeof undefined) {
                    $target.attr("data-title", response.title);
                    $target.find("[title]").attr("title", response.title);
                    $("[data-text=image-title]", $target).text(
                        PF.fn.htmlEncode(response.title)
                    );
                }
                if (typeof response.title_truncated !== typeof undefined) {
                    $("[data-text=image-title-truncated]", $target).html(
                        PF.fn.htmlEncode(response.title_truncated)
                    );
                }
                if (typeof response.category_id !== typeof undefined) {
                    $target.attr("data-category-id", response.category_id);
                }
                $target.attr({
                    "data-album-id": album.id_encoded,
                    "data-flag": response.nsfw == 1 ? "unsafe" : "safe",
                });
                $("[data-content=album-link]", $target).attr("href", album.url);
            } else {
                $target.attr({
                    "data-privacy": album.privacy,
                    "data-password": album.password,
                    "data-name": album.name,
                });
            }
            $target.attr("data-privacy", album.privacy);
            $("[data-text=album-name]", $target).html(PF.fn.htmlEncode(album.name));

            PF.fn.growl.expirable(
                action == "edit" ?
                    PF.fn._s("The content has been edited.") :
                    PF.fn._s("The content has been moved.")
            );
        }
    },

    addAlbumtoModals: function (album) {
        var added = false;
        $("[name=form-album-id]", "[data-modal]").each(function () {
            if (
                album.id_encoded &&
                !$("option[value=" + album.id_encoded + "]", this).exists()
            ) {
                $(this).append(
                    '<option value="' +
                    album.id_encoded +
                    '">' +
                    album.name_with_privacy_readable_html +
                    "</option>"
                );
                added = true;
            }
        });
        if (added) {
            CHV.fn.list_editor.updateUserCounters("album", 1, "+");
        }
    },

    updateAlbum: function (album) {
        $("[data-id=" + album.id_encoded + "]").each(function () {
            if (album.html !== "") {
                $(this).after(album.html);
                $(this).remove();
            }
        });
    },

    updateUserCounters: function (counter, number, operation) {
        if (typeof operation == "undefined") {
            var operation = "+";
        }

        // Current resource counter
        var $count = $("[data-text=" + counter + "-count]"),
            $count_label = $("[data-text=" + counter + "-label]"),
            number = parseInt(number),
            old_count = parseInt($count.html()),
            new_count,
            delta;

        switch (operation) {
            case "+":
                new_count = old_count + number;
                break;
            case "-":
                new_count = old_count - number;
                break;
            case "=":
                new_count = number;
                break;
        }

        delta = new_count - old_count;

        // Total counter
        var $total_count = $("[data-text=total-" + $count.data("text") + "]"),
            $total_count_label = $(
                "[data-text=" + $total_count.data("text") + "-label]"
            ),
            old_total_count = parseInt($total_count.html()),
            new_total_count = old_total_count + delta;

        $count.text(new_count);
        $total_count.text(new_total_count);
        $count_label.text(
            $count_label.data(new_count == 1 ? "label-single" : "label-plural")
        );
        $total_count_label.text(
            $count_label.data(new_total_count == 1 ? "label-single" : "label-plural")
        );
    },

    updateMoveItemLists: function (response, dealing_with, $targets) {
        CHV.fn.list_editor.clearSelection();
        if (/image/.test(dealing_with)) {
            if (dealing_with == "image") {
                // single
                CHV.fn.list_editor.updateItem(
                    "[data-type=image][data-id=" + $targets.data("id") + "]",
                    response.image,
                    "move"
                );
            } else {
                $targets.each(function () {
                    CHV.fn.list_editor.updateItem(
                        "[data-type=image][data-id=" + $(this).data("id") + "]",
                        response,
                        "move",
                        false
                    );
                });
                PF.fn.growl.expirable(PF.fn._s("The content has been moved."));
            }
        } else {
            CHV.fn.list_editor.moveFromList($targets, false);
            PF.fn.growl.expirable(PF.fn._s("The content has been moved."));
            if (response.album) {
                if (
                    typeof response.albums_old !== "undefined" ?
                        response.request.album.new == "true" :
                        response.request.editing.new_album == "true"
                ) {
                    // Add option select to modals
                    CHV.fn.list_editor.addAlbumtoModals(response.album);

                    var old_count = parseInt($("[data-text=album-count]").text()) - 1;

                    $(PF.obj.listing.selectors.pad_content).each(function () {
                        var list_count = $(this).find(PF.obj.listing.selectors.list_item)
                            .length;

                        if (list_count == 0) {
                            return;
                        }

                        var params = PF.fn.deparam(
                            $(this)
                                .closest(PF.obj.listing.selectors.content_listing)
                                .data("params")
                        );

                        if (params.sort == "date_desc" || old_count == list_count) {
                            $(this)[params.sort == "date_desc" ? "prepend" : "append"](
                                response.album.html
                            );
                        }
                    });
                } else {
                    CHV.fn.list_editor.updateAlbum(response.album);
                }
            }

            PF.fn.listing.columnizerQueue();
            PF.fn.listing.refresh(0);
        }
    },
};

CHV.fn.import = {
    errorHandler: function (response) {
        PF.fn.growl.call(response.error.message);
    },
    reset: function (id) {
        var id = parseInt(id);
        CHV.obj.import.working[id].stats = $.ajax({
            type: "POST",
            data: {
                action: "importReset",
                id: id,
            },
        });
        CHV.obj.import.working[id].stats.complete(function (XHR) {
            var response = XHR.responseJSON;
            if (response) {
                var $html = CHV.fn.import.parseTemplate(response.import);
                $(
                    "[data-id=" + response.import.id + "]",
                    CHV.obj.import.sel.root
                ).replaceWith($html);
                if (response.import.status != "working") {
                    clearInterval(CHV.obj.import.working[id].interval);
                }
            }
        });
    },
    updateStats: function (id) {
        var id = parseInt(id);
        if (
            "readyState" in CHV.obj.import.working[id].stats &&
            CHV.obj.import.working[id].stats.readyState != 4
        ) {
            console.error(
                "Aborting stats timeout call (previous call is still not ready)"
            );
            return;
        }
        CHV.obj.import.working[id].stats = $.ajax({
            type: "POST",
            data: {
                action: "importStats",
                id: id,
            },
        });
        CHV.obj.import.working[id].stats.complete(function (XHR) {
            var response = XHR.responseJSON;
            if (response) {
                var $html = CHV.fn.import.parseTemplate(response.import);
                $(
                    "[data-id=" + response.import.id + "]",
                    CHV.obj.import.sel.root
                ).replaceWith($html);
                if (response.import.status != "working") {
                    clearInterval(CHV.obj.import.working[id].interval);
                }
            }
        });
    },
    delete: {
        submit: function (id) {
            PF.obj.modal.form_data = {
                action: "importDelete",
                id: id,
            };
            return true;
        },
        deferred: {
            success: function (XHR) {
                var response = XHR.responseJSON;
                PF.fn.growl.call(PF.fn._s("Import ID %s removed", response.import.id));
                $(
                    "[data-id=" + response.import.id + "]",
                    CHV.obj.import.sel.root
                ).remove();
                if ($("li", CHV.obj.import.sel.root).size() == 1) {
                    $(CHV.obj.import.sel.root).addClass("hidden");
                }
            },
            error: function (XHR) {
                CHV.fn.import.errorHandler(XHR.responseJSON);
            },
        },
    },
    parseTemplate: function (dataset, $el) {
        var tpl = CHV.obj.import.rowTpl;
        for (var key in CHV.obj.import.importTr) {
            if (typeof dataset[key] != typeof undefined) {
                tpl = tpl.replaceAll("%" + key + "%", dataset[key]);
            }
        }
        tpl = tpl.replaceAll("%parse%", dataset.options.root);
        tpl = tpl.replaceAll("%shortParse%", dataset.options.root.charAt(0));
        tpl = tpl.replaceAll(
            "%displayStatus%",
            CHV.obj.import.statusesDisplay[dataset.status]
        );
        var $html = $($.parseHTML(tpl)).attr(
            "data-object",
            JSON.stringify(dataset)
        );
        return $html;
    },
};

CHV.fn.Palettes = {
    timeout: {},
    get: function () {
        return ($("html").get(0).className.match(/(^|\s)palette-\S+/g) || []).join(' ');
    },
    set: function (palette) {
        $("html")
            .attr("data-palette", palette)
            .removeClass(this.get())
            .addClass("palette-" + palette);
    },
    preview: function (palette) {
        $("html")
            .removeClass(this.get())
            .addClass("palette-" + palette);
    },
    save: function () {
        clearTimeout(this.timeout);
        this.timeout = setTimeout(function () {
            $.ajax({
                type: "POST",
                data: {
                    action: "paletteSet",
                    palette_id: CHV.obj.config.palettesId[$("html").attr("data-palette")],
                },
                cache: false,
            });
        }, 400);
    }
}

CHV.fn.license = {
    set: {
        submit: function () {
            var $modal = $(PF.obj.modal.selectors.root),
                submit = true;
            $.each($(":input", $modal), function (i, v) {
                if ($(this).val() == "" && $(this).attr("required")) {
                    $(this).highlight();
                    submit = false;
                }
            });
            if (!submit) {
                PF.fn.growl.call(PF.fn._s("Please fill all the required fields."));
                return false;
            }
            PF.obj.modal.form_data = {
                action: "set-license-key",
                key: $("[name=chevereto-license-key]", $modal).val(),
            };
            return true;
        },
        complete: {
            success: function (XHR) {
                let response = XHR.responseJSON;
                let $trigger = $("[data-action=upgrade]");
                if(CHV.obj.system_info.edition === 'free') {
                    $trigger.removeClass("hidden");
                    $trigger.trigger("click");
                    return;
                }
                PF.fn.growl.call(PF.fn._s(response.success.message));
            },
            error: function (XHR) {
                var response = XHR.responseJSON;
                PF.fn.growl.call(PF.fn._s(response.error.message));
            },
        },
    },
};
