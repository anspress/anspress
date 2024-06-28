import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

export const appendFormError = (field, errors) => {
  let errorElement = field.parentElement.querySelector('.anspress-form-error');
  if (!errorElement) {
    field?.classList.add('anspress-form-has-error')
    errorElement = document.createElement('div');
    errorElement.classList.add('anspress-form-error');
    field.appendChild(errorElement);
  }
  errorElement.innerText = errors;
}

export const clearFormErrors = (form) => {
  form.querySelectorAll('.anspress-form-has-error').forEach(errorElement => {
    errorElement.classList.remove('anspress-form-has-error');
  });
  form.querySelectorAll('.anspress-form-error').forEach(errorElement => {
    errorElement.remove();
  });
}

export const handleFormErrors = async (errors, form) => {
  if (!errors) {
    return;
  }
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
      appendFormError(fieldEl, fieldErrors);
    } else {
      console.error(`Field not found for error: ${field}`);
    }
  });
}

export const dispatchSnackbar = (message, type = 'success', duration = 5000) => {
  const event = new CustomEvent('anspress-snackbar', {
    detail: { message, type, duration }
  });

  document.body.dispatchEvent(event);
}

export const scrollToElement = (element) => {
  if (element) {
    setTimeout(() => {
      element.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
    }, 100);
  } else {
    console.error(`Element with selector '${selector}' not found.`);
  }
}

export const fetch = (options) => {
  return apiFetch(options)
    .then(res => {
      const data = res?.anspress || {}
      if (data.errors && Array.isArray(data.errors) && data.errors.length) {
        data.errors.map(snackbarItem => this.dispatchSnackbar(snackbarItem.message, 'error'));
      }

      if (data?.setData) {
        Object.keys(data?.setData).forEach(key => {
          const element = document.querySelector('[data-anspress-id="' + key + '"]');

          if (element && element?.data) {
            element.data = data?.setData[key];
          }
        });
      }

      // Handle replaceHtml.
      if (data?.replaceHtml) {
        Object.keys(data?.replaceHtml).forEach(key => {
          const replaceEl = document.querySelector(key);

          if (replaceEl) {
            replaceHTML(key, data?.replaceHtml[key]);
          } else {
            console.error(`Element with selector '${key}' not found.`);
          }
        });
      }

      // Handle trigger event.
      if (data?.triggerEvents) {
        Object.keys(data?.triggerEvents).forEach(key => {
          if (key === 'scrollTo' && data?.triggerEvents[key]?.element) {
            const element = document.querySelector(data?.triggerEvents[key]?.element);
            scrollToElement(element)
          } else if ('appendTo' === key && data?.triggerEvents[key]?.selector && data?.triggerEvents[key]?.html) {
            const element = document.querySelector(data?.triggerEvents[key]?.selector);

            if (element) {
              element.insertAdjacentHTML(data?.triggerEvents[key]?.position || 'beforeend', data?.triggerEvents[key]?.html);
            } else {
              console.error(`Element with selector '${data?.triggerEvents[key]?.element}' not found.`);
            }

          } else {
            document.dispatchEvent(new CustomEvent(key, { detail: data?.triggerEvents[key] }));
          }
        });
      }

      // Handle dispatch snackbar.
      if (data?.messages && Array.isArray(data?.messages)) {
        data?.messages.map(snackbarItem => dispatchSnackbar(snackbarItem.message, snackbarItem.type));
      }

      // Handle reload.
      if (data?.reload) {
        location.reload();
      }

      // Handle redirect.
      if (data?.redirect) {
        location.href = data?.redirect;
      }

      return data;
    })
    .catch(err => {
      console.error(err)

      const errorData = err?.anspress?.errors || err?.anspress?.message || err?.errors || err?.message || {};

      if (err.errors && Array.isArray(err.errors) && err.errors.length) {
        err.errors.map(snackbarItem => dispatchSnackbar(snackbarItem, 'error'));
      } else if (
        err.message &&
        typeof err.message === 'string' &&
        err.message.length
      ) {
        dispatchSnackbar(err.message, 'error');
      } else if (typeof errorData === 'string') {
        dispatchSnackbar(errorData, 'error');
      }

      throw err;
    });
}

export const replaceHTML = (selector, newHTML) => {
  // Select the element based on the provided selector
  const element = document.querySelector(selector);

  if (element) {
    // Create a new element from the provided HTML string
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = newHTML;
    const newElement = tempDiv.firstElementChild;

    // add class to new element.
    newElement.classList.add('anspress-replacing')

    // Replace the selected element with the new element
    element.replaceWith(newElement);

    setTimeout(() => {
      newElement.classList.add('anspress-replaced')
    }, 300);
  } else {
    console.error(`Element with selector '${selector}' not found.`);
  }
}

export const initTynimce = (textarea) => {
  tinymce.init({
    selector: '#' + textarea,
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

export const removeTinymce = (textarea) => {
  tinymce.remove('#' + textarea);
}

export const loadForm = (data) => {
  return fetch({
    path: addQueryArgs(data.load_form_path, { form_loaded: true }),
    method: 'POST',
  })
    .then(res => {
      if (res?.load_tinymce) {
        initTynimce(res?.load_tinymce)
      }
    });
}

export const removeForm = async (data) => {
  if (data?.load_tinymce) {
    removeForm(data.load_tinymce);
  }

  return fetch({
    path: addQueryArgs(data.load_form_path, { form_loaded: 0 }),
    method: 'POST',
  })
}
