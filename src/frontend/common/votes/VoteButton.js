import { EventManager } from "../EventManager";

export class VoteButton extends EventManager {
  updateElements() {
    return {
      'votes-net-count': 'votesNet',
    };
  }
  init() {
    if (!this.data?.postId) {
      console.error('Post ID not found.');
      return;
    }

    super.init();
  }

  async vote(action) {
    try {
      const path = !this.data.currentUserVoted
        ? `/anspress/v1/post/${this.data.postId}/actions/vote/${action}`
        : `/anspress/v1/post/${this.data.postId}/actions/undo-vote`;

      const response = await this.fetch({
        path,
        method: 'POST'
      });

      this.changeButtonState();
    } catch (error) {
      console.error('An error occurred:', error);
    }
  }

  voteUp() {
    this.vote('voteup');

  }

  voteDown() {
    this.vote('votedown');
  }

  changeButtonState() {
    this.elements['vote-up'].disabled = this.data.currentUserVoted === 'votedown';
    this.elements['vote-down'].disabled = this.data.currentUserVoted === 'voteup';
  }
}
