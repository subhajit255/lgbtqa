$(document).on("click", ".get-role", function () {
    const role = $(this).data("name");
    const id = $(this).data("id");
    const action = $(this).data("action");
    Swal.fire({
        text: "Are you sure you want to Edit Permission ?",
        icon: "warning",
        showCancelButton: true,
        buttonsStyling: false,
        confirmButtonText: "Yes, Edit!",
        cancelButtonText: "No, cancel",
        customClass: {
            confirmButton: "btn fw-bold btn-danger",
            cancelButton: "btn fw-bold btn-active-light-primary",
        },
    }).then(function (result) {
        if (result.value) {
            if (result.isConfirmed) {
                window.location.href = action;
            }
        } else if (result.dismiss === "cancel") {
            Swal.fire({
                text: "Permission Not Edited",
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
