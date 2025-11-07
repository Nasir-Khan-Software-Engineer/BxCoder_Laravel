BxCoder.Common = (function(){
    var getFormData = function(selector) {
        var data = {};
        var dataArray = $(selector).serializeArray();

        $.each(dataArray, function (index, item) {
            if (data[item.name]) {
                if (!Array.isArray(data[item.name])) {
                    data[item.name] = [data[item.name]];
                }
                data[item.name].push(item.value.trim());
            } else {
                data[item.name] = item.value.trim();
            }
        });

        // Now, manually add file inputs
        $(selector).find('input[type="file"]').each(function() {
            var inputName = $(this).attr('name');
            var files = this.files; // FileList object

            if (files.length === 1) {
                data[inputName] = files[0]; // single file
            } else if (files.length > 1) {
                data[inputName] = Array.from(files); // array of files
            }
        });

        return data;
    }


    var toastFormErrorMessages = function(errors){
        $.each(errors, function(key, item){
            let errorMsgs = [];

            $.each(item, function(index, value){
                errorMsgs.push(value);
            });

            toastr.error(errorMsgs);
        });
    }

    var isValidEmail = function(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    var isValidPhoneNumber = function(phoneNumber) {
        var phoneRegex = /^\d{11}$/;
        return phoneRegex.test(phoneNumber);
    }


    var somethingWrongToast = function(response){
        toastr.error("Something went wrong, please try later.");
    }

    var postAjaxCall = function(endPoint, data, callbackSuccess, callbackError){
        $.ajax({
            url: endPoint,
            method: 'POST',
            contentType: "application/json",
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                callbackSuccess(response);
            },
            error: function(response) {
                if(callbackError == null){
                    somethingWrongToast(response);
                }
                else{
                    callbackError(response);
                }
            }
        });
    }

    var putAjaxCall = function(endPoint, data, callbackSuccess, callbackError){
        $.ajax({
            url: endPoint,
            method: 'PUT',
            contentType: "application/json",
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                callbackSuccess(response);
            },
            error: function(response) {
                if(callbackError == null){
                    somethingWrongToast(response);
                }
                else{
                    callbackError(response);
                }
            }
        });
    }

    var putAjaxCallPost = function(endPoint, data, callbackSuccess, callbackError){
        if (typeof data === 'string') {
            data = JSON.parse(data);
        }

        data._token = $('meta[name="csrf-token"]').attr('content'); // CSRF
        data._method = 'PUT'; // or 'PATCH'

        $.ajax({
            url: endPoint,
            method: 'POST',
            contentType: "application/json",
            data: JSON.stringify(data),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                callbackSuccess(response);
            },
            error: function(response) {
                if(callbackError == null){
                    somethingWrongToast(response);
                }
                else{
                    callbackError(response);
                }
            }
        });
    }

    var getAjaxCall = function(endPoint, callbackSuccess, callbackError){
        $.ajax({
            url: endPoint,
            method: 'GET',
            contentType: "application/json",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                callbackSuccess(response);
            },
            error: function(response) {
                if(callbackError == null){
                    somethingWrongToast(response);
                }
                else{
                    callbackError(response);
                }
            }
        });
    }

    var deleteAjaxCall = function (endpoint, callbackSuccess, callbackError){
        $.ajax({
            url: endpoint,
            method: 'DELETE',
            contentType: false,
            cache: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response){
                callbackSuccess(response);
            },
            error: function(response) {
                if(callbackError == null){
                    somethingWrongToast(response);
                }
                else{
                    callbackError(response);
                }
            }
        });
    }

    var deleteAjaxCallPost = function (endpoint, callbackSuccess, callbackError) {
        $.ajax({
            url: endpoint,
            method: 'POST', // use POST instead of DELETE
            data: {
                _method: 'DELETE',
                _token: $('meta[name="csrf-token"]').attr('content') // CSRF protection
            },
            success: function (response) {
                if (typeof callbackSuccess === "function") {
                    callbackSuccess(response);
                }
            },
            error: function (response) {
                if (typeof callbackError === "function") {
                    callbackError(response);
                } else {
                    somethingWrongToast(response);
                }
            }
        });
    };


    var preivewImage = function(container, input){
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $(container).css('background-image', 'url('+e.target.result +')');
                $(container).hide();
                $(container).fadeIn(650);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    var renderCustomDataTable = function (data, filter){

    }

    var showBootstrapModal = function(modalID){
        return $("#"+modalID).modal('show');
    }

    var hideBootstrapModal = function(modalID){
        return $("#"+modalID).modal('hide');
    }

    var dataTableActionCell = function(id, className, customDataAttribute, btns=['show', 'edit', 'delete'], deletebtnAttribute=''){
        let actionCell = [];

        if(btns.includes('show')){
            actionCell.push(`<button data-id="${id}" class="btn btn-sm thm-btn-bg thm-btn-text-color show-${className}" ${customDataAttribute}><i class="fa-solid fa-eye"></i></button>`);
        }

        if(btns.includes('edit')){
            actionCell.push(` <button data-id="${id}" class="btn btn-sm thm-btn-bg thm-btn-text-color edit-${className}" ${customDataAttribute}><i class="fa-solid fa-pen-to-square"></i></button>`);
        }

        if(btns.includes('delete')){
            actionCell.push(` <button data-id="${id}" class="btn btn-sm thm-btn-bg thm-btn-text-color delete-${className}" ${customDataAttribute}  ${deletebtnAttribute}><i class="fa-solid fa-trash"></i></button>`);
        }

        return actionCell.join('');
    }

    var dataTableCreatedOnCell = function(time, date){

        return `<div class="text-center align-middle d-inline-block px-2" style="line-height: normal;">${time}<br>${date}</div>`;
    }

    var getImageAsBase64 = function (imageUrl) {
        return new Promise(function(resolve, reject) {
            let img = new Image();
            img.crossOrigin = 'Anonymous';

            img.onload = function () {
                let canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;

                let ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);

                try {
                    let dataURL = canvas.toDataURL('image/png');
                    resolve(dataURL);
                } catch (err) {
                    reject('Error converting image to Base64: ' + err);
                }
            };

            img.onerror = function () {
                reject('Image failed to load.');
            };

            img.src = imageUrl;

            if (img.complete || img.complete === undefined) {
                img.src = imageUrl;
            }
        });
    }

    var truncate = function(text, maxLen){
      return text.length > maxLen ? text.substring(0, maxLen) + '...' : text;
    }

    var maskPhoneNumber = function(phoneNumber) {
        if (!phoneNumber || phoneNumber.length < 3) {
            return phoneNumber;
        }
        
        var length = phoneNumber.length;
        var lastThree = phoneNumber.substring(length - 3);
        var maskedPart = 'X'.repeat(length - 3);
        
        return maskedPart + lastThree;
    };

    // Password validation functions
    var checkPasswordStrength = function(password) {
        var strength = 0;
        var feedback = [];
        
        if (password.length >= 8) {
            strength += 1;
        } else {
            feedback.push('At least 8 characters');
        }
        
        if (/[a-z]/.test(password)) {
            strength += 1;
        } else {
            feedback.push('Lowercase letter');
        }
        
        if (/[A-Z]/.test(password)) {
            strength += 1;
        } else {
            feedback.push('Uppercase letter');
        }
        
        if (/[0-9]/.test(password)) {
            strength += 1;
        } else {
            feedback.push('Number');
        }
        
        if (/[^A-Za-z0-9]/.test(password)) {
            strength += 1;
        } else {
            feedback.push('Special character');
        }
        
        return { score: strength, feedback: feedback };
    }

    var displayPasswordStrength = function(strength, targetId) {
        var strengthDiv = $(targetId);
        var strengthText = '';
        var strengthClass = '';
        
        switch(strength.score) {
            case 0:
            case 1:
                strengthText = 'Very Weak';
                strengthClass = 'text-danger';
                break;
            case 2:
                strengthText = 'Weak';
                strengthClass = 'text-danger';
                break;
            case 3:
                strengthText = 'Fair';
                strengthClass = 'text-warning';
                break;
            case 4:
                strengthText = 'Good';
                strengthClass = 'text-info';
                break;
            case 5:
                strengthText = 'Strong';
                strengthClass = 'text-success';
                break;
        }
        
        if (strength.feedback.length > 0) {
            strengthText += ' - Missing: ' + strength.feedback.join(', ');
        }
        
        strengthDiv.html('<small class="' + strengthClass + '">' + strengthText + '</small>');
    }

    var checkPasswordMatch = function(password, confirmPassword, targetId) {
        var matchDiv = $(targetId);
        
        if (confirmPassword === '') {
            matchDiv.html('');
            return;
        }
        
        if (password === confirmPassword) {
            matchDiv.html('<small class="text-success"><i class="fa fa-check"></i> Passwords match</small>');
        } else {
            matchDiv.html('<small class="text-danger"><i class="fa fa-times"></i> Passwords do not match</small>');
        }
    }

    var validatePasswordStrength = function(password) {
        var strength = checkPasswordStrength(password);
        return strength.score >= 4; // Require at least "Good" strength
    }

    var hasPermission = function(routeNameOrShortId){ 
        return userPermissions.some(p => 
            p.route_name === routeNameOrShortId || p.short_id === routeNameOrShortId
        );
    }
    function isFeatureEnabled(feature) {
        return Array.isArray(enabledSitefeatures) && enabledSitefeatures.includes(feature);
    }
    

    return{
        getFormData: getFormData,
        showValidationErrors: toastFormErrorMessages,
        isValidEmail: isValidEmail,
        isValidPhoneNumber: isValidPhoneNumber,
        postAjaxCall: postAjaxCall,
        getAjaxCall: getAjaxCall,
        deleteAjaxCall: deleteAjaxCall,
        putAjaxCall: putAjaxCall,
        previewImage: preivewImage,
        showBootstrapModal: showBootstrapModal,
        hideBootstrapModal: hideBootstrapModal,
        dataTableCreatedOnCell: dataTableCreatedOnCell,
        dataTableActionCell: dataTableActionCell,
        deleteAjaxCallPost: deleteAjaxCallPost,
        putAjaxCallPost: putAjaxCallPost,
        getImageAsBase64: getImageAsBase64,
        truncate: truncate,
        maskPhoneNumber: maskPhoneNumber,
        checkPasswordStrength: checkPasswordStrength,
        displayPasswordStrength: displayPasswordStrength,
        checkPasswordMatch: checkPasswordMatch,
        validatePasswordStrength: validatePasswordStrength,
        hasPermission: hasPermission,
        isFeatureEnabled: isFeatureEnabled
    }
})();
