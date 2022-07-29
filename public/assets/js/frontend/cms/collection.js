define(['jquery', 'bootstrap', 'frontend', 'template', 'form'], function ($, undefined, Frontend, Template, Form) {
    var Controller = {
        index: function () {
            $(document).on('click', '.btn-delete', function () {
                var that = this;
                var url = $(that).data("url");
                var id = $(that).data("id");
                Fast.api.ajax({
                    url: url,
                    data: {id: id}
                }, function (data) {
                    setTimeout(function () {
                        location.reload();
                    }, 1500);
                });
                return false;
            });
        },
    };
    return Controller;
});
