const is_user_connected = wplp_localize_data.is_user_connected;
const plan_name = wplp_localize_data.plan_name;

$ = jQuery.noConflict();
var localised_data = obj;

Vue.component("WelcomeSection", {
  render(createElement) {
    return createElement(
      "div",
      {
        staticClass: "wplegal-welcome-section",
      },
      [
        createElement(
          "div",
          {
            staticClass: "wplegal-section-title",
          },
          [
            createElement("img", {
              domProps: {
                src: obj.image_url + "WPLPCompliancePlatform.png",
                alt: "WP LegalPages compliance dashboard",
              },
            }),
            createElement("p", {
              staticClass: "wplegal-title-heading",
              domProps: {
                textContent: obj.welcome_text,
              },
            }),
            createElement("p", {
              staticClass: "wplegal-title-subheading",
              domProps: {
                textContent: obj.welcome_subtext,
              },
            }),
            createElement("p", {
              staticClass: "wplegal-section-content",
              domProps: {
                innerHTML: obj.welcome_description,
              },
            }),
          ]
        ),
        createElement(
          "div",
          {
            staticClass: "wplegal-video-section",
          },
          [
            createElement("iframe", {
              attrs: {
                width: "450",
                height: "260",
                src: obj.video_url,
                style: "border-radius: 20px; margin-top: 15px;",
              },
            }),
          ]
        ),
      ]
    );
  },
});

Vue.component("VideoSection", {
  render(createElement) {
    return createElement(
      "div",
      {
        staticClass: "wplegal-video-section",
      },
      [
        createElement("iframe", {
          attrs: {
            width: "746",
            height: "350",
            src: obj.video_url,
          },
        }),
      ]
    );
  },
});
//WP Cookie Consent installation quick section
Vue.component("install-gdpr", {
  render(createElement) {
    return createElement(
      "div",
      {
        staticClass: "wplegal-create-legal-container",
      },
      [
        createElement(
          "div",
          {
            staticClass: "wplegal-create-gdpr",
          },
          [
            createElement(
              "div",
              {
                staticClass: "wplegal-feature-icon",
              },
              [
                createElement("img", {
                  attrs: {
                    src: obj.image_url + "cookie-img.svg",
                    alt: "Cookie Consent Icon",
                  },
                }),
                createElement(
                  "div",
                  {
                    staticClass: "wplegal-create-gdpr-subtext",
                  },
                  [
                    createElement("p", {
                      staticClass: "wplegal-create-gdpr-subheading",
                      domProps: {
                        textContent: obj.install_gdpr_text,
                      },
                    }),
                    createElement("p", {
                      staticClass: "wplegal-create-gdpr-content",
                      domProps: {
                        innerHTML: obj.install_gdpr_subtext,
                      },
                    }),
                  ]
                ),
              ]
            ),
            createElement(
              "div",
              {
                staticClass: "wplegal-create-gdpr-link",
              },
              [
                createElement(
                  "a",
                  {
                    staticClass: "install-gdpr-button",
                    attrs: {
                      href: obj.create_legal_url,
                    },
                  },
                  [
                    createElement("span", {
                      domProps: {
                        textContent: obj.install_gdpr_btn,
                      },
                    }),
                    createElement("img", {
                      attrs: {
                        src: obj.image_url + "down-arrow.png",
                        alt: "Download Arrow Icon",
                      },
                    }),
                  ]
                ),
              ]
            ),
          ]
        ),
      ]
    );
  },
});
Vue.component("create-gdpr", {
  render(createElement) {
    return createElement(
      "div",
      {
        staticClass: "wplegal-create-legal-container",
      },
      [
        createElement(
          "div",
          {
            staticClass: "wplegal-create-gdpr",
          },
          [
            createElement(
              "div",
              {
                staticClass: "wplegal-feature-icon",
              },
              [
                createElement("img", {
                  attrs: {
                    src: obj.image_url + "cookie-img.svg",
                    alt: "Cookie Consent Icon",
                  },
                }),
                createElement(
                  "div",
                  {
                    staticClass: "wplegal-create-gdpr-subtext",
                  },
                  [
                    createElement("p", {
                      staticClass: "wplegal-create-gdpr-subheading",
                      domProps: {
                        textContent: obj.create_gdpr,
                      },
                    }),
                    createElement("p", {
                      staticClass: "wplegal-create-gdpr-content",
                      domProps: {
                        innerHTML: obj.create_gdpr_subtext,
                      },
                    }),
                  ]
                ),
              ]
            ),
            createElement(
              "div",
              {
                staticClass: "wplegal-create-gdpr-link",
              },
              [
                createElement(
                  "a",
                  {
                    staticClass: "create-gdpr-button",
                    attrs: {
                      href: obj.create_gdpr_url,
                    },
                  },
                  [
                    createElement("span", {
                      domProps: {
                        textContent: obj.gdpr_link_title,
                      },
                    }),
                    createElement("img", {
                      attrs: {
                        src: obj.image_url + "configure-btn.png",
                        alt: "Configure Button Icon",
                      },
                    }),
                  ]
                ),
              ]
            ),
          ]
        ),
      ]
    );
  },
});

