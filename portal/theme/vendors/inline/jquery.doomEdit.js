/**
* Doom EditInPlace jQuery Plugin
*
* @author Dumitru Glavan
* @link http://dumitruglavan.com/jquery-doom-inplace-edit-plugin/
* @version 2.2 (2-MAR-2012)
* @requires jQuery v1.4 or later
*
* @example $('.dedit-simple').doomEdit({ajaxSubmit:false, afterFormSubmit: function (data, form, el) {el.text(data);}}); - Simple inline edit
* @example $('.dedit-textarea').doomEdit({ajaxSubmit:false, editField: '<textarea name="myEditTextarea" rows="10" cols="70"></textarea>', afterFormSubmit: function (data, form, el) {el.text(data);}}); - Inline edit with textarea
* @example $('.dedit-remote').doomEdit({editForm:{method:'post', action:'remote.html', id:'myeditformid'}, afterFormSubmit: function (data, form, el) {el.text($('input', form).val());alert(data);}}); - Inline edit and remote save with ajax
* @example $('.dedit-remote-json').doomEdit({editForm:{method:'post', action:'remote_json.html', id:'myeditformid'}, afterFormSubmit: function (data, form, el) {data = $.parseJSON(data);el.text(data.message);alert(data.message);}}); - Inline edit and remote save with ajax with JSON response
*
* Examples and documentation at: http://dumitruglavan.com/jquery-doom-inplace-edit-plugin/
* Official jQuery plugin page: http://plugins.jquery.com/project/doom-inplace-edit
* Find source on GitHub: https://github.com/doomhz/jQuery-Inplace-Edit
*
* Dual licensed under the MIT and GPL licenses:
*   http://www.opensource.org/licenses/mit-license.php
*   http://www.gnu.org/licenses/gpl.html
*
*/
(function ($) {
  $.fn.doomEdit = function (options) {

    if ($(this).length > 1) {
      $(this).each(function (i, el) {
        $(el).doomEdit(options);
      });
      return $(this);
    }

    this.config = {
      editForm: {
        method:'post',
        action:'/',
        id:'doomEditForm'
      },
      ajaxSubmit: true,
      editField: '<input name="{editFieldName}" type="text" />',
      editFieldName: 'doomEditElement',
      submitBtn: '<button type="submit" class="save-btn btn-success">Save</button>',
      cancelBtn: '<button type="button" class="cancel-btn btn-info">Cancel</button>',
      autoDisableBt: true,
      placeholder: true,
      extraHtml: '',
      htmlFilter: false,
      showOnEvent: 'click',
      autoTrigger: false,
      submitOnBlur: false,
      afterFormSubmit: function (data, form, el) {
        $('button', form).removeAttr('disabled').fadeTo(0, 1);
      },
      beforeFormSubmit: function (data, form, el) {
        $('button', form).attr('disabled', true).fadeTo(0, 0.2);
      },
      onFormError: function (xhr) {
        console.log(xhr.responseText);
      },
      onCancel: null,
      onStartEdit: null
    };
    $.extend(this.config, options);

    var self = this;
    var $self = $(this);
    this.showPlaceholder = function () {
        if (self.config.placeholder && $self.data('placeholder') && ($self.html() === '')) {
            $self.text($self.data('placeholder'));
        }
    };
    this.hidePlaceholder = function ($inputEl) {
        if (self.config.placeholder && ($self.data('placeholder') === $inputEl.val())) {
            $inputEl.val('');
        }
    };

    this.showPlaceholder();

    if (this.config.showOnEvent) {
      $self.unbind(this.config.showOnEvent);
      $self.bind(this.config.showOnEvent, function () {
        $self.showEditForm.call(self);
      });
    }
    if (this.config.autoTrigger) {
      $self.showEditForm.call(self);
    }

    return this;
  };

  $.fn.showEditForm = function () {
    var self = this;
    var $self = $(this);

    self.initialValue = $.isFunction(self.config.htmlFilter) ? self.config.htmlFilter($self.html()) : $self.text();
    var editForm = $('<form>' + self.config.extraHtml + '</form>').attr(self.config.editForm);
    var editFieldName = $self.data("field-name") || self.config.editFieldName;
    var editElement = $(self.config.editField.replace(/\{editFieldName\}/g, editFieldName)).addClass('text');
    var editElementTagName = editElement.get(0).tagName;

    editElement.val(self.initialValue);

    editForm.append(editElement);
    var submitButton = $(self.config.submitBtn).attr({disabled:self.config.autoDisableBt}).addClass('button');
    var cancelButton = $(self.config.cancelBtn).addClass('button inactive');
    cancelButton.click(function () {
      editForm.remove();
      self.showPlaceholder();
      $self.show();
      $.isFunction(self.config.onCancel) && self.config.onCancel(editForm, $self);
    });
    if (self.config.autoDisableBt) {
      var eventType = $.inArray(editElementTagName, ['INPUT', 'TEXTAREA']) > -1 ? 'change' : 'change';
	  //keyup
      editElement.bind(eventType, function () {
        var value = editElement.val();
        if (value === '' || value === self.initialValue) {
          submitButton.attr('disabled', true);
        } else {
          submitButton.attr('disabled', false);
        }
      });
    }
    editForm.append(submitButton);
    editForm.append(cancelButton);
    editForm.submit(function () {
      var newVal = editElement.val() || editElement.text();
      if (self.config.ajaxSubmit) {
        $.ajax({
          url: editForm.attr('action'),
          type: editForm.attr('method'),
          data: editForm.serialize(),
          beforeSend: function(xhr, settings) {
            return $.isFunction(self.config.beforeFormSubmit) && self.config.beforeFormSubmit(xhr, editForm, $self, settings);
          },
          success: function(data) {
            $.isFunction(self.config.afterFormSubmit) && self.config.afterFormSubmit(data, editForm, $self, newVal);
            editForm.remove();
            self.showPlaceholder();
            $(self).show();
          },
          error: function (xhr) {
            $.isFunction(self.config.onFormError) && self.config.onFormError(xhr);
          }
        });
      } else {
        $.isFunction(self.config.afterFormSubmit) && self.config.afterFormSubmit(newVal, editForm, $self);
        editForm.remove();
        self.showPlaceholder();
        $(self).show();
      }
      return false;
    });

    $self.hide().after(editForm);
    editElement.focus();

    if (self.config.submitOnBlur) {
      editElement.blur(function () {
        if (document.activeElement != editElement[0])
          editForm.submit();
      });
    }

    $.isFunction(self.config.onStartEdit) && self.config.onStartEdit(editForm, $self);

    self.hidePlaceholder(editElement);
  };
})(jQuery);