import { Loader } from '@googlemaps/js-api-loader';
import $ from "jquery";

let markers = [];

export function Map () {
    function createMarkers (google, map) {
        let infowindowElm = null;
        for (let i = 0; i < markers.length; i++) {
            markers[i].setMap(null);
        }
        markers = [];
    
        $('.property-item:not(.hidden), .tease-event:not(.hidden),#map[data-marker]').each((k, v) => {
            if ($(v).attr("data-lat") !== void 0 && $(v).attr("data-lat") != '' && $(v).attr("data-lng") !== void 0 && $(v).attr("data-lng") != "") {
                let content = $(v).parent().html();
                let latLng = { lat: parseFloat($(v).attr("data-lat")), lng: parseFloat($(v).attr("data-lng")) };
                let marker = new google.maps.Marker({
                    position: latLng,
                    icon: "/app/themes/lumber/static/gfx/pin.svg"
                });
    
                if ($(v).attr("data-marker") !== void 0) { 
                    content = $(v).attr("data-marker");
                    map.setCenter(latLng);
                }
                markers.push(marker);
                marker.setMap(map);
    
                let infowindow = new google.maps.InfoWindow({
                    content: `<div class="pop-up-map">${ content }</div>`,
                    disableAutoPan: true
                });
                marker.addListener("click", (e) => {
                    if (infowindowElm != null) infowindowElm.close();
                    infowindow.open({
                        anchor: marker,
                        map
                    });
                    infowindowElm = infowindow;
                });
            }
        });
    }

    $(document).ready(() => {
        if ($("#map").length) {
            const apiKey = $("#map").attr("data-uid");
            const loader = new Loader({
                apiKey: apiKey,
                version: "weekly",
                libraries: []
            });

            let mapStyled = [{"featureType":"all","elementType":"labels.text.fill","stylers":[{"saturation":36},{"color":"#333333"},{"lightness":40}]},{"featureType":"all","elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#ffffff"},{"lightness":16}]},{"featureType":"all","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#fefefe"},{"lightness":20}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#fefefe"},{"lightness":17},{"weight":1.2}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#f5f5f5"},{"lightness":20}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#f5f5f5"},{"lightness":21}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#dedede"},{"lightness":21}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#c0eea4"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#ffffff"},{"lightness":17}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#ffffff"},{"lightness":29},{"weight":0.2}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#ffffff"},{"lightness":18}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#ffffff"},{"lightness":16}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#f2f2f2"},{"lightness":19}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#e9e9e9"},{"lightness":17}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#a6edf1"}]}];
            
            const mapOptions = {
                center: {
                lat: 50.477380,
                lng: 4.186350
                },
                zoom: 13,
                mapTypeControl: false,
                fullscreenControl: false,
                styles: mapStyled
            };


            loader
                .load()
                .then((google) => {
                    let map = new google.maps.Map(document.getElementById("map"), mapOptions);
                    //
                    createMarkers(google, map);
                    $('.filters').on("click", ".filter", (e) => {
                        $('.no-elements').hide();
                        let groups = { category: [], status: [], type: [], location: [], surface: [] };
                        e.preventDefault();
                        $(e.currentTarget).toggleClass('is-active');
                        let lgt = $('.filters').find(".is-active").length;
                        if (lgt == 0) {
                            $(".property-item, .tease-event, .tool").removeClass("hidden");
                            $(".property-item, .tease-event, .tool").parent().fadeIn(200);       
                            createMarkers(google,map);
                        }  else {
                            $(".property-item, .tease-event, .tool").addClass("hidden").parent().hide();
                            $('.filters').find(".is-active").each((k, v) => {
                                let grp = $(v).attr("data-group");
                                let cat = $(v).attr("data-category");
                                if (groups[grp] !== void 0) { groups[grp].push(cat); }
                                let f = groups.category.join('') + groups.status.join('') + groups.status.join('') + groups.type.join('') + groups.location.join('') + groups.surface.join('');
                                $(f).removeClass("hidden").parent().fadeIn(200);
                                if ($(".property-item:not(.hidden), .tease-event:not(.hidden), .tool:not(.hidden)").length == 0) $('.no-elements').fadeIn();
                                if (k == length - 1) {
                                    createMarkers(google,map);
                                }
                            });
                        }
                    });
                })
            ;
        }
    });
}
