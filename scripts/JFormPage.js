/**
 * bFormPage handles all functions on the page level, including page validation.
 *
 */
BFormPage = Class.extend({
    init: function(bFormer, pageId, options) {
        this.options = $.extend({
            dependencyOptions: null,
            onScrollTo: {
                onBefore: null,
                onAfter: null,
                notificationHtml: null
            }
        }, options || {});

        // Setup the onScrollTo functions
        if(this.options.onScrollTo.onBefore !== null) {
            var onBeforeFunction = $.trim(this.options.onScrollTo.onBefore);
            this.options.onScrollTo.onBefore = function() {eval(onBeforeFunction);};
        }
        if(this.options.onScrollTo.onAfter !== null) {
            var onAfterFunction = $.trim(this.options.onScrollTo.onAfter);
            this.options.onScrollTo.onAfter = function() {eval(onAfterFunction);};
        }

        // Class variables
        this.bFormer = bFormer;
        this.id = pageId;
        this.page = $('#'+pageId);
        this.bFormSections = {};
        this.formData = {};
        this.active = false;
        this.validationPassed = null;
        this.disabledByDependency = false;
    },

    addSection: function(section) {
        this.bFormSections[section.id] = section;
        return this;
    },

    getData: function() {
        //console.log('getting data for page');
        var self = this;

        // Handle disabled pages
        if(this.disabledByDependency) {
            this.formData = null;
        }
        else {
            this.formData = {};
            $.each(this.bFormSections, function(bFormSectionKey, bFormSection) {
                self.formData[bFormSectionKey] = bFormSection.getData();
            });
        }

        return this.formData;
    },

    setData: function(data) {
        var self = this;
        $.each(data, function(key, values) {
            if(self.bFormSections[key] != undefined){
                self.bFormSections[key].setData(values);
            } else {
                data[key] = undefined;
            }
        });
        this.formData = data;
        return this.formData;
    },

    validate: function(silent) {
        //console.log('validating', this.id);
        // Handle dependencies
        if(this.disabledByDependency) {
            return null;
        }

        var self = this;
        var each = $.each;
        
        self.validationPassed = true;
        each(this.bFormSections, function(sectionKey, section) {
           each(section.instanceArray, function(instanceIndex, sectionInstance){
                each(sectionInstance.bFormComponents, function(componentKey, component) {
                    if(component.type == 'BFormComponentLikert'){
                        return;
                    }
                    each(component.instanceArray, function(instanceIndex, instance) {
                        instance.validate();
                        if(instance.validationPassed == false) {
                            self.validationPassed = false;
                        }
                    });
                });
            });
        });

        if(self.validationPassed) {
            $('#navigatePage'+(self.bFormer.currentBFormPageIdArrayIndex + 1)).removeClass('bFormPageNavigatorLinkWarning');
        }
        else if(!silent) {
            if(this.id === this.bFormer.currentBFormPage.id){
                this.focusOnFirstFailedComponent();
            }
        }

        return self.validationPassed;
    },

    clearValidation: function() {
        $.each(this.bFormSections, function(sectionKey, section) {
            section.clearValidation();
        });
    },

    focusOnFirstFailedComponent: function() {
        var each = $.each,
        validationPassed = true;
        each(this.bFormSections, function(sectionLabel, section){
            each(section.instanceArray, function(sectionInstanceIndex, sectionInstance){
                each(sectionInstance.bFormComponents, function(componentLabel, component){
                    each(component.instanceArray, function(instanceLabel, instance){
                        if(!instance.validationPassed || instance.errorMessageArray.length > 0){
                            var offset = instance.component.offset().top - 30;
                            var top = $(window).scrollTop();
                            if(top < offset && top + $(window).height() > instance.component.position().top) {
                                instance.component.find(':input:first').focus();
                                //instance.highlight();
                            }
                            else {
                                $.scrollTo(offset + 'px', 500, {
                                    onAfter: function() {
                                        instance.component.find(':input:first').focus();
                                        //instance.highlight();
                                    }
                                });
                            }
                            validationPassed = false;
                        }
                        return validationPassed;
                    });
                    return validationPassed;
                });
                return validationPassed;
            });
            return validationPassed;
        });
    },

    scrollTo: function(options) {
        this.bFormer.scrollToPage(this.id, options);
        return this;
    },

    show: function(){
        if(this.page.hasClass('bFormPageInactive')){
            this.page.removeClass('bFormPageInactive');
        }
    },

    hide:function() {
        if(!this.active){
            this.page.addClass('bFormPageInactive');
        }
    },

    disableByDependency: function(disable) {
        // If the condition is different then the current condition
        if(this.disabledByDependency !== disable) {
            var pageIndex = $.inArray(this.id, this.bFormer.bFormPageIdArray);

            // Disable the page
            if(disable === true) {
                // Hide the page
                this.page.hide();

                // Update the page navigator appropriately
                if(this.bFormer.options.pageNavigator !== false) {
                    // Hide the page link
                    if(this.options.dependencyOptions.display == 'hide') {
                        $('#navigatePage'+(pageIndex+1)).hide();

                        // Renumber appropriately
                        this.bFormer.renumberPageNavigator();
                    }
                    // Lock the page link
                    else {
                        $('#navigatePage'+(pageIndex+1)).addClass('bFormPageNavigatorLinkDependencyLocked').find('span').html('&nbsp;');
                    }
                }
            }
            // Show the page
            else {
                this.checkChildrenDependencies();
                 this.page.show();

                // Update the page navigator appropriately
                if(this.bFormer.options.pageNavigator !== false) {
                    // Show the page link
                    if(this.options.dependencyOptions.display == 'hide') {
                        $('#navigatePage'+(pageIndex+1)).show();
                    }
                    // Unlock the page link
                    else {
                        $('#navigatePage'+(pageIndex+1)).removeClass('bFormPageNavigatorLinkDependencyLocked');
                    }

                    // Renumber the existing links
                    this.bFormer.renumberPageNavigator();
                 }

             }

            this.disabledByDependency = disable;
            this.bFormer.setupControl();
        }
    },

    checkDependencies: function() {
        var self = this;
        if(this.options.dependencyOptions !== null) {
            // Run the dependency function
            //console.log(self.options.dependencyOptions.jsFunction);
            //console.log(eval(self.options.dependencyOptions.jsFunction));
            var disable = !(eval(self.options.dependencyOptions.jsFunction));
            this.disableByDependency(disable);
        }
    },

    checkChildrenDependencies: function() {
        $.each(this.bFormSections, function(bFormSectionKey, bFormSection) {
            bFormSection.checkDependencies();
        });
    }
});