Vue.component("CreateLegal", {
  render(createElement) {
    return createElement(
      "div",
      {
        staticClass: "wplegal-create-legal-container",
      },
      [
        createElement(
          "div",
          {
            staticClass: "wplegal-create-legal",
          },
          [
            createElement(
              "div",
              {
                staticClass: "wplegal-feature-icon",
              },
              [
                createElement("img", {
                  attrs: {
                    src: obj.image_url + "wplp-icon.png",
                    alt: "WP Legal Pages Icon",
                  },
                }),
                createElement(
                  "div",
                  {
                    staticClass: "wplegal-create-legal-subtext",
                  },
                  [
                    createElement("p", {
                      staticClass: "wplegal-create-legal-subheading",
                      domProps: {
                        textContent: obj.create_legal,
                      },
                    }),
                    createElement("p", {
                      staticClass: "wplegal-create-legal-content",
                      domProps: {
                        innerHTML: obj.create_legal_subtext,
                      },
                    }),
                  ]
                ),
              ]
            ),
            createElement(
              "div",
              {
                staticClass: "wplegal-create-legal-link",
              },
              [
                createElement(
                  "a",
                  {
                    staticClass: "wplegal-create-legal-button",
                    attrs: {
                      href: obj.create_legal_url,
                    },
                  },
                  [
                    createElement("span", {
                      domProps: {
                        textContent: obj.link_title,
                      },
                    }),
                    createElement("img", {
                      attrs: {
                        src: obj.image_url + "right_arrow.svg",
                        alt: "Right Arrow Icon",
                      },
                    }),
                  ]
                ),
              ]
            ),
          ]
        ),
      ]
    );
  },
});

Vue.component("QuickLinks", {
  methods: {
    createHelpCards: function (createElement) {
      var helpCards = [];
      for (const [key, value] of Object.entries(obj.quick_link)) {
        var helpCard = [
          createElement(
            "div",
            {
              staticClass: "wplegal-quick-link",
            },
            [
              createElement(
                "div",
                {
                  staticClass: "wplegal-quick-link-card",
                },
                [
                  createElement("img", {
                    staticClass: "wplegal-quick-link-icon",
                    attrs: {
                      src: value.image_src,
                      alt: value.alt_text,
                    },
                  }),
                  createElement(
                    "div",
                    {
                      staticClass: "wplegal-quick-link-content",
                    },
                    [
                      createElement("p", {
                        staticClass: "wplegal-quick-link-heading",
                        domProps: {
                          textContent: value.title,
                        },
                      }),
                      createElement("p", {
                        staticClass: "wplegal-quick-link-subheading",
                        domProps: {
                          innerHTML: value.description,
                        },
                      }),
                      createElement(
                        "a",
                        {
                          staticClass: "wplegal-quick-link-button",
                          attrs: {
                            href: value.link,
                            target:
                              value.title !== "Settings" ? "_blank" : "_self",
                          },
                          on: {
                            click: function (e) {
                              if (value.title == "Settings") {
                                window.location.assign(value.link);
                                location.reload();
                              }
                            },
                          },
                        },
                        [
                          createElement("span", {
                            domProps: {
                              textContent: value.link_name,
                            },
                          }),
                          createElement("img", {
                            attrs: {
                              src: obj.image_url + "blue_right_arrow.svg",
                              alt: "Blue Right Arrow Icon",
                            },
                          }),
                        ]
                      ),
                    ]
                  ),
                ]
              ),
            ]
          ),
        ];
        helpCards.push(helpCard);
      }
      return helpCards;
    },
  },
  render(createElement) {
    var self = this;
    return createElement(
      "div",
      {
        staticClass: "wplegal-quick-links-section",
      },
      [
        createElement("div", {
          staticClass: "wplegal-quick-links-section-heading",
          domProps: {
            textContent: "Quick Links ",
          },
        }),
        createElement(
          "div",
          {
            staticClass: "wplegal-quick-links",
          },
          [self.createHelpCards(createElement)]
        ),
      ]
    );
  },
});

