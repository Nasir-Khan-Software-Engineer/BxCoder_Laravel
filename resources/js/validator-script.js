BxCoder.Validator = {
    
    types:{},               // all the validation rules will be added under this object
    message: [],
    config: {},             // key value pair. Contain which validation will apply for which property 

    /**
     * 
     * @param {object} data key value pair. Key is the property(input field name) and value is the validation rule (property of types object)
     * @returns {boolean}  return that the message array is empty
     */
    validate: function(data){
        var i, msg, type, checker, result_ok;
        this.message = [];

        for(i in data){
            if(data.hasOwnProperty(i)){
                type = this.config[i];
                checker = this.types[type];

                if(!type){
                    continue;
                }
                
                if(!checker){
                    throw{
                        name: "Validation Error",
                        message: "Validation handler is missing for type: " + type,
                    };
                }

                result_ok = checker.validate(data[i]);
                if(!result_ok){
                    // i is the property name formatted as camel case
                    // format the property name and append with error message

                    msg = this.formatPropertyName(i) + ": " + checker.instructions;
                    this.message.push(msg);
                }
            }
        }
        
        return this.hasErrors();
    },

    hasErrors: function(){
        return this.message.length !== 0;
    },

    formatPropertyName: function(property){
        var propertyName = [], str = '';
        property = property.replace(property.charAt(0), property.charAt(0).toUpperCase()); 
        for(var j = 0; j<property.length;j++){
            if(property.charAt(j) >= 'A' && property.charAt(j) <= 'Z'){
                if(str !== ''){
                    propertyName.push(str);
                } 
                str = '';
                str = property.charAt(j);
            }else{
                str += property.charAt(j);
            }
        }

        propertyName.push(str);

        return propertyName.join(' ');
    }
}

// validation rules
BxCoder.Validator.types.isNonEmpty = {
    validate : function(value){
        return value !== "";
    },
    instructions: "The value can not be empty"
};

BxCoder.Validator.types.isBDPhoneNumber = {
    validate: function(phoneNumber){
        var phoneRegex = /^\d{11}$/;
        return phoneRegex.test(phoneNumber);
    },

    instructions: "The BD phone number is not valid."
};

BxCoder.Validator.types.isEmail = {
    validate: function(email){
         var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    instructions: "The email address is not valid."
};

BxCoder.Validator.types.isAlphaNumericAndUnderscore = {
    validate: function(value){
        return /^[A-Za-z][A-Za-z0-9 ]{2,49}$/.test(value);
    },
    instructions: "value should be between 3 to 50 characters and can contain alphabet, number and underscore."
}

BxCoder.Validator.types.isAlphaNumericAndUnderscoreExpenseName = {
    validate: function(value){
        return /^[A-Za-z][A-Za-z0-9 ]{2,49}$/.test(value);
    },
    instructions: "value should be between 3 to 500 characters and can contain alphabet, number and underscore."
}

BxCoder.Validator.types.isLessThanNextDay = {
    validate: function(value){
        var inputDate = new Date(value),
        currentDate = new Date();

        currentDate.setDate(currentDate.getDate()+1);
        currentDate.setHours(0, 0, 0, 0);
        inputDate.setHours(0, 0, 0, 0);

        if(isNaN(inputDate)){
            return false;
        }

        return inputDate <= currentDate;
    },

    instructions: "Date is greater than today."
}

BxCoder.Validator.types.isMoney = {
    validate: function(value){
        var money = parseFloat(value);

        if(!isNaN(money) && money !== 0){
            return true;
        }

        return false;
    },

    instructions: "Value should be valid and not 0."
}
