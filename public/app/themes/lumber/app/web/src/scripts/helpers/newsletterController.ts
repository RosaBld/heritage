import $ from "jquery";

$(document).ready(function(){
    $("body").on("submit", ".newsletter-block form", (e) => {
        let form = $(e.currentTarget);
        let email = form.find(".newsletter-email");
        let language = form.find(".newsletter-language");
        e.preventDefault();
        form.find("button").addClass("wait");
        $.post("/api/newsletter/subscribe", { email: email.val(), language: language.val() }).done((data) => {
            form.find("button").removeClass("wait").addClass("success");
            setTimeout(() => {
                email.val("");
                form.find("button").removeClass("success");
            }, 5000);
        });
    });
});