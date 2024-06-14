import { VoteButton } from '../common/votes/VoteButton';
import { Comments } from '../common/comments/Comments';

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.wp-block-anspress-single-question-vote').forEach(voteBlock => new VoteButton(voteBlock));
});

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.anspress-comments').forEach(container => new Comments(container));
});
