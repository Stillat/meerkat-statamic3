/**
 * Provides a simple way to implement automatic reply forms in Statamic Meerkat templates.
 *
 * Can be automatically loaded using the {{ meerkat:replies-to }} Antlers tag.
 */
(function () {
  let MeerkatReply = {
    Endpoints: Object.freeze({
      SubmitComment: '/!/Meerkat/socialize'
    }),
    closeOnCancel: true,
    replyOpen: null,
    canceled: null,
    submit: function (event) {

    },
    getOpenReplyForm: function () {
      let forms = document.querySelectorAll('form[data-meerkat-form="comment-reply-form"]');

      return forms[forms.length - 1];
    }
  };
  const MeerkatForms = {
    data: {
      ReplyForm: null,
      Extend: null,
      IsHCaptchaInUse: false,
      IsGoogleRecaptchaInUse: false,
      CaptchaElementId: null,
      GoogleRecaptchaInstance: null,
      HCaptchaInstance: null,
      GoogleRecaptchaTheme: null,
      GoogleRecaptchaSiteKey: null,
      HCaptchaSiteKey: null
    },
    findClosest: function (el, selector) {
      let matchesFn;

      [
        'matches', 'webkitMatchesSelector', 'mozMatchesSelector',
        'msMatchesSelector', 'oMatchesSelector']
        .some(function (fn) {
          if (typeof document.body[fn] === 'function') {
            matchesFn = fn;
            return true;
          }
          return false;
        });

      let parent;

      while (el) {
        parent = el.parentElement;
        if (parent && parent[matchesFn](selector)) {
          return parent;
        }
        el = parent;
      }

      return null;
    },
    generateId: function () {
      return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        let r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);

        return v.toString(16);
      });
    },
    findElementWithClass: function (node, classRegex) {
      let matches = [];

      function traverse(childNode) {
        for (let i = 0; i < childNode.childNodes.length; i++) {
          if (childNode.childNodes[i].getAttribute && childNode.childNodes[i].getAttribute('class')) {
            if (childNode.childNodes[i].getAttribute('class').match(classRegex)) {
              matches.push(childNode.childNodes[i]);
            }
          }

          if (childNode.childNodes[i].childNodes.length > 0) {
            traverse(childNode.childNodes[i]);
          }
        }
      }

      traverse(node);

      return matches;
    },
    getReplyForm: function () {
      let form = document.querySelectorAll('[data-meerkat-form="comment-reply-form"]');

      if (form.length === 0) {
        form = document.querySelectorAll('[data-meerkat-form="comment-form"]');
      }

      if (form.length > 0) {
        let meerkatReplyForm = form[0].cloneNode(true);

        if (meerkatReplyForm.innerHTML.indexOf('h-captcha') > -1) {
          this.data.IsHCaptchaInUse = true;
          this.data.IsGoogleRecaptchaInUse = false;

          let captchaElements = this.findElementWithClass(meerkatReplyForm, '\s*h-captcha\s*');

          if (typeof captchaElements !== 'undefined' && captchaElements.length > 0) {
            let captchaEle = captchaElements[0];

            this.data.CaptchaElementId = 'meerkat_c-' + this.generateId();
            captchaEle.setAttribute('id', this.data.CaptchaElementId);

            if (typeof  captchaEle.dataset !== 'undefined') {
              let captchaDataSet = captchaEle.dataset;

              this.data.HCaptchaSiteKey = captchaDataSet.sitekey;
            }
          }
        } else if (meerkatReplyForm.innerHTML.indexOf('g-recaptcha') > -1) {
          if (typeof window['grecaptcha'] !== 'undefined') {
            this.data.IsGoogleRecaptchaInUse = true;
            this.data.IsHCaptchaInUse = false;

            let captchaElements = this.findElementWithClass(meerkatReplyForm, '\s*g-recaptcha\s*');

            if (typeof captchaElements !== 'undefined' && captchaElements.length > 0) {
              let captchaEle = captchaElements[0];

              this.data.CaptchaElementId = 'meerkat_c-' + this.generateId();
              captchaEle.setAttribute('id', this.data.CaptchaElementId);

              if (typeof captchaEle.dataset !== 'undefined') {
                let captchaDataSet = captchaEle.dataset;

                if (typeof captchaDataSet.sitekey !== 'undefined') {
                  this.data.GoogleRecaptchaSiteKey = captchaDataSet.sitekey;
                }

                if (typeof captchaDataSet.theme !== 'undefined') {
                  this.data.GoogleRecaptchaTheme = captchaDataSet.theme;
                } else {
                  this.data.GoogleRecaptchaTheme = 'light';
                }
              }
            }
          }
        }

        form = meerkatReplyForm;
      }

      return form;
    },
    makeReplyInput: function (replyingTo) {
      let replyInput = document.createElement('input');

      replyInput.type = 'hidden';
      replyInput.value = replyingTo;
      replyInput.name = 'ids';

      return replyInput;
    },
    addEventListeners: function () {
      let _this = this,
        replyLinks = document.querySelectorAll('[data-meerkat-form="reply"]');

      replyLinks.forEach(function (el) {
        el.addEventListener('click', function (event) {

          if (_this.data.ReplyForm !== null && _this.data.ReplyForm.parentNode != null) {
            _this.data.ReplyForm.parentNode.removeChild(_this.data.ReplyForm);
          }

          _this.data.ReplyForm = _this.getReplyForm();

          let replyingTo = event.target.getAttribute('data-meerkat-reply-to');

          _this.data.ReplyForm.appendChild(_this.makeReplyInput(replyingTo));
          _this.data.ReplyForm.addEventListener('submit', _this.data.Extend.submit, false);

          if (typeof MeerkatForms.data.Extend.replyOpen !== 'undefined' &&
            MeerkatForms.data.Extend.replyOpen !== null) {
            MeerkatForms.data.Extend.replyOpen(_this.data.ReplyForm);
          }

          el.parentNode.insertBefore(_this.data.ReplyForm, el.nextSibling);

          if (_this.data.IsGoogleRecaptchaInUse && _this.data.CaptchaElementId !== null) {
            if (_this.data.GoogleRecaptchaTheme !== null && _this.data.GoogleRecaptchaSiteKey !== null) {
              window.setTimeout(function () {
                let captchaElement = window.document.getElementById(_this.data.CaptchaElementId);

                captchaElement.innerHTML = '';

                try {
                  _this.data.GoogleRecaptchaInstance = window.grecaptcha.render(_this.data.CaptchaElementId, {
                    'sitekey': _this.data.GoogleRecaptchaSiteKey,
                    'theme': _this.data.GoogleRecaptchaTheme
                  });
                } catch (err) {
                }
              }, 250);
            }
          }

          if (_this.data.IsHCaptchaInUse === true && _this.data.CaptchaElementId !== null) {
            if (_this.data.HCaptchaSiteKey !== null) {
              window.setTimeout(function () {
                let captchaElement = window.document.getElementById(_this.data.CaptchaElementId);

                captchaElement.innerHTML = '';

                try {
                  _this.data.HCaptchaInstance = window.hcaptcha.render(_this.data.CaptchaElementId, {
                    'sitekey': _this.data.HCaptchaSiteKey
                  });
                } catch (err) {
                }
              }, 250);
            }
          }

          _this.addCancelReplyListeners();
          event.preventDefault();
        });
      });
    },
    replyHandler: function (event) {
      let meerkatForm = MeerkatForms.findClosest(event.target, '[data-meerkat-form]');

      if (typeof meerkatForm !== 'undefined' && meerkatForm !== null) {

        var replyingTo = meerkatForm.querySelectorAll('[name=ids]')[0].value;

        if (typeof MeerkatForms.data.Extend.canceled !== 'undefined' && MeerkatForms.data.Extend.canceled !== null) {
          MeerkatForms.data.Extend.canceled(replyingTo, meerkatForm);
        }

        if (MeerkatForms.data.Extend.closeOnCancel) {
          this.removeEventListener('click', MeerkatForms.replyHandler);
          meerkatForm.remove();
        }
      }

      event.preventDefault();
    },
    addCancelReplyListeners: function () {
      let _this = this,
        cancelLinks = document.querySelectorAll('[data-meerkat-form="cancel-reply"]');

      cancelLinks.forEach(function (el) {
        el.addEventListener('click', _this.replyHandler);
      });
    },
    init: function () {
      this.data.Extend = MeerkatReply;
      this.getReplyForm();
      this.addEventListeners();
      window.MeerkatReply = this.data.Extend;
    }
  };

  document.addEventListener('DOMContentLoaded', function () {
    MeerkatForms.init();
  });
})();
