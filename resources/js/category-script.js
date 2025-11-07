BxCoder.Category = (function(Urls) {

    var deleteCategory = function(id) {

        console.log(id);
        console.log(Urls.deleteCategory);

        BxCoder.Common.deleteAjaxCallPost(
            Urls.deleteCategory.replace('categoryId', id),
            function(response) {

                if(response.status === 'success') {
                    BxCoder.Datatable.deleteRow();
                    toastr.success(response.message);
                } else {
                    BxCoder.Common.showValidationErrors(response.errors);
                }
            }
        );
    }

    return {
        deleteCategory: deleteCategory
    }

})(CategoryUrls);
