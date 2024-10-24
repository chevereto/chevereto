/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function () {
    var ajaxSetup = {
        url: PF.obj.config.json_api,
        cache: false,
        dataType: "json",
        data: { auth_token: PF.obj.config.auth_token }
    };
    if (typeof PF.obj.config.session_id !== typeof undefined) {
        ajaxSetup.data.session_id = PF.obj.config.session_id;
    }
    $.ajaxSetup(ajaxSetup);

    /**
     * WINDOW LISTENERS
     * -------------------------------------------------------------------------------------------------
     */
    function beforeUnloadListener(event) {
        if (
            $("form", PF.obj.modal.selectors.root).data("beforeunload") == "continue"
        )
        return;
        if (
            $(PF.obj.modal.selectors.root).is(":visible") &&
            PF.fn.form_modal_has_changed()
        ) {
            event.preventDefault();
            return event.returnValue = '<i class="fas fa-exclamation-triangle"></i> ' + PF.fn._s(
                "All the changes that you have made will be lost if you continue."
            );
        }
    };
    window.addEventListener('beforeunload', beforeUnloadListener);

    if(("standalone" in window.navigator) && window.navigator.standalone) {
        $(document).on("click", "a", function(e) {
            var new_location = $(this).attr('href');
            if (new_location != undefined && new_location.substr(0, 1) != '#' && $(this).attr('data-method') == undefined) {
                e.preventDefault();
                new_location = new_location.replace(PF.obj.config.public_url, PF.obj.config.base_url);
                window.location = new_location;
                return false;
            }
        });
    }

    var previousScrollPosition = 0;
    const supportPageOffset = window.pageXOffset !== undefined;
    const isCSS1Compat = (document.compatMode || "") === "CSS1Compat";
    const isScrollingDown = function () {
        let scrolledPosition = supportPageOffset
            ? window.pageYOffset
            : isCSS1Compat
                ? document.documentElement.scrollTop
                : document.body.scrollTop;
        let isScrollDown;
        if (scrolledPosition > previousScrollPosition) {
            isScrollDown = true;
        } else {
            isScrollDown = false;
        }
        previousScrollPosition = scrolledPosition;
        return isScrollDown;
    };
    var scrollTimer;
    var ninjaScroll = function () {
        var down = isScrollingDown();
        var scrollUpClass = "scroll-up";
        var scrollDownClass = "scroll-down";
        var noStickyMediaClass = "no-sticky-media";
        var isAnimated = $(".top-bar").is(":animated");
        if(isAnimated
            || $("html").attr("data-scroll-lock") === "1"
        ) {
            scrollTimer = false;
            return;
        }
        if($(window).scrollTop() <= 0) {
            $("html").removeClass(scrollUpClass + " " + scrollDownClass);
            scrollTimer = false;
            return;
        }
        var addClass = scrollUpClass;
        var removeClass = scrollDownClass;
        // fix force down for Safari scroll bottom bounce
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
            down = true;
          }
        if(down) {
            addClass = scrollDownClass;
            removeClass = scrollUpClass;
        }
        var mediaHeight = $('#image-viewer').outerHeight();
        var viewportHeight = $(window).height();
        if(mediaHeight/viewportHeight > 0.6) {
            addClass += " " + noStickyMediaClass;
        } else {
            removeClass += " " + noStickyMediaClass;
        }
        $("html")
            .addClass(addClass)
            .removeClass(removeClass);
        scrollTimer = false;
    };
    window.addEventListener("load", ninjaScroll());
    window.addEventListener("scroll", function () {
        if(!$("html").hasScrollbar().vertical) return;
        if(scrollTimer) return;
        scrollTimer = true;
        setTimeout(ninjaScroll(), 400);
    });

    // Blind the tipTips on load
    PF.fn.bindtipTip();

    var resizeTimeout = 0,
        resizeTimer,
        width = $(window).width();
    $(window).on("resize", function () {
        PF.fn.modal.styleAware();
        PF.fn.close_pops();
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            PF.fn.modal.fixScrollbars();
            var device = PF.fn.getDeviceName(),
                handled = ["phone", "phablet"],
                desktop = ["tablet", "laptop", "desktop"];
            var new_device = PF.fn.getDeviceName();
            if (
                (new_device !== device &&
                    ($.inArray(device, handled) >= 0 &&
                        $.inArray(new_device, handled) == -1)) ||
                ($.inArray(device, desktop) >= 0 && $.inArray(new_device, desktop) == -1)
            ) {
                PF.fn.close_pops();
            }

            $(".top-bar").css("top", "");
            $("body").css({ position: "", height: "" });

            $(".antiscroll")
                .removeClass("jsly")
                .data("antiscroll", ""); // Destroy for this?
            $(".antiscroll-inner").css({ height: "", width: "", maxheight: "" }); // .pop-box, .pop-box-inner ?

            PF.fn.list_fluid_width();

            if (width !== $(window).width()) {
                $(PF.obj.listing.selectors.list_item, PF.obj.listing.selectors.content_listing_visible).css("opacity", 0);
                if (
                    $("[data-action=top-bar-menu-full]", "#top-bar").hasClass("current")
                ) {
                    PF.fn.topMenu.hide(0);
                }
                PF.fn.listing.columnizer(true, 0, true);
                $(PF.obj.listing.selectors.list_item, PF.obj.listing.selectors.content_listing_visible).css("opacity", 1);
            }
            width = $(window).width();
        }, resizeTimeout);
    });

    // Close the opened pop-boxes on HTML click
    $(document).on("click", "html", function () {
        PF.fn.close_pops();
    });

    // Keydown numeric input (prevents non numeric keys)
    $(document).on("keydown", ".numeric-input", function (e) {
        e.keydown_numeric();
    });

    // The handly data-scrollto. IT will scroll the elements to the target
    $(document).on("click", "[data-scrollto]", function (e) {
        var target = $(this).data("scrollto"),
            $target = $(!target.match(/^\#|\./) ? "#" + target : target);

        if ($target.exists()) {
            PF.fn.scroll($target);
        } else {
            console.log("PF scrollto error: target doesn't exists", $target);
        }
    });

    $(document).on(
        "click focus",
        "[data-login-needed], [data-user-logged=must]",
        function (e) {
            if (!PF.fn.is_user_logged()) {
                e.preventDefault();
                e.stopPropagation();
                window.location.href = PF.obj.vars.urls.login;
                return false;
            }
        }
    );

    // The handly data-trigger. It will trigger click for elements with data-trigger
    $(document).on("click", "[data-trigger]", function (e) {
        if (e.isPropagationStopped()) {
            return false;
        }

        var trigger = $(this).data("trigger"),
            $target = $(!trigger.match(/^\#|\./) ? "#" + trigger : trigger);

        if ($target.exists()) {
            e.stopPropagation();
            e.preventDefault();
            if (!$target.closest(PF.obj.modal.selectors.root).length) {
                PF.fn.modal.close();
            }
            $target.trigger("click");
        } else {
            console.log("PF trigger error: target doesn't exists", $target);
        }
    });

    // Fix the auth_token inputs
    $("form[method=post]").each(function () {
        if (!$("input[name=auth_token]", this).exists()) {
            $(this).append(
                $("<input>", {
                    type: "hidden",
                    name: "auth_token",
                    value: PF.obj.config.auth_token
                })
            );
        }
    });

    // Clear form like magic
    $(document).on("click", ".clear-form", function () {
        $(this)
            .closest("form")[0]
            .reset();
    });

    $(document).on("submit", "form", function (e) {
        if(e.isPropagationStopped()) {
            return;
        }
        var type = $(this).data("type");
        var hasErrors = false;
        var $validate = $(this).find("[required], [data-validate]");
        var errorFn = function ($el) {
            if($el.is(":hidden")) {
                return;
            }
            $el.highlight();
            $el.closest(".input-label").find("label").shake();
            hasErrors = true;
        };
        $validate.each(function () {
            if(!$(this)[0].checkValidity()) {
                errorFn($(this));
            }
        });
        if (!hasErrors) {
            hasErrors = !$(this).get(0).checkValidity();
        }
        if(hasErrors) {
            $(this).get(0).reportValidity();
            return false;
        }
    });

    // Co-combo breaker
    $(document).on("change", "select[data-combo]", function () {
        var $combo = $("#" + $(this).data("combo"));

        if ($combo.exists()) {
            $combo.children(".switch-combo").hide();
        }

        var $combo_container = $(
            "#" +
            $(this)
                .closest("select")
                .data("combo")
        ),
            $combo_target = $(
                "[data-combo-value~=" + $("option:selected", this).attr("value") + "]",
                $combo_container
            );

        if ($combo_target.exists()) {
            $combo_target
                .show()
                .find("[data-required]")
                .each(function () {
                    $(this).attr("required", "required"); // re-enable any disabled required
                });
        }

        // Disable [required] in hidden combos
        $(".switch-combo", $combo_container).each(function () {
            if ($(this).is(":visible")) return;
            $("[required]", this)
                .attr("data-required", true)
                .removeAttr("required");
        });
    });

    $(document).on("keyup", function (e) {
        var $this = $(e.target);
        var event = e.originalEvent;
        if (event.key == "Escape") {
            if ($(PF.obj.modal.selectors.root).is(":visible")) {
                if(!$this.is(":input")) {
                    $(
                        "[data-action=cancel],[data-action=close-modal]",
                        PF.obj.modal.selectors.root
                    )
                        .first()
                        .trigger("click");
                } else {
                    $this.trigger("blur");
                }
                PF.fn.keyFeedback.spawn(e);
            }
        }
    });

    // Input events
    $(document).on("keyup", ":input", function (e) {
        $(".input-warning", $(this).closest(".input-label")).html("");
    });
    $(document).on("blur", ":input", function () {
        var this_val = $.trim($(this).prop("value"));
        $(this).prop("value", this_val);
    });

    $(document).on("click", "[data-focus=select-all],[data-click=select-all]", function () {
        if ($(this).is(":input")) {
            this.select();
        } else {
            var range = document.createRange();
            range.selectNodeContents(this);
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        }
    });

    // Input password strength
    $(document).on("keyup change blur", ":input[type=password]", function () {
        var password = testPassword($(this).val()),
            $parent = $(this).closest("div");

        if ($(this).val() == "") {
            password.percent = 0;
            password.verdict = "";
        }

        $("[data-content=password-meter-bar]", $parent)
            .attr("data-veredict", password.verdict.replace(/ /g, "-"))
            .width(password.percent);
        $("[data-text=password-meter-message]", $parent)
            .removeClass("red-warning")
            .text(password.verdict !== "" ? PF.fn._s(password.verdict) : "");
    });

    // Popup links
    $(document).on("click", "[rel=popup-link], .popup-link", function (e) {
        e.preventDefault();
        var href = $(this)[
            typeof $(this).attr("href") !== "undefined" ? "attr" : "data"
        ]("href");
        if (typeof href == "undefined") {
            return;
        }
        if(PF.fn.isDevice(["phone", "phablet"])) {
            if (href.substring(0, 6) == "mailto") {
                window.location = href;
                return;
            }
            if (href.substring(0, 5) == "share") {
                if(navigator.canShare) {
                    navigator.share(
                        PF.fn.deparam(href.substring(6))
                    );
                }
                return;
            }

        }
        PF.fn.popup({ href: href });
    });

    /**
     * MODAL
     * -------------------------------------------------------------------------------------------------
     */

    // Call plain simple HTML modal
    $(document).on("click", "[data-modal=simple],[data-modal=html]", function () {
        var $target = $(
            "[data-modal=" + $(this).data("target") + "], #" + $(this).data("target")
        ).first();
        PF.fn.modal.call({ template: $target.html(), buttons: false });
    });

    // Prevent modal submit form since we only use the form in the modal to trigger HTML5 validation
    $(document).on("submit", PF.obj.modal.selectors.root + " form", function (e) {
        if ($(this).data("prevented")) return false; // Don't send the form if is prevented
        if (typeof $(this).attr("method") !== "undefined") return; // Don't bind anything extra if is normal form
        return false; // Prevent default form handling
    });

    // Form/editable/confirm modal
    $(document).on(
        "click",
        "[data-modal=edit],[data-modal=form],[data-confirm]",
        function (e) {
            e.preventDefault();

            var $this = $(this);
            var $target;

            if ($this.is("[data-confirm]")) {
                $target = $this;
                PF.obj.modal.type = "confirm";
            } else {
                $target = $(
                    "[data-modal=" + $this.data("target") + "], #" + $this.data("target")
                ).first();

                if ($target.length == 0) {
                    $target = $("[data-modal=form-modal], #form-modal").first();
                }

                if ($target.length == 0) {
                    console.log("PF Error: Modal target doesn't exists.");
                }

                PF.obj.modal.type = $this.data("modal");
            }

            var args = $this.data("args"),
                submit_function = window[$target.data("submit-fn")],
                cancel_function = window[$target.data("cancel-fn")],
                onload_function = window[$target.data("load-fn")],
                submit_done_msg = $target.data("submit-done"),
                ajax = {
                    url:
                        $target.data("ajax-url") ||
                        (typeof $target.data("is-xhr") !== typeof undefined
                            ? PF.obj.config.json_api
                            : null),
                    deferred: window[$target.data("ajax-deferred")]
                };

            if (typeof submit_function !== "function" && $target.data("submit-fn")) {
                var submit_fn_split = $target.data("submit-fn").split(".");
                submit_function = window;
                for (var i = 0; i < submit_fn_split.length; i++) {
                    submit_function = submit_function[submit_fn_split[i]];
                }
            }
            if (typeof cancel_function !== "function" && $target.data("cancel-fn")) {
                var cancel_fn_split = $target.data("cancel-fn").split(".");
                cancel_function = window;
                for (var i = 0; i < cancel_fn_split.length; i++) {
                    cancel_function = cancel_function[cancel_fn_split[i]];
                }
            }
            if (typeof load_function !== "function" && $target.data("load-fn")) {
                var load_fn_split = $target.data("load-fn").split(".");
                load_function = window;
                for (var i = 0; i < load_fn_split.length; i++) {
                    load_function = load_function[load_fn_split[i]];
                }
            }

            if (typeof ajax.deferred !== "object" && $target.data("ajax-deferred")) {
                var deferred_obj_split = $target.data("ajax-deferred").split(".");
                ajax.deferred = window;
                for (var i = 0; i < deferred_obj_split.length; i++) {
                    ajax.deferred = ajax.deferred[deferred_obj_split[i]];
                }
            }

            // Before fn
            var fn_before = window[$target.data("before-fn")];
            if (typeof fn_before !== "function" && $target.data("before-fn")) {
                var before_obj_split = $target.data("before-fn").split(".");
                fn_before = window;
                for (var i = 0; i < before_obj_split.length; i++) {
                    fn_before = fn_before[before_obj_split[i]];
                }
            }
            if (typeof fn_before == "function") {
                var before_result = fn_before(e);
                if(before_result === false) {
                    return false;
                }
            }

            var inline_options = $(this).data("options") || {};

            // Confirm modal
            if ($this.is("[data-confirm]")) {
                var default_options = {
                    message: $this.data("confirm"),
                    confirm:
                        typeof submit_function == "function" ? submit_function(args) : "",
                    cancel:
                        typeof cancel_function == "function" ? cancel_function(args) : "",
                    ajax: ajax
                };

                if ($this.attr("href") && default_options.confirm == "") {
                    default_options.confirm = function () {
                        return window.location.replace($this.attr("href"));
                    };
                }

                PF.fn.modal.confirm($.extend(default_options, inline_options));
            } else {
                // Form/editable
                var default_options = {
                    template: $target.html(),
                    button_submit: $(this).is("[data-modal=edit]")
                        ? PF.fn._s("Save changes")
                        : PF.fn._s("Submit"),
                    confirm: function () {
                        var form_modal_has_changed = PF.fn.form_modal_has_changed();

                        // Conventional form handling
                        var $form = $("form", PF.obj.modal.selectors.root);
                        if (typeof $form.attr("action") !== "undefined") {
                            $form.data("prevented", !form_modal_has_changed);
                            PF.fn.modal.close();
                            return;
                        }
                        $(":input[name]", $form).each(function () {
                            if (!$(this).is(":visible")) {
                                var input_attr = $(this).attr("required");
                                if (
                                    typeof input_attr !== typeof undefined &&
                                    input_attr !== false
                                ) {
                                    $(this)
                                        .prop("required", false)
                                        .attr("data-required", "required");
                                }
                            } else {
                                if ($(this).attr("data-required") == "required") {
                                    $(this).prop("required", true);
                                }
                            }
                        });
                        if (!PF.fn.form.validateForm($form)) {
                            return false;
                        }

                        // Run the full function only when the form changes
                        if (!form_modal_has_changed && !inline_options.forced) {
                            PF.fn.modal.close();
                            return;
                        }

                        if (typeof submit_function == "function")
                            submit_fn = submit_function(args);
                        if (typeof submit_fn !== "undefined" && submit_fn == false) {
                            return false;
                        }

                        $(":input", PF.obj.modal.selectors.root).each(function () {
                            $(this).val($.trim($(this).val()));
                        });

                        if ($this.is("[data-modal=edit]")) {
                            // Set the input values before cloning the html
                            $target.html(
                                $(
                                    PF.obj.modal.selectors.body,
                                    $(PF.obj.modal.selectors.root).bindFormData()
                                )
                                    .html()
                                    .replace(/rel=[\'"]tooltip[\'"]/g, 'rel="template-tooltip"')
                            );
                        }

                        if (typeof ajax.url !== "undefined") {
                            return true;
                        } else {
                            PF.fn.modal.close(function () {
                                if (typeof submit_done_msg !== "undefined" && submit_done_msg !== "") {
                                    PF.fn.growl.expirable(submit_done_msg);
                                }
                            });
                        }
                    },
                    cancel: function () {
                        if (typeof cancel_fn == "function") cancel_fn = cancel_fn();
                        if (typeof cancel_fn !== "undefined" && cancel_fn == false) {
                            return false;
                        }
                        // nota: falta template aca
                        if (
                            $target.data("prompt") != "skip" &&
                            PF.fn.form_modal_has_changed()
                        ) {
                            if ($(PF.obj.modal.selectors.changes_confirm).exists()) return;
                            $(PF.obj.modal.selectors.box, PF.obj.modal.selectors.root)
                                .css({ transition: "none" })
                                .hide();
                            $(PF.obj.modal.selectors.root).append(
                                '<div id="' +
                                PF.obj.modal.selectors.changes_confirm.replace("#", "") +
                                '"><div class="content-width"><h2>' +
                                '<i class="fas fa-exclamation-triangle"></i> ' +
                                PF.fn._s(
                                    "All the changes that you have made will be lost if you continue."
                                ) +
                                '</h2><div class="' +
                                PF.obj.modal.selectors.btn_container.replace(".", "") +
                                ' margin-bottom-0"><button class="btn btn-input default" data-action="cancel">' +
                                '<i class="fas fa-chevron-circle-left btn-icon"></i>'+
                                '<span class="btn-text">' +
                                PF.fn._s("Go back to form") +
                                '</span>' +
                                '</button> <span class="btn-alt">' +
                                PF.fn._s("or") +
                                ' <a data-action="submit"><i class="fas fa-check margin-right-5"></i>' +
                                PF.fn._s("continue anyway") +
                                "</a></span></div></div>"
                            );
                            $(PF.obj.modal.selectors.changes_confirm)
                                .css(
                                    "margin-top",
                                    -$(PF.obj.modal.selectors.changes_confirm).outerHeight(true) /
                                    2
                                )
                                .hide()
                                .fadeIn("fast");
                        } else {
                            PF.fn.modal.close();
                        }
                    },
                    load: function () {
                        if (typeof load_function == "function") load_function();
                    },
                    callback: function () { },
                    ajax: ajax
                };
                PF.fn.modal.call($.extend(default_options, inline_options));
            }
        }
    );

    if (!PF.fn.is_user_logged()) {
        $("[data-login-needed]:input, [data-user-logged=must]:input").each(
            function () {
                $(this).attr("readonly", true);
            }
        );
    }

    $(document).on("keydown", "html", function (e) {
        var $this = $(e.target),
            event = e.originalEvent;
        if (event.key === "Escape") {
            PF.fn.growl.close();
        }
        var submit = event.key === "Enter" && (event.ctrlKey || event.metaKey);
        if($this.is("textarea") && !submit) {
            e.stopPropagation();
            return;
        }
        if($this.is(":input.search") && event.key === "Escape") {
            if($this.val() == "") {
                $this.trigger("blur");
            }
            $this
                .closest(".input-search")
                .find("[data-action=clear-search]")
                .trigger("click");
            return;
        }

        var $inputEnabledEnter = $this.is(":input.search") || $this.closest(".input-with-button").exists();
        if(!$inputEnabledEnter && $this.is(":input, textarea") && event.key === 'Enter' && !submit) {
            e.stopPropagation();
            e.preventDefault();
            return;
        }
        var $form = $this.is(":input")
            ? $this.closest("form:not([data-js])")
            : $("form:not([data-js])", ".form-content:visible").first();
        if($(PF.obj.modal.selectors.root).exists()) {
            if(!submit
                && event.key === 'Enter'
                && $("[data-action=submit]", PF.obj.modal.selectors.root).exists()
                && !$this.is(".prevent-submit")
            ) {
                submit = true;
            }
            if(!submit) {
                return;
            }
            if(!$form.exists()) {
                e.stopPropagation();
                e.preventDefault();
                $("[data-action=submit]", PF.obj.modal.selectors.root).trigger("click");
            }
        }
        if(submit) {
            if($form.exists()) {
                e.stopPropagation();
                e.preventDefault();
                $form.trigger("submit");
            }
            PF.fn.keyFeedback.spawn(e);
        }
    });

    // function hashToAction() {
    //     $('[data-action="'+ window.location.hash.slice(1) +'"]')
    //         .first()
    //         .trigger("click");
    // }
    // if(window.location.hash) {
    //     hashToAction();
    // }

    // $(window).on("hashchange", function () {
    //     hashToAction();
    // });

    /**
     * MOBILE TOP BAR MENU
     * -------------------------------------------------------------------------------------------------
     */
    $(document).on("click", "#menu-fullscreen .fullscreen, [data-action=top-bar-menu-full]", function (e) {
        if($(e.target).is("#pop-box-mask")) {
            return;
        }
        var hasClass = $("[data-action=top-bar-menu-full]", "#top-bar").hasClass(
            "current"
        );
        PF.fn.topMenu[hasClass ? "hide" : "show"]();
        if(Boolean(window.navigator.vibrate)) {
            var pattern = !hasClass ? [15, 200, 25, 125, 15] : [15, 200, 15];
            window.navigator.vibrate(0);
            window.navigator.vibrate(pattern);
        }
    });

    /**
     * SEARCH INPUT
     * -------------------------------------------------------------------------------------------------
     */

    // Top-search feature
    $(document).on("click", "[data-action=top-bar-search]", function () {
        $("[data-action=top-bar-search-input]", ".top-bar")
            .removeClass("hidden");
        $("[data-action=top-bar-search-input]:visible input")
            .first()
            .focus();
        if (
            is_ios() &&
            !$(this)
                .closest(PF.fn.topMenu.vars.menu)
                .exists()
        ) {
            $(".top-bar").css("position", "absolute");
        }
        $("[data-action=top-bar-search]", ".top-bar").addClass("hidden");
    });

    $(document).on("click", ".input-search .icon--search", function (e) {
        $("input", e.currentTarget.offsetParent).focus();
    });

    $(document).on(
        "click",
        ".input-search .icon--close, .input-search [data-action=clear-search]",
        function (e) {
            var $input = $("input", e.currentTarget.offsetParent);
            if ($input.val() == "") {
                if (
                    $(this)
                        .closest("[data-action=top-bar-search-input]")
                        .exists()
                ) {
                    $("[data-action=top-bar-search-input]", ".top-bar").addClass("hidden");
                    $("[data-action=top-bar-search]", ".top-bar")
                        .removeClass("opened")
                        .removeClass("hidden");
                }
            } else {
                if (
                    !$(this)
                        .closest("[data-action=top-bar-search-input]")
                        .exists()
                ) {
                    $(this).addClass("hidden");
                }
                $input.val("").trigger("change");
            }
        }
    );

    // Input search clear search toggle
    $(document).on("keyup change", "input.search", function (e) {
        var $input = $(this),
            $div = $(this).closest(".input-search");
        if (
            !$(this)
                .closest("[data-action=top-bar-search-input]")
                .exists()
        ) {
            $(".icon--close, [data-action=clear-search]", $div)
                .toggleClass("hidden", $input.val() == "");
        }
    });

    /**
     * POP BOXES (MENUS)
     * -------------------------------------------------------------------------------------------------
     */
    $(document)
        .on("click mouseenter", ".pop-btn", function (e) {
            if (
                PF.fn.isDevice(["phone", "phablet"]) &&
                (e.type == "mouseenter" || $(this).hasClass("pop-btn-desktop"))
            ) {
                return;
            }

            var $this_click = $(e.target);
            var $pop_btn;
            var $pop_box;
            var devices = $.makeArray(["phone", "phablet"]);
            var $this = $(this);

            if (e.type == "mouseenter" && !$(this).hasClass("pop-btn-auto")) return;
            if (
                $(this).hasClass("disabled") ||
                ($this_click.closest(".current").exists() &&
                    !PF.fn.isDevice("phone") &&
                    !$this_click.closest(".pop-btn-show").exists())
            ) {
                return;
            }

            PF.fn.growl.close();

            e.stopPropagation();

            $pop_btn = $(this);
            $pop_box = $(".pop-box", $pop_btn);
            $pop_btn.addClass("opened");
            var marginBox = parseInt($pop_box.css("margin-right"));

            $(".pop-box-inner", $pop_box).css("max-height", "");

            if (PF.fn.isDevice(devices)) {
                var textButton = $(".pop-btn-text,.btn-text,.text", $pop_btn)
                    .first().text();
                var iconButton = $(".pop-btn-icon,.btn-icon,.icon", $pop_btn)[0].outerHTML;
                if (!$(".pop-box-header", $pop_box).exists()) {
                    $pop_box.prepend(
                        $("<div/>", {
                            class: "pop-box-header",
                            html: iconButton + ' ' + textButton + '<span class="btn-icon icon--close fas fa-times"></span></span>'
                        })
                    );
                }
            } else {
                $(".pop-box-header", $pop_box).remove();
                $pop_box.css({ bottom: "" });
            }
            if ($pop_box.hasClass("anchor-center")) {
                if (!PF.fn.isDevice(devices)) {
                    $pop_box.css("marginInlineStart", -($pop_box.outerWidth() / 2));
                } else {
                    $pop_box.css("marginInlineStart", "");
                }
            }

            // Pop button changer
            if ($this_click.is("[data-change]")) {
                $("li", $pop_box).removeClass("current");
                $this_click.closest("li").addClass("current");
                $("[data-text-change]", $pop_btn).text(
                    $("li.current a", $pop_box).text()
                );
                e.preventDefault();
            }

            if (!$pop_box.exists()) return;

            var $this = e.istriggered ? $(e.target) : $(this);
            if (
                $pop_box.is(":visible") &&
                $(e.target)
                    .closest(".pop-box-inner")
                    .exists() &&
                ($this.hasClass("pop-keep-click"))
            ) {
                return;
            }

            $(".pop-box:visible")
                .not($pop_box)
                .hide()
                .closest(".pop-btn")
                .removeClass("opened");

            var callback = function ($pop_box) {
                if (!$pop_box.is(":visible")) {
                    $pop_box
                        .css("marginInlineStart", "")
                        .removeAttr("data-guidstr")
                        .closest(".pop-btn")
                        .removeClass("opened");
                } else {
                    if (!PF.fn.isDevice(devices)) {
                        if($pop_box.is(".--auto-cols")) {
                            const max_cols = 5;
                            $pop_box.removeClass(function (i, c) {
                                return (c.match (/(^|\s)pbcols\S+/g) || []).join(' ');
                            });
                            for(let i = 1; i <= max_cols; i++) {
                                $pop_box.addClass("pbcols" + i);
                                $(".pop-box-inner", $pop_box)
                                    .toggleClass("pop-box-menucols", i > 1);
                                fixMargin();
                                if($pop_box.is_in_viewport() && $pop_box.height() < $(window).height()*.8) {
                                    break;
                                }
                                if(i !== max_cols) {
                                    $pop_box
                                        .css("marginInlineStart", "")
                                        .removeClass("pbcols" + i);
                                }
                            }
                        }
                        function fixMargin() {
                            var posMargin = $pop_box.css("marginInlineStart");
                            if (typeof posMargin !== typeof undefined) {
                                posMargin = parseFloat(posMargin);
                                $pop_box.css("marginInlineStart", "");
                            }
                            var cutoff = $pop_box.getWindowCutoff();
                            if (cutoff && cutoff.right && cutoff.right < posMargin) {
                                $pop_box
                                    .css("marginInlineStart", cutoff.right + "px");
                            } else {
                                $pop_box.css("marginInlineStart", posMargin + "px");
                                cutoff = $pop_box.getWindowCutoff();
                                if(cutoff && cutoff.left) {
                                    let marginFix = -(Math.abs(posMargin) + Math.abs(cutoff.left) + marginBox/2);
                                    $pop_box.css(
                                        "marginInlineStart",
                                        marginFix + "px"
                                    );
                                }
                            }
                        }
                        $(".antiscroll-wrap:not(.jsly):visible", $pop_box)
                            .addClass("jsly")
                            .antiscroll();
                    } else {
                        $(".antiscroll-inner", $pop_box).height("100%");
                    }
                }
            };

            if (PF.fn.isDevice(devices)) {
                if ($(this).is("[data-action=top-bar-notifications]")) {
                    $pop_box.css({ height: $(window).height() });
                }
                var pop_box_h = $pop_box.height() + "px";
                var menu_top =
                    parseInt($(".top-bar").outerHeight()) +
                    parseInt($(".top-bar").css("top")) +
                    parseInt($(".top-bar").css("margin-top")) +
                    parseInt($(".top-bar").css("margin-bottom")) +
                    "px";
                if ($pop_box.is(":visible")) {
                    $("#pop-box-mask").css({ opacity: 0 });
                    $pop_box.css({ transform: "none" });
                    if ($this.closest(PF.fn.topMenu.vars.menu).exists()) {
                        $(".top-bar").css({ transform: "none" });
                    }
                    setTimeout(function () {
                        $pop_box.hide().attr("style", "");
                        $("#pop-box-mask").remove();
                        callback($pop_box);
                        if ($this.closest(PF.fn.topMenu.vars.menu).exists()) {
                            $(PF.fn.topMenu.vars.menu).css({
                                height: ""
                            });
                            $(PF.fn.topMenu.vars.menu).animate(
                                { scrollTop: PF.fn.topMenu.vars.scrollTop },
                                PF.obj.config.animation.normal / 2
                            );
                        }
                        if (!$("body").data("hasOverflowHidden")) {
                            var removeClasses = "pop-box-show pop-box-show--top";
                            if(!$(PF.obj.modal.selectors.root).exists()) {
                                removeClasses += " overflow-hidden";
                            }
                            $("body,html").removeClass(removeClasses);
                        }
                        $pop_box.find(".pop-box-inner").css("height", "");
                    }, PF.obj.config.animation.normal);
                } else {
                    $("#pop-box-mask").remove();
                    $pop_box.parent().prepend(
                        $("<div/>", {
                            id: "pop-box-mask",
                            class: "fullscreen black"
                        }).css({
                            zIndex: 400,
                            display: "block"
                        })
                    );
                    PF.fn.topMenu.vars.scrollTop = $(PF.fn.topMenu.vars.menu).scrollTop();
                    setTimeout(function () {
                        $("#pop-box-mask").css({ opacity: 1 });
                        setTimeout(function () {
                            $pop_box.show().css({
                                bottom: "-" + pop_box_h,
                                maxHeight: "100%",
                                zIndex: 1000,
                                transform: "translate(0,0)"
                            });
                            setTimeout(function() {
                                $pop_box.find(".pop-box-inner").scrollTop(0)
                            }, 1)

                            setTimeout(function () {
                                $pop_box.css({ transform: "translate(0,-" + pop_box_h + ")" });
                            }, 1);

                            setTimeout(function () {
                                callback($pop_box);
                            }, PF.obj.config.animation.normal);

                            if ($("html").hasClass("overflow-hidden")) {
                                $("html").data("hasOverflowHidden", 1);
                            } else {
                                $("html").addClass("overflow-hidden");
                                $("body").addClass(
                                    ($this.closest('.top-bar').exists()
                                        ? 'pop-box-show--top'
                                        : 'pop-box-show')
                                );
                            }

                            $(".pop-box-inner", $pop_box).css(
                                "height",
                                $pop_box.height() -
                                $(".pop-box-header", $pop_box).outerHeight(true)
                            );
                        }, 1);
                    }, 1);
                }
            } else {
                $pop_box[$pop_box.is(":visible") ? "hide" : "show"](0, function () {
                    callback($pop_box);
                });
            }
        })
        .on("mouseleave", ".pop-btn", function () {
            if (!PF.fn.isDevice(["laptop", "desktop"])) {
                return;
            }
            var $pop_btn = $(this),
                $pop_box = $(".pop-box", $pop_btn);

            if (
                !$pop_btn.hasClass("pop-btn-auto") ||
                (PF.fn.isDevice(["phone", "phablet"]) &&
                    $pop_btn.hasClass("pop-btn-auto"))
            ) {
                return;
            }

            $pop_box
                .hide()
                .closest(".pop-btn")
                .removeClass("opened");
        });

    /**
     * TABS
     * -------------------------------------------------------------------------------------------------
     */

    var loadTabHash = function () {
        var hash = window.location.hash;
        var $hash_node = $('[href="' + hash + '"]');
        if($hash_node.length > 0) {
            $.each($hash_node[0].attributes, function() {
                PF.obj.tabs.hashdata[this.name] = this.value;
            });
            PF.obj.tabs.hashdata.pushed = "tabs";
            PF.fn.show_tab(PF.obj.tabs.hashdata['data-tab']);
        }
    }

    if (window.location.hash) {
        loadTabHash();
    }
    window.onhashchange = loadTabHash;

    $(document).on("click", "[data-action=tab-menu]", function () {
        var $tabs = $(this)
            .closest(".header")
            .find(".content-tabs"),
            visible = $tabs.is(":visible"),
            wrap = $tabs.closest('.content-tabs-wrap');
            $this = $(this);
        wrap.css("display", visible ? "" : "block");
        $this.toggleClass('--hide', visible);
        if (!visible) {
            $tabs.data("classes", $tabs.attr("class"));
            $tabs.removeClass(function (index, css) {
                return (css.match(/\b\w+-hide/g) || []).join(" ");
            });
            // $tabs.hide();
        }
        if (!visible) {
            $this.removeClass("current");
        }
        // $tabs[visible ? "hide" : "show"]();
        if (visible) {
            $tabs.css("display", "").addClass($tabs.data("classes"));
            $this.addClass("current");
        }
    });

    /**
     * LISTING
     * -------------------------------------------------------------------------------------------------
     */

    // Load more (listing +1 page)
    $(document).on("click", "[data-action=load-more]", function (e) {
        if (PF.obj.listing.lockClickMore) {
            return;
        }
        PF.obj.listing.lockClickMore = true;
        $(this)
            .closest(PF.obj.listing.selectors.content_listing_load_more)
            .hide();

        if (
            !PF.fn.is_listing() ||
            $(this)
                .closest(PF.obj.listing.selectors.content_listing)
                .is(":hidden") ||
            $(this)
                .closest("#content-listing-template")
                .exists() ||
            PF.obj.listing.calling
        )
            return;

        PF.fn.listing.queryString.stock_new();
        PF.obj.listing.query_string.seek = $(this).attr("data-seek");
        PF.obj.listing.query_string.page = $(
            PF.obj.listing.selectors.content_listing_visible
        ).data("page");
        PF.obj.listing.query_string.page++;

        PF.fn.listing.ajax();
        e.preventDefault();
        e.stopPropagation();
    });

    // List found on load html -> Do the columns!
    if ($(PF.obj.listing.selectors.pad_content).is(":visible")) {
        PF.fn.listing.show();
        // Bind the infinte scroll
        $(document).on("scroll", function (event) {
            PF.fn.listing.scrollLock = true;
            var $loadMore = $(
                PF.obj.listing.selectors.content_listing_load_more,
                PF.obj.listing.selectors.content_listing_visible
            ).find("button[data-action=load-more]");
            var toScroll = $(document).height() - $(window).height() - 1.5 * document.documentElement.clientHeight;
            if (
                $loadMore.length > 0 &&
                $(window).scrollTop() > toScroll &&
                PF.obj.listing.calling == false
            ) {
                event.preventDefault();
                $loadMore.trigger("click");
            }
        });
    } else {
        $(PF.obj.listing.selectors.content_listing + ".visible").addClass("jsly");
    }

    // Multi-selection tools
    $(document).on(
        "click",
        PF.obj.modal.selectors.root + " [data-switch]",
        function () {
            var $this_modal = $(this).closest(PF.obj.modal.selectors.root);
            $("[data-view=switchable]", $this_modal).hide();
            $("#" + $(this).attr("data-switch"), $this_modal).show();
        }
    );

    $(document).on("click", "[data-toggle]", function () {
        var $target = $("[data-content=" + $(this).data("toggle") + "]");
        var show = !$target.is(":visible");
        $(this).html($(this).data("html-" + (show ? "on" : "off")));
        $target.toggle();
    });

    // Cookie law thing
    $(document).on("click", "[data-action=cookie-law-close]", function () {
        var $cookie = $(this).closest("#cookie-law-banner");
        var cookieName =
            typeof $cookie.data("cookie") !== typeof undefined
                ? $cookie.data("cookie")
                : "PF_COOKIE_LAW_DISPLAY";
        Cookies.set(cookieName, 0, { expires: 365 });
        $cookie.remove();
    });

    Clipboard = new ClipboardJS("[data-action=copy]", {
        text: function (trigger) {
            var $target = $(trigger.getAttribute("data-action-target"));
            var text = $target.is(":input") ? $target.val() : $target.text();
            return text.trim();
        }
    });
    Clipboard.on("success", function (e) {
        var $target = $(e.trigger.getAttribute("data-action-target"));
        $target.highlight();
        e.clearSelection();
    });

    $(window).on("fullscreenchange", function () {
        $("html").toggleClass("--fullscreen", document.fullscreenElement !== null);
    });
});

/**
 * PEAFOWL OBJECT
 * -------------------------------------------------------------------------------------------------
 */
var PF = { fn: {}, str: {}, obj: {} };

/**
 * PEAFOWL CONFIG
 * -------------------------------------------------------------------------------------------------
 */
PF.obj.config = {
    base_url: "",
    json_api: "/json/",
    listing: {
        items_per_page: 24
    },
    animation: {
        easingFn: "ease",
        normal: 400,
        fast: 250
    }
};

/**
 * WINDOW VARS
 * -------------------------------------------------------------------------------------------------
 */

/**
 * LANGUAGE FUNCTIONS
 * -------------------------------------------------------------------------------------------------
 */
PF.obj.l10n = {};

/**
 * Get lang string by key
 * @argument string (lang key string)
 */
// pf: get_pf_lang
PF.fn._s = function (string, s) {
    var string;
    if (typeof string == "undefined") {
        return string;
    }
    if (
        typeof PF.obj.l10n !== "undefined" &&
        typeof PF.obj.l10n[string] !== "undefined"
    ) {
        string = PF.obj.l10n[string][0];
        if (typeof string == "undefined") {
            string = string;
        }
    } else {
        string = string;
    }
    string = string.toString();
    if (typeof s !== "undefined") {
        string = sprintf(string, s);
    }
    return string;
};

PF.fn._n = function (singular, plural, n) {
    var string;
    if (
        typeof PF.obj.l10n !== "undefined" &&
        typeof PF.obj.l10n[singular] !== "undefined"
    ) {
        string = PF.obj.l10n[singular][n == 1 ? 0 : 1];
    } else {
        string = n == 1 ? singular : plural;
    }
    string = typeof string == "undefined" ? singular : string.toString();
    if (typeof n !== "undefined") {
        string = sprintf(string, n);
    }
    return string;
};

/**
 * Extend Peafowl lang
 * Useful to add or replace strings
 * @argument strings obj
 */
// pf: extend_pf_lang
PF.fn.extend_lang = function (strings) {
    $.each(PF.obj.lang_strings, function (i, v) {
        if (typeof strings[i] !== "undefined") {
            $.extend(PF.obj.lang_strings[i], strings[i]);
        }
    });
};

/**
 * HELPER FUNCTIONS
 * -------------------------------------------------------------------------------------------------
 */

PF.fn.get_url_vars = function () {
    var match,
        pl = /\+/g, // Regex for replacing addition symbol with a space
        search = /([^&=]+)=?([^&]*)/g,
        decode = function (s) {
            return decodeURIComponent(escape(s.replace(pl, " ")));
        },
        query = window.location.search.substring(1),
        urlParams = {};

    while ((match = search.exec(query))) {
        urlParams[decode(match[1])] = decode(match[2]);
    }

    return urlParams;
};

PF.fn.get_url_var = function (name) {
    return PF.fn.get_url_vars()[name];
};

PF.fn.is_user_logged = function () {
    return $("#top-bar-user").exists(); // nota: default version
    // It should use backend conditional
};

PF.fn.generate_random_string = function (len) {
    if (typeof len == "undefined") len = 5;
    var text = "";
    var possible =
        "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for (var i = 0; i < len; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return text;
};

PF.fn.getDateTime = function () {
    var now = new Date();
    var year = now.getFullYear();
    var month = now.getMonth() + 1;
    var day = now.getDate();
    var hour = now.getHours();
    var minute = now.getMinutes();
    var second = now.getSeconds();
    if (month.toString().length == 1) {
        var month = "0" + month;
    }
    if (day.toString().length == 1) {
        var day = "0" + day;
    }
    if (hour.toString().length == 1) {
        var hour = "0" + hour;
    }
    if (minute.toString().length == 1) {
        var minute = "0" + minute;
    }
    if (second.toString().length == 1) {
        var second = "0" + second;
    }
    var dateTime =
        year + "-" + month + "-" + day + " " + hour + ":" + minute + ":" + second;
    return dateTime;
};

PF.fn.htmlEncode = function (value) {
    return $("<div/>")
        .text($.trim(value))
        .html();
};

PF.fn.nl2br = function (str) {
    var breakTag = "<br>";
    return (str + "").replace(
        /([^>\r\n]?)(\r\n|\n\r|\r|\n)/g,
        "$1" + breakTag + "$2"
    );
};

// https://raw.githubusercontent.com/johndwells/phpjs/master/functions/info/version_compare.js
PF.fn.versionCompare = function (v1, v2, operator) {
    this.php_js = this.php_js || {};
    this.php_js.ENV = this.php_js.ENV || {};
    // END REDUNDANT
    // Important: compare must be initialized at 0.
    var i = 0,
        x = 0,
        compare = 0,
        // vm maps textual PHP versions to negatives so they're less than 0.
        // PHP currently defines these as CASE-SENSITIVE. It is important to
        // leave these as negatives so that they can come before numerical versions
        // and as if no letters were there to begin with.
        // (1alpha is < 1 and < 1.1 but > 1dev1)
        // If a non-numerical value can't be mapped to this table, it receives
        // -7 as its value.
        vm = {
            dev: -6,
            alpha: -5,
            a: -5,
            beta: -4,
            b: -4,
            RC: -3,
            rc: -3,
            "#": -2,
            p: 1,
            pl: 1
        },
        // This function will be called to prepare each version argument.
        // It replaces every _, -, and + with a dot.
        // It surrounds any nonsequence of numbers/dots with dots.
        // It replaces sequences of dots with a single dot.
        //    version_compare('4..0', '4.0') == 0
        // Important: A string of 0 length needs to be converted into a value
        // even less than an unexisting value in vm (-7), hence [-8].
        // It's also important to not strip spaces because of this.
        //   version_compare('', ' ') == 1
        prepVersion = function (v) {
            v = ("" + v).replace(/[_\-+]/g, ".");
            v = v.replace(/([^.\d]+)/g, ".$1.").replace(/\.{2,}/g, ".");
            return !v.length ? [-8] : v.split(".");
        };
    // This converts a version component to a number.
    // Empty component becomes 0.
    // Non-numerical component becomes a negative number.
    // Numerical component becomes itself as an integer.
    numVersion = function (v) {
        return !v ? 0 : isNaN(v) ? vm[v] || -7 : parseInt(v, 10);
    };
    v1 = prepVersion(v1);
    v2 = prepVersion(v2);
    x = Math.max(v1.length, v2.length);
    for (i = 0; i < x; i++) {
        if (v1[i] == v2[i]) {
            continue;
        }
        v1[i] = numVersion(v1[i]);
        v2[i] = numVersion(v2[i]);
        if (v1[i] < v2[i]) {
            compare = -1;
            break;
        } else if (v1[i] > v2[i]) {
            compare = 1;
            break;
        }
    }
    if (!operator) {
        return compare;
    }

    // Important: operator is CASE-SENSITIVE.
    // "No operator" seems to be treated as "<."
    // Any other values seem to make the function return null.
    switch (operator) {
        case ">":
        case "gt":
            return compare > 0;
        case ">=":
        case "ge":
            return compare >= 0;
        case "<=":
        case "le":
            return compare <= 0;
        case "==":
        case "=":
        case "eq":
            return compare === 0;
        case "<>":
        case "!=":
        case "ne":
            return compare !== 0;
        case "":
        case "<":
        case "lt":
            return compare < 0;
        default:
            return null;
    }
};

/**
 * Basename
 * http://stackoverflow.com/questions/3820381/need-a-basename-function-in-javascript
 */
PF.fn.baseName = function (str) {
    var base = new String(str).substring(str.lastIndexOf("/") + 1);
    if (base.lastIndexOf(".") != -1) {
        base = base.substring(0, base.lastIndexOf("."));
    }
    return base;
};

// https://stackoverflow.com/a/8809472
PF.fn.guid = function () {
    var d = new Date().getTime();
    if (
        typeof performance !== "undefined" &&
        typeof performance.now === "function"
    ) {
        d += performance.now(); //use high-precision timer if available
    }
    return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function (c) {
        var r = (d + Math.random() * 16) % 16 | 0;
        d = Math.floor(d / 16);
        return (c === "x" ? r : (r & 0x3) | 0x8).toString(16);
    });
};

PF.fn.md5 = function (string) {
    return SparkMD5.hash(string);
};

/**
 * dataURI to BLOB
 * http://stackoverflow.com/questions/4998908/convert-data-uri-to-file-then-append-to-formdata
 */
PF.fn.dataURItoBlob = function (dataURI) {
    // convert base64/URLEncoded data component to raw binary data held in a string
    var byteString;
    if (dataURI.split(",")[0].indexOf("base64") >= 0) {
        byteString = atob(dataURI.split(",")[1]);
    } else {
        byteString = unescape(dataURI.split(",")[1]);
    }
    // separate out the mime component
    var mimeString = dataURI
        .split(",")[0]
        .split(":")[1]
        .split(";")[0];
    // write the bytes of the string to a typed array
    var ia = new Uint8Array(byteString.length);
    for (var i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }
    return new Blob([ia], { type: mimeString });
};

/**
 * Get the min and max value from 1D array
 */
Array.min = function (array) {
    return Math.min.apply(Math, array);
};
Array.max = function (array) {
    return Math.max.apply(Math, array);
};

/**
 * Return the sum of all the values in a 1D array
 */
Array.sum = function (array) {
    return array.reduce(function (pv, cv) {
        return cv + pv;
    });
};

/**
 * Return the size of an object
 */
Object.size = function (obj) {
    var size = 0,
        key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

/**
 * Flatten an object
 */
Object.flatten = function (obj, prefix) {
    if (typeof prefix == "undefined") {
        var prefix = "";
    }
    var result = {};
    $.each(obj, function (key, value) {
        if (value !== null && typeof value == "object") {
            result = $.extend({}, result, Object.flatten(value, prefix + key + "_"));
        } else {
            result[prefix + key] = value;
        }
    });

    return result;
};

/**
 * Tells if the string is a number or not
 */
String.prototype.isNumeric = function () {
    return !isNaN(parseFloat(this)) && isFinite(this);
};

/**
 * Repeats an string
 */
String.prototype.repeat = function (num) {
    return new Array(num + 1).join(this);
};

/**
 * Ucfirst
 */
String.prototype.capitalizeFirstLetter = function () {
    return this.charAt(0).toUpperCase() + this.slice(1);
};

/**
 * Replace all
 */
String.prototype.replaceAll = function (search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, "g"), replacement);
};

/**
 * Tells if the string is a email or not
 */
String.prototype.isEmail = function () {
    var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(this);
};

// http://phpjs.org/functions/round/
String.prototype.getRounded = function (precision, mode) {
    var m, f, isHalf, sgn; // helper variables
    precision |= 0; // making sure precision is integer
    m = Math.pow(10, precision);
    value = this;
    value *= m;
    sgn = (value > 0) | -(value < 0); // sign of the number
    isHalf = value % 1 === 0.5 * sgn;
    f = Math.floor(value);

    if (isHalf) {
        switch (mode) {
            case "PHP_ROUND_HALF_DOWN":
                value = f + (sgn < 0); // rounds .5 toward zero
                break;
            case "PHP_ROUND_HALF_EVEN":
                value = f + (f % 2) * sgn; // rouds .5 towards the next even integer
                break;
            case "PHP_ROUND_HALF_ODD":
                value = f + !(f % 2); // rounds .5 towards the next odd integer
                break;
            default:
                value = f + (sgn > 0); // rounds .5 away from zero
        }
    }

    return (isHalf ? value : Math.round(value)) / m;
};

/**
 * Return bytes from Size + Suffix like "10 MB"
 */
String.prototype.getBytes = function () {
    var units = ["KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"],
        suffix = this.toUpperCase().substr(-2);
    if (units.indexOf(suffix) == -1) {
        return this;
    }
    var pow_factor = units.indexOf(suffix) + 1;
    return parseFloat(this) * Math.pow(1000, pow_factor);
};

/**
 * Return size formatted from size bytes
 */
String.prototype.formatBytes = function (round) {
    var bytes = parseInt(this),
        units = ["KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
    if (!$.isNumeric(this)) {
        return false;
    }
    if (bytes < 1000) return bytes + " B";
    if (typeof round == "undefined") var round = 2;
    for (var i = 0; i < units.length; i++) {
        var multiplier = Math.pow(1000, i + 1),
            threshold = multiplier * 1000;
        if (bytes < threshold) {
            var size = bytes / multiplier;
            return this.getRounded.call(size, round) + " " + units[i];
        }
    }
};

/**
 * Returns the image url.matches (multiple)
 */
String.prototype.match_image_urls = function () {
    return this.match(
        /\b(?:(http[s]?|ftp[s]):\/\/)?([^:\/\s]+)(:[0-9]+)?((?:\/\w+)*\/)([\w\-\.]+[^#?\s]+)([^#\s]*)?(#[\w\-]+)?\.(?:jpe?g|gif|png|bmp|webp)\b/gim
    );
};

String.prototype.match_urls = function () {
    return this.match(
        /\b(?:(http[s]?|ftp[s]):\/\/)?([^:\/\s]+)(:[0-9]+)?((?:\/\w+)*\/)([\w\-\.]+[^#?\s]+)([^#\s]*)?(#[\w\-]+)?\b/gim
    );
};

// Add ECMA262-5 Array methods if not supported natively
if (!("indexOf" in Array.prototype)) {
    Array.prototype.indexOf = function (find, i /*opt*/) {
        if (i === undefined) i = 0;
        if (i < 0) i += this.length;
        if (i < 0) i = 0;
        for (var n = this.length; i < n; i++) {
            if (i in this && this[i] === find) {
                return i;
            }
        }
        return -1;
    };
}

/**
 * Removes all the array duplicates without loosing the array order.
 */
Array.prototype.array_unique = function () {
    var result = [];
    $.each(this, function (i, e) {
        if ($.inArray(e, result) == -1) result.push(e);
    });
    return result;
};

PF.fn.parseQueryString = function (querystring) {
    var obj = {};
    if (typeof querystring == "undefined" || !querystring) {
        return obj
    }
    var pairs = querystring
            .replace(/^[\?|&]*/, "")
            .replace(/[&|\?]*$/, "")
            .split("&");
    for (var i = 0; i < pairs.length; i++) {
        var split = pairs[i].split("=");
        var key = decodeURIComponent(split[0]);
        var value = split[1] ? decodeURIComponent(split[1]) : null;
        if (obj.hasOwnProperty(key) && !value) {
            continue;
        }
        obj[key] = value;
    }
    return obj;
};

PF.fn.isHttpUrl = function (string) {
    let url;
    try {
        url = new URL(string);
    } catch (_) {
        return false;
    }
    return url.protocol === "http:" || url.protocol === "https:";
};

/**
 * @param string querystring_or_url
 * "?a=1&b=2"
 * "a=1&b=2"
 * "http(s)://example.com/?a=1&b=2"
 */
PF.fn.deparam = function (querystring_or_url) {
    if (typeof querystring_or_url == "undefined" || !querystring_or_url) return;
    var querystring = querystring_or_url.substring(querystring_or_url.indexOf("?") + 1);
    if(PF.fn.isHttpUrl(querystring_or_url) && querystring_or_url == querystring) {
        return {};
    }
    return PF.fn.parseQueryString(querystring);
};

// http://stackoverflow.com/a/1634841/1145912
String.prototype.removeURLParameter = function (key) {
    var deparam = PF.fn.deparam(this.toString());
    if (typeof deparam[key] !== "undefined") {
        delete deparam[key];
    }
    return decodeURIComponent($.param(deparam));
};

String.prototype.changeURLParameterValue = function (key, value) {
    var base = this.substring(0, this.indexOf("?"));
    if(base == "") {
        base = this;
    }
    var deparam = PF.fn.deparam(this.toString());
    deparam[key] = value;
    return base + "?" + decodeURIComponent($.param(deparam));
};

String.prototype.addURLParameterNoCache = function () {
    var url = this.toString();
    var params = PF.fn.deparam(url);
    if(Object.keys(params).length === 0) {
        var url = this.replace(/\/?$/, '/');
    }
    return url.changeURLParameterValue("nocache", new Date().getTime());
};

/**
 * Truncate the middle of the URL just like Firebug
 * From http://stackoverflow.com/questions/10903002/shorten-url-for-display-with-beginning-and-end-preserved-firebug-net-panel-st
 */
String.prototype.truncate_middle = function (l) {
    var l = typeof l != "undefined" ? l : 40,
        chunk_l = l / 2,
        url = this.replace(/https?:\/\//g, "");

    if (url.length <= l) {
        return url;
    }

    function shortString(s, l, reverse) {
        var stop_chars = [" ", "/", "&"],
            acceptable_shortness = l * 0.8, // When to start looking for stop characters
            reverse = typeof reverse != "undefined" ? reverse : false,
            s = reverse
                ? s
                    .split("")
                    .reverse()
                    .join("")
                : s,
            short_s = "";

        for (var i = 0; i < l - 1; i++) {
            short_s += s[i];
            if (i >= acceptable_shortness && stop_chars.indexOf(s[i]) >= 0) {
                break;
            }
        }
        if (reverse) {
            return short_s
                .split("")
                .reverse()
                .join("");
        }
        return short_s;
    }

    return (
        shortString(url, chunk_l, false) + "..." + shortString(url, chunk_l, true)
    );
};

/**
 * Compare 2 arrays/objects
 * http://stackoverflow.com/questions/1773069/using-jquery-to-compare-two-arrays
 */
jQuery.extend({
    compare: function (a, b) {
        var obj_str = "[object Object]",
            arr_str = "[object Array]",
            a_type = Object.prototype.toString.apply(a),
            b_type = Object.prototype.toString.apply(b);
        if (a_type !== b_type) {
            return false;
        } else if (a_type === obj_str) {
            return $.compareObject(a, b);
        } else if (a_type === arr_str) {
            return $.compareArray(a, b);
        }
        return a === b;
    },
    compareArray: function (arrayA, arrayB) {
        var a, b, i, a_type, b_type;
        if (arrayA === arrayB) {
            return true;
        }
        if (arrayA.length != arrayB.length) {
            return false;
        }
        a = jQuery.extend(true, [], arrayA);
        b = jQuery.extend(true, [], arrayB);
        a.sort();
        b.sort();
        for (i = 0, l = a.length; i < l; i += 1) {
            a_type = Object.prototype.toString.apply(a[i]);
            b_type = Object.prototype.toString.apply(b[i]);
            if (a_type !== b_type) {
                return false;
            }
            if ($.compare(a[i], b[i]) === false) {
                return false;
            }
        }
        return true;
    },
    compareObject: function (objA, objB) {
        var i, a_type, b_type;
        // Compare if they are references to each other
        if (objA === objB) {
            return true;
        }
        if (Object.keys(objA).length !== Object.keys(objB).length) {
            return false;
        }
        for (i in objA) {
            if (objA.hasOwnProperty(i)) {
                if (typeof objB[i] === "undefined") {
                    return false;
                } else {
                    a_type = Object.prototype.toString.apply(objA[i]);
                    b_type = Object.prototype.toString.apply(objB[i]);
                    if (a_type !== b_type) {
                        return false;
                    }
                }
            }
            if ($.compare(objA[i], objB[i]) === false) {
                return false;
            }
        }
        return true;
    }
});

/**
 * Tells if a selector exits in the dom
 */
jQuery.fn.exists = function () {
    return this.length > 0;
};

/**
 * Replace .svg for .png
 */
jQuery.fn.replace_svg = function () {
    if (!this.attr("src")) return;
    $(this).each(function () {
        $(this).attr(
            "src",
            $(this)
                .attr("src")
                .replace(".svg", ".png")
        );
    });
};

/**
 * Detect fluid layout
 * nota: deberia ir en PF
 */
jQuery.fn.is_fluid = function () {
    return true;
};

/**
 * jQueryfy the form data
 * Bind the attributes and values of form data to be manipulated by DOM fn
 */
jQuery.fn.bindFormData = function () {
    $(":input", this).each(function () {
        var safeVal = PF.fn.htmlEncode($(this).val());

        if ($(this).is("input")) {
            this.setAttribute("value", this.value);
            if (this.checked) {
                this.setAttribute("checked", "checked");
            } else {
                this.removeAttribute("checked");
            }
        }
        if ($(this).is("textarea")) {
            $(this).html(safeVal);
        }
        if ($(this).is("select")) {
            var index = this.selectedIndex,
                i = 0;
            $(this)
                .children("option")
                .each(function () {
                    if (i++ != index) {
                        this.removeAttribute("selected");
                    } else {
                        this.setAttribute("selected", "selected");
                    }
                });
        }
    });
    return this;
};

/** jQuery.formValues: get or set all of the name/value pairs from child input controls
 * @argument data {array} If included, will populate all child controls.
 * @returns element if data was provided, or array of values if not
 * http://stackoverflow.com/questions/1489486/jquery-plugin-to-serialize-a-form-and-also-restore-populate-the-form
 */
jQuery.fn.formValues = function (data) {
    var els = $(":input", this);
    if (typeof data != "object") {
        data = {};
        $.each(els, function () {
            if (
                this.name &&
                !this.disabled &&
                (this.checked ||
                    /select|textarea/i.test(this.nodeName) ||
                    /color|date|datetime|datetime-local|email|month|range|search|tel|time|url|week|text|number|hidden|password/i.test(
                        this.type
                    ))
            ) {
                if (this.name.match(/^.*\[\]$/) && this.checked) {
                    if (typeof data[this.name] == "undefined") {
                        data[this.name] = [];
                    }
                    data[this.name].push($(this).val());
                } else {
                    data[this.name] = $(this).val();
                }
            }
        });
        return data;
    } else {
        $.each(els, function () {
            if (this.name.match(/^.*\[\]$/) && typeof data[this.name] == "object") {
                $(this).prop("checked", data[this.name].indexOf($(this).val()) !== -1);
            } else {
                if (this.name && data[this.name]) {
                    if (/checkbox|radio/i.test(this.type)) {
                        $(this).prop("checked", data[this.name] == $(this).val());
                    } else {
                        $(this).val(data[this.name]);
                    }
                } else if (/checkbox|radio/i.test(this.type)) {
                    $(this).removeProp("checked");
                }
            }
        });
        return $(this);
    }
};

jQuery.fn.storeformData = function (dataname) {
    if (
        typeof dataname == "undefined" &&
        typeof $(this).attr("id") !== "undefined"
    ) {
        dataname = $(this).attr("id");
    }
    if (typeof dataname !== "undefined")
        $(this).data(dataname, $(this).formValues());
    return this;
};

/**
 * Compare the $.data values against the current DOM values
 * It relies in using $.data to store the previous value
 * Data must be stored using $.formValues()
 *
 * @argument dataname string name for the data key
 */
jQuery.fn.is_sameformData = function (dataname) {
    var $this = $(this);
    if (typeof dataname == "undefined") dataname = $this.attr("id");
    return jQuery.compare($this.formValues(), $this.data(dataname));
};

/**
 * Prevent non-numeric keydown
 * Allows only numeric keys to be entered on the target event
 */
jQuery.Event.prototype.keydown_numeric = function () {
    var e = this;

    if (e.shiftKey) {
        e.preventDefault();
        return false;
    }

    var key = e.charCode || e.keyCode,
        target = e.target,
        value = $(target).val() == "" ? 0 : parseInt($(target).val());

    if (key == 13) {
        // Allow enter key
        return true;
    }

    if (
        key == 46 ||
        key == 8 ||
        key == 9 ||
        key == 27 ||
        // Allow: Ctrl+A
        (key == 65 && e.ctrlKey === true) ||
        // Allow: home, end, left, right
        (key >= 35 && key <= 40)
    ) {
        // let it happen, don't do anything
        return true;
    } else {
        // Ensure that it is a number and stop the keypress
        if ((key < 48 || key > 57) && (key < 96 || key > 105)) {
            e.preventDefault();
        }
    }
};

/**
 * Detect canvas support
 */
PF.fn.is_canvas_supported = function () {
    var elem = document.createElement("canvas");
    return !!(elem.getContext && elem.getContext("2d"));
};

/**
 * Detect validity support
 */
PF.fn.is_validity_supported = function () {
    var i = document.createElement("input");
    return typeof i.validity === "object";
};

PF.fn.getScrollBarWidth = function () {
    var inner = document.createElement("p");
    inner.style.width = "100%";
    inner.style.height = "200px";

    var outer = document.createElement("div");
    outer.style.position = "absolute";
    outer.style.top = "0px";
    outer.style.left = "0px";
    outer.style.visibility = "hidden";
    outer.style.width = "200px";
    outer.style.height = "150px";
    outer.style.overflow = "hidden";
    outer.appendChild(inner);

    document.body.appendChild(outer);
    var w1 = inner.offsetWidth;
    outer.style.overflow = "scroll";
    var w2 = inner.offsetWidth;
    if (w1 == w2) w2 = outer.clientWidth;

    document.body.removeChild(outer);

    return w1 - w2;
};

PF.str.ScrollBarWidth = PF.fn.getScrollBarWidth();

/**
 * Updates the notifications button
 */
PF.fn.top_notifications_viewed = function () {
    var $top_bar_notifications = $("[data-action=top-bar-notifications]"),
        $notifications_lists = $(
            ".top-bar-notifications-list",
            $top_bar_notifications
        ),
        $notifications_count = $(".top-btn-number", $top_bar_notifications);

    if ($(".persistent", $top_bar_notifications).exists()) {
        $notifications_count
            .text($(".persistent", $top_bar_notifications).length)
            .addClass("on");
    } else {
        $notifications_count.removeClass("on");
    }
};

/**
 * bind tipTip for the $target with options
 * @argument $target selector or jQuery obj
 * @argument options obj
 */
PF.fn.bindtipTip = function ($target, options) {
    if (typeof $target == "undefined") $target = $("body");
    if ($target instanceof jQuery == false) $target = $($target);
    var bindtipTipoptions = {
        delay: 0,
        content: false,
        fadeIn: 0
    };
    if (typeof options !== "undefined") {
        if (typeof options.delay !== "undefined")
            bindtipTipoptions.delay = options.delay;
        if (typeof options.content !== "undefined")
            bindtipTipoptions.content = options.content;
        if (typeof options.content !== "undefined")
            bindtipTipoptions.fadeIn = options.fadeIn;
    }
    if ($target.attr("rel") !== "tooltip") $target = $("[rel=tooltip]", $target);

    $target.each(function () {
        if (
            (typeof $(this).attr("href") !== "undefined" ||
                typeof $(this).data("href") !== "undefined") &&
            PF.fn.isDevice(["phone", "phablet", "tablet"])
        ) {
            return true;
        }
        var position =
            typeof $(this).data("tiptip") == "undefined"
                ? "bottom"
                : $(this).data("tiptip");
        if (PF.fn.isDevice(["phone", "phablet"])) {
            position = "top";
        }
        $(this).tipTip({
            delay: bindtipTipoptions.delay,
            defaultPosition: position,
            content: bindtipTipoptions.content,
            fadeIn: bindtipTipoptions.fadeIn,
            fadeOut: 0
        });
    });
};

/**
 * form modal changed
 * Detects if the form modal (fullscreen) has changed or not
 * Note: It relies in that you save a serialized data to the
 */
PF.fn.form_modal_has_changed = function () {
    if ($(PF.obj.modal.selectors.root).is(":hidden")) return;
    if (typeof $("html").data("modal-form-values") == typeof undefined) return;
    var data_stored = $("html").data("modal-form-values");
    var data_modal = PF.fn.parseQueryString(
        $(":input:visible", PF.obj.modal.selectors.root).serialize()
    );
    var has_changed = false;
    var keys = $.extend({}, data_stored, data_modal);
    for (var k in keys) {
        if (data_stored[k] !== data_modal[k]) {
            has_changed = true;
            break;
        }
    }
    return has_changed;
};

/**
 * PEAFOWL CONDITIONALS
 * -------------------------------------------------------------------------------------------------
 */

PF.fn.is_listing = function () {
    return $(PF.obj.listing.selectors.content_listing).exists();
};

PF.fn.is_tabs = function () {
    return $(".content-tabs").exists();
};

/**
 * PEAFOWL EFFECTS
 * -------------------------------------------------------------------------------------------------
 */

/**
 * Shake effect
 * Shakes the element using CSS animations.
 * @argument callback fn
 */
jQuery.fn.shake = function (callback) {
    this.each(function (init) {
        var $this = $(this);
        if($this.data("shake") == 0) {
            return this;
        }
        $this.addClass("animate shake")
            .promise()
            .done(function () {
                setTimeout(function () {
                    $this.removeClass("shake")
                }, 820);
            });

    });
    if (typeof callback == "function") callback();
    return this;
};

/**
 * Highlight effect
 * Changes the background of the element to a highlight color and revert to original
 * @argument string (yellow|red|hex-color)
 */
jQuery.fn.highlight = function (color) {
    if (this.is(":animated") || !this.exists()) return this;
    if (typeof color == "undefined") color = "yellow";

    var fadecolor = color;
    var forecolor = "#333";
    switch (color) {
        case "yellow":
            fadecolor = "#FFFBA2";
            break;
        case "red":
            fadecolor = "#FF7F7F";
            break;
        default:
            fadecolor = color;
            break;
    }
    var base_background_color = $(this).css("background-color");
    var base_foreground_color = $(this).css("color");

    $(this)
        .css({
            color: forecolor,
            backgroundColor: fadecolor
        })
        .delay(100)
        .animate(
            { backgroundColor: base_background_color, color: base_foreground_color },
            150,
            function () {
                $(this).css({backgroundColor: base_background_color, color: base_foreground_color});
            }
        );
    return this;
};

/**
 * Peafowl slidedown effect
 * Bring the element using slideDown-type effect
 * @argument speed (fast|normal|slow|int)
 * @argument callback fn
 */
jQuery.fn.pf_slideDown = function (speed, callback) {
    var default_speed = "normal",
        this_length = $(this).length,
        css_prechanges,
        css_animation,
        animation_speed;

    if (typeof speed == "function") {
        callback = speed;
        speed = default_speed;
    }
    if (typeof speed == "undefined") {
        speed = default_speed;
    }

    $(this).each(function (index) {
        var this_css_top = parseInt($(this).css("top")),
            to_top = this_css_top > 0 ? this_css_top : 0;

        if (speed == 0) {
            (css_prechanges = { display: "block", opacity: 0 }),
                (css_animation = { opacity: 1 }),
                (animation_speed = jQuery.speed("fast").duration);
        } else {
            css_prechanges = {
                top: -$(this).outerHeight(true),
                opacity: 1,
                display: "block"
            };
            css_animation = { top: to_top };
            animation_speed = jQuery.speed(speed).duration;
        }

        $(this).data("originalTop", $(this).css("top"));
        $(this)
            .css(css_prechanges)
            .animate(css_animation, animation_speed, function () {
                if (index == this_length - 1) {
                    if (typeof callback == "function") {
                        callback();
                    }
                }
            });
    });

    return this;
};

jQuery.fn.is_in_viewport = function () {
    var rect = $(this)[0].getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <=
        (window.innerHeight ||
            document.documentElement.clientHeight) &&
        rect.right <=
        (window.innerWidth ||
            document.documentElement.clientWidth)
    );
};

jQuery.fn.is_within_viewport = function (height) {
    var rect = $(this)[0].getBoundingClientRect();
    if (typeof height == "undefined") {
        height = 0;
    }
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        (rect.top + height) <=
        (window.innerHeight ||
            document.documentElement.clientHeight) &&
        rect.right <=
        (window.innerWidth ||
            document.documentElement.clientWidth)
    );
};


/**
 * Visible on current window stuff
 */
jQuery.fn.getWindowCutoff = function () {
    var rect = {
        top: $(this).offset().top,
        left: $(this).offset().left,
        width: $(this).outerWidth(),
        height: $(this).outerHeight()
    };
    rect.right = rect.left + rect.width;
    rect.bottom = rect.top + rect.height;
    var detected = false;
    var cutoff = {
        top: rect.top > 0 ? 0 : rect.top,
        right: document.body.clientWidth - rect.right,
        bottom: document.body.clientHeight - rect.bottom,
        left: rect.left > 0 ? 0 : rect.left
    };
    for (var key in cutoff) {
        if (cutoff[key] < 0) {
            detected = true;
        } else {
            cutoff[key] = 0;
        }
    }
    if (!detected) return null;
    return cutoff;
};

/**
 * Scroll the window to the target.
 * @argument target selector
 * @argument callback fn
 */
PF.fn.scroll = function (target, callback) {
    if (typeof target == "function") {
        var callback = target,
            target = "";
    }

    var pxtop = parseInt($("body").css("margin-top"));
    if (pxtop == 0 && $(".top-bar-placeholder").exists()) {
        pxtop = $(".top-bar-placeholder").height();
    }

    if (!$(target).exists()) target = "html";
    $("body,html").animate(
        { scrollTop: $(target).offset().top - pxtop },
        "normal",
        function () {
            if (typeof callback == "function") callback();
        }
    );
};

PF.fn.close_pops = function (e) {
    $(".pop-box:visible").each(function() {
        $(this)
            .hide()
            .attr("style", "")
            .closest(".pop-btn")
            .removeClass("opened")
    });
    $("body").removeClass("pop-box-show pop-box-show--top");
    $("#pop-box-mask").remove();
};

/**
 * Bring up a nice growl-like alert
 */
PF.fn.growl = {
    selectors: {
        root: "#growl"
    },

    str: {
        timeout: null,
        timeoutcall: false
    },

    /**
     * Fires the growl
     * @argument options object
     */
    call: function (options) {
        if (typeof options == "undefined") return;
        if (typeof options == "string") {
            options = { message: options };
        }
        if (typeof options.message == "undefined") return;
        options.message = PF.fn.htmlEncode(options.message);
        var growl_options, $growl, growl_class, growl_color;
        growl_options = {
            message: options.message,
            insertTo: "body",
            where: "before",
            color: "default",
            css: {},
            classes: "",
            expires: 0,
            callback: function () { }
        };

        for (key in growl_options) {
            if (typeof options[key] !== "undefined") {
                if (key.match("/^(callback)$/")) {
                    if (typeof options[key] == "function") {
                        growl_options[key] = options[key];
                    }
                } else {
                    growl_options[key] = options[key];
                }
            }
        }

        if (!$(growl_options.insertTo).exists()) {
            growl_options.insertTo = "body";
        }

        if ($(PF.fn.growl.selectors.root).exists()) {
            if ($(PF.fn.growl.selectors.root).text() == growl_options.message) {
                $(PF.fn.growl.selectors.root).shake();
                return;
            }
            $(PF.fn.growl.selectors.root).remove();
        }

        $growl = $(
            '<div id="' +
            PF.fn.growl.selectors.root.replace("#", "") +
            '" class="growl animated">' +
            growl_options.message +
            '<span class="icon fas fa-times" data-action="close"></span></div>'
        )
            .css(growl_options.css)
            .addClass(growl_options.classes);

        growl_class = growl_options.insertTo !== "body" ? "static" : "";

        switch (growl_options.color) {
            case "dark":
                growl_color = "dark";
                break;
            default:
                growl_color = "";
                break;
        }

        $growl.addClass(growl_class + " " + growl_color);

        if (growl_options.where == "before") {
            $(growl_options.insertTo).prepend($growl.hide());
        } else {
            $(growl_options.insertTo).append($growl.hide());
        }

        $growl.pf_slideDown(growl_class == "static" ? 0 : 200, function () {
            if (typeof growl_options.callback == "function") {
                growl_options.callback();
            }
        });

        $(document).on("click", ".growl [data-action=close]", function (e) {
            PF.fn.growl.close(true);
        });

        if (growl_options.expires > 0) {
            if (typeof this.str.timeout == "number") {
                clearTimeout(this.str.timeout);
            }
            this.str.timeout = setTimeout(function () {
                PF.fn.growl.str.timeoutcall = true;
                PF.fn.growl.close();
            }, growl_options.expires);
        }
    },

    /**
     * Fires an expirable growl (will close after time)
     * @argument msg string
     * @argument time int (ms)
     */
    expirable: function (msg, time) {
        if (typeof msg == "undefined") return;
        if (typeof time == "undefined") time = 5000;
        PF.fn.growl.call({ message: msg, expires: time });
    },

    /**
     * Closes the growl
     * @argument callback fn
     */
    close: function (forced, callback) {
        var $growl = $(PF.fn.growl.selectors.root);

        if (forced) {
            this.str.timeout = null;
            this.str.timeoutcall = false;
            clearTimeout(this.str.timeout);
        }

        if (
            !$growl.exists() ||
            (typeof this.str.timeout == "number" && !this.str.timeoutcall)
        ) {
            return;
        }

        $growl.fadeOut("fast", function () {
            $(this).remove();
            if (typeof callback == "function") {
                callback();
            }
        });
    },
};

/**
 * Bring up a nice fullscreen modal
 */
PF.obj.modal = {
    type: "",
    selectors: {
        root: "#fullscreen-modal",
        box: "#fullscreen-modal-box",
        body: "#fullscreen-modal-body",
        login: "[data-modal=login]",
        changes_confirm: "#fullscreen-changes-confirm",
        btn_container: ".btn-container",
        close_buttons:
            ".close-modal,.cancel-modal,[data-action=cancel],[data-action-close]",
        submit_button: "[data-action=submit]",
        growl_placeholder: "#fullscreen-growl-placeholder"
    },
    ajax: {
        url: "",
        deferred: {}
    },
    locked: false,
    form_data: {},
    XHR: {},
    prevented: false
};
PF.obj.modal.$close_buttons = $(
    PF.obj.modal.selectors.close_buttons,
    PF.obj.modal.selectors.root
);

PF.fn.modal = {
    str: {
        transition: "all " + PF.obj.config.animation.fast + "ms " + PF.obj.config.animation.easingFn
    },

    /**
     * Fires the modal
     * @argument options object
     */
    call: function (options) {
        var modal_options, modal_base_template, modal_message;

        if (typeof options == "undefined") return;
        if (
            typeof options.template !== "undefined" &&
            typeof options.type == "undefined"
        )
            options.type = "html";
        if (
            (typeof options.title == "undefined" ||
                typeof options.message == "undefined") &&
            (options.type !== "login" && options.type !== "html")
        )
            return;

        PF.fn.growl.close(true);

        modal_options = {
            forced: false,
            type: "confirm",
            title: options.title,
            message: options.message,
            html: false,
            template: options.template,
            buttons: true,
            button_submit: PF.fn._s("Submit"),
            txt_or: PF.fn._s("or"),
            button_cancel: PF.fn._s("cancel"),
            ajax: { url: null, data: null, deferred: {} },
            confirm: function () { },
            cancel: function () {
                PF.fn.modal.close();
            },
            load: function () { },
            callback: function () { }
        };

        for (key in modal_options) {
            if (typeof options[key] !== "undefined") {
                if (/^cancel|confirm|callback$/.test(key)) {
                    if (typeof options[key] == "function") {
                        modal_options[key] = options[key];
                    }
                } else {
                    modal_options[key] = options[key];
                }
            }
        }

        if (
            typeof options.ajax !== "undefined" &&
            !options.ajax.url &&
            options.ajax.deferred
        ) {
            modal_options.ajax.url = PF.obj.config.json_api;
        }

        if (modal_options.type == "login") {
            modal_options.buttons = false;
        }

        if (modal_options.type == "confirm") {
            modal_options.button_submit = PF.fn._s("Confirm");
        }

        var overlay_background = "black";
        var modal_base_template = [
            '<div id="',
            PF.obj.modal.selectors.root.replace("#", ""),
            '"class="fullscreen ' + overlay_background + '"><div id="',
            PF.obj.modal.selectors.box.replace("#", ""),
            '"class="clickable"><div id="',
            PF.obj.modal.selectors.body.replace("#", ""),
            '">%MODAL_BODY%</div>%MODAL_BUTTONS%<span class="close-modal icon--close fas fa-times" data-action="close-modal" title="Esc"></span></div></div>'
        ].join("");

        var modal_buttons = modal_options.buttons
            ? [
                '<div class="',
                PF.obj.modal.selectors.btn_container.replace(".", ""),
                '"><button class="btn btn-input accent" data-action="submit" type="submit" title="Ctrl/Cmd + Enter">',
                '<span class="btn-icon fas fa-check-circle user-select-none"></span>',
                '<span class="btn-text  user-select-none">',
                modal_options.button_submit,
                '</span>',
                '</button></div>'
            ].join("")
            : "";

        if (modal_options.type == "login") {
            modal_options.template =
                typeof modal_options.template == "undefined"
                    ? $(PF.obj.modal.selectors.login).html()
                    : modal_options.template;
        }

        var modalBodyHTML;

        switch (modal_options.type) {
            case "html":
            case "login":
                modalBodyHTML = modal_options.template;
                break;
            case "confirm":
            default:
                modal_message = modal_options.message;
                if (!modal_options.html) {
                    modal_message = "<p>" + modal_message + "</p>";
                }
                modalBodyHTML = "<h1>" + modal_options.title + "</h1>" + modal_message;
                break;
        }

        if (typeof modalBodyHTML == "undefined") {
            console.log("PF Error: Modal content is empty");
            return;
        }

        modal_base_template = modal_base_template
            .replace("%MODAL_BODY%", modalBodyHTML)
            .replace("%MODAL_BUTTONS%", modal_buttons)
            .replace(/template-tooltip/g, "tooltip");

        $(PF.obj.modal.selectors.root).remove();

        $("body").data("hasOverflowHidden", $("body").hasClass("overflow-hidden") && !$("body").hasClass("pop-box-show"));
        $("body")
            .prepend(modal_base_template)
            .addClass("overflow-hidden");

        this.fixScrollbars();

        $("[rel=tooltip]", PF.obj.modal.selectors.root).each(function () {
            PF.fn.bindtipTip(this, { content: $(this).data("title") });
        });

        if (
            $(
                ":button, input[type=submit], input[type=reset]",
                PF.obj.modal.selectors.root
            ).length > 0
        ) {
            var $form = $("form", PF.obj.modal.selectors.root);
            if ($form.exists()) {
                $form.append(
                    $(
                        $(
                            PF.obj.modal.selectors.btn_container,
                            PF.obj.modal.selectors.root
                        ).html()
                    ).wrapInner(PF.obj.modal.selectors.btn_container.replace(".", ""))
                );
                $(
                    PF.obj.modal.selectors.btn_container,
                    PF.obj.modal.selectors.root
                ).each(function () {
                    if (
                        !$(this)
                            .closest("form")
                            .exists()
                    ) {
                        $(this).remove();
                    }
                });
            } else {
                $(PF.obj.modal.selectors.box, PF.obj.modal.selectors.root).wrapInner(
                    "<form data-js><form />"
                );
            }
        }

        modal_options.callback();

        $(PF.obj.modal.selectors.box).css({
            transform: "scale(0.7)",
            opacity: 0,
            transition: PF.fn.modal.str.transition
        });
        $(PF.obj.modal.selectors.root).addClass("--show");
        setTimeout(function () {
            $(PF.obj.modal.selectors.root).css({ opacity: 1 });
            $(PF.obj.modal.selectors.box).css({ transform: "scale(1)", opacity: 1 });
            if (typeof PFrecaptchaCallback !== typeof undefined) {
                PFrecaptchaCallback();
            }
            setTimeout(function () {
                $("html").data(
                    "modal-form-values",
                    PF.fn.parseQueryString(
                        $(":input:visible", PF.obj.modal.selectors.root).serialize()
                    )
                );
                if (typeof modal_options.load == "function") {
                    modal_options.load();
                }
                $(PF.obj.modal.selectors.box).css({ transform: ""});
            }, PF.obj.config.animation.fast);
            PF.fn.modal.styleAware();
        }, 10);

        $(PF.obj.modal.selectors.root).on("click", function (e) {
            var $this = $(e.target),
                _this = this;
            if (PF.obj.modal.locked || $this.is(PF.obj.modal.selectors.root)) {
                return;
            }
            var isCloseButton = $this.is(PF.obj.modal.selectors.close_buttons)
                || $this.closest(PF.obj.modal.selectors.close_buttons).exists();
            var isSubmitButton = $this.is(PF.obj.modal.selectors.submit_button)
                || $this.closest(PF.obj.modal.selectors.submit_button).exists();
            var isButton = isCloseButton || isSubmitButton;
            if (
                $this.closest(PF.obj.modal.selectors.changes_confirm).exists() &&
                isButton
            ) {
                $(PF.obj.modal.selectors.changes_confirm).remove();

                if (isCloseButton) {
                    $(PF.obj.modal.selectors.box, _this).fadeIn("fast", function () {
                        $(this).css("transition", PF.fn.modal.str.transition);
                    });
                } else {
                    PF.fn.modal.close();
                }
            } else {
                if (
                    !$this.closest(".clickable").exists() ||
                    isCloseButton
                ) {
                    PF.fn.growl.close();
                    modal_options.cancel();
                }

                if (isSubmitButton) {
                    if (modal_options.confirm() === false) {
                        return false;
                    }
                    var modal_submit_continue = true;
                    if (
                        $("input, textarea, select", PF.obj.modal.selectors.root).not(
                            ":input[type=button], :input[type=submit], :input[type=reset]"
                        ).length > 0 &&
                        !PF.fn.form_modal_has_changed() &&
                        !modal_options.forced
                    ) {
                        modal_submit_continue = false;
                    }

                    if (modal_submit_continue) {
                        if (modal_options.ajax.url) {
                            var $btn_container = $(
                                PF.obj.modal.selectors.btn_container,
                                PF.obj.modal.selectors.root
                            );
                            PF.obj.modal.locked = true;

                            $btn_container
                                .first()
                                .clone()
                                .height($btn_container.height())
                                .html("")
                                .addClass("loading")
                                .appendTo(PF.obj.modal.selectors.root + " form");
                            $btn_container.hide();

                            PF.obj.modal.$close_buttons.hide();

                            var modal_loading_msg;

                            switch (PF.obj.modal.type) {
                                case "edit":
                                    modal_loading_msg = PF.fn._s("Saving");
                                    break;
                                case "confirm":
                                case "form":
                                default:
                                    modal_loading_msg = PF.fn._s("Sending");
                                    break;
                            }

                            PF.fn.loading.inline(
                                $(
                                    PF.obj.modal.selectors.btn_container + ".loading",
                                    PF.obj.modal.selectors.root
                                ),
                                { size: "small", message: modal_loading_msg, valign: "center" }
                            );

                            $(PF.obj.modal.selectors.root).disableForm();

                            if (
                                !$.isEmptyObject(PF.obj.modal.form_data) ||
                                (typeof options.ajax !== "undefined" &&
                                    typeof options.ajax.data == "undefined")
                            ) {
                                modal_options.ajax.data = PF.obj.modal.form_data;
                            }
                            PF.obj.modal.XHR = $.ajax({
                                url: modal_options.ajax.url,
                                type: "POST",
                                data: modal_options.ajax.data //PF.obj.modal.form_data // $.param ?
                            }).complete(function (XHR) {
                                PF.obj.modal.locked = false;

                                if (XHR.status == 200) {
                                    var success_fn =
                                        typeof modal_options.ajax.deferred !== "undefined" &&
                                            typeof modal_options.ajax.deferred.success !== "undefined"
                                            ? modal_options.ajax.deferred.success
                                            : null;

                                    if (typeof success_fn == "function") {
                                        PF.fn.modal.close(function () {
                                            if (typeof success_fn == "function") {
                                                success_fn(XHR);
                                            }
                                        });
                                    } else if (typeof success_fn == "object") {
                                        if (typeof success_fn.before == "function") {
                                            success_fn.before(XHR);
                                        }
                                        if (typeof success_fn.done == "function") {
                                            success_fn.done(XHR);
                                        }
                                    }
                                } else {
                                    $(PF.obj.modal.selectors.root).enableForm();
                                    $(
                                        PF.obj.modal.selectors.btn_container + ".loading",
                                        PF.obj.modal.selectors.root
                                    ).remove();
                                    $btn_container.css("display", "");

                                    if (
                                        typeof modal_options.ajax.deferred !== "undefined" &&
                                        typeof modal_options.ajax.deferred.error == "function"
                                    ) {
                                        modal_options.ajax.deferred.error(XHR);
                                    } else {
                                        var message = PF.fn._s(
                                            "An error occurred. Please try again later."
                                        );
                                        if(XHR.responseJSON.error.message) {
                                            message = XHR.responseJSON.error.message;
                                        }
                                        PF.fn.growl.call(message);
                                    }
                                }
                            });
                        } else {
                            // No ajax behaviour
                            PF.fn.modal.close(modal_options.callback());
                        }
                    }
                }
            }
        });
    },

    styleAware: function () {
        if(!$(PF.obj.modal.selectors.root).exists()) {
            return;
        }
        $(PF.obj.modal.selectors.root)
            .toggleClass(
                "--has-scrollbar",
                $(PF.obj.modal.selectors.root).hasScrollbar().vertical
            );
    },

    /**
     * Fires a confirm modal
     * @argument options object
     */
    confirm: function (options) {
        options.type = "confirm";
        if (typeof options.title == "undefined") {
            options.title = PF.fn._s("Confirm action");
        }
        PF.fn.modal.call(options);
    },

    /**
     * Fires a simple info modal
     */
    simple: function (options) {
        if (typeof options == "string") options = { message: options };
        if (typeof options.buttons == "undefined") options.buttons = false;
        if (typeof options.title == "undefined")
            options.title = PF.fn._s("information");
        PF.fn.modal.call(options);
    },

    fixScrollbars: function () {
        if (!$(PF.obj.modal.selectors.root).exists()) {
            return;
        }
        var $targets = {
            padding: $(".fixed, .position-fixed"),
            margin: $("html")
        };
        var properties = {};
        if (
            PF.str.ScrollBarWidth > 0 &&
            $("html").hasScrollbar().vertical &&
            !$("body").data("hasOverflowHidden")
        ) {
            properties.padding = PF.str.ScrollBarWidth + "px";
            properties.margin = PF.str.ScrollBarWidth + "px";
        } else {
            properties.padding = "";
            properties.margin = "";
        }
        $targets.padding.css({ paddingRight: properties.padding });
        $targets.margin.css({ marginRight: properties.margin });
    },

    /**
     * Closes the modal
     * @argument callback fn
     */
    close: function (callback) {
        if (!$(PF.obj.modal.selectors.root).exists()) {
            return;
        }
        PF.fn.growl.close(true);
        $("[rel=tooltip]", PF.obj.modal.selectors.root).tipTip("hide");
        $(PF.obj.modal.selectors.box).css({ transform: "scale(0.5)", opacity: 0 });
        $(PF.obj.modal.selectors.root).css({ opacity: 0 });
        setTimeout(function () {
            if (PF.str.ScrollBarWidth > 0 && $("html").hasScrollbar().vertical) {
                $(".fixed, .position-fixed").css({ paddingRight: "" });
            }
            $("html").css({ marginRight: "" });
            if (!$("body").data("hasOverflowHidden")) {
                $("html,body").removeClass("overflow-hidden");
            }
            $("body").removeData("hasOverflowHidden");
            $(PF.obj.modal.selectors.root).remove();
            if (typeof callback == "function") callback();
        }, PF.obj.config.animation.normal);
    }
};

PF.fn.keyFeedback = {
    enabled: false,
    timeout: {
        spawn: null,
        remove: null,
    },
    selectors: {
        root: "#key-feedback",
    },
    translate: {
        "ArrowLeft": "◄",
        "ArrowRight": "►",
        "Delete": "Del",
        "Escape": "Esc",
    },
    spawn: function(e) {
        if(this.enabled == false || PF.fn.isDevice(["phone", "phablet"])) {
            return;
        }
        var $el = $(PF.fn.keyFeedback.selectors.root);
        if(!$el.exists()) {
            $('body').append($('<div></div>').attr({id: "key-feedback", class: "key-feedback"}));
            $el = $(PF.fn.keyFeedback.selectors.root)
        }
        var message = [];
        if((e.ctrlKey || e.metaKey) && e.originalEvent.code === 'KeyV') {
            e = {
                type: "keydown",
                key: PF.fn._s("Paste")
            };
        }
        if(e.type === "contextmenu" && e.ctrlKey) {
            e.type = "click";
        }
        if(e.type === "contextmenu") {
            message.push(PF.fn._s("Right click"));
        } else {
            if(e.ctrlKey) {
                message.push('Ctrl');
            }
            if(e.metaKey) {
                message.push('⌘');
            }
            if(e.hasOwnProperty("key")) {
                var key = e.key.length === 1
                    ? e.key.toUpperCase()
                    : e.key;
                if(key in this.translate) {
                    key = this.translate[key];
                }
                message.push(key);
            }
        }
        if(e.type === "click") {
            message.push("click");
        }
        $el.html(message.join(" + ", message)).css("opacity", 1);
        clearTimeout(PF.fn.keyFeedback.timeout.spawn);
        clearTimeout(PF.fn.keyFeedback.timeout.remove);
        PF.fn.keyFeedback.timeout.spawn = setTimeout(function() {
            $el.css("opacity", 0);
            PF.fn.keyFeedback.timeout.remove = setTimeout(function() {
                $el.remove();
            }, 500)
        }, 1500);
    },
};

PF.fn.popup = function (options) {
    var settings = {
        height: options.height || 500,
        width: options.width || 650,
        scrollTo: 0,
        resizable: 0,
        scrollbars: 0,
        location: 0
    };

    settings.top = screen.height / 2 - settings.height / 2;
    settings.left = screen.width / 2 - settings.width / 2;

    var settings_ = "";
    for (var key in settings) {
        settings_ += key + "=" + settings[key] + ",";
    }
    settings_ = settings_.slice(0, -1); // remove the last comma

    window.open(options.href, "Popup", settings_);
    return;
};

/**
 * PEAFOWL FLUID WIDTH FIXER
 * -------------------------------------------------------------------------------------------------
 */
PF.fn.list_fluid_width = function () {
    if (!$("body").is_fluid()) return;

    var $content_listing = $(PF.obj.listing.selectors.content_listing_visible),
        $pad_content_listing = $(
            PF.obj.listing.selectors.pad_content,
            $content_listing
        ),
        $list_item = $(PF.obj.listing.selectors.list_item, $content_listing),
        list_item_width = $list_item.outerWidth(true),
        list_item_gutter = $list_item.outerWidth(true) - $list_item.width();

    PF.obj.listing.content_listing_ratio = parseInt(
        ($content_listing.width() + list_item_gutter) / list_item_width
    );

    if ($list_item.length < PF.obj.listing.content_listing_ratio) {
        $pad_content_listing.css("width", "100%");
        return;
    }
};

/**
 * PEAFOWL TABS
 * -------------------------------------------------------------------------------------------------
 */

PF.obj.tabs = {
    hashdata: {}
};

PF.fn.show_tab = function (tab) {
    if (typeof tab == "undefined") {
        return;
    }
    var $link = $("a[data-tab=" + tab + "]", ".content-tabs");
    var $tab_menu = $("[data-action=tab-menu]", $link.closest(".header"));
    $tab_menu.find("[data-content=current-tab-label]").text($link.text());
    $tab_menu.find('[data-content="tab-icon"]').attr("class", "").addClass(
        $link.find(".btn-icon").attr("class")
    );
    if ($tab_menu.is(":visible")) {
        $tab_menu.trigger("click");
    }

    var $this = $("a[data-tab=" + tab + "]", ".content-tabs");

    $("li", $this.closest("ul")).removeClass("current");
    $this.closest("li").addClass("current");

    var $tab_content_group = $("#tabbed-content-group");
    $target = $("#" + $this.data("tab"));

    $(".tabbed-content", $tab_content_group)
        .removeClass("visible")
        .addClass("hidden");
    $($target, $tab_content_group)
        .addClass("visible")
        .removeClass("hidden");

    $("[data-content=list-selection]")
        .addClass("hidden");
    $("[data-content=list-selection][data-tab=" + $this.data("tab") + "]")
        .removeClass("hidden");

    if ($tab_content_group.exists()) {
        var $list_item_target = $(
            PF.obj.listing.selectors.list_item + ":not(.jsly)",
            $target
        );

        if (
            $target.data("load") == "ajax" &&
            $target.data("empty") !== "true" &&
            !$(PF.obj.listing.selectors.list_item, $target).exists()
        ) {
            PF.fn.listing.queryString.stock_load();
            $target.html(PF.obj.listing.template.fill);
            PF.fn.listing.queryString.stock_new();
            PF.fn.listing.ajax();
            $target.addClass("jsly");
        } else {
            PF.fn.listing.queryString.stock_current();
            PF.fn.listing.columnizer(false, 0, false);
            $list_item_target.show();
        }
    }

    PF.fn.listing.columnizerQueue();

    if (
        $(PF.obj.listing.selectors.content_listing_visible).data("queued") == true
    ) {
        PF.fn.listing.columnizer(true, 0);
    }
};

/**
 * PEAFOWL LISTINGS
 * -------------------------------------------------------------------------------------------------
 */
PF.obj.listing = {
    columns: "",
    columns_number: 1,
    current_column: "",
    current_column: "",
    XHR: {},
    query_string: PF.fn.get_url_vars(),
    calling: false,
    content_listing_ratio: 1,
    selectors: {
        sort: ".sort-listing .current [data-sort]",
        content_listing: ".content-listing",
        content_listing_visible: ".content-listing:visible",
        content_listing_loading: ".content-listing-loading",
        content_listing_load_more: ".content-listing-more",
        content_listing_pagination: ".content-listing-pagination",
        empty_icon: ".icon.fas.fa-inbox",
        pad_content: ".pad-content-listing",
        list_item: ".list-item"
    },
    template: {
        fill: $("[data-template=content-listing]").html(),
        empty: $("[data-template=content-listing-empty]").html(),
        loading: $("[data-template=content-listing-loading]").html()
    }
};

PF.fn.listing = {};

PF.fn.listing.show = function (response, callback) {
    $content_listing = $("#content-listing-tabs").exists()
        ? $(
            PF.obj.listing.selectors.content_listing_visible,
            "#content-listing-tabs"
        )
        : $(PF.obj.listing.selectors.content_listing);
    var list_content = $content_listing.data("list");
    var item_detect = list_content === "tags"
        ? ".tag-container"
        : PF.obj.listing.selectors.list_item;
    var $targets = $(
        item_detect,
        $content_listing
    );
    PF.fn.loading.inline(PF.obj.listing.selectors.content_listing_loading);
    if (
        (
            typeof response !== "undefined"
            && $(response.html).length < PF.obj.config.listing.items_per_page
        )
        || $targets.length < PF.obj.config.listing.items_per_page
    ) {
        PF.fn.listing.removeLoader($content_listing);
    }
    if (
        $(
            PF.obj.listing.selectors.content_listing_pagination,
            $content_listing
        ).is("[data-type=classic]") ||
        !$("[data-action=load-more]", $content_listing).exists()
    ) {
        $(
            PF.obj.listing.selectors.content_listing_loading,
            $content_listing
        ).remove();
    }

    if(list_content === "tags") {
        $content_listing.addClass("jsly");
    } else {
        PF.fn.listing.columnizer(false, 0);
        $targets.show();
        PF.fn.listing.columnizer(true, 0);
        $targets.addClass("--show");
    }

    PF.obj.listing.calling = false;

    var visible_loading =
        $(
            PF.obj.listing.selectors.content_listing_loading,
            $content_listing
        ).exists() &&
        $(
            PF.obj.listing.selectors.content_listing_loading,
            $content_listing
        ).is_in_viewport();

    PF.obj.listing.show_load_more = visible_loading;

    $(PF.obj.listing.selectors.content_listing_loading, $content_listing)[
        (visible_loading ? "add" : "remove") + "Class"
    ]("hidden");
    $(PF.obj.listing.selectors.content_listing_load_more, $content_listing)[
        PF.obj.listing.show_load_more ? "show" : "hide"
    ]();

    if (PF.obj.listing.lockClickMore) {
        PF.obj.listing.lockClickMore = false;
    }

    if (typeof callback == "function") {
        callback();
    }
};

PF.fn.listing.removeLoader = function (obj) {
    var remove = [
        PF.obj.listing.selectors.content_listing_load_more,
        PF.obj.listing.selectors.content_listing_loading
    ];

    if (
        $(PF.obj.listing.selectors.content_listing_pagination, $content_listing).is(
            "[data-type=endless]"
        )
    ) {
        remove.push(PF.obj.listing.selectors.content_listing_pagination);
    }

    $.each(remove, function (i, v) {
        $(v, obj).remove();
    });
};

PF.fn.listing.queryString = {
    // Stock the querystring values from initial load
    stock_load: function () {
        var $content_listing = $(PF.obj.listing.selectors.content_listing_visible),
            params = PF.fn.parseQueryString($content_listing.data("params"));

        PF.obj.listing.params_hidden =
            typeof $content_listing.data("params-hidden") !== "undefined"
                ? PF.fn.parseQueryString($content_listing.data("params-hidden"))
                : null;

        if (typeof PF.obj.listing.query_string.action == "undefined") {
            PF.obj.listing.query_string.action =
                $content_listing.data("action") || "list";
        }
        if (typeof PF.obj.listing.query_string.list == "undefined") {
            PF.obj.listing.query_string.list = $content_listing.data("list");
        }
        if (typeof PF.obj.listing.query_string.sort == "undefined") {
            if (typeof params !== "undefined" && typeof params.sort !== "undefined") {
                PF.obj.listing.query_string.sort = params.sort;
            } else {
                PF.obj.listing.query_string.sort = $(
                    ":visible" + PF.obj.listing.selectors.sort
                ).data("sort");
            }
        }
        if (typeof PF.obj.listing.query_string.page == "undefined") {
            PF.obj.listing.query_string.page = 1;
        }
        $content_listing.data("page", PF.obj.listing.query_string.page);

        // Stock the real ajaxed hrefs for ajax loads
        $(PF.obj.listing.selectors.content_listing + "[data-load=ajax]").each(
            function () {
                var $sortable_switch = $(
                    "[data-tab=" +
                    $(this).attr("id") +
                    "]" +
                    PF.obj.listing.selectors.sort
                );
                var dataParams = PF.fn.parseQueryString($(this).data("params")),
                    dataParamsHidden = PF.fn.parseQueryString($(this).data("params-hidden")),
                    params = {
                        q: dataParams && dataParams.q ? dataParams.q : null,
                        list: $(this).data("list"),
                        sort: $sortable_switch.exists()
                            ? $sortable_switch.data("sort")
                            : dataParams && dataParams.sort
                                ? dataParams.sort
                                : null,
                        page: dataParams && dataParams.page ? dataParams.page : 1
                    };

                if (dataParamsHidden && dataParamsHidden.list) {
                    delete params.list;
                }

                for (var k in params) {
                    if (!params[k]) delete params[k];
                }
            }
        );

        // The additional params setted in data-params=""
        for (var k in params) {
            if (/action|list|sort|page/.test(k) == false) {
                PF.obj.listing.query_string[k] = params[k];
            }
        }

        if (typeof PF.obj.listing.params_hidden !== typeof undefined) {
            // The additional params setted in data-hidden-params=""
            for (var k in PF.obj.listing.params_hidden) {
                if (/action|list|sort|page/.test(k) == false) {
                    PF.obj.listing.query_string[k] = PF.obj.listing.params_hidden[k];
                }
            }
            PF.obj.listing.query_string["params_hidden"] = PF.obj.listing.params_hidden;
            // Add this key for legacy, params_hidden v3.9.0 intro*
            // PF.obj.listing.params_hidden["params_hidden"] = null;
        }
    },
    stock_new: function () {
        var $content_listing = $(PF.obj.listing.selectors.content_listing_visible),
            params = PF.fn.parseQueryString($content_listing.data("params"));

        if ($content_listing.data("offset")) {
            PF.obj.listing.query_string.offset = $content_listing.data("offset");
        } else {
            delete PF.obj.listing.query_string.offset;
        }
        PF.obj.listing.query_string.seek = '';
        PF.obj.listing.query_string.action =
            $content_listing.data("action") || "list";
        PF.obj.listing.query_string.list = $content_listing.data("list");

        if (typeof params !== "undefined" && typeof params.sort !== "undefined") {
            PF.obj.listing.query_string.sort = params.sort;
        } else {
            PF.obj.listing.query_string.sort = $(
                ":visible" + PF.obj.listing.selectors.sort
            ).data("sort");
        }

        PF.obj.listing.query_string.page = 1;
    },

    // Stock querystring values for static tab change
    stock_current: function () {
        this.stock_new();
        PF.obj.listing.query_string.page = $(
            PF.obj.listing.selectors.content_listing_visible
        ).data("page");
    }
};

// Initial load -> Stock the current querystring
PF.fn.listing.queryString.stock_load();

PF.fn.listing.ajax = function () {
    if (PF.obj.listing.calling == true) {
        return;
    }

    PF.obj.listing.calling = true;

    var $content_listing = $(PF.obj.listing.selectors.content_listing_visible);
    var $pad_content_listing = $(
        PF.obj.listing.selectors.pad_content,
        $content_listing
    );
    var $content_listing_load_more = $(
        PF.obj.listing.selectors.content_listing_load_more,
        $content_listing
    );

    $content_listing_load_more.hide();
    $(PF.obj.listing.selectors.content_listing_loading, $content_listing)
        .removeClass("visibility-hidden")
        .show();

    PF.obj.listing.XHR = $.ajax({
        type: "POST",
        data: $.param(
            $.extend({}, PF.obj.listing.query_string, $.ajaxSettings.data)
        )
    }).complete(function (XHR) {
        var response = XHR.responseJSON;
        var removePagination = function () {
            $(
                PF.obj.listing.selectors.content_listing_loading +
                "," +
                PF.obj.listing.selectors.content_listing_pagination +
                ":not([data-visibility=visible])",
                $content_listing
            ).remove();
        },
            setEmptyTemplate = function () {
                $content_listing
                    .data("empty", "true")
                    .html(PF.obj.listing.template.empty);
                $(
                    "[data-content=list-selection][data-tab=" +
                    $content_listing.attr("id") +
                    "]"
                ).addClass("disabled");
            };

        if (XHR.readyState == 4 && typeof response !== "undefined") {
            $(
                "[data-content=list-selection][data-tab=" +
                $content_listing.attr("id") +
                "]"
            ).removeClass("disabled");

            if (XHR.status !== 200) {
                var response_output =
                    typeof response.error !== "undefined" &&
                        typeof response.error.message !== "undefined"
                        ? response.error.message
                        : "Bad request";
                PF.fn.growl.call("Error: " + response_output);
                $content_listing.data("load", "");
            }
            if (
                (typeof response.html == "undefined" || response.html == "") &&
                $(PF.obj.listing.selectors.list_item, $content_listing).length == 0
            ) {
                setEmptyTemplate();
            }
            if (typeof response.html == "undefined" || response.html == "") {
                removePagination();
                PF.obj.listing.calling = false;
                if (typeof PF.fn.listing_end == "function") {
                    PF.fn.listing_end();
                }
                return;
            }
            $content_listing.data({
                load: "",
                page: PF.obj.listing.query_string.page
            });

            var url_object = $.extend({}, PF.obj.listing.query_string);
            for (var k in PF.obj.listing.params_hidden) {
                if (typeof url_object[k] !== "undefined") {
                    delete url_object[k];
                }
            }

            delete url_object["action"];

            for (var k in url_object) {
                if (!url_object[k]) delete url_object[k];
            }

            $("a[data-tab=" + $content_listing.attr("id") + "]").attr(
                "href",
                document.URL
            );
            var $append_target = $pad_content_listing;
            var $content_tags = $append_target.find(".content-tags").first();
            if($content_tags.exists()) {
                $append_target = $content_tags;
            }
            $append_target.append(response.html);

            var $loadMore =  $(PF.obj.listing.selectors.content_listing_visible).find(
                "[data-action=load-more]"
            );
            if(response.seekEnd !== '') {
                $loadMore.attr("data-seek", response.seekEnd);
            } else {
                $loadMore.remove();
            }

            PF.fn.listing.show(response, function () {
                $(
                    PF.obj.listing.selectors.content_listing_loading,
                    $content_listing
                ).addClass("visibility-hidden");
            });
        } else {
            // Network error, abort or something similar
            PF.obj.listing.calling = false;
            $content_listing.data("load", "");
            removePagination();
            if ($(PF.obj.listing.selectors.list_item, $content_listing).length == 0) {
                setEmptyTemplate();
            }
            if (XHR.readyState !== 0) {
                PF.fn.growl.call(
                    PF.fn._s("An error occurred. Please try again later.")
                );
            }
        }

        if (typeof PF.fn.listing.ajax.callback == "function") {
            PF.fn.listing.ajax.callback(XHR);
        }
    });
};

PF.fn.listing.columnizerQueue = function () {
    $(PF.obj.listing.selectors.content_listing + ":hidden").data("queued", true);
};

PF.fn.listing.refresh = function (animation_time) {
    PF.fn.listing.columnizer(true, animation_time, false);
};

var width = $(window).width();
PF.fn.listing.columnizer = function (forced, animation_time, hard_forced) {
    var device_to_columns = {
        // default
        phone: 1,
        phablet: 3,
        tablet: 4,
        laptop: 5,
        desktop: 6,
        largescreen: 7
    };

    if (typeof forced !== "boolean") var forced = false;
    if (typeof PF.obj.listing.mode == "undefined") forced = true;
    if (typeof hard_forced !== "boolean") {
        var hard_forced = false,
            default_hard_forced = true;
    } else {
        var default_hard_forced = false;
    }
    if (!hard_forced && default_hard_forced) {
        if (width !== $(window).width() || forced) {
            hard_forced = true;
        }
    }

    if (typeof animation_time == typeof undefined)
        var animation_time = PF.obj.config.animation.normal;

    var $container = $("#content-listing-tabs").exists()
        ? $(
            PF.obj.listing.selectors.content_listing_visible,
            "#content-listing-tabs"
        )
        : $(PF.obj.listing.selectors.content_listing),
        $pad_content_listing = $(PF.obj.listing.selectors.pad_content, $container),
        list_mode = "responsive",
        $list_item = $(
            forced || hard_forced
                ? PF.obj.listing.selectors.list_item
                : PF.obj.listing.selectors.list_item + ":not(.jsly)",
            $container
        );

    if (typeof PF.obj.config.listing.device_to_columns !== "undefined") {
        device_to_columns = $.extend(
            {},
            device_to_columns,
            PF.obj.config.listing.device_to_columns
        );
    }

    if ($container.data("device-columns")) {
        device_to_columns = $.extend(
            {},
            device_to_columns,
            $container.data("device-columns")
        );
    }

    PF.obj.listing.mode = list_mode;
    PF.obj.listing.device = PF.fn.getDeviceName();

    if (!$list_item.exists()) return;

    if (
        typeof $container.data("columns") !== "undefined" &&
        !forced &&
        !hard_forced
    ) {
        PF.obj.listing.columns = $container.data("columns");
        PF.obj.listing.columns_number = $container.data("columns").length - 1;
        PF.obj.listing.current_column = $container.data("current_column");
    } else {
        var $list_item_1st = $list_item.first();
        $list_item_1st.css("width", "");
        PF.obj.listing.columns = new Array();
        PF.obj.listing.columns_number = device_to_columns[PF.fn.getDeviceName()];
        for (i = 0; i < PF.obj.listing.columns_number; i++) {
            PF.obj.listing.columns[i + 1] = 0;
        }
        PF.obj.listing.current_column = 1;
    }

    $container
        .removeClass("small-cols")
        .addClass(PF.obj.listing.columns_number > 6 ? "small-cols" : "");

    $pad_content_listing.css("width", "100%");

    var delay = 0;

    $list_item.each(function (index) {
        $(this).addClass("jsly");

        var $list_item_img = $(".list-item-image", this),
            $list_item_src = $(".list-item-image img", this),
            $list_item_thumbs = $(".list-item-thumbs", this),
            isJslyLoaded = $list_item_src.hasClass("jsly-loaded");

        $list_item_src.show();

        if (hard_forced) {
            $(this).css({ top: "", left: "", height: "", position: "" });
            $list_item_img.css({ maxHeight: "", height: "" });
            $list_item_src
                .removeClass("jsly")
                .css({ width: "", height: "" })
                .parent()
                .css({
                    marginLeft: "",
                    marginTop: ""
                });
            $("li", $list_item_thumbs).css({ width: "", height: "" });
        }

        var width_responsive =
            PF.obj.listing.columns_number == 1
                ? "100%"
                : parseFloat(
                    (1 / PF.obj.listing.columns_number) *
                    $container.width() +
                    "px"
                );
        $(this).css("width", width_responsive);

        if (PF.obj.listing.current_column > PF.obj.listing.columns_number) {
            PF.obj.listing.current_column = 1;
        }

        $(this).attr("data-col", PF.obj.listing.current_column);

        if (!$list_item_src.exists()) {
            var empty = true;
            $list_item_src = $(".image-container .empty", this);
        }

        var already_shown = $(this).is(":visible");
        $list_item.show();

        var isFixed = $list_item_img.hasClass("fixed-size");

        var image = {
            w: parseFloat($list_item_src.attr("width")),
            h: parseFloat($list_item_src.attr("height"))
        };
        image.ratio = image.w / image.h;

        if (
            empty ||
            ($list_item_img.css("min-height") && !$list_item_src.hasClass("jsly"))
        ) {
            var col = {
                    w: $(this).width(),
                    h: isFixed ? $(this).width() : null
                },
                magicWidth = Math.min(image.w, image.w < col.w ? image.w : col.w);

            if (isFixed) {
                $list_item_img.css({ height: col.w }); // Sets the item container height
                if (image.ratio <= 3 && (image.ratio > 1 || image.ratio == 1)) {
                    // Landscape or square
                    image.h = Math.min(image.h, image.w < col.w ? image.w : col.w);
                    image.w = image.h * image.ratio;
                } else {
                    // Portrait
                    image.w = magicWidth;
                    image.h = image.w / image.ratio;
                }
                $list_item_img.css("min-height", 0);
            } else {
                // Fluid height
                image.w = magicWidth;
                if (image.ratio >= 3 || image.ratio < 1 || image.ratio == 1) {
                    // Portrait or square
                    image.h = image.w / image.ratio;
                } else {
                    // Landscape
                    image.h = Math.min(image.h, image.w);
                    image.w = image.h * image.ratio;
                }
                if (empty) {
                    image.h = col.w;
                }
                $list_item_img.css({ height: image.h }); // Fill some gaps
            }

            if ($list_item_src.width() == 0) {
                $list_item_src.css({
                    width: magicWidth,
                    height: magicWidth / image.ratio
                });
            }

            if ($(".image-container", this).is(".list-item-avatar-cover")) {
                $list_item_src.css(
                    isFixed
                        ? { width: "auto", height: "100%" }
                        : { width: "100%", height: "auto" }
                );
            }

            var list_item_src_pitfall_x = Math.max(
                $list_item_src.position().left * 2,
                0
            ),
                list_item_src_pitfall_y = Math.max(
                    $list_item_src.position().top * 2,
                    0
                );

            var pitfall_ratio_x = list_item_src_pitfall_x / $list_item_img.width(),
                pitfall_ratio_y = list_item_src_pitfall_y / $list_item_img.height();
            if (
                (list_item_src_pitfall_x > 0 || list_item_src_pitfall_y > 0)
                && (pitfall_ratio_x <= 0.25 || pitfall_ratio_y <= 0.25)
            ) {
                $list_item_img.addClass("--fit");
            }
            if ($list_item_thumbs.exists()) {
                $("li", $list_item_thumbs)
                    .css({ width: 100 / $("li", $list_item_thumbs).length + "%" })
                    .css({ height: $("li", $list_item_thumbs).width() });
            }

            if (!already_shown) {
                $list_item.hide();
            }
        }

        if (!$list_item_src.hasClass("jsly") && $(this).is(":hidden")) {
            $(this).css("top", "100%");
        }

        PF.obj.listing.columns[PF.obj.listing.current_column] += $(
            this
        ).outerHeight(true);

        if ($(this).is(":animated")) {
            animation_time = 0;
        }
        $(this).addClass("position-absolute");

        var new_left =
            $(this).outerWidth(true) * (PF.obj.listing.current_column - 1);
        var must_change_left = parseFloat($(this).css("left")) != new_left;
        if (must_change_left) {
            animate_grid = true;
            $(this).animate(
                {
                    left: new_left
                },
                animation_time
            );
        }

        var new_top =
            PF.obj.listing.columns[PF.obj.listing.current_column] -
            $(this).outerHeight(true);
        if (parseFloat($(this).css("top")) != new_top) {
            animate_grid = true;
            $(this).animate(
                {
                    top: new_top
                },
                animation_time
            );
            if (must_change_left) {
                delay = 1;
            }
        }

        if (already_shown) {
            $list_item.show();
        }

        PF.obj.listing.current_column++;
    });

    $container.data({
        columns: PF.obj.listing.columns,
        current_column: PF.obj.listing.current_column
    }).attr('data-columns', PF.obj.listing.columns_number);

    var content_listing_height = 0;
    $.each(PF.obj.listing.columns, function (i, v) {
        if (v > content_listing_height) {
            content_listing_height = v;
        }
    });

    PF.obj.listing.width = $container.width();

    if (typeof PF.obj.listing.height !== typeof undefined) {
        var old_listing_height = PF.obj.listing.height;
    }
    PF.obj.listing.height = content_listing_height;

    var do_listing_h_resize =
        typeof old_listing_height !== typeof undefined &&
        old_listing_height !== PF.obj.listing.height;

    if (!do_listing_h_resize) {
        $pad_content_listing.height(content_listing_height);
        PF.fn.list_fluid_width();
    }

    if (do_listing_h_resize) {
        $pad_content_listing.height(old_listing_height);
        setTimeout(function () {
            $pad_content_listing.animate(
                { height: content_listing_height },
                animation_time,
                function () {
                    PF.fn.list_fluid_width();
                }
            );
        }, animation_time * delay);
    }

    $container.data("list-mode", PF.obj.listing.mode);
    $(PF.obj.listing.selectors.content_listing_visible).data("queued", false);

    $container.addClass("jsly");
};

/**
 * PEAFOWL LOADERS
 * -------------------------------------------------------------------------------------------------
 */
PF.fn.loading = {
    spin: {
        small: {
            lines: 11,
            length: 0,
            width: 3,
            radius: 7,
            speed: 1,
            trail: 45,
            blocksize: 20
        }, // 20x20
        normal: {
            lines: 11,
            length: 0,
            width: 5,
            radius: 10,
            speed: 1,
            trail: 45,
            blocksize: 30
        }, // 30x30
        big: {
            lines: 11,
            length: 0,
            width: 7,
            radius: 13,
            speed: 1,
            trail: 45,
            blocksize: 40
        }, // 40x40
        huge: {
            lines: 11,
            length: 0,
            width: 9,
            radius: 16,
            speed: 1,
            trail: 45,
            blocksize: 50
        } // 50x50
    },
    inline: function ($target, options) {
        if (typeof $target == "undefined") return;

        if ($target instanceof jQuery == false) {
            var $target = $($target);
        }

        var defaultoptions = {
            size: "normal",
            color: $("body").css("color"),
            center: false,
            position: "absolute",
            shadow: false,
            valign: "top"
        };

        if (typeof options == "undefined") {
            options = defaultoptions;
        } else {
            for (var k in defaultoptions) {
                if (typeof options[k] == "undefined") {
                    options[k] = defaultoptions[k];
                }
            }
        }

        var size = PF.fn.loading.spin[options.size];

        PF.fn.loading.spin[options.size].color = options.color;
        PF.fn.loading.spin[options.size].shadow = options.shadow;

        $target
            .html(
                '<span class="loading-indicator"></span>' +
                (typeof options.message !== "undefined"
                    ? '<span class="loading-text">' + options.message + "</span>"
                    : "")
            )
            .css({
                "line-height": PF.fn.loading.spin[options.size].blocksize + "px"
            });

        $(".loading-indicator", $target)
            .css({
                width: PF.fn.loading.spin[options.size].blocksize,
                height: PF.fn.loading.spin[options.size].blocksize
            })
            .spin(PF.fn.loading.spin[options.size]);

        if (options.center) {
            $(".loading-indicator", $target.css("textAlign", "center")).css({
                position: options.position,
                top: "50%",
                insetInlineStart: "50%",
                marginTop: -(PF.fn.loading.spin[options.size].blocksize / 2),
                marginInlineStart: -(PF.fn.loading.spin[options.size].blocksize / 2)
            });
        }
        if (options.valign == "center") {
            $(".loading-indicator,.loading-text", $target).css(
                "marginTop",
                ($target.height() - PF.fn.loading.spin[options.size].blocksize) / 2 +
                "px"
            );
        }

        $(".spinner", $target).css({
            top: PF.fn.loading.spin[options.size].blocksize / 2 + "px",
            insetInlineStart: PF.fn.loading.spin[options.size].blocksize / 2 + "px"
        });
    },
    fullscreen: function () {
        $("body").append(
            '<div class="fullscreen" id="pf-fullscreen-loader"><div class="fullscreen-loader black-bkg"><span class="loading-txt">' +
            PF.fn._s("loading") +
            "</span></div></div>"
        );
        $(".fullscreen-loader", "#pf-fullscreen-loader").spin(
            PF.fn.loading.spin.huge
        );
        $("#pf-fullscreen-loader").css("opacity", 1);
    },
    destroy: function ($target) {
        var $loader_fs = $("#pf-fullscreen-loader"),
            $loader_os = $("#pf-onscreen-loader");

        if ($target == "fullscreen") $target = $loader_fs;
        if ($target == "onscreen") $target = $loader_os;

        if (typeof $target !== "undefined") {
            $target.remove();
        } else {
            $loader_fs.remove();
            $loader_os.remove();
        }
    }
};

/**
 * PEAFOWL FORM HELPERS
 * -------------------------------------------------------------------------------------------------
 */
jQuery.fn.disableForm = function () {
    $(this).data("disabled", true);
    $(":input", this).each(function () {
        $(this).attr("disabled", true);
    });
    return this;
};
jQuery.fn.enableForm = function () {
    $(this).data("disabled", false);
    $(":input", this).removeAttr("disabled");
    return this;
};

PF.fn.isDevice = function (device) {
    if (typeof device == "object") {
        var device = "." + device.join(",.");
    } else {
        var device = "." + device;
    }
    return $("html").is(device);
};

PF.fn.getDeviceName = function () {
    var current_device;
    $.each(PF.obj.devices, function (i, v) {
        if (PF.fn.isDevice(v)) {
            current_device = v;
            return true;
        }
    });
    return current_device;
};

PF.fn.topMenu = {
    vars: {
        $button: $("[data-action=top-bar-menu-full]", "#top-bar"),
        menu: "#menu-fullscreen",
        speed: PF.obj.config.animation.fast,
        menu_top:
            parseInt($("#top-bar").outerHeight()) +
            parseInt($("#top-bar").css("top")) +
            parseInt($("#top-bar").css("margin-top")) +
            parseInt($("#top-bar").css("margin-bottom")) -
            parseInt($("#top-bar").css("border-bottom-width")) +
            "px"
    },
    show: function (speed) {
        if ($("body").is(":animated")) return;

        if (typeof speed == "undefined") {
            var speed = this.vars.speed;
        }

        this.vars.$button.addClass("current");
        $("html").addClass("menu-fullscreen-visible");
        $("#top-bar")
            .append(
                $("<div/>", {
                    id: "menu-fullscreen",
                    class: "touch-scroll",
                    html: $("<div/>", {
                            class: "fullscreen black",
                        })
                })
                .css({
                    left: "-100%"
                })
                .append(
                    $("<ul/>", {
                        html: $(".top-bar-left").html() + $(".top-bar-right").html()
                    })
                )
            );

        var $menu = $(this.vars.menu);

        $(
            "li.phone-hide, li > .top-btn-text, li > .top-btn-text > span, li > a > .top-btn-text > span",
            $menu
        ).each(function () {
            $(this).removeClass("phone-hide");
        });
        $("[data-action=top-bar-menu-full]", $menu).remove();
        $(
            ".btn.black, .btn.default, .btn.blue, .btn.green, .btn.orange, .btn.red, .btn.transparent",
            $menu
        ).removeClass("btn black default blue green orange red transparent");

        setTimeout(function () {
            $menu.css({ transform: "translateX(100%)" });
            $(".fullscreen").css("opacity", 1);
        }, 1);
        setTimeout(function () {
            $menu.css({ transition: "none", transform: "", left: "" });
            $("html").css({ backgroundColor: "" });
        }, speed);
    },
    hide: function (speed) {
        if ($("body").is(":animated")) return;

        if (!$(this.vars.menu).is(":visible")) return;
        var $menu = $(this.vars.menu);
        if (typeof speed == "undefined") {
            var speed = this.vars.speed;
        }
        $menu.css({transition: ""});
        setTimeout(function () {
            $menu.css({
                transform: "translateX(-100%)"
            });
        }, 1);
        $("#top-bar").css("position", "");
        this.vars.$button.removeClass("current");
        $("html").removeClass("menu-fullscreen-visible");
        setTimeout(function () {
            $menu.remove();
        }, speed);
    }
};

PF.fn.form = {
    validateInput: function ($input) {
        if($input[0].checkValidity()) {
            return true;
        }
        $input.highlight();
        $("label", $input.closest(".input-label")).shake();
        return false;
    },
    validateForm: function ($form) {
        let validate = true;
        let _this = this;
        $(":input[name]:visible", $form).each(function () {
            validate = _this.validateInput($(this)) && validate;
        });
        if(!validate) {
            $form[0].reportValidity();
        }

        return validate;
    }
};

/**
 * JQUERY PLUGINS (strictly needed plugins)
 * -------------------------------------------------------------------------------------------------
 */

// http://phpjs.org/functions/sprintf/
function sprintf() {
    var e = /%%|%(\d+\$)?([-+\'#0 ]*)(\*\d+\$|\*|\d+)?(\.(\*\d+\$|\*|\d+))?([scboxXuideEfFgG])/g;
    var t = arguments;
    var n = 0;
    var r = t[n++];
    var i = function (e, t, n, r) {
        if (!n) {
            n = " ";
        }
        var i = e.length >= t ? "" : new Array((1 + t - e.length) >>> 0).join(n);
        return r ? e + i : i + e;
    };
    var s = function (e, t, n, r, s, o) {
        var u = r - e.length;
        if (u > 0) {
            if (n || !s) {
                e = i(e, r, o, n);
            } else {
                e = e.slice(0, t.length) + i("", u, "0", true) + e.slice(t.length);
            }
        }
        return e;
    };
    var o = function (e, t, n, r, o, u, a) {
        var f = e >>> 0;
        n = (n && f && { 2: "0b", 8: "0", 16: "0x" }[t]) || "";
        e = n + i(f.toString(t), u || 0, "0", false);
        return s(e, n, r, o, a);
    };
    var u = function (e, t, n, r, i, o) {
        if (r != null) {
            e = e.slice(0, r);
        }
        return s(e, "", t, n, i, o);
    };
    var a = function (e, r, a, f, l, c, h) {
        var p, d, v, m, g;
        if (e === "%%") {
            return "%";
        }
        var y = false;
        var b = "";
        var w = false;
        var E = false;
        var S = " ";
        var x = a.length;
        for (var T = 0; a && T < x; T++) {
            switch (a.charAt(T)) {
                case " ":
                    b = " ";
                    break;
                case "+":
                    b = "+";
                    break;
                case "-":
                    y = true;
                    break;
                case "'":
                    S = a.charAt(T + 1);
                    break;
                case "0":
                    w = true;
                    S = "0";
                    break;
                case "#":
                    E = true;
                    break;
            }
        }
        if (!f) {
            f = 0;
        } else if (f === "*") {
            f = +t[n++];
        } else if (f.charAt(0) == "*") {
            f = +t[f.slice(1, -1)];
        } else {
            f = +f;
        }
        if (f < 0) {
            f = -f;
            y = true;
        }
        if (!isFinite(f)) {
            throw new Error("sprintf: (minimum-)width must be finite");
        }
        if (!c) {
            c = "fFeE".indexOf(h) > -1 ? 6 : h === "d" ? 0 : undefined;
        } else if (c === "*") {
            c = +t[n++];
        } else if (c.charAt(0) == "*") {
            c = +t[c.slice(1, -1)];
        } else {
            c = +c;
        }
        g = r ? t[r.slice(0, -1)] : t[n++];
        switch (h) {
            case "s":
                return u(String(g), y, f, c, w, S);
            case "c":
                return u(String.fromCharCode(+g), y, f, c, w);
            case "b":
                return o(g, 2, E, y, f, c, w);
            case "o":
                return o(g, 8, E, y, f, c, w);
            case "x":
                return o(g, 16, E, y, f, c, w);
            case "X":
                return o(g, 16, E, y, f, c, w).toUpperCase();
            case "u":
                return o(g, 10, E, y, f, c, w);
            case "i":
            case "d":
                p = +g || 0;
                p = Math.round(p - (p % 1));
                d = p < 0 ? "-" : b;
                g = d + i(String(Math.abs(p)), c, "0", false);
                return s(g, d, y, f, w);
            case "e":
            case "E":
            case "f":
            case "F":
            case "g":
            case "G":
                p = +g;
                d = p < 0 ? "-" : b;
                v = ["toExponential", "toFixed", "toPrecision"][
                    "efg".indexOf(h.toLowerCase())
                ];
                m = ["toString", "toUpperCase"]["eEfFgG".indexOf(h) % 2];
                g = d + Math.abs(p)[v](c);
                return s(g, d, y, f, w)[m]();
            default:
                return e;
        }
    };
    return r.replace(e, a);
}

/**
 * TipTip
 * Copyright 2010 Drew Wilson
 * code.drewwilson.com/entry/tiptip-jquery-plugin
 *
 * Version 1.3(modified) - Updated: Jun. 23, 2011
 * http://drew.tenderapp.com/discussions/tiptip/70-updated-tiptip-with-new-features
 *
 * This TipTip jQuery plug-in is dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */
(function ($) {
    $.fn.tipTip = function (options) {
        var defaults = {
            activation: "hover",
            keepAlive: false,
            maxWidth: "200px",
            edgeOffset: 6,
            defaultPosition: "bottom",
            delay: 400,
            fadeIn: 200,
            fadeOut: 200,
            attribute: "title",
            content: false,
            enter: function () { },
            afterEnter: function () { },
            exit: function () { },
            afterExit: function () { },
            cssClass: ""
        };
        if ($("#tiptip_holder").length <= 0) {
            var tiptip_holder = $('<div id="tiptip_holder"></div>');
            var tiptip_content = $('<div id="tiptip_content"></div>');
            var tiptip_arrow = $('<div id="tiptip_arrow"></div>');
            $("body").append(
                tiptip_holder
                    .html(tiptip_content)
                    .prepend(tiptip_arrow.html('<div id="tiptip_arrow_inner"></div>'))
            );
        } else {
            var tiptip_holder = $("#tiptip_holder");
            var tiptip_content = $("#tiptip_content");
            var tiptip_arrow = $("#tiptip_arrow");
        }
        return this.each(function () {
            var org_elem = $(this),
                data = org_elem.data("tipTip"),
                opts = (data && data.options) || $.extend(defaults, options),
                callback_data = {
                    holder: tiptip_holder,
                    content: tiptip_content,
                    arrow: tiptip_arrow,
                    options: opts
                };
            if (data) {
                switch (options) {
                    case "show":
                        active_tiptip();
                        break;
                    case "hide":
                        deactive_tiptip();
                        break;
                    case "destroy":
                        org_elem.unbind(".tipTip").removeData("tipTip");
                        break;
                }
            } else {
                var timeout = false;
                org_elem.data("tipTip", { options: opts });
                if (opts.activation == "hover") {
                    org_elem
                        .bind("mouseenter.tipTip", function () {
                            active_tiptip();
                        })
                        .bind("mouseleave.tipTip", function () {
                            if (!opts.keepAlive) {
                                deactive_tiptip();
                            } else {
                                tiptip_holder.one("mouseleave.tipTip", function () {
                                    deactive_tiptip();
                                });
                            }
                        });
                } else {
                    if (opts.activation == "focus") {
                        org_elem
                            .bind("focus.tipTip", function () {
                                active_tiptip();
                            })
                            .bind("blur.tipTip", function () {
                                deactive_tiptip();
                            });
                    } else {
                        if (opts.activation == "click") {
                            org_elem
                                .bind("click.tipTip", function (e) {
                                    e.preventDefault();
                                    active_tiptip();
                                    return false;
                                })
                                .bind("mouseleave.tipTip", function () {
                                    if (!opts.keepAlive) {
                                        deactive_tiptip();
                                    } else {
                                        tiptip_holder.one("mouseleave.tipTip", function () {
                                            deactive_tiptip();
                                        });
                                    }
                                });
                        } else {
                            if (opts.activation == "manual") {
                            }
                        }
                    }
                }
            }
            function active_tiptip() {
                if (opts.enter.call(org_elem, callback_data) === false) {
                    return;
                }
                var org_title;
                if (opts.content) {
                    org_title = $.isFunction(opts.content)
                        ? opts.content.call(org_elem, callback_data)
                        : opts.content;
                } else {
                    org_title = opts.content = org_elem.attr(opts.attribute);
                    org_elem.removeAttr(opts.attribute);
                }
                if (!org_title) {
                    return;
                }
                tiptip_content.html(org_title);
                tiptip_holder
                    .hide()
                    .removeAttr("class")
                    .css({ margin: "0px", "max-width": opts.maxWidth });
                if (opts.cssClass) {
                    tiptip_holder.addClass(opts.cssClass);
                }
                tiptip_arrow.removeAttr("style");
                var top = parseInt(org_elem.offset()["top"]),
                    left = parseInt(org_elem.offset()["left"]),
                    org_width = parseInt(org_elem.outerWidth()),
                    org_height = parseInt(org_elem.outerHeight()),
                    tip_w = tiptip_holder.outerWidth(),
                    tip_h = tiptip_holder.outerHeight(),
                    w_compare = Math.round((org_width - tip_w) / 2),
                    h_compare = Math.round((org_height - tip_h) / 2),
                    marg_left = Math.round(left + w_compare),
                    marg_top = Math.round(top + org_height + opts.edgeOffset),
                    t_class = "",
                    arrow_top = "",
                    arrow_left = Math.round(tip_w - 12) / 2;
                if (opts.defaultPosition == "bottom") {
                    t_class = "_bottom";
                } else {
                    if (opts.defaultPosition == "top") {
                        t_class = "_top";
                    } else {
                        if (opts.defaultPosition == "left") {
                            t_class = "_left";
                        } else {
                            if (opts.defaultPosition == "right") {
                                t_class = "_right";
                            }
                        }
                    }
                }
                var right_compare = w_compare + left < parseInt($(window).scrollLeft()),
                    left_compare = tip_w + left > parseInt($(window).width());
                if (
                    (right_compare && w_compare < 0) ||
                    (t_class == "_right" && !left_compare) ||
                    (t_class == "_left" && left < tip_w + opts.edgeOffset + 5)
                ) {
                    t_class = "_right";
                    arrow_top = Math.round(tip_h - 13) / 2;
                    arrow_left = -12;
                    marg_left = Math.round(left + org_width + opts.edgeOffset);
                    marg_top = Math.round(top + h_compare);
                } else {
                    if (
                        (left_compare && w_compare < 0) ||
                        (t_class == "_left" && !right_compare)
                    ) {
                        t_class = "_left";
                        arrow_top = Math.round(tip_h - 13) / 2;
                        arrow_left = Math.round(tip_w);
                        marg_left = Math.round(left - (tip_w + opts.edgeOffset + 5));
                        marg_top = Math.round(top + h_compare);
                    }
                }
                var top_compare =
                    top + org_height + opts.edgeOffset + tip_h + 8 >
                    parseInt($(window).height() + $(window).scrollTop()),
                    bottom_compare = top + org_height - (opts.edgeOffset + tip_h + 8) < 0;
                if (
                    top_compare ||
                    (t_class == "_bottom" && top_compare) ||
                    (t_class == "_top" && !bottom_compare)
                ) {
                    if (t_class == "_top" || t_class == "_bottom") {
                        t_class = "_top";
                    } else {
                        t_class = t_class + "_top";
                    }
                    arrow_top = tip_h;
                    marg_top = Math.round(top - (tip_h + 5 + opts.edgeOffset));
                } else {
                    if (
                        bottom_compare | (t_class == "_top" && bottom_compare) ||
                        (t_class == "_bottom" && !top_compare)
                    ) {
                        if (t_class == "_top" || t_class == "_bottom") {
                            t_class = "_bottom";
                        } else {
                            t_class = t_class + "_bottom";
                        }
                        arrow_top = -12;
                        marg_top = Math.round(top + org_height + opts.edgeOffset);
                    }
                }
                if (t_class == "_right_top" || t_class == "_left_top") {
                    marg_top = marg_top + 5;
                } else {
                    if (t_class == "_right_bottom" || t_class == "_left_bottom") {
                        marg_top = marg_top - 5;
                    }
                }
                if (t_class == "_left_top" || t_class == "_left_bottom") {
                    marg_left = marg_left + 5;
                }
                tiptip_arrow.css({
                    "margin-left": arrow_left + "px",
                    "margin-top": arrow_top + "px"
                });
                tiptip_holder
                    .css({
                        "margin-left": marg_left + "px",
                        "margin-top": marg_top + "px"
                    })
                    .addClass("tip" + t_class);
                if (timeout) {
                    clearTimeout(timeout);
                }
                timeout = setTimeout(function () {
                    tiptip_holder.stop(true, true).fadeIn(opts.fadeIn);
                }, opts.delay);
                opts.afterEnter.call(org_elem, callback_data);
            }
            function deactive_tiptip() {
                if (opts.exit.call(org_elem, callback_data) === false) {
                    return;
                }
                if (timeout) {
                    clearTimeout(timeout);
                }
                tiptip_holder.fadeOut(opts.fadeOut);
                opts.afterExit.call(org_elem, callback_data);
            }
        });
    };
})(jQuery);

/**
 * jQuery UI Touch Punch 0.2.2
 * Copyright 2011, Dave Furfero
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * Depends: jquery.ui.widget jquery.ui.mouse
 */
(function (b) {
    b.support.touch = "ontouchend" in document;
    if (!b.support.touch) {
        return;
    }
    var c = b.ui.mouse.prototype,
        e = c._mouseInit,
        a;
    function d(g, h) {
        if (g.originalEvent.touches.length > 1) {
            return;
        }
        g.preventDefault();
        var i = g.originalEvent.changedTouches[0],
            f = document.createEvent("MouseEvents");
        f.initMouseEvent(
            h,
            true,
            true,
            window,
            1,
            i.screenX,
            i.screenY,
            i.clientX,
            i.clientY,
            false,
            false,
            false,
            false,
            0,
            null
        );
        g.target.dispatchEvent(f);
    }
    c._touchStart = function (g) {
        var f = this;
        if (a || !f._mouseCapture(g.originalEvent.changedTouches[0])) {
            return;
        }
        a = true;
        f._touchMoved = false;
        d(g, "mouseover");
        d(g, "mousemove");
        d(g, "mousedown");
    };
    c._touchMove = function (f) {
        if (!a) {
            return;
        }
        this._touchMoved = true;
        d(f, "mousemove");
    };
    c._touchEnd = function (f) {
        if (!a) {
            return;
        }
        d(f, "mouseup");
        d(f, "mouseout");
        if (!this._touchMoved) {
            d(f, "click");
        }
        a = false;
    };
    c._mouseInit = function () {
        var f = this;
        f.element
            .bind("touchstart", b.proxy(f, "_touchStart"))
            .bind("touchmove", b.proxy(f, "_touchMove"))
            .bind("touchend", b.proxy(f, "_touchEnd"));
        e.call(f);
    };
})(jQuery);

/**
 * fileOverview TouchSwipe - jQuery Plugin
 * version 1.6.5
 */
(function (a) {
    if (typeof define === "function" && define.amd && define.amd.jQuery) {
        define(["jquery"], a);
    } else {
        a(jQuery);
    }
})(function (e) {
    var o = "left",
        n = "right",
        d = "up",
        v = "down",
        c = "in",
        w = "out",
        l = "none",
        r = "auto",
        k = "swipe",
        s = "pinch",
        x = "tap",
        i = "doubletap",
        b = "longtap",
        A = "horizontal",
        t = "vertical",
        h = "all",
        q = 10,
        f = "start",
        j = "move",
        g = "end",
        p = "cancel",
        a = "ontouchstart" in window,
        y = "TouchSwipe";
    var m = {
        fingers: 1,
        threshold: 75,
        cancelThreshold: null,
        pinchThreshold: 20,
        maxTimeThreshold: null,
        fingerReleaseThreshold: 250,
        longTapThreshold: 500,
        doubleTapThreshold: 200,
        swipe: null,
        swipeLeft: null,
        swipeRight: null,
        swipeUp: null,
        swipeDown: null,
        swipeStatus: null,
        pinchIn: null,
        pinchOut: null,
        pinchStatus: null,
        click: null,
        tap: null,
        doubleTap: null,
        longTap: null,
        triggerOnTouchEnd: true,
        triggerOnTouchLeave: false,
        allowPageScroll: "auto",
        fallbackToMouseEvents: true,
        excludedElements: "label, button, input, select, textarea, a, .noSwipe"
    };
    e.fn.swipe = function (D) {
        var C = e(this),
            B = C.data(y);
        if (B && typeof D === "string") {
            if (B[D]) {
                return B[D].apply(this, Array.prototype.slice.call(arguments, 1));
            } else {
                e.error("Method " + D + " does not exist on jQuery.swipe");
            }
        } else {
            if (!B && (typeof D === "object" || !D)) {
                return u.apply(this, arguments);
            }
        }
        return C;
    };
    e.fn.swipe.defaults = m;
    e.fn.swipe.phases = {
        PHASE_START: f,
        PHASE_MOVE: j,
        PHASE_END: g,
        PHASE_CANCEL: p
    };
    e.fn.swipe.directions = { LEFT: o, RIGHT: n, UP: d, DOWN: v, IN: c, OUT: w };
    e.fn.swipe.pageScroll = { NONE: l, HORIZONTAL: A, VERTICAL: t, AUTO: r };
    e.fn.swipe.fingers = { ONE: 1, TWO: 2, THREE: 3, ALL: h };
    function u(B) {
        if (
            B &&
            (B.allowPageScroll === undefined &&
                (B.swipe !== undefined || B.swipeStatus !== undefined))
        ) {
            B.allowPageScroll = l;
        }
        if (B.click !== undefined && B.tap === undefined) {
            B.tap = B.click;
        }
        if (!B) {
            B = {};
        }
        B = e.extend({}, e.fn.swipe.defaults, B);
        return this.each(function () {
            var D = e(this);
            var C = D.data(y);
            if (!C) {
                C = new z(this, B);
                D.data(y, C);
            }
        });
    }
    function z(a0, aq) {
        var av = a || !aq.fallbackToMouseEvents,
            G = av ? "touchstart" : "mousedown",
            au = av ? "touchmove" : "mousemove",
            R = av ? "touchend" : "mouseup",
            P = av ? null : "mouseleave",
            az = "touchcancel";
        var ac = 0,
            aL = null,
            Y = 0,
            aX = 0,
            aV = 0,
            D = 1,
            am = 0,
            aF = 0,
            J = null;
        var aN = e(a0);
        var W = "start";
        var T = 0;
        var aM = null;
        var Q = 0,
            aY = 0,
            a1 = 0,
            aa = 0,
            K = 0;
        var aS = null;
        try {
            aN.bind(G, aJ);
            aN.bind(az, a5);
        } catch (ag) {
            e.error("events not supported " + G + "," + az + " on jQuery.swipe");
        }
        this.enable = function () {
            aN.bind(G, aJ);
            aN.bind(az, a5);
            return aN;
        };
        this.disable = function () {
            aG();
            return aN;
        };
        this.destroy = function () {
            aG();
            aN.data(y, null);
            return aN;
        };
        this.option = function (a8, a7) {
            if (aq[a8] !== undefined) {
                if (a7 === undefined) {
                    return aq[a8];
                } else {
                    aq[a8] = a7;
                }
            } else {
                e.error("Option " + a8 + " does not exist on jQuery.swipe.options");
            }
            return null;
        };
        function aJ(a9) {
            if (ax()) {
                return;
            }
            if (e(a9.target).closest(aq.excludedElements, aN).length > 0) {
                return;
            }
            var ba = a9.originalEvent ? a9.originalEvent : a9;
            var a8,
                a7 = a ? ba.touches[0] : ba;
            W = f;
            if (a) {
                T = ba.touches.length;
            } else {
                a9.preventDefault();
            }
            ac = 0;
            aL = null;
            aF = null;
            Y = 0;
            aX = 0;
            aV = 0;
            D = 1;
            am = 0;
            aM = af();
            J = X();
            O();
            if (!a || (T === aq.fingers || aq.fingers === h) || aT()) {
                ae(0, a7);
                Q = ao();
                if (T == 2) {
                    ae(1, ba.touches[1]);
                    aX = aV = ap(aM[0].start, aM[1].start);
                }
                if (aq.swipeStatus || aq.pinchStatus) {
                    a8 = L(ba, W);
                }
            } else {
                a8 = false;
            }
            if (a8 === false) {
                W = p;
                L(ba, W);
                return a8;
            } else {
                ak(true);
            }
            return null;
        }
        function aZ(ba) {
            var bd = ba.originalEvent ? ba.originalEvent : ba;
            if (W === g || W === p || ai()) {
                return;
            }
            var a9,
                a8 = a ? bd.touches[0] : bd;
            var bb = aD(a8);
            aY = ao();
            if (a) {
                T = bd.touches.length;
            }
            W = j;
            if (T == 2) {
                if (aX == 0) {
                    ae(1, bd.touches[1]);
                    aX = aV = ap(aM[0].start, aM[1].start);
                } else {
                    aD(bd.touches[1]);
                    aV = ap(aM[0].end, aM[1].end);
                    aF = an(aM[0].end, aM[1].end);
                }
                D = a3(aX, aV);
                am = Math.abs(aX - aV);
            }
            if (T === aq.fingers || aq.fingers === h || !a || aT()) {
                aL = aH(bb.start, bb.end);
                ah(ba, aL);
                ac = aO(bb.start, bb.end);
                Y = aI();
                aE(aL, ac);
                if (aq.swipeStatus || aq.pinchStatus) {
                    a9 = L(bd, W);
                }
                if (!aq.triggerOnTouchEnd || aq.triggerOnTouchLeave) {
                    var a7 = true;
                    if (aq.triggerOnTouchLeave) {
                        var bc = aU(this);
                        a7 = B(bb.end, bc);
                    }
                    if (!aq.triggerOnTouchEnd && a7) {
                        W = ay(j);
                    } else {
                        if (aq.triggerOnTouchLeave && !a7) {
                            W = ay(g);
                        }
                    }
                    if (W == p || W == g) {
                        L(bd, W);
                    }
                }
            } else {
                W = p;
                L(bd, W);
            }
            if (a9 === false) {
                W = p;
                L(bd, W);
            }
        }
        function I(a7) {
            var a8 = a7.originalEvent;
            if (a) {
                if (a8.touches.length > 0) {
                    C();
                    return true;
                }
            }
            if (ai()) {
                T = aa;
            }
            a7.preventDefault();
            aY = ao();
            Y = aI();
            if (a6()) {
                W = p;
                L(a8, W);
            } else {
                if (
                    aq.triggerOnTouchEnd ||
                    (aq.triggerOnTouchEnd == false && W === j)
                ) {
                    W = g;
                    L(a8, W);
                } else {
                    if (!aq.triggerOnTouchEnd && a2()) {
                        W = g;
                        aB(a8, W, x);
                    } else {
                        if (W === j) {
                            W = p;
                            L(a8, W);
                        }
                    }
                }
            }
            ak(false);
            return null;
        }
        function a5() {
            T = 0;
            aY = 0;
            Q = 0;
            aX = 0;
            aV = 0;
            D = 1;
            O();
            ak(false);
        }
        function H(a7) {
            var a8 = a7.originalEvent;
            if (aq.triggerOnTouchLeave) {
                W = ay(g);
                L(a8, W);
            }
        }
        function aG() {
            aN.unbind(G, aJ);
            aN.unbind(az, a5);
            aN.unbind(au, aZ);
            aN.unbind(R, I);
            if (P) {
                aN.unbind(P, H);
            }
            ak(false);
        }
        function ay(bb) {
            var ba = bb;
            var a9 = aw();
            var a8 = aj();
            var a7 = a6();
            if (!a9 || a7) {
                ba = p;
            } else {
                if (
                    a8 &&
                    bb == j &&
                    (!aq.triggerOnTouchEnd || aq.triggerOnTouchLeave)
                ) {
                    ba = g;
                } else {
                    if (!a8 && bb == g && aq.triggerOnTouchLeave) {
                        ba = p;
                    }
                }
            }
            return ba;
        }
        function L(a9, a7) {
            var a8 = undefined;
            if (F() || S()) {
                a8 = aB(a9, a7, k);
            } else {
                if ((M() || aT()) && a8 !== false) {
                    a8 = aB(a9, a7, s);
                }
            }
            if (aC() && a8 !== false) {
                a8 = aB(a9, a7, i);
            } else {
                if (al() && a8 !== false) {
                    a8 = aB(a9, a7, b);
                } else {
                    if (ad() && a8 !== false) {
                        a8 = aB(a9, a7, x);
                    }
                }
            }
            if (a7 === p) {
                a5(a9);
            }
            if (a7 === g) {
                if (a) {
                    if (a9.touches.length == 0) {
                        a5(a9);
                    }
                } else {
                    a5(a9);
                }
            }
            return a8;
        }
        function aB(ba, a7, a9) {
            var a8 = undefined;
            if (a9 == k) {
                aN.trigger("swipeStatus", [a7, aL || null, ac || 0, Y || 0, T]);
                if (aq.swipeStatus) {
                    a8 = aq.swipeStatus.call(aN, ba, a7, aL || null, ac || 0, Y || 0, T);
                    if (a8 === false) {
                        return false;
                    }
                }
                if (a7 == g && aR()) {
                    aN.trigger("swipe", [aL, ac, Y, T]);
                    if (aq.swipe) {
                        a8 = aq.swipe.call(aN, ba, aL, ac, Y, T);
                        if (a8 === false) {
                            return false;
                        }
                    }
                    switch (aL) {
                        case o:
                            aN.trigger("swipeLeft", [aL, ac, Y, T]);
                            if (aq.swipeLeft) {
                                a8 = aq.swipeLeft.call(aN, ba, aL, ac, Y, T);
                            }
                            break;
                        case n:
                            aN.trigger("swipeRight", [aL, ac, Y, T]);
                            if (aq.swipeRight) {
                                a8 = aq.swipeRight.call(aN, ba, aL, ac, Y, T);
                            }
                            break;
                        case d:
                            aN.trigger("swipeUp", [aL, ac, Y, T]);
                            if (aq.swipeUp) {
                                a8 = aq.swipeUp.call(aN, ba, aL, ac, Y, T);
                            }
                            break;
                        case v:
                            aN.trigger("swipeDown", [aL, ac, Y, T]);
                            if (aq.swipeDown) {
                                a8 = aq.swipeDown.call(aN, ba, aL, ac, Y, T);
                            }
                            break;
                    }
                }
            }
            if (a9 == s) {
                aN.trigger("pinchStatus", [a7, aF || null, am || 0, Y || 0, T, D]);
                if (aq.pinchStatus) {
                    a8 = aq.pinchStatus.call(
                        aN,
                        ba,
                        a7,
                        aF || null,
                        am || 0,
                        Y || 0,
                        T,
                        D
                    );
                    if (a8 === false) {
                        return false;
                    }
                }
                if (a7 == g && a4()) {
                    switch (aF) {
                        case c:
                            aN.trigger("pinchIn", [aF || null, am || 0, Y || 0, T, D]);
                            if (aq.pinchIn) {
                                a8 = aq.pinchIn.call(aN, ba, aF || null, am || 0, Y || 0, T, D);
                            }
                            break;
                        case w:
                            aN.trigger("pinchOut", [aF || null, am || 0, Y || 0, T, D]);
                            if (aq.pinchOut) {
                                a8 = aq.pinchOut.call(
                                    aN,
                                    ba,
                                    aF || null,
                                    am || 0,
                                    Y || 0,
                                    T,
                                    D
                                );
                            }
                            break;
                    }
                }
            }
            if (a9 == x) {
                if (a7 === p || a7 === g) {
                    clearTimeout(aS);
                    if (V() && !E()) {
                        K = ao();
                        aS = setTimeout(
                            e.proxy(function () {
                                K = null;
                                aN.trigger("tap", [ba.target]);
                                if (aq.tap) {
                                    a8 = aq.tap.call(aN, ba, ba.target);
                                }
                            }, this),
                            aq.doubleTapThreshold
                        );
                    } else {
                        K = null;
                        aN.trigger("tap", [ba.target]);
                        if (aq.tap) {
                            a8 = aq.tap.call(aN, ba, ba.target);
                        }
                    }
                }
            } else {
                if (a9 == i) {
                    if (a7 === p || a7 === g) {
                        clearTimeout(aS);
                        K = null;
                        aN.trigger("doubletap", [ba.target]);
                        if (aq.doubleTap) {
                            a8 = aq.doubleTap.call(aN, ba, ba.target);
                        }
                    }
                } else {
                    if (a9 == b) {
                        if (a7 === p || a7 === g) {
                            clearTimeout(aS);
                            K = null;
                            aN.trigger("longtap", [ba.target]);
                            if (aq.longTap) {
                                a8 = aq.longTap.call(aN, ba, ba.target);
                            }
                        }
                    }
                }
            }
            return a8;
        }
        function aj() {
            var a7 = true;
            if (aq.threshold !== null) {
                a7 = ac >= aq.threshold;
            }
            return a7;
        }
        function a6() {
            var a7 = false;
            if (aq.cancelThreshold !== null && aL !== null) {
                a7 = aP(aL) - ac >= aq.cancelThreshold;
            }
            return a7;
        }
        function ab() {
            if (aq.pinchThreshold !== null) {
                return am >= aq.pinchThreshold;
            }
            return true;
        }
        function aw() {
            var a7;
            if (aq.maxTimeThreshold) {
                if (Y >= aq.maxTimeThreshold) {
                    a7 = false;
                } else {
                    a7 = true;
                }
            } else {
                a7 = true;
            }
            return a7;
        }
        function ah(a7, a8) {
            if (aq.allowPageScroll === l || aT()) {
                a7.preventDefault();
            } else {
                var a9 = aq.allowPageScroll === r;
                switch (a8) {
                    case o:
                        if ((aq.swipeLeft && a9) || (!a9 && aq.allowPageScroll != A)) {
                            a7.preventDefault();
                        }
                        break;
                    case n:
                        if ((aq.swipeRight && a9) || (!a9 && aq.allowPageScroll != A)) {
                            a7.preventDefault();
                        }
                        break;
                    case d:
                        if ((aq.swipeUp && a9) || (!a9 && aq.allowPageScroll != t)) {
                            a7.preventDefault();
                        }
                        break;
                    case v:
                        if ((aq.swipeDown && a9) || (!a9 && aq.allowPageScroll != t)) {
                            a7.preventDefault();
                        }
                        break;
                }
            }
        }
        function a4() {
            var a8 = aK();
            var a7 = U();
            var a9 = ab();
            return a8 && a7 && a9;
        }
        function aT() {
            return !!(aq.pinchStatus || aq.pinchIn || aq.pinchOut);
        }
        function M() {
            return !!(a4() && aT());
        }
        function aR() {
            var ba = aw();
            var bc = aj();
            var a9 = aK();
            var a7 = U();
            var a8 = a6();
            var bb = !a8 && a7 && a9 && bc && ba;
            return bb;
        }
        function S() {
            return !!(
                aq.swipe ||
                aq.swipeStatus ||
                aq.swipeLeft ||
                aq.swipeRight ||
                aq.swipeUp ||
                aq.swipeDown
            );
        }
        function F() {
            return !!(aR() && S());
        }
        function aK() {
            return T === aq.fingers || aq.fingers === h || !a;
        }
        function U() {
            return aM[0].end.x !== 0;
        }
        function a2() {
            return !!aq.tap;
        }
        function V() {
            return !!aq.doubleTap;
        }
        function aQ() {
            return !!aq.longTap;
        }
        function N() {
            if (K == null) {
                return false;
            }
            var a7 = ao();
            return V() && a7 - K <= aq.doubleTapThreshold;
        }
        function E() {
            return N();
        }
        function at() {
            return (T === 1 || !a) && (isNaN(ac) || ac === 0);
        }
        function aW() {
            return Y > aq.longTapThreshold && ac < q;
        }
        function ad() {
            return !!(at() && a2());
        }
        function aC() {
            return !!(N() && V());
        }
        function al() {
            return !!(aW() && aQ());
        }
        function C() {
            a1 = ao();
            aa = event.touches.length + 1;
        }
        function O() {
            a1 = 0;
            aa = 0;
        }
        function ai() {
            var a7 = false;
            if (a1) {
                var a8 = ao() - a1;
                if (a8 <= aq.fingerReleaseThreshold) {
                    a7 = true;
                }
            }
            return a7;
        }
        function ax() {
            return !!(aN.data(y + "_intouch") === true);
        }
        function ak(a7) {
            if (a7 === true) {
                aN.bind(au, aZ);
                aN.bind(R, I);
                if (P) {
                    aN.bind(P, H);
                }
            } else {
                aN.unbind(au, aZ, false);
                aN.unbind(R, I, false);
                if (P) {
                    aN.unbind(P, H, false);
                }
            }
            aN.data(y + "_intouch", a7 === true);
        }
        function ae(a8, a7) {
            var a9 = a7.identifier !== undefined ? a7.identifier : 0;
            aM[a8].identifier = a9;
            aM[a8].start.x = aM[a8].end.x = a7.pageX || a7.clientX;
            aM[a8].start.y = aM[a8].end.y = a7.pageY || a7.clientY;
            return aM[a8];
        }
        function aD(a7) {
            var a9 = a7.identifier !== undefined ? a7.identifier : 0;
            var a8 = Z(a9);
            a8.end.x = a7.pageX || a7.clientX;
            a8.end.y = a7.pageY || a7.clientY;
            return a8;
        }
        function Z(a8) {
            for (var a7 = 0; a7 < aM.length; a7++) {
                if (aM[a7].identifier == a8) {
                    return aM[a7];
                }
            }
        }
        function af() {
            var a7 = [];
            for (var a8 = 0; a8 <= 5; a8++) {
                a7.push({ start: { x: 0, y: 0 }, end: { x: 0, y: 0 }, identifier: 0 });
            }
            return a7;
        }
        function aE(a7, a8) {
            a8 = Math.max(a8, aP(a7));
            J[a7].distance = a8;
        }
        function aP(a7) {
            if (J[a7]) {
                return J[a7].distance;
            }
            return undefined;
        }
        function X() {
            var a7 = {};
            a7[o] = ar(o);
            a7[n] = ar(n);
            a7[d] = ar(d);
            a7[v] = ar(v);
            return a7;
        }
        function ar(a7) {
            return { direction: a7, distance: 0 };
        }
        function aI() {
            return aY - Q;
        }
        function ap(ba, a9) {
            var a8 = Math.abs(ba.x - a9.x);
            var a7 = Math.abs(ba.y - a9.y);
            return Math.round(Math.sqrt(a8 * a8 + a7 * a7));
        }
        function a3(a7, a8) {
            var a9 = (a8 / a7) * 1;
            return a9.toFixed(2);
        }
        function an() {
            if (D < 1) {
                return w;
            } else {
                return c;
            }
        }
        function aO(a8, a7) {
            return Math.round(
                Math.sqrt(Math.pow(a7.x - a8.x, 2) + Math.pow(a7.y - a8.y, 2))
            );
        }
        function aA(ba, a8) {
            var a7 = ba.x - a8.x;
            var bc = a8.y - ba.y;
            var a9 = Math.atan2(bc, a7);
            var bb = Math.round((a9 * 180) / Math.PI);
            if (bb < 0) {
                bb = 360 - Math.abs(bb);
            }
            return bb;
        }
        function aH(a8, a7) {
            var a9 = aA(a8, a7);
            if (a9 <= 45 && a9 >= 0) {
                return o;
            } else {
                if (a9 <= 360 && a9 >= 315) {
                    return o;
                } else {
                    if (a9 >= 135 && a9 <= 225) {
                        return n;
                    } else {
                        if (a9 > 45 && a9 < 135) {
                            return v;
                        } else {
                            return d;
                        }
                    }
                }
            }
        }
        function ao() {
            var a7 = new Date();
            return a7.getTime();
        }
        function aU(a7) {
            a7 = e(a7);
            var a9 = a7.offset();
            var a8 = {
                left: a9.left,
                right: a9.left + a7.outerWidth(),
                top: a9.top,
                bottom: a9.top + a7.outerHeight()
            };
            return a8;
        }
        function B(a7, a8) {
            return (
                a7.x > a8.left && a7.x < a8.right && a7.y > a8.top && a7.y < a8.bottom
            );
        }
    }
});

/**
 * Copyright (c) 2011-2013 Felix Gnass
 * Licensed under the MIT license
 */
//fgnass.github.com/spin.js#v1.3.2
(function (root, factory) {
    if (typeof exports == "object") {
        module.exports = factory();
    } else {
        if (typeof define == "function" && define.amd) {
            define(factory);
        } else {
            root.Spinner = factory();
        }
    }
})(this, function () {
    var prefixes = ["webkit", "Moz", "ms", "O"],
        animations = {},
        useCssAnimations;
    function createEl(tag, prop) {
        var el = document.createElement(tag || "div"),
            n;
        for (n in prop) {
            el[n] = prop[n];
        }
        return el;
    }
    function ins(parent) {
        for (var i = 1, n = arguments.length; i < n; i++) {
            parent.appendChild(arguments[i]);
        }
        return parent;
    }
    var sheet = (function () {
        var el = createEl("style", { type: "text/css" });
        ins(document.getElementsByTagName("head")[0], el);
        return el.sheet || el.styleSheet;
    })();
    function addAnimation(alpha, trail, i, lines) {
        var name = ["opacity", trail, ~~(alpha * 100), i, lines].join("-"),
            start = 0.01 + (i / lines) * 100,
            z = Math.max(1 - ((1 - alpha) / trail) * (100 - start), alpha),
            prefix = useCssAnimations
                .substring(0, useCssAnimations.indexOf("Animation"))
                .toLowerCase(),
            pre = (prefix && "-" + prefix + "-") || "";
        if (!animations[name]) {
            sheet.insertRule(
                "@" +
                pre +
                "keyframes " +
                name +
                "{0%{opacity:" +
                z +
                "}" +
                start +
                "%{opacity:" +
                alpha +
                "}" +
                (start + 0.01) +
                "%{opacity:1}" +
                ((start + trail) % 100) +
                "%{opacity:" +
                alpha +
                "}100%{opacity:" +
                z +
                "}}",
                sheet.cssRules.length
            );
            animations[name] = 1;
        }
        return name;
    }
    function vendor(el, prop) {
        var s = el.style,
            pp,
            i;
        prop = prop.charAt(0).toUpperCase() + prop.slice(1);
        for (i = 0; i < prefixes.length; i++) {
            pp = prefixes[i] + prop;
            if (s[pp] !== undefined) {
                return pp;
            }
        }
        if (s[prop] !== undefined) {
            return prop;
        }
    }
    function css(el, prop) {
        for (var n in prop) {
            el.style[vendor(el, n) || n] = prop[n];
        }
        return el;
    }
    function merge(obj) {
        for (var i = 1; i < arguments.length; i++) {
            var def = arguments[i];
            for (var n in def) {
                if (obj[n] === undefined) {
                    obj[n] = def[n];
                }
            }
        }
        return obj;
    }
    function pos(el) {
        var o = { x: el.offsetLeft, y: el.offsetTop };
        while ((el = el.offsetParent)) {
            (o.x += el.offsetLeft), (o.y += el.offsetTop);
        }
        return o;
    }
    function getColor(color, idx) {
        return typeof color == "string" ? color : color[idx % color.length];
    }
    var defaults = {
        lines: 12,
        length: 7,
        width: 5,
        radius: 10,
        rotate: 0,
        corners: 1,
        color: "#000",
        direction: 1,
        speed: 1,
        trail: 100,
        opacity: 1 / 4,
        fps: 20,
        zIndex: "auto",
        className: "spinner",
        top: "auto",
        left: "auto",
        position: "relative"
    };
    function Spinner(o) {
        if (typeof this == "undefined") {
            return new Spinner(o);
        }
        this.opts = merge(o || {}, Spinner.defaults, defaults);
    }
    Spinner.defaults = {};
    merge(Spinner.prototype, {
        spin: function (target) {
            this.stop();
            var self = this,
                o = self.opts,
                el = (self.el = css(createEl(0, { className: o.className }), {
                    position: o.position,
                    width: 0,
                    zIndex: o.zIndex
                })),
                mid = o.radius + o.length + o.width,
                ep,
                tp;
            if (target) {
                target.insertBefore(el, target.firstChild || null);
                tp = pos(target);
                ep = pos(el);
                css(el, {
                    left:
                        (o.left == "auto"
                            ? tp.x - ep.x + (target.offsetWidth >> 1)
                            : parseInt(o.left, 10) + mid) + "px",
                    top:
                        (o.top == "auto"
                            ? tp.y - ep.y + (target.offsetHeight >> 1)
                            : parseInt(o.top, 10) + mid) + "px"
                });
            }
            el.setAttribute("role", "progressbar");
            self.lines(el, self.opts);
            if (!useCssAnimations) {
                var i = 0,
                    start = ((o.lines - 1) * (1 - o.direction)) / 2,
                    alpha,
                    fps = o.fps,
                    f = fps / o.speed,
                    ostep = (1 - o.opacity) / ((f * o.trail) / 100),
                    astep = f / o.lines;
                (function anim() {
                    i++;
                    for (var j = 0; j < o.lines; j++) {
                        alpha = Math.max(
                            1 - ((i + (o.lines - j) * astep) % f) * ostep,
                            o.opacity
                        );
                        self.opacity(el, j * o.direction + start, alpha, o);
                    }
                    self.timeout = self.el && setTimeout(anim, ~~(1000 / fps));
                })();
            }
            return self;
        },
        stop: function () {
            var el = this.el;
            if (el) {
                clearTimeout(this.timeout);
                if (el.parentNode) {
                    el.parentNode.removeChild(el);
                }
                this.el = undefined;
            }
            return this;
        },
        lines: function (el, o) {
            var i = 0,
                start = ((o.lines - 1) * (1 - o.direction)) / 2,
                seg;
            function fill(color, shadow) {
                return css(createEl(), {
                    position: "absolute",
                    width: o.length + o.width + "px",
                    height: o.width + "px",
                    background: color,
                    boxShadow: shadow,
                    transformOrigin: "left",
                    transform:
                        "rotate(" +
                        ~~((360 / o.lines) * i + o.rotate) +
                        "deg) translate(" +
                        o.radius +
                        "px,0)",
                    borderRadius: ((o.corners * o.width) >> 1) + "px"
                });
            }
            for (; i < o.lines; i++) {
                seg = css(createEl(), {
                    position: "absolute",
                    top: 1 + ~(o.width / 2) + "px",
                    transform: o.hwaccel ? "translate3d(0,0,0)" : "",
                    opacity: o.opacity,
                    animation:
                        useCssAnimations &&
                        addAnimation(o.opacity, o.trail, start + i * o.direction, o.lines) +
                        " " +
                        1 / o.speed +
                        "s linear infinite"
                });
                if (o.shadow) {
                    ins(
                        seg,
                        css(fill("rgba(0,0,0,.25)", "0 0 4px rgba(0,0,0,.5)"), {
                            top: 2 + "px"
                        })
                    );
                }
                ins(el, ins(seg, fill(getColor(o.color, i), "0 0 1px rgba(0,0,0,.1)")));
            }
            return el;
        },
        opacity: function (el, i, val) {
            if (i < el.childNodes.length) {
                el.childNodes[i].style.opacity = val;
            }
        }
    });
    function initVML() {
        function vml(tag, attr) {
            return createEl(
                "<" + tag + ' xmlns="urn:schemas-microsoft.com:vml" class="spin-vml">',
                attr
            );
        }
        sheet.addRule(".spin-vml", "behavior:url(#default#VML)");
        Spinner.prototype.lines = function (el, o) {
            var r = o.length + o.width,
                s = 2 * r;
            function grp() {
                return css(
                    vml("group", { coordsize: s + " " + s, coordorigin: -r + " " + -r }),
                    { width: s, height: s }
                );
            }
            var margin = -(o.width + o.length) * 2 + "px",
                g = css(grp(), { position: "absolute", top: margin, left: margin }),
                i;
            function seg(i, dx, filter) {
                ins(
                    g,
                    ins(
                        css(grp(), { rotation: (360 / o.lines) * i + "deg", left: ~~dx }),
                        ins(
                            css(vml("roundrect", { arcsize: o.corners }), {
                                width: r,
                                height: o.width,
                                left: o.radius,
                                top: -o.width >> 1,
                                filter: filter
                            }),
                            vml("fill", { color: getColor(o.color, i), opacity: o.opacity }),
                            vml("stroke", { opacity: 0 })
                        )
                    )
                );
            }
            if (o.shadow) {
                for (i = 1; i <= o.lines; i++) {
                    seg(
                        i,
                        -2,
                        "progid:DXImageTransform.Microsoft.Blur(pixelradius=2,makeshadow=1,shadowopacity=.3)"
                    );
                }
            }
            for (i = 1; i <= o.lines; i++) {
                seg(i);
            }
            return ins(el, g);
        };
        Spinner.prototype.opacity = function (el, i, val, o) {
            var c = el.firstChild;
            o = (o.shadow && o.lines) || 0;
            if (c && i + o < c.childNodes.length) {
                c = c.childNodes[i + o];
                c = c && c.firstChild;
                c = c && c.firstChild;
                if (c) {
                    c.opacity = val;
                }
            }
        };
    }
    var probe = css(createEl("group"), { behavior: "url(#default#VML)" });
    if (!vendor(probe, "transform") && probe.adj) {
        initVML();
    } else {
        useCssAnimations = vendor(probe, "animation");
    }
    return Spinner;
});
(function (e) {
    if (typeof exports == "object") {
        e(require("jquery"), require("spin"));
    } else if (typeof define == "function" && define.amd) {
        define(["jquery", "spin"], e);
    } else {
        if (!window.Spinner) throw new Error("Spin.js not present");
        e(window.jQuery, window.Spinner);
    }
})(function (e, t) {
    e.fn.spin = function (n, r) {
        return this.each(function () {
            var i = e(this),
                s = i.data();
            if (s.spinner) {
                s.spinner.stop();
                delete s.spinner;
            }
            if (n !== false) {
                n = e.extend({ color: r || i.css("color") }, e.fn.spin.presets[n] || n);
                s.spinner = new t(n).spin(this);
            }
        });
    };
    e.fn.spin.presets = {
        tiny: { lines: 8, length: 2, width: 2, radius: 3 },
        small: { lines: 8, length: 4, width: 3, radius: 5 },
        large: { lines: 10, length: 8, width: 4, radius: 8 }
    };
});

// http://stackoverflow.com/a/21422049
(function (e) {
    e.fn.hasScrollbar = function () {
        var e = {},
            t = this.get(0);
        e.vertical = t.scrollHeight > t.clientHeight ? true : false;
        e.horizontal = t.scrollWidth > t.clientWidth ? true : false;
        return e;
    };
})(jQuery);

/**
 * Antiscroll
 * https://github.com/LearnBoost/antiscroll
 */
(function ($) {
    $.fn.antiscroll = function (options) {
        return this.each(function () {
            if ($(this).data("antiscroll"))
                $(this)
                    .data("antiscroll")
                    .destroy();
            $(this).data("antiscroll", new $.Antiscroll(this, options));
        });
    };
    $.Antiscroll = Antiscroll;
    function Antiscroll(el, opts) {
        this.el = $(el);
        this.options = opts || {};
        this.x = false !== this.options.x || this.options.forceHorizontal;
        this.y = false !== this.options.y || this.options.forceVertical;
        this.autoHide = false !== this.options.autoHide;
        this.padding = undefined == this.options.padding ? 2 : this.options.padding;
        this.inner = this.el.find(".antiscroll-inner");
        this.inner.css({
            width: "+=" + (this.y ? scrollbarSize() : 0),
            height: "+=" + (this.x ? scrollbarSize() : 0)
        });
        this.refresh();
    }
    Antiscroll.prototype.refresh = function () {
        var needHScroll =
            this.inner.get(0).scrollWidth >
            this.el.width() + (this.y ? scrollbarSize() : 0),
            needVScroll =
                this.inner.get(0).scrollHeight >
                this.el.height() + (this.x ? scrollbarSize() : 0);
        if (this.x)
            if (!this.horizontal && needHScroll)
                this.horizontal = new Scrollbar.Horizontal(this);
            else if (this.horizontal && !needHScroll) {
                this.horizontal.destroy();
                this.horizontal = null;
            } else if (this.horizontal) this.horizontal.update();
        if (this.y)
            if (!this.vertical && needVScroll)
                this.vertical = new Scrollbar.Vertical(this);
            else if (this.vertical && !needVScroll) {
                this.vertical.destroy();
                this.vertical = null;
            } else if (this.vertical) this.vertical.update();
    };
    Antiscroll.prototype.destroy = function () {
        if (this.horizontal) {
            this.horizontal.destroy();
            this.horizontal = null;
        }
        if (this.vertical) {
            this.vertical.destroy();
            this.vertical = null;
        }
        return this;
    };
    Antiscroll.prototype.rebuild = function () {
        this.destroy();
        this.inner.attr("style", "");
        Antiscroll.call(this, this.el, this.options);
        return this;
    };
    function Scrollbar(pane) {
        this.pane = pane;
        this.pane.el.append(this.el);
        this.innerEl = this.pane.inner.get(0);
        this.dragging = false;
        this.enter = false;
        this.shown = false;
        this.pane.el.mouseenter($.proxy(this, "mouseenter"));
        this.pane.el.mouseleave($.proxy(this, "mouseleave"));
        this.el.mousedown($.proxy(this, "mousedown"));
        this.innerPaneScrollListener = $.proxy(this, "scroll");
        this.pane.inner.scroll(this.innerPaneScrollListener);
        this.innerPaneMouseWheelListener = $.proxy(this, "mousewheel");
        this.pane.inner.bind("mousewheel", this.innerPaneMouseWheelListener);
        var initialDisplay = this.pane.options.initialDisplay;
        if (initialDisplay !== false) {
            this.show();
            if (this.pane.autoHide)
                this.hiding = setTimeout(
                    $.proxy(this, "hide"),
                    parseInt(initialDisplay, 10) || 3e3
                );
        }
    }
    Scrollbar.prototype.destroy = function () {
        this.el.remove();
        this.pane.inner.unbind("scroll", this.innerPaneScrollListener);
        this.pane.inner.unbind("mousewheel", this.innerPaneMouseWheelListener);
        return this;
    };
    Scrollbar.prototype.mouseenter = function () {
        this.enter = true;
        this.show();
    };
    Scrollbar.prototype.mouseleave = function () {
        this.enter = false;
        if (!this.dragging) if (this.pane.autoHide) this.hide();
    };
    Scrollbar.prototype.scroll = function () {
        if (!this.shown) {
            this.show();
            if (!this.enter && !this.dragging)
                if (this.pane.autoHide)
                    this.hiding = setTimeout($.proxy(this, "hide"), 1500);
        }
        this.update();
    };
    Scrollbar.prototype.mousedown = function (ev) {
        ev.preventDefault();
        this.dragging = true;
        this.startPageY = ev.pageY - parseInt(this.el.css("top"), 10);
        this.startPageX = ev.pageX - parseInt(this.el.css("left"), 10);
        this.el[0].ownerDocument.onselectstart = function () {
            return false;
        };
        var pane = this.pane,
            move = $.proxy(this, "mousemove"),
            self = this;
        $(this.el[0].ownerDocument)
            .mousemove(move)
            .mouseup(function () {
                self.dragging = false;
                this.onselectstart = null;
                $(this).unbind("mousemove", move);
                if (!self.enter) self.hide();
            });
    };
    Scrollbar.prototype.show = function (duration) {
        if (!this.shown && this.update()) {
            this.el.addClass("antiscroll-scrollbar-shown");
            if (this.hiding) {
                clearTimeout(this.hiding);
                this.hiding = null;
            }
            this.shown = true;
        }
    };
    Scrollbar.prototype.hide = function () {
        if (this.pane.autoHide !== false && this.shown) {
            this.el.removeClass("antiscroll-scrollbar-shown");
            this.shown = false;
        }
    };
    Scrollbar.Horizontal = function (pane) {
        this.el = $(
            '<div class="antiscroll-scrollbar antiscroll-scrollbar-horizontal">',
            pane.el
        );
        Scrollbar.call(this, pane);
    };
    inherits(Scrollbar.Horizontal, Scrollbar);
    Scrollbar.Horizontal.prototype.update = function () {
        var paneWidth = this.pane.el.width(),
            trackWidth = paneWidth - this.pane.padding * 2,
            innerEl = this.pane.inner.get(0);
        this.el
            .css("width", (trackWidth * paneWidth) / innerEl.scrollWidth)
            .css("left", (trackWidth * innerEl.scrollLeft) / innerEl.scrollWidth);
        return paneWidth < innerEl.scrollWidth;
    };
    Scrollbar.Horizontal.prototype.mousemove = function (ev) {
        var trackWidth = this.pane.el.width() - this.pane.padding * 2,
            pos = ev.pageX - this.startPageX,
            barWidth = this.el.width(),
            innerEl = this.pane.inner.get(0);
        var y = Math.min(Math.max(pos, 0), trackWidth - barWidth);
        innerEl.scrollLeft =
            ((innerEl.scrollWidth - this.pane.el.width()) * y) /
            (trackWidth - barWidth);
    };
    Scrollbar.Horizontal.prototype.mousewheel = function (ev, delta, x, y) {
        if (
            (x < 0 && 0 == this.pane.inner.get(0).scrollLeft) ||
            (x > 0 &&
                this.innerEl.scrollLeft + Math.ceil(this.pane.el.width()) ==
                this.innerEl.scrollWidth)
        ) {
            ev.preventDefault();
            return false;
        }
    };
    Scrollbar.Vertical = function (pane) {
        this.el = $(
            '<div class="antiscroll-scrollbar antiscroll-scrollbar-vertical">',
            pane.el
        );
        Scrollbar.call(this, pane);
    };
    inherits(Scrollbar.Vertical, Scrollbar);
    Scrollbar.Vertical.prototype.update = function () {
        var paneHeight = this.pane.el.height(),
            trackHeight = paneHeight - this.pane.padding * 2,
            innerEl = this.innerEl;
        var scrollbarHeight = (trackHeight * paneHeight) / innerEl.scrollHeight;
        scrollbarHeight = scrollbarHeight < 20 ? 20 : scrollbarHeight;
        var topPos = (trackHeight * innerEl.scrollTop) / innerEl.scrollHeight;
        if (topPos + scrollbarHeight > trackHeight) {
            var diff = topPos + scrollbarHeight - trackHeight;
            topPos = topPos - diff - 3;
        }
        this.el.css("height", scrollbarHeight).css("top", topPos);
        return paneHeight < innerEl.scrollHeight;
    };
    Scrollbar.Vertical.prototype.mousemove = function (ev) {
        var paneHeight = this.pane.el.height(),
            trackHeight = paneHeight - this.pane.padding * 2,
            pos = ev.pageY - this.startPageY,
            barHeight = this.el.height(),
            innerEl = this.innerEl;
        var y = Math.min(Math.max(pos, 0), trackHeight - barHeight);
        innerEl.scrollTop =
            ((innerEl.scrollHeight - paneHeight) * y) / (trackHeight - barHeight);
    };
    Scrollbar.Vertical.prototype.mousewheel = function (ev, delta, x, y) {
        if (
            (y > 0 && 0 == this.innerEl.scrollTop) ||
            (y < 0 &&
                this.innerEl.scrollTop + Math.ceil(this.pane.el.height()) ==
                this.innerEl.scrollHeight)
        ) {
            ev.preventDefault();
            return false;
        }
    };
    function inherits(ctorA, ctorB) {
        function f() { }
        f.prototype = ctorB.prototype;
        ctorA.prototype = new f();
    }
    var size;
    function scrollbarSize() {
        if (size === undefined) {
            var div = $(
                '<div class="antiscroll-inner" style="width:50px;height:50px;overflow-y:scroll;' +
                'position:absolute;top:-200px;left:-200px;"><div style="height:100px;width:100%">' +
                "</div>"
            );
            $("body").append(div);
            var w1 = $(div).innerWidth();
            var w2 = $("div", div).innerWidth();
            $(div).remove();
            size = w1 - w2;
        }
        return size;
    }
})(jQuery);

/**
 * jQuery Mousewheel
 * ! Copyright (c) 2013 Brandon Aaron (http://brandonaaron.net)
 * Licensed under the MIT License (LICENSE.txt).
 *
 * Thanks to: http://adomas.org/javascript-mouse-wheel/ for some pointers.
 * Thanks to: Mathias Bank(http://www.mathias-bank.de) for a scope bug fix.
 * Thanks to: Seamus Leahy for adding deltaX and deltaY
 *
 * Version: 3.1.3
 *
 * Requires: 1.2.2+
 */
(function (factory) {
    if (typeof define === "function" && define.amd) define(["jquery"], factory);
    else if (typeof exports === "object") module.exports = factory;
    else factory(jQuery);
})(function ($) {
    var toFix = ["wheel", "mousewheel", "DOMMouseScroll", "MozMousePixelScroll"];
    var toBind =
        "onwheel" in document || document.documentMode >= 9
            ? ["wheel"]
            : ["mousewheel", "DomMouseScroll", "MozMousePixelScroll"];
    var lowestDelta, lowestDeltaXY;
    if ($.event.fixHooks)
        for (var i = toFix.length; i;)
            $.event.fixHooks[toFix[--i]] = $.event.mouseHooks;
    $.event.special.mousewheel = {
        setup: function () {
            if (this.addEventListener)
                for (var i = toBind.length; i;)
                    this.addEventListener(toBind[--i], handler, false);
            else this.onmousewheel = handler;
        },
        teardown: function () {
            if (this.removeEventListener)
                for (var i = toBind.length; i;)
                    this.removeEventListener(toBind[--i], handler, false);
            else this.onmousewheel = null;
        }
    };
    $.fn.extend({
        mousewheel: function (fn) {
            return fn ? this.bind("mousewheel", fn) : this.trigger("mousewheel");
        },
        unmousewheel: function (fn) {
            return this.unbind("mousewheel", fn);
        }
    });
    function handler(event) {
        var orgEvent = event || window.event,
            args = [].slice.call(arguments, 1),
            delta = 0,
            deltaX = 0,
            deltaY = 0,
            absDelta = 0,
            absDeltaXY = 0,
            fn;
        event = $.event.fix(orgEvent);
        event.type = "mousewheel";
        if (orgEvent.wheelDelta) delta = orgEvent.wheelDelta;
        if (orgEvent.detail) delta = orgEvent.detail * -1;
        if (orgEvent.deltaY) {
            deltaY = orgEvent.deltaY * -1;
            delta = deltaY;
        }
        if (orgEvent.deltaX) {
            deltaX = orgEvent.deltaX;
            delta = deltaX * -1;
        }
        if (orgEvent.wheelDeltaY !== undefined) deltaY = orgEvent.wheelDeltaY;
        if (orgEvent.wheelDeltaX !== undefined) deltaX = orgEvent.wheelDeltaX * -1;
        absDelta = Math.abs(delta);
        if (!lowestDelta || absDelta < lowestDelta) lowestDelta = absDelta;
        absDeltaXY = Math.max(Math.abs(deltaY), Math.abs(deltaX));
        if (!lowestDeltaXY || absDeltaXY < lowestDeltaXY)
            lowestDeltaXY = absDeltaXY;
        fn = delta > 0 ? "floor" : "ceil";
        delta = Math[fn](delta / lowestDelta);
        deltaX = Math[fn](deltaX / lowestDeltaXY);
        deltaY = Math[fn](deltaY / lowestDeltaXY);
        args.unshift(event, delta, deltaX, deltaY);
        return ($.event.dispatch || $.event.handle).apply(this, args);
    }
});

/**
Created: 20060120
Author:  Steve Moitozo <god at zilla dot us> -- geekwisdom.com
License: MIT License (see below)
Copyright (c) 2006 Steve Moitozo <god at zilla dot us>

Slightly modified for Peafowl

*/
function testPassword(e) {
    var t = 0,
        n = "weak",
        r = "",
        i = 0;
    if (e.length < 5) {
        t = t + 3;
        r = r + "3 points for length (" + e.length + ")\n";
    } else if (e.length > 4 && e.length < 8) {
        t = t + 6;
        r = r + "6 points for length (" + e.length + ")\n";
    } else if (e.length > 7 && e.length < 16) {
        t = t + 12;
        r = r + "12 points for length (" + e.length + ")\n";
    } else if (e.length > 15) {
        t = t + 18;
        r = r + "18 point for length (" + e.length + ")\n";
    }
    if (e.match(/[a-z]/)) {
        t = t + 1;
        r = r + "1 point for at least one lower case char\n";
    }
    if (e.match(/[A-Z]/)) {
        t = t + 5;
        r = r + "5 points for at least one upper case char\n";
    }
    if (e.match(/\d+/)) {
        t = t + 5;
        r = r + "5 points for at least one number\n";
    }
    if (e.match(/(.*[0-9].*[0-9].*[0-9])/)) {
        t = t + 5;
        r = r + "5 points for at least three numbers\n";
    }
    if (e.match(/.[!,@,#,$,%,^,&,*,?,_,~]/)) {
        t = t + 5;
        r = r + "5 points for at least one special char\n";
    }
    if (e.match(/(.*[!,@,#,$,%,^,&,*,?,_,~].*[!,@,#,$,%,^,&,*,?,_,~])/)) {
        t = t + 5;
        r = r + "5 points for at least two special chars\n";
    }
    if (e.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
        t = t + 2;
        r = r + "2 combo points for upper and lower letters\n";
    }
    if (e.match(/([a-zA-Z])/) && e.match(/([0-9])/)) {
        t = t + 2;
        r = r + "2 combo points for letters and numbers\n";
    }
    if (
        e.match(
            /([a-zA-Z0-9].*[!,@,#,$,%,^,&,*,?,_,~])|([!,@,#,$,%,^,&,*,?,_,~].*[a-zA-Z0-9])/
        )
    ) {
        t = t + 2;
        r = r + "2 combo points for letters, numbers and special chars\n";
    }
    if (e.length == 0) {
        t = 0;
    }
    if (t < 16) {
        n = "very weak";
    } else if (t > 15 && t < 25) {
        n = "weak";
    } else if (t > 24 && t < 35) {
        n = "average";
    } else if (t > 34 && t < 45) {
        n = "strong";
    } else {
        n = "stronger";
    }
    i = Math.round(Math.min(100, (100 * t) / 45)) / 100;
    return { score: t, ratio: i, percent: i * 100 + "%", verdict: n, log: r };
}

// SparkMD5
(function (factory) {
    if (typeof exports === "object") {
        module.exports = factory();
    } else if (typeof define === "function" && define.amd) {
        define(factory);
    } else {
        var glob;
        try {
            glob = window;
        } catch (e) {
            glob = self;
        }
        glob.SparkMD5 = factory();
    }
})(function (undefined) {
    "use strict";
    var add32 = function (a, b) {
        return (a + b) & 4294967295;
    },
        hex_chr = [
            "0",
            "1",
            "2",
            "3",
            "4",
            "5",
            "6",
            "7",
            "8",
            "9",
            "a",
            "b",
            "c",
            "d",
            "e",
            "f"
        ];
    function cmn(q, a, b, x, s, t) {
        a = add32(add32(a, q), add32(x, t));
        return add32((a << s) | (a >>> (32 - s)), b);
    }
    function ff(a, b, c, d, x, s, t) {
        return cmn((b & c) | (~b & d), a, b, x, s, t);
    }
    function gg(a, b, c, d, x, s, t) {
        return cmn((b & d) | (c & ~d), a, b, x, s, t);
    }
    function hh(a, b, c, d, x, s, t) {
        return cmn(b ^ c ^ d, a, b, x, s, t);
    }
    function ii(a, b, c, d, x, s, t) {
        return cmn(c ^ (b | ~d), a, b, x, s, t);
    }
    function md5cycle(x, k) {
        var a = x[0],
            b = x[1],
            c = x[2],
            d = x[3];
        a = ff(a, b, c, d, k[0], 7, -680876936);
        d = ff(d, a, b, c, k[1], 12, -389564586);
        c = ff(c, d, a, b, k[2], 17, 606105819);
        b = ff(b, c, d, a, k[3], 22, -1044525330);
        a = ff(a, b, c, d, k[4], 7, -176418897);
        d = ff(d, a, b, c, k[5], 12, 1200080426);
        c = ff(c, d, a, b, k[6], 17, -1473231341);
        b = ff(b, c, d, a, k[7], 22, -45705983);
        a = ff(a, b, c, d, k[8], 7, 1770035416);
        d = ff(d, a, b, c, k[9], 12, -1958414417);
        c = ff(c, d, a, b, k[10], 17, -42063);
        b = ff(b, c, d, a, k[11], 22, -1990404162);
        a = ff(a, b, c, d, k[12], 7, 1804603682);
        d = ff(d, a, b, c, k[13], 12, -40341101);
        c = ff(c, d, a, b, k[14], 17, -1502002290);
        b = ff(b, c, d, a, k[15], 22, 1236535329);
        a = gg(a, b, c, d, k[1], 5, -165796510);
        d = gg(d, a, b, c, k[6], 9, -1069501632);
        c = gg(c, d, a, b, k[11], 14, 643717713);
        b = gg(b, c, d, a, k[0], 20, -373897302);
        a = gg(a, b, c, d, k[5], 5, -701558691);
        d = gg(d, a, b, c, k[10], 9, 38016083);
        c = gg(c, d, a, b, k[15], 14, -660478335);
        b = gg(b, c, d, a, k[4], 20, -405537848);
        a = gg(a, b, c, d, k[9], 5, 568446438);
        d = gg(d, a, b, c, k[14], 9, -1019803690);
        c = gg(c, d, a, b, k[3], 14, -187363961);
        b = gg(b, c, d, a, k[8], 20, 1163531501);
        a = gg(a, b, c, d, k[13], 5, -1444681467);
        d = gg(d, a, b, c, k[2], 9, -51403784);
        c = gg(c, d, a, b, k[7], 14, 1735328473);
        b = gg(b, c, d, a, k[12], 20, -1926607734);
        a = hh(a, b, c, d, k[5], 4, -378558);
        d = hh(d, a, b, c, k[8], 11, -2022574463);
        c = hh(c, d, a, b, k[11], 16, 1839030562);
        b = hh(b, c, d, a, k[14], 23, -35309556);
        a = hh(a, b, c, d, k[1], 4, -1530992060);
        d = hh(d, a, b, c, k[4], 11, 1272893353);
        c = hh(c, d, a, b, k[7], 16, -155497632);
        b = hh(b, c, d, a, k[10], 23, -1094730640);
        a = hh(a, b, c, d, k[13], 4, 681279174);
        d = hh(d, a, b, c, k[0], 11, -358537222);
        c = hh(c, d, a, b, k[3], 16, -722521979);
        b = hh(b, c, d, a, k[6], 23, 76029189);
        a = hh(a, b, c, d, k[9], 4, -640364487);
        d = hh(d, a, b, c, k[12], 11, -421815835);
        c = hh(c, d, a, b, k[15], 16, 530742520);
        b = hh(b, c, d, a, k[2], 23, -995338651);
        a = ii(a, b, c, d, k[0], 6, -198630844);
        d = ii(d, a, b, c, k[7], 10, 1126891415);
        c = ii(c, d, a, b, k[14], 15, -1416354905);
        b = ii(b, c, d, a, k[5], 21, -57434055);
        a = ii(a, b, c, d, k[12], 6, 1700485571);
        d = ii(d, a, b, c, k[3], 10, -1894986606);
        c = ii(c, d, a, b, k[10], 15, -1051523);
        b = ii(b, c, d, a, k[1], 21, -2054922799);
        a = ii(a, b, c, d, k[8], 6, 1873313359);
        d = ii(d, a, b, c, k[15], 10, -30611744);
        c = ii(c, d, a, b, k[6], 15, -1560198380);
        b = ii(b, c, d, a, k[13], 21, 1309151649);
        a = ii(a, b, c, d, k[4], 6, -145523070);
        d = ii(d, a, b, c, k[11], 10, -1120210379);
        c = ii(c, d, a, b, k[2], 15, 718787259);
        b = ii(b, c, d, a, k[9], 21, -343485551);
        x[0] = add32(a, x[0]);
        x[1] = add32(b, x[1]);
        x[2] = add32(c, x[2]);
        x[3] = add32(d, x[3]);
    }
    function md5blk(s) {
        var md5blks = [],
            i;
        for (i = 0; i < 64; i += 4) {
            md5blks[i >> 2] =
                s.charCodeAt(i) +
                (s.charCodeAt(i + 1) << 8) +
                (s.charCodeAt(i + 2) << 16) +
                (s.charCodeAt(i + 3) << 24);
        }
        return md5blks;
    }
    function md5blk_array(a) {
        var md5blks = [],
            i;
        for (i = 0; i < 64; i += 4) {
            md5blks[i >> 2] =
                a[i] + (a[i + 1] << 8) + (a[i + 2] << 16) + (a[i + 3] << 24);
        }
        return md5blks;
    }
    function md51(s) {
        var n = s.length,
            state = [1732584193, -271733879, -1732584194, 271733878],
            i,
            length,
            tail,
            tmp,
            lo,
            hi;
        for (i = 64; i <= n; i += 64) {
            md5cycle(state, md5blk(s.substring(i - 64, i)));
        }
        s = s.substring(i - 64);
        length = s.length;
        tail = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        for (i = 0; i < length; i += 1) {
            tail[i >> 2] |= s.charCodeAt(i) << (i % 4 << 3);
        }
        tail[i >> 2] |= 128 << (i % 4 << 3);
        if (i > 55) {
            md5cycle(state, tail);
            for (i = 0; i < 16; i += 1) {
                tail[i] = 0;
            }
        }
        tmp = n * 8;
        tmp = tmp.toString(16).match(/(.*?)(.{0,8})$/);
        lo = parseInt(tmp[2], 16);
        hi = parseInt(tmp[1], 16) || 0;
        tail[14] = lo;
        tail[15] = hi;
        md5cycle(state, tail);
        return state;
    }
    function md51_array(a) {
        var n = a.length,
            state = [1732584193, -271733879, -1732584194, 271733878],
            i,
            length,
            tail,
            tmp,
            lo,
            hi;
        for (i = 64; i <= n; i += 64) {
            md5cycle(state, md5blk_array(a.subarray(i - 64, i)));
        }
        a = i - 64 < n ? a.subarray(i - 64) : new Uint8Array(0);
        length = a.length;
        tail = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        for (i = 0; i < length; i += 1) {
            tail[i >> 2] |= a[i] << (i % 4 << 3);
        }
        tail[i >> 2] |= 128 << (i % 4 << 3);
        if (i > 55) {
            md5cycle(state, tail);
            for (i = 0; i < 16; i += 1) {
                tail[i] = 0;
            }
        }
        tmp = n * 8;
        tmp = tmp.toString(16).match(/(.*?)(.{0,8})$/);
        lo = parseInt(tmp[2], 16);
        hi = parseInt(tmp[1], 16) || 0;
        tail[14] = lo;
        tail[15] = hi;
        md5cycle(state, tail);
        return state;
    }
    function rhex(n) {
        var s = "",
            j;
        for (j = 0; j < 4; j += 1) {
            s += hex_chr[(n >> (j * 8 + 4)) & 15] + hex_chr[(n >> (j * 8)) & 15];
        }
        return s;
    }
    function hex(x) {
        var i;
        for (i = 0; i < x.length; i += 1) {
            x[i] = rhex(x[i]);
        }
        return x.join("");
    }
    if (hex(md51("hello")) !== "5d41402abc4b2a76b9719d911017c592") {
        add32 = function (x, y) {
            var lsw = (x & 65535) + (y & 65535),
                msw = (x >> 16) + (y >> 16) + (lsw >> 16);
            return (msw << 16) | (lsw & 65535);
        };
    }
    function toUtf8(str) {
        if (/[\u0080-\uFFFF]/.test(str)) {
            str = unescape(encodeURIComponent(str));
        }
        return str;
    }
    function utf8Str2ArrayBuffer(str, returnUInt8Array) {
        var length = str.length,
            buff = new ArrayBuffer(length),
            arr = new Uint8Array(buff),
            i;
        for (i = 0; i < length; i++) {
            arr[i] = str.charCodeAt(i);
        }
        return returnUInt8Array ? arr : buff;
    }
    function arrayBuffer2Utf8Str(buff) {
        return String.fromCharCode.apply(null, new Uint8Array(buff));
    }
    function concatenateArrayBuffers(first, second, returnUInt8Array) {
        var result = new Uint8Array(first.byteLength + second.byteLength);
        result.set(new Uint8Array(first));
        result.set(new Uint8Array(second), first.byteLength);
        return returnUInt8Array ? result : result.buffer;
    }
    function SparkMD5() {
        this.reset();
    }
    SparkMD5.prototype.append = function (str) {
        this.appendBinary(toUtf8(str));
        return this;
    };
    SparkMD5.prototype.appendBinary = function (contents) {
        this._buff += contents;
        this._length += contents.length;
        var length = this._buff.length,
            i;
        for (i = 64; i <= length; i += 64) {
            md5cycle(this._hash, md5blk(this._buff.substring(i - 64, i)));
        }
        this._buff = this._buff.substring(i - 64);
        return this;
    };
    SparkMD5.prototype.end = function (raw) {
        var buff = this._buff,
            length = buff.length,
            i,
            tail = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            ret;
        for (i = 0; i < length; i += 1) {
            tail[i >> 2] |= buff.charCodeAt(i) << (i % 4 << 3);
        }
        this._finish(tail, length);
        ret = !!raw ? this._hash : hex(this._hash);
        this.reset();
        return ret;
    };
    SparkMD5.prototype.reset = function () {
        this._buff = "";
        this._length = 0;
        this._hash = [1732584193, -271733879, -1732584194, 271733878];
        return this;
    };
    SparkMD5.prototype.getState = function () {
        return { buff: this._buff, length: this._length, hash: this._hash };
    };
    SparkMD5.prototype.setState = function (state) {
        this._buff = state.buff;
        this._length = state.length;
        this._hash = state.hash;
        return this;
    };
    SparkMD5.prototype.destroy = function () {
        delete this._hash;
        delete this._buff;
        delete this._length;
    };
    SparkMD5.prototype._finish = function (tail, length) {
        var i = length,
            tmp,
            lo,
            hi;
        tail[i >> 2] |= 128 << (i % 4 << 3);
        if (i > 55) {
            md5cycle(this._hash, tail);
            for (i = 0; i < 16; i += 1) {
                tail[i] = 0;
            }
        }
        tmp = this._length * 8;
        tmp = tmp.toString(16).match(/(.*?)(.{0,8})$/);
        lo = parseInt(tmp[2], 16);
        hi = parseInt(tmp[1], 16) || 0;
        tail[14] = lo;
        tail[15] = hi;
        md5cycle(this._hash, tail);
    };
    SparkMD5.hash = function (str, raw) {
        return SparkMD5.hashBinary(toUtf8(str), raw);
    };
    SparkMD5.hashBinary = function (content, raw) {
        var hash = md51(content);
        return !!raw ? hash : hex(hash);
    };
    SparkMD5.ArrayBuffer = function () {
        this.reset();
    };
    SparkMD5.ArrayBuffer.prototype.append = function (arr) {
        var buff = concatenateArrayBuffers(this._buff.buffer, arr, true),
            length = buff.length,
            i;
        this._length += arr.byteLength;
        for (i = 64; i <= length; i += 64) {
            md5cycle(this._hash, md5blk_array(buff.subarray(i - 64, i)));
        }
        this._buff = i - 64 < length ? buff.subarray(i - 64) : new Uint8Array(0);
        return this;
    };
    SparkMD5.ArrayBuffer.prototype.end = function (raw) {
        var buff = this._buff,
            length = buff.length,
            tail = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            i,
            ret;
        for (i = 0; i < length; i += 1) {
            tail[i >> 2] |= buff[i] << (i % 4 << 3);
        }
        this._finish(tail, length);
        ret = !!raw ? this._hash : hex(this._hash);
        this.reset();
        return ret;
    };
    SparkMD5.ArrayBuffer.prototype.reset = function () {
        this._buff = new Uint8Array(0);
        this._length = 0;
        this._hash = [1732584193, -271733879, -1732584194, 271733878];
        return this;
    };
    SparkMD5.ArrayBuffer.prototype.getState = function () {
        var state = SparkMD5.prototype.getState.call(this);
        state.buff = arrayBuffer2Utf8Str(state.buff);
        return state;
    };
    SparkMD5.ArrayBuffer.prototype.setState = function (state) {
        state.buff = utf8Str2ArrayBuffer(state.buff, true);
        return SparkMD5.prototype.setState.call(this, state);
    };
    SparkMD5.ArrayBuffer.prototype.destroy = SparkMD5.prototype.destroy;
    SparkMD5.ArrayBuffer.prototype._finish = SparkMD5.prototype._finish;
    SparkMD5.ArrayBuffer.hash = function (arr, raw) {
        var hash = md51_array(new Uint8Array(arr));
        return !!raw ? hash : hex(hash);
    };
    return SparkMD5;
});

/*!
 * jQuery Color Animations v3.0.0
 * https://github.com/jquery/jquery-color
 *
 * Copyright OpenJS Foundation and other contributors
 * Released under the MIT license.
 * https://jquery.org/license
 *
 * Date: Wed May 15 16:49:44 2024 +0200
 */

( function( root, factory ) {
	"use strict";

	if ( typeof define === "function" && define.amd ) {

		// AMD. Register as an anonymous module.
		define( [ "jquery" ], factory );
	} else if ( typeof exports === "object" ) {
		module.exports = factory( require( "jquery" ) );
	} else {
		factory( root.jQuery );
	}
} )( this, function( jQuery, undefined ) {
	"use strict";

	var stepHooks = "backgroundColor borderBottomColor borderLeftColor borderRightColor " +
		"borderTopColor color columnRuleColor outlineColor textDecorationColor textEmphasisColor",

	class2type = {},
	toString = class2type.toString,

	// plusequals test for += 100 -= 100
	rplusequals = /^([\-+])=\s*(\d+\.?\d*)/,

	// a set of RE's that can match strings and generate color tuples.
	stringParsers = [ {
			re: /rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,
			parse: function( execResult ) {
				return [
					execResult[ 1 ],
					execResult[ 2 ],
					execResult[ 3 ],
					execResult[ 4 ]
				];
			}
		}, {
			re: /rgba?\(\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,
			parse: function( execResult ) {
				return [
					execResult[ 1 ] * 2.55,
					execResult[ 2 ] * 2.55,
					execResult[ 3 ] * 2.55,
					execResult[ 4 ]
				];
			}
		}, {

			// this regex ignores A-F because it's compared against an already lowercased string
			re: /#([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})?/,
			parse: function( execResult ) {
				return [
					parseInt( execResult[ 1 ], 16 ),
					parseInt( execResult[ 2 ], 16 ),
					parseInt( execResult[ 3 ], 16 ),
					execResult[ 4 ] ?
						( parseInt( execResult[ 4 ], 16 ) / 255 ).toFixed( 2 ) :
						1
				];
			}
		}, {

			// this regex ignores A-F because it's compared against an already lowercased string
			re: /#([a-f0-9])([a-f0-9])([a-f0-9])([a-f0-9])?/,
			parse: function( execResult ) {
				return [
					parseInt( execResult[ 1 ] + execResult[ 1 ], 16 ),
					parseInt( execResult[ 2 ] + execResult[ 2 ], 16 ),
					parseInt( execResult[ 3 ] + execResult[ 3 ], 16 ),
					execResult[ 4 ] ?
						( parseInt( execResult[ 4 ] + execResult[ 4 ], 16 ) / 255 )
							.toFixed( 2 ) :
						1
				];
			}
		}, {
			re: /hsla?\(\s*(\d+(?:\.\d+)?)\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,
			space: "hsla",
			parse: function( execResult ) {
				return [
					execResult[ 1 ],
					execResult[ 2 ] / 100,
					execResult[ 3 ] / 100,
					execResult[ 4 ]
				];
			}
		} ],

	// jQuery.Color( )
	color = jQuery.Color = function( color, green, blue, alpha ) {
		return new jQuery.Color.fn.parse( color, green, blue, alpha );
	},
	spaces = {
		rgba: {
			props: {
				red: {
					idx: 0,
					type: "byte"
				},
				green: {
					idx: 1,
					type: "byte"
				},
				blue: {
					idx: 2,
					type: "byte"
				}
			}
		},

		hsla: {
			props: {
				hue: {
					idx: 0,
					type: "degrees"
				},
				saturation: {
					idx: 1,
					type: "percent"
				},
				lightness: {
					idx: 2,
					type: "percent"
				}
			}
		}
	},
	propTypes = {
		"byte": {
			floor: true,
			max: 255
		},
		"percent": {
			max: 1
		},
		"degrees": {
			mod: 360,
			floor: true
		}
	},

	// colors = jQuery.Color.names
	colors,

	// local aliases of functions called often
	each = jQuery.each;

// define cache name and alpha properties
// for rgba and hsla spaces
each( spaces, function( spaceName, space ) {
	space.cache = "_" + spaceName;
	space.props.alpha = {
		idx: 3,
		type: "percent",
		def: 1
	};
} );

// Populate the class2type map
jQuery.each( "Boolean Number String Function Array Date RegExp Object Error Symbol".split( " " ),
	function( _i, name ) {
		class2type[ "[object " + name + "]" ] = name.toLowerCase();
	} );

function getType( obj ) {
	if ( obj == null ) {
		return obj + "";
	}

	return typeof obj === "object" ?
		class2type[ toString.call( obj ) ] || "object" :
		typeof obj;
}

function clamp( value, prop, allowEmpty ) {
	var type = propTypes[ prop.type ] || {};

	if ( value == null ) {
		return ( allowEmpty || !prop.def ) ? null : prop.def;
	}

	// ~~ is an short way of doing floor for positive numbers
	value = type.floor ? ~~value : parseFloat( value );

	if ( type.mod ) {

		// we add mod before modding to make sure that negatives values
		// get converted properly: -10 -> 350
		return ( value + type.mod ) % type.mod;
	}

	// for now all property types without mod have min and max
	return Math.min( type.max, Math.max( 0, value ) );
}

function stringParse( string ) {
	var inst = color(),
		rgba = inst._rgba = [];

	string = string.toLowerCase();

	each( stringParsers, function( _i, parser ) {
		var parsed,
			match = parser.re.exec( string ),
			values = match && parser.parse( match ),
			spaceName = parser.space || "rgba";

		if ( values ) {
			parsed = inst[ spaceName ]( values );

			// if this was an rgba parse the assignment might happen twice
			// oh well....
			inst[ spaces[ spaceName ].cache ] = parsed[ spaces[ spaceName ].cache ];
			rgba = inst._rgba = parsed._rgba;

			// exit each( stringParsers ) here because we matched
			return false;
		}
	} );

	// Found a stringParser that handled it
	if ( rgba.length ) {

		// if this came from a parsed string, force "transparent" when alpha is 0
		// chrome, (and maybe others) return "transparent" as rgba(0,0,0,0)
		if ( rgba.join() === "0,0,0,0" ) {
			jQuery.extend( rgba, colors.transparent );
		}
		return inst;
	}

	return colors[ string ];
}

color.fn = jQuery.extend( color.prototype, {
	parse: function( red, green, blue, alpha ) {
		if ( red === undefined ) {
			this._rgba = [ null, null, null, null ];
			return this;
		}
		if ( red.jquery || red.nodeType ) {
			red = jQuery( red ).css( green );
			green = undefined;
		}

		var inst = this,
			type = getType( red ),
			rgba = this._rgba = [];

		// more than 1 argument specified - assume ( red, green, blue, alpha )
		if ( green !== undefined ) {
			red = [ red, green, blue, alpha ];
			type = "array";
		}

		if ( type === "string" ) {
			return this.parse( stringParse( red ) || colors._default );
		}

		if ( type === "array" ) {
			each( spaces.rgba.props, function( _key, prop ) {
				rgba[ prop.idx ] = clamp( red[ prop.idx ], prop );
			} );
			return this;
		}

		if ( type === "object" ) {
			if ( red instanceof color ) {
				each( spaces, function( _spaceName, space ) {
					if ( red[ space.cache ] ) {
						inst[ space.cache ] = red[ space.cache ].slice();
					}
				} );
			} else {
				each( spaces, function( _spaceName, space ) {
					var cache = space.cache;
					each( space.props, function( key, prop ) {

						// if the cache doesn't exist, and we know how to convert
						if ( !inst[ cache ] && space.to ) {

							// if the value was null, we don't need to copy it
							// if the key was alpha, we don't need to copy it either
							if ( key === "alpha" || red[ key ] == null ) {
								return;
							}
							inst[ cache ] = space.to( inst._rgba );
						}

						// this is the only case where we allow nulls for ALL properties.
						// call clamp with alwaysAllowEmpty
						inst[ cache ][ prop.idx ] = clamp( red[ key ], prop, true );
					} );

					// everything defined but alpha?
					if ( inst[ cache ] && jQuery.inArray(
						null,
						inst[ cache ].slice( 0, 3 )
					) < 0 ) {

						// use the default of 1
						if ( inst[ cache ][ 3 ] == null ) {
							inst[ cache ][ 3 ] = 1;
						}

						if ( space.from ) {
							inst._rgba = space.from( inst[ cache ] );
						}
					}
				} );
			}
			return this;
		}
	},
	is: function( compare ) {
		var is = color( compare ),
			same = true,
			inst = this;

		each( spaces, function( _, space ) {
			var localCache,
				isCache = is[ space.cache ];
			if ( isCache ) {
				localCache = inst[ space.cache ] || space.to && space.to( inst._rgba ) || [];
				each( space.props, function( _, prop ) {
					if ( isCache[ prop.idx ] != null ) {
						same = ( isCache[ prop.idx ] === localCache[ prop.idx ] );
						return same;
					}
				} );
			}
			return same;
		} );
		return same;
	},
	_space: function() {
		var used = [],
			inst = this;
		each( spaces, function( spaceName, space ) {
			if ( inst[ space.cache ] ) {
				used.push( spaceName );
			}
		} );
		return used.pop();
	},
	transition: function( other, distance ) {
		var end = color( other ),
			spaceName = end._space(),
			space = spaces[ spaceName ],
			startColor = this.alpha() === 0 ? color( "transparent" ) : this,
			start = startColor[ space.cache ] || space.to( startColor._rgba ),
			result = start.slice();

		end = end[ space.cache ];
		each( space.props, function( _key, prop ) {
			var index = prop.idx,
				startValue = start[ index ],
				endValue = end[ index ],
				type = propTypes[ prop.type ] || {};

			// if null, don't override start value
			if ( endValue === null ) {
				return;
			}

			// if null - use end
			if ( startValue === null ) {
				result[ index ] = endValue;
			} else {
				if ( type.mod ) {
					if ( endValue - startValue > type.mod / 2 ) {
						startValue += type.mod;
					} else if ( startValue - endValue > type.mod / 2 ) {
						startValue -= type.mod;
					}
				}
				result[ index ] = clamp( ( endValue - startValue ) * distance + startValue, prop );
			}
		} );
		return this[ spaceName ]( result );
	},
	blend: function( opaque ) {

		// if we are already opaque - return ourself
		if ( this._rgba[ 3 ] === 1 ) {
			return this;
		}

		var rgb = this._rgba.slice(),
			a = rgb.pop(),
			blend = color( opaque )._rgba;

		return color( jQuery.map( rgb, function( v, i ) {
			return ( 1 - a ) * blend[ i ] + a * v;
		} ) );
	},
	toRgbaString: function() {
		var prefix = "rgba(",
			rgba = jQuery.map( this._rgba, function( v, i ) {
				if ( v != null ) {
					return v;
				}
				return i > 2 ? 1 : 0;
			} );

		if ( rgba[ 3 ] === 1 ) {
			rgba.pop();
			prefix = "rgb(";
		}

		return prefix + rgba.join( ", " ) + ")";
	},
	toHslaString: function() {
		var prefix = "hsla(",
			hsla = jQuery.map( this.hsla(), function( v, i ) {
				if ( v == null ) {
					v = i > 2 ? 1 : 0;
				}

				// catch 1 and 2
				if ( i && i < 3 ) {
					v = Math.round( v * 100 ) + "%";
				}
				return v;
			} );

		if ( hsla[ 3 ] === 1 ) {
			hsla.pop();
			prefix = "hsl(";
		}
		return prefix + hsla.join( ", " ) + ")";
	},
	toHexString: function( includeAlpha ) {
		var rgba = this._rgba.slice(),
			alpha = rgba.pop();

		if ( includeAlpha ) {
			rgba.push( ~~( alpha * 255 ) );
		}

		return "#" + jQuery.map( rgba, function( v ) {

			// default to 0 when nulls exist
			return ( "0" + ( v || 0 ).toString( 16 ) ).substr( -2 );
		} ).join( "" );
	},
	toString: function() {
		return this.toRgbaString();
	}
} );
color.fn.parse.prototype = color.fn;

// hsla conversions adapted from:
// https://code.google.com/p/maashaack/source/browse/packages/graphics/trunk/src/graphics/colors/HUE2RGB.as?r=5021

function hue2rgb( p, q, h ) {
	h = ( h + 1 ) % 1;
	if ( h * 6 < 1 ) {
		return p + ( q - p ) * h * 6;
	}
	if ( h * 2 < 1 ) {
		return q;
	}
	if ( h * 3 < 2 ) {
		return p + ( q - p ) * ( ( 2 / 3 ) - h ) * 6;
	}
	return p;
}

spaces.hsla.to = function( rgba ) {
	if ( rgba[ 0 ] == null || rgba[ 1 ] == null || rgba[ 2 ] == null ) {
		return [ null, null, null, rgba[ 3 ] ];
	}
	var r = rgba[ 0 ] / 255,
		g = rgba[ 1 ] / 255,
		b = rgba[ 2 ] / 255,
		a = rgba[ 3 ],
		max = Math.max( r, g, b ),
		min = Math.min( r, g, b ),
		diff = max - min,
		add = max + min,
		l = add * 0.5,
		h, s;

	if ( min === max ) {
		h = 0;
	} else if ( r === max ) {
		h = ( 60 * ( g - b ) / diff ) + 360;
	} else if ( g === max ) {
		h = ( 60 * ( b - r ) / diff ) + 120;
	} else {
		h = ( 60 * ( r - g ) / diff ) + 240;
	}

	// chroma (diff) == 0 means greyscale which, by definition, saturation = 0%
	// otherwise, saturation is based on the ratio of chroma (diff) to lightness (add)
	if ( diff === 0 ) {
		s = 0;
	} else if ( l <= 0.5 ) {
		s = diff / add;
	} else {
		s = diff / ( 2 - add );
	}
	return [ Math.round( h ) % 360, s, l, a == null ? 1 : a ];
};

spaces.hsla.from = function( hsla ) {
	if ( hsla[ 0 ] == null || hsla[ 1 ] == null || hsla[ 2 ] == null ) {
		return [ null, null, null, hsla[ 3 ] ];
	}
	var h = hsla[ 0 ] / 360,
		s = hsla[ 1 ],
		l = hsla[ 2 ],
		a = hsla[ 3 ],
		q = l <= 0.5 ? l * ( 1 + s ) : l + s - l * s,
		p = 2 * l - q;

	return [
		Math.round( hue2rgb( p, q, h + ( 1 / 3 ) ) * 255 ),
		Math.round( hue2rgb( p, q, h ) * 255 ),
		Math.round( hue2rgb( p, q, h - ( 1 / 3 ) ) * 255 ),
		a
	];
};


each( spaces, function( spaceName, space ) {
	var props = space.props,
		cache = space.cache,
		to = space.to,
		from = space.from;

	// makes rgba() and hsla()
	color.fn[ spaceName ] = function( value ) {

		// generate a cache for this space if it doesn't exist
		if ( to && !this[ cache ] ) {
			this[ cache ] = to( this._rgba );
		}
		if ( value === undefined ) {
			return this[ cache ].slice();
		}

		var ret,
			type = getType( value ),
			arr = ( type === "array" || type === "object" ) ? value : arguments,
			local = this[ cache ].slice();

		each( props, function( key, prop ) {
			var val = arr[ type === "object" ? key : prop.idx ];
			if ( val == null ) {
				val = local[ prop.idx ];
			}
			local[ prop.idx ] = clamp( val, prop );
		} );

		if ( from ) {
			ret = color( from( local ) );
			ret[ cache ] = local;
			return ret;
		} else {
			return color( local );
		}
	};

	// makes red() green() blue() alpha() hue() saturation() lightness()
	each( props, function( key, prop ) {

		// alpha is included in more than one space
		if ( color.fn[ key ] ) {
			return;
		}
		color.fn[ key ] = function( value ) {
			var local, cur, match, fn,
				vtype = getType( value );

			if ( key === "alpha" ) {
				fn = this._hsla ? "hsla" : "rgba";
			} else {
				fn = spaceName;
			}
			local = this[ fn ]();
			cur = local[ prop.idx ];

			if ( vtype === "undefined" ) {
				return cur;
			}

			if ( vtype === "function" ) {
				value = value.call( this, cur );
				vtype = getType( value );
			}
			if ( value == null && prop.empty ) {
				return this;
			}
			if ( vtype === "string" ) {
				match = rplusequals.exec( value );
				if ( match ) {
					value = cur + parseFloat( match[ 2 ] ) * ( match[ 1 ] === "+" ? 1 : -1 );
				}
			}
			local[ prop.idx ] = value;
			return this[ fn ]( local );
		};
	} );
} );

// add cssHook and .fx.step function for each named hook.
// accept a space separated string of properties
color.hook = function( hook ) {
	var hooks = hook.split( " " );
	each( hooks, function( _i, hook ) {
		jQuery.cssHooks[ hook ] = {
			set: function( elem, value ) {
				var parsed;

				if ( value !== "transparent" &&
					( getType( value ) !== "string" ||
						( parsed = stringParse( value ) ) ) ) {
					value = color( parsed || value );
					value = value.toRgbaString();
				}
				elem.style[ hook ] = value;
			}
		};
		jQuery.fx.step[ hook ] = function( fx ) {
			if ( !fx.colorInit ) {
				fx.start = color( fx.elem, hook );
				fx.end = color( fx.end );
				fx.colorInit = true;
			}
			jQuery.cssHooks[ hook ].set( fx.elem, fx.start.transition( fx.end, fx.pos ) );
		};
	} );

};

color.hook( stepHooks );

jQuery.cssHooks.borderColor = {
	expand: function( value ) {
		var expanded = {};

		each( [ "Top", "Right", "Bottom", "Left" ], function( _i, part ) {
			expanded[ "border" + part + "Color" ] = value;
		} );
		return expanded;
	}
};

// Basic color names only.
// Usage of any of the other color names requires adding yourself or including
// jquery.color.svg-names.js.
colors = jQuery.Color.names = {

	// 4.1. Basic color keywords
	aqua: "#00ffff",
	black: "#000000",
	blue: "#0000ff",
	fuchsia: "#ff00ff",
	gray: "#808080",
	green: "#008000",
	lime: "#00ff00",
	maroon: "#800000",
	navy: "#000080",
	olive: "#808000",
	purple: "#800080",
	red: "#ff0000",
	silver: "#c0c0c0",
	teal: "#008080",
	white: "#ffffff",
	yellow: "#ffff00",

	// 4.2.3. "transparent" color keyword
	transparent: [ null, null, null, 0 ],

	_default: "#ffffff"
};

} );
