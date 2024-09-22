Vue.use(VueToast);
$ = jQuery.noConflict();

const toastOptions = {
    message: 'Something went wrong, please try again!<br/> If you are experiencing this issue repeatedly, please raise a <a target="_blank" href="https://club.wpeka.com/my-account/orders/?utm_source=wplegalpages&utm_medium=wizard&utm_campaign=link&utm_content=support">support ticket</a> with us.',
    type: 'error',
    position: 'bottom-right',
    duration: 10000,
    dismissible: true,
    queue: true,
    pauseOnHover: true
}
Vue.component('tinymce', Editor);

Vue.component('TheWizardHeader', {
    data:function(){
        return {
            text_exit:wizard_obj.return_button_text,
            href:wizard_obj.return_url
        }
    },
    render(createElement){
        return createElement('header',{
            staticClass:'wplegal-wizard-header'
        },[createElement('nav',{
            staticClass: 'wplegal-header-navigation'
        },[createElement('a',{
            staticClass: 'wplegal-exit-button',
            attrs: {
                href: this.href
            }
        },[createElement('i',{
            staticClass: 'dashicons dashicons-dismiss'
        }),createElement('span',{
            domProps: {
                textContent: this.text_exit
            }
        })])]),createElement('h1',{
            staticClass: "wplegal-wizard-logo"
        },[createElement('div',{
            staticClass: "wplegal-logo"
        },[createElement('div',{
            staticClass: "wplegal-bg-img"
        })])])])
    }
});

Vue.component('TheWizardTimeline', {
    data: function() {
        return {
            steps: this.$parent.wizardSteps
        }
    },
    methods: {
        stepClass: function(index) {
            var e = "wplegal-wizard-step"
                , s = 0;
            for (var step in this.steps)
                this.$parent.route.name === this.steps[step] && (s = step);
            return index < s && (e += " wplegal-wizard-step-completed"),
            parseInt(index) === parseInt(s) && (e += " wplegal-wizard-step-active"),
                e
        },
        lineClass: function(index) {
            var e = "wplegal-wizard-step-line"
                , s = 0;
            for (var step in this.steps)
                this.$parent.route.name === this.steps[step] && (s = step);
            return index <= s && (e += " wplegal-wizard-line-active"),
                e
        },
        renderElements(createElement) {
            var html = [];
            this.steps.forEach((value,index) => {
                if(index > 0) {
                    html.push(createElement('div',{
                        class: this.lineClass(index)
                    },[]));
                }
                html.push(createElement('div', {
                    class: this.stepClass(index),
                    domProps: {
                        textContent: index+1,
                    }
                },[]));
            });
            return html;
        }
    },
    render(createElement) {
        return createElement('div',{
            staticClass: 'wplegal-wizard-steps'
        },this.renderElements(createElement))
    }
});

Vue.component('StepHeader',{
    render(createElement){
        return createElement('header',{},[createElement('h2',{
            domProps: {
                innerHTML: this.$parent.text_title
            }
        }),createElement('p',{
            staticClass: 'subtitle',
            domProps: {
                innerHTML: this.$parent.text_subtitle
            }
        })])
    }
});

Vue.component('Separator',{
    render(createElement){
        return createElement('div',{
            staticClass:'wplegal-separator'
        });
    }
});

Vue.component('WplegalInfoTooltip',{
    props: {
        content: String
    },
    data:function(){
        return {
            contents: this.content,
        }
    },
    render(createElement) {
        return createElement('span',{
                staticClass:'wplegal-info dashicons dashicons-editor-help'
            }
            ,[createElement('span',{
                staticClass:'wplegal-info-text',
                domProps: {
                    innerHTML: this.contents
                }
            })]
        )
    }
});

Vue.component('Loading',{
    render(createElement) {
        return createElement('div',{
            staticClass: 'wplegal-loader'
        })
    }
});

Vue.component('WplegalTimer', {
    props: {
        step: String
    },
    data:function(){
        return {
            count: 15,
        }
    },
    methods: {
        countDownTimer() {
            if(this.count > 0) {
                setTimeout(() => {
                    this.count -= 1
                    this.countDownTimer()
                }, 1000)
            } else {
                this.$router.push(this.step);
            }
        }
    },
    created: function(){
        this.countDownTimer();
    },
    render(createElement){
        var self = this;
        return createElement('span', {
            domProps: {
                textContent: 'You will be redirected to the previous step in '+self.count+' seconds or '
            }
        })
    }
});

Vue.component('ErrorMessage',{
    props: {
        step: String
    },
    render(createElement) {
        var self = this;
        return createElement('div',{
            staticClass: 'wplegal-error',
        }, [createElement('p', {
            domProps: {
                textContent: 'Something went wrong, please try again later!'
            }
        }), createElement('p', {}, [ createElement('WplegalTimer', {
            attrs: {
                step: self.step
            }
        }), createElement('a', {
            attrs: {
                href: '#'
            },
            on: {
                click: function(e) {
                    return e.preventDefault(),
                        self.$router.push(self.step);
                }
            },
            domProps: {
                textContent: 'click here to go back to previous step'
            }
        })])])
    }
});

