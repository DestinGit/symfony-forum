$(document).ready(function() {
    $('#moderate_bloc span').click(function () {
        var status = $(this).data('status');

        $.ajax('http://localhost:8000/api/129/0').done(function (msg) {
            console.log(msg);
        });
    });
});