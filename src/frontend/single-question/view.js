// import { VoteButton } from '../common/votes/VoteButton';
import { Comments } from '../common/comments/Comments';

import { FormManager } from '../common/FormManager';

import '../common/AnsPressVoteButton';

document.addEventListener('DOMContentLoaded', () => {
  // document.querySelectorAll('[data-anspressel="vote"]').forEach(voteBlock => new VoteButton(voteBlock));

  document.querySelectorAll('[data-anspressel="comments"]').forEach(container => new Comments(container));

  document.querySelectorAll('[data-anspressel="answer-form"]').forEach(form => new FormManager(form));

  document.querySelectorAll('[data-anspressel="comment-form"]').forEach(form => new FormManager(form));


  const answersItemsElement = document.querySelector('[data-anspressel="answers-items"]');

  // Set up the Mutation Observer for dynamically added nodes
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      mutation.addedNodes.forEach((node) => {
        if (node.nodeType === Node.ELEMENT_NODE && node.getAttribute('data-anspressel') === 'question-item') {
          // node.querySelectorAll('[data-anspressel="vote"]').forEach(voteBlock => new VoteButton(voteBlock));
          node.querySelectorAll('[data-anspressel="comments"]').forEach(container => new Comments(container));
          node.querySelectorAll('[data-anspressel="comment-form"]').forEach(form => new FormManager(form));
        }
      });
    });
  });

  observer.observe(answersItemsElement, {
    childList: true,
    subtree: false // Set to false to avoid deep nesting
  });
});