Vue.component('GettingStartedWizardForm',{
    data: function() {
        return {
            formElements: [],
            loading: 1,
            formProElements: [],
        }
    },
    methods: {
        handleSubmit: function() {
            this.$router.push('page_settings');
            this.$root.route.name = 'page_settings';
        },
		storeAuth: function ( data,callback ) {
			// Create spinner element
			var spinner = jQuery('<div class="wplegal-ajax-spinner"></div>');
			jQuery('.wplegal-wizard').append(spinner);

			//Make Ajax Call
			jQuery.ajax({
				type: 'POST',
				url: wizard_obj.ajax_url,
				data: {
					action: 'wp_legal_pages_app_store_auth',
					_ajax_nonce : wizard_obj._ajax_nonce,
					response: data.response,
					origin: data.origin,

				},
				beforeSend: function() {
					// Show spinner before AJAX call starts
					spinner.show();
				},
				complete: function() {
					// Hide spinner after AJAX call completes
					spinner.hide();
				},
				success: function(response) {

					// remove hidden instance from the local storage
					localStorage.removeItem('wplegalConnectPopupHide');
					//remove disconnect from local storage when user connects to the api
					localStorage.removeItem('wplegalDisconnect');

					jQuery('.wplegal-api-overlay').hide();
					jQuery('.wplegal-api-connection-popup').hide();
					callback();
				},
				error: function(error) {
					// Handle error response
					console.error('Error sending data to PHP:', error);
				}
			});
		},
		startAuth: function ( is_new_user,callback ) {
			var self = this;

			// Create spinner element
			var spinner = jQuery('<div class="wplegal-ajax-spinner"></div>');

			// Append spinner to .wplegal-wizard div.

			var container = jQuery('.wplegal-wizard');
			container.css('position', 'relative'); // Ensure container has relative positioning.
			container.append(spinner);

			// Make an AJAX request.
			jQuery.ajax(
				{
					url  : wizard_obj.ajax_url,
					type : 'POST',
					data : {
						action      : 'wp_legal_pages_app_start_auth',
						_ajax_nonce : wizard_obj._ajax_nonce,
						is_new_user : is_new_user,
					},
					beforeSend: function() {
						// Show spinner before AJAX call starts
						spinner.show();
					},
					complete: function() {
						// Hide spinner after AJAX call completes
						spinner.hide();
					}
				}
			)
			.done(
				function ( response ) {

					// Get the width and height of the viewport.
					var viewportWidth = window.innerWidth;
					var viewportHeight = window.innerHeight;

					// Set the dimensions of the popup.
					var popupWidth = 367;
					var popupHeight = 650;

					// Calculate the position to center the popup.
					var leftPosition = (viewportWidth - popupWidth) / 2;
					var topPosition = (viewportHeight - popupHeight) / 2;

					// Open the popup window at the calculated position.
					var e = window.open(
					response.data.url,
					"_blank",
					"location=no,width=" + popupWidth + ",height=" + popupHeight + ",left=" + leftPosition + ",top=" + topPosition + ",scrollbars=0"
					);

					if (null === e) {
						console.log('Failed to open the authentication window');
					} else {
						e.focus();// Focus on the popup window.
					}

					window.addEventListener("message", function(event) {
						//event is originated on server
						if ( event.isTrusted && event.origin === wizard_obj.wplegal_app_url) {
							self.storeAuth(event.data, callback);
						}
					});

				}
			);

		},
        labelClass: function(value) {
            var e = '';
            if(this.isChecked(value)) {
                e += 'wplegal-styled-radio-label-checked'
            }
            return e;
        },
        titleClass: function(value) {
            var e = 'wplegal-styled-radio';
            if(this.isChecked(value)) {
                e += ' wplegal-styled-radio-checked';
            }
            return e;
        },
        isChecked:function(value) {
            if(this.$root.page == value) {
                return true;
            }
            return false;
        },
        updateSettings: function(value) {
            this.$root.page = value;
        },
        createFormTypeLabel(createElement){
            var self = this;
            var html = [];
            var static_classes = 'wplegal-template-type-name';

            for( let key in this.formElements ) {
                var el = createElement('div', {
                    class: 'wplegal-template-type-row'
                },
                [
                    createElement('div',{
                        staticClass: 'wplegal-template-type-header'
                    },[createElement('h4',{
                        staticClass: static_classes,
                        domProps: {
                            textContent: key
                        }
                    })]),
                    createElement('div', {
                        staticClass: 'wplegal-template-type-labels-row'},
                        self.createFormRows(createElement, this.formElements[key])
                    )
                ],
                );
                html.push(el);
            }
            return html;
        },
        createProTemplateLabels(createElement) {
            var self = this;
            var html = [];
            for( let key in this.formProElements ) {
                var el = createElement('div', {
                    class: 'wplegal-template-type-row'
                },
                [
                    createElement('div',{
                        staticClass: 'wplegal-template-type-header'
                    },[createElement('h4',{
                        staticClass: 'wplegal-template-type-name',
                        domProps: {
                            textContent: key
                        }
                    })]),
                    createElement('div', {
                        staticClass: 'wplegal-template-type-labels-row wplegal-template-type-labels-pro-row'},
                        self.createProTemplateRows(createElement, this.formProElements[key])
                    )
                ],
                );
                html.push(el);
            }
            return html;
        },
        createProTemplateRows: function(createElement, formProElements) {
            var self = this;
            var html = [];
            formProElements.forEach((value, index) => {
                var el = createElement('label',{
                    class:'wplegal-pro-label-class',
                },[createElement('img',{
                    domProps:{
                        src: wizard_obj.image_url + value.value+'.png'
                    }
                }),createElement('span',{
                    staticClass: 'wplegal-input-title',
                    domProps: {
                        textContent: value.label
                    }
                }),createElement('p',{
                    staticClass: "wplegal-description",
                    domProps: {
                        textContent: value.description
                    }
                })]);
               html.push(el);
            });
            return html;
        },
        createFormRows:function(createElement, formLabelElements) {
            var self = this;
            var html = [];
            formLabelElements.forEach((value, index) => {
                var buttonText = value.pid ? wizard_obj.welcome.edit : wizard_obj.welcome.create;
                var el = createElement('label',{
                    class:self.labelClass(value.value)
                },[createElement('span',{
                    class: self.titleClass(value.value)
                }),createElement('input',{
                    attrs:{
                        type:value.type,
                        name:value.name,
                    },
                    domProps: {
                        value: value.value,
                        checked: this.isChecked(value.value)
                    },
                    on: {
                        change: function(e) {
                            e.preventDefault()
                            self.updateSettings(value.value);
                        }
                    }
                }),createElement('img',{
                    domProps:{
                        src: wizard_obj.image_url + value.value+'.png'
                    }
                }),createElement('span',{
                    staticClass: 'wplegal-input-title',
                    domProps: {
                        textContent: value.label
                    }
                }),createElement('p',{
                    staticClass: "wplegal-description",
                    domProps: {
                        textContent: value.description
                    }
                }),createElement('span',{
                    staticClass: 'wplegal-pro-tag',
                    domProps: {
                        textContent: value.pro
                    }
                }), createElement('span', {
                    staticClass: "wplegal-create-button-wrapper"
                }, [createElement('span', {
                    staticClass: "wplegal-create-button",
                    domProps: {
                        textContent: buttonText
                    },
                    on: {
                        click: function(e) {

							if ( wizard_obj.is_user_connected != 'true' ) {

								var overlay = document.createElement('div');
								overlay.classList.add('wplegal-api-overlay');

								// Append overlay to the body
								document.body.appendChild(overlay);

								var newDiv = document.createElement('div');
								newDiv.classList.add('wplegal-api-connection-popup');

								// Create and append an h3 element with the upgrade message
								var h3 = document.createElement('h3');
								var h3TextNode = document.createTextNode('Connect Your Website');
								h3.appendChild(h3TextNode);
								newDiv.appendChild(h3);

								// Create and append a paragraph element with the upgrade instructions
								var p = document.createElement('p');
								p.classList.add('wplegal-api-upgrade-text');
								var pTextNode = document.createTextNode('Sign up for an account and get this legal template.');
								p.appendChild(pTextNode);
								newDiv.appendChild(p);

								// Create and append a button element with the specified text
								var button = document.createElement('button');
								button.classList.add('wplegal-api-connect-new');
								button.textContent = "New? Create an account";
								newDiv.appendChild(button);

								// Create and append the "Already have an account?" message and link
								var existingAccountP = document.createElement('p');
								existingAccountP.classList.add('wplegal-api-connect-text');
								existingAccountP.innerHTML = 'Already have an account? <span class="wplegal-api-connect-existing"><a href="#">Connect your existing account</a></span>';
								newDiv.appendChild(existingAccountP);

								// Append the new div just below the .wplegal-admin-page wizard
								var wizardElement = document.querySelector('.wplegal-admin-page.wizard');
								wizardElement.parentNode.insertBefore(newDiv, wizardElement.nextSibling);

								var closeIcon = document.createElement('span');
								closeIcon.classList.add('wplegal-api-close-icon');
								closeIcon.innerHTML = '&times;'; // Using the 'times' symbol (×) for the close icon
								newDiv.appendChild(closeIcon);

								// Add a click event listener to the close icon to remove the popup
								closeIcon.addEventListener('click', function() {
									newDiv.parentNode.removeChild(newDiv); // Remove the popup from the DOM
									overlay.parentNode.removeChild(overlay);
								});


								// Register event handler for the button inside the popup
								newDiv.querySelector('.wplegal-api-connect-new').addEventListener('click', function() {

									var is_new_user = this.classList.contains('wplegal-api-connect-new');

									self.startAuth(is_new_user,function() {
										// This function will be called when the AJAX call inside startAuth is successful
										// Execute updateSettings and handleSubmit here
										self.updateSettings(value.value);
										self.handleSubmit(e);
									});
								});

								newDiv.querySelector('.wplegal-api-connect-existing').addEventListener('click', function() {

									var is_new_user = '';

									self.startAuth(is_new_user,function() {
										// This function will be called when the AJAX call inside startAuth is successful
										// Execute updateSettings and handleSubmit here
										self.updateSettings(value.value);
										self.handleSubmit(e);
									});
								});
							}else{
								self.updateSettings(value.value);
								return e.preventDefault(),
									self.handleSubmit(e);
							}
                        }
                    }
                })])]);
               html.push(el);
            });
            return html;
        }
    },
    created: function(){
        this.$parent.hasError = !1;
        $.get(wizard_obj.ajax_url,{'action':'step_settings','nonce':wizard_obj.ajax_nonce,'step':'getting_started','page':this.$root.page}).then((response => {
            if(response.success) {
                this.formElements = response.data[0];
                this.formProElements = response.data[1];
            } else {
                Vue.$toast.open( toastOptions );
            }
            this.loading = !1;
        }));
    },
    updated: function () {
        this.$nextTick(function () {
            this.loading = !1;
        })
    },
    render(createElement){
        var self = this;
        return this.loading ? createElement('loading') : createElement('form',{
           staticClass:'wplegal-wizard-getting-started-form',
           on: {
               submit: function(e) {
                   return e.preventDefault(),
                       self.handleSubmit(e);
               }
           }
       },[createElement('div',{
           staticClass: 'wplegal-form-row'
       },[createElement('div',{
           staticClass:'wplegal-form-label'
       },[createElement('label',{
           domProps: {
               textContent: this.$parent.input_title
           }
       }),createElement('p',{
           staticClass: "wplegal-description",
           domProps: {
               textContent: this.$parent.input_subtitle
           }
       })]), createElement('WizardForms')]),createElement('Separator') ,createElement('div',{
           staticClass:'wplegal-form-row wplegal-form-buttons'
       },[createElement('button',{
           staticClass: "wplegal-wizard-button wplegal-wizard-button-next wplegal-wizard-button-large",
           attrs: {
               type: "submit",
               name: "next_step"
           },
           domProps: {
               textContent: this.$parent.text_next
           }
       })])]);
   }
});

