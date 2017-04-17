BFormComponentDropDown = BFormComponent.extend({
    init: function(parentBFormSection, bFormComponentId, bFormComponentType, options) {
        this._super(parentBFormSection, bFormComponentId, bFormComponentType, options);
    },

    initialize: function(){
       this.tipTarget = this.component.find('select:last');
    },

    getValue: function() {
        if(this.disabledByDependency || this.parentBFormSection.disabledByDependency){
           return null;
        }
            var dropDownValue = $('#'+this.id).val();
            return dropDownValue;
    },

    setValue: function(value){
        $('#'+this.id).val(value).trigger('bFormComponent:changed');
      //this.component.find('option[value=\''+value+'\']').attr('selected', 'selected').trigger('bFormComponent:changed');
      this.validate(true);
    }

});