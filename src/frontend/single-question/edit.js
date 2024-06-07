import { useBlockProps, InspectorControls, InnerBlocks } from '@wordpress/block-editor';
import { PanelBody, SelectControl, RangeControl, ToggleControl, TextControl, Button } from '@wordpress/components';
import { useState } from 'react';
import ServerSideRender from '@wordpress/server-side-render';

const Edit = ({ attributes }) => {
  const { avatarPosition } = attributes;

  const blockProps = useBlockProps();

  return (
    <div {...blockProps}>
      <InspectorControls>
        <PanelBody title="Avatar Settings">
          {/* <SelectControl
            label="Avatar Position"
            value={avatarPosition}
            options={[
              { label: 'Left', value: 'left' },
              { label: 'Right', value: 'right' },
              { label: 'Top', value: 'top' },
              { label: 'Bottom', value: 'bottom' }
            ]}
            onChange={(value) => setAttributes({ avatarPosition: value })}
          />
          <RangeControl
            label="Avatar Size"
            value={avatarSize}
            onChange={(value) => setAttributes({ avatarSize: value })}
            min={24}
            max={192}
          /> */}
        </PanelBody>
      </InspectorControls>

      <div className="wp-block-anspress-single-question-avatar">
        <a href="#">
          <img src="https://placehold.it/50x50" alt="Rahul Arya" className="avatar avatar-100 photo" loading="lazy" />
        </a>
      </div>
      <div className="wp-block-anspress-single-question-content">
        <div className="wp-block-anspress-single-question-metas">
          <div className="wp-block-anspress-single-question-author">
            Rahul Arya
          </div>
          <a href="#" className="wp-block-anspress-single-question-posted">
            10 mins ago
          </a>
          <span className="wp-block-anspress-single-question-ccount">
            0 Comments
          </span>
        </div>
        <div className="wp-block-anspress-single-question-inner">
          Vel facilis architecto laborum rerum debitis nam. Eius voluptatem sed dignissimos. Similique dolor molestias et voluptatibus.
        </div>

        <div className="ap-post-footer clearfix">
          Footer
        </div>
      </div>

      <div className="wp-block-anspress-single-question-votes">
        <a className="apicon-thumb-up wp-block-anspress-single-question-vote-up" href="#" title="Up vote this question"></a>
        <span className="wp-block-anspress-single-question-count">0</span>
        <a data-tipposition="bottom center" className="apicon-thumb-down wp-block-anspress-single-question-vote-down" href="#" title="Down vote this question">

        </a>
      </div>
    </div>
  );
};

export default Edit;
