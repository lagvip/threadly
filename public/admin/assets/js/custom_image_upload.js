// public/assets/js/custom_image_upload.js

document.addEventListener('DOMContentLoaded', function() {
    // Hàm chung để khởi tạo chức năng tải ảnh cho một cặp ID cụ thể
    function initializeImageUpload(wrapperId, inputId, fileNameId, previewId, leftImagePreviewId = null, existingImageUrl = null) {
        const imageUploadWrapper = document.getElementById(wrapperId);
        const actualImageInput = document.getElementById(inputId);
        const selectedFileName = document.getElementById(fileNameId);
        const imagePreview = document.getElementById(previewId);
        const leftImagePreview = leftImagePreviewId ? document.getElementById(leftImagePreviewId) : null;

        if (!imageUploadWrapper || !actualImageInput || !selectedFileName || !imagePreview) {
            console.warn(`Missing elements for image upload: ${wrapperId}, ${inputId}, ${fileNameId}, ${previewId}`);
            return;
        }

        // Kích hoạt input file khi click vào vùng tải ảnh
        imageUploadWrapper.addEventListener('click', function() {
            actualImageInput.click();
        });

        // Xử lý khi file được chọn hoặc kéo thả vào input
        actualImageInput.addEventListener('change', handleFiles);

        // Xử lý kéo thả (Drag and Drop)
        imageUploadWrapper.addEventListener('dragover', function(e) {
            e.preventDefault(); // Ngăn chặn hành vi mặc định (mở file trong trình duyệt)
            imageUploadWrapper.style.borderColor = '#007bff'; // Đổi màu viền khi kéo qua
        });

        imageUploadWrapper.addEventListener('dragleave', function() {
            imageUploadWrapper.style.borderColor = '#ccc'; // Khôi phục màu viền khi rời đi
        });

        imageUploadWrapper.addEventListener('drop', function(e) {
            e.preventDefault();
            imageUploadWrapper.style.borderColor = '#ccc'; // Khôi phục màu viền

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                actualImageInput.files = files; // Gán file đã thả vào input file
                handleFiles(); // Gọi hàm xử lý file
            }
        });

        // Hàm xử lý file (hiển thị tên, xem trước ảnh và cập nhật ảnh bên trái)
        function handleFiles() {
            if (actualImageInput.files && actualImageInput.files.length > 0) {
                const file = actualImageInput.files[0];
                selectedFileName.textContent = 'File đã chọn: ' + file.name;

                // Hiển thị xem trước ảnh và cập nhật ảnh bên trái
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.innerHTML = `<img src="${e.target.result}" alt="Image Preview">`;
                        if (leftImagePreview) {
                            leftImagePreview.src = e.target.result;
                            leftImagePreview.style.display = 'block'; // Đảm bảo ảnh hiển thị
                        }
                    };
                    reader.readAsDataURL(file);
                } else {
                    imagePreview.innerHTML = ''; // Xóa xem trước nếu không phải ảnh
                    selectedFileName.textContent = 'File không phải là ảnh. Vui lòng chọn lại.';
                    // Nếu file không phải ảnh, khôi phục ảnh cũ hoặc ẩn ảnh bên trái
                    if (leftImagePreview) {
                        if (existingImageUrl) { // Kiểm tra nếu có URL ảnh cũ
                            leftImagePreview.src = existingImageUrl;
                            leftImagePreview.style.display = 'block';
                        } else {
                            leftImagePreview.src = '';
                            leftImagePreview.style.display = 'none';
                        }
                    }
                }
            } else {
                selectedFileName.textContent = '';
                imagePreview.innerHTML = '';
                // Nếu không có file mới được chọn, hiển thị lại ảnh cũ (nếu có)
                if (leftImagePreview) {
                    if (existingImageUrl) { // Kiểm tra nếu có URL ảnh cũ
                        leftImagePreview.src = existingImageUrl;
                        leftImagePreview.style.display = 'block';
                    } else {
                        leftImagePreview.src = '';
                        leftImagePreview.style.display = 'none';
                    }
                }
            }
        }
    }

    // --- Khởi tạo cho trang Add Category ---
    // Chỉ khởi tạo nếu các phần tử tồn tại (tránh lỗi nếu script được nhúng ở trang không có các ID này)
    if (document.getElementById('imageUploadWrapper')) {
        initializeImageUpload('imageUploadWrapper', 'actualImageInput', 'selectedFileName', 'imagePreview');
    }

    // --- Khởi tạo cho trang Update Category ---
    // Đảm bảo rằng bạn truyền URL ảnh hiện có từ PHP nếu có
    // (existingImageUrl sẽ được truyền từ Blade template qua data attribute)
    if (document.getElementById('imageUploadWrapperUpdate')) {
        const existingImageUrl = document.getElementById('imageUploadWrapperUpdate').dataset.existingImageUrl;
        initializeImageUpload('imageUploadWrapperUpdate', 'actualImageInputUpdate', 'selectedFileNameUpdate', 'imagePreviewUpdate', 'leftImagePreview', existingImageUrl);
    }
});