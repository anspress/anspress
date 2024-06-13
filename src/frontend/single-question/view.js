import { useState, useEffect } from '@wordpress/element';
import { createRoot } from 'react-dom';
import apiFetch from '@wordpress/api-fetch';

const VoteCounter = ({ postId, initialCount, voteData }) => {
  const [count, setCount] = useState(initialCount);
  const [postVoteData, setPostVoteData] = useState(voteData);

  const vote = async (action) => {
    try {
      const path = !postVoteData.currentUserVoted ? `/anspress/v1/post/${postId}/actions/vote/${action}` : `/anspress/v1/post/${postId}/actions/undo-vote`;

      const response = await apiFetch({
        path,
        method: 'POST'
      });

      if (response.voteData) {
        setPostVoteData(response.voteData);
        // Manually trigger a re-render to apply the animation
        setCount(response.voteData.votesNet);
      }
    } catch (error) {
      console.error('An error occurred:', error);
    }
  };

  useEffect(() => {
    const countElement = document.querySelector('.vote-count');
    if (countElement) {
      countElement.classList.add('animate-count');
      const animationEndHandler = () => countElement.classList.remove('animate-count');
      countElement.addEventListener('animationend', animationEndHandler);

      return () => {
        countElement.removeEventListener('animationend', animationEndHandler);
      };
    }
  }, [count]);

  return (
    <>
      <button
        className="apicon-thumb-up wp-block-anspress-single-question-vote-up"
        onClick={() => vote('voteup')}
        disabled={postVoteData.currentUserVoted === 'votedown'}
        title="Up vote this question"
      >
      </button>
      <span className="wp-block-anspress-single-question-vcount vote-count">{postVoteData.votesNet}</span>
      <button
        className="apicon-thumb-down wp-block-anspress-single-question-vote-down"
        onClick={() => vote('votedown')}
        disabled={postVoteData.currentUserVoted === 'voteup'}
        title="Down vote this question"
      >
      </button>
    </>
  );
};

window.addEventListener('load', () => {
  document.querySelectorAll('.wp-block-anspress-single-question-vote').forEach(voteBlock => {
    const postId = voteBlock.dataset.postId;
    const initialCount = parseInt(voteBlock.querySelector('.wp-block-anspress-single-question-vcount').textContent, 10);
    const voteData = JSON.parse(voteBlock.dataset.voteData || '{}');

    const root = createRoot(voteBlock);
    root.render(<VoteCounter postId={postId} initialCount={initialCount} voteData={voteData} />);
  });
}, false);
