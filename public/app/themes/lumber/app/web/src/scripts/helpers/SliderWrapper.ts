import $ from "jquery";
import Siema from "siema";

Siema.prototype.setAutoHeight = function (stopTime) {
	
    var that, timeout;
    that = this;

    function autoHeight() {
    	
        var currentItems, min, max, itemHeightList, height, maxHeight, i;
    	min = that.currentSlide;
        max =  min + that.perPage;
        itemHeightList = [];

        for (i = min; i < max; i++) {
            height = parseInt(that.innerElements[i].scrollHeight, 10);
            itemHeightList.push(height);
        }

        maxHeight = Math.max.apply(null, itemHeightList);
        $(that.selector).animate({ height: maxHeight }, 250);
        that.sliderFrame.style.maxHeight = maxHeight + 'px';
    }
    
    window.addEventListener('resize', function () {
        that.sliderFrame.style.maxHeight = '';
        clearTimeout(timeout);
        timeout = setTimeout(autoHeight, 500);
    });
    autoHeight();
};

export function sliderWrapper () {

    if ($(".sliderWrapper").length) {
        let sliders = {};
        $('.sliderWrapper').each((k,v) => {
            let siema = $(v).find(".siema").get(0);
            let controlNavigation = $(v).find(".siema-slider-nav");
            let hasTimer = $(v).hasClass("has-timer");
            let controls = $(v).attr("data-controls");
            let draggable = $(v).attr("data-draggable");
            let autoheight = $(v).attr("data-autoheight");
            let name = $(v).attr("data-name");
            let hasControls = (controls != "");

            let interval;
            let navigation = $(v).find(".navigation");
            let slider = new Siema({
                selector: siema,
                duration: 300,
                easing: 'ease-out',
                perPage: 1,
                startIndex: 0,
                draggable: draggable == "true" ? true : false,
                multipleDrag: false,
                threshold: 20,
                loop: false,
                rtl: false,
                onInit: () => {
                    navigation.find("span:eq(0)").addClass("current");
                },
                onChange: () => {
                    if (hasTimer) {
                        clearInterval(interval);
                        interval = setInterval(() => slider.next(), 5000);
                    }
                    navigation.find(".current").removeClass("current");
                    navigation.find("span:eq("+slider.currentSlide+")").addClass("current");
                    if (hasControls) {
                        $(controls).children(".current").removeClass("current");
                        $(controls).children("li:eq("+slider.currentSlide+")").addClass("current");
                    }
                    if (autoheight == 1) {
                        slider.setAutoHeight();
                    }
                }
            });

            if (autoheight == 1) {
                slider.setAutoHeight();
            }
            $(window).resize(() => {
                if (autoheight == 1) {
                    slider.setAutoHeight();
                }
            });

            sliders[name] = slider;

            $(window).on("keydown", (event) => {
                const key = event.keyCode; 
                if (key == 39) slider.next();
                if (key == 37) slider.prev();
            });

            controlNavigation.on("click", "a", (e) => {
                if ($(e.currentTarget).hasClass("left")) slider.prev();
                if ($(e.currentTarget).hasClass("right")) slider.next();
            });

            if (hasTimer) {
                interval = setInterval(() => slider.next(), 5000);
            }

            navigation.on("click", "span", (e) => {
                e.preventDefault();
                slider.goTo($(e.currentTarget).index());
            });

            if (hasControls) {
                $(controls).on("click", "a", (e) => {
                    e.preventDefault();
                    if ($(e.currentTarget).hasClass("arrow")) {
                        if ($(e.currentTarget).hasClass("left")) slider.prev();
                        if ($(e.currentTarget).hasClass("right")) slider.next();
                    } else {
                        slider.goTo($(e.currentTarget).parent().index());
                    }
                })
            }
        });
    }

};
