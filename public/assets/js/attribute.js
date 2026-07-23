var flag = 1;
$(document).on("click", ".addMore", function () {
    let html = `<div class="row pt-2" id="app_${flag}">
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Enter Value" name="values[]" id="values">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <i class="fa fa-trash-o pt-4" aria-hidden="true" onclick="rMdiv(${flag})"></i>
                    </div>
                </div>`;
    $('.appendDiv').append(html);
    flag++;
});

function rMdiv(flag) {
    $(`#app_${flag}`).remove();
}