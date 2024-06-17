import $ from "jquery"
import swal from "sweetalert";

$(document).ready(() => {

    $('label:contains(Confirm Password)').html("Confirmation du mot de passe " + '<span aria-hidden="true" role="presentation" class="field_required" style="color:#ee0000;">*</span>');

    $("form.add-to-cart").on("submit",(e) => {
        e.preventDefault();
        let jqxhr = $.post($(e.currentTarget).attr("action"), $(e.currentTarget).serialize(), (data) => {
            let htmlNode = $(data);
            $("body").find(".Cart.cart-mini-contents").html(htmlNode.find(".Cart.cart-mini-contents").html());
            $(e.currentTarget).find(".cart").addClass("ok");
            $("#nav-main").find(".icons").addClass("open");
            setTimeout(() => {
                $(e.currentTarget).find(".cart").removeClass("ok");
                $("#nav-main").find(".icons").removeClass("open");
            }, 2000);
        });
        jqxhr.fail(() => {
            alert("Il y a eu un souci avec l'ajout de votre produit dans le panier.");
        });
    });

    $('.Cart.preview.cart-mini-contents').on("click", "a.rm", (e) => {
        e.preventDefault();
        let current = $(e.currentTarget);
        swal({
            title: "",
            text: current.attr("data-message"),
            icon: "warning",
            buttons: true,
            dangerMode: true,
          })
          .then((willDelete) => {
            if (willDelete) {
                $.get(current.attr("href"), (data) => {
                    let htmlNode = $(data);
                    $("body").find(".Cart.cart-mini-contents").html(htmlNode.find(".Cart.cart-mini-contents").html());
                    $( document.body ).trigger( 'updated_cart_totals' );
                    swal(current.attr("data-success"), {
                        icon: "success",
                    });

                    if ($(".woocommerce-cart.woocommerce-page").length) {
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                });
            } else {
              swal(current.attr("data-stop"));
            }
          });
    });

    let timeout;
	$('.woocommerce-cart-form__contents').on('change', 'input.qty', function(){
 
		if ( timeout !== undefined ) {
			clearTimeout( timeout );
		}
 
		timeout = setTimeout(function() {
			$("[name='update_cart']").trigger("click");
		}, 500 ); // 1 second delay, half a second (500) seems comfortable too
 
	});

});