Vue.component('WizardPromotional', {
    render(createElement) {
        return createElement('div',{
            staticClass: 'wplegal-wizard-promotion'
        },[
            createElement('span',{
                staticClass: 'wplegal-wizard-promotion-text',
                domProps: {
                    textContent: wizard_obj.promotion_text
                }
            }), createElement('a', {
                staticClass: 'wplegal-wizard-promotion-link',
                attrs: {
                    href: wizard_obj.promotion_link,
                    target: '_blank'
                },
            },[
                createElement('span', {
                    staticClass: 'wplegal-wizard-promotion-button',
                    domProps: {
                        textContent: wizard_obj.promotion_button
                    }
                })
            ])
        ])
    }
})

Vue.component('WizardForms', {
    render(createElement) {
        var self = this;
		return createElement('fieldset',{},[createElement('div',{
			staticClass:'wplegal-settings-input-radio'
		},[self.$parent.createFormTypeLabel(createElement)])])
    }
})

Vue.component('PageSettingsWizardForm',{
    data: function() {
        return {
            formElements: [],
            loading: 1,
            template: '',
            skip_section_templates: ['ccpa_free', 'dmca', 'coppa', 'terms_forced', 'gdpr_cookie_policy', 'gdpr_privacy_policy', 'cookies_policy', 'linking_policy', 'external_link_policy', 'blog_comments_policy', 'affiliate_disclosure', 'amazon_affiliate_disclosure' ,'testimonials_disclosure', 'confidentiality_disclosure', 'general_disclaimer', 'earnings_disclaimer', 'medical_disclaimer', 'newsletters', 'antispam', 'ftc_statement', 'double_dart', 'about_us', 'cpra' , 'advertising_disclosure']
        }
    },
    methods: {
        handleSubmit: function() {
            var data = {};
            $('.wplegal-wizard-settings-form input').each(function(){
                data[this.name] = this.value;
            });
            $('.wplegal-wizard-settings-form select').each(function(){
                data[this.name] = this.value;
            });
            $.post(wizard_obj.ajax_url,{'action':'page_settings_save','data':data,'nonce':wizard_obj.ajax_nonce,'page':this.$root.page}).then((response => {
                if(response.success) {
                    if( this.skip_section_templates.indexOf(this.template) >= 0 ) {
                        this.$router.push('page_preview');
                        this.$root.route.name = 'page_preview';
                    } else {
                        this.$router.push('page_sections');
                        this.$root.route.name = 'page_sections';
                    }
                }  else {
                    this.$router.push('page_settings');
                    this.$root.route.name = 'page_settings';
                    Vue.$toast.open( toastOptions );
                }
            }))
        },
        handlePrev: function() {
            this.$router.push('/');
            this.$root.route.name = 'getting_started';
        },
        createSelectOptions:function(createElement, options) {
            var self = this;
            var html = [];
            options.forEach((value, index) => {
                var el = createElement('option',{
                    attrs: {
                        value: value.value
                    },
                    domProps: {
                        selected:value.selected,
                        textContent: value.label
                    }
                });
                html.push(el);
            });
            return html;
        },
        createFormRows: function(createElement) {
            var self = this;
            var html = [];
            this.formElements.forEach((value, index) => {
                if(value.type == 'select') {
                    var el = createElement('div', {
                        staticClass: 'wplegal-form-row'
                    },[createElement('fieldset',{},[createElement('div',{
                        staticClass: 'wplegal-settings-input-select'
                    },[createElement('label',{},[createElement('span',{
                        staticClass:'wplegal-dark',
                        domProps: {
                            textContent: value.label
                        }
                    })]),createElement('div',{
                        staticClass:'wplegal-settings-input-select-input'
                    },[createElement('select',{
                        staticClass: 'select',
                        attrs: {
                            name:value.name
                        }
                    },[self.createSelectOptions(createElement, value.options)])]) ])])]);
                    setTimeout(function(){
                        $('select').select2({
                            multiple: false,
                            width: '100%'
                        })
                    },1000);

                } else if( this.$root.page === 'custom_legal' ){
                    var el = createElement('div',{
                        staticClass:'wplegal-form-row'
                    },[createElement('fieldset',{},[createElement('div',{
                        staticClass:'settings-input-text'
                    },[createElement('div',{
                                staticClass:'wplegal-include-shortcode'
                            },
                    [createElement('label',{},[createElement('span',{
                        staticClass:'wplegal-dark',
                        domProps: {
                            textContent: value.label
                        }
                    })]),createElement('div',{
                        staticClass:'settings-input-text wplegal-form-shortcode'
                    },[createElement('label',{},[createElement('span',{
                        domProps: {
                            textContent: value.shortcode
                        }
                    }),
                    createElement('wplegal-info-tooltip',{
                        attrs: {
                            content: 'Use this placeholder in your Legal Policy description.'
                        }
                    })
                ])])]),createElement('div',{
                        staticClass:'settings-input-text-input'
                    },[createElement('input',{
                        attrs: {
                            type:value.type,
                            name:value.name
                        },
                        domProps: {
                            value:value.value
                        }
                    })])])])]);

                }
                else {
                    var el = createElement('div',{
                        staticClass:'wplegal-form-row'
                    },[createElement('fieldset',{},[createElement('div',{
                        staticClass:'settings-input-text'
                    },[createElement('label',{},[createElement('span',{
                        staticClass:'wplegal-dark',
                        domProps: {
                            textContent: value.label
                        }
                    })]),createElement('div',{
                        staticClass:'settings-input-text-input'
                    },[createElement('input',{
                        attrs: {
                            type:value.type,
                            name:value.name
                        },
                        domProps: {
                            value:value.value
                        }
                    })])])])]);
                }
                html.push(el);
            });
            return html;
        }
    },
    created: function(){
        this.$parent.hasError = !1;
        $.get(wizard_obj.ajax_url,{'action':'step_settings','nonce':wizard_obj.ajax_nonce,'step':'page_settings','page':this.$root.page}).then((response => {
            if(response.success) {
                this.formElements = response.data;
                this.template     = response.page;
            } else {
                this.$router.push('/');
                this.$root.route.name = 'getting_started';
                Vue.$toast.open( toastOptions );
            }
            this.loading = !1;
        }))
    },
    updated: function () {
        this.$nextTick(function () {
            this.loading = !1;
        })
    },
    render(createElement){
        var self = this;
        return this.loading ? createElement('loading') : createElement('form',{
            staticClass:'wplegal-wizard-settings-form',
            attrs: {
                method:'post'
            },
            on: {
                submit: function(e) {
                    $('.wplegal-wizard-button-next').prop('disabled', true);
                    $('.wplegal-wizard-button-next').addClass('wplegal-wizard-button-loading');
                    return e.preventDefault(),
                        self.handleSubmit(e);
                }
            }
        },[self.createFormRows(createElement),createElement('Separator'), createElement('div',{
            staticClass:'wplegal-form-row wplegal-form-buttons'
        },[createElement('button',{
            staticClass: "wplegal-wizard-button wplegal-wizard-button-prev wplegal-wizard-button-large",
            attrs: {
                type: "",
                name: "prev_step"
            },
            on: {
                click: function(e) {
                    return e.preventDefault(),
                        self.handlePrev(e);
                }
            },
            domProps: {
                textContent: this.$parent.text_prev
            }
        }), createElement('button',{
            staticClass: "wplegal-wizard-button wplegal-wizard-button-next wplegal-wizard-button-large",
            attrs: {
                type: "submit",
                name: "next_step"
            },
            domProps: {
                textContent: this.$parent.text_next
            }
        })])]);
    }
});

