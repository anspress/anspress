import apiFetch from '@wordpress/api-fetch';

export const fetch = (path, options) => {
  return apiFetch(path, options)
    .then(res => {
      // if (res.errors && Array.isArray(res.errors) && res.errors.length) {
      //   res.errors.map(snackbarItem => this.dispatchSnackbar(snackbarItem.message, 'error', 5000));
      // }

      // if (res?.appendHtmlTo) {
      //   Object.keys(res.appendHtmlTo).forEach(key => {
      //     const appendTo = document.querySelector(key);
      //     console.log(appendTo)
      //     if (appendTo) {
      //       appendTo.insertAdjacentHTML('beforeend', res.appendHtmlTo[key]);
      //     }
      //   })
      // }

      // // Handle replaceHtml.
      // if (res?.replaceHtml) {
      //   Object.keys(res.replaceHtml).forEach(key => {
      //     const replaceEl = document.querySelector(key);
      //     console.log(replaceEl)
      //     if (replaceEl) {
      //       replaceEl.outerHTML = res.replaceHtml[key];
      //     }
      //   });
      // }

      return res;
    })
    .catch(err => {
      console.error(err)

      // if (err.errors && Array.isArray(err.errors) && err.errors.length) {
      //   err.errors.map(snackbarItem => EventManager.dispatchSnackbar(snackbarItem, 'error', 5000));
      // } else if (
      //   err.message &&
      //   typeof err.message === 'string' &&
      //   err.message.length
      // ) {
      //   EventManager.dispatchSnackbar(err.message, 'error', 5000);
      // }

      throw err;
    });
}
