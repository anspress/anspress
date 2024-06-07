// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
import { Icon } from '@wordpress/components';
import { useBlockProps } from '@wordpress/block-editor';
import { useMemo } from '@wordpress/element';
import icons from './icons';

const VoteEdit = () => {
  const { voteUp, voteDown } = attributes;
  const blockProps = useBlockProps();

  const netVotes = useMemo(() => {
    return voteUp - voteDown;
  })

  return (
    <div {...blockProps}>
      <div className='wp-block-anspress-vote-button-buttons'>
        <a className="wp-block-anspress-vote-button-vote-up" href="#" title="Up vote this question">
          <Icon icon={icons.voteUp} />
        </a>
        <div className="wp-block-anspress-vote-button-count">{netVotes}</div>
        <a className="wp-block-anspress-vote-button-vote-down" href="#" title="Down vote this question">
          <Icon icon={icons.voteDown} />
        </a>
      </div>
    </div>
  );

};

export default VoteEdit;