Vue.component('PageSectionsWizardForm',{
    data: function() {
        return {
            formElements: [],
            formSettings: [],
            loading: 1
        }
    },
    methods: {
        handleSubmit: function() {
            var self = this;
            var data = {};
            $('.wplegal-wizard-sections-form input[type="hidden"]').each(function(){
                data[this.name] = this.value;
            });
            $('.wplegal-wizard-sections-form input[type="checkbox"]').each(function(){
                if(this.checked) {
                    data[this.name] = this.value;
                }
            });
            $('.wplegal-wizard-sections-form input[type="radio"]').each(function(){
                if(this.checked) {
                    data[this.name] = this.value;
                }
            });
            $('.wplegal-wizard-sections-form textarea').each(function(){

                if( self.$root.page === 'custom_legal'){
                    var id = '#' + this.id +'_ifr';
                    data[this.name] = $(id).contents().find("body").html();
                } else{
                    data[this.name] = this.value;
                }

            });
            $('.wplegal-wizard-sections-form input[type="text"]').each(function(){
                data[this.name] = this.value;
            });
            $('.wplegal-wizard-sections-form select').each(function(){
                var selected = [...this.options]
                    .filter(option => option.selected)
                    .map(option => option.value);
                this.name = this.name.slice(0,-2);
                data[this.name] = selected;
            });
            $.post(wizard_obj.ajax_url,{'action':'page_sections_save','data':data,'nonce':wizard_obj.ajax_nonce,'page':this.$root.page}).then((response => {
                if(response.success) {
                    this.$router.push('page_preview');
                    this.$root.route.name = 'page_preview';
                } else {
                    this.$router.push('page_sections');
                    this.$root.route.name = 'page_sections';
                    Vue.$toast.open( toastOptions );
                }
            }));
        },
        handlePrev: function() {
            this.$router.push('page_settings');
            this.$root.route.name = 'page_settings';
        },
        labelClass: function(key) {
            var s = '';
            if(this.isChecked(key)) {
                s += 'wplegal-styled-radio-label-checked'
            }
            return s;
        },
        titleClass: function(key) {
            var s = 'wplegal-styled-radio';
            if(this.isChecked(key)) {
                s += ' wplegal-styled-radio-checked';
            }
            return s;
        },
        labelBoxClass: function(key) {
            var s = '';
            if(this.isChecked(key)) {
                s += 'wplegal-styled-checkbox-label-checked'
            }
            return s;
        },
        titleBoxClass: function(key) {
            var s = 'wplegal-styled-checkbox';
            if(this.isChecked(key)) {
                s += ' wplegal-styled-checkbox-checked';
            }
            return s;
        },
        isChecked:function(key) {
            if(this.formSettings[key].checked) {
                return true;
            }
            return false;
        },
        collapsibleClass:function(field) {
            var s = ' ';
            if(this.isCollapsible(field)) {
                s += 'wplegal-collapsible wplegal-collapsible-hide'
            }
            return s;
        },
        isCollapsible:function(field) {
          if(field.collapsible) {
              return true;
          }
          return false;
        },
        updateSettings: function(event){
            id = event.target.id;
            parent = $('#'+id).parents('div.wplegal-settings-input-radio');
            var ids = [];
            parent.siblings('div.wplegal-settings-input-radio').each(function() {
                var ele = $(this).find('input[type="radio"]');
                ids.push(ele.attr('id'));
            });
            ids.forEach((value, index) => {
                this.formSettings[value].checked = false;
            });
            this.formSettings[id].checked = true;
        },
        updateBoxSettings: function(key){
            this.formSettings[key].checked = !this.formSettings[key].checked;
        },
        updateFormSettings: function(key, selected) {
            var sub_fields = this.formSettings[key].sub_fields;
            for(index in sub_fields) {
                var field = sub_fields[index];
                field.checked = false;
                sub_fields[index] = field;
            }
            for(k in selected) {
                var option = selected[k];
                sub_fields[option].checked = true;
            }
            this.formSettings[key].sub_fields = sub_fields;
        },
        sortOptions: function(options) {
            return Object.keys(options).sort().reduce(function (result, key) {
                result[key] = options[key];
                return result;
            }, {});
        },
        createSelectOptions:function(createElement, options) {
            var self = this;
            options = self.sortOptions(options);
            var html = [];
            for(var key in options) {
                var option = options[key];
                var el = createElement('option',{
                    attrs: {
                        value: option.value
                    },
                    domProps: {
                        selected:option.checked,
                        textContent: option.title
                    }
                });
                html.push(el);
            }
            return html;
        },
        createFormFields: function(createElement, fields) {
            var self = this;
            var html = [];
            if(fields) {
                for(var key in fields){
                    var field = fields[key];
                    if(field.type == 'section') {
                        if(this.isCollapsible(field)) {
                            var e = createElement('div',{
                                class: 'wplegal-form-row wplegal-section' + this.collapsibleClass(field)
                            },[createElement('span',{
                                staticClass: 'dashicons dashicons-arrow-down-alt2',
                                on: {
                                    click: function(event) {
                                        event.preventDefault();
                                        var parent = event.target.parentNode;
                                        if($(parent).hasClass('wplegal-collapsible-hide')) {
                                            $(parent).removeClass('wplegal-collapsible-hide');
                                            $(parent).addClass('wplegal-collapsible-show');
                                            $(parent).find('> span.dashicons').removeClass('dashicons-arrow-down-alt2');
                                            $(parent).find('> span.dashicons').addClass('dashicons-arrow-up-alt2');
                                        } else {
                                            $(parent).removeClass('wplegal-collapsible-show');
                                            $(parent).addClass('wplegal-collapsible-hide');
                                            $(parent).find('> span.dashicons').removeClass('dashicons-arrow-up-alt2');
                                            $(parent).find('> span.dashicons').addClass('dashicons-arrow-down-alt2');
                                        }
                                        return event;
                                    }
                                }
                            }),createElement('div',{
                                attrs: {
                                    id: field.id
                                },
                                staticClass: 'wplegal-form-label'
                            },[createElement('label',{
                                domProps: {
                                    innerHTML: field.title
                                }
                            }),field.description != '' ? createElement('wplegal-info-tooltip',{
                                attrs: {
                                    content: field.description
                                }
                            }) : []]), createElement('fieldset',{},[self.createFormFields(createElement, field.sub_fields)])]);
                        } else {
                            var e = createElement('div',{
                                class: 'wplegal-form-row wplegal-section' + this.collapsibleClass(field)
                            },[createElement('div',{
                                attrs: {
                                    id: field.id
                                },
                                staticClass: 'wplegal-form-label'
                            },[createElement('label',{
                                domProps: {
                                    innerHTML: field.title
                                }
                            }),field.description != '' ? createElement('wplegal-info-tooltip',{
                                attrs: {
                                    content: field.description
                                }
                            }) : []]), createElement('fieldset',{},[self.createFormFields(createElement, field.sub_fields)])]);
                        }
                    }
                    if(field.type == 'input') {
                        var e = (createElement('div',{
                            staticClass:'settings-input-text'
                        },[createElement('label',{},[createElement('span',{
                            staticClass:'wplegal-dark',
                            domProps: {
                                innerHTML: field.title
                            }
                        }),field.description != '' ? createElement('wplegal-info-tooltip',{
                            attrs: {
                                content: field.description
                            }
                        }) : []]),createElement('div',{
                            staticClass:'settings-input-text-input'
                        },[createElement('input',{
                            attrs: {
                                type:'text',
                                name:field.name,
                                id: field.id
                            },
                            domProps: {
                                value:field.value
                            }
                        })])]))
                    }
                    if(field.type == 'radio') {
                        var e = (createElement('div',{
                            staticClass:'wplegal-settings-input-radio'
                        },[createElement('label',{
                            class:self.labelClass(key)
                        },[createElement('span',{
                            class: self.titleClass(key)
                        }),createElement('input',{
                            attrs:{
                                type:field.type,
                                name:field.name,
                                id: field.id
                            },
                            domProps: {
                                value: field.value,
                                checked: this.isChecked(key)
                            },
                            on: {
                                change: function(event) {
                                    event.preventDefault();
                                    event.target.checked = !event.target.checked;
                                    self.updateSettings(event);
                                }
                            }
                        }),createElement('span',{
                            staticClass: 'wplegal-input-title',
                            domProps: {
                                innerHTML: field.title
                            }
                        }),field.description != '' ? createElement('wplegal-info-tooltip',{
                            attrs: {
                                content: field.description
                            }
                        }) : []]), createElement('div',{
                            staticClass:'wplegal-sub-field'
                        },[self.createFormFields(createElement, field.sub_fields)])]))
                    }
                    if(field.type == 'checkbox') {
                        var e = (createElement('div',{
                            staticClass:'wplegal-settings-input-checkbox'
                        },[createElement('label',{
                            class:self.labelBoxClass(key)
                        },[createElement('span',{
                            class: self.titleBoxClass(key)
                        }),createElement('input',{
                            attrs:{
                                type:field.type,
                                name:field.name,
                                id:field.id
                            },
                            domProps: {
                                value: field.value,
                                checked: this.isChecked(key)
                            },
                            on: {
                                change: function(event) {
                                    event.preventDefault();
                                    event.target.checked = !event.target.checked;
                                    self.updateBoxSettings(event.target.name);
                                }
                            }
                        }),createElement('span',{
                            staticClass: 'wplegal-input-title',
                            domProps: {
                                innerHTML: field.title
                            }
                        }),field.description != '' ? createElement('wplegal-info-tooltip',{
                            attrs: {
                                content: field.description
                            }
                        }) : []]), createElement('div',{
                            staticClass: 'wplegal-sub-field'
                        },[self.createFormFields(createElement, field.sub_fields)])]))
                    }
                    if(field.type == 'select2') {
                        var e = createElement('div', {
                            staticClass: 'wplegal-form-row'
                        },[createElement('fieldset',{},[createElement('div',{
                            staticClass: 'wplegal-settings-input-select'
                        },[createElement('label',{},[createElement('span',{
                            staticClass:'wplegal-dark',
                            domProps: {
                                textContent: field.title
                            }
                        }),field.description != '' ? createElement('wplegal-info-tooltip',{
                            attrs: {
                                content: field.description
                            }
                        }) : []]),createElement('div',{
                            staticClass:'wplegal-settings-input-select-input'
                        },[createElement('select',{
                            staticClass: 'select2',
                            attrs: {
                                name:field.name+'[]',
                                id: field.id,
                                multiple:true
                            }
                        },[self.createSelectOptions(createElement, field.sub_fields)])]) ])])]);
                        setTimeout(function(){
                            $( '#'+field.id ).select2(
                                {
                                    multiple: true,
                                    width: '100%',
                                    allowClear: false
                                }
                            ).on('change', function(event){
                                event.preventDefault();
                                var key = event.target.id;
                                var selected = [...event.target.options]
                                    .filter(option => option.selected)
                                    .map(option => option.value);
                                self.updateFormSettings(key, selected);
                                return event;
                            })
                        }, 1000);

                    }
                    if(field.type == 'textarea') {
                        var e = (createElement('div',{
                            staticClass:'settings-input-textarea'
                        },[createElement('label',{},[createElement('span',{
                            staticClass:'wplegal-dark',
                            domProps: {
                                innerHTML: field.label
                            }
                        }),field.description != '' ? createElement('wplegal-info-tooltip',{
                            attrs: {
                                content: field.description
                            }
                        }) : []]),createElement('div',{
                            staticClass:'settings-input-textarea-input'
                        },[createElement('textarea',{
                            attrs: {
                                name:field.name,
                                rows:5,
                                id:field.id
                            },
                            domProps: {
                                textContent:field.value
                            }
                        })])]))
                    }
                    if(field.type == 'wpeditor') {
                        var e = (createElement('tinymce',{
                            staticClass:'settings-input-textarea',
                            attrs: {
                                init : {
                                    menubar:false,
                                    height:400,
                                    plugins: ["paste"],
                                    paste_as_text: true,
                                    branding:false
                                 },
                                name:field.name,
                                id: field.id,
                                initialValue:field.value,
                                plugins:"link table",
                                toolbar:"undo redo | formatselect | bold italic | table | alignleft aligncenter alignright alignjustify | link bullist numlist outdent indent ",

                            },
                        }));
                    }
                    html.push(e);
                }
            }

            return html;
        },
        createFormRows: function(createElement) {
            var self = this;
            var html = [];
            for(var key in self.formElements){
                var field = self.formElements[key];
                if(this.isCollapsible(field)) {
                    var e = createElement('div',{
                        attrs: {
                            id: field.id
                        },
                        class: 'wplegal-form-row wplegal-clause' + this.collapsibleClass(field)
                    },[createElement('span',{
                        staticClass: 'dashicons dashicons-arrow-down-alt2',
                        on: {
                            click: function(event) {
                                event.preventDefault();
                                var parent = event.target.parentNode;
                                if($(parent).hasClass('wplegal-collapsible-hide')) {
                                    $(parent).removeClass('wplegal-collapsible-hide');
                                    $(parent).addClass('wplegal-collapsible-show');
                                    $(parent).find('> span.dashicons').removeClass('dashicons-arrow-down-alt2');
                                    $(parent).find('> span.dashicons').addClass('dashicons-arrow-up-alt2');
                                } else {
                                    $(parent).removeClass('wplegal-collapsible-show');
                                    $(parent).addClass('wplegal-collapsible-hide');
                                    $(parent).find('> span.dashicons').removeClass('dashicons-arrow-up-alt2');
                                    $(parent).find('> span.dashicons').addClass('dashicons-arrow-down-alt2');
                                }
                                return event;
                            }
                        }
                    }),createElement('div',{
                        staticClass:'wplegal-settings-input-checkbox'
                    },[createElement('label',{
                        class:self.labelBoxClass(key)
                    },[createElement('span',{
                        class: self.titleBoxClass(key)
                    }),createElement('input',{
                        attrs:{
                            type:'checkbox',
                            name:field.id
                        },
                        domProps: {
                            value: field.value,
                            checked: this.isChecked(key)
                        },
                        on: {
                            change: function(event) {
                                event.preventDefault();
                                event.target.checked = !event.target.checked;
                                self.updateBoxSettings(event.target.name);
                            }
                        }
                    }),createElement('span',{
                        domProps: {
                            innerHTML: field.title
                        }
                    }),field.description != '' ? createElement('wplegal-info-tooltip',{
                        attrs: {
                            content: field.description
                        }
                    }) : []])]),field.fields ? self.createFormFields(createElement, field.fields): []])
                } else {
                    var e = createElement('div',{
                        attrs: {
                            id: field.id
                        },
                        class: 'wplegal-form-row wplegal-clause' + this.collapsibleClass(field)
                    },[createElement('div',{
                        staticClass:'wplegal-form-label'
                    },[createElement('label',{
                        domProps: {
                            innerHTML: field.title
                        }
                    }),field.description != '' ? createElement('wplegal-info-tooltip',{
                        attrs: {
                            content: field.description
                        }
                    }) : []]),createElement('input',{
                        attrs: {
                            type:'hidden',
                            value:'1',
                            name:field.id
                        }
                    }),field.fields ? self.createFormFields(createElement, field.fields): []]);
                }
                html.push(e);
            }
            return html;
        },
        configureSettings: function(fields) {
            for(var key in fields) {
                var field = fields[key];
                var sub_fields = field.sub_fields;
                if(field.type == 'checkbox' || field.type == 'radio') {
                    this.formSettings[key] = field;
                } else if(field.type == 'select' || field.type == 'select2') {
                    this.formSettings[key] = field;
                }
                this.configureSettings(sub_fields);
            }
        }
    },
    created: function(){
        this.$parent.hasError = !1;
        $.get(wizard_obj.ajax_url,{'action':'step_settings','nonce':wizard_obj.ajax_nonce,'step':'page_sections','page':this.$root.page}).then((response => {
			$(document).ready(function() {
				if ($('#no_of_days_information').length) {
					$('#no_of_days_information').prop('required', true);
				}
			});
            if(response.success) {
                this.formElements = response.data;
                for(var key in this.formElements) {
                    var field = this.formElements[key];
                    this.formSettings[key] = field;
                    this.configureSettings(field.fields);
                }
            } else {
                this.$router.push('page_settings');
                this.$root.route.name = 'page_settings';
                Vue.$toast.open( toastOptions );
            }
            this.loading = !1;
        }));
    },
    updated: function () {
        this.$nextTick(function () {
            this.loading = !1;
        })
    },
    render(createElement){
        var self = this;
        return this.loading ? createElement('loading') : createElement('form',{
            staticClass:'wplegal-wizard-sections-form',
            on: {
                submit: function(e) {
                    $('.wplegal-wizard-button-next').prop('disabled', true);
                    $('.wplegal-wizard-button-next').addClass('wplegal-wizard-button-loading');
                    return e.preventDefault(),
                        self.handleSubmit(e);
                }
            }
        },[self.createFormRows(createElement),createElement('Separator'),createElement('div',{
            staticClass:'wplegal-form-row wplegal-form-buttons'
        },[createElement('button',{
            staticClass: "wplegal-wizard-button wplegal-wizard-button-prev wplegal-wizard-button-large",
            attrs: {
                type: "",
                name: "prev_step"
            },
            on: {
                click: function(e) {
                    return e.preventDefault(),
                        self.handlePrev(e);
                }
            },
            domProps: {
                textContent: this.$parent.text_prev
            }
        }), createElement('button',{
            staticClass: "wplegal-wizard-button wplegal-wizard-button-next wplegal-wizard-button-large",
            attrs: {
                type: "submit",
                name: "next_step"
            },
            domProps: {
                textContent: this.$parent.text_next
            }
        })])]);
    }
});

