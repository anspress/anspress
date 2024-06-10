import { useState, useEffect } from '@wordpress/element';
import { createRoot } from 'react-dom';
import apiFetch from '@wordpress/api-fetch';

const VoteCounter = ({ postId, initialCount, initialVote }) => {
  const [count, setCount] = useState(initialCount);
  const [previousCount, setPreviousCount] = useState(initialCount);
  const [userVote, setUserVote] = useState(initialVote);

  const vote = async (action) => {
    setPreviousCount(count);
    const newCount = action === 'upvote' ? count + 1 : count - 1;
    setCount(newCount);

    try {
      const response = await apiFetch({
        path: `/wp/v2/posts/${postId}/vote`,
        method: 'POST',
        data: { action }
      });

      if (response.success) {
        setUserVote(action);
      } else {
        setCount(previousCount);
        console.error('Vote action failed:', response.message);
      }
    } catch (error) {
      setCount(previousCount);
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
        className="apicon-thumb-up"
        onClick={() => vote('upvote')}
        disabled={userVote === 'upvote'}
        title="Up vote this question"
      >
      </button>
      <span className="wp-block-anspress-single-question-vcount vote-count">{count}</span>
      <button
        className="apicon-thumb-down"
        onClick={() => vote('downvote')}
        disabled={userVote === 'downvote'}
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
    const vote = JSON.parse(voteBlock.dataset.voteData);
    console.log(vote)
    const root = createRoot(voteBlock);
    root.render(<VoteCounter postId={postId} initialCount={initialCount} vote={vote} />);
  });
}, false);
