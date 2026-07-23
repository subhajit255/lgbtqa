let imageId = [];
$(document).on('click', '.removeImage', function (e) {
    const currentImageId = $(this).attr('data-id');
    if(!imageId.includes(currentImageId)){
        imageId.push(currentImageId);
    }
    $('#remove_image').val(JSON.stringify(imageId));
});

function rMdiv(flag) {
    $(`#imgCls_${flag}`).remove();
}

function previewImages() {
    var preview = document.querySelector('#previewImages');
    if (this.files) {
        [].forEach.call(this.files, readAndPreview);
    }

    function readAndPreview(file) {
        if (!/\.(jpe?g|png|gif)$/i.test(file.name)) {
            Swal.fire({
                icon: 'error',
                title: `${file.name} is not an image`,
                showConfirmButton: false,
                timer: 1500
            })
        }
        var reader = new FileReader();
        reader.addEventListener("load", function () {
            var image = new Image();
            image.height = 100;
            image.title = file.name;
            image.src = this.result;
            preview.appendChild(image);
        });
        reader.readAsDataURL(file);
    }
}
document.querySelector('#file').addEventListener("change", previewImages);