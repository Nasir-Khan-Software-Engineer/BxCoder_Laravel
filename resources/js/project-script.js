BxCoder.Project = (function(Urls){
    var deleteProject = function (id){

        console.log(id);
        console.log(Urls.deleteProject);


        BxCoder.Common.deleteAjaxCallPost(Urls.deleteProject.replace('projectId', id), function (response){
            if(response.status === 'success'){
                BxCoder.Datatable.deleteRow();
                toastr.success(response.message);
            }else{
                BxCoder.Common.showValidationErrors(response.errors);
            }
        })
    }

    return {
        deleteProject: deleteProject
    }
})(ProjectUrls);