Vue.component("Feature", {
  methods: {
    createHelpCards: function (createElement) {
      var helpCards = [];
      for (const [key, value] of Object.entries(obj.new_feature)) {
        var helpCard = [
          createElement(
            "div",
            {
              // staticClass: 'wplegal-feature-card'
            },
            [
              createElement(
                "div",
                {
                  staticClass: "wplegal-feature-card",
                },
                [
                  createElement("img", {
                    staticClass: "wplegal-feature-card-icon",
                    attrs: {
                      src: value.image_src,
                      alt: value.alt_text,
                    },
                  }),
                  createElement(
                    "div",
                    {
                      staticClass: "wplegal-feature-content",
                    },
                    [
                      createElement("p", {
                        staticClass: "wplegal-feature-heading",
                        domProps: {
                          textContent: value.title,
                        },
                      }),
                      createElement("p", {
                        staticClass: "wplegal-feature-subheading",
                        domProps: {
                          innerHTML: value.description,
                        },
                      }),
                    ]
                  ),
                ]
              ),
            ]
          ),
        ];
        helpCards.push(helpCard);
      }
      return helpCards;
    },
  },
  render(createElement) {
    var self = this;
    return createElement(
      "div",
      {
        staticClass: "wplegal-feature-section",
      },
      [
        !(is_user_connected && plan_name !== "free") &&
          createElement(
            "div",
            {
              staticClass: "wplegal-feature-section-header",
            },
            [
              createElement("img", {
                staticClass: "wplegal-feature-img-icon",
                domProps: {
                  src: obj.image_url + "pro_icon.svg",
                  alt: "WPLegalPages featured Image Icon"
                },
              }),
              createElement("p", {
                staticClass: "wplegal-feature-heading",
                domProps: {
                  textContent: obj.feature_heading,
                },
              }),
              createElement("p", {
                staticClass: "wplegal-feature-subheading",
                domProps: {
                  textContent: obj.feature_description,
                },
              }),
              createElement(
                "a",
                {
                  staticClass: "wplegal-feature-header-link-button",
                  attrs: {
                    href:
                      "https://wplegalpages.com/pricing/?utm_source=wplegalpagesplugin&utm_medium=upgrade&utm_campaign=button",
                    target: "_blank",
                  },
                },
                [
                  createElement("span", {
                    domProps: {
                      textContent: obj.feature_button,
                    },
                  }),
                  createElement("img", {
                    attrs: {
                      src: obj.image_url + "blue_right_arrow.svg",
                      alt: "Blue Right Arrow Icon",
                    },
                  }),
                ]
              ),
            ]
          ),
        createElement(
          "div",
          {
            staticClass: "wplegal-feature-cards",
          },
          [self.createHelpCards(createElement)]
        ),
      ]
    );
  },
});

