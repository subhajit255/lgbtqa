$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

$("form.formSubmit").on("submit", function (e) {
    e.preventDefault();
    var $this = $(this);
    var formActionUrl = $this.prop("action");

    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.setAttribute("data-kt-indicator", "on");
    submitButton.disabled = true;

    if ($($this).hasClass("fileUpload")) {
        var fd = new FormData(document.getElementById($($this).attr("id")));
    } else {
        var fd = $($this).serialize();
    }
    let commonOption = {
        type: "post",
        url: formActionUrl,
        data: fd,
        dataType: "json",
    };
    if ($($this).hasClass("fileUpload")) {
        commonOption["cache"] = false;
        commonOption["processData"] = false;
        commonOption["contentType"] = false;
    }
    $.ajax({
        ...commonOption,
        beforeSend: function () { },
        success: function (response) {
            if (response.status) {
                submitButton.removeAttribute("data-kt-indicator");
                submitButton.disabled = false;
                if (response.url) {
                    // Swal.fire({
                    //     icon: "success",
                    //     title: response.message,
                    //     showConfirmButton: false,
                    //     timer: 1500,
                    // });
                    toastr.success(response.message);
                    setTimeout(() => {
                        $(location).attr("href", response.url);
                    }, 1500);
                } else if (response.data.url) {
                    toastr.success(response.message);
                    setTimeout(() => {
                        $(location).attr("href", response.data.url);
                    }, 1500);
                } else {
                    // Swal.fire({
                    //     icon: "success",
                    //     title: response.message,
                    //     showConfirmButton: false,
                    //     timer: 1500,
                    // });
                    toastr.success(response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            } else {
                submitButton.removeAttribute("data-kt-indicator");
                submitButton.disabled = false;
                // Swal.fire({
                //     icon: "error",
                //     title: response.message,
                //     showConfirmButton: false,
                //     timer: 5000,
                // });
                toastr.error(response.message);
            }
        },
        error: function (response) {
            submitButton.removeAttribute("data-kt-indicator");
            submitButton.disabled = false;
            let responseJSON = response.responseJSON;
            $(".err_message").removeClass("d-block").hide();
            $("form .form-control").removeClass("is-invalid");
            $.each(responseJSON.errors, function (index, valueMessage) {
                toastr.warning(valueMessage);
                $(`#${index}`).addClass("is-invalid");
                // $(`#${index}`).after(
                //     `<p class='d-block text-danger err_message'> ${valueMessage}</p>`
                // );
            });
        },
    });
});

$(document).on("click", ".deleteData", function (e) {
    const actionUrl = baseUrl + "/ajax/delete/data";
    var $this = $(this);
    var uuid = $this.data("uuid");
    var find = $this.data("table");
    Swal.fire({
        title: "Are you sure you want to delete it?",
        text: "You wont be able to revert this action!!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "delete",
                url: actionUrl,
                data: { uuid: uuid, find: find },
                cache: false,
                dataType: "json",
                beforeSend: function () { },
                success: function (response) {
                    if (response.status) {
                        // Swal.fire({
                        //     icon: "success",
                        //     title: "Deleted Successfully",
                        //     showConfirmButton: false,
                        //     timer: 1500,
                        // });
                        toastr.success("Deleted Successfully");
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        // Swal.fire({
                        //     icon: "error",
                        //     title: "We are facing some technical issue now",
                        //     showConfirmButton: false,
                        //     timer: 2000,
                        // });
                        toastr.error("We are facing some technical issue now");
                    }
                },
                error: function (response) {
                    // Swal.fire({
                    //     icon: "error",
                    //     title: "We are facing some technical issue now. Please try again after some time",
                    //     showConfirmButton: false,
                    //     timer: 1500,
                    // });
                    toastr.error(
                        "We are facing some technical issue now. Please try again after some time"
                    );
                },
            });
        }
    });
});

$(document).on("click", ".isVerified", function (e) {
    e.preventDefault();
    const uuid = $(this).attr("data-uuid");
    const find = $(this).attr("data-table");
    const action = baseUrl + "/ajax/status/change";
    Swal.fire({
        title: "Are you sure you want to change it?",
        text: "The status will be changed",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, Change it!",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: action,
                method: "POST",
                data: {
                    status: this.value == 0 ? 1 : 0,
                    uuid: uuid,
                    find: find,
                },
                async: false,
                success: function (data) {
                    // Swal.fire({
                    //     icon: "success",
                    //     title: "Status Changed Successfully",
                    //     showConfirmButton: false,
                    //     timer: 1500,
                    // });
                    toastr.success("Status Changed Successfully");
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                },
                error: function (response) {
                    // Swal.fire({
                    //     icon: "error",
                    //     title: "We are facing some technical issue now. Please try again after some time",
                    //     showConfirmButton: false,
                    //     timer: 1500,
                    // });
                    toastr.error(
                        "We are facing some technical issue now. Please try again after some time"
                    );
                },
            });
        }
    });
});
