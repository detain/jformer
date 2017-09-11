BFormComponentHidden = BFormComponent.extend({
    init: function(parentBFormSection, bFormComponentId, bFormComponentType, options) {
        this._super(parentBFormSection, bFormComponentId, bFormComponentType, options);
    },

    getValue: function() {
        if(this.disabledByDependency || this.parentBFormSection.disabledByDependency){
           return null;
        }
        return $('#'+this.id).val();
    },

    validate: function() {
        this._super();
    }
});
