$(function(){
    $("[data-tooltip]").tooltip({ html: true });
    $(".alert").alert();
    $("[data-confirm]").on('click', function(){
        return confirm($(this).data('confirm'));
    });
});
