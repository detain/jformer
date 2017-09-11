BFormComponentLikertStatement = BFormComponent.extend({
    init: function(parentBFormSection, bFormComponentId, bFormComponentType, options) {
        this._super(parentBFormSection, bFormComponentId, bFormComponentType, options);
    },

    initialize: function(){
        var self = this
        this.changed = false;
        this.component = $('input[name='+this.id+']:first').closest('tr');
        this.tipTarget = this.component;
        this.tipDiv = this.component.find('div.bFormComponentLikertStatementTip');

        // Allow the user to click on the box
        //this.component.find('td').click(function(event){
            //event.preventDefault();
            //$(event.target).find('input').attr("checked", "checked").trigger('click');
        //});

        this.validationFunctions = {
            'required': function(options) {
                var errorMessageArray = ['Required.'];
                return options.value.length > 0 ? 'success' : errorMessageArray;
            }
        }

    },

    setValue: function(data) {
        var self = this;
            self.component.find('input').val([data]);
            this.validate(true);
        /*
        $.each(data, function(key, value){
            if(data[key] != self.options.emptyValue[key]){
                self.component.find('input[id*='+key+']').removeClass('defaultValue').val(value).blur().trigger('component:changed');
            }
        });*/
    },

    validate: function(){
        this._super();
    },

    getValue: function() {
        if(this.disabledByDependency || this.parentBFormSection.disabledByDependency){
            return null;
        }      
        var value = this.component.find('input:checked');
        if(value.length > 0){
            value = value.val()
        } else {
            value = '';
        }
        return value;
    }
});