Vue.component('PagePreviewWizardForm',{
    data: function() {
        return {
            previewText: '',
            loading: 1,
            template: '',
            skip_section_templates: ['ccpa_free', 'dmca', 'coppa', 'terms_forced', 'gdpr_cookie_policy', 'gdpr_privacy_policy', 'cookies_policy', 'linking_policy', 'external_link_policy', 'blog_comments_policy', 'affiliate_disclosure', 'amazon_affiliate_disclosure' ,'testimonials_disclosure','advertising_disclosure', 'confidentiality_disclosure', 'general_disclaimer', 'earnings_disclaimer', 'medical_disclaimer', 'newsletters', 'antispam', 'ftc_statement', 'double_dart', 'about_us', 'cpra']
        }
    },
    methods: {
        handleSubmit: function() {
            $.post(wizard_obj.ajax_url,{'action':'page_preview_save','nonce':wizard_obj.ajax_nonce,'page':this.$root.page}).then((response => {
                if(response.success) {
                    window.location.href = response.url;
                }  else {
                    this.$router.push('page_preview');
                    this.$root.route.name = 'page_preview';
                    Vue.$toast.open( toastOptions );
                }
            }));
        },
        handlePrev: function() {
            if( this.skip_section_templates.indexOf(this.template) >= 0 ) {
                this.$router.push('page_settings');
                this.$root.route.name = 'page_settings';
            } else {
                this.$router.push('page_sections');
                this.$root.route.name = 'page_sections';
            }
        }
    },
    created: function(){
        this.$parent.hasError = !1;
        $.get(wizard_obj.ajax_url,{'action':'step_settings','nonce':wizard_obj.ajax_nonce,'step':'page_preview','page':this.$root.page}).then((response => {
            if(response.success) {
                this.template = response.page;
                this.previewText = response.data;
            }  else {
                if( this.skip_section_templates.indexOf(this.template) >= 0 ) {
                    this.$router.push('page_settings');
                    this.$root.route.name = 'page_settings';
                } else{
                    this.$router.push('page_sections');
                    this.$root.route.name = 'page_sections';
                }
                Vue.$toast.open( toastOptions );
            }
            this.loading = !1;
        }));
    },
    updated: function () {
        this.$nextTick(function () {
            this.loading = !1;
        })
    },
    render(createElement){
        var self = this;
        return this.loading ? createElement('loading') : createElement('form',{
            staticClass:'wplegal-wizard-preview-form',
            on: {
                submit: function(e) {
                    return e.preventDefault(),
                        self.handleSubmit(e);
                }
            }
        },[createElement('div',{
            staticClass:'wplegal-form-row'
        },[createElement('div', {
            staticClass: 'wplegal-page-preview',
            domProps: {
                innerHTML: self.previewText
            }
        })]), createElement('Separator'),createElement('div',{
            staticClass:'wplegal-form-row wplegal-form-buttons'
        },[createElement('button',{
            staticClass: "wplegal-wizard-button wplegal-wizard-button-prev wplegal-wizard-button-large",
            attrs: {
                type: "",
                name: "prev_step"
            },
            on: {
                click: function(e) {
                    return e.preventDefault(),
                        self.handlePrev(e);
                }
            },
            domProps: {
                textContent: this.$parent.text_prev
            }
        }), createElement('button',{
            staticClass: "wplegal-wizard-button wplegal-wizard-button-next wplegal-wizard-button-large",
            attrs: {
                type: "submit",
                name: "next_step"
            },
            domProps: {
                textContent: this.$parent.text_next
            }
        })])]);
    }
});

