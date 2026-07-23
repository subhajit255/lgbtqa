$(document).on("click", ".logOut", function (e) {
    const action = $(this).attr("action");
    Swal.fire({
        text: "Are you sure to logged off ?",
        icon: "warning",
        showCancelButton: true,
        buttonsStyling: false,
        confirmButtonText: "👉 Yes, Do it! ",
        cancelButtonText: "No, cancel 😒",
        customClass: {
            confirmButton: "btn fw-bold btn-danger",
            cancelButton: "btn fw-bold btn-active-light-primary",
        },
    }).then(function (result) {
        if (result.value) {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: "success",
                    title: "Logout Successfully 😊",
                    showConfirmButton: false,
                    timer: 1500,
                });
                setTimeout(() => {
                    window.location.href = action;
                }, 1500);
            }
        } else if (result.dismiss === "cancel") {
            Swal.fire({
                text: "Logged off cancelled!",
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                    confirmButton: "btn fw-bold btn-primary",
                },
            });
        }
    });
});

$(document).on("click", ".goTo", function () {
    const action = $(this).attr("data-action");
    $(location).attr("href", action);
});

$(document).on("click", ".checkValChange", function () {
    this.value = 0;
    if (this.checked) {
        this.value = 1;
    }
});

$(".fromAlias").keyup(function () {
    let Text = $(this).val();
    Text = Text.toLowerCase();
    Text = Text.replace(/[^a-zA-Z0-9]+/g, "-");
    $(".toAlias").val(Text);
});

$(".toggle-password").click(function () {
    $(this).toggleClass("fa-eye fa-eye-slash");
    const input = $($(this).attr("toggle"));
    if (input.attr("type") == "password") {
        input.attr("type", "text");
    } else {
        input.attr("type", "password");
    }
});

$(document).on("keypress", ".number-only", function (e) {
    if (isNaN(this.value + "" + String.fromCharCode(e.charCode))) return false;
});
