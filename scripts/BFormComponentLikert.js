BFormComponentLikert = BFormComponent.extend({
    init: function(parentBFormSection, bFormComponentId, bFormComponentType, options) {
        this._super(parentBFormSection, bFormComponentId, bFormComponentType, options);
    },

    initialize: function(){
        var self = this;
        this.changed = false;
        this.tipTarget = this.component;
        this.statementComponentArray = {};

        $.each(this.options.statementArray, function(statementName, statementOptions){
            if(!bFormerUtility.empty(self.options.validationOptions)){
                statementOptions.validationOptions = self.options.validationOptions;
            }
            var newLikertStatment = new BFormComponentLikertStatement(self.parentBFormSection, statementName, 'BFormComponentLikertStatement', statementOptions);
            newLikertStatment.id = self.id+'-'+newLikertStatment.id
            self.parentBFormSection.addComponent(newLikertStatment);
            self.statementComponentArray[statementName] = newLikertStatment;
        });
    },

    clearValidation: function (){
      $.each(this.statementComponentArray, function(index, statement){
          statement.clearValidation();
      });
    },

    setValue: function(data) {
        var self = this;
        return
        /*
        $.each(data, function(key, value){
            if(data[key] != self.options.emptyValue[key]){
                self.component.find('input[id*='+key+']').removeClass('defaultValue').val(value).blur().trigger('component:changed');
            }
        });*/
    },

    catchComponentChangedEventListener: function() { return null },
    addHighlightListeners: function() { return null },
    defineComponentChangedEventListener: function() { return null },
    addTipListeners: function() { return null },

    getValue: function() {
        var value = {};
        $.each(this.statementComponentArray, function(key, component){
            value[key] = component.getValue();
        })


        return value;
    },

    handleErrors: function() {
        var self = this;
        return true;
    },

    handleServerValidationResponse: function(errorMessageArray) {
        var self = this;
        if(errorMessageArray.length > 0) {
            $.each(this.instanceArray, function(key, instance){    
                $.each(errorMessageArray, function(index, passedErrorArray){
                    $.each(passedErrorArray, function(statementKey, statementError){
                        var likertStatement = self.parentBFormSection.bFormComponents[instance.id+'-'+statementKey];
                        if(likertStatement != undefined){
                            likertStatement.errorMessageArray = [statementError];
                            likertStatement.validationPassed = false;
                            likertStatement.handleErrors();
                        }
                    }) ;
                });
            });
        }
    },

    validate: function(){
        var self = this;
        return true;
    }
        
});
