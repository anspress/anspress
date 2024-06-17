import { VoteButton } from '../common/votes/VoteButton';
import { Comments } from '../common/comments/Comments';

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-anspressel="vote"]').forEach(voteBlock => new VoteButton(voteBlock));
});

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-anspressel="comments"]').forEach(container => new Comments(container));
});
