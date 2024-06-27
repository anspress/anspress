import { BaseCustomElement } from "./BaseCustomElement";

export class VoteButton extends BaseCustomElement {

  updateComponent() {
    const voteData = this.data;

    const upVoteButton = this.querySelector('[data-anspressel="vote-up"]');
    const downVoteButton = this.querySelector('[data-anspressel="vote-down"]');
    const voteCountSpan = this.querySelector('[data-anspressel="votes-net-count"]');

    if (upVoteButton) {
      if (voteData.currentUserVoted === 'voteup') {
        upVoteButton.classList.add('highlight');
        upVoteButton.disabled = false; // Ensure it's enabled to allow undo
      } else {
        upVoteButton.classList.remove('highlight');
        upVoteButton.disabled = voteData.currentUserVoted === 'votedown'; // Disable if downvoted
      }
    }

    if (downVoteButton) {
      if (voteData.currentUserVoted === 'votedown') {
        downVoteButton.classList.add('highlight');
        downVoteButton.disabled = false; // Ensure it's enabled to allow undo
      } else {
        downVoteButton.classList.remove('highlight');
        downVoteButton.disabled = voteData.currentUserVoted === 'voteup'; // Disable if upvoted
      }
    }

    if (voteCountSpan) {
      voteCountSpan.textContent = voteData.votesNet;
    }
  }

  addEventListeners() {
    this.querySelector('[data-anspressel="vote-up"]').addEventListener('click', this.voteUp.bind(this));
    this.querySelector('[data-anspressel="vote-down"]').addEventListener('click', this.voteDown.bind(this));
  }

  disconnectedCallback() {
    this.querySelector('[data-anspressel="vote-up"]').removeEventListener('click', this.voteUp);
    this.querySelector('[data-anspressel="vote-down"]').removeEventListener('click', this.voteDown);
  }

  async send(action) {
    const path = !this.data.currentUserVoted
      ? `/anspress/v1/post/${this.data.postId}/actions/vote/${action}`
      : `/anspress/v1/post/${this.data.postId}/actions/undo-vote`;

    const response = await this.fetch({
      path,
      method: 'POST'
    });
  }

  async voteUp(event) {
    event.preventDefault();
    await this.send('voteup');
  }

  async voteDown(event) {
    event.preventDefault();
    await this.send('votedown')
  }
}

customElements.define('anspress-vote-button', VoteButton);
