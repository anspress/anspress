import { Comments } from '../common/comments/Comments';

import { FormManager } from '../common/FormManager';

import '../common/AnsPressVoteButton';
import '../common/comments/AnsPressCommentList';
import '../common/comments/AnsPressCommentItem';
import '../common/comments/AnsPressCommentForm';

document.addEventListener('DOMContentLoaded', () => {


  document.querySelectorAll('[data-anspressel="answer-form"]').forEach(form => new FormManager(form));



});
