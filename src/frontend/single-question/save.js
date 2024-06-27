import { useBlockProps } from '@wordpress/block-editor';

const Save = ({ attributes }) => {
  return (
    <div {...useBlockProps.save()}>
      <div className="anspress-apq-item-avatar">
        <a href="#">
          <img src="https://placehold.it/50x50" alt="Rahul Arya" className="avatar avatar-100 photo" loading="lazy" />
        </a>
      </div>
      <div className="anspress-apq-item-content">
        <div className="anspress-apq-item-metas">
          <div className="anspress-apq-item-author">
            Rahul Arya
          </div>
          <a href="#" className="anspress-apq-item-posted">
            10 mins ago
          </a>
          <span className="anspress-apq-item-ccount">
            0 Comments
          </span>
        </div>
        <div className="anspress-apq-item-inner">
          Vel facilis architecto laborum rerum debitis nam. Eius voluptatem sed dignissimos. Similique dolor molestias et voluptatibus.
        </div>

        <div className="ap-post-footer clearfix">
          Footer
        </div>
      </div>

      <div className="anspress-apq-item-votes from edit" data-gutenberg-attributes={JSON.stringify(attributes)}>
        <a className="apicon-thumb-up anspress-apq-item-vote-up" href="#" title="Up vote this question"></a>
        <span className="anspress-apq-item-count">0</span>
        <a data-tipposition="bottom center" className="apicon-thumb-down anspress-apq-item-vote-down" href="#" title="Down vote this question">

        </a>
      </div>
      <vote-component data-gutenberg-attributes={JSON.stringify(attributes)}></vote-component>
    </div>
  );
};

export default Save;
