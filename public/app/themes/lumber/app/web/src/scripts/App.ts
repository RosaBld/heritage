
import "./../styles/main.scss";
// JQuery Ecosystem
import $ from "jquery";
require ("./helpers/jQueryPlugins");
require("jquery.scrollto");

// Transition Ecosystem
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
// import Rellax from "rellax";
import AOS from "aos";
gsap.registerPlugin(ScrollTrigger);

// import barba from '@barba/core';
// import transitionEffect from "./helpers/transitionEffect";
// var te = new transitionEffect();

// Ui item
// import GLightbox from "glightbox";
import Carousel from '../../../../../lumber/home';
// import {carouselWrapper} from "./helpers/CarouselWrapper";
import {Map} from "./fragment/Map";
// import { CookieConsentFn } from "./helpers/CookieConsent";
// Woocommerce
// require('./woocommerce/Global');

// Define all actions
let Site = {
    // Plugins 
    plugins: {
        carousel: Carousel,
        map: Map,
        // cookies: CookieConsentFn,
    },
    // Actions
    init: () => {
        Site.global();
    },
    transition: (response) => {
        Site.global();
        Site.scripts(response);
    },
    global: () => {
        /* Load all registered plugins */
        Object.keys(Site.plugins)
        .forEach(function(key, index) { 
            Site.plugins[key](); 
        });
        
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

// barba.init({
//     views: [],
//     transitions: [{
//         name: 'opacity-transition',
//         async leave({ current, next, trigger }) {
//             const done = this.async();
//             $('.show-menu').removeClass("show-menu");
//             te.toggle().eventCallback("onComplete", () => {
//                 gsap.to(current.container, {
//                     opacity: 0
//                 }).eventCallback("onComplete", () => {
//                     done();
//                 });
//             });;
//         },
//         after(data) {
//             let parser = new DOMParser();
//             let htmlDoc = parser.parseFromString(data.next.html.replace(/(<\/?)body( .+?)?>/gi, '$1notbody$2>', data.next.html), 'text/html');
//             let bodyClasses = htmlDoc.querySelector('notbody').getAttribute('class');
//             let dataPid = $(htmlDoc).find('notbody').data("pid");
//             document.body.setAttribute('class', bodyClasses);
//             $(window).scrollTo(0);
//             $("#main-header").find(".current-menu-item").removeClass("current-menu-item");
//             let newMenu = $("#main-header").find(".menu-item[data-pid="+ dataPid +"]");
//             if (newMenu.length) {
//                 newMenu.addClass("current-menu-item");
//             }
//             gsap.from(data.next.container, {
//                 opacity: 0
//             });
//             te.toggle().eventCallback("onComplete", () => {
//                 te.removeScreen();
//                 Site.transition(htmlDoc);
//             });
//         }
//     }]
// });

$(document).ready(function(){

    Site.init();

    // var rellax = new Rellax('.rellax',{
    //     center: true
    // });

    if ($(window).width() < 600) {
        $("[data-aos='fade-left']").attr("data-aos", "fade-up");
    }

    AOS.init({
        disable: 'mobile'
    });

    setTimeout(() => {AOS.refresh();}, 500);


    $.extend($.scrollTo.defaults, {
        axis: 'y'
    });

    $("body").on("click", "a[data-goto]", (e:Event) => {
        e.preventDefault();
        var node = $(e.currentTarget).attr("href").replace("/","");
        if (node.length) {
            const posY = $(node).offset().top - $("#main-header").height();
            if (posY >= 0) {
                $.scrollTo(posY, 400);
            }
        }
    });

    $("body").on("click", ".opener", (e) => {
        e.preventDefault();
        $(e.currentTarget).toggleClass("opened");
        $(e.currentTarget).parent().next().slideToggle(250);
        // if ($(e.currentTarget).hasClass("opened"))
            $( document ).trigger( "updateGlider");
    });

    $(".opener:not(.first)").trigger("click");

    $("body").on("click", ".resource-item.large", (e)=> {
        e.preventDefault();
        $(e.currentTarget).toggleClass('opened');
        $(e.currentTarget).find(".content").slideToggle(250);
    });

    let allisOpened = false;
    $("body").on("click", "a[href='#expand']", (e) => {
        e.preventDefault();
        if (!allisOpened)
            $('.opener:not(.opened)').toggleClass("opened").parent().next().slideToggle(250);
        else
            $('.opener.opened').toggleClass("opened").parent().next().slideToggle(250);
        $( document ).trigger( "updateGlider");
        allisOpened = !allisOpened;
        $(e.currentTarget).toggleClass("opened");
    });

    $('body').on("click", ".shared-item", (e:Event) => {
        e.preventDefault();
        window.open($(e.currentTarget).attr("href"), "", "width=500,height=350");
    });

    $("body").on("click", "#menu-switcher a", (e:Event) => {
        e.preventDefault();
        $("#nav-main").toggleClass("show-menu");
        $("body").toggleClass("show-menu");
        $(e.currentTarget).toggleClass("show-menu");
    });

    $("body").on("click", ".goTo, [href^='#']:not(.glightbox)", (e:Event) => {
        const elm = $(e.currentTarget).attr("href");
        if ($(elm).length) {
            const posY = $(elm).offset().top - ($("#main-header").height() + 20);
            $.scrollTo(posY, 400);
        }
    });

    var lastScrollTop = 0;
    let scrollFn = function(){
        var st = $(window).scrollTop();
        if(st > 250) {
            $('#main-header').addClass('scrolled');
        } else {
            $('#main-header').removeClass('scrolled');
        }
        lastScrollTop = st;
    };
    $(window).scroll(scrollFn);

    scrollFn.call(null);

    // $('.position-item.article').each((k,v)=>{
    //     let oHeight = 0;
    //     oHeight = $(v).find(".content").outerHeight();
    //     $(v).find(".content").css({ paddingTop: 40 });
    //     $(v).children('a').children().css({ top: oHeight});

    //     $(v).hover(
    //         () => {
    //             oHeight = $(v).find(".content").outerHeight();
    //             $(v).find(".content").css({ paddingTop: 0 });
    //             $(v).children("a").children().css({
    //                 top: 0
    //             });
    //         }, () => {
    //             $(v).children("a").children().css({
    //                 top: oHeight 
    //             });
    //             $(v).find(".content").css({ paddingTop: 40 });
    //         }
    //     );
    // });

    // $(document).resize(()=>{
    //     $('.position-item.article').each((k,v)=>{
    //         let oHeight = 0;
    //         oHeight = $(v).find(".content").outerHeight();
    //         $(v).children('a').children().css({ top: oHeight});
    //         $(v).find(".content").css({ paddingTop: 40 });
    //     });
    // });
    
    $("body").on("click", "*[data-link]", (e) => {
        document.location = $(e.currentTarget).attr("data-link");
    });

    if ($("#search-form-faq").length)
    {
        $('.close-btn').on("click", (e) => {
            e.preventDefault();
            $('.found').removeClass("found");
            $('.results').html("");
            $('.close-btn').addClass("hidden");
        });
        $("#search-form-faq").on("submit", (e) => {
            e.preventDefault();
            let value = $("#search-form-faq .search-box").val();
            // Remove all classes
            // $(".opener.found").removeClass("opened");
            // $(".bottle.found").removeClass("shown");
            $('.found').removeClass("found");
            //
            if (value != "") {
                $('.close-btn').removeClass("hidden");
                $("#questions").addClass("found");
                let elm = $(".question:contains('"+value+"')");
                elm.addClass("found");
                let gf = elm.parents(".category");
                gf.addClass("found");
                gf.children("h2").children("a").addClass("found");
                gf.children(".bottle").addClass("found");
                $('.results').html("Élément(s) trouvé(s) pour le terme '"+ value +"': "+ elm.length +"");
            } else {
                $('.close-btn').addClass("hidden");
                $('.results').html("");
            }
        });
    }

});