const StepGettingStarted = {
    data:function(){
        return {
            text_title:wizard_obj.welcome.title,
            text_subtitle:wizard_obj.welcome.subtitle,
            text_next:wizard_obj.welcome.next,
            input_title:wizard_obj.welcome.inputtitle,
            input_subtitle:''
        }
    },
    render(createElement){
        return createElement('div', {
            staticClass:'wplegal-wizard-step-getting-started'
        },[createElement('StepHeader'),createElement('div',{
            staticClass: 'wplegal-wizard-form'
        },[createElement('Separator'), createElement('GettingStartedWizardForm')])]);
    }
};

const StepPageSettings = {
    data:function(){
        return {
            text_title:wizard_obj.settings.title,
            text_subtitle:wizard_obj.settings.subtitle,
            text_next:wizard_obj.settings.next,
            text_prev:wizard_obj.settings.prev
        }
    },
    render(createElement){
        return createElement('div', {
            staticClass:'wplegal-wizard-step-page-settings'
        },[createElement('StepHeader'),createElement('div',{
            staticClass: 'wplegal-wizard-form'
        },[createElement('Separator'), createElement('PageSettingsWizardForm')])]);
    }
};

const StepPageSections = {

    data:function(){
        if ( 'custom_legal' === this.$root.page ) {
            wizard_obj.sections.subtitle = 'Enter the details for your policy template';
        }
        return {
            text_title:wizard_obj.sections.title,
            text_subtitle:wizard_obj.sections.subtitle,
            text_next:wizard_obj.sections.next,
            text_prev:wizard_obj.sections.prev
        }
    },
    render(createElement){
        return createElement('div', {
            staticClass:'wplegal-wizard-step-page-sections'
        },[createElement('StepHeader'),createElement('div',{
            staticClass: 'wplegal-wizard-form'
        },[createElement('Separator'), createElement('PageSectionsWizardForm')])]);
    },
};