Vue.component("TermsSection", {
  data: function () {
    return {
      accept_terms: {
        checked: !1,
      },
      initialized: false,
    };
  },
  methods: {
    labelClass: function () {
      var s = "";
      if (this.isChecked()) {
        s += "wplegal-styled-checkbox-label-checked";
      }
      if (this.isDisabled()) {
        s += " wplegal-styled-checkbox-label-disabled";
      }
      return s;
    },
    titleClass: function () {
      var s = "wplegal-styled-checkbox";
      if (this.isChecked()) {
        s += " wplegal-styled-checkbox-checked";
      }
      return s;
    },
    isDisabled: function () {
      if (this.$parent.disabled) {
        return true;
      }
      return false;
    },
    isChecked: function () {
      if (this.accept_terms.checked) {
        return true;
      }
      return false;
    },
    updateSettings: function () {
      this.accept_terms.checked = !this.accept_terms.checked;
    },
    handleSubmit: function () {
      var data = {};
      jQuery('.wplegal-input-checkbox input[type="checkbox"]').each(
        function () {
          if (this.checked) {
            data[this.name] = this.value;
          }
        }
      );

      jQuery
        .post(localised_data.ajax_url, {
          action: "save_accept_terms",
          data: data,
          nonce: localised_data.ajax_nonce,
        })
        .then((response) => {
          if (response.success) {
            this.$parent.disabled = 1;
            window.location.href = "admin.php?page=wplp-dashboard";
          }
        });
    },
  },
  mounted: function () {  
    jQuery
      .get(obj.ajax_url, { action: "get_accept_terms", nonce: obj.ajax_nonce })
      .then((response) => {
        if (response.success) {
          if (response.data == "1") {
            this.$parent.disabled = 1;
            this.accept_terms.checked = 1;
          }
        }
        this.initialized = true;
        // console.log(this.initialized);
      });
  },
  render(createElement) {
    var self = this;
    if (!this.initialized) {
      return null;
    }
    return this.$parent.disabled
      ? []
      : createElement(
          "div",
          {
            staticClass: "wplegal-terms-container",
            style: this.$parent.disabled ? "display:none" : "display:block",
          },
          [
            createElement("div", {
              staticClass: this.$parent.disabled
                ? ""
                : "wplegal-terms-card-overlay",
            }),
            createElement(
              "div",
              {
                staticClass: "wplegal-terms-section-content",
              },
              [
                createElement("img", {
                  staticClass: "wplegal-terms-card-icon",
                  attrs: {
                    src: localised_data.terms.image,
                  },
                }),
                createElement("span", {
                  staticClass: "wplegal-terms-section-heading",
                  domProps: {
                    innerHTML: localised_data.terms.heading,
                  },
                }),
                createElement("p", {
                  staticClass: "wplegal-terms-section-text",
                  domProps: {
                    innerHTML: localised_data.terms.text,
                  },
                }),
                createElement("p", {
                  staticClass: "wplegal-terms-section-subtext",
                  domProps: {
                    textContent: localised_data.terms.subtext,
                  },
                }),
                createElement(
                  "form",
                  {
                    staticClass: "wplegal-input-checkbox",
                    on: {
                      submit: function (e) {
                        return e.preventDefault(), self.handleSubmit(e);
                      },
                    },
                  },
                  [
                    createElement(
                      "label",
                      {
                        class: this.labelClass(),
                      },
                      [
                        createElement("span", {
                          class: this.titleClass(),
                        }),
                        createElement("input", {
                          attrs: {
                            type: "checkbox",
                            name: "lp_accept_terms",
                          },
                          domProps: {
                            value: 1,
                            checked: this.isChecked(),
                          },
                          staticClass: "wplegal-terms-input-checkbox",
                          on: {
                            change: function (event) {
                              event.preventDefault();
                              event.target.checked = !event.target.checked;
                              self.updateSettings();
                            },
                          },
                        }),
                        createElement("span", {
                          staticClass: "wplegal-terms-input-checkbox-text",
                          domProps: {
                            innerHTML: localised_data.terms.input_text,
                          },
                        }),
                      ]
                    ),
                    this.$parent.disabled
                      ? []
                      : createElement("button", {
                          staticClass: this.isChecked()
                            ? "wplegal-accept-button"
                            : "wplegal-disable-accept-button",
                          attrs: {
                            type: "submit",
                            name: "accept_terms",
                          },
                          domProps: {
                            textContent: localised_data.terms.button_text,
                          },
                        }),
                  ]
                ),
              ]
            ),
          ]
        );
  },
});

var app = new Vue({
  el: "#gettingstartedapp",
  data: {
    is_pro: obj.is_pro,
    disabled: !1,
    gdpr_installed: obj.gdpr_installed,
    is_gdpr_active: obj.is_gdpr_active,
  },
  mounted() {
    // Hide loader
    const loader = document.getElementById("wplegal-loader");
    if (loader) {
      loader.style.display = "none";
    }
  },
  render(createElement) {
    return createElement(
      "div",
      {
        staticClass: "wplegal-container",
      },
      [
        createElement("header-section"),
        createElement(
          "div",
          {
            staticClass: "wplegal-container-main",
          },
          [
            createElement("welcome-section"),
            this.gdpr_installed == 1 && this.is_gdpr_active != ""
              ? createElement("create-gdpr")
              : createElement("install-gdpr"),
            createElement("create-legal"),
            createElement("quick-links"),
            createElement("feature"),
            createElement("help-section"),
            createElement("terms-section"),
            this.disabled && !this.is_pro
              ? createElement("settings-section")
              : [],
            this.disabled && !this.is_pro ? createElement("pages-section") : [],
          ]
        ),
        createElement(
          "div",
          {
            staticClass: "wplegal-container-features",
          },
          [
            this.is_pro
              ? this.disabled
                ? createElement("wizard-section")
                : []
              : createElement("features-section"),
          ]
        ),
      ]
    );
  },
});