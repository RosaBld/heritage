// import $ from "jquery";
// import Glide from "@glidejs/glide";
// import { siblings } from '@glidejs/glide/src/utils/dom';

// const CustomActiveClass = function (Glide, Components, Events) {
//     var Component = {
//         mount() {
//             this.changeActiveSlide();
//         },

//         changeActiveSlide() {
//             let selector = Glide.selector;
//             let control = Glide.control;
//             $(selector).find(".controls").children().removeClass("is-active");
//             let slide = Components.Html.slides[Glide.index];
//             selector.classList.remove("has-next", "has-prev", "hide-next");
//             slide.classList.remove('is-next', 'is-prev');
//             slide.classList.add('is-active');
//             $(selector).find(".controls").find(".control:eq("+ (Glide.index + 1) +")").addClass("is-active");
 
//             siblings(slide).forEach((sibling) => {
//                 sibling.classList.remove('is-active', 'is-next', 'is-prev');
//             });

//             if(slide.nextElementSibling) {
//                 selector.classList.add("has-next");
//                 slide.nextElementSibling.classList.add('is-next');
//             }
            
//             if(slide.previousElementSibling) {
//                 selector.classList.add("has-prev");
//                 slide.previousElementSibling.classList.add('is-prev');
//             }
            
//             let sizes = Components.Sizes.length;
//             let settings = Glide.settings;
//             if (sizes < settings.perView) {
//                 selector.classList.add("hide-next");
//             }
//         },
//     };

//     Events.on("resize", () => {
//         Component.changeActiveSlide();
//     });

//     Events.on('run', () => {
//         Component.changeActiveSlide();
//     });

//     return Component;
// };

// export function carouselWrapper () {
//     if ($('.carouselWrapper').length && $('.glide__slides').length && $('.glide__slides').children().length) {
//         $('.carouselWrapper').each((k,e) => {
//             let gap = $(e).attr("data-gap") !== void 0 ? $(e).attr("data-gap") : 50;
//             let autoplay = $(e).attr("data-autoplay") !== void 0 ? $(e).attr("data-autoplay") : 0;
//             let mobileGap = $(e).attr("data-mobile-gap") !== void 0 ? $(e).attr("data-mobile-gap") : 20;
//             let view = $(e).attr("data-view") !== void 0 ? $(e).attr("data-view") : 2.5;
//             let mobileView = $(e).attr("data-mobile-view") !== void 0 ? $(e).attr("data-mobile-view") : 1.3;
//             let type = $(e).attr("data-type") !== void 0 ? $(e).attr("data-type") : "slider";
//             let focus = $(e).attr("data-focus") !== void 0 ? $(e).attr("data-focus") : 0;
//             let peek = $(e).attr("data-peek") !== void 0 ? $(e).attr("data-peek") : 0;
//             let bound = $(e).attr("data-bound") !== void 0 ? true : false;

//             let lgGap = $(e).attr("data-lg-gap") !== void 0 ? $(e).attr("data-lg-gap") : gap;
//             let lgView = $(e).attr("data-lg-view") !== void 0 ? $(e).attr("data-lg-view") : view;
//             let lgPeek = $(e).attr("data-lg-peek") !== void 0 ? $(e).attr("data-lg-peek") : peek;
            
//             let mdGap = $(e).attr("data-md-gap") !== void 0 ? $(e).attr("data-md-gap") : lgGap;
//             let mdView = $(e).attr("data-md-view") !== void 0 ? $(e).attr("data-md-view") : lgView;
//             let mdPeek = $(e).attr("data-md-peek") !== void 0 ? $(e).attr("data-md-peek") : lgPeek;
            
//             let smGap = $(e).attr("data-sm-gap") !== void 0 ? $(e).attr("data-sm-gap") : mdGap;
//             let smView = $(e).attr("data-sm-view") !== void 0 ? $(e).attr("data-sm-view") : mdView;
//             let smPeek = $(e).attr("data-sm-peek") !== void 0 ? $(e).attr("data-sm-peek") : mdPeek;
            
//             let xsGap = $(e).attr("data-xs-gap") !== void 0 ? $(e).attr("data-xs-gap") : smGap;
//             let xsView = $(e).attr("data-xs-view") !== void 0 ? $(e).attr("data-xs-view") : smView;
//             let xsPeek = $(e).attr("data-xs-peek") !== void 0 ? $(e).attr("data-xs-peek") : smPeek;
//             let xsFocusAt = $(e).attr("data-xs-focus") !== void 0 ? $(e).attr("data-xs-focus") : "center";

//             let externalControl = $(e).attr("data-external-control") !== void 0 ? $($(e).attr("data-external-control")) : null;

//             let glide = new Glide(e, {
//                 type: type,
//                 focusAt: focus,
//                 perView: view,
//                 gap: gap,
//                 startAt: 0,
//                 bound: bound,
//                 peek: peek,
//                 autoplay: autoplay,
//                 hoverpause: false,
//                 breakpoints: {
//                     1280: {
//                         gap: lgGap,
//                         perView: lgView,
//                         peek: lgPeek
//                     },
//                     1050: {
//                         gap: mdGap,
//                         perView: mdView,
//                         peek: mdPeek
//                     },
//                     780: {
//                         gap: smGap,
//                         peek: smPeek,
//                         perView: smView
//                     },
//                     568 : {
//                         // peek: 40,
//                         focusAt: xsFocusAt,
//                         gap: xsGap,
//                         peek: 0,
//                         perView: xsView
//                     }
//                 }
//             });

//             glide.mount({
//                 'CustomActiveClass': CustomActiveClass,
//             });

//             if (externalControl != null) {
//                 externalControl.on("click", "a", (e) => {
//                     e.preventDefault();
//                     glide.go('=' + $(e.currentTarget).attr("data-index"));
//                 });

//                 glide.on("run", () => {
//                     externalControl.find(".current").removeClass("current");
//                     externalControl.children("li:eq("+glide.index+")").addClass("current");
//                 });
//             }

//             $( document ).on( "updateGlider", function( event ) {
//                 glide.update();
                
//             });

//             // glide.on(['mount.after'], function() {
                
                    
//                     $('.position-item.article').each((k,v)=>{
//                         let oHeight = 0;
//                         oHeight = $(v).find(".content").outerHeight();
//                         $(v).children('a').children().css({ top: oHeight});
//                         $(v).find(".content").css({ opacity: 0 });
                
//                         $(v).hover(
//                             () => {
//                                 oHeight = $(v).find(".content").outerHeight();
//                                 // $(v).find(".content").css({ paddingTop: 0 });
//                                 $(v).children("a").children().css({
//                                     top: 0
//                                 });
//                                 $(v).find(".content").css({ opacity: 1 });
//                             }, () => {
//                                 // $(v).find(".content").css({ paddingTop: 40 });
//                                 $(v).find(".content").css({ opacity: 0 });
//                                 $(v).children("a").children().css({
//                                     top: oHeight 
//                                 });
//                             }
//                         );
//                     });
//             // })
        
//             $(document).resize(()=>{
//                 $('.position-item.article').each((k,v)=>{
//                     let oHeight = 0;
//                     oHeight = $(v).find(".content").outerHeight();
//                     $(v).children('a').children().css({ top: oHeight});
                    
//                 });
//             });

//         });
//     }
// }
