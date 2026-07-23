var baseUrl = baseUrl + "/";
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

let userReportColumn = [
    { data: "id", name: "id" },
    { data: "name", name: "name", sClass: "w-90" },
    { data: "email", name: "email", sorting: false },
    { data: "phone", name: "phone", sClass: "w-90" },
    { data: "since", name: "since", sorting: false },
    { data: "status", name: "status", sorting: false },
    { data: "registered_at", name: "registered_at", sorting: false },
];
var userReportTable = $("#kt_customers_table").DataTable({
    dom: "Bfrtip",
    buttons: [
        {
            extend: "excelHtml5",
            text: "Download Excel",
            title: "Data",
            exportOptions: {
                columns: ":visible",
            },
        },
    ],

    responsive: true,
    searching: false,
    lengthChange: true,

    language: {
        lengthMenu: "Counts per page_MENU_",
        searchPlaceholder: "Search by name",
    },
    autoWidth: false,
    processing: true,
    serverSide: true,
    ajax: {
        url: baseUrl + "ajax/user/list",
        dataType: "json",
        type: "get",
        data: function (d) {
            return $.extend({}, d, {
                group: $(".group").val() ?? "",
                daterange: $(".daterange").val() ?? "",
            });
        },
    },
    columns: userReportColumn,
    ordering: true,
    fnDrawCallback: function (oSettings) {
        let pagination = $(oSettings.nTableWrapper).find(
            ".dataTables_paginate,.dataTables_info,.dataTables_length"
        );
        oSettings._iDisplayLength > oSettings.fnRecordsDisplay()
            ? pagination.hide()
            : pagination.show();
    },
});

$(document).ready(function () {
    $(".search,.reset").on("click", function (e) {
        let $this = $(this);
        let table = $this.data("reload");
        if ($this.hasClass("reset")) {
            console.log($this.parents().eq(2));
            $this.parents().eq(2).trigger("reset");
            // location.reload();
        }
        $("#" + table)
            .DataTable()
            .ajax.reload();
    });
});