const StepPagePreview = {
    data:function(){
        return {
            text_title:wizard_obj.preview.title,
            text_subtitle:wizard_obj.preview.subtitle,
            text_next:wizard_obj.preview.next,
            text_prev:wizard_obj.preview.prev
        }
    },
    render(createElement){
        return createElement('div', {
            staticClass:'wplegal-wizard-step-page-preview'
        },[createElement('StepHeader'),createElement('div',{
            staticClass: 'wplegal-wizard-form'
        },[createElement('Separator'), createElement('PagePreviewWizardForm')])]);
    }
};

const routes = [
    { path: '/', component: StepGettingStarted },
    { path: '/page_settings', component: StepPageSettings },
    { path: '/page_sections', component: StepPageSections },
    { path: '/page_preview', component: StepPagePreview },
];

const router = new VueRouter({
    routes // short for `routes: routes`
});

var app = new Vue({
    el: '#wplegalwizardapp',
    router,
    data: {
        wizardSteps : ['getting_started', 'page_settings','page_sections','page_preview'],
        route: {
            'name':'getting_started'
        },
        page: 'privacy_policy',
    },
    render(createElement) {
        return createElement('div',{
            staticClass:'wplegal-admin-page wizard'
        },[createElement('the-wizard-header'),createElement('div',{
            staticClass:'wplegal-wizard-container'
        },[createElement('div',{
            staticClass:'wplegal-wizard-content'
        },[createElement('the-wizard-timeline'),createElement('router-view')])])])
    }
});
