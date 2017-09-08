$(document).ready(function() {
    $('#moderate_bloc span').click(function () {
        $.ajax('http://localhost:8000/api/129/0').done(function (msg) {
            console.log(msg);
        });
    });
});