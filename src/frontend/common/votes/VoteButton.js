import apiFetch from '@wordpress/api-fetch';

export class VoteButton {
  constructor(voteBlock) {
    this.voteBlock = voteBlock;
    this.postId = voteBlock.dataset.postId;
    this.voteData = JSON.parse(voteBlock.dataset.voteData || '{}');
    this.upvoteButton = voteBlock.querySelector('.wp-block-anspress-single-question-vote-up');
    this.downvoteButton = voteBlock.querySelector('.wp-block-anspress-single-question-vote-down');
    this.voteCountSpan = voteBlock.querySelector('.wp-block-anspress-single-question-vcount');

    this.currentState = {
      postVoteData: this.voteData
    };

    this.upvoteButton.addEventListener('click', () => this.vote('voteup'));
    this.downvoteButton.addEventListener('click', () => this.vote('votedown'));

    // Initial render
    this.render();
  }

  async vote(action) {
    try {
      const path = !this.currentState.postVoteData.currentUserVoted
        ? `/anspress/v1/post/${this.postId}/actions/vote/${action}`
        : `/anspress/v1/post/${this.postId}/actions/undo-vote`;

      const response = await apiFetch({
        path,
        method: 'POST'
      });

      if (response.voteData) {
        this.currentState.postVoteData = response.voteData;
        this.render();
      }
    } catch (error) {
      console.error('An error occurred:', error);
    }
  }

  render() {
    this.voteCountSpan.textContent = this.currentState.postVoteData.votesNet;
    this.upvoteButton.disabled = this.currentState.postVoteData.currentUserVoted === 'votedown';
    this.downvoteButton.disabled = this.currentState.postVoteData.currentUserVoted === 'voteup';

    // Animation logic
    this.voteCountSpan.classList.add('animate-count');
    const animationEndHandler = () => this.voteCountSpan.classList.remove('animate-count');
    this.voteCountSpan.addEventListener('animationend', animationEndHandler, { once: true });
  }
}
