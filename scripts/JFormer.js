/**
 * bFormer is the steward of the form. Holds base functions which are not specific to any page, section, or component.
 * bFormer is initialized on top of the existing HTML and handles validation, tool tip management, dependencies, instances, triggers, pages, and form submission.
 *
 * @author Kirk Ouimet <kirk@kirkouimet.com>
 * @author Seth Jensen <seth@sethjdesign.com>
 * @version .5
 */
BFormer = Class.extend({
	init: function(formId, options) {
		var self = this;

		// Keep track of when the form starts initializing (turns off at buttom of init)
		this.initializing = true;

		// Update the options object
		this.options = $.extend(true, {
			animationOptions: {
				pageScroll: {
					duration: 375,
					adjustHeightDuration: 375
				},
				instance: {
					appearDuration: 0,
					appearEffect: 'fade',
					removeDuration: 0,
					removeEffect: 'fade',
					adjustHeightDuration: 0
				},
				dependency: {
					appearDuration: 250,
					appearEffect: 'fade',
					hideDuration: 100,
					hideEffect: 'fade',
					adjustHeightDuration: 100
				},
				alert: {
					appearDuration: 250,
					appearEffect: 'fade',
					hideDuration: 100,
					hideEffect: 'fade'
				},
				modal: {
					appearDuration: 0,
					hideDuration: 0
				}
			},
			setupPageScroller: true,
			validationTips: true,
			pageNavigator: false,
			splashPage: false,
			alertsEnabled: true,
			clientSideValidation: true,
			debugMode: false,
			submitButtonText: 'Submit',
			submitProcessingButtonText: 'Processing...',
			onSubmitStart: function() {
				return true;
			},
			onSubmitFinish: function() {
				return true;
			}
		}, options.options || {});

		// Class variables
		this.id = formId;
		this.form = $(['form#',this.id].join(''));
		this.formData = {};
		this.bFormPageWrapper = this.form.find('div.bFormPageWrapper');
		this.bFormPageScroller = this.form.find('div.bFormPageScroller');
		this.bFormPageNavigator = null;
		this.bFormPages = {};
		this.currentBFormPage = null;
		this.maxBFormPageIdArrayIndexReached = null;
		this.bFormPageIdArray = [];
		this.currentBFormPageIdArrayIndex = null;
		this.blurredTips = [];
		this.lastEnabledPage = false;

		// Controls
		this.control = this.form.find('ul.bFormerControl');
		this.controlNextLi = this.form.find('ul.bFormerControl li.nextLi');
		this.controlNextButton = this.controlNextLi.find('button.nextButton');
		this.controlPreviousLi = this.form.find('ul.bFormerControl li.previousLi');
		this.controlPreviousButton = this.controlPreviousLi.find('button.previousButton');

		// Initialize all of the pages
		this.initPages(options.bFormPages);

		// Add a splash page if enabled
		if(this.options.splashPage !== false) {
			if(this.options.splashPage == false) {
				this.options.splashPage = {};
			}
			this.addSplashPage();
		}
		// Set the current page
		else {
			this.currentBFormPageIdArrayIndex = 0;
			this.maxBFormPageIdArrayIndexReached = 0;
			this.currentBFormPage = this.bFormPages[this.bFormPageIdArray[0]];
			this.currentBFormPage.active = true;
			// Add the page navigator
			if(this.options.pageNavigator !== false) {
				this.addPageNavigator();
			}
		}

		// Setup the page scroller - mainly CSS changes to width and height
		if(this.options.setupPageScroller) {
			this.setupPageScroller();
		}
		
		// Hide all inactive pages
		this.hideInactivePages();

		// Setup the control buttons
		this.setupControl();

		// Add a submit button listener
		this.addSubmitListener();

		// Add enter key listener
		this.addEnterKeyListener();

		// The blur tip listener
		this.addBlurTipListener();

		// Check dependencies
		this.checkDependencies(true);

		// Record when the form is finished initializing
		this.initializing = false;

		// Instantly adjust the height of the form after the window is loaded
		$(window).load(function() {
			//console.log('Adjusting height!', self.id);
			self.adjustHeight({
				adjustHeightDuration:0
			});
		});
	},

	initPages: function(bFormPages) {
		var self = this
		var each = $.each;
		var dependencies = {};
		
		each(bFormPages, function(bFormPageKey, bFormPageValue) {
			var bFormPage = new BFormPage(self, bFormPageKey, bFormPageValue.options);
			bFormPage.show();
			
			// Handle page level dependencies
			if(bFormPage.options.dependencyOptions !== null) {
				$.each(bFormPage.options.dependencyOptions.dependentOn, function(index, componentId) {
					if(dependencies[componentId] === undefined) {
						dependencies[componentId] = {
							pages:[],
							sections:[],
							components:[]
						};
					}
					dependencies[componentId].pages.push({
						bFormPageId:bFormPageKey
					});
				});
			}

			each(bFormPageValue.bFormSections, function(bFormSectionKey, bFormSectionValue) {
				var bFormSection = new BFormSection(bFormPage, bFormSectionKey, bFormSectionValue.options);

				// Handle section level dependencies
				if(bFormSection.options.dependencyOptions !== null) {
					$.each(bFormSection.options.dependencyOptions.dependentOn, function(index, componentId) {
						if(dependencies[componentId] === undefined) {
							dependencies[componentId] = {
								pages:[],
								sections:[],
								components:[]
							};
						}
						dependencies[componentId].sections.push({
							bFormPageId:bFormPageKey,
							bFormSectionId:bFormSectionKey
						});
					});
				}

				each(bFormSectionValue.bFormComponents, function(bFormComponentKey, bFormComponentValue) {
					var bFormComponent = new window[bFormComponentValue.type](bFormSection, bFormComponentKey, bFormComponentValue.type, bFormComponentValue.options);
					bFormSection.addComponent(bFormComponent);

					// Check if there are pregenerated instances and add them
					bFormComponent.addInitialInstances();

					bFormSection.addComponent(bFormComponent);

					// Handle component level dependencies
					if(bFormComponent.options.dependencyOptions !== null) {
						$.each(bFormComponent.options.dependencyOptions.dependentOn, function(index, componentId) {
							if(dependencies[componentId] === undefined) {
								dependencies[componentId] = {
									pages:[],
									sections:[],
									components:[]
								};
							}
							dependencies[componentId].components.push({
								bFormPageId:bFormPageKey,
								bFormSectionId:bFormSectionKey,
								bFormComponentId:bFormComponentKey
							});
						});
					}
				});

				// Check if there are pregenerated instances and add them
				bFormSection.addInitialSectionInstances();

				// Add the section to the page
				bFormPage.addSection(bFormSection);
			});
			self.addBFormPage(bFormPage);
		});

		// Add listeners for all of the components that are being dependent on
		// We group the component event listeners to prevent them from constantly being called
		$.each(dependencies, function(componentId, dependentTypes) {
			
			$('#'+componentId+':text, textarea#'+componentId).bind('keyup', function(event) {
				$.each(dependentTypes.pages, function(index, object) {
					self.bFormPages[object.bFormPageId].checkDependencies();
				});
				$.each(dependentTypes.sections, function(index, object) {
					self.bFormPages[object.bFormPageId].bFormSections[object.bFormSectionId].checkDependencies();
				});
				$.each(dependentTypes.components, function(index, object) {
					self.bFormPages[object.bFormPageId].bFormSections[object.bFormSectionId].bFormComponents[object.bFormComponentId].checkDependencies();
				});
			});

			$('#'+componentId+'-wrapper').bind('bFormComponent:changed', function(event) {
				//console.log('running depend check');

				$.each(dependentTypes.pages, function(index, object) {
					self.bFormPages[object.bFormPageId].checkDependencies();
				});
				$.each(dependentTypes.sections, function(index, object) {
					self.bFormPages[object.bFormPageId].bFormSections[object.bFormSectionId].checkDependencies();
				});
				$.each(dependentTypes.components, function(index, object) {
					//console.log('running a check', componentId, 'for', object.bFormComponentId);
					self.bFormPages[object.bFormPageId].bFormSections[object.bFormSectionId].bFormComponents[object.bFormComponentId].checkDependencies();
				});
			});

			// Handle instances (this is super kludgy)
			var component = self.select(componentId);
			//console.log(component);
			if(component !== null && component.options.instanceOptions !== null){
				component.options.dependencies = dependentTypes;
			}
		});
	},

	select: function(bFormComponentId) {
		var componentFound = false,
		component = null;
		$.each(this.bFormPages, function(bFormPageKey, bFormPage){
			$.each(bFormPage.bFormSections, function(sectionKey, sectionObject){
				$.each(sectionObject.bFormComponents, function(componentKey, componentObject){
					if (componentObject.id == bFormComponentId){
						component = componentObject;
						componentFound = true;
					}
					return !componentFound;
				});
				return !componentFound;
			});
			return !componentFound;
		});
		return component;
	},

	checkDependencies: function(onInit) {
		$.each(this.bFormPages, function(bFormPageKey, bFormPage) {
			bFormPage.checkDependencies();

			$.each(bFormPage.bFormSections, function(bFormSectionKey, bFormSection) {
				bFormSection.checkDependencies();

				$.each(bFormSection.bFormComponents, function(bFormComponentKey, bFormComponent) {
					bFormComponent.checkDependencies();
				});
			});
		});
	},

	addSplashPage: function() {
		var self = this;

		// Setup the formPage for the splash page
		// Setup default page options for the splash page
		if(!this.options.splashPage.options) {
			this.options.splashPage.options = {};
		}

		// Setup the bFormPage for the splash page
		this.options.splashPage.bFormPage = new BFormPage(this, this.form.find('div.bFormerSplashPage').attr('id'));
		this.options.splashPage.bFormPage.addSection(new BFormSection(this.options.splashPage.bFormPage, this.form.find('div.bFormerSplashPage').attr('id') + '-section'));
		this.options.splashPage.bFormPage.page.width(this.form.width());
		this.options.splashPage.bFormPage.active = true;

		// Set the splash page as the current page
		this.currentBFormPage = this.options.splashPage.bFormPage;

		// Set the height of the page wrapper to the height of the splash page
		this.bFormPageWrapper.height(this.options.splashPage.bFormPage.page.outerHeight());

		// If they have a custom button
		if(this.options.splashPage.customButtonId) {
			this.options.splashPage.controlSplashLi = this.form.find('#'+this.options.splashPage.customButtonId);
			this.options.splashPage.controlSplashButton = this.form.find('#'+this.options.splashPage.customButtonId);
		}
		// Use the native control buttons
		else {
			this.options.splashPage.controlSplashLi = this.form.find('li.splashLi');
			this.options.splashPage.controlSplashButton = this.form.find('button.splashButton');
		}

		// Hide the other native controls
		this.setupControl();

		this.options.splashPage.controlSplashButton.bind('click', function(event) {
			event.preventDefault();
			self.beginFormFromSplashPage(false);
		});
	},

	beginFormFromSplashPage: function(initSaveState, loadForm) {
		var self = this;

		// Add the page navigator
		if(this.options.pageNavigator !== false && this.bFormPageNavigator == null) {
			this.addPageNavigator();
			this.bFormPageNavigator.show();
		}
		else if(this.options.pageNavigator !== false) {
			this.bFormPageNavigator.show();
		}

		// Find all of the pages
		var pages = this.form.find('.bFormPage');

		// Set the width of each page
		pages.css('width', this.form.find('.bFormWrapperContainer').width());
		
		// Mark the splash page as inactive
		self.options.splashPage.bFormPage.active = false;

		if(!loadForm){
			// Set the current page index
			self.currentBFormPageIdArrayIndex = 0;

			// Scroll to the new page, hide the old page when it is finished
			self.bFormPages[self.bFormPageIdArray[0]].scrollTo({
				onAfter: function() {
					self.options.splashPage.bFormPage.hide();
					self.renumberPageNavigator();
				}
			});
	}
},

addPageNavigator: function(){
	var self = this;

	this.bFormPageNavigator = this.form.find('.bFormPageNavigator');

	this.bFormPageNavigator.find('.bFormPageNavigatorLink:first').click(function(event) {
		// Don't scroll to the page if you already on it
		if(self.currentBFormPageIdArrayIndex != 0) {
			self.currentBFormPageIdArrayIndex = 0;

			self.scrollToPage(self.bFormPageIdArray[0], {
				//onAfter: function() {
				//}
				});
		}
	});

	// Update the style is right aligned
	if(this.options.pageNavigator.position == 'right'){
		this.form.find('.bFormWrapperContainer').width(this.form.width() - this.bFormPageNavigator.width() - 30);
	}
},

updatePageNavigator: function() {
	var self = this, pageCount, pageIndex;
	for(var i = 1; i <= this.maxBFormPageIdArrayIndexReached + 1; i++) {
		pageCount = i;
		var bFormPageNavigatorLink = $('#navigatePage'+pageCount);

		// Remove the active class from the page you aren't on
		if(this.currentBFormPageIdArrayIndex != pageCount - 1) {
			bFormPageNavigatorLink.removeClass('bFormPageNavigatorLinkActive');
		}
		// Add the active class to the page you are on
		else {
			bFormPageNavigatorLink.addClass('bFormPageNavigatorLinkActive');
		}

		// If the page is currently locked
		if(bFormPageNavigatorLink.hasClass('bFormPageNavigatorLinkLocked')){
			// Remove the lock
			bFormPageNavigatorLink.removeClass('bFormPageNavigatorLinkLocked').addClass('bFormPageNavigatorLinkUnlocked');

			bFormPageNavigatorLink.click(function(event) {
				var target = $(event.target);
				if(!target.is('li')){
					target = target.closest('li');
				}

				pageIndex = target.attr('id').match(/[0-9]+$/)
				pageIndex = parseInt(pageIndex) - 1;

				// Perform a silent validation on the page you are leaving
				self.getActivePage().validate(true);

				// Don't scroll to the page if you already on it
				if(self.currentBFormPageIdArrayIndex != pageIndex) {
					self.scrollToPage(self.bFormPageIdArray[pageIndex]);
				}

				self.currentBFormPageIdArrayIndex = pageIndex;
					
			});
		}
	}
},

renumberPageNavigator: function() {
	$('.bFormPageNavigatorLink:visible').each(function(index, element) {
		// Renumber page link icons
		if($(element).find('span').length > 0) {
			$(element).find('span').html(index+1);
		}
		// Relabel pages that have no title or icons
		else {
			$(element).html('Page '+(index+1));
		}
	});
},
	
addBFormPage: function(bFormPage) {
	this.bFormPageIdArray.push(bFormPage.id);
	this.bFormPages[bFormPage.id] = bFormPage;
},

removeBFormPage: function(bFormPageId) {
	var self = this;

	// Remove the HTML
	$('#'+bFormPageId).remove();

	this.bFormPageIdArray = $.grep(self.bFormPageIdArray, function(value) {
		return value != bFormPageId;
	});
	delete this.bFormPages[bFormPageId];
},

addEnterKeyListener: function() {
	var self = this;

	// Prevent the default submission on key down
	this.form.bind('keydown', {
		context:this
	}, function(event) {
		if(event.keyCode === 13 || event.charCode === 13) {
			if($(event.target).is('textarea')){
				return;
			}
			event.preventDefault();
		}
	});

	this.form.bind('keyup', {
		context:this
	}, function(event) {
		// Get the current page, check to see if you are on the splash page
		var currentPage = self.getActivePage().page;

		// Listen for the enter key keycode
		if(event.keyCode === 13 || event.charCode === 13) {
			var target = $(event.target);
			// Do nothing if you are on a text area
			if(target.is('textarea')){
				return;
			}

			// If you are on a button, press it
			if(target.is('button')){
				event.preventDefault();
				target.trigger('click').blur();
			}
			// If you are on a field where pressing enter submits
			else if(target.is('.bFormComponentEnterSubmits')){
				event.preventDefault();
				target.blur();
				self.controlNextButton.trigger('click');
			}
			// If you are on an input that is a check box or radio button, select it
			else if(target.is('input:checkbox')) {
				event.preventDefault();
				target.trigger('click');
			}
			// If you are the last input and you are a password input, submit the form
			else if(target.is('input:password')) {
				event.preventDefault();
				target.blur();

				// Handle if you are on the splash page
				if(self.options.splashPage !== null && self.currentBFormPage.id == self.options.splashPage.bFormPage.id) {
					self.options.splashPage.controlSplashButton.trigger('click');
				}
				else {
					self.controlNextButton.trigger('click');
				}
			}

		}
	});
},

addSubmitListener: function(){
	var self = this;
	this.form.bind('submit', {
		context: this
	}, function(event) {
		event.preventDefault();
		self.submitEvent(event);
	});
},

getData: function() {
	var self = this;
	this.formData = {};
	$.each(this.bFormPages, function(bFormKey, bFormPage) {
		self.formData[bFormKey] = bFormPage.getData();
	});
	return this.formData;
},

setData: function(data) {
	var self = this;
	this.formData = data;
	$.each(data, function(key, page) {
		if(self.bFormPages[key] != undefined){
			self.bFormPages[key].setData(page);
		} else {
			return;
		}
	});
	return this.formData;
},

setupPageScroller: function(options) {
	var self = this;

	// Set some default values for the options
	var defaultOptions = {
		adjustHeightDuration: 0,
		bFormWrapperContainerWidth : self.form.find('.bFormWrapperContainer').width(),
		bFormPageWrapperWidth : self.bFormPageWrapper.width(),
		activePageOuterHeight : self.getActivePage().page.outerHeight()
	};
	options = $.extend(defaultOptions, options);
		
	// Find all of the pages
	var pages = this.form.find('.bFormPage');

	// Count the total number of pages
	var pageCount = pages.length;

	// Don't set width's if they are 0 (the form is hidden)
	if(options.formWrapperContainerWidth != 0) {
		// Set the width of each page
		pages.css('width', options.formWrapperContainerWidth)
	}
	pages.show();

	// Don't set width's if they are 0 (the form is hidden)
	if(options.formWrapperContainerWidth != 0) {
		// Set the width of the scroller
		self.bFormPageScroller.css('width', options.bFormPageWrapperWidth * (pageCount));
		self.bFormPageWrapper.parent().css('width', options.bFormPageWrapperWidth);
	}

	// Don't set height if it is 0 (the form is hidden)
	if(options.activePageOuterHeight != 0) {
		// Set the height of the wrapper
		self.bFormPageWrapper.height(options.activePageOuterHeight);
	}

	// Scroll to the current page (prevent weird Firefox bug where the page does not display on soft refresh
	if(options.scrollToPage) {
		self.scrollToPage(self.currentBFormPage.id, options);
	}
},

setupControl: function() {
	//console.log('setting up control');

	var self = this;
	// console.log(this.currentBFormPageIdArrayIndex);
	// Setup event listener for next button
	this.controlNextButton.unbind().click(function(event) {
		event.preventDefault();
		event['context'] = self;
		self.submitEvent(event);
	}).removeAttr('disabled');

	//check to see if this is the last enabled page.
	this.lastEnabledPage = false;
	for(i = this.bFormPageIdArray.length - 1 ; i > this.currentBFormPageIdArrayIndex; i--){
		if(!this.bFormPages[this.bFormPageIdArray[i]].disabledByDependency){
			this.lastEnabledPage = false;
			break;
		}
		this.lastEnabledPage = true;
	}

	// Setup event listener for previous button
	this.controlPreviousButton.unbind().click(function(event) {
		event.preventDefault();

		// Be able to return to the splash page
		if(self.options.splashPage !== false && self.currentBFormPageIdArrayIndex === 0) {
			self.currentBFormPageIdArrayIndex = null;
			if(self.bFormPageNavigator){
				self.bFormPageNavigator.hide();
			}
			self.options.splashPage.bFormPage.scrollTo();
		}
		// Scroll to the previous page
		else {
			if(self.bFormPages[self.bFormPageIdArray[self.currentBFormPageIdArrayIndex - 1]].disabledByDependency){
				for(var i = 1; i <= self.currentBFormPageIdArrayIndex; i++){
					var nextIndex  = self.currentBFormPageIdArrayIndex - i;
					if(nextIndex == 0 && self.options.splashPage !== false && self.bFormPages[self.bFormPageIdArray[nextIndex]].disabledByDependency ){
						if(self.bFormPageNavigator){
							self.bFormPageNavigator.hide();
						}
						self.options.splashPage.bFormPage.scrollTo();
						break;
					}
					else if(!self.bFormPages[self.bFormPageIdArray[nextIndex]].disabledByDependency){
						self.currentBFormPageIdArrayIndex = nextIndex;
						break;
					}
				}
			} else {
				self.currentBFormPageIdArrayIndex = self.currentBFormPageIdArrayIndex - 1;
			}
			self.scrollToPage(self.bFormPageIdArray[self.currentBFormPageIdArrayIndex]);
		}
	});
	   
	// First page with more pages after, or splash page
	if(this.currentBFormPageIdArrayIndex === 0 && this.currentBFormPageIdArrayIndex != this.bFormPageIdArray.length - 1 && this.lastEnabledPage === false) {
		this.controlNextButton.html('Next');
		this.controlNextLi.show();
		this.controlPreviousLi.hide();
		this.controlPreviousButton.attr('disabled', 'disabled');
	}
	// Last page
	else if(self.currentBFormPageIdArrayIndex == this.bFormPageIdArray.length - 1 || this.lastEnabledPage === true) {
		this.controlNextButton.html(this.options.submitButtonText);
		this.controlNextLi.show();

		// First page is the last page
		if(self.currentBFormPageIdArrayIndex === 0 ) {
			// Hide the previous button
			this.controlPreviousLi.hide();
			this.controlPreviousButton.attr('disabled', '');
		}
		// There is a previous page
		else if(self.currentBFormPageIdArrayIndex > 0) {
			this.controlPreviousButton.removeAttr('disabled');
			this.controlPreviousLi.show();
		}
	}
	// Middle page with a previous and a next
	else {
		this.controlNextButton.html('Next');
		this.controlNextLi.show();
		this.controlPreviousButton.removeAttr('disabled');
		this.controlPreviousLi.show();
	}

	// Splash page
	if(this.options.splashPage !== false) {
		// If you are on the splash page
		if(this.options.splashPage.bFormPage.active) {
			this.options.splashPage.controlSplashLi.show();
			this.controlNextLi.hide();
			this.controlPreviousLi.hide();
			this.controlPreviousButton.attr('disabled', 'disabled');
		}
		// If you aren't on the splash page, don't show the splash button
		else {
			this.options.splashPage.controlSplashLi.hide();
		}

		// If you are on the first page
		if(this.currentBFormPageIdArrayIndex === 0  && this.options.saveState == false) {
			this.controlPreviousButton.removeAttr('disabled');
			this.controlPreviousLi.show();
		}
	}

	// Failure page
	if(this.control.find('.startOver').length == 1){
		// Hide the other buttons
		this.controlNextLi.hide();
		this.controlPreviousLi.hide();

		// Bind an event listener to the start over button
		this.control.find('.startOver').one('click', function(event){
			event.preventDefault();
			self.currentBFormPageIdArrayIndex = 0;
			self.scrollToPage(self.bFormPageIdArray[0], {
				onAfter: function(){
					// Remove the start over button
					$(event.target).parent().remove();
					self.removeBFormPage(self.id+'bFormPageFailure');
				}
			});
		});
	}
},
	
scrollToPage: function(bFormPageId, options) {
	//console.log('BFormer('+this.id+'):scrollToPage', bFormPageId, options);

	// Prevent scrolling to dependency disabled pages
	if(this.bFormPages[bFormPageId] && this.bFormPages[bFormPageId].disabledByDependency) {
		return false;
	}

	var self = this;

	// Disable buttons
	this.controlNextButton.attr('disabled', true);
	this.controlPreviousButton.attr('disabled', true);

	// Handle page specific onScrollTo onBefore custom function
	var formPage = null;
	if(self.options.splashPage !== false && bFormPageId == self.options.splashPage.formPage.id) {
		formPage = self.options.splashPage.formPage;
	}
	else {
		formPage = this.bFormPages[bFormPageId];
	}

	if(formPage && formPage.options.onScrollTo.onBefore !== null) {
		// put a notice up if defined
		if(this.bFormPages[bFormPageId].options.onScrollTo.notificationHtml !== undefined) {
			if(self.control.find('.bformerScrollToNotification').length != 0 ){
				self.control.find('.bformerScrollToNotification').html(this.bFormPages[bFormPageId].options.onScrollTo.notificationHtml);
			} else {
				self.control.append('<li class="bformerScrollToNotification">'+this.bFormPages[bFormPageId].options.onScrollTo.notificationHtml+'<li>');
			}
				
		}
		this.bFormPages[bFormPageId].options.onScrollTo.onBefore();
	}

	// Remember the active duration time of the page
	var oldBFormPage = this.getActivePage();

	// Show every page so you can see them as you scroll through
	$.each(this.bFormPages, function(bFormPageKey, bFormPage) {
		bFormPage.show();
		bFormPage.active = false;
	});

	// If on the splash page, set the current page to the splash page
	if(self.options.splashPage !== false && bFormPageId == self.options.splashPage.bFormPage.id) {
		self.currentBFormPage = self.options.splashPage.bFormPage;
		self.currentBFormPage.show();
	}
	// Set the current page to the new page
	else {
		this.currentBFormPage = this.bFormPages[bFormPageId];
	}

	// Mark the current page as active
	this.currentBFormPage.active = true;

	// Adjust the height of the page wrapper
	// If there is a custom adjust height duration
	if(options && options.adjustHeightDuration !== undefined) {
		self.adjustHeight({
			adjustHeightDuration: options.adjustHeightDuration
			});
	}
	else {
		self.adjustHeight();
	}

	// Run the next animation immediately
	this.bFormPageWrapper.dequeue();

	// Scroll the document the top of the form
	this.scrollToTop();
		
	// PageWrapper is like a viewport - this scrolls to the top of the new page, but the document needs to be scrolled too
	var initializing = this.initializing;
	this.bFormPageWrapper.scrollTo(
		self.currentBFormPage.page,
		self.options.animationOptions.pageScroll.duration,
		{
			onAfter: function() {
				// Don't hide any pages while scrolling
				if($(self.bFormPageWrapper).queue('fx').length <= 1 ) {
					self.hideInactivePages(self.getActivePage());
				}

				// Set the max page reach indexed
				if(self.maxBFormPageIdArrayIndexReached < self.currentBFormPageIdArrayIndex) {
					self.maxBFormPageIdArrayIndexReached = self.currentBFormPageIdArrayIndex;
				}

				// Update the page navigator
				self.updatePageNavigator();

				// Start the time for the new page
				self.currentBFormPage.startTime = (new Date().getTime()/1000);

				// Run any special functions
				if(options && options.onAfter) {
					options.onAfter();
				}

				// Run any specific page functions
				//console.log(self.currentBFormPage);
				if(self.currentBFormPage.options.onScrollTo.onAfter) {
					self.currentBFormPage.options.onScrollTo.onAfter();
				}

				// Setup the controls
				self.setupControl();

				// Enable the buttons again
				self.controlNextButton.removeAttr('disabled').blur();
				self.controlPreviousButton.removeAttr('disabled').blur();

				// Focus on the first failed component, if it is failed,
				if(self.currentBFormPage.validationPassed === false && !initializing){
					self.currentBFormPage.focusOnFirstFailedComponent();
				}

				// Handle page specific onScrollTo onAfter custom function
				if(self.bFormPages[bFormPageId] && self.bFormPages[bFormPageId].options.onScrollTo.onAfter !== null) {
					self.bFormPages[bFormPageId].options.onScrollTo.onAfter();
					if(self.bFormPages[bFormPageId].options.onScrollTo.notificationHtml !== null) {
						self.control.find('li.bFormerScrollToNotification').remove();
					}
				}
			}
		}
		);

	return this;
},

scrollToTop: function() {
	if(this.initializing) {
		return;
	}

	var self = this;
	// Only scroll if the top of the form is not visible
	if($(window).scrollTop() > this.form.offset().top) {
		$(document).scrollTo(self.form, self.options.animationOptions.pageScroll.duration, {
			offset: {
				top: -10
			}
		});
	}
},

getActivePage: function() {
	// if active page has not been set
	return this.currentBFormPage;
},

hideInactivePages: function(){
	$.each(this.bFormPages, function(bFormPageKey, bFormPage){
		bFormPage.hide();
	});
},

clearValidation: function() {
	$.each(this.bFormPages, function(bFormPageKey, bFormPage){
		bFormPage.clearValidation();
	});
},

submitEvent: function(event) {
	var self = this;
	//console.log('last enabled page', self.lastEnabledPage);
	// Stop the event no matter what
	event.stopPropagation();
	event.preventDefault();

	// Remove any failure notices
	self.control.find('.bFormerFailureNotice').remove();
	self.form.find('.bFormerFailure').remove();

	// Run a custom function at beginning of the form submission
	var onSubmitStartResult;
	if(typeof(self.options.onSubmitStart) != 'function') {
		onSubmitStartResult = eval(self.options.onSubmitStart);
	}
	else {
		onSubmitStartResult = self.options.onSubmitStart();
	}

	// Validate the current page if you are not the last page
	var clientSideValidationPassed = false;
	if(this.options.clientSideValidation) {
		if(self.currentBFormPageIdArrayIndex < self.bFormPageIdArray.length - 1 && !self.lastEnabledPage) {
			//console.log('Validating single page.');
			clientSideValidationPassed = self.getActivePage().validate();
		}
		else {
			//console.log('Validating whole form.');
			clientSideValidationPassed = self.validateAll();
		}
	}
	// Ignore client side validation
	else {
		this.clearValidation();
		clientSideValidationPassed = true;
	}

	// Run any custom functions at the end of the validation
	var onSubmitFinishResult = self.options.onSubmitFinish();

	// If the custom finish function returns false, do not submit the form
	if(onSubmitFinishResult) {
		// Last page, submit the form
		//console.log(clientSideValidationPassed && (self.currentBFormPageIdArrayIndex == self.bFormPageIdArray.length - 1) || (self.lastEnabledPage === true ));
		if(clientSideValidationPassed && (self.currentBFormPageIdArrayIndex == self.bFormPageIdArray.length - 1) || (self.lastEnabledPage === true )) {
			self.submitForm(event);
		}
		// Not last page, scroll to the next page
		else if(clientSideValidationPassed && self.currentBFormPageIdArrayIndex < self.bFormPageIdArray.length - 1) {
			// if the next page is disabled by dependency, loop through till you find a good page.
			if(self.bFormPages[self.bFormPageIdArray[self.currentBFormPageIdArrayIndex + 1]].disabledByDependency){
				for(var i = self.currentBFormPageIdArrayIndex + 1; i <= self.bFormPageIdArray.length - 1; i++){
					// page is enabled, set the proper index, and break out of the loop.
					if(!self.bFormPages[self.bFormPageIdArray[self.currentBFormPageIdArrayIndex + i]].disabledByDependency){
						self.currentBFormPageIdArrayIndex = self.currentBFormPageIdArrayIndex + i;
						break;
					}
				}
			} else {
				self.currentBFormPageIdArrayIndex = self.currentBFormPageIdArrayIndex + 1;
			}
			self.scrollToPage(self.bFormPageIdArray[self.currentBFormPageIdArrayIndex]);
		}
	}
},

validateAll: function(){
	var self = this;
	var validationPassed = true;
	var index = 0;
	$.each(this.bFormPages, function(bFormPageKey, bFormPage) {
		var passed = bFormPage.validate();
		//console.log(bFormPage.id, 'passed', passed);
		if(passed === false) {
			//console.log('something went wrong' );
			self.currentBFormPageIdArrayIndex = index;
			if(self.currentBFormPage.id != bFormPage.id) {
				bFormPage.scrollTo();
			}
			validationPassed = false;
			return false; // Break out of the .each
		}
		index++;
	});
	return validationPassed;
},

adjustHeight: function(options) {
	//console.log('bFormer:adjustHeight', options)

	var self = this;
	var duration = this.options.animationOptions.pageScroll.adjustHeightDuration;

	// Use custom one time duration settings
	if(this.initializing){
		duration = 0;
	}
	else if(options && options.adjustHeightDuration !== undefined) {
		duration = options.adjustHeightDuration;
	}

	if(!this.initializing) {
		this.bFormPageWrapper.animate({
			'height' : self.getActivePage().page.outerHeight()
		}, duration);
	}
},

submitForm: function(event) {
	var self = this;

	// Use a temporary form targeted to the iframe to submit the results
	var formClone = this.form.clone(false);
	formClone.attr('id', formClone.attr('id')+'-clone');
	formClone.attr('style', 'display: none;');
	formClone.empty();
	formClone.appendTo($(this.form).parent());
	// Wrap all of the form responses into an object based on the component bFormComponentType
	var formData = $('<input type="hidden" name="bFormer" />').attr('value', encodeURIComponent(bFormerUtility.jsonEncode(this.getData()))); // Set all non-file values in one form object
	var formIdentifier = $('<input type="hidden" name="bFormerId" value="'+this.id+'" />');
	formClone.append(formData);
	formClone.append(formIdentifier);


	this.form.find('input:file').each(function(index, fileInput) {
		if($(fileInput).val() != '') {
			// grab the IDs needed to pass
			var sectionId = $(fileInput).closest('.bFormSection').attr('id');
			var pageId = $(fileInput).closest('.bFormPage').attr('id');
			//var fileInput = $(fileInput).clone()

			// do find out the section instance index
			if($(fileInput).attr('id').match(/-section[0-9]+/)){
				var sectionInstance = null;
				var section = $(fileInput).closest('.bFormSection');
				// grab the base id of the section to find all sister sections
				var sectionBaseId = section.attr('id').replace(/-section[0-9]+/, '') ;
				sectionId = sectionId.replace(/-section[0-9]+/, '');
				// Find out which instance it is
				section.closest('.bFormPage').find('div[id*='+sectionBaseId+']').each(function(index, fileSection){
					if(section.attr('id') == $(fileSection).attr('id')){
						sectionInstance = index + 1;
						return false;
					}
					return true;
				});
				fileInput.attr('name', fileInput.attr('name').replace(/-section[0-9]+/, '-section'+sectionInstance));
			}

			// do find out the component instance index
			if($(fileInput).attr('id').match(/-instance[0-9]+/)){
				// grab the base id of the component to find all sister components
				var baseId = $(fileInput).attr('id').replace(/-instance[0-9]+/, '')
				var instance = null;
				// Find out which instance it is
				$(fileInput).closest('.bFormSection').find('input[id*='+baseId+']').each(function(index, fileComponent){
					if($(fileComponent).attr('id') == $(fileInput).attr('id')){
						instance = index + 1;
						return false;
					}
					return true;
				});
				fileInput.attr('name', $(fileInput).attr('name').replace(/-instance[0-9]+/, '-instance'+instance));
			}

			$(fileInput).attr('name', $(fileInput).attr('name')+':'+pageId+':'+sectionId);
			$(fileInput).appendTo(formClone);
		}
	});
		
	// Submit the form
	formClone.submit();
	formClone.remove(); // Ninja vanish!

	// Find the submit button and the submit response
	if(!this.options.debugMode){
		this.controlNextButton.text(this.options.submitProcessingButtonText).attr('disabled', 'disabled');
	}
	else {
		this.form.find('iframe:hidden').show();
	}

	// Add a processing li to the form control
	this.control.append('<li class="processingLi"></li>');
},

handleFormSubmissionResponse: function(json) {
	var self = this;
		
	// Remove the processing li from the form control
	this.control.find('.processingLi').remove();

	// Form failed processing
	if(json.status == 'failure') {
		// Handle validation failures
		if(json.response.validationFailed) {
			$.each(json.response.validationFailed, function(bFormPageKey, bFormPageValues){
				$.each(bFormPageValues, function(bFormSectionKey, bFormSectionValues){
					// Handle section instances
					if($.isArray(bFormSectionValues)) {
						$.each(bFormSectionValues, function(bFormSectionInstanceIndex, bFormSectionInstanceValues){
							var sectionKey;
							if(bFormSectionInstanceIndex != 0) {
								sectionKey = '-section'+(bFormSectionInstanceIndex + 1);
							}
							else {
								sectionKey = '';
							}
							$.each(bFormSectionInstanceValues, function(bFormComponentKey, bFormComponentErrors) {
								self.bFormPages[bFormPageKey].bFormSections[bFormSectionKey].instanceArray[bFormSectionInstanceIndex].bFormComponents[bFormComponentKey + sectionKey].handleServerValidationResponse(bFormComponentErrors);
							});
						});
					}
					// There are no section instances
					else {
						$.each(bFormSectionValues, function(bFormComponentKey, bFormComponentErrors){
							self.bFormPages[bFormPageKey].bFormSections[bFormSectionKey].bFormComponents[bFormComponentKey].handleServerValidationResponse(bFormComponentErrors);
						});
					}
				});
			});
		}

		// Show the failureHtml if there was a problem
		if(json.response.failureHtml) {
			// Update the failure HTML
			this.control.find('.bFormerFailure').remove();
			this.control.after('<div class="bFormerFailure">'+json.response.failureHtml+'</div>');
		}

		// Strip the script out of the iframe
		this.form.find('iframe').contents().find('body script').remove();
		if(this.form.find('iframe').contents().find('body').html() !== null) {
			this.form.find('.bFormerFailure').append('<p>Output:</p>'+this.form.find('iframe').contents().find('body').html().trim());
		}

		// Reset the page, focus on the first failed component
		this.controlNextButton.text(this.options.submitButtonText);
		this.controlNextButton.removeAttr('disabled');
		this.getActivePage().focusOnFirstFailedComponent();
	}
	// Form passed processing
	else if(json.status == 'success'){
		// Show a success page
		if(json.response.successPageHtml){
			// Stop saving the form
			clearInterval(this.saveIntervalSetTimeoutId);

			// Create the success page html
			var successPageDiv = $('<div id="'+this.id+'bFormPageSuccess" class="bFormPage bFormPageSuccess">'+json.response.successPageHtml+'</div>');
			successPageDiv.css('width', this.bFormPages[this.bFormPageIdArray[0]].page.width());
			this.bFormPageScroller.css('width', this.bFormPageScroller.width() + this.bFormPages[this.bFormPageIdArray[0]].page.width());
			this.bFormPageScroller.append(successPageDiv);

			// Create the success page
			var bFormPageSuccess = new BFormPage(this, this.id+'bFormPageSuccess');
			this.addBFormPage(bFormPageSuccess);

			// Hide the page navigator and controls
			this.control.hide();
			if(this.bFormPageNavigator) {
				this.bFormPageNavigator.hide();
			}

			// Scroll to the page
			bFormPageSuccess.scrollTo();
		}
		// Show a failure page that allows you to go back
		else if(json.response.failurePageHtml){
			// Create the failure page html
			var failurePageDiv = $('<div id="'+this.id+'bFormPageFailure" class="bFormPage bFormPageFailure">'+json.response.failurePageHtml+'</div>');
			failurePageDiv.width(this.bFormPages[this.bFormPageIdArray[0]].page.width());
			this.bFormPageScroller.append(failurePageDiv);

			// Create the failure page
			var bFormPageFailure = new BFormPage(this, this.id+'bFormPageFailure');
			this.addBFormPage(bFormPageFailure);

			// Create a start over button
			this.control.append($('<li class="startOver"><button class="startOverButton">Start Over</button></li>'));

			// Scroll to the failure page
			bFormPageFailure.scrollTo();
		}
		// Show a failure notice on the same page
		if(json.response.failureNoticeHtml){
			this.control.find('.bFormerFailureNotice').remove();
			this.control.append('<li class="bFormerFailureNotice">'+json.response.failureNoticeHtml+'</li>');
			this.controlNextButton.text(this.options.submitButtonText);
			this.controlNextButton.removeAttr('disabled');
		}

		// Show a large failure response on the same page
		if(json.response.failureHtml){
			this.control.find('.bFormerFailure').remove();
			this.control.after('<div class="bFormerFailure">'+json.response.failureHtml+'</div>');
			this.controlNextButton.text(this.options.submitButtonText);
			this.controlNextButton.removeAttr('disabled');
		}

		// Evaluate any failure or successful javascript
		if(json.response.successJs){
			eval(json.response.successJs);
		}
		else if(json.response.failureJs){
			eval(json.response.failureJs);
		}

		// Redirect the user
		if(json.response.redirect){
			this.controlNextButton.html('Redirecting...');
			document.location = json.response.redirect;
		}

		// Reload the page
		if(json.response.reload){
			this.controlNextButton.html('Reloading...');
			document.location.reload(true);
		}
	}
},

reset: function() {
	this.control.find('.bFormFailureNotice').remove();
	this.control.find('.bFormFailure').remove();
	this.controlNextButton.text(this.options.submitButtonText);
	this.controlNextButton.removeAttr('disabled');
},

showAlert: function(message, bFormComponentType, modal, options){
	if(!this.options.alertsEnabled){
		return;
	}
	var animationOptions = $.extend(this.options.animationOptions.alert, options);


	var alertWrapper = this.form.find('.bFormerAlertWrapper');
	var alertDiv = this.form.find('.bFormerAlert');

	alertDiv.addClass(bFormComponentType);
	alertDiv.text(message);

	// Show the message
	if(animationOptions.appearEffect == 'slide'){
		alertWrapper.slideDown(animationOptions.appearDuration, function(){
			// hide the message
			setTimeout(hideAlert, 1000);
		});
	} else if(animationOptions.appearEffect == 'fade') {
		alertWrapper.fadeIn(animationOptions.appearDuration, function(){
			// hide the message
			setTimeout(hideAlert, 1000);
		});
	}

	function hideAlert(){
		if(animationOptions.hideEffect == 'slide'){
			alertWrapper.slideUp(animationOptions.hideDuration, function() {
				});
		} else if(animationOptions.hideEffect == 'fade'){
			alertWrapper.fadeOut(animationOptions.hideDuration, function() {
				});
		}
	}

},

showModal: function(header, content, className, options) {
	// Get the modal wrapper div element
	var modalWrapper = this.form.find('.bFormerModalWrapper');

	// set animation options
	var animationOptions = $.extend(this.options.animationOptions.modal, options);

	// If there is no modal wrapper, add it
	if(modalWrapper.length == 0) {
		var modalTransparency = $('<div class="bFormerModalTransparency"></div>');
		modalWrapper = $('<div style="display: none;" class="bFormerModalWrapper"><div class="bFormerModal"><div class="bFormerModalHeader">'+header+'</div><div class="bFormerModalContent">'+content+'</div><div class="bFormerModalFooter"><button>Okay</button></div></div></div>');

		// Add the modal wrapper after the alert
		this.form.find('.bFormerAlertWrapper').after(modalTransparency);
		this.form.find('.bFormerAlertWrapper').after(modalWrapper);

		// Add any custom classes
		if(className != '') {
			modalWrapper.addClass(className);
		}

		// Add the onclick event for the Okay button
		modalWrapper.find('button').click(function(event) {
			$('.bFormerModalWrapper').hide(animationOptions.hideDuration);
			$('.bFormerModalTransparency').hide(animationOptions.hideDuration);
			$('.bFormerModalWrapper').remove();
			$('.bFormerModalTransparency').remove();
			$('body').css('overflow','auto');
		});
	}

	// Get the modal div element
	var modal = modalWrapper.find('.bFormerModal');
	modal.css({
		'position':'absolute'
	});
	var varWindow = $(window);
	$('body').css('overflow','hidden');
	// Add window resize and scroll events
	varWindow.resize(function(event) {
		leftMargin = (varWindow.width() / 2) - (modal.width() / 2);
		topMargin = (varWindow.height() / 2) - (modal.height() / 2) + varWindow.scrollTop();
		modal.css({
			'top': topMargin,
			'left': leftMargin
		});
		$('.bFormerModalTransparency').width(varWindow.width()).height(varWindow.height());
	});

	// If they click away from the modal (on the modal wrapper), remove it
	$('.bFormerModalTransparency').click(function(event) {
		if($(event.target).is('.bFormerModalTransparency')) {
			modalWrapper.hide(animationOptions.hideDuration);
			modalWrapper.remove();
			$('.bFormerModalTransparency').hide(animationOptions.hideDuration);
			$('.bFormerModalTransparency').remove();
			$('body').css('overflow','auto');
		}
	});

	// Show the wrapper
	//modalWrapper.width(varWindow.width()).height(varWindow.height()*1.1).css('top', varWindow.scrollTop());
	modalWrapper.show(animationOptions.appearDuration);

	// Set the position
	var leftMargin = (varWindow.width() / 2) - (modal.width() / 2);
	var topMargin = (varWindow.height() / 2) - (modal.height() / 2) + varWindow.scrollTop();
	$('.bFormerModalTransparency').width(varWindow.width()).height(varWindow.height()*1.1).css('top', varWindow.scrollTop());
	modal.css({
		'top': topMargin,
		'left': leftMargin
	});
},

addBlurTipListener: function(){
	var self = this;
	$(document).bind('blurTip', function(event, tipElement, action){
		if(action == 'hide'){
			self.blurredTips = $.map(self.blurredTips, function(tip, index){
				if($(tip).attr('id') == tipElement.attr('id')){
					return null
				} else {
					return tip;
				}
			});
			if(self.blurredTips[self.blurredTips.length-1] != undefined){
				self.blurredTips[self.blurredTips.length-1].removeClass('bFormerTipBlurred');
			}
		} else if(action == 'show'){
			if(self.blurredTips.length > 0){
				$.each(self.blurredTips, function(index, tip){
					$(tip).addClass('bFormerTipBlurred')
				})
			}
			self.blurredTips.push(tipElement)
			tipElement.removeClass('bFormerTipBlurred');
		}
	});
}
});
