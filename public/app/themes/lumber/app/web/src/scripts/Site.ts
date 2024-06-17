import Carousel from "../../../../home";
// import GLightbox from "glightbox";
import $ from "jquery";

let Site = {
    // Plugins
    carousel: Carousel,
    // Actions
    init: () => {
        Site.global();
    },
    transition: (response) => {
        Site.global();
        Site.scripts(response);
    },
    global: () => {
        Site.carousel();
        // GLightbox({
        //     touchNavigation: false,
        //     touchFollowAxis: false,
        //     keyboardNavigation: false,
        // });
    },
    scripts: (response) => {
        var initialized_scripts = [];
        var script_tags = document.getElementsByTagName("script");
        $(script_tags).each(function(i, s) {
            var src = $(s).attr('src');
            if (src) {
                initialized_scripts.push(src);
            }
        });
        var new_imports = [];
        var new_evaluations = '';
        var script_tags = $(response).find('script');
        $(script_tags).each(function(i, s) {
            var src = $(s).attr('src');
            if (src) {
                // if not already initialized add it
                if (initialized_scripts.indexOf(src) == -1) {
                    new_imports.push(src);
                }
            } else {
                // it is an inline script, will evaluate it
                new_evaluations += script_tags[i].innerHTML;
            }
        });
        $.getMultiScripts(new_imports).done(function() {
            eval(new_evaluations);
        }).fail(function(error) {
            // one or more scripts failed to load
        }).always(function() {
            // always called, both on success and error
        });
    }
};

export default Site;