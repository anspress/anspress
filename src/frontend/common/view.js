import { Snackbar } from "./snackbar/Snackbar";

// wp.hooks.addAction('anspress.snackbar.init', 'anspress', (snackbar) => {
//   Snackbar.dispatchSnackbarEvent('Hello, world!', 'success', 5000);
//   setTimeout(() => {
//     Snackbar.dispatchSnackbarEvent('Hello, world!', 'success', 5000);
//     Snackbar.dispatchSnackbarEvent('Hello, world!', 'success', 5000);
//   }, 2000);
// });


const snackbarManager = new Snackbar(Snackbar.createSnackbarContainer());


