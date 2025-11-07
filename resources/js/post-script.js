BxCoder.Post = (function(Urls){
    var deletePost = function (id){

        console.log(id);
        console.log(Urls.deleteProject);


        BxCoder.Common.deleteAjaxCallPost(Urls.deletePost.replace('postId', id), function (response){
            if(response.status === 'success'){
                BxCoder.Datatable.deleteRow();
                toastr.success(response.message);
            }else{
                BxCoder.Common.showValidationErrors(response.errors);
            }
        })
    }

    return {
        deletePost: deletePost
    }
})(PostUrls);
