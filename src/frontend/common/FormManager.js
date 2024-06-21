import { EventManager } from './EventManager';
import { addQueryArgs } from '@wordpress/url';

export class FormManager extends EventManager {

  getFormFields(form) {
    return form.querySelectorAll('[data-anspress-field]');
  }

  appendError(field, errors) {
    let errorElement = field.parentElement.querySelector('.anspress-form-error');
    if (!errorElement) {
      field?.classList.add('anspress-form-has-error')
      errorElement = document.createElement('div');
      errorElement.classList.add('anspress-form-error');
      field.appendChild(errorElement);
    }
    errorElement.innerText = errors;
  }

  clearErrors(form) {
    form.querySelectorAll('.anspress-form-has-error').forEach(errorElement => {
      errorElement.classList.remove('anspress-form-has-error');
    });
    form.querySelectorAll('.anspress-form-error').forEach(errorElement => {
      errorElement.remove();
    });
  }

  async submitForm(e, form) {
    this.clearErrors(form);

    const formData = new FormData(form);

    const data = Object.fromEntries(formData.entries());

    try {
      const response = await this.fetch({
        path: this.data.form_action,
        method: 'POST',
        data: data
      });

      this.handleSuccess(response, form);

      tinymce.remove();
    } catch (error) {
      console.error('Form submission error:', error);
      this.handleErrors(error.errors, form);
    }
  }

  handleSuccess(response, form) {
    form.reset();
  }

  handleErrors(errors, form) {
    Object.keys(errors).forEach((field) => {
      // If error field is * then show error on top of form.
      if (field === '*') {
        const errorElement = form.querySelector('.anspress-form-error');
        if (errorElement) {
          errorElement.remove();
        }

        {
          const errorElement = document.createElement('div');
          errorElement.classList.add('anspress-form-error', 'anspress-form-global-error');
          errorElement.innerHTML = errors[field];
          form.prepend(errorElement);
        }

        return;
      }

      const fieldErrors = errors[field] ?? [];
      const fieldEl = form.querySelector(`[data-anspress-field="${field}"]`);
      if (fieldEl) {
        this.appendError(fieldEl, fieldErrors);
      } else {
        console.error(`Field not found for error: ${field}`);
      }
    });
  }

  isFormLoaded() {
    return this.data?.formLoaded;
  }

  loadForm() {
    this.fetch({
      path: addQueryArgs(this.data.load_form_path, { form_loaded: true }),
      method: 'GET',
    })
      .then(res => {
        if (res?.load_easymde) {
          tinymce.init({
            selector: '#' + res?.load_easymde,
            toolbar: 'bold italic | underline strikethrough | bullist numlist | link unlink blockquote hr| image removeformat',
            menubar: false,
            statusbar: false,
            plugins: 'lists link hr wpautoresize image',
            min_height: 200,
            wp_autoresize_on: true,
            images_file_types: 'jpg,svg,webp',
            file_picker_types: 'file image media',
            automatic_uploads: true,
            images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
            }),
            setup: function (editor) {
              editor.on('change', function () {
                editor.save();
              });
            }
          });
        }
      });
  }

  closeForm() {
    this.fetch({
      path: addQueryArgs(this.data.load_form_path, { form_loaded: false }),
      method: 'GET',
    })
  }

  imageUploadHandler() {

  }
}
