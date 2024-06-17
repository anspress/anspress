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

      <div className="anspress-apq-item-votes">
        <a className="apicon-thumb-up anspress-apq-item-vote-up" href="#" title="Up vote this question"></a>
        <span className="anspress-apq-item-count">0</span>
        <a data-tipposition="bottom center" className="apicon-thumb-down anspress-apq-item-vote-down" href="#" title="Down vote this question">

        </a>
      </div>
    </div>
  );
};

export default Edit;
