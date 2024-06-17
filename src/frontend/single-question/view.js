import { VoteButton } from '../common/votes/VoteButton';
import { Comments } from '../common/comments/Comments';

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-anspressel="vote"]').forEach(voteBlock => new VoteButton(voteBlock));

  document.querySelectorAll('[data-anspressel="comments"]').forEach(container => new Comments(container));

  // Quick tag editor for the forms.
  // QTags.addButton('eg_paragraph', 'p', '<p>', '</p>', 'p', 'Paragraph tag', 1);
  // QTags.addButton('eg_hr', 'hr', '<hr />', '', 'h', 'Horizontal rule line', 201);
  // QTags.addButton('eg_pre', 'pre', '<pre lang="php">', '</pre>', 'q', 'Preformatted text tag', 111);

  QTags({
    id: 'anspress-quicktag-editor'
  })
});
